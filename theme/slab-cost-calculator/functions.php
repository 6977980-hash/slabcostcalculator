<?php
/**
 * SLAB COST CALCULATOR - THEME FUNCTIONS & SETUP
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Theme Support Setup
function slab_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ]);
    add_theme_support('responsive-embeds');

    // Register Menus
    register_nav_menus([
        'primary-menu' => __('Primary Navigation', 'slab-cost-calculator'),
        'footer-menu' => __('Footer Navigation', 'slab-cost-calculator'),
        'calculators-menu' => __('Calculators Hub Menu', 'slab-cost-calculator')
    ]);
}
add_action('after_setup_theme', 'slab_theme_setup');

// 2. Enqueue Styles & Scripts
function slab_enqueue_assets() {
    // Read the version from the style.css actually being served.
    // wp_get_theme() always reads wp-content/themes/, which is not the
    // copy this site runs from (see the Slab Cost Calculator Fixes
    // plugin), so bumping Version there would never bust asset caches.
    $theme_version = get_file_data(get_template_directory() . '/style.css', ['Version' => 'Version'])['Version'];

    // CSS
    wp_enqueue_style('slab-main-style', get_template_directory_uri() . '/assets/css/main.css', [], $theme_version);
    wp_enqueue_style('slab-calculator-style', get_template_directory_uri() . '/assets/css/calculator.css', ['slab-main-style'], $theme_version);
    wp_enqueue_style('slab-print-style', get_template_directory_uri() . '/assets/css/print.css', ['slab-main-style'], $theme_version, 'print');

    // Core Theme JS
    //
    // Core Web Vitals / INP: switched from the old boolean $in_footer arg to
    // WP 6.3's array-args form with 'strategy' => 'defer'. All three of these
    // scripts already ran from the footer (so they weren't head-blocking),
    // but plain footer placement still forces the browser to execute them
    // the instant it reaches that point in the HTML; `defer` lets the browser
    // keep parsing/painting and only run the script once parsing is done, in
    // source order, which is strictly better for INP/TBT with no behavior
    // change (all of this code already only runs on DOMContentLoaded).
    // On WordPress < 6.3, this array is simply evaluated as a truthy
    // $in_footer value, so it safely degrades to the previous behavior
    // instead of erroring.
    wp_enqueue_script('slab-theme-main', get_template_directory_uri() . '/assets/js/main.js', [], $theme_version, [
        'in_footer' => true,
        'strategy' => 'defer',
    ]);

    // Primary Calculator JS (Front-page or single calculator templates)
    //
    // BUG FIX (found during the PDF report audit, confirmed with user
    // permission): page-templates/template-hub.php also embeds the primary
    // calculator via get_template_part('template-parts/calculator-primary')
    // -- same as front-page.php and template-calculator-single.php -- but
    // was missing from this condition. Any page using that template loaded
    // the calculator's HTML with none of its JS, so every input silently
    // did nothing (no live totals, no BOQ table, no PDF button) on those
    // pages specifically.
    if (is_front_page() || is_page_template('page-templates/template-calculator-single.php') || is_page_template('page-templates/template-calculator-hub.php') || is_page_template('page-templates/template-hub.php')) {
        // PDF REPORT ENGINE: the "Print Estimate" button used to call
        // window.print() against print.css -- effectively printing/
        // screenshotting the live calculator page. It now builds a real,
        // structured PDF (assets/js/pdf-report.js) straight from the
        // calculator's own calculation-result object. jsPDF + its
        // autoTable plugin are vendored locally (assets/js/vendor/) rather
        // than pulled from a CDN, matching this theme's existing
        // zero-dependency approach, and must load, in order, BEFORE
        // pdf-report.js, which in turn must load BEFORE primary-calculator.js
        // runs its DOMContentLoaded handler -- enqueue dependencies below
        // enforce that exact order regardless of each script's own position
        // in the page.
        //
        // LAZY PDF LIBRARIES: jsPDF + autoTable (~400KB) are no longer
        // enqueued on page load. Only their URLs are handed to
        // pdf-report.js, which injects them the first time the visitor
        // clicks "Print Estimate" (see SlabPDFReport.load()).
        wp_enqueue_script('slab-pdf-report', get_template_directory_uri() . '/assets/js/pdf-report.js', [], $theme_version, [
            'in_footer' => true,
            'strategy' => 'defer',
        ]);
        wp_add_inline_script('slab-pdf-report', 'window.SlabPDFConfig = ' . wp_json_encode([
            'libs' => [
                get_template_directory_uri() . '/assets/js/vendor/jspdf.umd.min.js?ver=' . $theme_version,
                get_template_directory_uri() . '/assets/js/vendor/jspdf.plugin.autotable.min.js?ver=' . $theme_version,
            ],
        ]) . ';', 'before');
        wp_enqueue_script('slab-primary-calc', get_template_directory_uri() . '/assets/js/primary-calculator.js', ['slab-pdf-report'], $theme_version, [
            'in_footer' => true,
            'strategy' => 'defer',
        ]);
        wp_enqueue_script('slab-supporting-calcs', get_template_directory_uri() . '/assets/js/supporting-calculators.js', ['slab-primary-calc'], $theme_version, [
            'in_footer' => true,
            'strategy' => 'defer',
        ]);
    }
}
add_action('wp_enqueue_scripts', 'slab_enqueue_assets');

// 3. Load Modular Includes
require_once get_template_directory() . '/inc/seo-engine.php';
require_once get_template_directory() . '/inc/calculator-faq.php';
require_once get_template_directory() . '/inc/schema-generator.php';
require_once get_template_directory() . '/inc/sitemap-generator.php';
require_once get_template_directory() . '/inc/llms-txt-generator.php';
require_once get_template_directory() . '/inc/custom-post-types.php';
require_once get_template_directory() . '/inc/demo-content-importer.php';

// 4. Custom Excerpt Length
function slab_custom_excerpt_length($length) {
    return 24;
}
add_filter('excerpt_length', 'slab_custom_excerpt_length', 999);

// 5. Breadcrumbs Helper
function slab_render_breadcrumbs() {
    get_template_part('template-parts/breadcrumbs');
}

// 6. Author Identity Hardening
//
// The site has a single admin account whose login slug ("6977980") was
// exposed in three places: the public REST users endpoint, core's
// /wp-sitemap-users-1.xml, and the /author/<slug>/ archive. None of these
// serve readers (all content is credited to the team via /authors/), so
// they are closed off here.

// 6a. Drop the users provider from WordPress core's /wp-sitemap.xml.
add_filter('wp_sitemaps_add_provider', function ($provider, $name) {
    return $name === 'users' ? false : $provider;
}, 10, 2);

// 6b. Hide the REST users endpoints from logged-out visitors. Logged-in
// editors keep them, since the block editor relies on them.
add_filter('rest_endpoints', function ($endpoints) {
    if (!is_user_logged_in()) {
        unset($endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }
    return $endpoints;
});

// 6c. 301 author archives (and ?author=N enumeration) to the team page.
add_action('template_redirect', function () {
    if (is_author() || (isset($_GET['author']) && !is_admin())) {
        wp_safe_redirect(home_url('/authors/'), 301);
        exit;
    }
});
