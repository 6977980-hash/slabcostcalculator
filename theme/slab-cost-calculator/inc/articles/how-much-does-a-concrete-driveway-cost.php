<?php
/**
 * Article: How Much Does a Concrete Driveway Cost? (post slug how-much-does-a-concrete-driveway-cost).
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
$op = 1 + $sd['overhead_profit'];

// Driveway sizes, all at 5 inches.
$sizes = [
    '1-car' => [12, 40],
    '2-car' => [20, 40],
    '3-car' => [30, 40],
];
$size_rows = [];
foreach ($sizes as $label => $s) {
    $size_rows[$label] = ['l' => $s[0], 'w' => $s[1], 'c' => Slab_Data::size_costs($s[1], $s[0], 5)];
}
$area = 800;
$d5 = $size_rows['2-car']['c'];
$e = $d5['est'];
$d4 = Slab_Data::size_costs(40, 20, 4);
$d6 = Slab_Data::size_costs(40, 20, 6);
$drive_psf = Slab_Data::driveway_psf();

// Finish upgrades on the 20x40: same method as Slab_Data::stamped_psf().
$broom = $sd['finish']['broom']['default'];
$fin_total = function ($key) use ($sd, $d5, $area, $broom, $op) {
    $add = $sd['finish'][$key]['default'] - $broom;
    return Slab_Data::round_pair([$d5['installed'][0] + $add * $area, $d5['installed'][1] + $add * $area * $op], 10);
};
$exposed = $fin_total('exposed');
$stamped = $fin_total('stamped');
$stamped_psf = Slab_Data::stamped_psf(40, 20, 5);

// PSI.
$code = $sd['code'];
$psi4000_extra = Slab_Data::round_pair(Slab_Data::psi_extra($code['psi_deicing_recommended'], $e['order_yd']), 10);

// Reinforcement.
$grid = Slab_Data::rebar_grid(40, 20, $sd['reinforcement']['rebar_base_spacing_in']);
$rebar_cost = [$area * $sd['reinforcement']['rebar_range'][0], $area * $sd['reinforcement']['rebar_range'][1]];
$mesh_cost = [$area * $sd['reinforcement']['mesh_range'][0], $area * $sd['reinforcement']['mesh_range'][1]];

// Removal, prep, slope, joints.
$demo = [$area * $sd['site']['demo_range'][0], $area * $sd['site']['demo_range'][1]];
$prep = Slab_Data::site_prep_psf();
$frac = function ($s) {
    $p = explode('/', $s);
    return $p[0] / $p[1];
};
$slopes = $sd['site']['drainage_slope_in_per_ft'];
$fall_lo = 40 * $frac($slopes[0]);
$fall_hi = 40 * $frac($slopes[1]);
$joint_span = [$sd['joints']['ratio'][0] * 5, $sd['joints']['ratio'][1] * 5];
$joint_lf = 40 + 3 * 20;
$cut = [$joint_lf * $sd['joints']['cut_price_lf'][0], $joint_lf * $sd['joints']['cut_price_lf'][1]];
$asphalt = [$area * $sd['asphalt_driveway_psf'][0], $area * $sd['asphalt_driveway_psf'][1]];

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> a standard 2-car concrete driveway (20x40 ft, 800 sq ft) at 5 inches thick costs about <strong><?php echo esc_html($mr($d5['installed'])); ?></strong> installed with a broom finish, or roughly <?php echo esc_html(Slab_Data::money_range($drive_psf, 0, ' to ')); ?> per sq ft. A 1-car driveway runs about <?php echo esc_html($mr($size_rows['1-car']['c']['installed'])); ?> and a 3-car about <?php echo esc_html($mr($size_rows['3-car']['c']['installed'])); ?>. Stamped concrete raises the 2-car price to roughly <?php echo esc_html($mr($stamped)); ?>.</p>

<p>A driveway is one of the larger concrete jobs most homeowners ever buy. It also takes more abuse than a patio: car and truck weight, freezing water, de-icing salt and runoff from the roof and street. This guide prices the common sizes, then walks through the design choices that decide whether the slab lasts for decades or starts cracking within a few years.</p>

<h2>Driveway Cost by Size (5-Inch Slab)</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Driveway</th><th>Size</th><th>Area</th><th>Concrete (+<?php echo (int) $sd['concrete']['waste_pct']; ?>%)</th><th>Installed, broom finish</th></tr></thead>
<tbody>
<?php foreach ($size_rows as $label => $r) : ?>
<tr><td><strong><?php echo esc_html($label); ?></strong></td><td><?php echo (int) $r['l']; ?> × <?php echo (int) $r['w']; ?> ft</td><td><?php echo esc_html(number_format($r['l'] * $r['w'])); ?> sq ft</td><td><?php echo esc_html(number_format($r['c']['est']['order_yd'], 2)); ?> yd³</td><td><?php echo esc_html($mr($r['c']['installed'])); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption"><?php echo esc_html(Slab_Data::cost_basis_note()); ?></div>
</div>
<p>A 1-car driveway is usually 10 to 12 ft wide, a 2-car about 20 ft and a 3-car about 30 ft. Length depends on your setback, so we used 40 ft for all three. Run your exact measurements through the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a>. These prices are for a new slab on prepared ground; tearing out an old driveway is extra (see below).</p>

<h2>How Thick Should a Driveway Be?</h2>
<p>The IRC sets a 3.5-inch minimum for concrete floors on ground, but that is a floor, not a driveway target. For passenger cars, 4 inches works on firm, well-drained soil. We recommend 5 inches for most driveways because it handles pickups, delivery vans and soft spots in the subgrade with much less cracking. Go to 6 inches for RVs, boats on trailers or a concrete truck that will need to drive on it later.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>20x40 driveway</th><th>Concrete to order</th><th>Installed</th><th>Per sq ft</th></tr></thead>
<tbody>
<?php foreach ([4 => $d4, 5 => $d5, 6 => $d6] as $t => $c) : ?>
<tr><td><strong><?php echo (int) $t; ?> in</strong></td><td><?php echo esc_html(number_format($c['est']['order_yd'], 2)); ?> yd³</td><td><?php echo esc_html($mr($c['installed'])); ?></td><td><?php echo esc_html(Slab_Data::money_range($c['installed_psf'], 2, ' to ')); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<p>Going from 4 to 5 inches adds about <?php echo esc_html(number_format($d5['est']['order_yd'] - $d4['est']['order_yd'], 2)); ?> cubic yards and only about <?php echo esc_html($m(round(($d5['est']['direct'] - $d4['est']['direct']) / 10) * 10)); ?> to the direct cost, because forms, finishing and labor stay the same. It is the cheapest durability upgrade on the job. Our <a href="<?php echo esc_url(home_url('/how-thick-should-a-concrete-slab-be/')); ?>">slab thickness guide</a> covers the trade-offs in more detail.</p>

<h2>Finish Options and What They Cost</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Finish</th><th>Finish rate</th><th>2-car driveway installed</th><th>Notes</th></tr></thead>
<tbody>
<tr><td><strong>Broom</strong></td><td><?php echo esc_html(Slab_Data::mr('finish.broom.range', 2)); ?>/sq ft</td><td><?php echo esc_html($mr($d5['installed'])); ?></td><td>Standard. Good traction, hides tire marks.</td></tr>
<tr><td><strong>Exposed aggregate</strong></td><td><?php echo esc_html(Slab_Data::mr('finish.exposed.range', 2)); ?>/sq ft</td><td><?php echo esc_html($mr($exposed)); ?></td><td>Very good traction on slopes; textured look.</td></tr>
<tr><td><strong>Stamped</strong></td><td><?php echo esc_html(Slab_Data::mr('finish.stamped.range', 2)); ?>/sq ft</td><td><?php echo esc_html($mr($stamped)); ?></td><td>About <?php echo esc_html(Slab_Data::money_range($stamped_psf, 0, ' to ')); ?>/sq ft in total. Needs regular resealing.</td></tr>
</tbody>
</table>
</div>
<p>A smooth steel-trowel finish is a poor choice outdoors: it gets slick when wet or icy. A popular middle path is a broom-finish field with a stamped or colored border, which gives most of the look for a fraction of the price. Stamped driveways also need a sealer every few years, which is an ongoing cost that broom finish mostly avoids.</p>

<h2>Concrete Strength: PSI and De-Icing Salt</h2>
<p>IRC Table R402.2 sets minimum strengths by weathering exposure. It does not list driveways by name, but in a severe-weathering (freeze-thaw) region it requires at least <?php echo esc_html(number_format($code['psi_severe'])); ?> PSI, air-entrained concrete for garage floors, carport slabs, porches and steps, and that is the sensible minimum for a driveway too. Where you or the city will use de-icing salt, we recommend <?php echo esc_html(number_format($code['psi_deicing_recommended'])); ?> PSI with <?php echo esc_html(Slab_Data::r('concrete.air_entrainment_pct')); ?>% entrained air. The tiny air bubbles give freezing water room to expand, which is what stops the surface from flaking (scaling).</p>
<p>The upgrade is cheap. Each <?php echo esc_html(number_format($sd['concrete']['psi_step'])); ?>-PSI step adds about <?php echo esc_html(Slab_Data::mr('concrete.psi_step_price', 0, ' to ')); ?> per cubic yard, so <?php echo esc_html(number_format($code['psi_deicing_recommended'])); ?> PSI instead of <?php echo esc_html(number_format($sd['concrete']['psi_base'])); ?> on a 2-car driveway (<?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³) costs only about <?php echo esc_html($mr($psi4000_extra)); ?> more. Also: do not use salt on new concrete during its first winter, and never use products containing ammonium nitrate or ammonium sulfate, which attack concrete chemically.</p>

<h2>Reinforcement: Rebar or Mesh</h2>
<p>For a driveway, #4 rebar on chairs is the better choice. At <?php echo (int) $sd['reinforcement']['rebar_base_spacing_in']; ?>-inch centers a 20x40 driveway takes about <?php echo (int) $grid['sticks']; ?> twenty-foot bars and <?php echo (int) $grid['chairs']; ?> chairs, roughly <?php echo esc_html($mr($rebar_cost)); ?> installed. Welded wire mesh costs about <?php echo esc_html($mr($mesh_cost)); ?>, but it often ends up trodden to the bottom of the slab where it does little. Reinforcement does not stop cracks from forming; it holds cracks tight so the panels do not shift. Lay out bars with the <a href="<?php echo esc_url(home_url('/concrete-rebar-calculator/')); ?>">rebar calculator</a>.</p>

<h2>Joints, Slope and Drainage</h2>
<p>Control joints for a 5-inch slab go every <?php echo esc_html(Slab_Data::num($joint_span[0])); ?> to <?php echo esc_html(Slab_Data::num($joint_span[1])); ?> ft (<?php echo esc_html(Slab_Data::r('joints.ratio')); ?> times the thickness), cut a quarter of the slab deep. On a 20x40 driveway that usually means one joint down the center and cross joints every 10 ft: eight 10x10 panels and about <?php echo (int) $joint_lf; ?> linear ft of joints. Saw-cutting that runs about <?php echo esc_html($mr($cut)); ?> if it is not already in the bid, and it should happen <?php echo esc_html(Slab_Data::r('joints.sawcut_h')); ?> hours after finishing.</p>
<div class="formula-box">
<div class="formula-label">Slope on a 40 ft driveway</div>
<div class="formula-math">40 ft × <?php echo esc_html($slopes[0]); ?> in/ft = <?php echo esc_html(Slab_Data::num($fall_lo)); ?> in &nbsp;|&nbsp; 40 ft × <?php echo esc_html($slopes[1]); ?> in/ft = <?php echo esc_html(Slab_Data::num($fall_hi)); ?> in</div>
<div class="formula-explanation">Aim for at least <?php echo esc_html($sd['site']['drainage_slope_preferred']); ?> inch of fall per foot, away from the garage and the house, toward the street or a drain. Water that sits on the slab or soaks the base under it is the most common cause of early failure.</div>
</div>
<p>If the driveway slopes toward the garage, a trench drain at the garage door is cheaper than a wet garage. Steep driveways need a transition at the top and bottom so cars do not scrape.</p>

<h2>Apron, Approach and Removing the Old Driveway</h2>
<p><strong>The apron</strong> (the strip between the sidewalk or curb and the street) usually sits on city right-of-way. Many cities require a separate right-of-way permit for it, set its thickness and mix (often thicker than the driveway itself), and may require a licensed contractor. Ask your public works department before you get bids, and make sure each bid says whether the apron and any curb cut are included.</p>
<p><strong>Removing an old driveway</strong> costs about <?php echo esc_html(Slab_Data::mr('site.demo_range', 0, ' to ')); ?> per sq ft for a plain 4-inch slab, or <?php echo esc_html($mr($demo)); ?> for 800 sq ft. Thicker or reinforced concrete lands at or above the high end. Asphalt usually comes out cheaper. Check your numbers with the <a href="<?php echo esc_url(home_url('/concrete-removal-cost-calculator/')); ?>">concrete removal cost calculator</a>. After tear-out, the base often needs rework: excavation and a fresh compacted gravel base run about <?php echo esc_html(Slab_Data::money_range($prep, 2, ' to ')); ?> per sq ft, or <?php echo esc_html($m($prep[0] * $area) . ' to ' . $m($prep[1] * $area)); ?> for 800 sq ft.</p>

<div class="callout callout-warning">
<div class="callout-title">Do not drive on it too early</div>
<p>Keep cars off a new driveway for at least <?php echo (int) $sd['curing']['cars_days']; ?> days and heavy trucks off for <?php echo (int) $sd['curing']['full_days']; ?> days, while the concrete gains strength. Keep it moist or use a curing compound for the first week. If you have nowhere else to park, plan for that before the pour.</p>
</div>

<h2>Permits and Inspections</h2>
<p>Many towns require a permit for a new driveway, partly because it adds impervious surface and changes runoff. Small residential flatwork permits typically cost <?php echo esc_html(Slab_Data::mr('permit.flatwork', 0, ' to ')); ?>, and the apron permit may be separate. Some places limit total lot coverage or driveway width. See <a href="<?php echo esc_url(home_url('/concrete-slab-permits-and-costs/')); ?>">do you need a permit for a concrete slab</a>.</p>

<h2>Concrete vs. Asphalt</h2>
<p>Asphalt is cheaper up front: about <?php echo esc_html(Slab_Data::mr('asphalt_driveway_psf', 0, ' to ')); ?> per sq ft installed, or <?php echo esc_html($mr($asphalt)); ?> for 800 sq ft. Concrete costs more but usually lasts much longer with less upkeep, and it handles heat better. Asphalt flexes more in freeze-thaw climates and needs sealcoating every few years. Our <a href="<?php echo esc_url(home_url('/concrete-vs-asphalt-driveway/')); ?>">concrete vs. asphalt driveway comparison</a> covers lifespan, maintenance and climate in detail.</p>

<h2>DIY or Hire a Pro?</h2>
<p>An 800 sq ft driveway is a hard DIY job. It is <?php echo esc_html(number_format($e['order_yd'], 2)); ?> cubic yards, more than one truckload (a mixer truck carries about <?php echo esc_html(Slab_Data::r('delivery.truck_capacity_yd')); ?> yd³), and it all has to be placed, screeded, floated and jointed before it sets. A pro crew of <?php echo esc_html(Slab_Data::r('labor.crew_size')); ?> finishers handles that routinely. Where DIY makes sense is the prep: demolition, hauling and base work, if your contractor will price the job that way. Before you sign, run any bid through the <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">concrete quote checker</a> and make sure it states thickness, PSI and air entrainment, reinforcement, base depth, joint spacing and whether removal and the apron are included.</p>

<h2>Sources and Method</h2>
<p>Costs come from the same line items and default rates as our calculator (see our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>). They are national working benchmarks, and local prices vary by region, season and access. Strength and air-entrainment minimums follow IRC Table R402.2; slab-on-ground minimums follow IRC R506; joint and finishing practice follows ACI 302.1R and ACI 332 (residential concrete); curing follows ACI 308R. Your local building department and any city right-of-way rules have the final say.</p>
<?php
$html = ob_get_clean();

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'How much does a concrete driveway cost?',
            'a' => 'A 2-car driveway (20x40 ft, 800 sq ft) at 5 inches thick costs about ' . $mr($d5['installed']) . ' installed with a broom finish, or about ' . Slab_Data::money_range($drive_psf, 0, ' to ') . ' per sq ft. Removing an old driveway and stamped finishes cost extra.',
        ],
        [
            'q' => 'How thick should a concrete driveway be?',
            'a' => 'Five inches is the best all-round choice for cars, pickups and vans. Four inches can work for light cars on firm, well-drained soil, and 6 inches is better for RVs, trailers or heavy trucks.',
        ],
        [
            'q' => 'What PSI concrete should I use for a driveway?',
            'a' => 'In freeze-thaw regions use at least ' . number_format($code['psi_severe']) . ' PSI air-entrained concrete, the IRC minimum for garage floors and other exposed slabs. Where de-icing salt is used, ' . number_format($code['psi_deicing_recommended']) . ' PSI with ' . Slab_Data::r('concrete.air_entrainment_pct') . '% air is the safer choice. On a 2-car driveway the upgrade from ' . number_format($sd['concrete']['psi_base']) . ' PSI costs only about ' . $mr($psi4000_extra) . '.',
        ],
        [
            'q' => 'How much does a stamped concrete driveway cost?',
            'a' => 'About ' . Slab_Data::money_range($stamped_psf, 0, ' to ') . ' per sq ft, or roughly ' . $mr($stamped) . ' for an 800 sq ft 2-car driveway at 5 inches. Stamped concrete also needs resealing every few years.',
        ],
        [
            'q' => 'How long before I can drive on a new concrete driveway?',
            'a' => 'Wait at least ' . $sd['curing']['cars_days'] . ' days before driving a car on it and ' . $sd['curing']['full_days'] . ' days before heavy trucks. Walking on it is usually fine after ' . Slab_Data::r('curing.foot_traffic_h') . ' hours.',
        ],
        [
            'q' => 'How much does it cost to remove an old concrete driveway?',
            'a' => 'About ' . Slab_Data::mr('site.demo_range', 0, ' to ') . ' per sq ft for a plain 4-inch slab, or ' . $mr($demo) . ' for 800 sq ft. Thick or reinforced concrete costs more to break out and haul.',
        ],
    ],
];
