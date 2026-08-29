# MNC WordPress SEO deployment

The public website is an Elementor WordPress installation. The original repository contains only static prototypes, so the live-safe changes are packaged as `wordpress-plugin/mnc-site-seo`.

## Before activation

1. Take a WordPress backup or confirm a current recoverable backup exists.
2. Upload the plugin directory without modifying the active Elementor theme.
3. Confirm page IDs still match production:
   - booking: 1922
   - employee panel: 2104
   - customer panel: 2211
   - malaria prevention: 3271
4. Confirm the packaged v3 SHA-384 value matches the deployed assistant. `MNC_CHAT_WIDGET_V3_INTEGRITY` is an optional override for later releases.

## Expected changes

| Route | Expected result |
|---|---|
| `/book-now/` | One H1, a specific title and a meta description |
| `/employee-panel/` | `noindex, nofollow`, an X-Robots-Tag header and no sitemap entry |
| `/customer-panel/` | `noindex, nofollow`, an X-Robots-Tag header and no sitemap entry |
| `/service/malaria-vaccination/` | One-hop HTTP 301 to `/service/malaria-prevention/` |
| `/service/malaria-prevention/` | HTTP 200, self-canonical and one H1 |
| home and `/locations/` | Three Pharmacy nodes linked to the existing MNC Organization schema |
| all public pages | Exactly one `/pharmacy_chat_widget_v3` embed with the final SHA-384 integrity value |
| contact and `/locations/` | Wilmslow closes at 6:00pm; Denton remains 5:30pm |

## Verification

Run local syntax and transformation checks before upload:

```sh
php -l wordpress-plugin/mnc-site-seo/mnc-site-seo.php
php -l wordpress-plugin/mnc-site-seo/includes/content-transform.php
php wordpress-plugin/mnc-site-seo/tests/run.php
```

After activation, verify the public responses:

```sh
curl -sSI https://mnctravelclinic.co.uk/service/malaria-vaccination/
curl -sSI https://mnctravelclinic.co.uk/service/malaria-prevention/
curl -sSI https://mnctravelclinic.co.uk/employee-panel/
curl -sSI https://mnctravelclinic.co.uk/customer-panel/
```

Inspect page source for canonical URLs, robot directives, the booking meta description, exactly one H1 on the booking and malaria pages, and three distinct Pharmacy schema nodes. Confirm the old panel URLs are absent from the XML sitemap.

## Rollback

Deactivate the plugin and clear all caches. Activation and deactivation flush rewrite rules. The plugin does not rewrite Elementor data, page slugs or stored content.
