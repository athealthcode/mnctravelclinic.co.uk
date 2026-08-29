<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/content-transform.php';
require_once dirname(__DIR__) . '/includes/routing.php';

use function MNC\SiteSEO\demote_duplicate_malaria_heading;
use function MNC\SiteSEO\correct_wilmslow_opening_hours;
use function MNC\SiteSEO\promote_booking_heading;
use function MNC\SiteSEO\replace_chat_widget_embed;
use function MNC\SiteSEO\resolve_malaria_canonical_redirect;

function assert_same($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, "FAIL: {$message}\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true) . "\n");
        exit(1);
    }
}

$booking = '<main><h2 class="elementor-heading-title">Book Your Appointment</h2><h2>Select Your Appointment Time</h2></main>';
$expectedBooking = '<main><h1 class="elementor-heading-title">Book Your Appointment</h1><h2>Select Your Appointment Time</h2></main>';
assert_same($expectedBooking, promote_booking_heading($booking), 'booking page must receive one visible H1');

$liveBooking = <<<'HTML'
<main><h2 class="text-gray-900 text-5xl font-bold">
    Book Your            <span class="text-transparent"> Appointment</span>
</h2><h2>Select Your Appointment Time</h2></main>
HTML;
$expectedLiveBooking = <<<'HTML'
<main><h1 class="text-gray-900 text-5xl font-bold">
    Book Your            <span class="text-transparent"> Appointment</span>
</h1><h2>Select Your Appointment Time</h2></main>
HTML;
assert_same($expectedLiveBooking, promote_booking_heading($liveBooking), 'live booking heading markup must be promoted');

$malaria = '<main><h1>Malaria Prevention</h1><h1 class="elementor-heading-title">Malaria Tablets Manchester Travel Clinic</h1><h2>Who Needs Antimalarial Tablets?</h2></main>';
$expectedMalaria = '<main><h1>Malaria Prevention</h1><h2 class="elementor-heading-title">Malaria Tablets Manchester Travel Clinic</h2><h2>Who Needs Antimalarial Tablets?</h2></main>';
assert_same($expectedMalaria, demote_duplicate_malaria_heading($malaria), 'malaria page must keep only its primary H1');

$liveMalaria = <<<'HTML'
<main><h1>Malaria Prevention</h1><h1 class="text-white text-[40px] md:text-[64px]">
    Malaria Tablets<span class="block text-cyan-200">Manchester Travel Clinic</span>
</h1><h2>Who Needs Antimalarial Tablets?</h2></main>
HTML;
$expectedLiveMalaria = <<<'HTML'
<main><h1>Malaria Prevention</h1><h2 class="text-white text-[40px] md:text-[64px]">
    Malaria Tablets<span class="block text-cyan-200">Manchester Travel Clinic</span>
</h2><h2>Who Needs Antimalarial Tablets?</h2></main>
HTML;
assert_same($expectedLiveMalaria, demote_duplicate_malaria_heading($liveMalaria), 'live malaria heading markup must be demoted');

$unrelated = '<main><h2>Book another service</h2></main>';
assert_same($unrelated, promote_booking_heading($unrelated), 'unrelated headings must not change');
assert_same($unrelated, demote_duplicate_malaria_heading($unrelated), 'unrelated malaria content must not change');

$widgetV2 = '<script src="https://tlfyevjyogfinulsmljv.supabase.co/functions/v1/pharmacy_chat_widget?ver=2.0.1.1" integrity="sha384-old" crossorigin="anonymous" defer></script>';
$widgetV3 = '<script src="https://tlfyevjyogfinulsmljv.supabase.co/functions/v1/pharmacy_chat_widget_v3" integrity="sha384-dGVzdA==" crossorigin="anonymous" defer></script>';
assert_same($widgetV3, replace_chat_widget_embed($widgetV2, 'sha384-dGVzdA=='), 'valid SRI must migrate the widget to v3');
assert_same($widgetV2, replace_chat_widget_embed($widgetV2, 'pending'), 'missing SRI must leave the working widget unchanged');

$openingHours = '<h3>Wilmslow Pharmacy</h3><p>Monday to Friday, 9.00am – 5.30pm</p>'
    . '<h3>Denton Pharmacy</h3><p>Monday to Friday, 9.00am – 5.30pm</p>'
    . '<p>Wilmslow Pharmacy is open Monday to Friday 9:00am–5:30pm.</p>';
$expectedOpeningHours = '<h3>Wilmslow Pharmacy</h3><p>Monday to Friday, 9.00am – 6.00pm</p>'
    . '<h3>Denton Pharmacy</h3><p>Monday to Friday, 9.00am – 5.30pm</p>'
    . '<p>Wilmslow Pharmacy is open Monday to Friday 9:00am–6:00pm.</p>';
assert_same(
    $expectedOpeningHours,
    correct_wilmslow_opening_hours($openingHours),
    'Wilmslow must show 6pm without changing Denton'
);

$legacyPath = '/service/malaria-vaccination/';
$canonicalPath = '/service/malaria-prevention/';
$canonicalUrl = 'https://mnctravelclinic.co.uk/service/malaria-prevention/';
$legacyUrl = 'https://mnctravelclinic.co.uk/service/malaria-vaccination/';

assert_same(
    false,
    resolve_malaria_canonical_redirect($legacyUrl, $canonicalUrl, $canonicalUrl, $legacyPath, $canonicalPath),
    'an already canonical request must not bounce to the stored legacy slug'
);
assert_same(
    $canonicalUrl,
    resolve_malaria_canonical_redirect($legacyUrl, rtrim($canonicalUrl, '/'), $canonicalUrl, $legacyPath, $canonicalPath),
    'a missing trailing slash must redirect to the canonical URL'
);
assert_same(
    $canonicalUrl,
    resolve_malaria_canonical_redirect($legacyUrl, 'http://mnctravelclinic.co.uk/service/malaria-prevention/', $canonicalUrl, $legacyPath, $canonicalPath),
    'a non-canonical scheme must redirect directly to the canonical URL'
);
assert_same(
    $canonicalUrl,
    resolve_malaria_canonical_redirect($legacyUrl, 'https://www.mnctravelclinic.co.uk/service/malaria-prevention/', $canonicalUrl, $legacyPath, $canonicalPath),
    'a non-canonical host must redirect directly to the canonical URL'
);
assert_same(
    $canonicalUrl,
    resolve_malaria_canonical_redirect($canonicalUrl, 'http://mnctravelclinic.co.uk/service/malaria-prevention/', $canonicalUrl, $legacyPath, $canonicalPath),
    'a normal WordPress scheme redirect must be preserved'
);
$unrelatedRedirect = 'https://mnctravelclinic.co.uk/contact/';
assert_same(
    $unrelatedRedirect,
    resolve_malaria_canonical_redirect($unrelatedRedirect, 'https://mnctravelclinic.co.uk/contact-us/', $canonicalUrl, $legacyPath, $canonicalPath),
    'unrelated canonical redirects must remain unchanged'
);

fwrite(STDOUT, "All MNC Site SEO tests passed.\n");
