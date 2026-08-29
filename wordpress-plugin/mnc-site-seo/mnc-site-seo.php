<?php
/**
 * Plugin Name: MNC Site SEO
 * Description: Targeted technical SEO safeguards for MNC Travel Clinic.
 * Version: 1.0.1
 * Author: AT Health Ltd
 */

declare(strict_types=1);

namespace MNC\SiteSEO;

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/includes/content-transform.php';
require_once __DIR__ . '/includes/routing.php';

const BOOKING_PAGE_ID = 1922;
const EMPLOYEE_PANEL_PAGE_ID = 2104;
const CUSTOMER_PANEL_PAGE_ID = 2211;
const MALARIA_PAGE_ID = 3271;
const MALARIA_OLD_PATH = '/service/malaria-vaccination/';
const MALARIA_NEW_PATH = '/service/malaria-prevention/';

function bootstrap(): void
{
    add_action('init', __NAMESPACE__ . '\\register_rewrites');
    add_action('template_redirect', __NAMESPACE__ . '\\redirect_old_malaria_url', 1);
    add_action('template_redirect', __NAMESPACE__ . '\\start_output_buffer', 100);
    add_action('send_headers', __NAMESPACE__ . '\\send_panel_robots_header');
    add_action('wp_head', __NAMESPACE__ . '\\print_fallback_booking_description', 1);
    add_action('wp_head', __NAMESPACE__ . '\\print_location_schema', 35);

    add_filter('page_link', __NAMESPACE__ . '\\filter_malaria_page_link', 10, 2);
    add_filter('redirect_canonical', __NAMESPACE__ . '\\filter_canonical_redirect', 10, 2);
    add_filter('pre_get_document_title', __NAMESPACE__ . '\\filter_booking_title', 20);
    add_filter('document_title_parts', __NAMESPACE__ . '\\filter_booking_title_parts', 20);
    add_filter('wpseo_title', __NAMESPACE__ . '\\filter_booking_title', 20);
    add_filter('wpseo_opengraph_title', __NAMESPACE__ . '\\filter_booking_title', 20);
    add_filter('wpseo_metadesc', __NAMESPACE__ . '\\filter_booking_description', 20);
    add_filter('wpseo_opengraph_desc', __NAMESPACE__ . '\\filter_booking_description', 20);
    add_filter('wpseo_canonical', __NAMESPACE__ . '\\filter_malaria_canonical', 20);
    add_filter('wp_robots', __NAMESPACE__ . '\\filter_panel_robots', 20);
    add_filter('wpseo_robots', __NAMESPACE__ . '\\filter_yoast_panel_robots', 20);
    add_filter('wpseo_robots_array', __NAMESPACE__ . '\\filter_yoast_panel_robots_array', 20);
    add_filter('wpseo_exclude_from_sitemap_by_post_ids', __NAMESPACE__ . '\\exclude_panels_from_yoast_sitemap', 20);
    add_filter('wp_sitemaps_posts_query_args', __NAMESPACE__ . '\\exclude_panels_from_core_sitemap', 20, 2);
}

function activate(): void
{
    register_rewrites();
    flush_rewrite_rules(false);
}

function deactivate(): void
{
    flush_rewrite_rules(false);
}

function register_rewrites(): void
{
    add_rewrite_rule(
        '^service/malaria-prevention/?$',
        'index.php?page_id=' . MALARIA_PAGE_ID,
        'top'
    );
}

function request_path(): string
{
    $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
    $path = wp_parse_url($requestUri, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        return '/';
    }

    $trimmed = trim($path, '/');
    return $trimmed === '' ? '/' : '/' . $trimmed . '/';
}

function malaria_url(): string
{
    return home_url(MALARIA_NEW_PATH);
}

function redirect_old_malaria_url(): void
{
    if (request_path() !== MALARIA_OLD_PATH) {
        return;
    }

    wp_safe_redirect(malaria_url(), 301, 'MNC Site SEO');
    exit;
}

function filter_malaria_page_link(string $url, int $postId): string
{
    return $postId === MALARIA_PAGE_ID ? malaria_url() : $url;
}

/**
 * WordPress otherwise redirects the rewritten new route back to the stored slug.
 * The old route is handled separately as a permanent redirect.
 *
 * @param string|false $redirectUrl
 * @return string|false
 */
function filter_canonical_redirect($redirectUrl, string $requestedUrl)
{
    return resolve_malaria_canonical_redirect(
        $redirectUrl,
        $requestedUrl,
        malaria_url(),
        MALARIA_OLD_PATH,
        MALARIA_NEW_PATH
    );
}

function booking_title(): string
{
    return 'Book a Travel Clinic Appointment | MNC Travel Clinic';
}

function booking_description(): string
{
    return 'Book a pharmacist-led travel clinic appointment in Denton, Wythenshawe or Wilmslow. Choose your clinic, service and appointment time online.';
}

function filter_booking_title(string $title): string
{
    return is_page(BOOKING_PAGE_ID) ? booking_title() : $title;
}

/**
 * @param array<string,string> $parts
 * @return array<string,string>
 */
function filter_booking_title_parts(array $parts): array
{
    if (!is_page(BOOKING_PAGE_ID)) {
        return $parts;
    }

    $parts['title'] = 'Book a Travel Clinic Appointment';
    $parts['site'] = 'MNC Travel Clinic';
    return $parts;
}

