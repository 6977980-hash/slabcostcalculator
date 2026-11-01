<?php
/**
 * CALCULATOR PAGES: WHICH TOOL EACH PAGE SHOWS, AND ITS OWN TEXT
 *
 * The 16 calculator pages share page-templates/template-calculator-single.php.
 * The template used to pick a tool by guessing from the slug, so "Mix Ratio",
 * "Formwork", "Waste", "Ready-Mix", "Cost Per Sq Ft" and "Project Cost" all
 * fell through to the same slab calculator, "Wire Mesh" showed the rebar
 * tool, and every page printed the same "How This Calculation Works" text.
 *
 * Each page is now listed here by its exact slug with the tool it shows, a
 * short answer printed with real numbers (so search engines and visitors
 * without JavaScript see results), its own method section and, for the
 * pages that need them, its own FAQ (inc/calculator-faq.php falls back to
 * the shared lists for the rest). Every figure comes from Slab_Data.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_Calculator_Pages {

    private static $cache = null;

    /** Page slug => tool key, title for menus, short card description. */
    public static function directory() {
        return [
            'concrete-volume-calculator' => ['volume', 'Concrete Volume Calculator', 'Cubic feet, cubic yards and cubic meters for any slab.'],
            'concrete-yard-calculator' => ['yards', 'Cubic Yard Calculator', 'Yards to order with waste, rounded to the steps plants sell in.'],
            'concrete-bags-calculator' => ['bags', 'Concrete Bags Calculator', '40, 50, 60 or 80-lb bags needed, with waste and cost.'],
            'concrete-mix-calculator' => ['mix', 'Concrete Mix Ratio Calculator', 'Cement bags, sand, stone and water for site-mixed concrete.'],
            'ready-mix-concrete-calculator' => ['readymix', 'Ready-Mix Concrete Calculator', 'Truck order, price by PSI, delivery and short-load fees.'],
            'concrete-waste-calculator' => ['waste', 'Concrete Waste Calculator', 'How much extra to order for your site, and what it costs.'],
            'concrete-weight-calculator' => ['weight', 'Concrete Weight Calculator', 'Wet and cured weight in pounds, tons and tonnes.'],
            'concrete-rebar-calculator' => ['rebar', 'Rebar Calculator', 'Bar footage, 20-ft sticks and chairs for a #4 grid.'],
            'concrete-wire-mesh-calculator' => ['mesh', 'Wire Mesh Calculator', 'Sheets or rolls of 6x6 welded wire mesh, with overlaps.'],
            'concrete-form-calculator' => ['forms', 'Concrete Form Calculator', 'Form boards, stakes and screws for the slab edge.'],
            'gravel-base-calculator' => ['gravel', 'Gravel Base Calculator', 'Yards and tons of crushed stone, with compaction.'],
            'concrete-footing-calculator' => ['footing', 'Footing & Pier Calculator', 'Concrete for strip footings and round piers.'],
            'concrete-labor-cost-calculator' => ['labor', 'Labor Cost Calculator', 'Finishing labor cost and crew hours by finish.'],
            'concrete-removal-cost-calculator' => ['removal', 'Concrete Removal Calculator', 'Demolition cost and debris tonnage.'],
            'concrete-cost-per-square-foot-calculator' => ['primary', 'Cost Per Sq Ft Calculator', 'Installed cost per square foot by thickness and finish.'],
            'concrete-project-cost-calculator' => ['primary', 'Project Cost Calculator', 'Every line item of a slab project, from base to finish.'],
        ];
    }

    /** Number of different tools on the site (the slab calculator plus each distinct page tool). */
    public static function tool_count() {
        $tools = array_unique(array_column(self::directory(), 0));
        // Plus the routed tools (fence posts, steps, bags vs. ready-mix).
        return count($tools) + (class_exists('Slab_Tool_Pages') ? count(Slab_Tool_Pages::directory()) : 0);
    }

    /** Tool key for a page slug, or null. */
    public static function tool($slug) {
        $d = self::directory();
        return isset($d[$slug]) ? $d[$slug][0] : null;
    }

    /** Page text for a slug: subtitle, meta, answer (HTML), sections [[h2, html]], faqs (optional). */
    public static function get($slug) {
        $all = self::pages();
        return isset($all[$slug]) ? $all[$slug] : null;
    }

    private static function formula($label, $math, $note) {
        return '<div class="formula-box"><div class="formula-label">' . esc_html($label) . '</div><div class="formula-math">' . esc_html($math) . '</div><div class="formula-explanation">' . esc_html($note) . '</div></div>';
    }

    private static function link($path, $text) {
        return '<a href="' . esc_url(home_url($path)) . '">' . esc_html($text) . '</a>';
    }

    /* ----------------------------- shared results ----------------------------- */

    /** Default results for the new tools, used by the page text and the tool markup. */
    public static function mix_result($l, $w, $t, $ratio, $waste_pct) {
        $c = Slab_Data::get('concrete');
        $parts = array_map('floatval', explode(':', $ratio));
        $sum = array_sum($parts);
        $wet = $l * $w * $t / 12 * (1 + $waste_pct / 100);
        $dry = $wet * $c['dry_volume_factor'];
        $cement_ft3 = $dry * $parts[0] / $sum;
        $sand_ft3 = $dry * $parts[1] / $sum;
        $stone_ft3 = $dry * $parts[2] / $sum;
        $cement_lb = $cement_ft3 / $c['cement_bag_ft3'] * $c['cement_bag_lb'];
        return [
            'wet_ft3' => $wet,
            'cement_bags' => (int) ceil(round($cement_ft3 / $c['cement_bag_ft3'], 6)),
            'sand_yd' => $sand_ft3 / 27,
            'sand_tons' => $sand_ft3 / 27 * $c['sand_tons_per_yd'],
            'stone_yd' => $stone_ft3 / 27,
            'stone_tons' => $stone_ft3 / 27 * Slab_Data::get('site.gravel_tons_per_yd'),
            'water_gal' => $cement_lb * $c['water_cement_ratio'] / 8.34,
        ];
    }

    public static function form_result($l, $w, $t, $board_len, $stake_ft) {
        $depths = Slab_Data::get('site.form_board_depth_in');
        $board = '2x12';
        foreach ($depths as $name => $depth) {
            if ($depth >= $t - 0.5) {
                $board = $name;
                break;
            }
        }
        $boards = 0;
        $stakes = 0;
        foreach ([$l, $l, $w, $w] as $side) {
            $n = (int) ceil($side / $board_len - 1e-9);
            $boards += $n;
            $stakes += (int) ceil($side / $stake_ft - 1e-9) + ($n - 1);
        }
        return [
            'perimeter' => 2 * ($l + $w),
            'board' => $board,
            'boards' => $boards,
            'stakes' => $stakes,
            'screws' => $stakes * 2,
            'cost' => 2 * ($l + $w) * Slab_Data::get('site.forms_default'),
        ];
    }

    public static function mesh_result($l, $w, $type = 'sheet') {
        $r = Slab_Data::get('reinforcement');
        $lap = $r['mesh_overlap_in'] / 12;
        $chair = $r['chair_grid_ft'][1];
        $chairs = ((int) floor($l / $chair) + 1) * ((int) floor($w / $chair) + 1);
        if ($type === 'roll') {
            $lane = $r['mesh_roll_ft'][0] - $lap;
            $run = min((int) ceil($w / $lane - 1e-9) * $l, (int) ceil($l / $lane - 1e-9) * $w);
            $count = (int) ceil($run / $r['mesh_roll_ft'][1] - 1e-9);
            $price = $r['mesh_roll_price'];
        } else {
            $a = $r['mesh_sheet_ft'][0] - $lap;
            $b = $r['mesh_sheet_ft'][1] - $lap;
            $count = min((int) ceil($l / $b - 1e-9) * (int) ceil($w / $a - 1e-9), (int) ceil($l / $a - 1e-9) * (int) ceil($w / $b - 1e-9));
            $price = $r['mesh_sheet_price'];
        }
        return ['count' => $count, 'chairs' => $chairs, 'cost' => [$count * $price[0], $count * $price[1]]];
    }

    public static function readymix_result($l, $w, $t, $psi) {
        $d = Slab_Data::all();
        $e = Slab_Data::estimate($l, $w, $t);
        $step = $d['delivery']['order_step_yd'][0];
        $order = ceil($e['order_yd'] / $step - 1e-9) * $step;
        $price = Slab_Data::psi_price($psi);
        $short = $order < $d['delivery']['common_min_yd'][0];
        $concrete = [$order * $price[0], $order * $price[1]];
        $fee = $d['delivery']['fee_default'];
        $short_fee = $short ? $d['delivery']['short_load_flat'] : [0, 0];
        return [
            'net_yd' => $e['net_yd'],
            'order_yd' => $order,
            'price' => $price,
            'concrete' => $concrete,
            'short' => $short,
            'short_fee' => $short_fee,
            'trucks' => (int) ceil($order / $d['delivery']['truck_capacity_yd'][0] - 1e-9),
            'total' => [$concrete[0] + $fee + $short_fee[0], $concrete[1] + $fee + $short_fee[1]],
            'bags80' => $e['bags80'],
        ];
    }

    /* --------------------------------- pages --------------------------------- */

    private static function pages() {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $sd = Slab_Data::all();
        $c = $sd['concrete'];
        $dl = $sd['delivery'];
        $r = $sd['reinforcement'];
        $s = $sd['site'];
        $lab = $sd['labor'];
        $waste = $c['waste_pct'];
        $e20 = Slab_Data::estimate(20, 20, 4);
        $e10 = Slab_Data::estimate(10, 10, 4);
        $step = $dl['order_step_yd'][0];
        $round = function ($yd) use ($step) {
            return ceil($yd / $step - 1e-9) * $step;
        };
        $nf = function ($n, $d = 2) {
            return number_format($n, $d);
        };
        $mr = function ($pair, $dec = 0) {
            return Slab_Data::money_range($pair, $dec, ' to ');
        };

        $mix = self::mix_result(10, 10, 4, '1:2:3', $waste);
        $form = self::form_result(20, 20, 4, 12, $s['stake_spacing_ft'][0]);
        $mesh = self::mesh_result(20, 20, 'sheet');
        $mesh_roll = self::mesh_result(20, 20, 'roll');
        $rm = self::readymix_result(20, 20, 4, 3000);
        $rm_small = self::readymix_result(10, 10, 4, 3000);
        $grid = Slab_Data::rebar_grid(20, 20, $r['rebar_base_spacing_in']);
        $gravel_yd = 400 * $s['gravel_depth_in'] / 12 / 27 * $s['gravel_compaction'];
        $wt = 400 * 4 / 12 * $c['density_lb_ft3'];
        $psf = Slab_Data::installed_psf();
        $foot_yd = 80 * 1 * 1 / 27 * (1 + $waste / 100);

        self::$cache = [

            'concrete-volume-calculator' => [
                'subtitle' => 'Enter length, width and thickness to get the exact volume of a slab in cubic feet, cubic yards and cubic meters.',
                'meta' => 'Concrete volume calculator: slab volume in cubic feet, cubic yards and cubic meters. Formula, worked example and unit conversions.',
                'answer' => sprintf('A 20 × 20 ft slab 4 inches thick holds <strong>%s cubic feet</strong>, which is <strong>%s cubic yards</strong> or %s cubic meters, before any waste allowance.', $nf(400 * 4 / 12), $nf($e20['net_yd']), $nf(400 * 4 / 12 * 0.0283168)),
                'sections' => [
                    ['How the Volume Is Calculated', self::formula('Slab volume', 'Length (ft) × Width (ft) × Thickness (in) ÷ 12 = cubic feet', 'Divide cubic feet by 27 for cubic yards, or multiply by 0.0283 for cubic meters.')
                        . '<p>This tool gives the exact geometric volume. It is the number to use when you compare quantities or check a takeoff. To decide how much to order, add a waste allowance with the ' . self::link('/concrete-yard-calculator/', 'cubic yard calculator') . ' or the ' . self::link('/concrete-waste-calculator/', 'waste calculator') . '. For shapes other than a rectangle, split the area into rectangles, triangles (half of base × height) and circles (3.14 × radius²) and add them up. Our guide on ' . self::link('/how-to-calculate-concrete-for-a-slab/', 'how to calculate concrete for a slab') . ' walks through those shapes.</p>'],
                ],
            ],

            'concrete-yard-calculator' => [
                'subtitle' => 'Turn slab measurements into the number of cubic yards to order from a ready-mix plant, with waste and rounding included.',
                'meta' => sprintf('Cubic yard calculator for concrete: yards to order with %d%% waste, rounded to quarter yards. A 20x20x4 slab needs %s yd³.', $waste, $nf($round($e20['order_yd']))),
                'answer' => sprintf('A 20 × 20 ft slab 4 inches thick is %s cubic yards. With %d%% waste that is %s yd³, so you would order <strong>%s cubic yards</strong>, rounded up to the next quarter yard.', $nf($e20['net_yd']), $waste, $nf($e20['order_yd']), $nf($round($e20['order_yd']))),
                'sections' => [
                    ['From Volume to an Order', self::formula('Yards to order', '(L × W × T ÷ 12) ÷ 27 × (1 + waste %) → round up', 'Plants sell in ' . Slab_Data::num($dl['order_step_yd'][0]) . ' or ' . Slab_Data::num($dl['order_step_yd'][1]) . ' yd steps, so always round up, never down.')
                        . sprintf('<p>Running short is far more expensive than a little extra. A second delivery means another delivery fee and often a short-load fee (%s), and the first concrete may set before the rest arrives, leaving a weak cold joint. A truck carries about %s yd³, so larger pours need more than one load, and many plants charge extra for orders under %s yd³. The %s shows those fees and prices by strength.</p>', esc_html($mr($dl['short_load_flat'])), esc_html(Slab_Data::range($dl['truck_capacity_yd'], '–')), esc_html(Slab_Data::range($dl['common_min_yd'], '–')), self::link('/ready-mix-concrete-calculator/', 'ready-mix calculator'))],
                ],
                'faqs' => [
                    ['q' => 'How many cubic yards of concrete do I need for a 20x20 slab?', 'a' => sprintf('At 4 inches thick, %s cubic yards. With %d%% waste, order %s yd³. At 5 inches order %s yd³, and at 6 inches %s yd³.', $nf($e20['net_yd']), $waste, $nf($round($e20['order_yd'])), $nf($round(Slab_Data::estimate(20, 20, 5)['order_yd'])), $nf($round(Slab_Data::estimate(20, 20, 6)['order_yd'])))],
                    ['q' => 'How many square feet does a yard of concrete cover?', 'a' => sprintf('One cubic yard covers about %s sq ft at 4 inches thick, %s sq ft at 5 inches and %s sq ft at 6 inches, before waste.', Slab_Data::num(27 * 12 / 4), Slab_Data::num(27 * 12 / 5), Slab_Data::num(27 * 12 / 6))],
                    ['q' => 'Should I round my concrete order up or down?', 'a' => 'Always up. Plants sell in quarter or half-yard steps, and being short mid-pour costs a second delivery and can leave a cold joint.'],
                ],
            ],

            'concrete-bags-calculator' => [
                'subtitle' => 'Find how many 40, 50, 60 or 80-lb bags of concrete mix a slab or pad needs, with a waste allowance and the bag cost.',
                'meta' => sprintf('Concrete bag calculator: 80, 60, 50 and 40-lb bags for any slab. A 10x10x4 slab needs %d bags of 80-lb mix with %d%% waste.', $e10['bags80'], $waste),
                'answer' => sprintf('A 10 × 10 ft slab 4 inches thick needs <strong>%d bags of 80-lb mix</strong> (or %d bags of 60-lb mix) with %d%% waste. One 80-lb bag makes %s cubic feet, so it takes %d bags to make a cubic yard.', $e10['bags80'], $e10['bags60'], $waste, Slab_Data::num($c['bag_yields']['80'], 2), (int) round(27 / $c['bag_yields']['80'])),
                'sections' => [
                    ['How Bag Counts Are Worked Out', self::formula('Bags needed', 'Area (sq ft) × Thickness (in) ÷ 12 × (1 + waste %) ÷ bag yield', 'Bag yields: 80 lb = ' . Slab_Data::num($c['bag_yields']['80'], 2) . ' ft³, 60 lb = ' . Slab_Data::num($c['bag_yields']['60'], 2) . ' ft³, 50 lb = ' . Slab_Data::num($c['bag_yields']['50'], 3) . ' ft³, 40 lb = ' . Slab_Data::num($c['bag_yields']['40'], 2) . ' ft³. Always round up to a whole bag.')
                        . sprintf('<p>Bags make sense for small pours. Above about %s cubic yards, a ready-mix truck usually costs less and saves a lot of lifting. See %s for the break-even, or %s for a full bag-by-bag guide.</p>', Slab_Data::num($dl['ready_mix_wins_above_yd']), self::link('/ready-mix-vs-bagged-concrete/', 'ready-mix vs. bagged concrete'), self::link('/how-many-bags-of-concrete-do-i-need/', 'how many bags of concrete do I need'))],
                ],
            ],

            'concrete-mix-calculator' => [
                'subtitle' => 'Mixing concrete yourself from cement, sand and stone? Get the bags of portland cement, sand, gravel and water for your slab and mix ratio.',
                'meta' => sprintf('Concrete mix ratio calculator: cement bags, sand, gravel and water for 1:2:3, 1:2:4 and other mixes. A 10x10x4 slab at 1:2:3 needs %d bags of cement.', $mix['cement_bags']),
                'answer' => sprintf('A 10 × 10 ft slab 4 inches thick mixed at <strong>1:2:3</strong> (cement : sand : stone) with %d%% waste takes about <strong>%d bags of %d-lb portland cement</strong>, %s tons of sand, %s tons of gravel and %d gallons of water.', $waste, $mix['cement_bags'], $c['cement_bag_lb'], $nf($mix['sand_tons'], 1), $nf($mix['stone_tons'], 1), (int) round($mix['water_gal'])),
                'sections' => [
                    ['How the Mix Is Calculated', self::formula('Dry materials', 'Wet volume × ' . Slab_Data::num($c['dry_volume_factor'], 2) . ' = dry volume, split by the ratio', 'Example 1:2:3: cement is 1/6 of the dry volume, sand 2/6 and stone 3/6. One ' . $c['cement_bag_lb'] . '-lb bag of portland cement is about ' . Slab_Data::num($c['cement_bag_ft3']) . ' ft³.')
                        . sprintf('<p>Dry ingredients take up more room than the concrete they make, because sand fills the gaps between stones and cement paste fills the gaps in the sand. That is why the dry volume is about %d%% more than the slab volume. Water is set by a water-to-cement ratio of about %s by weight: less water makes stronger concrete, so add only enough to make it workable.</p>', (int) round(($c['dry_volume_factor'] - 1) * 100), Slab_Data::num($c['water_cement_ratio'], 2))],
                    ['Which Mix Ratio to Use', '<ul>' . implode('', array_map(function ($ratio, $use) {
                        return '<li><strong>' . esc_html($ratio) . ':</strong> ' . esc_html($use) . '</li>';
                    }, array_keys($c['mix_ratios']), $c['mix_ratios'])) . '</ul><p>Site-mixed concrete varies from batch to batch. For slabs that carry cars or need a set strength, ready-mix (ordered by PSI, see the ' . self::link('/ready-mix-concrete-calculator/', 'ready-mix calculator') . ') or bagged mix (see the ' . self::link('/concrete-bags-calculator/', 'bags calculator') . ') is more predictable.</p>'],
                ],
                'faqs' => [
                    ['q' => 'What is the best concrete mix ratio for a slab?', 'a' => '1:2:3 (one part cement, two parts sand, three parts stone by volume) is a common all-round mix for slabs, driveways and posts. 1:2:4 is leaner and used for footings and light slabs.'],
                    ['q' => 'How many bags of cement are in a cubic yard of 1:2:3 concrete?', 'a' => sprintf('About %d bags of %d-lb portland cement per cubic yard, including the dry-volume factor of %s and no waste.', self::mix_result(27, 1, 12, '1:2:3', 0)['cement_bags'], $c['cement_bag_lb'], Slab_Data::num($c['dry_volume_factor'], 2))],
                    ['q' => 'Why multiply by 1.54 for dry volume?', 'a' => sprintf('Loose dry ingredients have air gaps that disappear when the concrete is mixed and placed, so you need about %d%% more dry material than the finished volume.', (int) round(($c['dry_volume_factor'] - 1) * 100))],
                    ['q' => 'How much water do I add to a concrete mix?', 'a' => sprintf('Aim for a water-to-cement ratio of about %s by weight, about %s gallons per %d-lb bag of cement. Too much water makes weaker concrete that cracks more.', Slab_Data::num($c['water_cement_ratio'], 2), Slab_Data::num(round($c['cement_bag_lb'] * $c['water_cement_ratio'] / 8.34, 1)), $c['cement_bag_lb'])],
                ],
            ],

            'ready-mix-concrete-calculator' => [
                'subtitle' => 'Plan a ready-mix order: yards to order, price by PSI strength, number of trucks, and the delivery and short-load fees that small orders pay.',
                'meta' => sprintf('Ready-mix concrete calculator: yards to order, price by PSI (%s/yd³ for 3,000 PSI), trucks, delivery and short-load fees.', Slab_Data::money_range(Slab_Data::psi_price(3000), 0, '-')),
                'answer' => sprintf('A 20 × 20 ft slab 4 inches thick needs <strong>%s cubic yards</strong> of ready-mix (with %d%% waste, rounded up). At %s per yard for 3,000 PSI plus a %s delivery fee, expect about <strong>%s</strong> for the concrete. A 10 × 10 slab (%s yd³) is below most plants\' minimum load and may add a short-load fee of %s.', $nf($rm['order_yd']), $waste, esc_html(Slab_Data::money_range($rm['price'], 0, ' to ')), esc_html(Slab_Data::money($dl['fee_default'])), esc_html($mr(Slab_Data::round_pair($rm['total'], 10))), $nf($rm_small['order_yd']), esc_html($mr($dl['short_load_flat']))),
                'sections' => [
                    ['How a Ready-Mix Order Is Priced', '<ul>'
                        . '<li><strong>Concrete:</strong> priced per cubic yard by strength: ' . esc_html(Slab_Data::money_range(Slab_Data::psi_price(3000), 0, '–')) . ' for 3,000 PSI, plus ' . esc_html(Slab_Data::money_range($c['psi_step_price'], 0, '–')) . ' for each 500 PSI more.</li>'
                        . '<li><strong>Delivery:</strong> about ' . esc_html(Slab_Data::money($dl['fee_default'])) . ' per trip in this calculator; it varies by distance.</li>'
                        . '<li><strong>Short load:</strong> orders under about ' . esc_html(Slab_Data::range($dl['common_min_yd'], '–')) . ' yd³ often pay ' . esc_html(Slab_Data::money_range($dl['short_load_flat'], 0, '–')) . ' flat or ' . esc_html(Slab_Data::money_range($dl['short_load_per_yd'], 0, '–')) . ' per missing yard.</li>'
                        . '<li><strong>Time on site:</strong> each truck allows about ' . esc_html(Slab_Data::range($dl['unload_min_per_yd'], '–')) . ' minutes per yard to unload; waiting longer is billed.</li>'
                        . '<li><strong>Extras:</strong> pump (' . esc_html(Slab_Data::mr('pump.typical', 0, '–')) . '), fiber, accelerators, hot water in winter, and weekend or after-hours charges.</li>'
                        . '</ul><p>Call two or three plants with your yardage, strength and pour date; prices vary a lot by region. To see how the concrete fits into the whole job, use the ' . self::link('/', 'slab cost calculator') . ', and read our ' . self::link('/concrete-cost-per-cubic-yard-pricing-explained/', 'cost per cubic yard guide') . '.</p>'],
                ],
                'faqs' => [
                    ['q' => 'How much does a yard of ready-mix concrete cost?', 'a' => sprintf('About %s per cubic yard for standard 3,000 PSI concrete, and %s for 4,000 PSI, before delivery and any short-load fee.', Slab_Data::money_range(Slab_Data::psi_price(3000), 0, ' to '), Slab_Data::money_range(Slab_Data::psi_price(4000), 0, ' to '))],
                    ['q' => 'What is a short-load fee?', 'a' => sprintf('A charge for orders smaller than the plant\'s minimum (often %s yd³). It is usually %s flat or %s per yard you are short.', Slab_Data::range($dl['common_min_yd'], ' to '), $mr($dl['short_load_flat']), $mr($dl['short_load_per_yd']))],
                    ['q' => 'How much concrete does a ready-mix truck hold?', 'a' => sprintf('A standard mixer truck carries about %s cubic yards, so larger pours are split across several trucks scheduled to arrive one after another.', Slab_Data::range($dl['truck_capacity_yd'], ' to '))],
                    ['q' => 'When is ready-mix cheaper than bags?', 'a' => sprintf('Usually above about %s cubic yards. Below about %s yard, bags are often cheaper once delivery and short-load fees are counted.', Slab_Data::num($dl['ready_mix_wins_above_yd']), Slab_Data::num($dl['bags_win_below_yd']))],
                ],
            ],

            'concrete-waste-calculator' => [
                'subtitle' => 'Pick the waste allowance that fits your site, and see how much extra concrete it adds and what that costs compared with running short.',
                'meta' => sprintf('Concrete waste factor calculator: %d%% to %d%% extra by site condition, extra yards and cost. Why %d%% is the usual allowance.', (int) min(array_keys($c['waste_levels'])), (int) max(array_keys($c['waste_levels'])), $waste),
                'answer' => sprintf('For a 20 × 20 ft slab 4 inches thick, a <strong>%d%% waste allowance</strong> adds %s yd³ (about %s of ready-mix at %s per yard). Running short instead can cost a second delivery fee of %s plus a short-load fee of %s.', $waste, $nf($e20['order_yd'] - $e20['net_yd']), esc_html(Slab_Data::money(($e20['order_yd'] - $e20['net_yd']) * $c['ready_mix_default'], 0)), esc_html(Slab_Data::money($c['ready_mix_default'])), esc_html(Slab_Data::money($dl['fee_default'])), esc_html($mr($dl['short_load_flat']))),
                'sections' => [
                    ['Where the Extra Concrete Goes', '<p>Ground is never perfectly flat: a base that dips a quarter inch over a large slab can swallow a surprising amount of concrete. Forms bow a little under the weight of wet concrete, some spills from the chute or wheelbarrow, and some stays in the pump or mixer. The allowance below covers those losses so you finish the pour in one go.</p><ul>' . implode('', array_map(function ($pct, $text) {
                        return '<li><strong>' . (int) $pct . '%:</strong> ' . esc_html($text) . '</li>';
                    }, array_keys($c['waste_levels']), $c['waste_levels'])) . '</ul>'
                        . self::formula('Order volume', 'Net volume × (1 + waste % ÷ 100)', 'Then round up to the plant\'s order step.')
                        . '<p>More detail in our guide to the ' . self::link('/concrete-slab-waste-factor-explained/', 'concrete waste factor') . '.</p>'],
                ],
                'faqs' => [
                    ['q' => 'How much extra concrete should I order?', 'a' => sprintf('%d%% is the usual allowance for a formed slab. Use about %d%% on a very flat, well-compacted base with rigid forms, and %d%% or more on rough or dug ground.', $waste, (int) min(array_keys($c['waste_levels'])), 15)],
                    ['q' => 'Is it better to order too much or too little concrete?', 'a' => 'Too much. A little leftover can go into a small pad or post footing, while running short costs a second delivery, possible short-load fees and a weak cold joint.'],
                    ['q' => 'Does the waste factor apply to bags too?', 'a' => 'Yes. Add the same percentage before dividing by the bag yield, and round up to whole bags.'],
                ],
            ],

            'concrete-weight-calculator' => [
                'subtitle' => 'How much does a slab weigh? Get the wet and cured weight in pounds, US tons and metric tonnes for hauling, lifting or load checks.',
                'meta' => sprintf('Concrete weight calculator: a 20x20x4 slab weighs about %s lbs. Wet and cured weight in pounds, tons and tonnes at %d lb/ft³.', number_format($wt), $c['density_lb_ft3']),
                'answer' => sprintf('Normal-weight concrete weighs about <strong>%d lb per cubic foot</strong> (%s lb per cubic yard). A 20 × 20 ft slab 4 inches thick weighs about <strong>%s lbs</strong>, or %s US tons.', $c['density_lb_ft3'], number_format($c['density_lb_ft3'] * 27), number_format($wt), $nf($wt / 2000)),
                'sections' => [
                    ['How Weight Is Calculated', self::formula('Slab weight', 'Volume (ft³) × ' . $c['density_lb_ft3'] . ' lb/ft³', 'Divide by 2,000 for US tons, or multiply by 0.4536 for kilograms.')
                        . '<p>Weight matters when you haul old concrete away (dumpsters and trailers have weight limits), when a slab sits on a structure or a lift, and when you move precast pieces. Lightweight concrete with special aggregate weighs noticeably less, and heavily reinforced concrete a little more. For debris from a tear-out, the ' . self::link('/concrete-removal-cost-calculator/', 'removal calculator') . ' estimates both the tonnage and the cost.</p>'],
                ],
            ],

            'concrete-rebar-calculator' => [
                'title' => 'Rebar Calculator for Concrete Slab: Bars, Sticks & Chairs',
                'subtitle' => 'Lay out a #4 rebar grid for your slab: total bar footage with laps, number of 20-ft sticks, and how many chairs hold it up.',
                'meta' => sprintf('Rebar calculator for slabs: a 20x20 #4 grid at %d in needs %d ft of bar, %d twenty-foot sticks and %d chairs. Spacing, laps and edge cover.', $r['rebar_base_spacing_in'], (int) round($grid['lin_ft']), $grid['sticks'], $grid['chairs']),
                'answer' => sprintf('A 20 × 20 ft slab with #4 rebar at %d inches each way needs about <strong>%d linear feet</strong> of bar including laps, which is <strong>%d twenty-foot sticks</strong> and about %d chairs.', $r['rebar_base_spacing_in'], (int) round($grid['lin_ft']), $grid['sticks'], $grid['chairs']),
                'sections' => [
                    ['How the Grid Is Laid Out', sprintf('<p>Bars start %d inches in from each edge (so they stay covered by concrete), then run at the chosen spacing in both directions. The total length gets %d%% added for laps and offcuts; where two bars meet end to end they overlap about %d bar diameters (%d inches for #4). Chairs hold the grid near the middle of the slab, roughly every %s ft. This is the same grid our slab calculator draws in its blueprint.</p>', $r['edge_cover_in'], (int) round($r['splice_allowance'] * 100), $r['lap_bar_diameters'], $r['lap_in_no4'], Slab_Data::range($r['chair_grid_ft'], ' to '))
                        . '<p>Lighter foot-traffic slabs often use welded wire mesh instead; see the ' . self::link('/concrete-wire-mesh-calculator/', 'wire mesh calculator') . ' and our ' . self::link('/concrete-slab-reinforcement-rebar-vs-wire-mesh/', 'rebar vs. wire mesh guide') . '.</p>'],
                    ['Rebar for Common Slab Sizes', self::rebar_size_table()],
                ],
                'faqs' => [
                    ['q' => 'How much rebar do I need for a concrete slab?', 'a' => sprintf('For a #4 grid at %d inches each way, plan on about %s linear feet of bar per square foot of slab including laps. A 20 x 20 ft slab needs about %d ft (%d twenty-foot sticks) and %d chairs; the table on this page lists other sizes.', $r['rebar_base_spacing_in'], Slab_Data::num(round($grid['lin_ft'] / 400, 2)), (int) round($grid['lin_ft']), $grid['sticks'], $grid['chairs'])],
                    ['q' => 'What size rebar should I use for a 4 inch slab?', 'a' => sprintf('#3 (3/8 in) or #4 (1/2 in) bar. #4 at %d inches each way is the common choice for driveways and garage floors; #3 or welded wire mesh is enough for patios and walks. Keep the bar near mid-depth on chairs with at least %d inches of cover at the edges.', $r['rebar_base_spacing_in'], $r['edge_cover_in'])],
                    ['q' => 'What rebar spacing should I use for a residential slab?', 'a' => sprintf('%d inches each way is standard for residential slabs. Tighten to %d inches for RV pads, heavy trucks or poor soil; light foot-traffic slabs can use 24 inches or wire mesh.', $r['rebar_base_spacing_in'], $r['rebar_tight_spacing_in'])],
                    ['q' => 'Do I need rebar in a concrete slab?', 'a' => 'Not always. Rebar does not stop cracks; it keeps them tight so the pieces do not shift. Use it for slabs that carry vehicles, sit on clay or will hold a building. A patio or walkway is often fine with wire mesh or fiber mix and properly spaced control joints.'],
                ],
            ],

            'concrete-wire-mesh-calculator' => [
                'subtitle' => 'Work out how many sheets or rolls of 6x6 welded wire mesh cover your slab, with the overlap at the seams and the chairs to lift it.',
                'meta' => sprintf('Wire mesh calculator: sheets or rolls of 6x6 welded wire for a slab. A 20x20 slab takes %d sheets (5x10) or %d roll (5x150) with %d in overlaps.', $mesh['count'], $mesh_roll['count'], $r['mesh_overlap_in']),
                'answer' => sprintf('A 20 × 20 ft slab needs <strong>%d sheets</strong> of 5 × 10 ft welded wire mesh, or <strong>%d roll</strong> of 5 × 150 ft, with %d-inch overlaps at the seams, plus about %d chairs. Mesh costs roughly %s per sheet.', $mesh['count'], $mesh_roll['count'], $r['mesh_overlap_in'], $mesh['chairs'], esc_html(Slab_Data::money_range($r['mesh_sheet_price'], 0, ' to '))),
                'sections' => [
                    ['How Sheets and Rolls Are Counted', self::formula('Coverage per sheet', '(5 ft − ' . $r['mesh_overlap_in'] . ' in) × (10 ft − ' . $r['mesh_overlap_in'] . ' in)', 'Each seam overlaps one grid square (' . $r['mesh_overlap_in'] . ' in), so a 5 × 10 sheet covers 4.5 × 9.5 ft of new area. The tool tries both directions and keeps the one that needs fewer sheets.')
                        . sprintf('<p>Sheets lie flat and are easier to keep at mid-depth, so most pros prefer them for slabs. Rolls are cheaper per square foot but want to curl back up, so they must be flattened and tied down. Either way, mesh only helps if it sits in the middle of the slab: set it on chairs (about every %s ft) instead of pulling it up during the pour. Mesh runs about %s per sq ft installed, against %s for a #4 rebar grid (see the %s).</p>', Slab_Data::range($r['chair_grid_ft'], ' to '), esc_html(Slab_Data::money_range($r['mesh_range'], 2, ' to ')), esc_html(Slab_Data::money_range($r['rebar_range'], 2, ' to ')), self::link('/concrete-rebar-calculator/', 'rebar calculator'))],
                ],
                'faqs' => [
                    ['q' => 'How many sheets of wire mesh do I need for a 20x20 slab?', 'a' => sprintf('%d sheets of 5 x 10 ft mesh with %d-inch overlaps, or one 5 x 150 ft roll.', $mesh['count'], $r['mesh_overlap_in'])],
                    ['q' => 'How much should wire mesh overlap?', 'a' => sprintf('At least one full grid square, which is %d inches for 6x6 mesh, at every seam. Tie the overlaps with wire so they do not separate.', $r['mesh_overlap_in'])],
                    ['q' => 'Is wire mesh as good as rebar?', 'a' => 'For patios, walks and shed pads, mesh on chairs works well to hold shrinkage cracks tight. Driveways and garage floors that carry vehicles are better served by a #4 rebar grid.'],
                ],
            ],

            'concrete-form-calculator' => [
                'subtitle' => 'Count the form boards, stakes and screws to build the edge forms for a rectangular slab, and pick the right board depth for the thickness.',
                'meta' => sprintf('Concrete form calculator: boards, stakes and screws for slab forms. A 20x20x4 slab needs %d ft of %s forms, %d boards and %d stakes.', $form['perimeter'], $form['board'], $form['boards'], $form['stakes']),
                'answer' => sprintf('A 20 × 20 ft slab 4 inches thick needs <strong>%d linear feet</strong> of form, which is <strong>%d %s boards</strong> 12 ft long, about <strong>%d stakes</strong> (one every %s ft plus one at each board joint) and %d screws. Forms run about %s per linear foot, or %s here.', $form['perimeter'], $form['boards'], esc_html($form['board']), $form['stakes'], Slab_Data::num($s['stake_spacing_ft'][0]), $form['screws'], esc_html(Slab_Data::money($s['forms_default'], 2)), esc_html(Slab_Data::money($form['cost'], 0))),
                'sections' => [
                    ['How Form Material Is Counted', sprintf('<p>The forms run around the perimeter, 2 × (length + width). Each side gets enough whole boards to span it; board joints need a stake on each side, and stakes go every %s ft in between. Use a board at least as deep as the slab: a 2x4 is really 3.5 inches deep, which suits a 4-inch slab with the base bedded slightly below it, while a 2x6 (5.5 in) suits 5 and 6-inch slabs.</p>', Slab_Data::range($s['stake_spacing_ft'], ' to '))
                        . '<ul><li><strong>Stakes:</strong> wood or steel, driven outside the form and screwed to it, with their tops cut flush so the screed can slide over.</li><li><strong>Curves:</strong> use bendable hardboard or kerf-cut plywood, with stakes every 1 to 2 ft.</li><li><strong>Release:</strong> coat the inside face with form oil so the boards strip cleanly and can be reused.</li></ul>'
                        . '<p>Forms are one line of the full budget; the ' . self::link('/', 'slab cost calculator') . ' prices them with everything else.</p>'],
                ],
                'faqs' => [
                    ['q' => 'What size lumber do I use for concrete forms?', 'a' => 'Use boards at least as deep as the slab: 2x4 (3.5 in actual) for a 4-inch slab, and 2x6 (5.5 in actual) for a 5 or 6-inch slab.'],
                    ['q' => 'How far apart should form stakes be?', 'a' => sprintf('Every %s feet along straight forms, plus one on each side of every board joint, and closer on curves.', Slab_Data::range($s['stake_spacing_ft'], ' to '))],
                    ['q' => 'How much do concrete forms cost?', 'a' => sprintf('About %s per linear foot of edge for lumber, stakes and setup, so a 20 x 20 ft slab (%d ft of edge) runs about %s.', Slab_Data::money_range($s['forms_range'], 2, ' to '), $form['perimeter'], Slab_Data::money_range([$form['perimeter'] * $s['forms_range'][0], $form['perimeter'] * $s['forms_range'][1]], 0, ' to '))],
                ],
            ],

            'gravel-base-calculator' => [
                'title' => 'Gravel Base Calculator: How Much Gravel Under a Slab',
                'subtitle' => 'Find the cubic yards and tons of crushed stone to put under a slab, with extra for compaction so the base ends up at full depth.',
                'meta' => sprintf('Gravel base calculator: yards and tons of crushed stone under a slab. 400 sq ft at %d in needs %s yd³ (%s tons) including compaction.', $s['gravel_depth_in'], $nf($gravel_yd), $nf($gravel_yd * $s['gravel_tons_per_yd'])),
                'answer' => sprintf('A %d-inch compacted gravel base under a 20 × 20 ft slab takes about <strong>%s cubic yards</strong> of crushed stone, or <strong>%s tons</strong>, including %d%% extra for compaction. At %s per yard that is roughly %s.', $s['gravel_depth_in'], $nf($gravel_yd), $nf($gravel_yd * $s['gravel_tons_per_yd']), (int) round(($s['gravel_compaction'] - 1) * 100), esc_html(Slab_Data::money_range($s['gravel_range'], 0, ' to ')), esc_html(Slab_Data::money_range([$gravel_yd * $s['gravel_range'][0], $gravel_yd * $s['gravel_range'][1]], 0, ' to '))),
                'sections' => [
                    ['How the Base Is Calculated', self::formula('Gravel to order', 'Area × Depth (in) ÷ 12 ÷ 27 × ' . Slab_Data::num($s['gravel_compaction'], 2), 'Multiply yards by about ' . Slab_Data::num($s['gravel_tons_per_yd'], 1) . ' to get tons; most yards sell stone by the ton.')
                        . '<p>The base spreads the slab\'s load and lets water drain away from its underside. Spread it in thin layers and run a plate compactor over each one. See ' . self::link('/how-much-gravel-do-i-need-under-a-concrete-slab/', 'how much gravel you need under a slab') . ' for stone types and when a base can be thinner.</p>'],
                    ['How Much Gravel Under a Concrete Slab, by Size', self::gravel_size_table()],
                ],
                'faqs' => [
                    ['q' => 'How much gravel do I need under a concrete slab?', 'a' => sprintf('Multiply the slab area by the base depth, divide by 12 and by 27 for cubic yards, and add %d%% for compaction. A %d-inch base under a 20 x 20 ft slab is about %s cubic yards, or %s tons of crushed stone.', (int) round(($s['gravel_compaction'] - 1) * 100), $s['gravel_depth_in'], $nf($gravel_yd), $nf($gravel_yd * $s['gravel_tons_per_yd']))],
                    ['q' => 'How thick should the gravel base be under a slab?', 'a' => sprintf('%s inches of compacted crushed stone for patios, sheds, driveways and garage floors. Go thicker on soft or wet soil, and compact it in layers of no more than about 4 inches.', Slab_Data::range($s['gravel_depth_range'], ' to '))],
                    ['q' => 'What kind of gravel goes under a concrete slab?', 'a' => 'Crushed stone with fines (often sold as 3/4-inch minus, crusher run or road base) compacts into a firm, level layer. Clean 3/4-inch stone drains better and is used where water is a concern. Avoid round pea gravel, which rolls and does not lock together.'],
                    ['q' => 'How many tons of gravel per cubic yard?', 'a' => sprintf('About %s tons per cubic yard for crushed stone. Most suppliers sell by the ton, so convert your yards before you order.', Slab_Data::num($s['gravel_tons_per_yd'], 1))],
                ],
            ],

            'concrete-footing-calculator' => [
                'subtitle' => 'Concrete for continuous strip footings and round piers or sonotubes, in cubic yards and 80-lb bags, with a waste allowance.',
                'meta' => sprintf('Footing and pier calculator: concrete for strip footings and sonotubes. An 80 ft run of 12x12 in footing needs %s yd³ with %d%% waste.', $nf($foot_yd), $waste),
                'answer' => sprintf('An 80 ft run of 12 × 12 inch strip footing needs <strong>%s cubic yards</strong> of concrete with %d%% waste. A 12-inch sonotube 4 ft deep holds about %s cubic feet, or %d bags of 80-lb mix.', $nf($foot_yd), $waste, $nf(M_PI * 0.25 * 4), Slab_Data::bags(M_PI * 0.25 * 4 * (1 + $waste / 100))),
                'sections' => [
                    ['Strip Footings and Round Piers', self::formula('Strip footing', 'Length (ft) × Width (in) ÷ 12 × Depth (in) ÷ 12 ÷ 27', 'Round pier: 3.1416 × (diameter ÷ 2)² × depth, all in feet, then ÷ 27 for yards.')
                        . sprintf('<p>Footings must sit on undisturbed soil and, in cold climates, below the frost line (often %s inches in northern states). Your building code and plans set the size; this tool only counts the concrete. Footings dug straight into the ground use more concrete than the drawing shows, so pick a higher waste allowance for trenches.</p>', Slab_Data::range($sd['freeze_thaw_ft']))],
                ],
            ],

            'concrete-labor-cost-calculator' => [
                'title' => 'Concrete Labor Cost Calculator (2026 Rates per Sq Ft)',
                'subtitle' => 'Estimate what a crew charges to place and finish a slab, by finish and site difficulty, with the crew hours behind the price.',
                'meta' => sprintf('Concrete labor cost calculator: %s/sq ft for placing and finishing, crew hours by finish. A 400 sq ft broom-finish slab is about %s.', Slab_Data::money_range($lab['sqft_range'], 2, '-'), Slab_Data::money(400 * $lab['sqft_default'], 0)),
                'answer' => sprintf('Placing and finishing a 400 sq ft broom-finished slab costs about <strong>%s</strong> in labor at %s per sq ft, which is roughly %d crew-hours. Stamped finishes run about %s per sq ft.', esc_html(Slab_Data::money(400 * $lab['sqft_default'], 0)), esc_html(Slab_Data::money($lab['sqft_default'], 2)), (int) round(400 * $lab['crew_hours_per_sqft']), esc_html(Slab_Data::money($lab['sqft_stamped'], 2))),
                'sections' => [
                    ['How Labor Is Estimated', sprintf('<p>Crews price small and mid-size slabs per square foot (%s), which covers setting forms, placing, screeding, floating, finishing, jointing and curing. Behind that is time: about %s crew-hours per square foot for a broom finish, more for stamped work or hard-to-reach sites, at %s per crew-hour (%s per finisher). Site prep, base, concrete and rebar are separate lines. Our guide to %s explains both methods.</p>', esc_html(Slab_Data::money_range($lab['sqft_range'], 2, ' to ')), Slab_Data::num($lab['crew_hours_per_sqft'], 3), esc_html(Slab_Data::money_range($lab['crew_hour'], 0, ' to ')), esc_html(Slab_Data::money_range($lab['worker_hour'], 0, ' to ')), self::link('/how-to-estimate-concrete-labor-cost/', 'estimating concrete labor cost'))],
                    ['Labor Cost by Slab Size', self::labor_size_table()],
                ],
                'faqs' => [
                    ['q' => 'How much does labor cost to pour a concrete slab?', 'a' => sprintf('About %s per sq ft for placing and finishing with a broom finish, so a 400 sq ft slab runs about %s in labor. Stamped and decorative finishes cost about %s per sq ft. Small jobs often have a minimum charge.', Slab_Data::money_range($lab['sqft_range'], 2, ' to '), Slab_Data::money_range([400 * $lab['sqft_range'][0], 400 * $lab['sqft_range'][1]], 0, ' to '), Slab_Data::money($lab['sqft_stamped'], 2))],
                    ['q' => 'How much do concrete finishers charge per hour?', 'a' => sprintf('About %s per finisher-hour, or %s per crew-hour for a typical crew, depending on the area and the season.', Slab_Data::money_range($lab['worker_hour'], 0, ' to '), Slab_Data::money_range($lab['crew_hour'], 0, ' to '))],
                    ['q' => 'Is labor more than materials on a concrete slab?', 'a' => 'On most small and mid-size slabs, labor and the contractor\'s overhead make up roughly half of the installed price or more. Doing the site prep and forms yourself and hiring a crew only for pour day is a common way to save.'],
                ],
            ],

            'concrete-removal-cost-calculator' => [
                'subtitle' => 'Estimate what it costs to break out and haul away an old concrete slab, and how many tons of debris it makes.',
                'meta' => sprintf('Concrete removal cost calculator: about %s/sq ft to break out and haul a 4 in slab, more with rebar. Debris tonnage included.', Slab_Data::money_range($s['demo_range'], 0, '-')),
                'answer' => sprintf('Removing a 4-inch concrete slab costs about <strong>%s per sq ft</strong>, more if it has rebar or is thicker. A 400 sq ft, 4-inch slab makes about <strong>%s tons</strong> of debris to haul away.', esc_html(Slab_Data::money_range($s['demo_range'], 0, ' to ')), $nf(400 * 4 / 12 * $c['density_lb_ft3'] / 2000, 1)),
                'sections' => [
                    ['What Drives Removal Cost', '<p>Thickness and steel are the big factors: a thicker slab takes longer to break, and rebar or mesh has to be cut free. Access matters too: a skid steer that can drive up to the slab is much faster than hand work with a jackhammer and wheelbarrow. Disposal is charged by weight or by the load, so check the dumpster\'s weight limit before ordering one; the ' . self::link('/concrete-weight-calculator/', 'weight calculator') . ' helps. Clean concrete can often go to a recycler for less than landfill rates.</p>'],
                ],
            ],

            'concrete-cost-per-square-foot-calculator' => [
                'subtitle' => 'See what a slab costs per square foot, installed, for your size, thickness and finish, and how that compares with typical prices.',
                'meta' => sprintf('Concrete cost per square foot calculator: %s/sq ft installed for a typical 4 in slab. Per-sq-ft prices by thickness and finish, plus a free estimator.', Slab_Data::money_range($psf, 0, '-')),
                'answer' => sprintf('A typical 4-inch concrete slab costs <strong>%s per sq ft</strong> installed with a broom finish (based on a 20 × 20 ft slab). Small slabs cost more per foot because fixed costs are spread over less area. Change the size and options in the calculator below to see your own per-square-foot price.', esc_html(Slab_Data::money_range($psf, 0, ' to '))),
                'sections' => [
                    ['Cost per Square Foot by Thickness', self::psf_table()],
                    ['Why the Per-Foot Price Changes', sprintf('<p>Concrete, rebar and finishing scale with area, but the delivery fee, the trip to the site and setup time do not. A 10 × 10 slab therefore works out to %s per sq ft installed, while a 24 × 24 garage at 5 inches is %s. Use the per-foot price to compare quotes of a similar size, and use the total when the sizes differ. Our %s has the full matrix by finish.</p>', esc_html(Slab_Data::money_range(Slab_Data::psf(10, 10, 4), 0, ' to ')), esc_html(Slab_Data::money_range(Slab_Data::garage_psf(), 0, ' to ')), self::link('/concrete-cost-per-square-foot-pricing-matrix/', 'cost per square foot guide'))],
                ],
                'faqs' => [
                    ['q' => 'How much does concrete cost per square foot?', 'a' => sprintf('About %s per sq ft installed for a 4-inch broom-finished slab, and %s per sq ft in materials if you do it yourself.', Slab_Data::money_range($psf, 0, ' to '), Slab_Data::money_range(Slab_Data::diy_psf(), 2, ' to '))],
                    ['q' => 'Why do small slabs cost more per square foot?', 'a' => 'Delivery fees, minimum charges and setup time are the same whether the slab is 100 or 400 sq ft, so they add more per foot on a small slab.'],
                    ['q' => 'How much more is a 6-inch slab per square foot?', 'a' => sprintf('On a 20 x 20 ft slab, going from 4 to 6 inches moves the installed price from %s to %s per sq ft, mostly from the extra concrete.', Slab_Data::money_range($psf, 0, ' to '), Slab_Data::money_range(Slab_Data::psf(20, 20, 6), 0, ' to '))],
                ],
            ],

            'concrete-project-cost-calculator' => [
                'subtitle' => 'Build a full slab budget line by line: site prep, base, forms, vapor barrier, reinforcement, concrete, finish, labor, delivery and contingency.',
                'meta' => sprintf('Concrete project cost calculator: every line item of a slab job. A 20x20x4 slab comes to about %s with the default scope. Free and editable.', Slab_Data::money($e20['calculator_total'], 0)),
                'answer' => sprintf('With the calculator\'s default scope (ready-mix, forms, vapor barrier, #4 rebar, broom finish and contractor labor), a 20 × 20 ft slab 4 inches thick comes to about <strong>%s</strong> including %d%% contingency. Switch items on or off and edit any rate below.', esc_html(Slab_Data::money($e20['calculator_total'], 0)), $sd['contingency_pct']),
                'sections' => [
                    ['Line Items in a Slab Budget', self::line_item_table($e20)],
                    ['Costs a Slab Budget Often Misses', '<ul><li><strong>Permits and inspections</strong> (' . esc_html(Slab_Data::mr('permit.flatwork', 0, '–')) . ' for a typical residential slab).</li><li><strong>Old slab removal</strong> (' . esc_html(Slab_Data::mr('site.demo_range', 0, '–')) . ' per sq ft).</li><li><strong>Pumping</strong> if the truck cannot reach (' . esc_html(Slab_Data::mr('pump.typical', 0, '–')) . ').</li><li><strong>Sealer, drains, sleeves for utilities</strong> and any landscaping repair after the work.</li></ul><p>Already have a contractor\'s number? Put it into the ' . self::link('/concrete-quote-checker/', 'concrete quote checker') . ' to see whether it is in a fair range.</p>'],
                ],
                'faqs' => [
                    ['q' => 'What is included in a concrete slab project cost?', 'a' => 'Site prep and gravel base, forms, vapor barrier, reinforcement, the concrete itself, finishing, labor, delivery or pumping, and a contingency. Permits and removal of an old slab are separate.'],
                    ['q' => 'How much contingency should I add to a concrete project?', 'a' => sprintf('This calculator adds %d%% by default. Use more on sites with unknown soil, tree roots or tight access.', $sd['contingency_pct'])],
                    ['q' => 'How accurate is a project cost calculator?', 'a' => 'It is a planning budget built from typical U.S. rates that you can edit. Local prices, site conditions and a contractor\'s overhead decide the final number, so compare it with real quotes.'],
                ],
            ],
        ];

        return self::$cache;
    }

    /** Common slab sizes for the per-size tables. */
    private static function common_sizes() {
        return [[10, 10], [12, 16], [20, 20], [24, 24], [30, 40], [40, 60]];
    }

    /** #4 rebar footage, sticks and chairs for common sizes. */
    private static function rebar_size_table() {
        $r = Slab_Data::get('reinforcement');
        $o = '<div class="table-responsive"><table class="data-table"><thead><tr><th>Slab</th><th>Bar (lin ft)</th><th>20-ft sticks</th><th>Chairs</th></tr></thead><tbody>';
        foreach (self::common_sizes() as $sz) {
            $g = Slab_Data::rebar_grid($sz[1], $sz[0], $r['rebar_base_spacing_in']);
            $o .= '<tr><td><strong>' . $sz[0] . ' × ' . $sz[1] . ' ft</strong></td><td>' . number_format(round($g['lin_ft'])) . '</td><td>' . (int) $g['sticks'] . '</td><td>' . (int) $g['chairs'] . '</td></tr>';
        }
        return $o . '</tbody></table><div class="table-caption">#4 bar at ' . (int) $r['rebar_base_spacing_in'] . ' in each way, ' . (int) $r['edge_cover_in'] . ' in edge cover, ' . (int) round($r['splice_allowance'] * 100) . '% added for laps.</div></div>';
    }

    /** Gravel yards and tons under common slab sizes at the low and high base depth. */
    private static function gravel_size_table() {
        $s = Slab_Data::get('site');
        $dep = $s['gravel_depth_range'];
        $o = '<div class="table-responsive"><table class="data-table"><thead><tr><th>Slab</th><th>' . (int) $dep[0] . ' in base</th><th>' . (int) $dep[1] . ' in base</th></tr></thead><tbody>';
        foreach (self::common_sizes() as $sz) {
            $o .= '<tr><td><strong>' . $sz[0] . ' × ' . $sz[1] . ' ft</strong></td>';
            foreach ($dep as $in) {
                $yd = $sz[0] * $sz[1] * $in / 12 / 27 * $s['gravel_compaction'];
                $o .= '<td>' . esc_html(Slab_Data::num(round($yd, 1), 1)) . ' yd³ (' . esc_html(Slab_Data::num(round($yd * $s['gravel_tons_per_yd'], 1), 1)) . ' tons)</td>';
            }
            $o .= '</tr>';
        }
        return $o . '</tbody></table><div class="table-caption">Compacted crushed stone, including ' . (int) round(($s['gravel_compaction'] - 1) * 100) . '% extra for compaction, at about ' . esc_html(Slab_Data::num($s['gravel_tons_per_yd'], 1)) . ' tons per yard.</div></div>';
    }

    /** Placing and finishing labor for common slab sizes (broom finish). */
    private static function labor_size_table() {
        $lab = Slab_Data::get('labor');
        $o = '<div class="table-responsive"><table class="data-table"><thead><tr><th>Slab</th><th>Area</th><th>Labor (broom finish)</th><th>Crew-hours</th></tr></thead><tbody>';
        foreach (self::common_sizes() as $sz) {
            $a = $sz[0] * $sz[1];
            $o .= '<tr><td><strong>' . $sz[0] . ' × ' . $sz[1] . ' ft</strong></td><td>' . number_format($a) . ' sq ft</td><td>' . esc_html(Slab_Data::money_range(Slab_Data::round_pair([$a * $lab['sqft_range'][0], $a * $lab['sqft_range'][1]], 10), 0, '–')) . '</td><td>' . (int) round($a * $lab['crew_hours_per_sqft']) . '</td></tr>';
        }
        return $o . '</tbody></table><div class="table-caption">Placing, screeding, finishing, jointing and curing at ' . esc_html(Slab_Data::money_range($lab['sqft_range'], 2, '–')) . ' per sq ft. Concrete, base, forms and rebar are separate.</div></div>';
    }

    /** $/sq ft installed by thickness for three slab sizes. */
    private static function psf_table() {
        $sizes = [[10, 10], [20, 20], [24, 24]];
        $o = '<div class="table-responsive"><table class="data-table"><thead><tr><th>Slab</th><th>4 in</th><th>5 in</th><th>6 in</th></tr></thead><tbody>';
        foreach ($sizes as $sz) {
            $o .= '<tr><td><strong>' . $sz[0] . ' × ' . $sz[1] . ' ft</strong></td>';
            foreach ([4, 5, 6] as $t) {
                $o .= '<td>' . esc_html(Slab_Data::money_range(Slab_Data::psf($sz[0], $sz[1], $t), 0, '–')) . '</td>';
            }
            $o .= '</tr>';
        }
        $o .= '</tbody></table><div class="table-caption">' . esc_html(Slab_Data::cost_basis_note()) . '</div></div>';
        return $o;
    }

    /** The default 20x20 line items as a table. */
    private static function line_item_table($e) {
        $sd = Slab_Data::all();
        $rows = [
            ['Ready-mix concrete + delivery', number_format($e['order_yd'], 2) . ' yd³ at ' . Slab_Data::money($sd['concrete']['ready_mix_default']) . '/yd³ + ' . Slab_Data::money($sd['delivery']['fee_default']), $e['ready_mix']],
            ['Forms', $e['perimeter'] . ' lin ft at ' . Slab_Data::money($sd['site']['forms_default'], 2), $e['forms']],
            ['Vapor barrier', number_format($e['area']) . ' sq ft at ' . Slab_Data::money($sd['site']['vapor_default'], 2), $e['vapor']],
            ['#4 rebar grid', number_format($e['area']) . ' sq ft at ' . Slab_Data::money($sd['reinforcement']['rebar_default'], 2), $e['rebar']],
            ['Broom finish', number_format($e['area']) . ' sq ft at ' . Slab_Data::money($sd['finish']['broom']['default'], 2), $e['broom']],
            ['Labor', number_format($e['area']) . ' sq ft at ' . Slab_Data::money($sd['labor']['sqft_default'], 2), $e['labor']],
            ['Contingency', $sd['contingency_pct'] . '% of the above', $e['calculator_total'] - $e['direct']],
        ];
        $o = '<div class="table-responsive"><table class="data-table"><thead><tr><th>Item</th><th>Basis (20 × 20 ft, 4 in)</th><th>Cost</th></tr></thead><tbody>';
        foreach ($rows as $row) {
            $o .= '<tr><td>' . esc_html($row[0]) . '</td><td>' . esc_html($row[1]) . '</td><td>' . esc_html(Slab_Data::money($row[2], 0)) . '</td></tr>';
        }
        $o .= '<tr><td><strong>Total</strong></td><td>Default calculator scope</td><td><strong>' . esc_html(Slab_Data::money($e['calculator_total'], 0)) . '</strong></td></tr>';
        return $o . '</tbody></table></div>';
    }
}
