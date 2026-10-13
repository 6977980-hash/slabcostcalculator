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
     * Pages left out of the lists: the six that 301 to an article. (The mix,
     * wire mesh and form calculators were left out until Phase 5 gave them
     * their own tools; they are listed again.)
     */
    const SKIP_SLUGS = [
        '10x10-concrete-slab-cost', '12x12-concrete-slab-cost',
        '20x20-concrete-slab-cost', '24x24-concrete-slab-cost',
        'concrete-cost-per-square-foot', 'concrete-cost-per-cubic-yard',
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
        $desc = Slab_Post_Figures::fix($post->post_name, $desc);
        if (!$desc) {
            $desc = wp_trim_words(wp_strip_all_tags(strip_shortcodes($post->post_content)), 28, '...');
        }
        return $desc;
    }

    /** Quotable facts, all from Slab_Data (inc/site-data.php). */
    private static function key_facts() {
        $sd = Slab_Data::all();
        $y = $sd['concrete']['bag_yields'];
        $f10 = Slab_Data::size_fact(10, 10);
        $f20 = Slab_Data::size_fact(20, 20);
        $psf = Slab_Data::installed_psf();
        $diy = Slab_Data::diy_psf();
        $j = $sd['joints'];
        $w = $sd['weather'];
        $cure = $sd['curing'];
        $rm_range = [Slab_Data::psi_price(3000)[0], Slab_Data::psi_price(4000)[1]];

        return [
            '1 cubic yard = 27 cubic feet.',
            'Slab volume (cubic yards) = length (ft) x width (ft) x thickness (in) / 12 / 27. Add ' . $sd['concrete']['waste_pct'] . '% for waste.',
            sprintf('One 80-lb bag of pre-mixed concrete yields about %s cubic feet, so %s bags make 1 cubic yard. A 60-lb bag yields about %s cubic feet and a 40-lb bag about %s cubic feet.', number_format($y['80'], 2), Slab_Data::num(27 / $y['80']), number_format($y['60'], 2), number_format($y['40'], 2)),
            sprintf('A 10x10 ft slab, 4 inches thick, needs %s cubic yards, or %s cubic yards with %d%% waste: %d bags of 80-lb mix (%s at %s a bag). Installed cost: %s. DIY materials: %s.', $f10['net_yd'], $f10['order_yd'], $sd['concrete']['waste_pct'], $f10['bags80'], Slab_Data::money($f10['bags80'] * $sd['concrete']['bag_price_default'], 0), Slab_Data::money($sd['concrete']['bag_price_default'], 2), $f10['installed'], $f10['diy']),
            sprintf('A 20x20 ft slab, 4 inches thick, needs %s cubic yards, or %s cubic yards with %d%% waste. Installed cost: %s. DIY materials: %s.', $f20['net_yd'], $f20['order_yd'], $sd['concrete']['waste_pct'], $f20['installed'], $f20['diy']),
            sprintf('Installed concrete slab cost: about %s per sq ft for a 20x20 ft, 4-inch slab; small slabs cost more per sq ft. DIY materials: about %s per sq ft. Concrete driveway (5-inch): %s per sq ft. Garage slab (5-inch): %s per sq ft.', Slab_Data::money_range($psf, 0, ' to '), Slab_Data::money_range($diy, 2, ' to '), Slab_Data::money_range(Slab_Data::driveway_psf(), 0, ' to '), Slab_Data::money_range(Slab_Data::garage_psf(), 0, ' to ')),
            sprintf('Ready-mix concrete: %s per cubic yard for 3,000 to 4,000 PSI (calculator default %s for 3,000 PSI); each 500-PSI step adds about %s per cubic yard. Bagged concrete: %s per 80-lb bag (about %s per cubic yard at %s).', Slab_Data::money_range($rm_range, 0, ' to '), Slab_Data::money($sd['concrete']['ready_mix_default']), Slab_Data::mr('concrete.psi_step_price', 0, ' to '), Slab_Data::mr('concrete.bag_price_range', 2, ' to '), Slab_Data::money($sd['concrete']['bag_price_default'] / $y['80'] * 27, 0), Slab_Data::money($sd['concrete']['bag_price_default'], 2)),
            sprintf('Short-load fee: %s per load (or %s per yard short) below the plant minimum of %s cubic yards. Concrete pump: %s per pour.', Slab_Data::mr('delivery.short_load_flat', 0, ' to '), Slab_Data::mr('delivery.short_load_per_yd', 0, ' to '), Slab_Data::r('delivery.min_load_yd'), Slab_Data::mr('pump.typical', 0, ' to ')),
            sprintf('Labor: %s per sq ft to place and finish, or about %s per hour for a %s person crew.', Slab_Data::mr('labor.sqft_range', 2, ' to '), Slab_Data::mr('labor.crew_hour', 0, ' to '), Slab_Data::r('labor.crew_size', '-')),
            sprintf('Control joints: space them %s times the slab thickness in inches, in feet (a 4-inch slab gets joints every %s ft, never more than %d ft), cut one quarter of the slab depth, %s hours after finishing (%s hours with an early-entry saw).', Slab_Data::range($j['ratio']), Slab_Data::range([4 * $j['ratio_best'][0], 4 * $j['ratio_best'][1]]), $j['max_ft'][4], Slab_Data::range($j['sawcut_h']), Slab_Data::range($j['early_entry_h'])),
            sprintf('Rebar lap splice: %d bar diameters (%d inches for #4).', $sd['reinforcement']['lap_bar_diameters'], $sd['reinforcement']['lap_in_no4']),
            sprintf('Weather: cold-weather practice at or below %d°F (ACI 306R); hot-weather precautions above %d°F air temperature (ACI 305R).', $w['cold_f'], $w['hot_f']),
            sprintf('Permits for small residential slabs and driveways: commonly %s.', Slab_Data::mr('permit.flatwork', 0, ' to ')),
            sprintf('New slab: foot traffic after %s hours, cars after about %d days, design strength at %d days.', Slab_Data::range($cure['foot_traffic_h']), $cure['cars_days'], $cure['full_days']),
            'The calculator\'s line items, formulas and price assumptions are documented at ' . home_url('/methodology/'),
        ];
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
        foreach (self::key_facts() as $fact) {
            $o .= '- ' . $fact . "\n";
        }
        $o .= "\n";

        $o .= "## Main calculator\n\n";
        $o .= self::line('Concrete Slab Cost Calculator', $home, 'Full slab estimate: volume, bags or ready-mix, site prep, gravel, reinforcement, forms, finish, labor, delivery and pump, with a regional price index, slab blueprint, contractor bid checker and pour-day weather check.');
        $o .= self::line('Concrete Quote Checker', home_url('/concrete-quote-checker/'), 'Compare a contractor\'s concrete quote with a scope-matched benchmark (materials at your prices plus standard regional labor) and see whether it is below cost, fair, premium or overpriced.');
        $o .= "\n";

        $calcs = self::pages_with_template('page-templates/template-calculator-single.php');
        $o .= "## Calculators\n\n";
        $o .= self::line('All calculators', home_url('/calculators/'));
        foreach ($calcs as $p) {
            $o .= self::line(get_the_title($p), get_permalink($p));
        }
        foreach (Slab_Tool_Pages::directory() as $slug => $info) {
            $o .= self::line($info[1], home_url('/' . $slug . '/'), $info[2]);
        }
        $o .= "\n";

        $o .= "## Slab cost by size\n\n";
        $o .= self::line('Concrete slab cost by size (all sizes)', home_url(Slab_Size_Pages::hub_path()), 'Yards, bags and installed price for every size below.');
        foreach (Slab_Size_Pages::hub_rows() as $r) {
            $o .= self::line($r['size'] . ' concrete slab', home_url($r['url']), sprintf('%s sq ft at %d in: %s yd³ with waste or %s bags of 80-lb mix; %s installed.', number_format($r['area']), $r['t'], number_format($r['order_yd'], 2), number_format($r['bags80']), Slab_Data::money_range($r['installed'], 0, ' to ')));
        }
        $o .= "\n";

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
            $html = Slab_Post_Figures::fix($p->post_name, strip_shortcodes($p->post_content));
            // Stub posts are shown with the theme's full article (inc/post-articles.php).
            $html = class_exists('Slab_Post_Articles') ? Slab_Post_Articles::body($p, $html) : $html;
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
