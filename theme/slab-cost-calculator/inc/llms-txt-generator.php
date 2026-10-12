<?php
/**
 * LLMS.TXT
 *
 * /llms.txt      Short index for AI assistants: what the site is, key facts
 *                they can quote, and every canonical page with one line.
 * /llms-full.txt The same, plus the full plain text of every article.
 *
 * Both are served at the exact path with no trailing-slash redirect (see
 * Slab_SEO_Engine::keep_file_urls_unslashed()). Page lists are built from
 * the database, so new posts and pages appear without editing this file.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_LLMS_Generator {

    /**
     * Pages left out of the lists: the six that 301 to an article, and three
     * calculator pages that currently render a different tool than their
     * name (audit H4; to be built or removed in a later phase).
     */
    const SKIP_SLUGS = [
        '10x10-concrete-slab-cost', '12x12-concrete-slab-cost',
        '20x20-concrete-slab-cost', '24x24-concrete-slab-cost',
        'concrete-cost-per-square-foot', 'concrete-cost-per-cubic-yard',
        'concrete-mix-calculator', 'concrete-wire-mesh-calculator', 'concrete-form-calculator',
        'privacy-policy', 'terms',
    ];

    public static function init() {
        add_action('init', [__CLASS__, 'register_rewrite']);
        add_action('init', [__CLASS__, 'maybe_flush_rules'], 30);
        add_action('template_redirect', [__CLASS__, 'render'], 0);
    }

    public static function register_rewrite() {
        add_rewrite_rule('^llms\.txt$', 'index.php?slab_llms=1', 'top');
        add_rewrite_rule('^llms-full\.txt$', 'index.php?slab_llms=full', 'top');
        add_rewrite_tag('%slab_llms%', '(1|full)');
    }

    /** The llms-full.txt rule is new, so flush rewrite rules once. */
    public static function maybe_flush_rules() {
        $marker = 'llms-full-v1';
        if (get_option('slab_llms_rules_version') !== $marker) {
            flush_rewrite_rules(false);
            update_option('slab_llms_rules_version', $marker);
        }
    }

    public static function render() {
        $mode = get_query_var('slab_llms');
        if ($mode !== '1' && $mode !== 'full') {
            return;
        }

        status_header(200);
        header('Content-Type: text/plain; charset=utf-8');
        header('X-Robots-Tag: noindex, follow');

        echo self::build_index();
        if ($mode === 'full') {
            echo self::build_articles_full_text();
        }
        exit;
    }

    private static function plain($text) {
        $text = html_entity_decode(wp_strip_all_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    private static function line($title, $url, $summary = '') {
        $out = '- [' . self::plain($title) . '](' . $url . ')';
        if ($summary !== '') {
            $out .= ': ' . self::plain($summary);
        }
        return $out . "\n";
    }

    private static function pages_with_template($template) {
        $pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby'     => 'menu_order title',
            'order'       => 'ASC',
            'meta_key'    => '_wp_page_template',
            'meta_value'  => $template,
        ]);
        return array_values(array_filter($pages, function ($p) {
            return !in_array($p->post_name, self::SKIP_SLUGS, true);
        }));
    }

    private static function post_summary($post) {
        $desc = get_post_meta($post->ID, '_slab_meta_description', true);
        if (!$desc) {
            $desc = $post->post_excerpt;
        }
        if (!$desc) {
            $desc = wp_trim_words(wp_strip_all_tags(strip_shortcodes($post->post_content)), 28, '...');
        }
        return $desc;
    }

    private static function build_index() {
        $home = home_url('/');
        $updated = wp_date('F j, Y', strtotime(SLAB_CONTENT_UPDATED . ' 12:00:00'));
        $o = '';

        $o .= "# Slab Cost Calculator\n\n";
        $o .= "> Free concrete slab cost calculator for U.S. homeowners and contractors: concrete volume (cubic yards and m3), bag counts, ready-mix vs bagged cost, gravel base, rebar or mesh, forms, labor and a line-item total, plus cost guides and how-to articles.\n\n";
        $o .= "Site: {$home}\n";
        $o .= "Contact: contact@slabcostcalculator.com\n";
        $o .= "Prices last reviewed: {$updated}\n";
        $o .= "Full text of all articles: " . home_url('/llms-full.txt') . "\n\n";

        $o .= "## Key facts\n\n";
        $o .= "- 1 cubic yard = 27 cubic feet.\n";
        $o .= "- Slab volume (cubic yards) = length (ft) x width (ft) x thickness (in) / 12 / 27.\n";
        $o .= "- One 80-lb bag of pre-mixed concrete yields about 0.60 cubic feet, so 45 bags make 1 cubic yard. A 60-lb bag yields about 0.45 cubic feet and a 40-lb bag about 0.30 cubic feet.\n";
        $o .= "- A 10x10 ft slab, 4 inches thick, needs 1.23 cubic yards, or 1.36 cubic yards with 10% waste: 62 bags of 80-lb mix.\n";
        $o .= "- A 20x20 ft slab, 4 inches thick, needs 4.94 cubic yards, or 5.43 cubic yards with 10% waste.\n";
        $o .= "- Control joints: space them 2 to 3 times the slab thickness in inches, in feet (a 4-inch slab gets joints every 8 to 10 ft, never more than 12 ft), cut one quarter of the slab depth.\n";
        $o .= "- New slab: foot traffic after 24 to 48 hours, cars after about 7 days, full design strength at 28 days.\n";
        $o .= "- The calculator's line items, formulas and price assumptions are documented at " . home_url('/methodology/') . "\n\n";

        $o .= "## Main calculator\n\n";
        $o .= self::line('Concrete Slab Cost Calculator', $home, 'Full slab estimate: volume, bags or ready-mix, site prep, gravel, reinforcement, forms, finish, labor, delivery and pump, with a regional price index, slab blueprint, contractor bid checker and pour-day weather check.');
        $o .= "\n";

        $calcs = self::pages_with_template('page-templates/template-calculator-single.php');
        if ($calcs) {
            $o .= "## Calculators\n\n";
            $o .= self::line('All calculators', home_url('/calculators/'));
            foreach ($calcs as $p) {
                $o .= self::line(get_the_title($p), get_permalink($p));
            }
            $o .= "\n";
        }

        $apps = self::pages_with_template('page-templates/template-hub.php');
        if ($apps) {
            $o .= "## Slab cost by project type\n\n";
            foreach ($apps as $p) {
                $o .= self::line(get_the_title($p), get_permalink($p));
            }
            $o .= "\n";
        }

        $posts = get_posts([
            'post_type'   => 'post',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby'     => 'title',
            'order'       => 'ASC',
        ]);
        if ($posts) {
            $by_cat = [];
            foreach ($posts as $p) {
                $cats = get_the_category($p->ID);
                $name = $cats ? self::plain($cats[0]->name) : 'Articles';
                $by_cat[$name][] = $p;
            }
            ksort($by_cat);
            $o .= "## Articles\n\n";
            $o .= self::line('Knowledgebase (all articles)', home_url('/knowledgebase/'));
            $o .= self::line('Cost guides', home_url('/cost-guides/'));
            $o .= self::line('How-to guides', home_url('/how-to/'));
            $o .= "\n";
            foreach ($by_cat as $name => $items) {
                $o .= "### {$name}\n\n";
                foreach ($items as $p) {
                    $o .= self::line(get_the_title($p), get_permalink($p), self::post_summary($p));
                }
                $o .= "\n";
            }
        }

        $o .= "## About this site\n\n";
        $o .= self::line('Methodology and formulas', home_url('/methodology/'), 'How every number is calculated, and where prices come from.');
        $o .= self::line('Editorial policy', home_url('/editorial-policy/'), 'How content and prices are reviewed and updated.');
        $o .= self::line('Authors', home_url('/authors/'));
        $o .= self::line('About', home_url('/about/'));
        $o .= self::line('Contact', home_url('/contact/'));
        $o .= self::line('Disclaimer', home_url('/disclaimer/'), 'Estimates are for planning; structural work needs a licensed engineer and local code approval.');
        return $o;
    }

    private static function build_articles_full_text() {
        $posts = get_posts([
            'post_type'   => 'post',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby'     => 'title',
            'order'       => 'ASC',
        ]);
        $o = "\n---\n\n# Full text of all articles\n";
        foreach ($posts as $p) {
            $html = strip_shortcodes($p->post_content);
            // Keep paragraph and list breaks before stripping tags.
            $html = preg_replace('#<(h[1-6])[^>]*>(.*?)</\1>#is', "\n\n## $2\n\n", $html);
            $html = preg_replace('#<li[^>]*>#i', "\n- ", $html);
            $html = preg_replace('#</(p|div|ul|ol|table|tr)>|<br\s*/?>#i', "\n", $html);
            $html = preg_replace('#</t[dh]>#i', ' | ', $html);
            $text = html_entity_decode(wp_strip_all_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $text = preg_replace("/[ \t]+/", ' ', $text);
            $text = trim(preg_replace("/\n\s*\n\s*\n+/", "\n\n", $text));

            $o .= "\n## " . self::plain(get_the_title($p)) . "\n\n";
            $o .= 'URL: ' . get_permalink($p) . "\n";
            $o .= 'Last updated: ' . get_the_modified_date('F j, Y', $p) . "\n\n";
            $o .= $text . "\n";
        }
        return $o;
    }
}

Slab_LLMS_Generator::init();
