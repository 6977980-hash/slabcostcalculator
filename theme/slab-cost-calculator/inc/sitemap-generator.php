<?php
/**
 * DYNAMIC XML SITEMAP GENERATOR
 * Serves an optimized sitemap.xml indexing all calculators, cost guides, and authoritative pages.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_Sitemap_Generator {

    public static function init() {
        add_action('init', [__CLASS__, 'register_rewrite']);
        add_action('template_redirect', [__CLASS__, 'render_sitemap']);
        add_filter('wp_sitemaps_posts_query_args', [__CLASS__, 'exclude_redirected_pages_from_core_sitemap'], 10, 2);
    }

    /**
     * SECOND, SEPARATE SITEMAP FIX: render_sitemap() above already excludes
     * the 6 redirected duplicate-content Pages from OUR custom /sitemap.xml
     * (see $redirected_page_slugs below). But WordPress core (5.5+) also
     * generates its own, completely independent native sitemap at
     * /wp-sitemap.xml -> /wp-sitemap-posts-page-1.xml, which this theme
     * never touches and which core builds straight from "every published
     * Page", with no awareness of Slab_Architecture_Router's redirects.
     * Confirmed live: all 6 old duplicate URLs -- which correctly 301 to
     * their blog-post equivalents when visited -- were still listed in
     * that core sitemap. An SEO crawler or Search Console checking
     * /wp-sitemap.xml (not our custom one) would flag them as "submitted
     * URL redirected" duplicates. Filtering them out of the same
     * wp_sitemaps_posts_query_args() WordPress uses to build that file
     * fixes it at the source instead of adding a third sitemap to maintain.
     */
    public static function exclude_redirected_pages_from_core_sitemap($args, $post_type) {
        if ($post_type !== 'page') {
            return $args;
        }

        $redirected_page_slugs = [
            '10x10-concrete-slab-cost', '12x12-concrete-slab-cost',
            '20x20-concrete-slab-cost', '24x24-concrete-slab-cost',
            'concrete-cost-per-square-foot', 'concrete-cost-per-cubic-yard',
        ];

        $exclude_ids = [];
        foreach ($redirected_page_slugs as $slug) {
            $p = get_page_by_path($slug, OBJECT, 'page');
            if ($p) {
                $exclude_ids[] = $p->ID;
            }
        }

        if (!empty($exclude_ids)) {
            $existing = isset($args['post__not_in']) && is_array($args['post__not_in']) ? $args['post__not_in'] : [];
            $args['post__not_in'] = array_merge($existing, $exclude_ids);
        }

        return $args;
    }

    public static function register_rewrite() {
        add_rewrite_rule('^sitemap\.xml$', 'index.php?slab_sitemap=1', 'top');
        add_rewrite_tag('%slab_sitemap%', '1');
    }

    public static function render_sitemap() {
        if (get_query_var('slab_sitemap') !== '1') {
            return;
        }

        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex, follow');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // 1. Homepage / Primary Calculator
        self::render_url_node(home_url('/'), date('Y-m-d'), 'daily', '1.0');

        // 1b. Virtual hub/listing routes (Slab_Architecture_Router). These are
        // real, internally-linked, self-contained directory pages — reachable
        // from the primary nav on every page — but they are rendered via a
        // custom rewrite rule + template_include, not a real page_type=page
        // row in the database, so the "All Published Pages" query below can
        // never discover them. Without this, four of the site's most-linked
        // pages (including the new Knowledgebase hub) would be entirely
        // absent from the sitemap.
        $hub_routes = ['calculators', 'cost-guides', 'how-to', 'knowledgebase'];
        foreach ($hub_routes as $hub_slug) {
            self::render_url_node(home_url('/' . $hub_slug . '/'), date('Y-m-d'), 'weekly', '0.9');
        }

        // 2. All Published Pages
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ]);

        // Pages that Slab_Architecture_Router::redirect_duplicate_topic_pages()
        // now 301s to a better-matching blog post (duplicate-content /
        // keyword-cannibalization fix — see that method's comment). Excluded
        // here so the sitemap only ever lists final, non-redirecting URLs
        // instead of handing crawlers a "submitted URL redirected" warning.
        $redirected_page_slugs = [
            '10x10-concrete-slab-cost', '12x12-concrete-slab-cost',
            '20x20-concrete-slab-cost', '24x24-concrete-slab-cost',
            'concrete-cost-per-square-foot', 'concrete-cost-per-cubic-yard',
            'concrete-slab-cost',
        ];

        foreach ($pages as $p) {
            if ($p->ID == get_option('page_on_front')) continue;
            if (in_array($p->post_name, $redirected_page_slugs, true)) continue;
            $url = get_permalink($p->ID);
            $mod = get_the_modified_date('Y-m-d', $p->ID);
            $priority = '0.8';
            if (strpos($url, '/calculators/') !== false) $priority = '0.9';
            if (strpos($url, '/cost-guides/') !== false) $priority = '0.85';
            self::render_url_node($url, $mod, 'weekly', $priority);
        }

        // 3. Published Blog Posts & Guides
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1
        ]);

        foreach ($posts as $post) {
            $url = get_permalink($post->ID);
            $mod = get_the_modified_date('Y-m-d', $post->ID);
            self::render_url_node($url, $mod, 'monthly', '0.7');
        }

        echo '</urlset>' . "\n";
        exit;
    }

    private static function render_url_node($loc, $lastmod, $changefreq, $priority) {
        echo "  <url>\n";
        echo "    <loc>" . esc_url($loc) . "</loc>\n";
        echo "    <lastmod>" . esc_html($lastmod) . "</lastmod>\n";
        echo "    <changefreq>" . esc_html($changefreq) . "</changefreq>\n";
        echo "    <priority>" . esc_html($priority) . "</priority>\n";
        echo "  </url>\n";
    }
}

Slab_Sitemap_Generator::init();
