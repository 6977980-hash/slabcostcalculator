<?php
/**
 * ROUTING & CUSTOM POST HIERARCHIES
 * Configures clean URL rewrite endpoints for Calculators, Applications, Cost Guides, and How-Tos.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_Architecture_Router {

    public static function init() {
        add_action('init', [__CLASS__, 'register_clean_rewrite_rules']);
        add_action('init', [__CLASS__, 'maybe_flush_rewrite_rules'], 20);
        add_action('template_redirect', [__CLASS__, 'redirect_legacy_nested_urls'], 1);
        add_action('template_redirect', [__CLASS__, 'redirect_duplicate_slug_urls'], 1);
        add_action('template_redirect', [__CLASS__, 'redirect_duplicate_topic_pages'], 1);
        add_filter('template_include', [__CLASS__, 'route_custom_templates']);
    }

    /**
     * The /knowledgebase/ rule added below only takes effect once WordPress's
     * rewrite rules are flushed. That normally happens automatically on
     * theme activation (see Slab_Content_Seeder::seed_pages_and_menus()),
     * but a theme UPDATE (re-uploading these files over an already-active
     * theme) does not fire after_switch_theme, so the new route would 404
     * until someone happened to visit wp-admin > Settings > Permalinks and
     * hit Save. Auto-flush exactly once by comparing a version marker, so
     * this new URL works immediately after updating the theme files with no
     * manual step required.
     */
    public static function maybe_flush_rewrite_rules() {
        $marker = 'phase7-report-states-embed-v1';
        if (get_option('slab_rewrite_rules_version') !== $marker) {
            flush_rewrite_rules();
            update_option('slab_rewrite_rules_version', $marker);
        }
    }

    public static function register_clean_rewrite_rules() {
        // 1. Calculators Hub & Singles: /calculators/ & /calculators/[slug]/
        add_rewrite_rule('^calculators/?$', 'index.php?slab_route=calc_hub', 'top');
        add_rewrite_rule('^calculators/([^/]+)/?$', 'index.php?slab_route=calc_single&slab_slug=$matches[1]', 'top');

        // 2. Concrete Project Applications: /concrete/[slug]/
        add_rewrite_rule('^concrete/([^/]+)/?$', 'index.php?slab_route=concrete_app&slab_slug=$matches[1]', 'top');

        // 3. Cost Guides: /cost-guides/ & /cost-guides/[slug]/
        add_rewrite_rule('^cost-guides/?$', 'index.php?slab_route=cost_guide_hub', 'top');
        add_rewrite_rule('^cost-guides/([^/]+)/?$', 'index.php?slab_route=cost_guide_single&slab_slug=$matches[1]', 'top');

        // 4. How-To Hub & Articles: /how-to/ & /how-to/[slug]/
        add_rewrite_rule('^how-to/?$', 'index.php?slab_route=how_to_hub', 'top');
        add_rewrite_rule('^how-to/([^/]+)/?$', 'index.php?slab_route=how_to_single&slab_slug=$matches[1]', 'top');

        // 5. Knowledgebase Hub: /knowledgebase/
        // A stable, self-contained URL for the full blog-post archive grouped
        // by category. Added so the header nav's "Articles" link and the
        // homepage's "View All Guides" link no longer depend on WordPress
        // admin's Settings > Reading > "Posts page" option (get_option('page_for_posts')),
        // which silently falls back to the /cost-guides/ hub whenever that
        // option is unset — a fragile, easy-to-misconfigure dependency for a
        // link that appears in the primary navigation on every page.
        add_rewrite_rule('^knowledgebase/?$', 'index.php?slab_route=knowledgebase_hub', 'top');

        // 6. Contractor quote checker: /concrete-quote-checker/
        // The calculator with its Bid Check tab open, plus a guide to
        // judging a concrete quote.
        add_rewrite_rule('^concrete-quote-checker/?$', 'index.php?slab_route=quote_checker', 'top');

        // 7. Slab sizes: /concrete-slab-cost-by-size/ and one page per size
        // (/12x16-concrete-slab-cost/). Only the sizes listed in
        // inc/size-pages.php match; 10x10, 12x12, 20x20 and 24x24 keep
        // their existing Page redirects to the size articles.
        add_rewrite_rule('^concrete-slab-cost-by-size/?$', 'index.php?slab_route=size_hub', 'top');
        add_rewrite_rule(Slab_Size_Pages::route_regex(), 'index.php?slab_route=size_page&slab_slug=$matches[1]', 'top');

        // 8. Tool pages without a Page row: fence posts, steps, bags vs.
        // ready-mix (inc/tool-pages.php).
        add_rewrite_rule(Slab_Tool_Pages::route_regex(), 'index.php?slab_route=tool_page&slab_slug=$matches[1]', 'top');

        // 9. State prices: /concrete-slab-cost-by-state/ and one page per
        // state that has its own notes (/texas-concrete-slab-cost/).
        add_rewrite_rule('^concrete-slab-cost-by-state/?$', 'index.php?slab_route=state_hub', 'top');
        add_rewrite_rule(Slab_State_Pages::route_regex(), 'index.php?slab_route=state_page&slab_slug=$matches[1]', 'top');

        // 10. Price report and its CSV, the embeddable calculator and its
        // info page (inc/price-report.php, inc/embed-widget.php).
        add_rewrite_rule('^concrete-price-report/?$', 'index.php?slab_route=price_report', 'top');
        add_rewrite_rule('^concrete-price-report\.csv$', 'index.php?slab_route=price_report_csv', 'top');
        add_rewrite_rule('^embed-concrete-calculator/?$', 'index.php?slab_route=embed_info', 'top');
        add_rewrite_rule('^embed/concrete-calculator/?$', 'index.php?slab_route=embed_frame', 'top');

        // Register Query Vars
        add_rewrite_tag('%slab_route%', '([^&]+)');
        add_rewrite_tag('%slab_slug%', '([^&]+)');
    }

    /**
     * ROOT-CAUSE FIX for the nested-URL soft-404 problem.
     *
     * register_clean_rewrite_rules() above registers /calculators/[slug]/,
     * /concrete/[slug]/, /cost-guides/[slug]/, and /how-to/[slug]/ as
     * high-priority ('top') rewrite rules. Every one of those "single item"
     * routes was previously handed to route_custom_templates() below, which
     * forced a generic template onto the request even though no real page or
     * post was ever matched by the main query for that nested path (every
     * calculator, cost guide, and use-case page is actually published at a
     * flat top-level URL, e.g. /concrete-volume-calculator/, /patio/, via
     * WordPress's normal page-template mechanism). With no real content
     * behind the route, WordPress's main query silently fell back to
     * whatever it defaults to when no post matches (the site's front-page
     * query), so the templates' the_title()/permalink calls printed the
     * front page's own title and canonical URL while still returning a 200
     * status — a site-wide soft-404 reachable straight from the header
     * nav's own dropdowns.
     *
     * Because the correct content already lives at a working flat URL, and
     * because these nested URLs may already be crawled or linked
     * externally, the safe fix is a permanent redirect to the flat
     * equivalent rather than deleting the route outright.
     *
     * The *hub* routes (/calculators/, /cost-guides/, /how-to/,
     * /knowledgebase/ with no slug) are a separate matter, handled in
     * route_custom_templates() and Slab_SEO_Engine::get_slab_hub_info()
     * below — they render real directory-listing templates, not a
     * redirect, but (like every slab_route request) still need explicit
     * title/canonical/description handling since WordPress's own query
     * never recognizes them as anything but a "front page" fallback.
     */
    public static function redirect_legacy_nested_urls() {
        $route = get_query_var('slab_route');
        $slug = get_query_var('slab_slug');

        if (!$route || !$slug) {
            return;
        }

        $single_routes = ['calc_single', 'concrete_app', 'cost_guide_single', 'how_to_single'];
        if (!in_array($route, $single_routes, true)) {
            return;
        }

        $flat_slug = sanitize_title($slug);

        // One nested slug needs to resolve to a different flat destination
        // than a simple prefix-strip: /calculators/concrete-cost-per-square-foot/
        // is the *calculator*, which lives at the disambiguated
        // /concrete-cost-per-square-foot-calculator/ URL (see item 4 — the
        // plain /concrete-cost-per-square-foot/ slug is the cost guide).
        if ($route === 'calc_single' && $flat_slug === 'concrete-cost-per-square-foot') {
            $flat_slug = 'concrete-cost-per-square-foot-calculator';
        }

        wp_safe_redirect(home_url('/' . trailingslashit($flat_slug)), 301);
        exit;
    }

    public static function route_custom_templates($template) {
        $route = get_query_var('slab_route');

        // Single-item nested routes are redirected in redirect_legacy_nested_urls()
        // above (which runs on template_redirect, earlier than this template_include
        // filter) and never reach this point. Only the genuine hub/listing routes
        // are handled here.
        if (!$route) {
            return $template;
        }

        // Locate appropriate theme template
        if ($route === 'calc_hub') {
            $new_template = locate_template(['page-templates/template-calculator-hub.php', 'page.php']);
            if ($new_template) return $new_template;
        }

        if ($route === 'cost_guide_hub' || $route === 'how_to_hub') {
            // BUG FIX: these two hub routes used to be handed
            // template-cost-guide.php / template-how-to.php — but those are
            // single-POST templates (Template Name: "Cost Guide Deep-Dive" /
            // "How-To Instructional Guide") that call the_title() against
            // WordPress's main query loop. Since /cost-guides/ and /how-to/
            // have no real post behind them, the main query silently fell
            // back to its default "nothing matched" state (see the
            // is_front_page() explanation in Slab_SEO_Engine), and
            // the_title()/the_content() ended up printing whatever the
            // latest blog post happened to be — a random, mismatched H1
            // sitting on top of unrelated hardcoded boilerplate content, at
            // a URL reachable straight from the main nav. Both hubs now
            // reuse the same working, tested Knowledgebase listing template,
            // pre-filtered to the categories that belong under each nav
            // label (see template-knowledgebase.php's route-aware filter).
            $new_template = locate_template(['page-templates/template-knowledgebase.php', 'page.php']);
            if ($new_template) return $new_template;
        }

        if ($route === 'quote_checker') {
            $new_template = locate_template(['page-templates/template-quote-checker.php', 'page.php']);
            if ($new_template) return $new_template;
        }

        $routed = [
            'size_hub'  => 'page-templates/template-size-hub.php',
            'size_page' => 'page-templates/template-size-page.php',
            'tool_page' => 'page-templates/template-tool-page.php',
            'state_hub' => 'page-templates/template-state-hub.php',
            'state_page' => 'page-templates/template-state-page.php',
            'price_report' => 'page-templates/template-price-report.php',
            'embed_info' => 'page-templates/template-embed-info.php',
        ];
        if (isset($routed[$route]) && Slab_SEO_Engine::get_slab_hub_info()) {
            $new_template = locate_template([$routed[$route]]);
            if ($new_template) return $new_template;
        }

        if ($route === 'knowledgebase_hub') {
            $new_template = locate_template(['page-templates/template-knowledgebase.php', 'page.php']);
            if ($new_template) return $new_template;
        }

        return $template;
    }

    /**
     * Legacy redirect for the confirmed slug-collision duplicate from item 4:
     * WordPress auto-suffixed a second page to /concrete-cost-per-square-foot-2/
     * (see the demo-content-importer.php fix for the root cause). Anyone who
     * already bookmarked, linked, or had that "-2" URL indexed is sent to the
     * correct, clean calculator URL instead of hitting a 404.
     */
    public static function redirect_duplicate_slug_urls() {
        if (!is_404()) {
            return;
        }

        $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        $legacy_redirects = [
            'concrete-cost-per-square-foot-2' => 'concrete-cost-per-square-foot-calculator',
            // The main nav dropdown, footer, and the How-To sidebar all
            // linked to /concrete-slab-cost/ as the site's general cost
            // overview — but no page or post was ever published at that
            // exact slug (only longer variants like /10x10-concrete-slab-cost/
            // exist), so it 404'd straight from primary navigation. Those
            // three links now point at the real article directly; this
            // redirect is a safety net in case the dead URL was already
            // crawled, indexed, or bookmarked externally.
            'concrete-slab-cost' => 'how-much-does-a-concrete-slab-cost',
        ];

        if (isset($legacy_redirects[$path])) {
            wp_safe_redirect(home_url('/' . trailingslashit($legacy_redirects[$path])), 301);
            exit;
        }
    }

    /**
     * KEYWORD-CANNIBALIZATION / DUPLICATE-CONTENT FIX.
     *
     * Six real, live wp-admin Pages (10x10/12x12/20x20/24x24-concrete-slab-cost,
     * concrete-cost-per-square-foot, concrete-cost-per-cubic-yard) all use the
     * "Cost Guide Deep-Dive" page template (page-templates/template-cost-guide.php).
     * That template hardcodes its entire body — the intro paragraph, the pricing
     * table, the "Material vs. Labor" breakdown, the "Regional Pricing
     * Variations" section — as static text with only the_title() changing per
     * page. The result: six different URLs on the live site publish
     * byte-for-byte identical body content, a serious duplicate-content
     * problem on its own. Each of those six pages also targets the exact same
     * keyword as one of the 30 imported blog posts (e.g. /10x10-concrete-slab-cost/
     * vs. /10x10-concrete-slab-cost-breakdown/), which is the keyword
     * cannibalization this was flagged for — except the blog post has unique,
     * substantive content and the page has none.
     *
     * Since this template's content isn't tied to the page in any way that a
     * code fix could safely make unique per page without guessing at intent
     * the site owner may have had for these specific pages, and since a
     * genuinely unique, better article already exists at each equivalent
     * topic, the safe fix — same pattern as the other legacy redirects above —
     * is a 301 to the real article. This is unconditional (not gated on
     * is_404 like redirect_duplicate_slug_urls() above) because these are
     * real, resolving pages, not broken links.
     */
    public static function redirect_duplicate_topic_pages() {
        if (is_404() || is_admin()) {
            return;
        }

        $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        $topic_redirects = [
            '10x10-concrete-slab-cost'       => '10x10-concrete-slab-cost-breakdown',
            '12x12-concrete-slab-cost'       => '12x12-concrete-slab-cost-breakdown',
            '20x20-concrete-slab-cost'       => '20x20-concrete-slab-cost-breakdown',
            '24x24-concrete-slab-cost'       => '24x24-concrete-slab-cost-breakdown',
            'concrete-cost-per-square-foot'  => 'concrete-cost-per-square-foot-pricing-matrix',
            'concrete-cost-per-cubic-yard'   => 'concrete-cost-per-cubic-yard-pricing-explained',
        ];

        if (isset($topic_redirects[$path]) && is_page($path)) {
            wp_safe_redirect(home_url('/' . trailingslashit($topic_redirects[$path])), 301);
            exit;
        }
    }
}

Slab_Architecture_Router::init();
