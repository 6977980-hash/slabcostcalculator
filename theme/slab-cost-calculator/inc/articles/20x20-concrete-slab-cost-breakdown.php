<?php
/**
 * Article: 20x20 Concrete Slab Cost (post slug 20x20-concrete-slab-cost-breakdown).
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

$area = 400;
$c4 = Slab_Data::size_costs(20, 20, 4);
$c5 = Slab_Data::size_costs(20, 20, 5);
$c6 = Slab_Data::size_costs(20, 20, 6);
$p = $c4['est'];   // patio, 4 in
$g = $c5['est'];   // garage floor, 5 in
$waste = $sd['concrete']['waste_pct'];
$dl = $sd['delivery'];
$f = $sd['finish'];
$lab = $sd['labor'];
$j = $sd['joints'];
$code = $sd['code'];
$op = 1 + $sd['overhead_profit'];
$prep = Slab_Data::site_prep_psf();
$grid = Slab_Data::rebar_grid(20, 20, $sd['reinforcement']['rebar_base_spacing_in']);

// Garage floor upgrade to the IRC severe-weathering grade.
$psi_up = Slab_Data::psi_extra($code['psi_severe'], $g['order_yd']);
$psi_up_r = Slab_Data::round_out($psi_up, 10);
$trowel_add = $area * ($f['trowel']['default'] - $f['broom']['default']);

// Bags vs. ready-mix.
$tons = $p['bags80'] * 80 / 2000;
$bag_gap = $p['bagged'] - $p['ready_mix'];
$short_to6 = max(0, $dl['common_min_yd'][1] - $p['order_yd']);
$short_fee = [$short_to6 * $dl['short_load_per_yd'][0], $short_to6 * $dl['short_load_per_yd'][1]];

// Pour day.
$unload_p = [$p['order_yd'] * $dl['unload_min_per_yd'][0], $p['order_yd'] * $dl['unload_min_per_yd'][1]];
$unload_g = [$g['order_yd'] * $dl['unload_min_per_yd'][0], $g['order_yd'] * $dl['unload_min_per_yd'][1]];
$crew_h = $area * $lab['crew_hours_per_sqft'];
$sp4 = [4 * $j['ratio_best'][0], 4 * $j['ratio_best'][1]];
$sp5 = [5 * $j['ratio_best'][0], 5 * $j['ratio_best'][1]];
$joint_lf = 2 * 20; // one 20 ft joint each way

// Large patio finishes.
$stamped = Slab_Data::stamped_psf();
$exposed_add = $area * ($f['exposed']['default'] - $f['broom']['default']);

// Slope over 20 ft for each use.
$fall_patio = [20 / 8, 20 / 4];

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> a 20x20 concrete slab (400 sq ft) costs about <strong><?php echo esc_html($mr($c4['installed'])); ?></strong> installed as a 4-inch patio, and <strong><?php echo esc_html($mr($c5['installed'])); ?></strong> as a 5-inch 2-car garage floor. That is <?php echo esc_html(Slab_Data::money_range(Slab_Data::installed_psf(), 0, ' to ')); ?> per sq ft. A patio needs <?php echo esc_html(number_format($p['order_yd'], 2)); ?> yd³ of concrete with <?php echo (int) $waste; ?>% waste; a garage floor needs <?php echo esc_html(number_format($g['order_yd'], 2)); ?> yd³. At either thickness one ready-mix truck is far cheaper than bags.</p>

<p>A 20x20 is the size where a backyard slab turns into a real construction job. It is the footprint of a small two-car garage and of a large patio with room for a dining set, a grill and a seating area. The two uses look alike on a plan, but a garage floor is thicker, stronger and built to a different standard. This guide prices both side by side, explains why bags stop making sense at this size, and walks through what pour day looks like.</p>

<h2>Patio vs. Garage Floor: Side by Side</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th></th><th>Large patio</th><th>2-car garage floor</th></tr></thead>
<tbody>
<tr><td>Thickness</td><td>4 in</td><td>5 in</td></tr>
<tr><td>Concrete grade</td><td><?php echo esc_html(number_format($code['psi_exposed'])); ?> PSI</td><td><?php echo esc_html(number_format($code['psi_severe'])); ?> PSI air-entrained in freeze-thaw areas</td></tr>
<tr><td>Concrete to order (+<?php echo (int) $waste; ?>%)</td><td><?php echo esc_html(number_format($p['order_yd'], 2)); ?> yd³</td><td><?php echo esc_html(number_format($g['order_yd'], 2)); ?> yd³</td></tr>
<tr><td>Reinforcement</td><td>Mesh or #4 rebar</td><td>#4 rebar at <?php echo (int) $sd['reinforcement']['rebar_base_spacing_in']; ?> in on chairs</td></tr>
<tr><td>Vapor barrier</td><td>Usually not needed</td><td>6-mil under an enclosed floor</td></tr>
<tr><td>Slope</td><td><?php echo esc_html($sd['site']['drainage_slope_in_per_ft'][0]); ?> to <?php echo esc_html($sd['site']['drainage_slope_in_per_ft'][1]); ?> in per ft away from the house</td><td>Toward the door, so water and snowmelt run out</td></tr>
<tr><td>Control joint spacing</td><td><?php echo esc_html(Slab_Data::range($sp4)); ?> ft</td><td><?php echo esc_html(Slab_Data::range($sp5)); ?> ft</td></tr>
<tr><td>Installed cost</td><td><strong><?php echo esc_html($mr($c4['installed'])); ?></strong></td><td><strong><?php echo esc_html($mr($c5['installed'])); ?></strong></td></tr>
<tr><td>DIY materials</td><td><?php echo esc_html($mr($c4['diy'])); ?></td><td><?php echo esc_html($mr($c5['diy'])); ?></td></tr>
</tbody>
</table>
<div class="table-caption"><?php echo esc_html(Slab_Data::cost_basis_note()); ?></div>
</div>

<p>The extra inch for a garage costs only about <?php echo esc_html($m($g['direct'] - $p['direct'])); ?> at full scope, because it changes the concrete line and nothing else. That is cheap insurance. IRC section R506 allows a 3.5-inch floor slab, but car tires put concentrated loads near the edges and joints, so most builders treat 5 inches as the practical minimum for a garage floor (our <a href="<?php echo esc_url(home_url('/how-thick-should-a-concrete-slab-be/')); ?>">thickness guide</a> explains why). If the garage will hold a heavy pickup, a camper or a lift, look at 6 inches (<?php echo esc_html($mr($c6['installed'])); ?> installed).</p>

<h2>Line Items for Each Version</h2>
<p>Both columns use the full scope from our <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a>: ready-mix, forms, vapor barrier, a #4 rebar grid, broom finish and labor at <?php echo esc_html(Slab_Data::money($lab['sqft_default'], 2)); ?>/sq ft. A plain patio would drop the vapor barrier and might swap rebar for mesh.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Item</th><th>4 in patio</th><th>5 in garage</th></tr></thead>
<tbody>
<tr><td>Ready-mix + <?php echo esc_html($m($dl['fee_default'])); ?> delivery</td><td><?php echo esc_html($m($p['ready_mix'])); ?></td><td><?php echo esc_html($m($g['ready_mix'])); ?></td></tr>
<tr><td>Forms (<?php echo (int) $p['perimeter']; ?> ft)</td><td><?php echo esc_html($m($p['forms'])); ?></td><td><?php echo esc_html($m($g['forms'])); ?></td></tr>
<tr><td>Vapor barrier</td><td><?php echo esc_html($m($p['vapor'])); ?></td><td><?php echo esc_html($m($g['vapor'])); ?></td></tr>
<tr><td>#4 rebar (<?php echo (int) $grid['sticks']; ?> bars, <?php echo (int) $grid['chairs']; ?> chairs)</td><td><?php echo esc_html($m($p['rebar'])); ?></td><td><?php echo esc_html($m($g['rebar'])); ?></td></tr>
<tr><td>Broom finish</td><td><?php echo esc_html($m($p['broom'])); ?></td><td><?php echo esc_html($m($g['broom'])); ?></td></tr>
<tr><td>Placing and finishing labor</td><td><?php echo esc_html($m($p['labor'])); ?></td><td><?php echo esc_html($m($g['labor'])); ?></td></tr>
<tr><td><strong>Direct cost</strong></td><td><strong><?php echo esc_html($m($p['direct'])); ?></strong></td><td><strong><?php echo esc_html($m($g['direct'])); ?></strong></td></tr>
<tr><td>With <?php echo (int) round($sd['overhead_profit'] * 100); ?>% overhead and profit</td><td><?php echo esc_html($m($p['direct'] * $op)); ?></td><td><?php echo esc_html($m($g['direct'] * $op)); ?></td></tr>
</tbody>
</table>
</div>
<p>Two garage extras are not in that table. Moving from <?php echo esc_html(number_format($code['psi_exposed'])); ?> to <?php echo esc_html(number_format($code['psi_severe'])); ?> PSI adds about <?php echo esc_html($mr($psi_up_r)); ?> for this volume. A smooth steel-trowel finish, which many people prefer inside a garage because it sweeps clean, adds about <?php echo esc_html($m($trowel_add)); ?> over broom. Labor is about <?php echo (int) round($p['labor'] / $p['direct'] * 100); ?>% of the direct cost on the patio and <?php echo (int) round($g['labor'] / $g['direct'] * 100); ?>% on the garage floor, which is why the DIY savings at this size are real but so is the workload.</p>

<div class="callout callout-warning">
<div class="callout-title">A garage needs more than a slab</div>
<p>A floor slab alone does not hold up walls. The garage walls sit on footings: either a thickened edge poured with the slab (a monolithic slab) or a separate footing and stem wall below the frost line. That work is priced separately and depends on your climate. See <a href="<?php echo esc_url(home_url('/how-much-does-a-garage-slab-cost/')); ?>">how much a garage slab costs</a> for monolithic vs. stem-wall pricing, and the <a href="<?php echo esc_url(home_url('/24x24-concrete-slab-cost-breakdown/')); ?>">24x24 guide</a> for a worked thickened-edge example.</p>
</div>

<h2>Why Ready-Mix Clearly Wins at 20x20</h2>
<p>At 4 inches, a 20x20 needs <?php echo (int) $p['bags80']; ?> bags of 80-lb mix. That costs about <?php echo esc_html($m($p['bagged'])); ?>, against <?php echo esc_html($m($p['ready_mix'])); ?> for <?php echo esc_html(number_format($p['order_yd'], 2)); ?> yd³ of ready-mix with delivery. The truck saves about <?php echo esc_html($m($bag_gap)); ?> before you even count the mixer rental, and it saves you from lifting about <?php echo esc_html(Slab_Data::num($tons, 1)); ?> tons of bags. At 5 inches the gap grows to <?php echo esc_html($m($g['bagged'] - $g['ready_mix'])); ?> (<?php echo (int) $g['bags80']; ?> bags).</p>
<p>There is a bigger reason than price. Hand-mixing hundreds of bags takes hours, so the first batches start to stiffen before the last ones go in. You end up with cold joints and a patchy finish across a slab that should look like one piece. A <?php echo esc_html(number_format($p['order_yd'], 2)); ?> yd³ order is also at or near most plants' usual minimum load of <?php echo esc_html(Slab_Data::range($dl['common_min_yd'], '–')); ?> yards, so short-load fees are small or zero. If your supplier's minimum is <?php echo esc_html(Slab_Data::num($dl['common_min_yd'][1])); ?> yards, the per-yard short-load charge on a 4-inch patio is only about <?php echo esc_html($mr($short_fee)); ?>. The <a href="<?php echo esc_url(home_url('/ready-mix-concrete-calculator/')); ?>">ready-mix calculator</a> rounds your order to what the plant will sell.</p>

<h2>Pour Day for a 20x20</h2>
<ul>
<li><strong>One truck.</strong> Mixer trucks carry about <?php echo esc_html(Slab_Data::r('delivery.truck_capacity_yd')); ?> yd³, so either version fits in a single load. Unloading takes about <?php echo esc_html(Slab_Data::range(Slab_Data::round_pair($unload_p, 5))); ?> minutes for the patio and <?php echo esc_html(Slab_Data::range(Slab_Data::round_pair($unload_g, 5))); ?> minutes for the garage floor at a normal pace. Most suppliers include a set amount of unloading time and charge for extra waiting, so be ready when the truck arrives.</li>
<li><strong>Access.</strong> The chute reaches about <?php echo esc_html(Slab_Data::r('site.chute_reach_ft')); ?> ft from where the truck parks. The far side of a 20-ft slab is often beyond that, so plan for wheelbarrows or a line pump (<?php echo esc_html(Slab_Data::money_range($sd['pump']['line_job'], 0, ' to ')); ?> for the job).</li>
<li><strong>Crew.</strong> A contractor spends about <?php echo esc_html(Slab_Data::num(round($crew_h))); ?> crew-hours placing and finishing 400 sq ft, so a crew of <?php echo esc_html(Slab_Data::r('labor.crew_size')); ?> is busy most of a day.</li>
<li><strong>Joints.</strong> One joint each way at the 10-ft mark gives four 10x10 panels, inside the spacing limit for both 4 and 5 inches. That is <?php echo (int) $joint_lf; ?> linear ft of joint, cut to a quarter of the slab depth. With a conventional saw, cut within about <?php echo esc_html(Slab_Data::r('joints.sawcut_h')); ?> hours of finishing.</li>
<li><strong>Curing.</strong> Foot traffic after <?php echo esc_html(Slab_Data::r('curing.foot_traffic_h')); ?> hours; wait about <?php echo (int) $sd['curing']['cars_days']; ?> days before parking a car on a new garage floor.</li>
</ul>

<h2>Large Patio Upgrades</h2>
<p>Finish choice moves the price of a 400 sq ft patio more than thickness does. Exposed aggregate adds about <?php echo esc_html($m($exposed_add)); ?> in finish cost over broom. A fully stamped 20x20 runs about <?php echo esc_html(Slab_Data::money_range($stamped, 0, ' to ')); ?> per sq ft installed, or <?php echo esc_html($m($stamped[0] * $area) . ' to ' . $m($stamped[1] * $area)); ?>. Many homeowners stamp a border and broom-finish the middle to get most of the look for less. Over 20 ft, the drainage slope drops the outer edge about <?php echo esc_html(Slab_Data::num($fall_patio[0], 1)); ?> to <?php echo esc_html(Slab_Data::num($fall_patio[1])); ?> inches below the house side. More in the <a href="<?php echo esc_url(home_url('/concrete-patio-slab-cost-guide/')); ?>">patio slab cost guide</a>.</p>

<h2>What Else Changes the Price</h2>
<ul>
<li><strong>Site prep:</strong> excavation and a compacted gravel base add about <?php echo esc_html($m($prep[0] * $area) . ' to ' . $m($prep[1] * $area)); ?>. A garage floor should always have a compacted base; the <a href="<?php echo esc_url(home_url('/gravel-base-calculator/')); ?>">gravel base calculator</a> sizes it.</li>
<li><strong>Removing an old slab:</strong> about <?php echo esc_html(Slab_Data::money_range($sd['site']['demo_range'], 0, ' to ')); ?> per sq ft, or <?php echo esc_html($m($sd['site']['demo_range'][0] * $area) . ' to ' . $m($sd['site']['demo_range'][1] * $area)); ?>.</li>
<li><strong>Permits:</strong> a garage almost always needs one; a detached patio often does not. Flatwork permits run roughly <?php echo esc_html(Slab_Data::money_range($sd['permit']['flatwork'], 0, ' to ')); ?>, and a garage building permit costs more.</li>
</ul>

<h2>DIY or Hire a Pro?</h2>
<p>A 20x20 is a big step up from a shed pad. Screeding a 20-ft span needs a long straightedge and two people working in step, and a full truck has to be placed and finished before it sets. DIY can save the labor line, roughly <?php echo esc_html($m($sd['labor']['sqft_range'][0] * $area) . ' to ' . $m($lab['sqft_default'] * $area)); ?>, but only if you have experienced help. For a garage floor tied to footings and an inspection, most homeowners hire out. Check any quote with the <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">concrete quote checker</a>, and see our <a href="<?php echo esc_url(home_url('/diy-concrete-slab-what-you-can-and-shouldnt-do/')); ?>">DIY guide</a>. Other sizes: <a href="<?php echo esc_url(home_url('/12x12-concrete-slab-cost-breakdown/')); ?>">12x12</a> and <a href="<?php echo esc_url(home_url('/24x24-concrete-slab-cost-breakdown/')); ?>">24x24</a>.</p>

<h2>Sources and Method</h2>
<p>Quantities use length × width × thickness plus <?php echo (int) $waste; ?>% waste. Costs use the calculator's line items and default rates (see our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>) and vary by region and season. Concrete strength for garage floors follows IRC Table R402.2; slab and base guidance follows IRC section R506 and ACI 302.1R; ready-mix delivery practice follows ASTM C94.</p>
<?php
$html = ob_get_clean();

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'How much does a 20x20 concrete slab cost?',
            'a' => 'About ' . $mr($c4['installed']) . ' installed at 4 inches thick, and ' . $mr($c5['installed']) . ' at 5 inches for a garage floor. DIY materials run ' . $mr($c4['diy']) . ' at 4 inches. Excavation and a gravel base are extra.',
        ],
        [
            'q' => 'How many yards of concrete do I need for a 20x20 slab?',
            'a' => 'At 4 inches a 20x20 slab is ' . number_format($p['net_yd'], 2) . ' cubic yards, so order ' . number_format($p['order_yd'], 2) . ' yd³ with ' . $waste . '% waste. At 5 inches order ' . number_format($g['order_yd'], 2) . ' yd³, and at 6 inches ' . number_format($c6['est']['order_yd'], 2) . ' yd³.',
        ],
        [
            'q' => 'How thick should a 20x20 garage slab be?',
            'a' => 'Five inches is a sensible minimum for a 2-car garage floor, with ' . number_format($code['psi_severe']) . ' PSI air-entrained concrete in freeze-thaw areas. Use 6 inches for heavy trucks, campers or a car lift. A 4-inch slab is fine for a patio.',
        ],
        [
            'q' => 'Should I use bags or ready-mix for a 20x20 slab?',
            'a' => 'Ready-mix. A 4-inch 20x20 takes ' . $p['bags80'] . ' bags of 80-lb mix (about ' . $m($p['bagged']) . '), while ready-mix with delivery costs about ' . $m($p['ready_mix']) . '. Hand-mixing that much concrete also risks cold joints.',
        ],
        [
            'q' => 'How much does a 20x20 stamped concrete patio cost?',
            'a' => 'About ' . Slab_Data::money_range($stamped, 0, ' to ') . ' per sq ft installed, or ' . $m($stamped[0] * $area) . ' to ' . $m($stamped[1] * $area) . ' for 400 sq ft, depending on the pattern and colors.',
        ],
    ],
];
