<?php
/**
 * STRUCTURED DATA / SCHEMA.ORG GENERATOR
 * Injects valid, verified JSON-LD for WebSite, Organization, SoftwareApplication, Article, BreadcrumbList, and FAQPage.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_Schema_Generator {

    public static function init() {
        add_action('wp_head', [__CLASS__, 'render_json_ld'], 20);
    }

    public static function render_json_ld() {
        $schemas = [];

        // 1. WebSite & Organization Schema (Site-wide)
        $schemas[] = self::get_website_schema();
        $schemas[] = self::get_organization_schema();

        // Search and 404 pages are noindexed; they get nothing beyond the
        // site-wide entities.
        if (is_search() || is_404()) {
            self::print_schemas($schemas);
            return;
        }

        // The hub routes (/calculators/, /cost-guides/, /how-to/,
        // /knowledgebase/) report is_front_page() as true, so they used to
        // get the homepage's FAQPage and SoftwareApplication. They are
        // listing pages: a breadcrumb and a CollectionPage, nothing more.
        $hub = Slab_SEO_Engine::get_slab_hub_info();
        if ($hub && isset($hub['type']) && $hub['type'] === 'tool') {
            $url = home_url($hub['path']);
            $schemas[] = self::build_breadcrumb_list([
                ['name' => $hub['name'], 'item' => $url],
            ]);
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                '@id' => $url . '#webpage',
                'url' => $url,
                'name' => $hub['title'],
                'description' => $hub['description'],
                'isPartOf' => ['@id' => home_url('/#website')],
                'inLanguage' => 'en-US',
                'dateModified' => SLAB_CONTENT_UPDATED,
            ];
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'SoftwareApplication',
                'name' => $hub['name'],
                'applicationCategory' => 'BusinessApplication',
                'operatingSystem' => 'All (Web Browser)',
                'browserRequirements' => 'Requires JavaScript',
                'url' => $url,
                'description' => $hub['description'],
                'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
            ];
            // Same Q&As the page shows (page-templates/template-quote-checker.php).
            if (function_exists('slab_quote_checker_faqs')) {
                $schemas[] = [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => array_map(function ($f) {
                        return [
                            '@type' => 'Question',
                            'name' => $f['q'],
                            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
                        ];
                    }, slab_quote_checker_faqs()),
                ];
            }
            self::print_schemas($schemas);
            return;
        }
        if ($hub) {
            $schemas[] = self::build_breadcrumb_list([
                ['name' => $hub['name'], 'item' => home_url($hub['path'])],
            ]);
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                '@id' => home_url($hub['path']) . '#webpage',
                'url' => home_url($hub['path']),
                'name' => $hub['title'],
                'description' => $hub['description'],
                'isPartOf' => ['@id' => home_url('/#website')],
                'inLanguage' => 'en-US',
            ];
            self::print_schemas($schemas);
            return;
        }

        $is_home = slab_is_home_page();

        // 2. BreadcrumbList Schema (All pages except front page)
        if (!$is_home) {
            $breadcrumb_schema = self::get_breadcrumb_schema();
            if ($breadcrumb_schema) {
                $schemas[] = $breadcrumb_schema;
            }
        }

        // 3. SoftwareApplication Schema (For Homepage & Calculator Pages)
        if ($is_home || is_page_template('page-templates/template-calculator-single.php')) {
            $schemas[] = self::get_calculator_software_schema();
        }

        // 4. Article Schema (For Blog posts, Cost Guides, How-Tos)
        if (is_singular('post') || is_page_template('page-templates/template-cost-guide.php') || is_page_template('page-templates/template-how-to.php')) {
            $schemas[] = self::get_article_schema();
        }

        // 5. FAQPage Schema
        // - Front page: hand-written FAQ content (get_faq_schema()) matching
        //   the hand-written accordion in front-page.php.
        // - Individual calculator pages: generated from the SAME
        //   Slab_Calculator_FAQ source that renders the visible accordion
        //   via template-parts/calculator-faq.php, so schema can never drift
        //   out of sync with what's actually on the page.
        // The old _enable_faq_schema opt-in printed the homepage's FAQ on
        // whatever page had it set, which never matched that page's own
        // visible content, so it is gone.
        if ($is_home && !is_paged()) {
            $faq_schema = self::get_faq_schema();
            if ($faq_schema) {
                $schemas[] = $faq_schema;
            }
        } elseif (is_page_template('page-templates/template-calculator-single.php') && class_exists('Slab_Calculator_FAQ')) {
            global $post;
            $slug = $post ? $post->post_name : get_query_var('slab_slug');
            $faq_schema = self::get_calculator_faq_schema($slug);
            if ($faq_schema) {
                $schemas[] = $faq_schema;
            }
        }

        self::print_schemas($schemas);
    }

    private static function print_schemas($schemas) {
        // Output JSON-LD Block
        if (!empty($schemas)) {
            echo "\n<!-- Schema.org JSON-LD -->\n";
            foreach ($schemas as $schema) {
                echo '<script type="application/ld+json">' . "\n";
                echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
                echo '</script>' . "\n";
            }
            echo "<!-- End Schema.org JSON-LD -->\n\n";
        }
    }

    private static function get_website_schema() {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => home_url('/#website'),
            'url' => home_url('/'),
            'name' => 'Slab Cost Calculator',
            'description' => 'Estimate Concrete Volume, Materials, Labor & Total Slab Cost',
            'publisher' => [
                '@id' => home_url('/#organization')
            ],
            'inLanguage' => 'en-US'
        ];
    }

    private static function get_organization_schema() {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => home_url('/#organization'),
            'name' => 'Slab Cost Calculator',
            'url' => home_url('/'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => get_template_directory_uri() . '/assets/images/logo.svg'
            ],
            'email' => 'contact@slabcostcalculator.com',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer support',
                'email' => 'contact@slabcostcalculator.com',
                'url' => home_url('/contact/'),
            ],
            'publishingPrinciples' => home_url('/editorial-policy/'),
            'knowsAbout' => [
                'Concrete Estimating',
                'Civil Construction Engineering',
                'Concrete Mix Design',
                'Building Cost Estimation',
                'Slab Reinforcement Calculations',
                'ACI 302.1R Concrete Floor and Slab Construction',
                'ASTM C94 Ready-Mixed Concrete'
            ]
        ];
    }

    private static function get_calculator_software_schema() {
        $is_home = slab_is_home_page();
        $title = $is_home ? 'Concrete Slab Cost Calculator' : self::plain(get_post_field('post_title', get_queried_object_id()));
        // Was: wp_strip_all_tags(get_the_excerpt()) — get_the_excerpt() falls
        // back to trimming raw post_content when no manual excerpt is set,
        // which is the exact same root cause as the placeholder meta
        // description bug (see Slab_SEO_Engine::get_meta_description()):
        // these pages' real content is rendered entirely by the page
        // template, and post_content itself often held nothing but a dev
        // placeholder string. Reuse the already-hardened description
        // generator instead so structured data can never leak that string.
        // get_meta_description() runs its output through esc_attr() for its
        // normal <meta> tag context; decode that back here since this value
        // goes into JSON-LD instead, where wp_json_encode() does its own
        // correct escaping (avoids a literal "&amp;" appearing in rich results).
        $desc = $is_home
            ? 'Interactive concrete slab estimating software calculating volume in cubic yards and meters, bag requirements, sub-base gravel, rebar reinforcement, labor, and total project budget.'
            : html_entity_decode(Slab_SEO_Engine::get_meta_description(), ENT_QUOTES);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => $title,
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'All (Web Browser)',
            'browserRequirements' => 'Requires JavaScript',
            'url' => Slab_SEO_Engine::get_canonical_url(),
            'description' => $desc,
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD'
            ],
            'featureList' => [
                'Imperial and Metric unit conversion',
                'Ready-Mix vs Bagged concrete cost comparison',
                'Sub-base gravel and site preparation calculations',
                'Rebar grid and wire mesh reinforcement estimating',
                'Formwork lumber and vapor barrier estimating',
                'Customizable labor rates and contractor markup',
                'Real-time line-item Bill of Quantities output',
                'Printable and downloadable project estimates'
            ]
        ];
    }

    /**
     * JSON-LD is the only breadcrumb markup on the page (the visible trail in
     * template-parts/breadcrumbs.php no longer carries microdata), so there
     * is exactly one BreadcrumbList. Both are built from
     * slab_get_breadcrumb_trail(), so they always list the same steps.
     */
    private static function get_breadcrumb_schema() {
        $trail = slab_get_breadcrumb_trail();
        if (empty($trail)) {
            return null;
        }
        return self::build_breadcrumb_list($trail);
    }

    /**
     * @param array $trail Steps after "Home": each ['name' => ..., 'item' => url].
     */
    private static function build_breadcrumb_list($trail) {
        $items = [[
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => home_url('/'),
        ]];
        $position = 2;
        foreach ($trail as $step) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => self::plain($step['name']),
                'item' => $step['item'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * Titles and descriptions from WordPress come HTML-escaped and
     * texturized ("10&#215;10", "&#038;", "you&#039;ll"). JSON-LD needs the
     * plain characters; wp_json_encode() does its own escaping.
     */
    private static function plain($text) {
        return trim(html_entity_decode(wp_strip_all_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private static function get_article_schema() {
        $post_id = get_queried_object_id();
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => self::plain(get_post_field('post_title', $post_id)),
            'description' => self::plain(Slab_SEO_Engine::get_meta_description()),
            'image' => ['@type' => 'ImageObject'] + Slab_SEO_Engine::get_share_image($post_id),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => get_permalink($post_id)
            ],
            'datePublished' => get_the_date('c', $post_id),
            'dateModified' => get_the_modified_date('c', $post_id),
            // Articles are written by the site's editorial team, not a named
            // individual, so credit the team as an Organization instead of a
            // Person named after the WordPress login ("Team-SlabCostCalculator").
            'author' => [
                '@type' => 'Organization',
                'name' => 'Slab Cost Calculator Editorial Team',
                'url' => home_url('/authors/'),
                'parentOrganization' => ['@id' => home_url('/#organization')]
            ],
            'citation' => [
                ['@type' => 'CreativeWork', 'name' => 'ACI 302.1R: Guide for Concrete Floor and Slab Construction', 'publisher' => ['@type' => 'Organization', 'name' => 'American Concrete Institute']],
                ['@type' => 'CreativeWork', 'name' => 'ASTM C94: Standard Specification for Ready-Mixed Concrete', 'publisher' => ['@type' => 'Organization', 'name' => 'ASTM International']]
            ],
            'publisher' => [
                '@id' => home_url('/#organization')
            ]
        ];
    }

    /**
     * FAQPage schema for an individual calculator page, built from the exact
     * same Slab_Calculator_FAQ::get_faqs_for_slug() data that
     * template-parts/calculator-faq.php renders as visible content on the
     * page — so this can never advertise a question the visitor can't
     * actually see and expand.
     */
    private static function get_calculator_faq_schema($slug) {
        $faqs = Slab_Calculator_FAQ::get_faqs_for_slug($slug);
        if (empty($faqs)) {
            return null;
        }

        $items = [];
        foreach ($faqs as $faq) {
            $items[] = [
                '@type' => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => wp_strip_all_tags($faq['a']),
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $items,
        ];
    }

    private static function get_faq_schema() {
        // Same entries the homepage renders in its visible FAQ.
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(function ($faq) {
                return [
                    '@type' => 'Question',
                    'name' => $faq['q'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['a']
                    ]
                ];
            }, self::get_home_faqs())
        ];
    }

    /**
     * Every homepage FAQ, in display order. front-page.php renders these and
     * get_faq_schema() marks up the same list, so the two cannot drift.
     * All figures come from Slab_Data (inc/site-data.php).
     */
    public static function get_home_faqs() {
        $sd = Slab_Data::all();
        $f10 = Slab_Data::size_fact(10, 10);
        $f20 = Slab_Data::size_fact(20, 20);
        $f20_5 = Slab_Data::size_fact(20, 20, 5);
        $y80 = $sd['concrete']['bag_yields']['80'];
        $cure = $sd['curing'];

        return array_merge([
            [
                'q' => 'How much does a 10x10 concrete slab cost?',
                'a' => sprintf(
                    'A 10x10 foot concrete slab (100 square feet) at standard 4-inch depth costs about %s professionally installed. Material-only DIY cost is about %s. It requires %s cubic yards of concrete (%s cubic yards with %d%% waste), which is %d bags of 80-lb pre-mix or %d bags of 60-lb pre-mix; %d bags at %s comes to %s for the concrete alone.',
                    $f10['installed'], $f10['diy'], $f10['net_yd'], $f10['order_yd'], $sd['concrete']['waste_pct'], $f10['bags80'], $f10['bags60'],
                    $f10['bags80'], Slab_Data::money($sd['concrete']['bag_price_default'], 2), Slab_Data::money($f10['bags80'] * $sd['concrete']['bag_price_default'], 0)
                ),
            ],
            [
                'q' => 'How much does a 20x20 concrete slab cost?',
                'a' => sprintf(
                    'A 20x20 foot slab (400 square feet) typically costs %s installed for a standard 4-inch patio or shed floor, and %s for a 5-inch garage floor. At 4 inches thick it requires %s cubic yards of ready-mix concrete (%s cubic yards with %d%% waste).',
                    $f20['installed'], $f20_5['installed'], $f20['net_yd'], $f20['order_yd'], $sd['concrete']['waste_pct']
                ),
            ],
            [
                'q' => 'How many 80 lb bags of concrete do I need for a cubic yard?',
                'a' => sprintf(
                    'It takes %s bags of 80-lb concrete mix to equal one cubic yard (27 cubic feet). One 80-lb bag yields about %s cubic feet of mixed concrete (27 ÷ %s = %s bags).',
                    Slab_Data::num(27 / $y80), number_format($y80, 2), number_format($y80, 2), Slab_Data::num(27 / $y80)
                ),
            ],
            [
                'q' => 'How long should I wait before driving or parking on a new concrete slab?',
                'a' => sprintf(
                    'Foot traffic is safe after %s hours. Light passenger cars can park after %d days (when concrete reaches about 70%% of its design strength). Heavy trucks, RVs, and heavy machinery should wait %d days until the slab reaches its design strength.',
                    Slab_Data::range($cure['foot_traffic_h']), $cure['cars_days'], $cure['full_days']
                ),
            ],
            [
                'q' => 'Do I need a gravel base under a concrete slab?',
                'a' => sprintf(
                    'Yes. A %s-inch compacted crushed stone or gravel base provides uniform subgrade support, facilitates water drainage away from the slab, and dramatically reduces cracking caused by soil settling or freeze-thaw frost heave.',
                    Slab_Data::range($sd['site']['gravel_depth_range'], '-inch to ')
                ),
            ],
        ], self::get_cost_factor_faqs());
    }

    /**
     * FAQs for the cost factor sections (template-parts/cost-factor-guides.php).
     */
    public static function get_cost_factor_faqs() {
        $sd = Slab_Data::all();
        $ref = Slab_Data::estimate(20, 20, 4);
        $ref_yd = round($ref['order_yd'], 2);
        $small = Slab_Data::estimate(10, 10, 4);
        $step = $sd['concrete']['psi_step_price'];
        $p = $sd['pump'];
        $dl = $sd['delivery'];
        $rm = $sd['concrete']['ready_mix_default'];
        $order_yd = ceil($small['order_yd'] * 2 - 1e-9) / 2;
        $m = $sd['monolithic'];
        $garage_yd = Slab_Data::estimate(24, 24, 4)['order_yd'];
        $e12 = Slab_Data::edge_yd(24, 24, 4, $m['edge_depth_in'][0]);
        $e18 = Slab_Data::edge_yd(24, 24, 4, $m['edge_depth_in'][1]);
        $add = Slab_Data::round_pair([
            $e12 * $rm + $m['edge_rebar'][0] + $m['trenching']['12'][0],
            $e18 * $rm + $m['edge_rebar'][1] + $m['trenching']['18'][1],
        ], 5);
        $j = $sd['joints'];

        return [
            [
                'q' => 'How much more does 4000 PSI concrete cost than 3000 PSI?',
                'a' => sprintf(
                    'Each 500-PSI step usually adds about %s per cubic yard, so 4000 PSI runs roughly %s more per yard than 3000 PSI. On a 20x20 ft, 4-inch slab (%s cubic yards with waste) that is about %s in total. The IRC calls for at least %s PSI air-entrained concrete for garage floors, porches and steps in severe-weathering regions.',
                    Slab_Data::money_range($step, 0, ' to '),
                    Slab_Data::money_range([$step[0] * 2, $step[1] * 2], 0, ' to '),
                    number_format($ref_yd, 2),
                    Slab_Data::money_range(Slab_Data::round_pair(Slab_Data::psi_extra(4000, $ref_yd), 5), 0, ' to '),
                    number_format($sd['code']['psi_severe'])
                ),
            ],
            [
                'q' => 'How much does a concrete pump cost for a slab?',
                'a' => sprintf(
                    'Most residential pours cost %s to pump. A line (trailer) pump runs about %s per hour with a %s hour minimum, typically %s per pour. A boom pump runs about %s per hour with a %s hour minimum, typically %s or more. A pump is needed when the truck cannot park within about %s feet of the forms.',
                    Slab_Data::money_range($p['typical'], 0, ' to '), Slab_Data::money_range($p['line_rate'], 0, ' to '), Slab_Data::range($p['line_min_h']),
                    Slab_Data::money_range($p['line_job'], 0, ' to '), Slab_Data::money_range($p['boom_rate'], 0, ' to '), Slab_Data::range($p['boom_min_h']),
                    Slab_Data::money_range($p['boom_job'], 0, ' to '), Slab_Data::range($sd['site']['chute_reach_ft'])
                ),
            ],
            [
                'q' => 'What is a short load fee for ready-mix concrete?',
                'a' => sprintf(
                    'It is a charge for ordering less than the plant\'s minimum load, which ranges from %s cubic yards (many residential suppliers use %s). Suppliers charge either a flat %s per load or %s for each yard short of the minimum. For a 10x10 ft, 4-inch slab, %s cubic yards at %s plus a %s short-load fee comes to about %s, compared with %s for %d bags of 80-lb mix.',
                    Slab_Data::range($dl['min_load_yd']), Slab_Data::range($dl['common_min_yd']),
                    Slab_Data::money_range($dl['short_load_flat'], 0, ' to '), Slab_Data::money_range($dl['short_load_per_yd'], 0, ' to '),
                    Slab_Data::num($order_yd), Slab_Data::money($rm), Slab_Data::money($dl['fee_default']),
                    Slab_Data::money($order_yd * $rm + $dl['fee_default'], 0), Slab_Data::money($small['bagged'], 0), $small['bags80']
                ),
            ],
            [
                'q' => 'Is a monolithic slab more expensive than a floating slab?',
                'a' => sprintf(
                    'Yes. A monolithic slab adds a thickened edge footing, usually %d inches wide and %s inches deep, with two continuous #4 bars. On a 24x24 ft, 4-inch garage slab that adds about %s to %s cubic yards of concrete with waste, or roughly %s including rebar and trenching. Under IRC R403.1.4.1 a detached light-frame accessory building of 600 square feet or less with eaves of 10 feet or lower can usually sit on a floating slab.',
                    $m['edge_width_in'], Slab_Data::range($m['edge_depth_in']), number_format($e12, 1), number_format($e18, 1),
                    Slab_Data::money_range($add, 0, ' to ')
                ),
            ],
            [
                'q' => 'How far apart should control joints be in a 4-inch concrete slab?',
                'a' => sprintf(
                    'Space control joints %s times the slab thickness in inches, in feet, so a 4-inch slab gets joints every %s feet and never more than %d feet. Cut them 1 inch deep (one quarter of the thickness), typically %s hours after finishing with a conventional wet saw, or %s hours with an early-entry saw. A 20x20 ft slab jointed at 10 feet needs 40 linear feet of cuts.',
                    Slab_Data::range($j['ratio']), Slab_Data::range([4 * $j['ratio_best'][0], 4 * $j['ratio_best'][1]]), $j['max_ft'][4],
                    Slab_Data::range($j['sawcut_h']), Slab_Data::range($j['early_entry_h'])
                ),
            ],
        ];
    }
}

Slab_Schema_Generator::init();
