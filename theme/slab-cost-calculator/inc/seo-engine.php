<?php
/**
 * SEO ENGINE & METADATA ARCHITECTURE
 * Generates automated, standards-compliant canonical tags, Open Graph, and Twitter metadata.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_SEO_Engine {

    public static function init() {
        add_action('wp_head', [__CLASS__, 'render_meta_tags'], 1);
        add_filter('document_title_parts', [__CLASS__, 'custom_title_parts']);
        add_filter('document_title_separator', [__CLASS__, 'custom_title_separator']);
        // WordPress core (5.7+) independently echoes its own
        // <meta name="robots" content="max-image-preview:large"> via
        // wp_robots() on wp_head, separate from and in addition to the
        // single, complete robots tag render_meta_tags() below already
        // outputs (which includes index/follow explicitly). The result was
        // two different <meta name="robots"> elements on every page — not
        // harmful (neither is restrictive), but exactly the kind of
        // ambiguous duplicate tag an SEO audit flags, and a page-speed/DOM
        // no-op to remove. We own robots output ourselves, so drop core's.
        //
        // BUG FIX (found on live re-check): remove_action()'s $priority
        // argument defaults to 10, but WordPress core registers wp_robots
        // at priority 1 (wp-includes/default-filters.php:
        // add_action('wp_head', 'wp_robots', 1)). remove_action() only
        // unhooks a callback registered at the EXACT priority given, so the
        // earlier version of this call (with no priority) silently failed
        // to match and the duplicate tag stayed live. Must pass 1 here.
        remove_action('wp_head', 'wp_robots', 1);
    }

    public static function custom_title_separator($sep) {
        return '–';
    }

    /**
     * The four hub-style "virtual" routes (/calculators/, /cost-guides/,
     * /how-to/, /knowledgebase/) are matched entirely through
     * Slab_Architecture_Router's add_rewrite_rule()+template_include, not
     * through any post/page/category WordPress itself recognizes. Because
     * `slab_route` isn't a condition WP_Query's parse_query() knows about,
     * a request for one of these URLs matches none of is_singular(),
     * is_page(), is_category(), etc. — so WP_Query falls back to its
     * default "nothing matched" state, which (with the default
     * show_on_front=posts setting) makes is_home() AND is_front_page()
     * both evaluate true. Every method below previously branched on
     * is_front_page() first, so all four hub pages silently inherited the
     * homepage's title, canonical URL, and meta description — identical,
     * duplicate <title>/canonical tags reachable straight from the main
     * nav. Checking get_query_var('slab_route') first (before any of the
     * is_*() conditionals) fixes this for all three metadata methods.
     */
    private static function get_slab_hub_info() {
        $route = get_query_var('slab_route');
        $hubs = [
            'calc_hub' => [
                'path'        => '/calculators/',
                'title'       => 'Concrete Calculators — Volume, Bags, Rebar & Cost Tools',
                'description' => 'Browse our full suite of concrete and flatwork calculators — volume, bags, rebar, gravel, labor, and total project cost — for contractors and DIY builders.',
            ],
            'cost_guide_hub' => [
                'path'        => '/cost-guides/',
                'title'       => 'Concrete Slab Cost Guides — 2026 Pricing by Project Size',
                'description' => 'Concrete slab cost guides by size and project type — per-square-foot pricing, common slab dimensions, and cubic-yard rates, with real 2026 material and labor numbers.',
            ],
            'how_to_hub' => [
                'path'        => '/how-to/',
                'title'       => 'How to Pour, Cure & Repair a Concrete Slab — Guides',
                'description' => 'Step-by-step concrete how-to guides covering pouring, curing, crack prevention, sealing, and repair, aligned with ACI 302.1R standards for a durable slab.',
            ],
            'knowledgebase_hub' => [
                'path'        => '/knowledgebase/',
                'title'       => 'Concrete Knowledgebase — All Slab Cost Guides & Articles',
                'description' => 'The full Slab Cost Calculator knowledgebase — 30 in-depth articles on concrete costs, construction, estimating, materials, and maintenance, organized by topic.',
            ],
        ];
        return isset($hubs[$route]) ? $hubs[$route] : null;
    }

    public static function custom_title_parts($title) {
        $hub = self::get_slab_hub_info();
        if ($hub) {
            // BUG FIX (found on live re-check): this branch only ever set
            // $title['title'], leaving $title['tagline'] and $title['site']
            // untouched — so document_title_separator still glued the site
            // tagline onto the end (e.g. "Concrete Knowledgebase — All Slab
            // Cost Guides & Articles – Estimate Concrete, Labor & Total
            // Slab Cost", 101 characters). These hub titles are already
            // written to their own ~55-60 character budget, same as the
            // front-page title below, so they need the same tagline/site
            // clearing to actually land at that length.
            $title['title'] = $hub['title'];
            $title['tagline'] = '';
            $title['site'] = '';
            return $title;
        }

        if (is_front_page()) {
            // Previously: title + tagline + site ("Concrete Slab Cost
            // Calculator – Estimate Concrete, Labor & Total Cost – Slab Cost
            // Calculator") — 92 characters and repeated the brand name at
            // both ends, well past Google's ~60-character/~600px truncation
            // point. Collapsed into a single ~58-character string with the
            // core keyword phrase once, and the other parts cleared so
            // wp_get_document_title() doesn't re-append them.
            $title['title'] = 'Concrete Slab Cost Calculator — Estimate Materials & Labor';
            $title['tagline'] = '';
            $title['site'] = '';
        }

        // The 30 imported blog posts carry full, keyword-rich headlines as
        // their own post_title (several run 60-85 characters on their own).
        // WordPress's default document-title behavior appends
        // " – Slab Cost Calculator" (~23 more characters) to every singular
        // post regardless, which pushed a number of these well past
        // Google's ~60-character/~600px SERP truncation point — up to
        // 110+ characters total, the exact issue the homepage title fix
        // above already addressed once. Only drop the site-name suffix
        // when the post's own title is already long enough that adding it
        // would blow the budget further; a short post title still gets the
        // normal "Title – Slab Cost Calculator" treatment.
        if (is_singular('post') && !empty($title['title']) && strlen($title['title']) >= 50) {
            $title['tagline'] = '';
            $title['site'] = '';
        }

        return $title;
    }

    public static function get_meta_description() {
        $hub = self::get_slab_hub_info();
        if ($hub) {
            return esc_attr($hub['description']);
        }

        if (is_front_page()) {
            return 'Use our concrete slab cost calculator to estimate concrete volume, bags, ready-mix, labor, materials, and total slab cost. Supports imperial and metric units.';
        }

        if (is_singular()) {
            global $post;
            $custom_desc = get_post_meta($post->ID, '_slab_meta_description', true);
            if (!empty($custom_desc)) {
                return esc_attr($custom_desc);
            }

            // ROOT CAUSE of the site-wide placeholder meta description: every
            // calculator, cost-guide, and use-case page's *visible* content is
            // rendered entirely by PHP in a page-template (see
            // page-templates/template-calculator-single.php etc.) — the
            // page's own post_content field in the database is never
            // displayed on the front end. Whoever built these pages in
            // wp-admin left the literal reminder text "Content automatically
            // rendered by the specialized template." in that unused content
            // field, and the old fallback below (`wp_trim_words(...post_content...)`)
            // dutifully trimmed and echoed that literal placeholder as the
            // meta description on every one of them. Detect that placeholder
            // (and equally, an empty/too-short post_content, which has the
            // same underlying cause) and use a real, per-page generated
            // description instead of ever falling through to raw post_content.
            $has_dynamic_template = is_page_template([
                'page-templates/template-calculator-single.php',
                'page-templates/template-calculator-hub.php',
                'page-templates/template-cost-guide.php',
                'page-templates/template-hub.php',
                'page-templates/template-how-to.php',
            ]);

            $raw_content = wp_strip_all_tags((string) $post->post_content);
            $looks_like_placeholder = (stripos($raw_content, 'automatically rendered') !== false)
                || (strlen(trim($raw_content)) < 40);

            if ($has_dynamic_template || $looks_like_placeholder) {
                $generated = self::generate_template_description($post);
                if (!empty($generated)) {
                    return esc_attr($generated);
                }
            }

            if (!empty($post->post_excerpt)) {
                return esc_attr(wp_strip_all_tags($post->post_excerpt));
            }
            if (!$looks_like_placeholder && !empty($raw_content)) {
                return esc_attr(wp_trim_words($raw_content, 26, '...'));
            }

            // Last-resort fallback so a page can never again ship the literal
            // dev/placeholder string — use the same generator with only the
            // page title to work from.
            return esc_attr(self::generate_template_description($post));
        }

        return 'Accurate concrete slab calculations, volume estimating, bagged vs ready-mix comparisons, and cost guides for contractors and homeowners.';
    }

    /**
     * Builds a real, unique, ~150-160 character meta description for the
     * template-driven pages (calculators, cost guides, use-case/application
     * pages, how-tos) from data that's actually specific to that page: its
     * title, and — for the individual supporting calculators — the same
     * slug-based subtype detection template-calculator-single.php already
     * uses to decide which calculator widget to render. This intentionally
     * does NOT reuse one generic sentence for every page (item 2 of the fix
     * report explicitly rules that out) — each branch below produces
     * different copy depending on the page's own title/slug/template.
     *
     * This is a programmatic fallback, not a substitute for hand-written
     * copy. It guarantees zero pages are left with the placeholder string;
     * for maximum SEO quality, the site owner can still override any page
     * individually by setting its `_slab_meta_description` custom field.
     */
    private static function generate_template_description($post) {
        $title = get_the_title($post);
        $slug = $post->post_name;

        if (is_page_template('page-templates/template-calculator-single.php')) {
            if (strpos($slug, 'volume') !== false || strpos($slug, 'yard') !== false) {
                $detail = 'calculate exact volume in cubic feet, yards & meters for slabs, footings, and pours';
            } elseif (strpos($slug, 'bag') !== false) {
                $detail = 'find how many 40-80 lb concrete bags you need, with waste allowance and total cost';
            } elseif (strpos($slug, 'rebar') !== false || strpos($slug, 'mesh') !== false) {
                $detail = 'estimate rebar footage, stick counts, support chairs, or wire mesh coverage';
            } elseif (strpos($slug, 'gravel') !== false) {
                $detail = 'calculate crushed gravel sub-base volume and tonnage, with compaction factored in';
            } elseif (strpos($slug, 'removal') !== false) {
                $detail = 'estimate demolition cost, debris tonnage, and disposal fees';
            } elseif (strpos($slug, 'weight') !== false) {
                $detail = 'calculate wet and cured concrete weight in pounds, tons, and metric tonnes';
            } elseif (strpos($slug, 'cost-per-square-foot') !== false || strpos($slug, 'labor') !== false) {
                $detail = 'break down cost per square foot by material and labor to evaluate contractor bids';
            } else {
                $detail = 'get accurate material quantities for contractor planning and code-compliant estimates';
            }
            return "{$title} — {$detail}. Free, instant results.";
        }

        if (is_page_template('page-templates/template-calculator-hub.php')) {
            return 'Browse our full suite of concrete and flatwork calculators — volume, bags, rebar, gravel, labor, and total project cost — for contractors and DIY builders.';
        }

        if (is_page_template('page-templates/template-cost-guide.php')) {
            return "{$title} — 2026 material and labor pricing, a full cost breakdown, and a free calculator for your exact project budget.";
        }

        if (is_page_template('page-templates/template-hub.php')) {
            return "{$title} — recommended slab thickness, PSI strength, sub-base depth, reinforcement specs, and DIY vs. installed cost.";
        }

        if (is_page_template('page-templates/template-how-to.php')) {
            return "{$title} — step-by-step, ACI 302.1R-aligned instructions from layout and base prep through pouring and curing.";
        }

        return "{$title} — concrete slab estimating guidance, formulas, and cost data from Slab Cost Calculator.";
    }

    public static function get_canonical_url() {
        $hub = self::get_slab_hub_info();
        if ($hub) {
            return home_url($hub['path']);
        }

        if (is_front_page()) {
            return home_url('/');
        }
        if (is_singular()) {
            return get_permalink();
        }
        if (is_category() || is_tag() || is_tax()) {
            return get_term_link(get_queried_object());
        }
        if (is_post_type_archive()) {
            return get_post_type_archive_link(get_query_var('post_type'));
        }
        return home_url(add_query_arg([], $GLOBALS['wp']->request));
    }

    public static function render_meta_tags() {
        $title = wp_get_document_title();
        $description = self::get_meta_description();
        $canonical = self::get_canonical_url();
        // Was og-image.svg: most social platforms (Facebook, WhatsApp,
        // LinkedIn, many X/Twitter clients) do not reliably render SVG for
        // link-preview images, so shared links could show no preview image
        // at all. Rasterized to a 1200x630 PNG (assets/images/og-image.png)
        // from the same source artwork.
        $og_image = get_template_directory_uri() . '/assets/images/og-image.png';

        echo "\n<!-- Technical SEO & Meta Architecture -->\n";
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
        // Verified (fix report item 6): this already emits an explicit
        // "index, follow" alongside the max-* directives on every page — not
        // a misconfiguration, no change needed.
        echo '<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">' . "\n";

        // Open Graph
        echo '<meta property="og:locale" content="' . esc_attr(get_locale()) . '">' . "\n";
        echo '<meta property="og:type" content="' . (is_singular('post') ? 'article' : 'website') . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
        echo '<meta property="og:site_name" content="Slab Cost Calculator">' . "\n";
        echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
        echo '<meta property="og:image:width" content="1200">' . "\n";
        echo '<meta property="og:image:height" content="630">' . "\n";

        // Twitter Cards
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "\n";
        echo "<!-- End Technical SEO -->\n\n";
    }
}

Slab_SEO_Engine::init();
