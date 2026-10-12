<?php
/**
 * PROJECT-TYPE PAGES (/patio/, /driveway/, /garage/, /shed/, /foundation/, /floor/)
 *
 * These six pages share page-templates/template-hub.php. They used to print
 * the same body text with only the title changed (about 97% identical),
 * which reads as duplicate content. Each page now gets its own sizes, specs,
 * planning notes and FAQ from this file. Every figure comes from Slab_Data.
 *
 * The FAQ list is shown on the page and marked up as FAQPage schema
 * (inc/schema-generator.php), so the two always match.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_Applications {

    private static $cache = [];

    public static function slugs() {
        return ['patio', 'driveway', 'garage', 'shed', 'foundation', 'floor'];
    }

    /** Sidebar / related-link labels. */
    public static function labels() {
        return [
            'patio' => 'Patio Slabs',
            'driveway' => 'Driveways',
            'garage' => 'Garage Floor Slabs',
            'shed' => 'Shed Pads',
            'foundation' => 'Monolithic Foundations',
            'floor' => 'Interior Concrete Floors',
        ];
    }

    /** Max control-joint spacing in feet for a thickness (2.5 × t, capped). */
    private static function joint_ft($t) {
        $j = Slab_Data::get('joints');
        $max = isset($j['max_ft'][$t]) ? $j['max_ft'][$t] : $j['cap_ft'];
        return min($t * $j['ratio_best'][1], $max);
    }

    /** One table row for a slab size. */
    private static function size_row($label, $l, $w, $t) {
        $c = Slab_Data::size_costs($l, $w, $t);
        return [
            'label' => $label,
            'dims' => $l . ' × ' . $w . ' ft',
            'area' => $l * $w,
            't' => $t,
            'yd' => $c['est']['order_yd'],
            'bags' => $c['est']['bags80'],
            'diy' => $c['diy'],
            'installed' => $c['installed'],
        ];
    }

    /**
     * Page data for a slug, or null.
     * Keys: subtitle, meta, preset, answer, sizes (rows), specs ([label, value]),
     * sections ([h2, html]), faqs ([q, a]), links ([label, url]).
     */
    public static function get($slug) {
        if (!in_array($slug, self::slugs(), true)) {
            return null;
        }
        if (!isset(self::$cache[$slug])) {
            self::$cache[$slug] = call_user_func([__CLASS__, 'page_' . $slug]);
        }
        return self::$cache[$slug];
    }

    private static function mr($pair, $dec = 0) {
        return Slab_Data::money_range($pair, $dec, ' to ');
    }

    private static function url($path) {
        return esc_url(home_url($path));
    }

    /* ------------------------------------------------------------------ */

    private static function page_patio() {
        $sd = Slab_Data::all();
        $f = $sd['finish'];
        $psf = Slab_Data::installed_psf();
        $stamped = Slab_Data::stamped_psf();
        $mid = self::size_row('Mid-size patio', 12, 16, 4);
        $joint = self::joint_ft(4);

        return [
            'subtitle' => 'What a concrete patio costs by size and finish, how thick to pour it, and the details that keep water away from your house.',
            'meta' => sprintf('Concrete patio cost by size and finish: %s/sq ft installed broom finish, %s stamped. Thickness, slope, joints and a free patio calculator.', Slab_Data::money_range($psf, 0, '-'), Slab_Data::money_range($stamped, 0, '-')),
            'preset' => 'patio',
            'answer' => sprintf('A broom-finished concrete patio costs about <strong>%s per sq ft</strong> installed at 4 inches thick, so a 12 × 16 ft patio runs roughly <strong>%s</strong>. Stamped concrete runs about <strong>%s per sq ft</strong>.', esc_html(Slab_Data::money_range($psf, 0, ' to ')), esc_html(self::mr($mid['installed'])), esc_html(Slab_Data::money_range($stamped, 0, ' to '))),
            'size_title' => 'Patio Sizes and Costs (4 in thick)',
            'sizes' => [
                self::size_row('Bistro / grill pad', 10, 12, 4),
                self::size_row('Small dining patio', 12, 12, 4),
                $mid,
                self::size_row('Dining + lounge', 16, 20, 4),
                self::size_row('Large entertaining patio', 20, 20, 4),
            ],
            'specs' => [
                ['Thickness', '4 in (5 in under a hot tub or outdoor kitchen)'],
                ['Concrete strength', number_format($sd['code']['psi_exposed']) . ' PSI in mild climates; ' . number_format($sd['code']['psi_severe']) . ' PSI air-entrained (' . Slab_Data::r('concrete.air_entrainment_pct', '–') . '% air) where it freezes'],
                ['Base', Slab_Data::r('site.gravel_depth_range') . ' in of compacted crushed stone'],
                ['Reinforcement', 'Welded wire mesh or #4 rebar at ' . $sd['reinforcement']['rebar_base_spacing_in'] . ' in; fiber mix is common on patios'],
                ['Slope', implode(' to ', $sd['site']['drainage_slope_in_per_ft']) . ' in per ft away from the house'],
                ['Control joints', 'Every ' . Slab_Data::num($joint) . ' ft or closer, cut ' . (int) round($sd['joints']['depth_fraction'] * 100) . '% of the depth'],
            ],
            'sections' => [
                ['Broom, Exposed Aggregate or Stamped?', sprintf(
                    '<p>The finish is the biggest price lever on a patio. A broom finish adds about %s per sq ft and gives a non-slip surface that is easy to repair. A smooth steel-trowel finish (%s per sq ft) looks clean but gets slippery when wet, so it suits covered patios. Exposed aggregate (%s per sq ft) hides dirt and wear well. Stamped concrete (%s per sq ft for the finish alone, plus higher labor) imitates stone or brick and is the priciest, and it needs resealing every few years to keep its color.</p>',
                    esc_html(Slab_Data::money_range($f['broom']['range'], 2, ' to ')),
                    esc_html(Slab_Data::money_range($f['trowel']['range'], 2, ' to ')),
                    esc_html(Slab_Data::money_range($f['exposed']['range'], 2, ' to ')),
                    esc_html(Slab_Data::money_range($f['stamped']['range'], 2, ' to '))
                )],
                ['Patio Details People Forget', '<ul>'
                    . '<li><strong>Height at the door:</strong> keep the top of the slab at least a step below the door sill so rain and snow melt cannot run inside.</li>'
                    . '<li><strong>Isolation joint at the house:</strong> a strip of expansion joint material where the patio meets the foundation lets the two move separately.</li>'
                    . '<li><strong>Future posts and footings:</strong> if a pergola or roof may come later, pour footings now or thicken the slab where posts will land. Use the <a href="' . self::url('/concrete-footing-calculator/') . '">footing calculator</a>.</li>'
                    . '<li><strong>Utilities:</strong> run a sleeve under the slab for gas, power or irrigation before you pour.</li>'
                    . '</ul>'],
            ],
            'faqs' => [
                ['q' => 'How much does a 12x16 concrete patio cost?', 'a' => sprintf('About %s installed with a broom finish at 4 inches thick, or %s in materials if you pour it yourself. It needs %s cubic yards of concrete with waste.', self::mr($mid['installed']), self::mr($mid['diy']), number_format($mid['yd'], 2))],
                ['q' => 'How thick should a concrete patio be?', 'a' => 'Four inches is standard for a patio. Go to 5 inches under a hot tub, outdoor kitchen or anywhere a vehicle might drive.'],
                ['q' => 'Is stamped concrete worth it for a patio?', 'a' => sprintf('Stamped concrete costs about %s per sq ft against %s for broom finish. It looks like stone for much less than real pavers, but it needs resealing every few years and repairs are hard to match.', Slab_Data::money_range($stamped, 0, ' to '), Slab_Data::money_range($psf, 0, ' to '))],
                ['q' => 'Which way should a patio slope?', 'a' => sprintf('Away from the house, at %s inch per foot (%s inch per foot is preferred next to a wall).', implode(' to ', $sd['site']['drainage_slope_in_per_ft']), $sd['site']['drainage_slope_preferred'])],
            ],
            'links' => [
                ['Concrete patio cost guide (stamped vs. broom)', '/concrete-patio-slab-cost-guide/'],
                ['12x12 slab cost breakdown', '/12x12-concrete-slab-cost-breakdown/'],
                ['Concrete sealers: do you need one?', '/concrete-sealers-do-you-need-one/'],
            ],
        ];
    }

    private static function page_driveway() {
        $sd = Slab_Data::all();
        $dpsf = Slab_Data::driveway_psf();
        $two = self::size_row('2-car', 20, 40, 5);
        $stamped = Slab_Data::stamped_psf(40, 20, 5);
        $demo = $sd['site']['demo_range'];
        $joint = self::joint_ft(5);

        return [
            'subtitle' => 'Concrete driveway prices for 1, 2 and 3-car drives, the thickness and mix that stand up to cars and winter salt, and what adds to the bill.',
            'meta' => sprintf('Concrete driveway cost: %s/sq ft installed at 5 in thick, about %s for a 20x40 2-car drive. Mix, rebar, slope and tear-out costs.', Slab_Data::money_range($dpsf, 0, '-'), self::mr($two['installed'])),
            'preset' => 'driveway',
            'answer' => sprintf('A new concrete driveway costs about <strong>%s per sq ft</strong> installed at 5 inches thick with rebar. A typical 20 × 40 ft two-car driveway runs <strong>%s</strong>. Removing an old driveway first adds roughly %s per sq ft.', esc_html(Slab_Data::money_range($dpsf, 0, ' to ')), esc_html(self::mr($two['installed'])), esc_html(Slab_Data::money_range($demo, 0, ' to '))),
            'size_title' => 'Driveway Sizes and Costs (5 in thick)',
            'sizes' => [
                self::size_row('1-car, short', 10, 20, 5),
                self::size_row('1-car, long', 12, 40, 5),
                self::size_row('2-car, short', 20, 20, 5),
                $two,
                self::size_row('3-car', 30, 40, 5),
            ],
            'specs' => [
                ['Thickness', '5 in for cars; 6 in for RVs, trucks or a delivery route'],
                ['Concrete strength', number_format($sd['code']['psi_severe']) . ' PSI air-entrained in freeze-thaw areas (the IRC minimum for exposed flatwork there); ' . number_format($sd['code']['psi_deicing_recommended']) . ' PSI where de-icing salt is used'],
                ['Base', Slab_Data::r('site.gravel_depth_range') . ' in of compacted crushed stone; more on soft or clay soil'],
                ['Reinforcement', '#4 rebar at 12 to ' . $sd['reinforcement']['rebar_base_spacing_in'] . ' in centers on chairs'],
                ['Slope', 'At least ' . $sd['site']['drainage_slope_in_per_ft'][0] . ' in per ft so water runs off, away from the garage'],
                ['Control joints', 'Every ' . Slab_Data::num($joint) . ' ft or closer, panels close to square'],
            ],
            'sections' => [
                ['What Adds to a Driveway Bill', '<ul>'
                    . '<li><strong>Tear-out:</strong> breaking up and hauling away an old slab runs about ' . esc_html(Slab_Data::money_range($demo, 0, ' to ')) . ' per sq ft. Price it with the <a href="' . self::url('/concrete-removal-cost-calculator/') . '">removal cost calculator</a>.</li>'
                    . '<li><strong>The apron:</strong> the section between the street and the sidewalk usually belongs to the city, needs its own permit and is often built to a city detail (thicker, sometimes 6 in).</li>'
                    . '<li><strong>Grading:</strong> a sloped lot may need fill or a retaining edge before forming.</li>'
                    . '<li><strong>Decorative work:</strong> a stamped driveway runs about ' . esc_html(Slab_Data::money_range($stamped, 0, ' to ')) . ' per sq ft.</li>'
                    . '<li><strong>Pumping:</strong> a long drive the truck cannot reach may need a line pump (' . esc_html(Slab_Data::money_range($sd['pump']['line_job'], 0, ' to ')) . ' for a job).</li>'
                    . '</ul>'],
                ['Protecting a New Driveway', sprintf('<p>Keep cars off a new driveway for at least %d days, and heavy trucks off for the full %d days. In the first winter, avoid rock salt: it attacks young concrete. Sand gives traction without the damage. A penetrating sealer applied after the slab has cured helps it resist salt and oil.</p>', (int) $sd['curing']['cars_days'], (int) $sd['curing']['full_days'])],
            ],
            'faqs' => [
                ['q' => 'How much does a 2-car concrete driveway cost?', 'a' => sprintf('A 20 x 40 ft driveway at 5 inches thick costs about %s installed, which is %s per sq ft. It takes %s cubic yards of concrete.', self::mr($two['installed']), Slab_Data::money_range($dpsf, 0, ' to '), number_format($two['yd'], 2))],
                ['q' => 'How thick should a concrete driveway be?', 'a' => 'Five inches with rebar is the standard recommendation for passenger cars. Use 6 inches if RVs, heavy trucks or delivery vans will use it. Local codes vary, so check with your building department.'],
                ['q' => 'What PSI concrete should I use for a driveway?', 'a' => sprintf('At least %s PSI air-entrained concrete in areas with freeze-thaw cycles, and %s PSI where de-icing salt is used. Mild climates can use %s PSI.', number_format($sd['code']['psi_severe']), number_format($sd['code']['psi_deicing_recommended']), number_format($sd['code']['psi_exposed']))],
                ['q' => 'How long before I can drive on a new concrete driveway?', 'a' => sprintf('Wait at least %d days for a passenger car and %d days for heavy vehicles.', (int) $sd['curing']['cars_days'], (int) $sd['curing']['full_days'])],
            ],
            'links' => [
                ['Concrete driveway cost guide', '/how-much-does-a-concrete-driveway-cost/'],
                ['Concrete vs. asphalt driveway', '/concrete-vs-asphalt-driveway/'],
                ['Do you need a permit?', '/concrete-slab-permits-and-costs/'],
            ],
        ];
    }

    private static function page_garage() {
        $sd = Slab_Data::all();
        $gpsf = Slab_Data::garage_psf();
        $two = self::size_row('2-car', 24, 24, 5);
        $m = $sd['monolithic'];
        $edge = Slab_Data::edge_yd(24, 24, 5, $m['edge_depth_in'][1]);

        return [
            'subtitle' => 'Garage floor slab costs for 1, 2 and 3-car garages, and the choices that matter: thickness, vapor barrier, thickened edges and slope to the door.',
            'meta' => sprintf('Garage slab cost: %s/sq ft installed at 5 in, about %s for a 24x24 2-car garage. Thickened edges, vapor barrier, PSI and slope.', Slab_Data::money_range($gpsf, 0, '-'), self::mr($two['installed'])),
            'preset' => 'garage',
            'answer' => sprintf('A garage floor slab costs about <strong>%s per sq ft</strong> installed at 5 inches thick, so a 24 × 24 ft two-car garage runs about <strong>%s</strong> for the slab. Thickened edges for a monolithic pour add concrete and digging on top of that.', esc_html(Slab_Data::money_range($gpsf, 0, ' to ')), esc_html(self::mr($two['installed']))),
            'size_title' => 'Garage Slab Sizes and Costs (5 in thick)',
            'sizes' => [
                self::size_row('1-car', 12, 24, 5),
                self::size_row('1.5-car / workshop', 16, 24, 5),
                self::size_row('2-car, compact', 20, 22, 5),
                $two,
                self::size_row('3-car', 24, 36, 5),
            ],
            'specs' => [
                ['Thickness', '5 in for cars; 6 in for a shop with lifts, heavy tools or trucks'],
                ['Concrete strength', number_format($sd['code']['psi_severe']) . ' PSI (the IRC minimum for garage floors in severe weather areas); ' . number_format($sd['code']['psi_deicing_recommended']) . ' PSI if salty slush drips off cars'],
                ['Vapor barrier', '6-mil or heavier polyethylene under the slab (IRC R506.2.3), especially if the garage will be heated or finished'],
                ['Edges', 'Thickened edge ' . (int) $m['edge_width_in'] . ' in wide and ' . Slab_Data::range($m['edge_depth_in']) . ' in deep for monolithic slabs, or a stem wall on footings in deep-frost areas'],
                ['Reinforcement', '#4 rebar at 12 to ' . $sd['reinforcement']['rebar_base_spacing_in'] . ' in centers, plus ' . (int) $m['edge_bars'] . ' bars around the edge'],
                ['Slope', 'Toward the door or a floor drain, so melt water runs out'],
            ],
            'sections' => [
                ['Monolithic Slab or Stem Wall?', sprintf(
                    '<p>A <strong>monolithic slab</strong> pours the floor and a thickened edge in one go. It is the cheaper choice where frost is shallow. On a 24 × 24 ft garage, deepening the edge to %d in takes about %s extra cubic yards of concrete, plus trenching for the edge (%s at %d in deep in typical soil).</p><p>Where the frost line is deep (often %s in or more in northern states), codes usually want footings below frost, so builders pour a <strong>footing and stem wall</strong> first and the floor slab inside it. That costs more but protects against frost heave. Your building department decides which one your garage needs. Estimate footing concrete with the <a href="%s">footing calculator</a>.</p>',
                    (int) $m['edge_depth_in'][1],
                    esc_html(number_format($edge, 2)),
                    esc_html(Slab_Data::money_range($m['trenching'][(string) $m['edge_depth_in'][1]], 0, ' to ')),
                    (int) $m['edge_depth_in'][1],
                    esc_html(Slab_Data::range($sd['freeze_thaw_ft'])),
                    self::url('/concrete-footing-calculator/')
                )],
                ['Before You Pour a Garage Floor', '<ul>'
                    . '<li><strong>Floor drain or trench drain:</strong> it has to be set before the pour, with the slab sloped toward it.</li>'
                    . '<li><strong>Anchor bolts:</strong> sill-plate bolts or straps go into the wet edge concrete at the spacing your plans call for.</li>'
                    . '<li><strong>Door step-down:</strong> a small rebate at the overhead door keeps water from running under the door seal.</li>'
                    . '<li><strong>Coatings:</strong> if you plan an epoxy floor, ask for a steel-trowel finish and wait for the slab to dry fully first.</li>'
                    . '</ul>'],
            ],
            'faqs' => [
                ['q' => 'How much does a 2-car garage slab cost?', 'a' => sprintf('A 24 x 24 ft garage floor at 5 inches thick costs about %s installed, or %s per sq ft. It needs %s cubic yards of concrete before thickened edges.', self::mr($two['installed']), Slab_Data::money_range($gpsf, 0, ' to '), number_format($two['yd'], 2))],
                ['q' => 'How thick should a garage floor be?', 'a' => 'Five inches is the usual recommendation for cars. Use 6 inches for a workshop with a car lift, heavy machines or trucks.'],
                ['q' => 'Do I need a vapor barrier under a garage slab?', 'a' => 'The IRC calls for a 6-mil vapor retarder under slabs of enclosed spaces, with some exceptions for unheated garages. Most builders install one anyway because it is cheap and stops moisture from damaging coatings and stored items.'],
                ['q' => 'Monolithic slab or stem wall for a garage?', 'a' => 'A monolithic thickened-edge slab is cheaper and common where frost is shallow. In deep-frost areas codes usually require footings below the frost line, which means a stem wall foundation. Ask your building department.'],
            ],
            'links' => [
                ['Garage slab cost: monolithic vs. stem wall', '/how-much-does-a-garage-slab-cost/'],
                ['24x24 slab cost breakdown', '/24x24-concrete-slab-cost-breakdown/'],
                ['Rebar vs. wire mesh', '/concrete-slab-reinforcement-rebar-vs-wire-mesh/'],
            ],
        ];
    }

    private static function page_shed() {
        $sd = Slab_Data::all();
        $s = $sd['site'];
        $mid = self::size_row('10 × 12 shed', 12, 10, 4);
        // Gravel pad for the same shed: the deeper base depth of compacted
        // stone over the footprint plus 1 ft all round, in a timber frame.
        $pad_area = (12 + 2) * (10 + 2);
        $pad_yd = $pad_area * $s['gravel_depth_range'][1] / 12 / 27 * $s['gravel_compaction'];
        $pad_cost = [$pad_yd * $s['gravel_range'][0], $pad_yd * $s['gravel_range'][1]];
        $pad_cost = Slab_Data::round_pair([$pad_cost[0] + 2 * (14 + 12) * $s['forms_range'][0], $pad_cost[1] + 2 * (14 + 12) * $s['forms_range'][1]], 10);

        return [
            'subtitle' => 'Concrete shed pad costs for common shed sizes, how a concrete pad compares with gravel, and how to make sure the shed actually fits.',
            'meta' => sprintf('Concrete shed pad cost: about %s installed for a 10x12 shed, %s DIY. Sizes from 8x10 to 12x20, concrete vs gravel pad, anchors and thickness.', self::mr($mid['installed']), self::mr($mid['diy'])),
            'preset' => 'shed',
            'answer' => sprintf('A 4-inch concrete pad for a 10 × 12 ft shed costs about <strong>%s</strong> installed, or <strong>%s</strong> in materials if you pour it yourself. A compacted gravel pad for the same shed costs roughly %s in materials.', esc_html(self::mr($mid['installed'])), esc_html(self::mr($mid['diy'])), esc_html(self::mr($pad_cost))),
            'size_title' => 'Shed Pad Sizes and Costs (4 in thick)',
            'sizes' => [
                self::size_row('8 × 10 shed', 10, 8, 4),
                self::size_row('10 × 10 shed', 10, 10, 4),
                $mid,
                self::size_row('12 × 16 shed', 16, 12, 4),
                self::size_row('12 × 20 shed / small garage', 20, 12, 4),
            ],
            'specs' => [
                ['Thickness', '4 in; 5 in with thickened edges if a riding mower, ATV or car is stored inside'],
                ['Size', 'Match the shed floor exactly, or 1 to 2 in smaller per side so rain drips off the siding, not onto the pad'],
                ['Concrete strength', number_format($sd['code']['psi_exposed']) . ' PSI; ' . number_format($sd['code']['psi_severe']) . ' PSI air-entrained where it freezes'],
                ['Base', $s['gravel_depth_in'] . ' in of compacted crushed stone'],
                ['Anchoring', 'J-bolts or wedge anchors along the sill line (follow the shed maker\'s plan)'],
                ['Moisture', '6-mil poly under the slab keeps ground moisture off stored tools'],
            ],
            'sections' => [
                ['Concrete Pad or Gravel Pad?', '<p>A <strong>gravel pad</strong> (compacted stone inside a timber frame) is cheaper and drains well. It suits sheds with their own wood floor on skids. A <strong>concrete pad</strong> costs more but becomes the floor itself: it is flat, rodent-proof, easy to sweep, and it lets you bolt the walls down and roll heavy equipment in. Choose concrete for workshops, mower and ATV storage, or any shed without a built-in floor.</p>'],
                ['Getting the Shed to Fit', '<ul>'
                    . '<li><strong>Square:</strong> measure both diagonals of the forms; they must match before you pour.</li>'
                    . '<li><strong>Level:</strong> a shed pad should be level (not sloped like a patio), or the doors will bind.</li>'
                    . '<li><strong>Access:</strong> plan a ramp or a flush threshold at the door if you will roll equipment in.</li>'
                    . '<li><strong>Setbacks and permits:</strong> many towns limit how close a shed can sit to the property line, and some require a permit above a certain size. Check <a href="' . self::url('/concrete-slab-permits-and-costs/') . '">permit rules</a> first.</li>'
                    . '</ul>'],
            ],
            'faqs' => [
                ['q' => 'How much does a concrete pad for a 10x12 shed cost?', 'a' => sprintf('About %s installed at 4 inches thick, or %s in materials to do it yourself. It needs %s cubic yards of concrete (%d bags of 80-lb mix).', self::mr($mid['installed']), self::mr($mid['diy']), number_format($mid['yd'], 2), $mid['bags'])],
                ['q' => 'How thick should a shed slab be?', 'a' => 'Four inches for a storage shed. Use 5 inches with thickened edges if you will park a riding mower, ATV or car inside.'],
                ['q' => 'Is a gravel or concrete shed base better?', 'a' => 'Gravel is cheaper and fine for sheds with their own floor. Concrete costs more but gives a flat, rodent-proof floor you can anchor to, which is better for workshops and heavy equipment.'],
                ['q' => 'Should a shed pad be bigger than the shed?', 'a' => 'No. Make it the same size or slightly smaller, so rain running down the walls drips onto the ground rather than onto the slab and under the siding.'],
            ],
            'links' => [
                ['Shed pad cost and construction guide', '/concrete-shed-pad-cost-and-construction/'],
                ['10x10 slab cost breakdown', '/10x10-concrete-slab-cost-breakdown/'],
                ['How many bags of concrete do I need?', '/how-many-bags-of-concrete-do-i-need/'],
            ],
        ];
    }

    private static function page_foundation() {
        $sd = Slab_Data::all();
        $m = $sd['monolithic'];
        $fpsf = Slab_Data::foundation_psf();
        $houses = [
            ['Small house / ADU', 24, 32],
            ['Ranch, 1,200 sq ft', 30, 40],
            ['Ranch, 1,600 sq ft', 32, 50],
            ['Ranch, 2,000 sq ft', 40, 50],
        ];
        $rows = [];
        foreach ($houses as $h) {
            $area = $h[1] * $h[2];
            $c = Slab_Data::estimate($h[1], $h[2], 4);
            $edge = Slab_Data::edge_yd($h[1], $h[2], 4, $m['edge_depth_in'][1]);
            $rows[] = [
                'label' => $h[0],
                'dims' => $h[1] . ' × ' . $h[2] . ' ft',
                'area' => $area,
                't' => 4,
                'yd' => $c['order_yd'] + $edge,
                'bags' => null,
                'diy' => null,
                'installed' => Slab_Data::round_pair([$area * $fpsf[0], $area * $fpsf[1]], 100),
            ];
        }
        $mid = $rows[1];

        return [
            'subtitle' => 'What a monolithic slab foundation costs for a house, garage or ADU, what it includes, and where a slab is (and is not) the right foundation.',
            'meta' => sprintf('Monolithic slab foundation cost: %s/sq ft, about %s for a 30x40 house. Thickened edges, frost depth, plumbing rough-in and what is not included.', Slab_Data::money_range($fpsf, 0, '-'), self::mr($mid['installed'])),
            'preset' => 'foundation',
            'answer' => sprintf('A monolithic slab-on-grade foundation costs about <strong>%s per sq ft</strong>, so a 30 × 40 ft (1,200 sq ft) house runs roughly <strong>%s</strong> for the slab and its thickened edges. Plumbing rough-in, engineering and permits are extra.', esc_html(Slab_Data::money_range($fpsf, 0, ' to ')), esc_html(self::mr($mid['installed']))),
            'size_title' => 'Foundation Slab Sizes and Costs (4 in floor, thickened edge)',
            'sizes' => $rows,
            'specs' => [
                ['Floor thickness', '4 in typical (the IRC minimum is 3.5 in); thicker under bearing walls and point loads per the plans'],
                ['Thickened edge', (int) $m['edge_width_in'] . ' in or wider, ' . Slab_Data::range($m['edge_depth_in']) . ' in deep or to frost depth, with ' . (int) $m['edge_bars'] . ' or more #4 bars'],
                ['Concrete strength', number_format($sd['code']['psi_protected']) . ' PSI minimum for foundations not exposed to weather; most builders order ' . number_format($sd['code']['psi_exposed']) . ' or more'],
                ['Under the slab', Slab_Data::r('site.gravel_depth_range') . ' in of compacted stone, 6-mil or heavier vapor retarder, and insulation where the energy code requires it'],
                ['Frost', 'Footings below the local frost line (often ' . Slab_Data::range($sd['freeze_thaw_ft']) . ' in in cold states) or a frost-protected shallow foundation design'],
                ['Design', 'Follow stamped plans or your building department\'s details; soil conditions set the final design'],
            ],
            'sections' => [
                ['What the Price Includes (and What It Does Not)', '<p>The per-square-foot figure covers the slab and its thickened edges: forming, base, vapor retarder, rebar, concrete, placing and finishing. It does not cover the soil test or engineering, permits and inspections, plumbing and electrical rough-in under the slab, termite treatment, under-slab insulation, or major grading. On a house, those can add as much again as the slab itself, so get them priced separately.</p>'],
                ['When a Slab Is the Right Foundation', '<p>Slabs suit flat lots, warm and mixed climates, and single-story homes where you want no steps at the doors. They are the cheapest common foundation. A crawl space or basement makes more sense on sloped lots, where frost is very deep, or when you want easy access to pipes and ducts. Our <a href="' . self::url('/slab-vs-crawl-space-foundation/') . '">slab vs. crawl space comparison</a> goes through the trade-offs. Structural foundations need a design that meets your local code, so treat these numbers as a budget, not a plan.</p>'],
            ],
            'faqs' => [
                ['q' => 'How much does a slab foundation cost per square foot?', 'a' => sprintf('About %s per sq ft for a monolithic slab with thickened edges, before plumbing rough-in, engineering and permits.', Slab_Data::money_range($fpsf, 0, ' to '))],
                ['q' => 'How much does a slab foundation for a 1,200 sq ft house cost?', 'a' => sprintf('Roughly %s for a 30 x 40 ft monolithic slab. It takes about %s cubic yards of concrete including the thickened edges and waste.', self::mr($mid['installed']), number_format($mid['yd'], 1))],
                ['q' => 'How deep is the thickened edge on a monolithic slab?', 'a' => sprintf('Typically %s inches deep and at least %d inches wide, but it must reach below the frost line where codes require it. Your plans or building department set the exact size.', Slab_Data::range($m['edge_depth_in']), (int) $m['edge_width_in'])],
                ['q' => 'Is a slab foundation cheaper than a crawl space?', 'a' => 'Usually, yes. A slab needs less excavation, no stem walls and no floor framing. A crawl space costs more but gives access to plumbing and works better on sloped lots.'],
            ],
            'links' => [
                ['Monolithic slab foundation cost guide', '/concrete-foundation-monolithic-slab-cost/'],
                ['Slab vs. crawl space foundation', '/slab-vs-crawl-space-foundation/'],
                ['Footing & pier calculator', '/concrete-footing-calculator/'],
            ],
        ];
    }

    private static function page_floor() {
        $sd = Slab_Data::all();
        $f = $sd['finish'];
        $base = self::size_row('Basement floor', 24, 36, 4);
        $vapor = $sd['site']['vapor_range'];

        return [
            'subtitle' => 'Costs and specs for indoor concrete floors: basements, additions, workshops and polished living spaces, including the moisture steps that protect flooring.',
            'meta' => sprintf('Interior concrete floor cost: about %s for a 24x36 basement floor at 4 in. Trowel finish, vapor barrier, moisture testing before flooring.', self::mr($base['installed'])),
            'preset' => '',
            'answer' => sprintf('A new 4-inch interior concrete floor costs about <strong>%s</strong> for a 24 × 36 ft basement (%s per sq ft) before any special finish. Indoors you want a smooth steel-trowel finish and a vapor barrier under the slab.', esc_html(self::mr($base['installed'])), esc_html(Slab_Data::money_range([floor($base['installed'][0] / $base['area']), ceil($base['installed'][1] / $base['area'])], 0, ' to '))),
            'size_title' => 'Interior Floor Sizes and Costs (4 in thick)',
            'sizes' => [
                self::size_row('Room addition', 12, 16, 4),
                self::size_row('Workshop / studio', 20, 24, 4),
                $base,
                self::size_row('Large basement', 30, 40, 4),
            ],
            'specs' => [
                ['Thickness', '4 in (IRC minimum 3.5 in); 5 to 6 in under heavy machines'],
                ['Concrete strength', number_format($sd['code']['psi_protected']) . ' PSI minimum for interior slabs not exposed to weather; ' . number_format($sd['code']['psi_exposed']) . ' PSI is common'],
                ['Vapor retarder', '6-mil polyethylene minimum (IRC R506.2.3); 10 or 15-mil under finished floors; about ' . Slab_Data::money_range($vapor, 2, ' to ') . ' per sq ft'],
                ['Base', Slab_Data::r('site.gravel_depth_range') . ' in of compacted stone or clean sand'],
                ['Finish', 'Steel trowel (' . Slab_Data::money_range($f['trowel']['range'], 2, ' to ') . ' per sq ft); no broom texture indoors'],
                ['Joints', 'Saw-cut ' . Slab_Data::r('joints.sawcut_h') . ' hours after finishing, lined up with walls and columns'],
            ],
            'sections' => [
                ['Moisture Comes First Indoors', '<p>A slab can be strong enough to walk on in a day and still be too wet for flooring for months. Before laying vinyl, wood, carpet or an epoxy coating, test the slab with the method the flooring maker names (a calcium chloride test or an in-slab relative humidity probe) and wait until it passes. A good vapor retarder under the slab is the cheapest insurance: once the slab is poured, it cannot be added.</p>'],
                ['Polished, Stained or Covered?', '<p>If the concrete will be the finished floor, plan it from the start. Polished concrete needs a flat, dense, steel-troweled surface and careful protection during construction. Stains and dyes show every patch and mark. If you will cover the slab with tile, wood or vinyl, flatness matters more than looks. Radiant heat tubing, plumbing and floor outlets all have to be placed before the pour.</p>'],
            ],
            'faqs' => [
                ['q' => 'How much does a basement concrete floor cost?', 'a' => sprintf('About %s for a 24 x 36 ft basement at 4 inches thick, before polishing or coatings. It takes %s cubic yards of concrete with waste.', self::mr($base['installed']), number_format($base['yd'], 2))],
                ['q' => 'How thick should an indoor concrete floor be?', 'a' => 'Four inches is standard for basements, additions and workshops (the IRC minimum is 3.5 inches). Go thicker under heavy equipment.'],
                ['q' => 'Do I need a vapor barrier under an interior slab?', 'a' => 'Yes, the IRC calls for a 6-mil vapor retarder under slabs of heated spaces, and flooring makers often want a thicker one. It stops ground moisture from damaging flooring and coatings.'],
                ['q' => 'How long before I can put flooring on new concrete?', 'a' => sprintf('Concrete reaches design strength in about %d days, but drying for flooring often takes longer. Test the moisture level as the flooring maker requires before installing.', (int) $sd['curing']['full_days'])],
            ],
            'links' => [
                ['Concrete curing time guide', '/concrete-curing-time-guide/'],
                ['How thick should a slab be?', '/how-thick-should-a-concrete-slab-be/'],
                ['Resurfacing vs. extending a slab', '/resurfacing-vs-extending-a-concrete-slab/'],
            ],
        ];
    }
}
