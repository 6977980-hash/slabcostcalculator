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

        // 2. BreadcrumbList Schema (All pages except front page)
        if (!is_front_page()) {
            $breadcrumb_schema = self::get_breadcrumb_schema();
            if ($breadcrumb_schema) {
                $schemas[] = $breadcrumb_schema;
            }
        }

        // 3. SoftwareApplication Schema (For Homepage & Calculator Pages)
        if (is_front_page() || is_page_template('page-templates/template-calculator-single.php') || is_page_template('page-templates/template-calculator-hub.php')) {
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
        // - Any other page: still supports the manual _enable_faq_schema
        //   opt-in for a hand-authored FAQ block, unchanged.
        if (is_front_page()) {
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
        } elseif (get_post_meta(get_the_ID(), '_enable_faq_schema', true)) {
            $faq_schema = self::get_faq_schema();
            if ($faq_schema) {
                $schemas[] = $faq_schema;
            }
        }

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
            'publishingPrinciples' => home_url('/editorial-policy/'),
            'knowsAbout' => [
                'Concrete Estimating',
                'Civil Construction Engineering',
                'Concrete Mix Design',
                'Building Cost Estimation',
                'Slab Reinforcement Calculations'
            ]
        ];
    }

    private static function get_calculator_software_schema() {
        $title = is_front_page() ? 'Concrete Slab Cost Calculator' : get_the_title();
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
        $desc = is_front_page()
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

    private static function get_breadcrumb_schema() {
        $items = [];
        $position = 1;

        $items[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Home',
            'item' => home_url('/')
        ];

        if (is_page()) {
            global $post;
            $ancestors = get_post_ancestors($post->ID);
            if (!empty($ancestors)) {
                $ancestors = array_reverse($ancestors);
                foreach ($ancestors as $ancestor_id) {
                    $items[] = [
                        '@type' => 'ListItem',
                        'position' => $position++,
                        'name' => get_the_title($ancestor_id),
                        'item' => get_permalink($ancestor_id)
                    ];
                }
            }
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => get_the_title($post->ID),
                'item' => get_permalink($post->ID)
            ];
        } elseif (is_single()) {
            $categories = get_the_category();
            if (!empty($categories)) {
                $items[] = [
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $categories[0]->name,
                    'item' => get_category_link($categories[0]->term_id)
                ];
            }
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => get_the_title(),
                'item' => get_permalink()
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items
        ];
    }

    private static function get_article_schema() {
        global $post;
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title(),
            'description' => Slab_SEO_Engine::get_meta_description(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => get_permalink()
            ],
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'author' => [
                '@type' => 'Person',
                'name' => get_the_author_meta('display_name', $post->post_author),
                'url' => home_url('/authors/')
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
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'How much does a concrete slab cost per square foot?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'On average, a residential concrete slab costs between $6.00 and $12.00 per square foot installed, including materials, site preparation, base gravel, reinforcement, forms, and finishing labor. DIY projects with material only cost approximately $2.50 to $4.50 per square foot.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => 'How much does a 10x10 concrete slab cost?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'A 10x10 foot concrete slab (100 square feet) at a standard 4-inch thickness costs between $600 and $1,200 professionally installed. It requires approximately 1.23 to 1.35 cubic yards of concrete (around 28 bags of 60-lb or 21 bags of 80-lb concrete mix including waste).'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => 'How much does a 20x20 concrete slab cost?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'A 20x20 foot concrete slab (400 square feet) typically costs between $2,400 and $4,800 installed for a 4-inch slab, and $2,800 to $5,400 for a 5-inch or 6-inch garage slab. It requires approximately 5.0 to 5.5 cubic yards of ready-mix concrete.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => 'How thick should a concrete slab be?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Standard non-traffic patios, walkways, and shed floors require a 4-inch slab (minimum 3,000 PSI). Residential driveways and 2-car garages accommodating SUVs and trucks require a 5-inch or 6-inch slab (3,500 to 4,000 PSI) with rebar reinforcement. Heavy RV pads or commercial slabs require 6 to 8 inches.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Is ready-mix concrete cheaper than bagged concrete?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'For projects requiring more than 1.5 to 2 cubic yards (approx. 70+ 80-lb bags), ready-mix delivered by a transit-mix truck is almost always cheaper, faster, and provides superior compressive strength consistency compared to hand-mixing bags.'
                    ]
                ]
            ]
        ];
    }
}

Slab_Schema_Generator::init();
