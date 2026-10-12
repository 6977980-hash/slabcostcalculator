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
        // High-value FAQ entries supported by visible front page content
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_merge([
                [
                    '@type' => 'Question',
                    'name' => 'How much does a 10x10 concrete slab cost?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'A 10x10 foot concrete slab (100 square feet) at standard 4-inch depth costs between $600 and $1,200 professionally installed. Material-only DIY cost is approximately $220 to $350. It requires 1.23 cubic yards of concrete (1.36 cubic yards with 10% waste), which is 62 bags of 80-lb pre-mix or 82 bags of 60-lb pre-mix.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => 'How much does a 20x20 concrete slab cost?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'A 20x20 foot slab (400 square feet) typically costs $2,400 to $4,800 for a standard 4-inch patio or shed floor, and $2,800 to $5,400 for a 5-inch or 6-inch garage floor with rebar reinforcement. At 4 inches thick it requires 4.94 cubic yards of ready-mix concrete (5.43 cubic yards with 10% waste).'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => 'How many 80 lb bags of concrete do I need for a cubic yard?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'It takes exactly 45 bags of 80-lb concrete mix to equal one cubic yard (27 cubic feet). One 80-lb bag yields approximately 0.60 cubic feet of mixed wet concrete (27 ÷ 0.60 = 45 bags).'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => 'How long should I wait before driving or parking on a new concrete slab?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Foot traffic is safe after 24 to 48 hours. Light passenger cars can park after 7 days (when concrete achieves ~70% design strength). Heavy trucks, RVs, and heavy machinery should wait 28 days until full compressive curing is reached.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Do I need a gravel base under a concrete slab?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Yes. A 4-inch to 6-inch compacted crushed stone or gravel base provides uniform subgrade support, facilitates water drainage away from the slab, and dramatically reduces cracking caused by soil settling or freeze-thaw frost heave.'
                    ]
                ]
            ], array_map(function ($faq) {
                return [
                    '@type' => 'Question',
                    'name' => $faq['q'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['a']
                    ]
                ];
            }, self::get_cost_factor_faqs()))
        ];
    }

    /**
     * FAQs for the cost factor sections (template-parts/cost-factor-guides.php).
     * front-page.php renders these same entries in its visible FAQ, so the
     * schema and the page cannot drift apart.
     */
    public static function get_cost_factor_faqs() {
        return [
            [
                'q' => 'How much more does 4000 PSI concrete cost than 3000 PSI?',
                'a' => 'Each 500-PSI step usually adds about $4 to $10 per cubic yard, so 4000 PSI runs roughly $8 to $20 more per yard than 3000 PSI. On a 20x20 ft, 4-inch slab (5.43 cubic yards with waste) that is about $45 to $110 in total. The IRC calls for at least 3500 PSI air-entrained concrete for garage floors, porches and steps in severe-weathering regions.'
            ],
            [
                'q' => 'How much does a concrete pump cost for a slab?',
                'a' => 'Most residential pours cost $600 to $1,200 to pump. A line (trailer) pump runs about $100 to $175 per hour with a 2 to 4 hour minimum, typically $400 to $800 per pour. A boom pump runs about $150 to $350 per hour with a 3 to 4 hour minimum, typically $900 to $1,800 or more. A pump is needed when the truck cannot park within about 15 to 20 feet of the forms.'
            ],
            [
                'q' => 'What is a short load fee for ready-mix concrete?',
                'a' => 'It is a charge for ordering less than the plant\'s minimum load, usually set between 4 and 10 cubic yards. Suppliers charge either a flat $80 to $200 per load or $30 to $45 for each yard short of the minimum. For a 10x10 ft, 4-inch slab, 1.5 cubic yards at $150 plus a $120 short-load fee comes to about $345, compared with $403 for 62 bags of 80-lb mix.'
            ],
            [
                'q' => 'Is a monolithic slab more expensive than a floating slab?',
                'a' => 'Yes. A monolithic slab adds a thickened edge footing, usually 12 inches wide and 12 to 18 inches deep, with two continuous #4 bars. On a 24x24 ft, 4-inch garage slab that adds about 2.5 to 4.4 cubic yards of concrete with waste, or roughly $625 to $1,400 including rebar and trenching. Under IRC R403.1.4.1 a detached light-frame accessory building of 600 square feet or less with eaves of 10 feet or lower can usually sit on a floating slab.'
            ],
            [
                'q' => 'How far apart should control joints be in a 4-inch concrete slab?',
                'a' => 'Space control joints 2 to 3 times the slab thickness in inches, in feet, so a 4-inch slab gets joints every 8 to 10 feet and never more than 12 feet. Cut them 1 inch deep (one quarter of the thickness), typically 4 to 12 hours after finishing with a conventional wet saw, or 1 to 4 hours with an early-entry saw. A 20x20 ft slab jointed at 10 feet needs 40 linear feet of cuts.'
            ],
        ];
    }
}

Slab_Schema_Generator::init();
