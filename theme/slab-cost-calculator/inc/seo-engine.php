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

        // Core prints its own <link rel="canonical"> after ours, so every
        // singular page had two. render_meta_tags() owns it now.
        remove_action('wp_head', 'rel_canonical');

        // /llms.txt and /llms-full.txt are files, not pages: without this,
        // WordPress 301s them to "/llms.txt/" and some AI fetchers don't
        // follow redirects.
        add_filter('redirect_canonical', [__CLASS__, 'keep_file_urls_unslashed']);
    }

    public static function keep_file_urls_unslashed($redirect_url) {
        if (get_query_var('slab_llms') || get_query_var('slab_sitemap')) {
            return false;
        }
        return $redirect_url;
    }

    /**
     * Pages that should stay out of search results: internal search, 404,
     * date archives and page 2+ of listings (thin or duplicate), plus the
     * whole site when Settings > Reading > "Discourage search engines" is
     * on. Links on them are still followed.
     */
    public static function is_noindex() {
        if (!get_option('blog_public')) {
            return true;
        }
        if (self::get_slab_hub_info()) {
            return false;
        }
        return is_search() || is_404() || is_date() || is_paged();
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
    public static function get_slab_hub_info() {
        $route = get_query_var('slab_route');
        $hubs = [
            'calc_hub' => [
                'path'        => '/calculators/',
                'name'        => 'Calculators',
                'title'       => 'Concrete Calculators — Volume, Bags, Rebar & Cost Tools',
                'description' => 'Browse our full suite of concrete and flatwork calculators — volume, bags, rebar, gravel, labor, and total project cost — for contractors and DIY builders.',
            ],
            'cost_guide_hub' => [
                'path'        => '/cost-guides/',
                'name'        => 'Cost Guides',
                'title'       => 'Concrete Slab Cost Guides — 2026 Pricing by Project Size',
                'description' => 'Concrete slab cost guides by size and project type — per-square-foot pricing, common slab dimensions, and cubic-yard rates, with real 2026 material and labor numbers.',
            ],
            'how_to_hub' => [
                'path'        => '/how-to/',
                'name'        => 'How-To Guides',
                'title'       => 'How to Pour, Cure & Repair a Concrete Slab — Guides',
                'description' => 'Step-by-step concrete how-to guides covering pouring, curing, crack prevention, sealing, and repair, aligned with ACI 302.1R standards for a durable slab.',
            ],
            'knowledgebase_hub' => [
                'path'        => '/knowledgebase/',
                'name'        => 'Knowledgebase',
                'title'       => 'Concrete Knowledgebase — All Slab Cost Guides & Articles',
                'description' => 'The full Slab Cost Calculator knowledgebase — 30 in-depth articles on concrete costs, construction, estimating, materials, and maintenance, organized by topic.',
            ],
            // Not a listing page: a tool page (schema-generator.php gives it
            // WebPage + SoftwareApplication + FAQPage instead of CollectionPage).
            'quote_checker' => [
                'path'        => '/concrete-quote-checker/',
                'name'        => 'Concrete Quote Checker',
                'title'       => 'Concrete Quote Checker: Is Your Contractor Bid Fair?',
                'description' => 'Check a concrete contractor quote against a scope-matched benchmark for your slab size, location and specs. Free: see if the bid is fair, high or too low.',
                'type'        => 'tool',
            ],
        ];
        // Slab sizes and the routed tool pages (Phase 6). 'parent' adds a
        // breadcrumb step; 'faqs' is the visible FAQ, marked up as FAQPage.
        if ($route === 'size_hub' && class_exists('Slab_Size_Pages')) {
            return [
                'path'        => Slab_Size_Pages::hub_path(),
                'name'        => 'Slab Cost by Size',
                'title'       => sprintf('Concrete Slab Cost by Size: %d Sizes Priced', count(Slab_Size_Pages::hub_rows())),
                'description' => sprintf('Concrete slab cost by size: yards, 80-lb bags and installed price for %d common sizes, from an 8x8 hot tub pad to a 40x60 pole barn floor.', count(Slab_Size_Pages::hub_rows())),
                'type'        => 'collection',
                'faqs'        => Slab_Size_Pages::hub_faqs(),
            ];
        }
        $slug = get_query_var('slab_slug');
        if ($route === 'size_page' && class_exists('Slab_Size_Pages') && Slab_Size_Pages::exists($slug)) {
            return [
                'path'        => Slab_Size_Pages::path($slug),
                'name'        => $slug . ' Concrete Slab',
                'title'       => Slab_Size_Pages::title($slug),
                'description' => Slab_Size_Pages::meta($slug),
                'type'        => 'size',
                'parent'      => ['name' => 'Slab Cost by Size', 'path' => Slab_Size_Pages::hub_path()],
                'faqs'        => Slab_Size_Pages::faqs($slug),
            ];
        }
        if ($route === 'tool_page' && class_exists('Slab_Tool_Pages') && Slab_Tool_Pages::exists($slug)) {
            $page = Slab_Tool_Pages::get($slug);
            return [
                'path'        => '/' . $slug . '/',
                'name'        => Slab_Tool_Pages::directory()[$slug][1],
                'title'       => $page['title'],
                'description' => $page['meta'],
                'type'        => 'tool',
                'parent'      => ['name' => 'Calculators', 'path' => '/calculators/'],
                'faqs'        => $page['faqs'],
            ];
        }

        // State prices, the price report and the embed page (Phase 7).
        if ($route === 'state_hub' && class_exists('Slab_State_Pages')) {
            $ranked = Slab_State_Pages::ranked();
            $cheap = Slab_State_Pages::prices($ranked[count($ranked) - 1]['index'])['slab']['psf'];
            $dear = Slab_State_Pages::prices($ranked[0]['index'])['slab']['psf'];
            return [
                'path'        => Slab_State_Pages::hub_path(),
                'name'        => 'Slab Cost by State',
                'title'       => 'Concrete Slab Cost by State: All 50 States (2026)',
                'description' => sprintf('Concrete slab cost in every U.S. state and 13 metro areas: price per sq ft, a 24x24 garage floor and ready-mix per yard, from %s to %s per sq ft.', Slab_Data::money($cheap[0]), Slab_Data::money($dear[1])),
                'type'        => 'collection',
                'parent'      => ['name' => 'Price Report', 'path' => '/concrete-price-report/'],
                'faqs'        => Slab_State_Pages::hub_faqs(),
            ];
        }
        if ($route === 'state_page' && class_exists('Slab_State_Pages') && Slab_State_Pages::exists($slug)) {
            return [
                'path'        => Slab_State_Pages::path($slug),
                'name'        => Slab_State_Pages::data($slug)['name'],
                'title'       => Slab_State_Pages::title($slug),
                'description' => Slab_State_Pages::meta($slug),
                'type'        => 'size',
                'parent'      => ['name' => 'Slab Cost by State', 'path' => Slab_State_Pages::hub_path()],
                'faqs'        => Slab_State_Pages::faqs($slug),
            ];
        }
        if ($route === 'price_report' && class_exists('Slab_Price_Report')) {
            return [
                'path'        => Slab_Price_Report::path(),
                'name'        => 'Price Report',
                'title'       => Slab_Price_Report::title(),
                'description' => Slab_Price_Report::meta(),
                'type'        => 'report',
                'faqs'        => Slab_Price_Report::faqs(),
            ];
        }
        if ($route === 'embed_info' && class_exists('Slab_Embed_Widget')) {
            return [
                'path'        => Slab_Embed_Widget::info_path(),
                'name'        => 'Embed Our Calculator',
                'title'       => 'Free Concrete Calculator Widget for Your Website',
                'description' => 'Add a free concrete slab calculator to your website with two lines of code: cubic yards, bags and installed cost by state. No sign-up, always current prices.',
                'type'        => 'size',
                'faqs'        => Slab_Embed_Widget::faqs(),
            ];
        }

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
            // Search Console (2026-11-01): "concrete slab cost calculator" is the
            // site's biggest query; the title also covers the "price" variant.
            $title['title'] = 'Concrete Slab Cost Calculator: Price, Yards & Bags (2026)';
            $title['tagline'] = '';
            $title['site'] = '';
        }

        // Google cuts titles off at about 60 characters. Articles whose own
        // headline is longer get a shorter <title> here; the headline on the
        // page (H1) and the URL stay as they are.
        if (is_singular('post')) {
            $short = self::get_short_post_titles();
            $slug = get_post_field('post_name', get_queried_object_id());
            if (isset($short[$slug])) {
                $title['title'] = $short[$slug];
                $title['tagline'] = '';
                $title['site'] = '';
                return $title;
            }
        }

        // Calculator pages with a search-tuned <title> (inc/calculator-pages.php
        // 'title' key). The H1 on the page stays as it is.
        if (is_page() && class_exists('Slab_Calculator_Pages')) {
            $calc = Slab_Calculator_Pages::get(get_post_field('post_name', get_queried_object_id()));
            if ($calc && !empty($calc['title'])) {
                $title['title'] = $calc['title'];
                $title['tagline'] = '';
                $title['site'] = '';
                return $title;
            }
        }

        // Drop the " – Site Name" suffix whenever it would push the title
        // past 60 characters (most calculator, application and policy pages).
        if (!empty($title['title']) && !empty($title['site'])) {
            $full = html_entity_decode(wp_strip_all_tags($title['title'] . ' – ' . $title['site']), ENT_QUOTES, 'UTF-8');
            if (mb_strlen($full) > 60) {
                $title['tagline'] = '';
                $title['site'] = '';
            }
        }

        return $title;
    }

    /** Shorter <title> text for articles whose headline runs past 60 characters. */
    private static function get_short_post_titles() {
        return [
            'how-much-does-a-concrete-slab-cost'          => 'Concrete Slab Cost in 2026: Complete Price Breakdown',
            'how-to-calculate-concrete-for-a-slab'        => 'How to Calculate Concrete for a Slab (Formulas & Steps)',
            'how-many-bags-of-concrete-do-i-need'         => 'How Many Bags of Concrete Do I Need? (80/60/50/40 lb)',
            'ready-mix-vs-bagged-concrete'                => 'Ready-Mix vs Bagged Concrete: Cost & Breakeven',
            '10x10-concrete-slab-cost-breakdown'          => '10x10 Concrete Slab Cost: Pricing & Material Breakdown',
            '24x24-concrete-slab-cost-breakdown'          => '24x24 Concrete Slab Cost: 2-Car Garage & Shop Floor',
            'how-much-does-a-garage-slab-cost'            => 'Garage Slab Cost: Monolithic vs Stem Wall Footings',
            'how-much-does-a-concrete-driveway-cost'      => 'Concrete Driveway Cost in 2026: Price & Design Guide',
            'how-thick-should-a-concrete-slab-be'         => 'How Thick Should a Concrete Slab Be? (4″ to 8″ Guide)',
            'how-much-gravel-do-i-need-under-a-concrete-slab' => 'How Much Gravel Do I Need Under a Concrete Slab?',
            'concrete-slab-waste-factor-explained'        => 'Concrete Slab Waste Factor: Why 10% Is Recommended',
            'how-to-estimate-concrete-labor-cost'         => 'How to Estimate Concrete Labor Cost (Crew Hours & Rates)',
            'concrete-cost-per-cubic-yard-pricing-explained' => 'Concrete Cost Per Cubic Yard (2026 Ready-Mix Pricing)',
            'concrete-shed-pad-cost-and-construction'     => 'Concrete Shed Pad Cost & Build Guide (4″ Slab vs Gravel)',
            'concrete-foundation-monolithic-slab-cost'    => 'Monolithic Slab Foundation Cost: Full Structural Guide',
            'concrete-slab-cracks-causes-and-prevention'  => 'Concrete Slab Cracks: Causes and How to Prevent Them',
            'concrete-vs-asphalt-driveway'                => 'Concrete vs Asphalt Driveway: Cost, Lifespan & Winner',
            'best-time-of-year-to-pour-concrete'          => 'Best Time of Year to Pour Concrete (and Why It Matters)',
            'yard-prep-checklist-for-a-slab-pour'         => 'Yard Prep Checklist Before a Concrete Slab Pour',
        ];
    }

    public static function get_meta_description() {
        $hub = self::get_slab_hub_info();
        if ($hub) {
            return esc_attr($hub['description']);
        }

        if (is_front_page()) {
            return sprintf(
                'Free concrete slab cost calculator: a 4 in slab costs about %s per sq ft installed. Get the total price, cubic yards, bags, rebar and labor for any size.',
                Slab_Data::money_range(Slab_Data::installed_psf(), 0, '–')
            );
        }

        if (is_singular()) {
            global $post;
            $custom_desc = get_post_meta($post->ID, '_slab_meta_description', true);
            if (!empty($custom_desc)) {
                return esc_attr(Slab_Post_Figures::fix($post->post_name, $custom_desc));
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
                return esc_attr(wp_strip_all_tags(Slab_Post_Figures::fix($post->post_name, $post->post_excerpt)));
            }
            if (!$looks_like_placeholder && !empty($raw_content)) {
                return esc_attr(wp_trim_words($raw_content, 26, '...'));
            }

            // Last-resort fallback so a page can never again ship the literal
            // dev/placeholder string — use the same generator with only the
            // page title to work from.
            return esc_attr(self::generate_template_description($post));
        }

        if (is_category()) {
            return esc_attr(self::get_category_description(get_queried_object()));
        }

        return 'Accurate concrete slab calculations, volume estimating, bagged vs ready-mix comparisons, and cost guides for contractors and homeowners.';
    }

    /**
     * Every category archive used to share the generic site description.
     * A description written in wp-admin (Posts > Categories) wins; otherwise
     * each category gets its own line.
     */
    private static function get_category_description($term) {
        if (!$term || empty($term->term_id)) {
            return '';
        }
        $custom = trim(wp_strip_all_tags(term_description($term->term_id)));
        if ($custom !== '') {
            return $custom;
        }

        $by_slug = [
            'concrete-construction'       => 'Concrete construction guides: slab thickness, gravel base, rebar vs wire mesh, yard prep, curing time and the best season to pour, plus garage, shed and foundation slabs.',
            'concrete-costs'              => 'Concrete cost articles: what a slab costs in 2026, a price-per-square-foot matrix, concrete driveway costs, and concrete vs asphalt for a new driveway.',
            'concrete-estimating'         => 'Concrete estimating guides: how to work out cubic yards, bags, waste allowance and labor cost for a slab, with worked examples and the formulas behind our calculator.',
            'concrete-maintenance-repair' => 'Concrete maintenance and repair: why slabs crack and how to prevent it, whether you need a sealer, and resurfacing vs extending an existing slab.',
            'concrete-materials'          => 'Concrete materials explained: ready-mix vs bagged concrete and what a cubic yard of concrete costs, with prices and when each option makes sense.',
            'cost-guides'                 => 'Concrete slab cost guides by size: 10x10, 12x12, 20x20 and 24x24 slab prices plus patio slab costs, with material, labor and per-square-foot breakdowns.',
            'planning-decisions'          => 'Planning a concrete project: slab permits and their costs, and what you can safely do yourself on a DIY slab versus what to leave to a contractor.',
        ];
        if (isset($by_slug[$term->slug])) {
            return $by_slug[$term->slug];
        }

        $name = html_entity_decode($term->name, ENT_QUOTES);
        return sprintf('%s: %d %s on concrete slab costs, estimating and construction from the Slab Cost Calculator editorial team.', $name, (int) $term->count, _n('article', 'articles', (int) $term->count));
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
            $calc_page = class_exists('Slab_Calculator_Pages') ? Slab_Calculator_Pages::get($slug) : null;
            if ($calc_page && !empty($calc_page['meta'])) {
                return $calc_page['meta'];
            }
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
            $app = class_exists('Slab_Applications') ? Slab_Applications::get($post->post_name) : null;
            if ($app && !empty($app['meta'])) {
                return $app['meta'];
            }
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

        // Search and 404 pages are noindexed and have no URL worth
        // consolidating to, so they get no canonical at all.
        if (is_search() || is_404()) {
            return '';
        }

        if (is_paged()) {
            // Page 2+ points to itself, not page 1: it lists different posts.
            return strtok(get_pagenum_link(max(1, (int) get_query_var('paged'))), '?');
        }

        if (is_front_page()) {
            return home_url('/');
        }
        if (is_singular()) {
            return get_permalink();
        }
        if (is_category() || is_tag() || is_tax()) {
            $link = get_term_link(get_queried_object());
            return is_wp_error($link) ? '' : $link;
        }
        if (is_post_type_archive()) {
            return get_post_type_archive_link(get_query_var('post_type'));
        }
        return trailingslashit(home_url($GLOBALS['wp']->request));
    }

    /**
     * The image that represents a post: its featured image, else the first
     * article diagram embedded in its content (the imported articles embed
     * a 1200x675 PNG instead of setting a featured image), else the site's
     * 1200x630 share image. Used for og:image and the Article schema.
     */
    public static function get_share_image($post_id = 0) {
        $fallback = [
            'url'    => get_template_directory_uri() . '/assets/images/og-image.png',
            'width'  => 1200,
            'height' => 630,
        ];
        if (!$post_id) {
            return $fallback;
        }

        $thumb_id = get_post_thumbnail_id($post_id);
        if ($thumb_id) {
            $src = wp_get_attachment_image_src($thumb_id, 'full');
            if ($src) {
                return ['url' => $src[0], 'width' => (int) $src[1], 'height' => (int) $src[2]];
            }
        }

        $content = (string) get_post_field('post_content', $post_id);
        if (preg_match('/<img[^>]+src="([^"]+\.(?:png|jpe?g|webp))"[^>]*>/i', $content, $m)) {
            $tag = $m[0];
            $w = preg_match('/width="(\d+)"/', $tag, $wm) ? (int) $wm[1] : 0;
            $h = preg_match('/height="(\d+)"/', $tag, $hm) ? (int) $hm[1] : 0;
            if ($w && $h) {
                return ['url' => $m[1], 'width' => $w, 'height' => $h];
            }
        }

        return $fallback;
    }

    public static function render_meta_tags() {
        // Google Search Console ownership verification (HTML tag method).
        // Keep it: removing the tag can unverify the property.
        echo '<meta name="google-site-verification" content="9uGVms4w1OFt96PoLLNTu8S8Om2iWEVoyBw-jl5SsD0" />' . "\n";
        $title = wp_get_document_title();
        $description = self::get_meta_description();
        $canonical = self::get_canonical_url();
        // Was og-image.svg: most social platforms (Facebook, WhatsApp,
        // LinkedIn, many X/Twitter clients) do not reliably render SVG for
        // link-preview images, so shared links could show no preview image
        // at all. Rasterized to a 1200x630 PNG (assets/images/og-image.png)
        // from the same source artwork.
        $image = self::get_share_image(is_singular() ? get_queried_object_id() : 0);
        $og_image = $image['url'];

        echo "\n<!-- Technical SEO & Meta Architecture -->\n";
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        if ($canonical) {
            echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
        }
        if (self::is_noindex()) {
            echo '<meta name="robots" content="noindex, follow">' . "\n";
        } else {
            echo '<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">' . "\n";
        }

        // Open Graph
        echo '<meta property="og:locale" content="' . esc_attr(get_locale()) . '">' . "\n";
        echo '<meta property="og:type" content="' . (is_singular('post') ? 'article' : 'website') . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
        if ($canonical) {
            echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
        }
        echo '<meta property="og:site_name" content="Slab Cost Calculator">' . "\n";
        echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
        echo '<meta property="og:image:width" content="' . (int) $image['width'] . '">' . "\n";
        echo '<meta property="og:image:height" content="' . (int) $image['height'] . '">' . "\n";

        // Twitter Cards
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "\n";
        echo "<!-- End Technical SEO -->\n\n";
    }
}

Slab_SEO_Engine::init();
