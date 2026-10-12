<?php
/**
 * Article: 24x24 Concrete Slab Cost (post slug 24x24-concrete-slab-cost-breakdown).
 * Loaded by inc/post-articles.php. Every figure comes from Slab_Data.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$sd = Slab_Data::all();
$m = function ($n) {
    return Slab_Data::money($n, 0);
};
$mr = function ($pair) {
    return Slab_Data::money_range($pair, 0, ' to ');
};

$area = 576;
$c4 = Slab_Data::size_costs(24, 24, 4);
$c5 = Slab_Data::size_costs(24, 24, 5);
$c6 = Slab_Data::size_costs(24, 24, 6);
$e = $c5['est'];
$e6 = $c6['est'];
$waste = $sd['concrete']['waste_pct'];
$rm = $sd['concrete']['ready_mix_default'];
$dl = $sd['delivery'];
$cap = $dl['truck_capacity_yd'];
$f = $sd['finish'];
$lab = $sd['labor'];
$j = $sd['joints'];
$mono = $sd['monolithic'];
$op = 1 + $sd['overhead_profit'];
$prep = Slab_Data::site_prep_psf();
$grid = Slab_Data::rebar_grid(24, 24, $sd['reinforcement']['rebar_base_spacing_in']);
$grades = $sd['concrete']['psi_grades'];

// Truck loads: fewest with the largest trucks, most with the smallest.
$trucks = function ($yd) use ($cap) {
    $lo = (int) ceil(round($yd / $cap[1], 6));
    $hi = (int) ceil(round($yd / $cap[0], 6));
    return $lo === $hi ? (string) $lo : $lo . ' to ' . $hi;
};

// Thickened edge on the 5 in slab: 12 in wide, 12 or 18 in deep.
$edges = [];
foreach ($mono['edge_depth_in'] as $depth) {
    $yd = Slab_Data::edge_yd(24, 24, 5, $depth);
    $trench = $mono['trenching'][(string) $depth];
    $conc = $yd * $rm;
    $edges[$depth] = [
        'yd' => $yd,
        'conc' => $conc,
        'trench' => $trench,
        'add' => [$conc + $trench[0] + $mono['edge_rebar'][0], $conc + $trench[1] + $mono['edge_rebar'][1]],
        'total_yd' => $e['order_yd'] + $yd,
    ];
}
$d_lo = $mono['edge_depth_in'][0];
$d_hi = $mono['edge_depth_in'][1];
$mono_total = [$e['direct'] + $edges[$d_lo]['add'][0], ($e['direct'] + $edges[$d_hi]['add'][1]) * $op];
$mono_total = Slab_Data::round_pair($mono_total, 10);

// Shop floor: 6 in, 4,000 PSI, steel-trowel finish.
$shop_psi = 4000;
$psi4k = Slab_Data::psi_extra($shop_psi, $e6['order_yd']);
$trowel_add = $area * ($f['trowel']['default'] - $f['broom']['default']);
$shop_direct = [$e6['direct'] + $psi4k[0] + $trowel_add, $e6['direct'] + $psi4k[1] + $trowel_add];
$shop = Slab_Data::round_pair([$shop_direct[0], $shop_direct[1] * $op], 10);
$psi45 = Slab_Data::psi_price(4500);

// Joints.
$sp4 = [4 * $j['ratio_best'][0], 4 * $j['ratio_best'][1]];
$sp5 = [5 * $j['ratio_best'][0], 5 * $j['ratio_best'][1]];
$sp6 = [6 * $j['ratio_best'][0], min($j['cap_ft'], 6 * $j['ratio_best'][1])];

$crew_h = $area * $lab['crew_hours_per_sqft'];
$tons5 = $e['order_yd'] * 27 * $sd['concrete']['density_lb_ft3'] / 2000;
$garage_psf = Slab_Data::garage_psf();

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> a 24x24 concrete slab (576 sq ft) costs about <strong><?php echo esc_html($mr($c5['installed'])); ?></strong> installed at the usual 5 inches for an oversized 2-car garage, and <strong><?php echo esc_html($mr($shop)); ?></strong> as a 6-inch, <?php echo esc_html(number_format($shop_psi)); ?> PSI shop floor with a troweled finish. A 5-inch slab needs <?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³ of concrete with <?php echo (int) $waste; ?>% waste, which is <?php echo esc_html($trucks($e['order_yd'])); ?> truckloads. Pouring a thickened edge with it (a monolithic slab) brings the total to roughly <?php echo esc_html($mr($mono_total)); ?>.</p>

<p>A 24x24 is the size most builders mean by a "real" two-car garage: room for two vehicles with the doors open, plus a workbench along one wall. It is also a popular footprint for a detached workshop. At this size the slab is no longer something you pour in an afternoon. You are ordering more than one truck, thinking about the footing under the walls, and choosing a concrete grade for what the floor will carry. This guide covers each of those choices and what they cost.</p>

<h2>24x24 Slab Cost by Thickness</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Thickness</th><th>Concrete to order</th><th>Truckloads</th><th>DIY materials</th><th>Installed</th><th>Per sq ft</th></tr></thead>
<tbody>
<?php foreach ([4 => $c4, 5 => $c5, 6 => $c6] as $t => $c) : ?>
<tr><td><strong><?php echo (int) $t; ?> in</strong></td><td><?php echo esc_html(number_format($c['est']['order_yd'], 2)); ?> yd³</td><td><?php echo esc_html($trucks($c['est']['order_yd'])); ?></td><td><?php echo esc_html($mr($c['diy'])); ?></td><td><?php echo esc_html($mr($c['installed'])); ?></td><td><?php echo esc_html(Slab_Data::money_range($c['installed_psf'], 2, ' to ')); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption"><?php echo esc_html(Slab_Data::cost_basis_note()); ?> Truckloads assume trucks carrying <?php echo esc_html(Slab_Data::range($cap)); ?> yd³.</div>
</div>

<p>Five inches is the default for a garage this size. Four inches is legal under IRC section R506 (which sets a 3.5-inch minimum) and fine for a hobby room or storage, but a 576 sq ft floor that carries two vehicles is better at 5. Six inches suits a shop with heavy machines, a car lift or a work truck. Each extra inch adds about <?php echo esc_html($m($e6['direct'] - $e['direct'])); ?> at full scope, almost all of it concrete.</p>

<h2>Where the Money Goes on a 5-Inch 24x24</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Item</th><th>Basis</th><th>Cost</th></tr></thead>
<tbody>
<tr><td>Ready-mix concrete</td><td><?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³ at <?php echo esc_html(Slab_Data::money($rm)); ?>/yd³ + <?php echo esc_html($m($dl['fee_default'])); ?> delivery</td><td><?php echo esc_html($m($e['ready_mix'])); ?></td></tr>
<tr><td>Forms</td><td><?php echo (int) $e['perimeter']; ?> ft at <?php echo esc_html(Slab_Data::money($sd['site']['forms_default'], 2)); ?>/ft</td><td><?php echo esc_html($m($e['forms'])); ?></td></tr>
<tr><td>Vapor barrier</td><td>576 sq ft at <?php echo esc_html(Slab_Data::money($sd['site']['vapor_default'], 2)); ?>/sq ft</td><td><?php echo esc_html($m($e['vapor'])); ?></td></tr>
<tr><td>#4 rebar grid</td><td><?php echo (int) $grid['sticks']; ?> twenty-foot bars, about <?php echo (int) $grid['chairs']; ?> chairs</td><td><?php echo esc_html($m($e['rebar'])); ?></td></tr>
<tr><td>Broom finish</td><td>576 sq ft at <?php echo esc_html(Slab_Data::money($f['broom']['default'], 2)); ?>/sq ft</td><td><?php echo esc_html($m($e['broom'])); ?></td></tr>
<tr><td>Placing and finishing labor</td><td>576 sq ft at <?php echo esc_html(Slab_Data::money($lab['sqft_default'], 2)); ?>/sq ft</td><td><?php echo esc_html($m($e['labor'])); ?></td></tr>
<tr><td><strong>Direct cost</strong></td><td>Before overhead and profit</td><td><strong><?php echo esc_html($m($e['direct'])); ?></strong></td></tr>
</tbody>
</table>
</div>
<p>That direct cost works out to <?php echo esc_html(Slab_Data::money($e['direct'] / $area, 2)); ?> per sq ft, inside the <?php echo esc_html(Slab_Data::money_range($garage_psf, 0, ' to ')); ?> per sq ft range our <a href="<?php echo esc_url(home_url('/how-much-does-a-garage-slab-cost/')); ?>">garage slab cost guide</a> uses. Not included: excavation and a gravel base (<?php echo esc_html($m($prep[0] * $area) . ' to ' . $m($prep[1] * $area)); ?>), a pump if the trucks cannot reach, and the footing under the walls, which is the next section.</p>

<h2>Thickened Edge (Monolithic) Option</h2>
<p>Garage walls need a footing. In mild climates the cheapest way to get one is to pour it with the floor: the slab edge is dug deeper and wider so the perimeter becomes a beam that carries the walls. This is a monolithic or thickened-edge slab. A common residential detail is a <?php echo (int) $mono['edge_width_in']; ?>-inch-wide edge, <?php echo esc_html(Slab_Data::r('monolithic.edge_depth_in')); ?> inches deep, with <?php echo (int) $mono['edge_bars']; ?> continuous #4 bars running around it. The deeper the edge, the more concrete and digging:</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Edge depth</th><th>Extra concrete</th><th>Concrete cost</th><th>Trenching</th><th>Edge rebar</th><th>Added to slab</th><th>Total yd³ / trucks</th></tr></thead>
<tbody>
<?php foreach ($edges as $depth => $ed) : ?>
<tr><td><strong><?php echo (int) $depth; ?> in</strong></td><td><?php echo esc_html(number_format($ed['yd'], 2)); ?> yd³</td><td><?php echo esc_html($m($ed['conc'])); ?></td><td><?php echo esc_html($mr($ed['trench'])); ?></td><td><?php echo esc_html($mr($mono['edge_rebar'])); ?></td><td><?php echo esc_html($mr(Slab_Data::round_pair($ed['add'], 10))); ?></td><td><?php echo esc_html(number_format($ed['total_yd'], 2)); ?> yd³ / <?php echo esc_html($trucks($ed['total_yd'])); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">Edge concrete is the ring one edge-width wide, deepened from the 5 in slab to the edge depth, plus <?php echo (int) $waste; ?>% waste, at <?php echo esc_html(Slab_Data::money($rm)); ?>/yd³. Direct costs, before overhead and profit.</div>
</div>
<p>Added to the full-scope 5-inch slab, a monolithic 24x24 comes to about <?php echo esc_html($mr($mono_total)); ?> installed, from direct cost with a shallow edge to a deep edge plus overhead and profit. Whether you can use one is not your choice alone. Footing depth is set by the local frost depth under IRC section R403, and in cold regions the building department will usually require a footing below the frost line, often with a stem wall, instead of a shallow thickened edge (some areas allow a frost-protected shallow foundation with insulation). Ask the building department before pricing, and see the <a href="<?php echo esc_url(home_url('/concrete-foundation-monolithic-slab-cost/')); ?>">monolithic slab foundation guide</a>. The <a href="<?php echo esc_url(home_url('/concrete-footing-calculator/')); ?>">footing calculator</a> sizes a separate footing.</p>

<h2>Shop Floor Spec: 6 Inches and Stronger Concrete</h2>
<p>A shop floor takes point loads that a garage never sees: a two-post lift, a heavy table saw, an engine on a stand, a loaded trailer. For that, most owners go to 6 inches and a stronger mix. The jump in grade is cheap next to the rest of the job:</p>
<ul>
<li><strong><?php echo esc_html(number_format($sd['code']['psi_severe'])); ?> PSI:</strong> <?php echo esc_html($grades[3500]['use']); ?>. The IRC minimum for garage floors in severe-weathering areas (Table R402.2).</li>
<li><strong><?php echo esc_html(number_format($shop_psi)); ?> PSI:</strong> <?php echo esc_html($grades[4000]['use']); ?>. On a 6-inch 24x24 it adds about <?php echo esc_html($mr(Slab_Data::round_out($psi4k, 10))); ?> over <?php echo esc_html(number_format($sd['concrete']['psi_base'])); ?> PSI.</li>
<li><strong><?php echo esc_html(number_format(4500)); ?> PSI:</strong> <?php echo esc_html($grades[4500]['use']); ?>. Expect about <?php echo esc_html(Slab_Data::money_range($psi45, 0, ' to ')); ?> per yd³.</li>
</ul>
<p>Shops usually get a smooth steel-trowel finish so carts roll easily and spills wipe up, which adds about <?php echo esc_html($m($trowel_add)); ?> over broom. Put together, a 6-inch, <?php echo esc_html(number_format($shop_psi)); ?> PSI, troweled 24x24 runs <?php echo esc_html($mr($shop)); ?> installed (from the full-scope direct cost up to that plus <?php echo (int) round($sd['overhead_profit'] * 100); ?>% overhead and profit, so the low end is higher than a plain 6-inch slab's). If you plan a car lift, check the lift maker's slab requirements before the pour; some call for a thicker or stronger slab under the posts. For a larger shop, the size table in our <a href="<?php echo esc_url(home_url('/how-much-does-a-concrete-slab-cost/')); ?>">slab cost guide</a> includes a 30x40.</p>

<h2>Pour Day: Two Trucks, One Slab</h2>
<p>The 5-inch slab alone is <?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³, which is about <?php echo esc_html(Slab_Data::num(round($tons5))); ?> tons of wet concrete. Some plants can send it as one full load; many will send two. Add a thickened edge or go to 6 inches and it is two loads for almost everyone. That changes the plan:</p>
<ul>
<li><strong>Stagger the trucks.</strong> Ask for the second truck to arrive as the first one is finishing, not at the same time and not an hour later. A long gap leaves a cold joint where the two loads meet.</li>
<li><strong>Plan access for both.</strong> The chute reaches about <?php echo esc_html(Slab_Data::r('site.chute_reach_ft')); ?> ft, and a 24-ft slab's far side is out of reach from one parking spot. If trucks cannot drive alongside, budget a line pump (<?php echo esc_html(Slab_Data::money_range($sd['pump']['line_job'], 0, ' to ')); ?>) or a boom pump (<?php echo esc_html(Slab_Data::money_range($sd['pump']['boom_job'], 0, ' to ')); ?>).</li>
<li><strong>Crew size.</strong> About <?php echo esc_html(Slab_Data::num(round($crew_h))); ?> crew-hours of placing and finishing, so a full day for a crew of <?php echo esc_html(Slab_Data::r('labor.crew_size')); ?>.</li>
<li><strong>Joints.</strong> At 5 inches, joints every <?php echo esc_html(Slab_Data::range($sp5)); ?> ft work, so one joint each way at 12 ft makes four 12x12 panels. At 4 inches the limit drops to <?php echo esc_html(Slab_Data::range($sp4)); ?> ft and you need two joints each way. At 6 inches the range is <?php echo esc_html(Slab_Data::range($sp6)); ?> ft. Saw within about <?php echo esc_html(Slab_Data::r('joints.sawcut_h')); ?> hours of finishing.</li>
<li><strong>Curing.</strong> Keep cars off for about <?php echo (int) $sd['curing']['cars_days']; ?> days; the concrete reaches its design strength at about <?php echo (int) $sd['curing']['full_days']; ?> days, so hold heavy shop equipment until then.</li>
</ul>

<h2>DIY or Hire a Pro?</h2>
<p>For most people this is a pro job. Two trucks, a 24-ft screed, a footing that an inspector will check, and a finish that has to be done across 576 sq ft before it sets are a lot to manage with a first-time crew. The DIY materials range (<?php echo esc_html($mr($c5['diy'])); ?> at 5 inches) is tempting next to the installed price, but a failed pour on a slab this size costs more than the labor you were trying to save. Doing the site prep and form work yourself and hiring a finishing crew for pour day is a reasonable middle ground; the <a href="<?php echo esc_url(home_url('/concrete-labor-cost-calculator/')); ?>">labor cost calculator</a> shows what that crew should cost. Before you sign, compare the bid with the <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">quote checker</a> or price your own layout in the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a>. Smaller footprint? See the <a href="<?php echo esc_url(home_url('/20x20-concrete-slab-cost-breakdown/')); ?>">20x20 guide</a>.</p>

<h2>Sources and Method</h2>
<p>Volumes use length × width × thickness plus <?php echo (int) $waste; ?>% waste; thickened-edge volumes add the deepened perimeter ring. Costs use the calculator's line items and default rates (see the <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>) and vary by region and season. Code references: IRC section R506 (slabs on ground), section R403 (footings) and Table R402.2 (concrete strength). Slab and joint practice follows ACI 302.1R and ACI 332 (residential concrete); ready-mix ordering follows ASTM C94.</p>
<?php
$html = ob_get_clean();

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'How much does a 24x24 concrete slab cost?',
            'a' => 'About ' . $mr($c5['installed']) . ' installed at 5 inches thick, the usual spec for a 2-car garage. At 4 inches it is ' . $mr($c4['installed']) . ', and at 6 inches ' . $mr($c6['installed']) . '. Site prep and wall footings are extra.',
        ],
        [
            'q' => 'How many yards of concrete for a 24x24 slab?',
            'a' => 'At 5 inches a 24x24 slab is ' . number_format($e['net_yd'], 2) . ' cubic yards, so order ' . number_format($e['order_yd'], 2) . ' yd³ with ' . $waste . '% waste. At 4 inches order ' . number_format($c4['est']['order_yd'], 2) . ' yd³, and at 6 inches ' . number_format($e6['order_yd'], 2) . ' yd³.',
        ],
        [
            'q' => 'How many concrete trucks does a 24x24 slab need?',
            'a' => 'Mixer trucks carry about ' . Slab_Data::range($cap) . ' cubic yards. A 5-inch 24x24 needs ' . number_format($e['order_yd'], 2) . ' yd³, so ' . $trucks($e['order_yd']) . ' loads. With a thickened edge or at 6 inches, plan on ' . $trucks($e6['order_yd']) . ' loads.',
        ],
        [
            'q' => 'How much does a thickened edge add to a 24x24 garage slab?',
            'a' => 'A ' . $mono['edge_width_in'] . '-inch-wide edge adds ' . number_format($edges[$d_lo]['yd'], 2) . ' yd³ of concrete at ' . $d_lo . ' inches deep, or ' . number_format($edges[$d_hi]['yd'], 2) . ' yd³ at ' . $d_hi . ' inches. With trenching and edge rebar that adds about ' . Slab_Data::money($edges[$d_lo]['add'][0], 0) . ' to ' . Slab_Data::money($edges[$d_hi]['add'][1], 0) . ' before overhead and profit. Local frost depth decides whether a thickened edge is allowed.',
        ],
        [
            'q' => 'How thick should a 24x24 shop floor be?',
            'a' => 'Six inches with ' . number_format($shop_psi) . ' PSI concrete is a common shop spec, and costs about ' . $mr($shop) . ' installed with a troweled finish. Check any car lift maker\'s slab requirements before you pour.',
        ],
    ],
];
