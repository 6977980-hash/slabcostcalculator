<?php
/**
 * Article: Concrete Slab Cost Per Square Foot (post slug concrete-cost-per-square-foot-pricing-matrix).
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
$m2 = function ($pair) {
    return Slab_Data::money_range($pair, 2, ' to ');
};

$op = $sd['overhead_profit'];
$fin = $sd['finish'];
$finish_names = ['broom' => 'Broom', 'trowel' => 'Smooth trowel', 'exposed' => 'Exposed aggregate', 'stamped' => 'Stamped'];

// Installed $/sq ft with a finish other than broom, built the same way as Slab_Data::stamped_psf().
$fpsf_raw = function ($l, $w, $t, $finish) use ($fin, $op) {
    $add = $fin[$finish]['default'] - $fin['broom']['default'];
    $c = Slab_Data::size_costs($l, $w, $t);
    return [$c['installed_psf'][0] + $add, $c['installed_psf'][1] + $add * (1 + $op)];
};
$fpsf = function ($l, $w, $t, $finish) use ($fpsf_raw) {
    return Slab_Data::round_out($fpsf_raw($l, $w, $t, $finish), 1);
};

$psf = Slab_Data::installed_psf();
$diy = Slab_Data::diy_psf();
$c20 = Slab_Data::size_costs(20, 20, 4);
$waste = $sd['concrete']['waste_pct'];
$fee = $sd['delivery']['fee_default'];
$rm = $sd['concrete']['ready_mix_default'];
$forms = $sd['site']['forms_default'];
$prep = Slab_Data::site_prep_psf();

$sizes = [
    [10, 10, '10 × 10 ft', 'Shed pad, hot tub or AC pad'],
    [12, 12, '12 × 12 ft', 'Small patio, larger shed'],
    [10, 20, '10 × 20 ft', 'Walkway run, narrow patio'],
    [16, 24, '16 × 24 ft', 'Family patio'],
    [20, 20, '20 × 20 ft', 'Large patio, 2-car pad'],
    [24, 24, '24 × 24 ft', 'Oversized 2-car garage'],
    [30, 40, '30 × 40 ft', 'Shop or barn floor'],
];

// Example: 16x24 exposed-aggregate patio, 4 in.
$ex_area = 16 * 24;
$ex = $fpsf_raw(16, 24, 4, 'exposed');
$ex_total = Slab_Data::round_pair([$ex[0] * $ex_area, $ex[1] * $ex_area], 10);
$ex_broom = Slab_Data::size_costs(16, 24, 4);
$ex_prep = Slab_Data::round_pair([$prep[0] * $ex_area, $prep[1] * $ex_area], 10);

// Upgrading 20x20 x 4 in from 3,000 to 4,000 PSI, per sq ft.
$psi_up = Slab_Data::psi_extra(4000, $c20['est']['order_yd']);
$psi_up_psf = [$psi_up[0] / 400, $psi_up[1] / 400];
$pump_psf = [$sd['pump']['typical'][0] / 400, $sd['pump']['typical'][1] / 400];

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> a standard 4-inch concrete slab with a broom finish costs about <strong><?php echo esc_html($mr($psf)); ?> per square foot</strong> installed in 2026, or <strong><?php echo esc_html($m2($diy)); ?> per square foot</strong> in materials if you pour it yourself. Thicker slabs, decorative finishes and small footprints push the rate up: a stamped 4-inch slab runs about <?php echo esc_html($mr(Slab_Data::stamped_psf())); ?> per square foot, and a 10x10 pad costs more per foot than a 20x20 slab with the same specs.</p>

<p>Contractors price flatwork per square foot because most of the work scales with area: finishing, base prep, vapor barrier and reinforcement. But the concrete itself scales with volume, and some costs (delivery, forms, setup) are fixed or scale with the edge. That is why one "price per square foot" never fits every job. The tables below break the rate down by thickness, finish and slab size, all computed with the same line items our <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a> uses.</p>

<h2>The 2026 Pricing Matrix: Thickness × Finish</h2>
<p>Installed price per square foot for a 20x20 slab (400 sq ft), the reference size for most quotes. The low end is a plain slab with no rebar or vapor barrier. The high end is the full scope (vapor barrier, #4 rebar grid) plus <?php echo (int) round($op * 100); ?>% contractor overhead and profit.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Finish</th><th>4 in</th><th>5 in</th><th>6 in</th></tr></thead>
<tbody>
<?php foreach ($finish_names as $key => $label) : ?>
<tr><td><strong><?php echo esc_html($label); ?></strong></td><?php foreach ([4, 5, 6] as $t) : ?><td><?php echo esc_html(Slab_Data::money_range($fpsf(20, 20, $t, $key), 0, ' – ')); ?></td><?php endforeach; ?></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption"><?php echo esc_html(Slab_Data::cost_basis_note()); ?> Decorative finishes swap the broom-finish rate for the finish rate shown in the next table.</div>
</div>

<p>Two things stand out. First, thickness matters less than people expect: each extra inch adds about a quarter more concrete, but forms, finishing and labor barely change. Second, the finish matters more than anything else on this table. Going from broom to stamped can nearly double the price of the same slab.</p>

<h2>What Each Finish Adds</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Finish</th><th>Finish cost per sq ft</th><th>Calculator default</th><th>Where it fits</th></tr></thead>
<tbody>
<tr><td><strong>Broom</strong></td><td><?php echo esc_html($m2($fin['broom']['range'])); ?></td><td><?php echo esc_html(Slab_Data::money($fin['broom']['default'], 2)); ?></td><td>Driveways, walks, patios. Slip resistant and cheapest.</td></tr>
<tr><td><strong>Smooth trowel</strong></td><td><?php echo esc_html($m2($fin['trowel']['range'])); ?></td><td><?php echo esc_html(Slab_Data::money($fin['trowel']['default'], 2)); ?></td><td>Garage and shop floors, basements. Slick when wet outdoors.</td></tr>
<tr><td><strong>Exposed aggregate</strong></td><td><?php echo esc_html($m2($fin['exposed']['range'])); ?></td><td><?php echo esc_html(Slab_Data::money($fin['exposed']['default'], 2)); ?></td><td>Pool decks and patios. Grippy, hides wear.</td></tr>
<tr><td><strong>Stamped</strong></td><td><?php echo esc_html($m2($fin['stamped']['range'])); ?></td><td><?php echo esc_html(Slab_Data::money($fin['stamped']['default'], 2)); ?></td><td>Show patios and entries. Needs resealing.</td></tr>
</tbody>
</table>
</div>
<p>Stamped work also takes more crew time. Our calculator uses <?php echo esc_html(Slab_Data::money($sd['labor']['sqft_stamped'], 2)); ?> per sq ft for stamped placing and finishing labor instead of <?php echo esc_html(Slab_Data::money($sd['labor']['sqft_default'], 2)); ?>, so a complex pattern with several colors can land above the matrix range.</p>

<h2>Price Per Square Foot by Slab Size</h2>
<p>Same spec (4 inches, broom finish), different footprints. Watch the per-foot rate fall as the slab gets bigger.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Size</th><th>Typical use</th><th>Installed per sq ft</th><th>Installed total</th><th>DIY materials per sq ft</th></tr></thead>
<tbody>
<?php foreach ($sizes as $s) : $c = Slab_Data::size_costs($s[0], $s[1], 4); ?>
<tr><td><strong><?php echo esc_html($s[2]); ?></strong></td><td><?php echo esc_html($s[3]); ?></td><td><?php echo esc_html($m2($c['installed_psf'])); ?></td><td><?php echo esc_html($mr($c['installed'])); ?></td><td><?php echo esc_html($m2($c['diy_psf'])); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">4-inch slab, broom finish, <?php echo (int) $waste; ?>% concrete waste included. Site excavation and gravel base not included.</div>
</div>

<h2>Why Small Slabs Cost More Per Square Foot</h2>
<p>Three costs do not shrink in step with area:</p>
<ul>
<li><strong>Delivery:</strong> the <?php echo esc_html($m($fee)); ?> delivery fee in our estimates is the same for 1 yard as for 10. On a 10x10 that is <?php echo esc_html(Slab_Data::money($fee / 100, 2)); ?> per sq ft. On a 30x40 it is <?php echo esc_html(Slab_Data::money($fee / 1200, 2)); ?>. Below the plant's minimum load (commonly <?php echo esc_html(Slab_Data::r('delivery.common_min_yd')); ?> yd³) a short-load fee of <?php echo esc_html(Slab_Data::mr('delivery.short_load_flat', 0, ' to ')); ?> often comes on top.</li>
<li><strong>Forms:</strong> forms follow the perimeter. A 10x10 has <?php echo (int) (2 * (10 + 10)); ?> ft of edge for <?php echo (int) (10 * 10); ?> sq ft, so forms at <?php echo esc_html(Slab_Data::money($forms, 2)); ?>/ft cost <?php echo esc_html(Slab_Data::money(2 * (10 + 10) * $forms / 100, 2)); ?> per sq ft. A 30x40 has <?php echo (int) (2 * (30 + 40)); ?> ft of edge for <?php echo esc_html(number_format(30 * 40)); ?> sq ft: <?php echo esc_html(Slab_Data::money(2 * (30 + 40) * $forms / 1200, 2)); ?> per sq ft.</li>
<li><strong>Setup and minimums:</strong> a crew spends the same time driving out, unloading tools and cleaning up whether the slab is 100 or 1,000 sq ft. Many contractors set a minimum job charge, and small pads are often quoted as a flat price rather than per foot.</li>
</ul>
<div class="callout callout-tip">
<div class="callout-title">Combine small pours</div>
<p>If you need a shed pad and a walkway, pour them the same day from one truck. You pay one delivery fee, you are more likely to clear the plant's minimum load, and the crew sets up once.</p>
</div>

<h2>Add-Ons That Change the Rate</h2>
<p>The matrix covers the slab itself. These items are priced separately and are the most common reason two quotes for "the same slab" differ.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Add-on</th><th>Typical cost per sq ft</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>Excavation + <?php echo (int) $sd['site']['gravel_depth_in']; ?> in compacted gravel</td><td><?php echo esc_html($m2($prep)); ?></td><td>Skip only if the grade is already firm and level</td></tr>
<tr><td>Remove an old slab</td><td><?php echo esc_html($m2($sd['site']['demo_range'])); ?></td><td>4-inch unreinforced slab, break-out and haul</td></tr>
<tr><td>Vapor barrier</td><td><?php echo esc_html($m2($sd['site']['vapor_range'])); ?></td><td>Needed under heated or enclosed space</td></tr>
<tr><td>#4 rebar grid</td><td><?php echo esc_html($m2($sd['reinforcement']['rebar_range'])); ?></td><td><?php echo (int) $sd['reinforcement']['rebar_base_spacing_in']; ?> in centers with chairs</td></tr>
<tr><td>Welded wire mesh</td><td><?php echo esc_html($m2($sd['reinforcement']['mesh_range'])); ?></td><td>Lighter option for patios and walks</td></tr>
<tr><td>4,000 PSI mix instead of 3,000</td><td><?php echo esc_html($m2($psi_up_psf)); ?></td><td>On a 20x20, 4-inch slab</td></tr>
<tr><td>Concrete pump</td><td><?php echo esc_html($m2($pump_psf)); ?></td><td><?php echo esc_html(Slab_Data::mr('pump.typical', 0, ' to ')); ?> per pour spread over 400 sq ft</td></tr>
</tbody>
</table>
</div>
<p>Notice how small the strength upgrade is per square foot. If your area has freezing winters or uses road salt, the step up in mix grade is cheap insurance. Our <a href="<?php echo esc_url(home_url('/concrete-cost-per-cubic-yard-pricing-explained/')); ?>">cost per cubic yard guide</a> lists the price of each PSI grade.</p>

<h2>Per-Square-Foot Rates by Project Type</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Project</th><th>Reference slab</th><th>Installed per sq ft</th></tr></thead>
<tbody>
<tr><td><a href="<?php echo esc_url(home_url('/concrete-patio-slab-cost-guide/')); ?>">Patio</a>, broom finish</td><td>20 × 20 ft, 4 in</td><td><?php echo esc_html($mr($psf)); ?></td></tr>
<tr><td>Patio, stamped</td><td>20 × 20 ft, 4 in</td><td><?php echo esc_html($mr(Slab_Data::stamped_psf())); ?></td></tr>
<tr><td><a href="<?php echo esc_url(home_url('/how-much-does-a-concrete-driveway-cost/')); ?>">Driveway</a></td><td>20 × 40 ft, 5 in</td><td><?php echo esc_html($mr(Slab_Data::driveway_psf())); ?></td></tr>
<tr><td><a href="<?php echo esc_url(home_url('/how-much-does-a-garage-slab-cost/')); ?>">Garage slab</a></td><td>24 × 24 ft, 5 in</td><td><?php echo esc_html($mr(Slab_Data::garage_psf())); ?></td></tr>
<tr><td>Monolithic slab foundation</td><td>30 × 40 ft, 4 in floor, thickened edge</td><td><?php echo esc_html($mr(Slab_Data::foundation_psf())); ?></td></tr>
</tbody>
</table>
</div>

<h2>Worked Example: 16x24 Exposed Aggregate Patio</h2>
<div class="formula-box">
<div class="formula-label">16 ft × 24 ft × 4 in, exposed aggregate</div>
<div class="formula-math"><?php echo (int) $ex_area; ?> sq ft × <?php echo esc_html($m2($ex)); ?> = <?php echo esc_html($mr($ex_total)); ?></div>
<div class="formula-explanation">The same patio with a broom finish is <?php echo esc_html($mr($ex_broom['installed'])); ?>. If the yard needs digging out and a gravel base, add about <?php echo esc_html($mr($ex_prep)); ?> for site prep.</div>
</div>
<p>To run your own numbers, enter the size, thickness and finish in the <a href="<?php echo esc_url(home_url('/concrete-cost-per-square-foot-calculator/')); ?>">cost per square foot calculator</a>. Any change in thickness, finish or scope flows through to the per-foot rate.</p>

<h2>DIY vs. Contractor Per Square Foot</h2>
<p>DIY materials run about <?php echo esc_html($m2($diy)); ?> per square foot for a 20x20 slab, against <?php echo esc_html($mr($psf)); ?> installed. The gap is mostly labor (<?php echo esc_html($m2($sd['labor']['sqft_range'])); ?> per sq ft for placing and finishing) plus overhead and profit. The savings are real on a small patio or shed pad. On a large slab or a decorative finish, a crew's speed and finishing skill usually earn their price, because a bad finish cannot be fixed without tearing it out. If you have a quote in hand, the <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">concrete quote checker</a> tells you whether its per-foot rate is fair for your size and scope.</p>

<div class="callout callout-warning">
<div class="callout-title">Compare quotes on the same scope</div>
<p>A low per-foot price that leaves out the gravel base, rebar or vapor barrier is not cheaper. Ask each contractor to list thickness, PSI, reinforcement, base depth and finish in writing so you can compare line by line.</p>
</div>

<h2>Sources and Method</h2>
<p>Every rate on this page is computed from the same line items and default prices as the calculator: ready-mix by the cubic yard with <?php echo (int) $waste; ?>% waste, a delivery fee, forms by the linear foot, and finish, labor, vapor barrier and rebar by the square foot. Prices are this site's 2026 working benchmarks (see our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>) and vary by region, season and contractor workload. Thickness and base guidance follows the International Residential Code (section R506) and ACI 302.1R, the American Concrete Institute's guide to floor and slab construction.</p>
<?php
$html = ob_get_clean();

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'How much does a concrete slab cost per square foot in 2026?',
            'a' => 'About ' . $mr($psf) . ' per square foot installed for a 4-inch broom-finish slab, or ' . $m2($diy) . ' per square foot for materials if you do it yourself. Site excavation and a gravel base add about ' . $m2($prep) . ' per square foot.',
        ],
        [
            'q' => 'How much more does a 6-inch slab cost per square foot than a 4-inch slab?',
            'a' => 'Less than you might think. On a 20x20 slab, 4 inches runs about ' . $mr($fpsf(20, 20, 4, 'broom')) . ' per square foot installed and 6 inches about ' . $mr($fpsf(20, 20, 6, 'broom')) . '. The extra concrete costs more, but forms, finishing and labor barely change.',
        ],
        [
            'q' => 'How much is stamped concrete per square foot?',
            'a' => 'About ' . $mr(Slab_Data::stamped_psf()) . ' per square foot installed for a 4-inch stamped slab, compared with ' . $mr($psf) . ' for a broom finish. Complex patterns and multiple colors can cost more.',
        ],
        [
            'q' => 'Why is a small concrete slab more expensive per square foot?',
            'a' => 'Delivery fees, forms and crew setup do not shrink with the slab. A 10x10 slab works out to about ' . $m2(Slab_Data::size_costs(10, 10, 4)['installed_psf']) . ' per square foot installed, while a 30x40 slab with the same spec is about ' . $m2(Slab_Data::size_costs(30, 40, 4)['installed_psf']) . '.',
        ],
        [
            'q' => 'Does the price per square foot include the gravel base?',
            'a' => 'Usually not. Most per-square-foot slab prices cover forms, concrete, finishing and labor. Excavation and a compacted gravel base typically add about ' . $m2($prep) . ' per square foot, so ask every contractor whether it is included.',
        ],
    ],
];
