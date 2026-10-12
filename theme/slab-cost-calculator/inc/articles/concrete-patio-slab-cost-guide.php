<?php
/**
 * Article: Concrete Patio Slab Cost Guide (post slug concrete-patio-slab-cost-guide).
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
$f = $sd['finish'];

// Finish upgrade on any size, same method as Slab_Data::stamped_psf().
$with_finish = function ($c, $key) use ($f, $op) {
    $add = $f[$key]['default'] - $f['broom']['default'];
    $area = $c['est']['area'];
    return Slab_Data::round_pair([$c['installed'][0] + $add * $area, $c['installed'][1] + $add * $area * $op], 10);
};

$sizes = [[10, 12, 'Bistro / grill pad'], [12, 16, 'Table for 6'], [16, 20, 'Dining + lounge'], [20, 20, 'Large entertaining']];
$rows = [];
foreach ($sizes as $s) {
    $c = Slab_Data::size_costs($s[0], $s[1], 4);
    $rows[] = ['l' => $s[0], 'w' => $s[1], 'use' => $s[2], 'c' => $c, 'stamped' => $with_finish($c, 'stamped')];
}

// Worked example: 12x16.
$p = Slab_Data::size_costs(12, 16, 4);
$e = $p['est'];
$area = 192;
$finishes = [
    'broom' => ['Broom', $p['installed'], 'Best traction. The default for outdoor slabs.'],
    'trowel' => ['Smooth trowel', $with_finish($p, 'trowel'), 'Slick when wet. Better under a roof or screen room.'],
    'exposed' => ['Exposed aggregate', $with_finish($p, 'exposed'), 'Pebble surface, good grip, hides dirt well.'],
    'stamped' => ['Stamped (with color)', $with_finish($p, 'stamped'), 'Looks like stone or brick. Needs resealing.'],
];
$stamped_psf = Slab_Data::stamped_psf(12, 16, 4);
$sealer = [$area * $f['sealer_psf'][0], $area * $f['sealer_psf'][1]];

// Slope, joints, extras.
$frac = function ($s) {
    $x = explode('/', $s);
    return $x[0] / $x[1];
};
$slopes = $sd['site']['drainage_slope_in_per_ft'];
$fall = [12 * $frac($slopes[0]), 12 * $frac($slopes[1])];
$jmax = $sd['joints']['max_ft'][4];
$prep = Slab_Data::site_prep_psf();
$demo = [$area * $sd['site']['demo_range'][0], $area * $sd['site']['demo_range'][1]];
$mesh = [$area * $sd['reinforcement']['mesh_range'][0], $area * $sd['reinforcement']['mesh_range'][1]];
$rebar = [$area * $sd['reinforcement']['rebar_range'][0], $area * $sd['reinforcement']['rebar_range'][1]];

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> a broom-finish concrete patio costs about <strong><?php echo esc_html(Slab_Data::money_range(Slab_Data::installed_psf(), 0, ' to ')); ?> per sq ft</strong> installed at 4 inches thick, and stamped concrete about <strong><?php echo esc_html(Slab_Data::money_range(Slab_Data::stamped_psf(), 0, ' to ')); ?> per sq ft</strong>. A common 12x16 patio (192 sq ft) runs about <?php echo esc_html($mr($p['installed'])); ?> with a broom finish, or <?php echo esc_html($mr($finishes['stamped'][1])); ?> stamped.</p>

<p>The finish is the biggest choice you will make on a patio, and it can nearly double the price. Size comes second, then the things people forget: getting water to run away from the house, where the joints go, and whether a truck can reach the backyard. This guide covers each one with real numbers.</p>

<h2>Patio Cost by Size</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Size</th><th>Good for</th><th>Concrete (4 in, +<?php echo (int) $sd['concrete']['waste_pct']; ?>%)</th><th>Broom finish</th><th>Stamped</th></tr></thead>
<tbody>
<?php foreach ($rows as $r) : ?>
<tr><td><strong><?php echo (int) $r['l']; ?> × <?php echo (int) $r['w']; ?> ft</strong> (<?php echo esc_html(number_format($r['l'] * $r['w'])); ?> sq ft)</td><td><?php echo esc_html($r['use']); ?></td><td><?php echo esc_html(number_format($r['c']['est']['order_yd'], 2)); ?> yd³</td><td><?php echo esc_html($mr($r['c']['installed'])); ?></td><td><?php echo esc_html($mr($r['stamped'])); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption"><?php echo esc_html(Slab_Data::cost_basis_note()); ?></div>
</div>
<p>Four inches is the standard thickness for a patio; the IRC minimum for a slab on ground is 3.5 inches. Go to 5 inches only if a heavy hot tub, a masonry fireplace or a vehicle will sit on part of it. For a table and chairs, plan on about 12x12 for four people and 12x16 for six with room to walk around. Try your own size in the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a> or on the <a href="<?php echo esc_url(home_url('/patio/')); ?>">patio cost page</a>.</p>

<h2>Finishes Compared: Broom, Trowel, Exposed and Stamped</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Finish (12x16 patio)</th><th>Finish rate</th><th>Installed</th><th>Notes</th></tr></thead>
<tbody>
<?php foreach ($finishes as $key => $fin) : ?>
<tr><td><strong><?php echo esc_html($fin[0]); ?></strong></td><td><?php echo esc_html(Slab_Data::money_range($f[$key]['range'], 2, ' to ')); ?>/sq ft</td><td><?php echo esc_html($mr($fin[1])); ?></td><td><?php echo esc_html($fin[2]); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<p><strong>Broom finish</strong> is what most patios get: the finisher drags a stiff broom across the fresh surface to leave fine ridges. It is cheap, grippy and easy to repair. <strong>Exposed aggregate</strong> washes away the top layer of cement paste to show the stones, which hides dirt and wear well. <strong>Stamped concrete</strong> presses texture mats into the surface, usually with a color hardener and release agent, to look like slate, flagstone, brick or wood. It takes a bigger crew and has to be done within a short window while the concrete is workable, which is why the price jumps.</p>

<div class="callout callout-tip">
<div class="callout-title">Get the stamped look for less</div>
<p>A broom-finish center with a stamped or colored border 12 to 18 inches wide looks custom and costs far less than stamping the whole slab. Integral color in a broom-finish patio is another middle option.</p>
</div>

<h2>Sealing: Required for Stamped, Optional for Broom</h2>
<p>A sealer slows water and stains from soaking in. Installed sealer runs about <?php echo esc_html(Slab_Data::money_range($f['sealer_psf'], 2, ' to ')); ?> per sq ft, or <?php echo esc_html($mr($sealer)); ?> for a 12x16 patio. Stamped and colored concrete needs sealing every few years to keep the color and protect the texture. A plain broom-finish patio often does fine without one, though a penetrating sealer helps in freeze-thaw climates. Wait until the concrete has cured (the product label says how long). Our <a href="<?php echo esc_url(home_url('/concrete-sealers-do-you-need-one/')); ?>">concrete sealer guide</a> compares the types.</p>

<h2>Slope the Patio Away From the House</h2>
<div class="formula-box">
<div class="formula-label">12 ft patio, measured out from the house</div>
<div class="formula-math">12 ft × <?php echo esc_html($slopes[0]); ?> in/ft = <?php echo esc_html(Slab_Data::num($fall[0])); ?> in &nbsp;|&nbsp; 12 ft × <?php echo esc_html($slopes[1]); ?> in/ft = <?php echo esc_html(Slab_Data::num($fall[1])); ?> in</div>
<div class="formula-explanation">Set the forms so the outer edge is lower than the house edge by this much. <?php echo esc_html(ucfirst($sd['site']['drainage_slope_preferred'])); ?> inch per foot is the preferred slope; it drains well and you will barely notice it under a table.</div>
</div>
<p>A patio that slopes toward the house sends rain against the foundation. Also keep the patio surface below any door threshold and below the siding, and leave an isolation joint (a strip of foam or fiber board) where the slab meets the house so the two can move separately. If the yard itself slopes toward the house, plan a swale or drain before you pour.</p>

<h2>Control Joints</h2>
<p>For a 4-inch patio, keep joints no more than about <?php echo (int) $jmax; ?> ft apart (<?php echo esc_html(Slab_Data::r('joints.ratio')); ?> times the thickness, in feet) and keep panels close to square, no more than <?php echo esc_html(Slab_Data::num($sd['joints']['max_aspect'])); ?> times longer than wide. On a 12x16 patio, one joint across the middle of the long side makes two 12x8 panels. Cut or tool the joints a quarter of the slab deep. Stamped patterns can hide joints in a grout line if the crew plans for it.</p>

<h2>Add-Ons That Change the Price</h2>
<ul>
<li><strong>Backyard access:</strong> if the truck cannot get within chute reach (about <?php echo esc_html(Slab_Data::r('site.chute_reach_ft')); ?> ft), a line pump costs about <?php echo esc_html(Slab_Data::mr('pump.line_job', 0, ' to ')); ?> for the job. On a small patio that is a big share of the bill. Wheelbarrows through a gate are the low-cost alternative.</li>
<li><strong>Removing an old patio:</strong> about <?php echo esc_html(Slab_Data::mr('site.demo_range', 0, ' to ')); ?> per sq ft, or <?php echo esc_html($mr($demo)); ?> for 192 sq ft. Use the <a href="<?php echo esc_url(home_url('/concrete-removal-cost-calculator/')); ?>">removal cost calculator</a>.</li>
<li><strong>Digging and base:</strong> excavation and a 4-inch compacted gravel base add about <?php echo esc_html(Slab_Data::money_range($prep, 2, ' to ')); ?> per sq ft. Size the stone with the <a href="<?php echo esc_url(home_url('/gravel-base-calculator/')); ?>">gravel base calculator</a>.</li>
<li><strong>Reinforcement:</strong> welded wire mesh runs about <?php echo esc_html($mr($mesh)); ?> on a 12x16 patio and #4 rebar about <?php echo esc_html($mr($rebar)); ?>. Mesh is usually enough for foot traffic; rebar makes sense under a hot tub or on soft soil.</li>
<li><strong>Steps, seat walls and covered roofs:</strong> these are priced separately. A roof or pergola needs proper post footings, which should be sized for your soil and roof load.</li>
</ul>

<h2>DIY or Hire a Pro?</h2>
<p>A 12x16 patio needs <?php echo esc_html(number_format($e['order_yd'], 2)); ?> cubic yards of concrete. That is <?php echo (int) $e['bags80']; ?> bags of 80-lb mix (about <?php echo esc_html($m($e['bagged'])); ?>), or about <?php echo esc_html($m($e['ready_mix'])); ?> of ready-mix with delivery. At this size a truck usually wins, and DIY materials total about <?php echo esc_html($mr($p['diy'])); ?>. A broom finish is a realistic first project if you have two or three helpers and a cool morning. Stamped concrete is not: the timing is unforgiving and mistakes are permanent, so hire a crew with a portfolio. Our <a href="<?php echo esc_url(home_url('/diy-concrete-slab-what-you-can-and-shouldnt-do/')); ?>">DIY vs. contractor guide</a> helps you decide, and the <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">quote checker</a> tells you whether a bid is fair.</p>

<h2>Sources and Method</h2>
<p>Prices use the same line items as our calculator, with finish upgrades priced at each finish's default rate above broom finish (see our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>). They vary by region, season and access. Thickness and base guidance follows IRC R506 (concrete floors on ground); joints and finishing follow ACI 302.1R; curing follows ACI 308R.</p>
<?php
$html = ob_get_clean();

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'How much does a concrete patio cost per square foot?',
            'a' => 'About ' . Slab_Data::money_range(Slab_Data::installed_psf(), 0, ' to ') . ' per sq ft installed for a 4-inch broom-finish patio, and about ' . Slab_Data::money_range(Slab_Data::stamped_psf(), 0, ' to ') . ' per sq ft for stamped concrete. Small patios cost more per foot because fixed costs are spread over less area.',
        ],
        [
            'q' => 'How much does a 12x16 concrete patio cost?',
            'a' => 'About ' . $mr($p['installed']) . ' installed with a broom finish at 4 inches thick, or ' . $mr($finishes['stamped'][1]) . ' stamped. DIY materials run about ' . $mr($p['diy']) . '.',
        ],
        [
            'q' => 'Is stamped concrete worth it for a patio?',
            'a' => 'It looks like stone at a lower price than real stone, but it costs roughly ' . Slab_Data::money_range($stamped_psf, 0, ' to ') . ' per sq ft on a 12x16 patio and needs resealing every few years. A broom-finish patio with a stamped border is a cheaper middle option.',
        ],
        [
            'q' => 'How much slope does a patio need?',
            'a' => 'Slope it ' . $slopes[0] . ' to ' . $slopes[1] . ' inch per foot away from the house, with ' . $sd['site']['drainage_slope_preferred'] . ' inch per foot preferred. On a patio that extends 12 ft from the house that is ' . Slab_Data::num($fall[0]) . ' to ' . Slab_Data::num($fall[1]) . ' inches of fall.',
        ],
        [
            'q' => 'Should I seal a new concrete patio?',
            'a' => 'Stamped or colored patios should be sealed and resealed every few years. Broom-finish patios benefit most in freeze-thaw climates. Installed sealer costs about ' . Slab_Data::money_range($f['sealer_psf'], 2, ' to ') . ' per sq ft.',
        ],
    ],
];