function filter_booking_description(string $description): string
{
    return is_page(BOOKING_PAGE_ID) ? booking_description() : $description;
}

function print_fallback_booking_description(): void
{
    if (!is_page(BOOKING_PAGE_ID) || defined('WPSEO_VERSION')) {
        return;
    }

    echo '<meta name="description" content="' . esc_attr(booking_description()) . '">' . "\n";
}

function filter_malaria_canonical(string $canonical): string
{
    return is_page(MALARIA_PAGE_ID) ? malaria_url() : $canonical;
}

function is_restricted_panel(): bool
{
    return is_page([EMPLOYEE_PANEL_PAGE_ID, CUSTOMER_PANEL_PAGE_ID]);
}

/**
 * @param array<string,mixed> $robots
 * @return array<string,mixed>
 */
function filter_panel_robots(array $robots): array
{
    if (!is_restricted_panel()) {
        return $robots;
    }

    $robots['noindex'] = true;
    $robots['nofollow'] = true;
    unset($robots['index'], $robots['follow'], $robots['max-image-preview'], $robots['max-snippet'], $robots['max-video-preview']);
    return $robots;
}

function filter_yoast_panel_robots(string $robots): string
{
    return is_restricted_panel() ? 'noindex, nofollow' : $robots;
}

/**
 * @param array<string,mixed> $robots
 * @return array<string,mixed>
 */
function filter_yoast_panel_robots_array(array $robots): array
{
    return filter_panel_robots($robots);
}

function send_panel_robots_header(): void
{
    if (is_restricted_panel() && !headers_sent()) {
        header('X-Robots-Tag: noindex, nofollow', true);
    }
}

/**
 * @param array<int,int> $postIds
 * @return array<int,int>
 */
function exclude_panels_from_yoast_sitemap(array $postIds): array
{
    return array_values(array_unique(array_merge($postIds, [EMPLOYEE_PANEL_PAGE_ID, CUSTOMER_PANEL_PAGE_ID])));
}

/**
 * @param array<string,mixed> $args
 * @return array<string,mixed>
 */
function exclude_panels_from_core_sitemap(array $args, string $postType): array
{
    if ($postType !== 'page') {
        return $args;
    }

    $excluded = isset($args['post__not_in']) && is_array($args['post__not_in']) ? $args['post__not_in'] : [];
    $args['post__not_in'] = array_values(array_unique(array_merge($excluded, [EMPLOYEE_PANEL_PAGE_ID, CUSTOMER_PANEL_PAGE_ID])));
    return $args;
}

function start_output_buffer(): void
{
    if (is_admin() || wp_doing_ajax() || is_feed() || is_robots()) {
        return;
    }

    ob_start(__NAMESPACE__ . '\\transform_page_html');
}

function transform_page_html(string $html): string
{
    $integrity = defined('MNC_CHAT_WIDGET_V3_INTEGRITY')
        ? (string) constant('MNC_CHAT_WIDGET_V3_INTEGRITY')
        : CHAT_WIDGET_V3_INTEGRITY;
    $html = replace_chat_widget_embed($html, $integrity);
    $html = correct_wilmslow_opening_hours($html);
    if (is_page(BOOKING_PAGE_ID)) {
        return promote_booking_heading($html);
    }
    if (is_page(MALARIA_PAGE_ID)) {
        return demote_duplicate_malaria_heading($html);
    }

    return $html;
}

function print_location_schema(): void
{
    if (!is_front_page() && !is_page('locations')) {
        return;
    }

    $parentId = home_url('/#organization');
    $locationsUrl = home_url('/locations/');
    $locations = [
        [
            '@type' => 'Pharmacy',
            '@id' => home_url('/#denton-clinic'),
            'name' => 'MNC Travel Clinic at Denton Pharmacy',
            'url' => $locationsUrl,
            'telephone' => '+44 161 336 2548',
            'parentOrganization' => ['@id' => $parentId],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '14 Ashton Road',
                'addressLocality' => 'Denton',
                'addressRegion' => 'Greater Manchester',
                'postalCode' => 'M34 3EX',
                'addressCountry' => 'GB',
            ],
        ],
        [
            '@type' => 'Pharmacy',
            '@id' => home_url('/#bowland-clinic'),
            'name' => 'MNC Travel Clinic at Bowland Pharmacy',
            'url' => $locationsUrl,
            'telephone' => '+44 161 998 7114',
            'parentOrganization' => ['@id' => $parentId],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '52 Bowland Road',
                'addressLocality' => 'Wythenshawe',
                'addressRegion' => 'Greater Manchester',
                'postalCode' => 'M23 1JX',
                'addressCountry' => 'GB',
            ],
        ],
        [
            '@type' => 'Pharmacy',
            '@id' => home_url('/#wilmslow-clinic'),
            'name' => 'MNC Travel Clinic at Wilmslow Pharmacy',
            'url' => $locationsUrl,
            'telephone' => '+44 1625 523414',
            'parentOrganization' => ['@id' => $parentId],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Unit 2 Summerfields Village Centre, Dean Row Road',
                'addressLocality' => 'Wilmslow',
                'addressRegion' => 'Cheshire',
                'postalCode' => 'SK9 2TA',
                'addressCountry' => 'GB',
            ],
        ],
    ];

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => $locations,
    ];

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}

register_activation_hook(__FILE__, __NAMESPACE__ . '\\activate');
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\\deactivate');
bootstrap();
