=== MNC Site SEO ===
Contributors: at-health
Requires at least: 6.4
Requires PHP: 7.4
Stable tag: 1.0.1

Targeted technical SEO safeguards for MNC Travel Clinic.

== Changes ==

* Gives /book-now/ a specific title, meta description and visible H1.
* Emits Pharmacy schema for Denton, Bowland and Wilmslow on the home and locations pages.
* Marks the employee and customer panels noindex and nofollow, adds an X-Robots-Tag header, and removes both from WordPress and Yoast sitemaps.
* Serves the malaria page at /service/malaria-prevention/ and permanently redirects the misleading old URL.
* Demotes the legacy duplicate malaria H1 to H2.
* Replaces the existing shared assistant embed with the v3 assistant only after a valid SHA-384 integrity value is configured.
* Corrects Wilmslow Pharmacy's Monday to Friday closing time to 6:00pm without changing Denton's 5:30pm time.

== Installation ==

1. Upload the mnc-site-seo directory to wp-content/plugins/.
2. Activate MNC Site SEO in WordPress.
3. Clear the WordPress, page and CDN caches.
4. Run the verification steps in the repository deployment guide before requesting reindexing.

The plugin includes the verified v3 SHA-384 value. `MNC_CHAT_WIDGET_V3_INTEGRITY` may override it for a later release. The plugin flushes rewrite rules only on activation and deactivation and does not change the stored Elementor documents.
