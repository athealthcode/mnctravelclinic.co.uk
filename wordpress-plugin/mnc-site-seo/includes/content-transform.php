<?php

declare(strict_types=1);

namespace MNC\SiteSEO;

const CHAT_WIDGET_V3_URL = 'https://tlfyevjyogfinulsmljv.supabase.co/functions/v1/pharmacy_chat_widget_v3';
const CHAT_WIDGET_V3_INTEGRITY = 'sha384-wlxe3SLsS7hmJDVsBBjPMeEnUiNSaGVV1g263JQVjgituoX6VueASMArs8aWyyeE';

/**
 * Reduce rendered heading markup to stable text for an exact comparison.
 */
function normalize_heading_text(string $html): string
{
    $spacedHtml = preg_replace('/(<\/?[a-z][^>]*>)/i', ' $1 ', $html);
    $text = html_entity_decode(strip_tags(is_string($spacedHtml) ? $spacedHtml : $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $normalised = preg_replace('/[\s\x{00A0}]+/u', ' ', $text);

    return trim(is_string($normalised) ? $normalised : $text);
}

/**
 * Change the first heading whose rendered text exactly matches the target.
 */
function retag_heading_by_text(string $html, string $fromTag, string $toTag, string $targetText): string
{
    if (!preg_match('/^h[1-6]$/', $fromTag) || !preg_match('/^h[1-6]$/', $toTag)) {
        return $html;
    }

    $replaced = false;
    $pattern = '/<' . preg_quote($fromTag, '/') . '\b([^>]*)>(.*?)<\/' . preg_quote($fromTag, '/') . '>/is';
    $updated = preg_replace_callback(
        $pattern,
        static function (array $matches) use (&$replaced, $fromTag, $toTag, $targetText): string {
            if ($replaced || 0 !== strcasecmp(normalize_heading_text($matches[2]), $targetText)) {
                return $matches[0];
            }

            $replaced = true;
            return '<' . $toTag . $matches[1] . '>' . $matches[2] . '</' . $toTag . '>';
        },
        $html
    );

    return is_string($updated) ? $updated : $html;
}

/**
 * Replace the existing shared widget only when the final v3 SRI hash is known.
 */
function replace_chat_widget_embed(string $html, string $integrity): string
{
    if (!preg_match('/^sha384-[A-Za-z0-9+\/=]+$/', $integrity)) {
        return $html;
    }

    $pattern = '#<script\b[^>]*src=(["\'])https://tlfyevjyogfinulsmljv\.supabase\.co/functions/v1/pharmacy_chat_widget(?:\?[^"\']*)?\1[^>]*>\s*</script>#i';
    $replacement = '<script src="' . CHAT_WIDGET_V3_URL . '" integrity="' . $integrity . '" crossorigin="anonymous" defer></script>';
    $updated = preg_replace($pattern, $replacement, $html, 1);

    return is_string($updated) ? $updated : $html;
}

/**
 * Promote the booking page's visible title without rewriting Elementor data.
 */
function promote_booking_heading(string $html): string
{
    return retag_heading_by_text($html, 'h2', 'h1', 'Book Your Appointment');
}

/**
 * Keep the page title as the sole H1 and demote the legacy Elementor hero H1.
 */
function demote_duplicate_malaria_heading(string $html): string
{
    return retag_heading_by_text($html, 'h1', 'h2', 'Malaria Tablets Manchester Travel Clinic');
}

/**
 * Correct the Wilmslow card and FAQ copy without changing Denton's 5:30pm time.
 */
function correct_wilmslow_opening_hours(string $html): string
{
    $pattern = '/(<h3\b[^>]*>\s*Wilmslow Pharmacy(?:,\s*Cheshire)?\s*<\/h3>.{0,2500}?)9([:.])00am\s*(?:–|&ndash;|&#8211;)\s*5([:.])30pm/isu';
    $updated = preg_replace_callback(
        $pattern,
        static function (array $matches): string {
            return $matches[1] . '9' . $matches[2] . '00am – 6' . $matches[3] . '00pm';
        },
        $html
    );
    $result = is_string($updated) ? $updated : $html;

    return str_replace(
        'Wilmslow Pharmacy is open Monday to Friday 9:00am–5:30pm.',
        'Wilmslow Pharmacy is open Monday to Friday 9:00am–6:00pm.',
        $result
    );
}
