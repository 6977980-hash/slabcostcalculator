<?php
/**
 * XML SITEMAP
 *
 * The site has one sitemap: WordPress core's /wp-sitemap.xml, which
 * robots.txt already lists. The theme used to serve a second one at
 * /sitemap.xml that disagreed with it (hubs listed twice, lastmod always
 * "today", no robots.txt entry). Now:
 *  - the core sitemap leaves out the Pages that 301 elsewhere,
 *  - it gains a small "hubs" sitemap for /knowledgebase/, the one listing
 *    page that has no Page row for core to find,
 *  - /sitemap.xml 301s to /wp-sitemap.xml for anyone who submitted it.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_Sitemap_Generator {

    public static function init() {
        add_action('init', [__CLASS__, 'register_rewrite']);
        add_action('init', [__CLASS__, 'register_hub_provider']);
        add_action('template_redirect', [__CLASS__, 'redirect_legacy_sitemap'], 0);
        add_filter('wp_sitemaps_posts_query_args', [__CLASS__, 'exclude_redirected_pages_from_core_sitemap'], 10, 2);
    }

    /**
     * Pages that Slab_Architecture_Router 301s to a blog post. Listing them
     * would hand crawlers "submitted URL redirected" warnings.
     */
    public static function exclude_redirected_pages_from_core_sitemap($args, $post_type) {
        if ($post_type !== 'page') {
            return $args;
        }

        $redirected_page_slugs = [
            '10x10-concrete-slab-cost', '12x12-concrete-slab-cost',
            '20x20-concrete-slab-cost', '24x24-concrete-slab-cost',
            'concrete-cost-per-square-foot', 'concrete-cost-per-cubic-yard',
            'concrete-slab-cost',
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

    public static function redirect_legacy_sitemap() {
        if (get_query_var('slab_sitemap') !== '1') {
            return;
        }
        wp_safe_redirect(home_url('/wp-sitemap.xml'), 301);
        exit;
    }

    public static function register_hub_provider() {
        if (!function_exists('wp_register_sitemap_provider') || !class_exists('WP_Sitemaps_Provider')) {
            return;
        }
        wp_register_sitemap_provider('hubs', new Slab_Hub_Sitemap_Provider());
    }
}

if (class_exists('WP_Sitemaps_Provider')) {
    /**
     * /wp-sitemap-hubs-1.xml: pages served by a rewrite rule rather than a
     * Page: /knowledgebase/, the quote checker, the slab size pages and the
     * routed tools. /calculators/, /cost-guides/ and /how-to/ also exist as
     * Pages, so core already lists them.
     */
    class Slab_Hub_Sitemap_Provider extends WP_Sitemaps_Provider {

        public function __construct() {
            $this->name        = 'hubs';
            $this->object_type = 'hubs';
        }

        public function get_url_list($page_num, $object_subtype = '') {
            if ((int) $page_num !== 1) {
                return [];
            }
            $entry = ['loc' => home_url('/knowledgebase/')];
            // The knowledgebase lists every post, so it changes when the
            // newest post changes.
            $last = get_lastpostmodified('GMT', 'post');
            if ($last) {
                $entry['lastmod'] = gmdate('c', strtotime($last . ' UTC'));
            }
            $quote = [
                'loc'     => home_url('/concrete-quote-checker/'),
                'lastmod' => gmdate('c', strtotime(SLAB_CONTENT_UPDATED . ' 12:00:00 UTC')),
            ];
            $urls = [$entry, $quote];
            // Phase 6: slab size pages, the size hub and the routed tools.
            $paths = [Slab_Size_Pages::hub_path()];
            foreach (array_keys(Slab_Size_Pages::sizes()) as $size) {
                $paths[] = Slab_Size_Pages::path($size);
            }
            foreach (array_keys(Slab_Tool_Pages::directory()) as $slug) {
                $paths[] = '/' . $slug . '/';
            }
            // Phase 7: price report, state prices, embed page. The CSV and
            // the iframe page are not listed (noindex).
            $paths[] = '/concrete-price-report/';
            $paths[] = Slab_State_Pages::hub_path();
            foreach (array_keys(Slab_State_Pages::states()) as $slug) {
                $paths[] = Slab_State_Pages::path($slug);
            }
            $paths[] = Slab_Embed_Widget::info_path();
            foreach ($paths as $path) {
                $urls[] = ['loc' => home_url($path), 'lastmod' => $quote['lastmod']];
            }
            return $urls;
        }

        public function get_max_num_pages($object_subtype = '') {
            return 1;
        }
    }
}

Slab_Sitemap_Generator::init();
