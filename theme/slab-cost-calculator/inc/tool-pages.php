<?php
/**
 * TOOL PAGES: FENCE POSTS, STEPS, BAGS VS. READY-MIX
 *
 * Calculator pages served by a rewrite rule (no Page rows in the database,
 * see inc/custom-post-types.php), rendered by
 * page-templates/template-tool-page.php. Each entry has the tool's markup
 * part (template-parts/tools/), its title, a short answer with real
 * numbers, its own sections and FAQ. Every figure comes from Slab_Data, and
 * the JS (supporting-calculators.js) uses the same maths.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_Tool_Pages {

    private static $cache = null;

    /** Slug => [tool part, menu title, card description]. */
    public static function directory() {
        return [
            'fence-post-concrete-calculator' => ['fenceposts', 'Fence Post Concrete Calculator', 'Bags of regular or fast-setting mix per post hole.'],
            'concrete-steps-calculator' => ['steps', 'Concrete Steps Calculator', 'Concrete, bags and cost for poured steps and a landing.'],
            'bags-vs-ready-mix-calculator' => ['breakeven', 'Bags vs. Ready-Mix Calculator', 'Where a truck gets cheaper than bags, with your prices.'],
        ];
    }

    public static function exists($slug) {
        return isset(self::directory()[$slug]);
    }

    public static function route_regex() {
        return '^(' . implode('|', array_map('preg_quote', array_keys(self::directory()))) . ')/?$';
    }

    public static function get($slug) {
        $all = self::pages();
        return isset($all[$slug]) ? $all[$slug] : null;
    }

    private static function link($path, $text) {
        return '<a href="' . esc_url(home_url($path)) . '">' . esc_html($text) . '</a>';
    }

    private static function table($head, $rows, $caption = '') {
        $o = '<div class="table-responsive"><table class="data-table"><thead><tr>';
        foreach ($head as $h) {
            $o .= '<th>' . esc_html($h) . '</th>';
        }
        $o .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $o .= '<tr>';
            foreach ($row as $i => $cell) {
                $o .= '<td>' . ($i === 0 ? '<strong>' . esc_html($cell) . '</strong>' : esc_html($cell)) . '</td>';
            }
            $o .= '</tr>';
        }
        $o .= '</tbody></table>' . ($caption ? '<div class="table-caption">' . esc_html($caption) . '</div>' : '') . '</div>';
        return $o;
    }

    /* ------------------------------ defaults ------------------------------ */

    /** Starting example for the fence post tool: 4x4, 6 ft fence. */
    public static function post_default() {
        $p = Slab_Data::get('posts');
        $height_ft = 6;
        $bury_in = (int) round($height_ft * 12 * $p['depth_fraction'][0] / 6) * 6; // 24 in for a 6 ft fence
        $depth = $bury_in + $p['gravel_in'];
        $dia = Slab_Data::post_hole_diameter('4x4');
        return [
            'post' => '4x4', 'height_ft' => $height_ft, 'count' => 10, 'bury_in' => $bury_in,
            'depth_in' => $depth, 'hole_in' => $dia,
            'one' => Slab_Data::post_hole('4x4', $dia, $depth, 1),
            'ten' => Slab_Data::post_hole('4x4', $dia, $depth, 10),
        ];
    }

    /** Starting example for the steps tool: 3 steps, 4 ft wide, 7 x 11 in, 3 ft landing. */
    public static function steps_default() {
        $st = Slab_Data::get('steps');
        return [
            'n' => 3, 'width_ft' => 4, 'rise_in' => $st['riser_default_in'], 'tread_in' => $st['tread_default_in'],
            'landing_ft' => $st['landing_min_in'] / 12,
            'v' => Slab_Data::steps_volume(3, 4, $st['riser_default_in'], $st['tread_default_in'], $st['landing_min_in'] / 12),
        ];
    }

    /** Contractor price for N steps of a given width (per-step price is for 4 ft wide). */
    public static function steps_installed($n, $width_ft) {
        $per = Slab_Data::get('steps.installed_per_step');
        $k = max(1, $width_ft / 4);
        return Slab_Data::round_pair([$per[0] * $n * $k, $per[1] * $n * $k], 10);
    }

    /** DIY concrete bill for a volume: the cheaper of bags (with mixer) and a truck. */
    public static function concrete_bill($net_yd) {
        $c = Slab_Data::bags_vs_truck($net_yd, true);
        return $c['cheaper'] === 'bags' ? [$c['bag_cost'], 'bags'] : [$c['truck_cost'], 'ready-mix'];
    }

    /* -------------------------------- pages -------------------------------- */

    private static function pages() {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $sd = Slab_Data::all();
        $c = $sd['concrete'];
        $p = $sd['posts'];
        $st = $sd['steps'];
        $dl = $sd['delivery'];
        $waste = $c['waste_pct'];
        $nf = function ($n, $d = 1) {
            return number_format($n, $d);
        };
        $money = function ($n) {
            return Slab_Data::money($n, 0);
        };

        /* Fence posts */
        $pd = self::post_default();
        $per80 = $pd['one']['per_hole_80'];
        $perfast = $pd['one']['per_hole_fast'];
        $post_rows = [];
        foreach ([4, 5, 6, 8] as $h) {
            $bury = (int) round($h * 12 * $p['depth_fraction'][0] / 6) * 6;
            $depth = $bury + $p['gravel_in'];
            foreach (['4x4', '6x6'] as $key) {
                if ($h < 6 && $key === '6x6') {
                    continue;
                }
                $dia = Slab_Data::post_hole_diameter($key);
                $r = Slab_Data::post_hole($key, $dia, $depth, 1);
                $post_rows[] = [$h . ' ft fence, ' . $key, $depth . ' in deep × ' . $dia . ' in wide', $nf($r['per_hole_80']), $nf($r['per_hole_fast']), Slab_Data::money($r['per_hole_ft3'] / $c['bag_yields']['80'] * $c['bag_price_default'], 2)];
            }
        }
        $chain_rows = [];
        foreach (['pipe2' => 'Line post', 'pipe3' => 'Corner / end / gate post'] as $key => $role) {
            $dia = Slab_Data::post_hole_diameter($key);
            $r = Slab_Data::post_hole($key, $dia, 30, 1);
            $chain_rows[] = [$role . ' (' . $p['sizes'][$key][0] . ')', $dia . ' in × 30 in', $nf($r['per_hole_80']), $nf($r['per_hole_fast'])];
        }
        $cost_ft3_80 = $c['bag_price_default'] / $c['bag_yields']['80'];
        $cost_ft3_fast = $p['fast_set_price'] / $p['fast_set_yield'];

        /* Steps */
        $sv = self::steps_default();
        $v = $sv['v'];
        $steps_rows = [];
        foreach ([2, 3, 4, 5] as $n) {
            $vv = Slab_Data::steps_volume($n, 4, $st['riser_default_in'], $st['tread_default_in'], 0);
            $bill = self::concrete_bill($vv['net_yd']);
            $steps_rows[] = [$n . ' steps (' . Slab_Data::num($n * $st['riser_default_in']) . ' in rise)', $nf($vv['order_yd'], 2) . ' yd³', number_format($vv['bags80']), $money($bill[0]) . ' (' . $bill[1] . ')', Slab_Data::money_range(self::steps_installed($n, 4), 0, '–')];
        }
        $sv_bill = self::concrete_bill($v['net_yd']);

        /* Bags vs. truck */
        $be = Slab_Data::break_even_yd(true);
        $be_hand = Slab_Data::break_even_yd(false);
        $be_sqft = (int) round($be * 27 * 12 / 4 / 5) * 5;
        $bt_rows = [];
        foreach ([0.5, 1, 1.5, 2, 3, 4] as $yd) {
            $x = Slab_Data::bags_vs_truck($yd, true);
            $bt_rows[] = [Slab_Data::num($yd) . ' yd³ (≈ ' . number_format(round($yd * 27 * 12 / 4 / 5) * 5) . ' sq ft at 4 in)', number_format($x['bags']) . ' bags, ' . Slab_Data::num(round($x['mix_hours'], 1)) . ' h', $money($x['bag_cost']), $money($x['truck_cost']) . ($x['short'] ? ' *' : ''), $x['cheaper'] === 'bags' ? 'Bags' : 'Ready-mix'];
        }
        $short_mid = array_sum($dl['short_load_flat']) / 2;
        $mixer_mid = array_sum($c['mixer_rental_day']) / 2;

        self::$cache = [

            'fence-post-concrete-calculator' => [
                'title' => 'Fence Post Concrete Calculator: Bags per Post',
                'h1' => 'Fence Post Concrete Calculator: How Many Bags per Post?',
                'meta' => sprintf('How many bags of concrete per fence post? A 4x4 post in a %d in hole %d in deep takes %s bags of 80-lb mix or %s of 50-lb fast-setting. Free calculator.', $pd['hole_in'], $pd['depth_in'], $nf($per80), $nf($perfast)),
                'subtitle' => 'Enter your posts, hole size and depth to get the bags of regular or fast-setting concrete mix for the whole fence, with the gravel under each post and the post itself taken out.',
                'answer' => sprintf('A 4x4 fence post for a %d ft fence goes in a hole about <strong>%d in wide and %d in deep</strong> (%d in of post in the ground plus %d in of gravel). It takes about <strong>%s bags of 80-lb concrete mix</strong> or <strong>%s bags of 50-lb fast-setting mix</strong> per post, with %d%% waste; ten posts need %d bags of 80-lb mix or %d bags of fast-setting.', $pd['height_ft'], $pd['hole_in'], $pd['depth_in'], $pd['bury_in'], $p['gravel_in'], $nf($per80), $nf($perfast), $waste, $pd['ten']['bags80'], $pd['ten']['bags_fast']),
                'sections' => [
                    ['How Deep and Wide to Dig', sprintf('<p>Two rules of thumb set the hole. <strong>Depth:</strong> bury %s to %s of the post\'s above-ground height, then add %d in for a gravel base that lets water drain away from the end grain. In cold climates the bottom must also be below the frost line, often %s in in northern states, or frost will heave the post. <strong>Width:</strong> about %d times the post\'s width, so a 4x4 (3.5 in actual) gets a %d in hole and a 6x6 (5.5 in) a %d in hole.</p>', '1/3', '1/2', $p['gravel_in'], Slab_Data::range($sd['freeze_thaw_ft'], '–'), $p['hole_factor'], Slab_Data::post_hole_diameter('4x4'), Slab_Data::post_hole_diameter('6x6'))
                        . self::table(['Fence and post', 'Hole', '80-lb bags / post', '50-lb fast-set / post', 'Regular mix cost / post'], $post_rows, sprintf('Holes dug to bury 1/3 of the above-ground height plus %d in of gravel; %d%% waste included. Fractions of a bag carry over to the next hole.', $p['gravel_in'], $waste))],
                    ['Regular Mix or Fast-Setting?', '<p>Both make a solid post. The difference is time and price:</p><ul>'
                        . sprintf('<li><strong>Regular 80-lb mix</strong> costs about %s per cubic foot (%s a bag). Mix it in a wheelbarrow, fill the hole, then brace the post for a day or two before hanging boards.</li>', Slab_Data::money($cost_ft3_80, 2), Slab_Data::money($c['bag_price_default'], 2))
                        . sprintf('<li><strong>Fast-setting %d-lb mix</strong> costs about %s per cubic foot (%s a bag), roughly twice as much. Pour it into the hole dry, add water, and it sets in %s minutes; you can hang rails and boards after about %d hours.</li>', $p['fast_set_lb'], Slab_Data::money($cost_ft3_fast, 2), Slab_Data::money($p['fast_set_price'], 2), Slab_Data::range($p['fast_set_minutes'], '–'), $p['fast_set_hold_h'])
                        . '</ul><p>For a long fence, regular mix saves real money; for a gate post you need to hang today, or a few posts on a weekend, fast-setting is worth it. Either way, crown the top of the concrete so rain runs away from the post.</p>'],
                    ['Chain Link Posts', '<p>Chain link uses steel pipe. Line posts are the thinner pipe; corner, end and gate posts are thicker and take the wire tension, so they get the bigger hole.</p>'
                        . self::table(['Post', 'Hole', '80-lb bags / post', '50-lb fast-set / post'], $chain_rows)],
                    ['Setting a Post Step by Step', '<ol><li>Dig the hole to depth with a post-hole digger or auger, a little wider at the bottom than the top.</li>'
                        . sprintf('<li>Put in %d in of gravel and tamp it, so water drains away from the post\'s end.</li>', $p['gravel_in'])
                        . '<li>Set the post, check it for plumb on two faces, and brace it with two stakes.</li><li>Fill with concrete to just below ground level and slope the top away from the post.</li><li>Let regular mix cure a day or two before you hang heavy gates or boards.</li></ol>'
                        . '<p>Building a bigger footing or a deck pier? Use the ' . self::link('/concrete-footing-calculator/', 'footing and pier calculator') . '. Pouring a slab? Start with the ' . self::link('/concrete-bags-calculator/', 'bags calculator') . '.</p>'],
                ],
                'faqs' => [
                    ['q' => 'How many bags of concrete do I need per fence post?', 'a' => sprintf('For a 4x4 post on a %d ft fence in a %d in wide, %d in deep hole: about %s bags of 80-lb mix or %s bags of 50-lb fast-setting mix. Taller fences and 6x6 posts need more; see the table.', $pd['height_ft'], $pd['hole_in'], $pd['depth_in'], $nf($per80), $nf($perfast))],
                    ['q' => 'How many bags of concrete for 10 fence posts?', 'a' => sprintf('About %d bags of 80-lb mix or %d bags of 50-lb fast-setting mix for ten 4x4 posts on a %d ft fence, with %d%% waste.', $pd['ten']['bags80'], $pd['ten']['bags_fast'], $pd['height_ft'], $waste)],
                    ['q' => 'How deep should a fence post hole be?', 'a' => sprintf('Bury a third to a half of the post\'s above-ground height and add %d in for gravel: about %d in for a %d ft fence. In cold climates go below the frost line.', $p['gravel_in'], $pd['depth_in'], $pd['height_ft'])],
                    ['q' => 'Can I pour concrete mix dry into a post hole?', 'a' => 'Fast-setting post mix is made to go in dry with water poured on top. Regular concrete mix should be mixed with water first; dry-packed regular mix hardens unevenly and ends up weaker.'],
                    ['q' => 'Is 60-lb or 80-lb concrete better for fence posts?', 'a' => sprintf('They are the same concrete; only the bag size differs. One 80-lb bag makes %s ft³ and a 60-lb bag %s ft³, so you need about a third more 60-lb bags. Pick the size you can lift and mix comfortably.', Slab_Data::num($c['bag_yields']['80'], 2), Slab_Data::num($c['bag_yields']['60'], 2))],
                ],
            ],

            'concrete-steps-calculator' => [
                'title' => 'Concrete Steps Calculator: Yards, Bags & Cost',
                'h1' => 'Concrete Steps Calculator',
                'meta' => sprintf('Concrete steps calculator: 3 steps 4 ft wide with a 3 ft landing take %s yd³ or %d bags of 80-lb mix. Code rise and run limits, and cost per step.', $nf($v['order_yd'], 2), $v['bags80']),
                'subtitle' => 'Enter the number of steps, width, rise and run to get the concrete, bags and form material for solid poured steps, with an optional landing at the top, and a check against the building code.',
                'answer' => sprintf('Three steps 4 ft wide with a %s in rise, an %s in tread and a %s ft landing at the top hold <strong>%s cubic yards</strong> of concrete with %d%% waste, which is <strong>%d bags of 80-lb mix</strong>. Concrete for that runs about %s; a contractor charges about <strong>%s per step</strong>, so %s for three.', Slab_Data::num($sv['rise_in']), Slab_Data::num($sv['tread_in']), Slab_Data::num($sv['landing_ft']), $nf($v['order_yd'], 2), $waste, $v['bags80'], $money($sv_bill[0]), Slab_Data::money_range($st['installed_per_step'], 0, ' to '), Slab_Data::money_range(self::steps_installed(3, 4), 0, ' to ')),
                'sections' => [
                    ['How Step Concrete Is Calculated', '<div class="formula-box"><div class="formula-label">Solid steps</div><div class="formula-math">Width × Tread × Rise × N × (N + 1) ÷ 2</div><div class="formula-explanation">The bottom step is one rise tall, the next two, and so on, so the volume grows with N × (N + 1) ÷ 2. A landing adds Width × Landing depth × total height.</div></div>'
                        . '<p>Large steps use a lot of concrete for their size. To save some, contractors often fill the core of a tall set of steps with compacted gravel or clean broken concrete, keeping at least 4 in of concrete over and around it. The calculator assumes solid steps, so it gives the most you would need.</p>'],
                    ['Building Code Limits (IRC R311.7)', '<ul>'
                        . sprintf('<li><strong>Riser height:</strong> %s in maximum, and all risers within 3/8 in of each other.</li>', Slab_Data::num($st['riser_max_in']))
                        . sprintf('<li><strong>Tread depth:</strong> %d in minimum, front to back.</li>', $st['tread_min_in'])
                        . sprintf('<li><strong>Width:</strong> %d in minimum.</li>', $st['width_min_in'])
                        . sprintf('<li><strong>Landing:</strong> at least %d in deep at the top, outside an exterior door.</li>', $st['landing_min_in'])
                        . '<li><strong>Handrail:</strong> needed with four or more risers.</li></ul><p>A ' . Slab_Data::num($st['riser_default_in']) . ' in rise and ' . Slab_Data::num($st['tread_default_in']) . ' in tread is comfortable and passes everywhere. Local codes can differ; check before you pour.</p>'],
                    ['What Concrete Steps Cost', self::table(['Steps (4 ft wide)', 'Concrete', '80-lb bags', 'DIY concrete', 'Contractor price'], $steps_rows, sprintf('%s in rise, %s in tread, no landing; %d%% waste. DIY concrete is the cheaper of bags (with a rented mixer) or ready-mix with delivery and short-load fees. Contractor price %s per step for 4 ft wide steps, formed and broom finished.', Slab_Data::num($st['riser_default_in']), Slab_Data::num($st['tread_default_in']), $waste, Slab_Data::money_range($st['installed_per_step'], 0, '–')))
                        . '<p>Most of a contractor\'s price is the form work: each riser needs a board, and both sides need a stepped form braced against the pressure of wet concrete. Tearing out old steps, a handrail and a stamped or exposed-aggregate finish are extra.</p>'],
                    ['Building Them to Last', '<ul>'
                        . sprintf('<li>Use %s PSI air-entrained concrete; steps are exposed to weather and de-icing salt.</li>', number_format($sd['code']['psi_severe']))
                        . '<li>Tie the steps to the house foundation with rebar dowels, or leave a gap with expansion joint material so they can move on their own.</li><li>Set them on compacted gravel, and in cold climates on footings below the frost line.</li><li>Slope each tread about 1/8 in toward the front so water runs off.</li><li>Form the risers with a 1 in bevel at the bottom so the trowel can finish the tread behind them.</li></ul>'
                        . '<p>Pouring a landing pad or walkway too? Price it with the ' . self::link('/', 'slab cost calculator') . '.</p>'],
                ],
                'faqs' => [
                    ['q' => 'How much concrete do I need for 3 steps?', 'a' => sprintf('Three solid steps 4 ft wide with a %s in rise and %s in tread take %s cubic yards with %d%% waste, or %d bags of 80-lb mix. A %s ft landing at the top raises that to %s yd³ (%d bags).', Slab_Data::num($st['riser_default_in']), Slab_Data::num($st['tread_default_in']), $nf(Slab_Data::steps_volume(3, 4, $st['riser_default_in'], $st['tread_default_in'], 0)['order_yd'], 2), $waste, Slab_Data::steps_volume(3, 4, $st['riser_default_in'], $st['tread_default_in'], 0)['bags80'], Slab_Data::num($sv['landing_ft']), $nf($v['order_yd'], 2), $v['bags80'])],
                    ['q' => 'How much do concrete steps cost?', 'a' => sprintf('About %s per step for 4 ft wide steps built by a contractor, so %s for three. Doing it yourself, the concrete alone for three steps with a landing costs about %s.', Slab_Data::money_range($st['installed_per_step'], 0, ' to '), Slab_Data::money_range(self::steps_installed(3, 4), 0, ' to '), $money($sv_bill[0]))],
                    ['q' => 'What is the code rise and run for steps?', 'a' => sprintf('Under the IRC, risers can be at most %s in and treads at least %d in deep, on stairs at least %d in wide. A %s in rise and %s in tread is a comfortable choice.', Slab_Data::num($st['riser_max_in']), $st['tread_min_in'], $st['width_min_in'], Slab_Data::num($st['riser_default_in']), Slab_Data::num($st['tread_default_in']))],
                    ['q' => 'Do concrete steps need rebar?', 'a' => 'Yes, a few #3 or #4 bars in each step and dowels into the foundation keep cracks tight and tie the steps to the house. Unreinforced steps tend to crack and pull away.'],
                ],
            ],

            'bags-vs-ready-mix-calculator' => [
                'title' => 'Bags vs Ready-Mix Concrete Calculator (Break-Even)',
                'h1' => 'Bags vs. Ready-Mix: Which Is Cheaper for Your Pour?',
                'meta' => sprintf('Bags or a ready-mix truck? With a rented mixer, ready-mix gets cheaper above about %s yd³ (a 4-in slab of about %d sq ft). Enter your prices for your break-even.', Slab_Data::num($be), $be_sqft),
                'subtitle' => 'Enter your slab and your local prices. The tool compares 80, 60 or 40-lb bags (with an optional mixer rental) against a ready-mix truck with delivery and short-load fees, and finds the volume where the truck becomes cheaper.',
                'answer' => sprintf('At typical prices (%s per 80-lb bag, %s per yard of ready-mix, a %s delivery fee and about a %s short-load fee), <strong>ready-mix becomes cheaper above about %s cubic yards</strong>, a 4-inch slab of about %d sq ft, if you rent a mixer for the bags. Mixing by hand without a mixer moves the break-even to about %s yd³, but costs a lot more time.', Slab_Data::money($c['bag_price_default'], 2), Slab_Data::money($c['ready_mix_default']), Slab_Data::money($dl['fee_default']), $money($short_mid), Slab_Data::num($be), $be_sqft, Slab_Data::num($be_hand)),
                'sections' => [
                    ['Why the Fees Decide It', sprintf('<p>Per cubic yard, bagged concrete costs about twice as much as ready-mix: %d bags of 80-lb mix make a yard, which is about %s, against %s from the plant. But a truck brings fixed charges: a delivery fee of about %s and, on orders under the plant\'s minimum (often %s yd³), a short-load fee of %s. On a small pour those fixed charges outweigh the cheaper concrete; on a bigger one they are spread over enough yards to disappear.</p>', (int) round(27 / $c['bag_yields']['80']), $money(27 / $c['bag_yields']['80'] * $c['bag_price_default']), Slab_Data::money($c['ready_mix_default']), Slab_Data::money($dl['fee_default']), Slab_Data::range($dl['common_min_yd'], '–'), Slab_Data::money_range($dl['short_load_flat'], 0, '–'))
                        . self::table(['Pour size', 'Bags + mixing time', 'Bags + mixer', 'Ready-mix', 'Cheaper'], $bt_rows, sprintf('%d%% waste; bags at %s with a %s mixer rental; ready-mix rounded up to the quarter yard with a %s delivery fee. * includes a %s short-load fee.', $waste, Slab_Data::money($c['bag_price_default'], 2), $money($mixer_mid), Slab_Data::money($dl['fee_default']), $money($short_mid)))],
                    ['What the Price Doesn\'t Show', sprintf('<ul><li><strong>Time and labor.</strong> A crew of three with one drum mixer places about %s bags an hour. Past roughly 60 bags, the first concrete starts to set before the last is mixed, which risks cold joints and a patchy finish.</li><li><strong>Strength.</strong> Ready-mix is batched by weight to a set PSI; bags are consistent too, but only if every batch gets the same water.</li><li><strong>Access.</strong> Bags win where a truck can\'t get close: backyards behind fences, steep lots, or far from a plant.</li><li><strong>Leftovers.</strong> Unopened bags can be returned or saved; leftover ready-mix has to go somewhere on the day.</li></ul>', Slab_Data::range($sd['labor']['bags_per_hour_mixer'], '–'))
                        . '<p>For the rest of the job, use the ' . self::link('/', 'slab cost calculator') . ', or read our guide to ' . self::link('/ready-mix-vs-bagged-concrete/', 'ready-mix vs. bagged concrete') . '.</p>'],
                ],
                'faqs' => [
                    ['q' => 'When is ready-mix cheaper than bags?', 'a' => sprintf('Above about %s cubic yards at typical prices if you rent a mixer for the bags, or about %s yards if you would mix by hand. Below that, the delivery and short-load fees make the truck cost more.', Slab_Data::num($be), Slab_Data::num($be_hand))],
                    ['q' => 'How many 80-lb bags of concrete equal a yard?', 'a' => sprintf('%s bags. One 80-lb bag makes %s cubic feet, and a cubic yard is 27 cubic feet.', Slab_Data::num(27 / $c['bag_yields']['80']), Slab_Data::num($c['bag_yields']['80'], 2))],
                    ['q' => 'Is it worth renting a mixer for bagged concrete?', 'a' => sprintf('For more than about 20 bags, yes. A drum mixer rents for %s a day and lets a small crew place %s bags an hour; mixing by hand in a wheelbarrow is slower and harder to keep consistent.', Slab_Data::money_range($c['mixer_rental_day'], 0, ' to '), Slab_Data::range($sd['labor']['bags_per_hour_mixer'], ' to '))],
                    ['q' => 'What is the smallest ready-mix order?', 'a' => sprintf('Most plants deliver as little as 1 yard, but charge a short-load fee (%s flat or %s per missing yard) below their minimum, often %s yards.', Slab_Data::money_range($dl['short_load_flat'], 0, ' to '), Slab_Data::money_range($dl['short_load_per_yd'], 0, ' to '), Slab_Data::range($dl['common_min_yd'], ' to '))],
                ],
            ],
        ];

        return self::$cache;
    }
}
