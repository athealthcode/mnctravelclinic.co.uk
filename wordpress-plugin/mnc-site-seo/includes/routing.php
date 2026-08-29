<?php

declare(strict_types=1);

namespace MNC\SiteSEO;

/**
 * Normalise a URL path only for route identity checks.
 */
function normalise_url_route(string $url): ?string
{
    $path = parse_url($url, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        return null;
    }

    $trimmed = trim($path, '/');
    return $trimmed === '' ? '/' : '/' . $trimmed . '/';
}

/**
 * Compare the canonical parts that WordPress normally redirects.
 */
function has_canonical_origin_and_path(string $requestedUrl, string $canonicalUrl): bool
{
    $requested = parse_url($requestedUrl);
    $canonical = parse_url($canonicalUrl);
    if (!is_array($requested) || !is_array($canonical)) {
        return false;
    }

    $requestedScheme = strtolower((string) ($requested['scheme'] ?? ''));
    $canonicalScheme = strtolower((string) ($canonical['scheme'] ?? ''));
    $requestedHost = strtolower((string) ($requested['host'] ?? ''));
    $canonicalHost = strtolower((string) ($canonical['host'] ?? ''));
    $requestedPort = isset($requested['port']) ? (int) $requested['port'] : null;
    $canonicalPort = isset($canonical['port']) ? (int) $canonical['port'] : null;
    $requestedPath = isset($requested['path']) ? (string) $requested['path'] : '/';
    $canonicalPath = isset($canonical['path']) ? (string) $canonical['path'] : '/';

    return $requestedScheme === $canonicalScheme
        && $requestedHost === $canonicalHost
        && $requestedPort === $canonicalPort
        && $requestedPath === $canonicalPath;
}

/**
 * Suppress only the legacy-slug bounce for an already canonical request.
 *
 * For a non-canonical slash, scheme or host, redirect directly to the new
 * canonical URL rather than allowing WordPress to send the request backwards
 * to the stored legacy slug.
 *
 * @param string|false $redirectUrl
 * @return string|false
 */
function resolve_malaria_canonical_redirect(
    $redirectUrl,
    string $requestedUrl,
    string $canonicalUrl,
    string $legacyPath,
    string $canonicalPath
) {
    if (!is_string($redirectUrl) || $redirectUrl === '') {
        return $redirectUrl;
    }

    if (
        normalise_url_route($requestedUrl) !== normalise_url_route($canonicalPath)
        || normalise_url_route($redirectUrl) !== normalise_url_route($legacyPath)
    ) {
        return $redirectUrl;
    }

    return has_canonical_origin_and_path($requestedUrl, $canonicalUrl) ? false : $canonicalUrl;
}
