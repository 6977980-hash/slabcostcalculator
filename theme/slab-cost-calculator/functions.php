<?php
/**
 * SLAB COST CALCULATOR - THEME FUNCTIONS & SETUP
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

// Date the site's prices and homepage content were last reviewed. Shown as
// "Last Updated" instead of the current month, so the date only moves when
// someone actually changes the content. Update it when prices are revised.
define('SLAB_CONTENT_UPDATED', '2026-10-12');

/**
 * True only on the real homepage. On the virtual hub routes (/calculators/,
 * /cost-guides/, /how-to/, /knowledgebase/) WordPress also reports
 * is_front_page() as true (see inc/seo-engine.php), so anything meant for
 * the homepage alone must use this instead.
 */
function slab_is_home_page() {
    return is_front_page() && !get_query_var('slab_route');
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
    if (slab_is_home_page() || is_page_template('page-templates/template-calculator-single.php') || is_page_template('page-templates/template-hub.php')) {
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
        // Region index, blueprint, bid checker and weather check. Listens
        // for the engine's slabcalc:updated event, so it loads after it.
        wp_enqueue_script('slab-calculator-tools', get_template_directory_uri() . '/assets/js/calculator-tools.js', ['slab-primary-calc'], $theme_version, [
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

/**
 * The breadcrumb steps after "Home", each ['name' => text, 'item' => url].
 * The last step is the current page. Shared by the visible trail
 * (template-parts/breadcrumbs.php) and the BreadcrumbList JSON-LD
 * (inc/schema-generator.php) so the two always match.
 */
function slab_get_breadcrumb_trail() {
    $hub = Slab_SEO_Engine::get_slab_hub_info();
    if ($hub) {
        return [['name' => $hub['name'], 'item' => home_url($hub['path'])]];
    }

    $trail = [];
    if (is_page()) {
        $page_id = get_queried_object_id();
        foreach (array_reverse(get_post_ancestors($page_id)) as $ancestor_id) {
            $trail[] = ['name' => get_the_title($ancestor_id), 'item' => get_permalink($ancestor_id)];
        }
        $trail[] = ['name' => get_the_title($page_id), 'item' => get_permalink($page_id)];
    } elseif (is_single()) {
        $post_id = get_queried_object_id();
        $categories = get_the_category($post_id);
        if (!empty($categories)) {
            $trail[] = ['name' => $categories[0]->name, 'item' => get_category_link($categories[0]->term_id)];
        }
        $trail[] = ['name' => get_the_title($post_id), 'item' => get_permalink($post_id)];
    } elseif (is_category() || is_tag() || is_tax()) {
        $term = get_queried_object();
        $trail[] = ['name' => $term->name, 'item' => get_term_link($term)];
    } elseif (is_search()) {
        $trail[] = ['name' => 'Search results', 'item' => ''];
    } elseif (is_404()) {
        $trail[] = ['name' => 'Page not found', 'item' => ''];
    } elseif (is_archive()) {
        $trail[] = ['name' => wp_strip_all_tags(get_the_archive_title()), 'item' => ''];
    }
    return $trail;
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

// 6d. oEmbed responses named the WordPress login and linked /author/<slug>/.
// Credit the editorial team page instead, the same as the visible bylines.
add_filter('oembed_response_data', function ($data) {
    $data['author_name'] = 'Slab Cost Calculator Editorial Team';
    $data['author_url'] = home_url('/authors/');
    return $data;
});

// 7. Head cleanup and hardening
//
// - Generator tag and emoji scripts: the version number helps attackers
//   and the emoji script/CSS (about 5 KB) loads on every page for nothing.
// - Per-post and category comment feeds: comments are off, so these are
//   empty feeds linked from every page.
// - XML-RPC: unused here, and its multicall/pingback methods are a
//   brute-force and pingback-abuse target.
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'rsd_link');
add_filter('the_generator', '__return_empty_string');
add_filter('xmlrpc_enabled', '__return_false');
add_filter('xmlrpc_methods', '__return_empty_array');
add_filter('pings_open', '__return_false');
// The filters above still leave system.multicall answering, so refuse
// XML-RPC requests outright.
add_action('init', function () {
    if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
        status_header(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'XML-RPC is disabled on this site.';
        exit;
    }
}, 0);
add_filter('wp_headers', function ($headers) {
    unset($headers['X-Pingback']);
    return $headers;
});

// Security headers on every front-end response.
add_action('send_headers', function () {
    if (is_admin() || headers_sent()) {
        return;
    }
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), payment=(), usb=()');
    if (is_ssl()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
});

// Feeds are for feed readers, not search results. Comment feeds and the
// per-category feeds carry no content of their own.
add_action('template_redirect', function () {
    if (is_feed() && !headers_sent()) {
        header('X-Robots-Tag: noindex, follow', true);
    }
}, 0);
