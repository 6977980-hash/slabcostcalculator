<?php
/**
 * Article: How Thick Should a Concrete Slab Be? (post slug how-thick-should-a-concrete-slab-be).
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

$rm = $sd['concrete']['ready_mix_default'];
$waste = $sd['concrete']['waste_pct'];
$code = $sd['code'];
$j = $sd['joints'];
$mono = $sd['monolithic'];

// Cost of one extra inch of concrete per sq ft (ready-mix + waste).
$inch_psf = 1 / 12 / 27 * (1 + $waste / 100) * $rm;

// 20x20 slab at each thickness.
$thick = [];
foreach ([4, 5, 6, 8] as $t) {
    $thick[$t] = Slab_Data::size_costs(20, 20, $t);
}
$step_5 = $thick[5]['est']['direct'] - $thick[4]['est']['direct'];
$step_6 = $thick[6]['est']['direct'] - $thick[4]['est']['direct'];
$step_8 = $thick[8]['est']['direct'] - $thick[4]['est']['direct'];
$pct_5 = $step_5 / $thick[4]['est']['direct'] * 100;
$vol_pct_5 = ($thick[5]['est']['net_yd'] / $thick[4]['est']['net_yd'] - 1) * 100;

// Thickened edge on a 24x24 slab, 4 in floor.
$edge_lo = Slab_Data::edge_yd(24, 24, 4, $mono['edge_depth_in'][0]);
$edge_hi = Slab_Data::edge_yd(24, 24, 4, $mono['edge_depth_in'][1]);

$psi = function ($p) {
    return number_format($p) . ' PSI';
};

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> pour <strong>4 inches</strong> for patios, walkways, shed floors and interior floors, <strong>5 inches</strong> for driveways and garage floors that carry cars and pickups, and <strong>6 inches</strong> for RV pads, loaded trucks or a heavy workshop. Anything at <strong>8 inches</strong> or more is commercial or heavy-equipment work that an engineer should design. The building code minimum for a floor slab is 3.5 inches (IRC R506.1). Each extra inch adds about <?php echo esc_html(Slab_Data::money($inch_psf, 2)); ?> per sq ft in concrete, which is small next to the cost of a slab that cracks under load.</p>

<p>Thickness is the first number on every slab plan, and it sets how much concrete you order, how tall your forms are and how deep you dig. But it is only half the story. A thick slab on soft, uneven ground will crack, while a 4-inch slab on a firm, well-compacted base can last decades. This guide gives the standard thickness for each use, what each inch really costs, and the other choices that matter just as much.</p>

<h2>Slab Thickness by Use</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Use</th><th>Typical thickness</th><th>Typical concrete strength</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>Interior house floor (slab on grade)</td><td>4 in</td><td><?php echo esc_html($psi($code['psi_protected'])); ?> and up</td><td>Code minimum is 3.5 in; 4 in is the common practice.</td></tr>
<tr><td>Patio, walkway, porch landing</td><td>4 in</td><td><?php echo esc_html($psi($code['psi_exposed'])); ?>, <?php echo esc_html($psi($code['psi_severe'])); ?> in freeze-thaw areas</td><td>Foot traffic, furniture, grills.</td></tr>
<tr><td>Shed floor, AC or generator pad</td><td>4 in</td><td><?php echo esc_html($psi($code['psi_exposed'])); ?></td><td>Go to 5 in if a riding mower or ATV parks inside.</td></tr>
<tr><td>Hot tub pad</td><td>4 to 6 in</td><td><?php echo esc_html($psi($code['psi_exposed'])); ?> and up</td><td>Check the spa maker's spec; large filled spas are heavy.</td></tr>
<tr><td>Garage floor</td><td>4 to 5 in</td><td><?php echo esc_html($psi($code['psi_severe'])); ?> air-entrained (code minimum where exposed to freeze-thaw)</td><td>5 in is the better choice for pickups and SUVs.</td></tr>
<tr><td>Driveway</td><td>5 in</td><td><?php echo esc_html($psi($code['psi_severe'])); ?> to <?php echo esc_html($psi($code['psi_deicing_recommended'])); ?></td><td><?php echo esc_html($psi($code['psi_deicing_recommended'])); ?> where de-icing salt is used.</td></tr>
<tr><td>RV pad, boat or trailer parking, delivery trucks</td><td>6 in</td><td><?php echo esc_html($psi($code['psi_deicing_recommended'])); ?></td><td>Thicken where the heaviest wheels sit.</td></tr>
<tr><td>Shop floor with a car lift or heavy equipment</td><td>6 in, more at lift posts</td><td><?php echo esc_html($psi(4500)); ?> and up</td><td>Lift makers list a minimum depth and strength under each post.</td></tr>
<tr><td>Commercial truck bays, forklift traffic</td><td>8 in and up</td><td><?php echo esc_html($psi(4500)); ?> to <?php echo esc_html($psi(5000)); ?></td><td>Engineered design, not a rule of thumb.</td></tr>
</tbody>
</table>
<div class="table-caption">Typical residential practice. Your local code, a structural plan or a product maker's installation guide can require more.</div>
</div>

<div class="callout callout-info">
<div class="callout-title">What the code and the standards actually say</div>
<p>The International Residential Code (section R506.1) sets a minimum of 3.5 inches for concrete floors on ground, and Table R402.2 sets minimum strength by exposure: <?php echo esc_html($psi($code['psi_protected'])); ?> where the concrete is protected from weather, <?php echo esc_html($psi($code['psi_exposed'])); ?> for exposed concrete in mild climates, and <?php echo esc_html($psi($code['psi_severe'])); ?> air-entrained concrete for garage floors, porches and steps in severe weather areas. ACI 302.1R is a guide to good floor and slab construction (base, placing, finishing, joints, curing). It does not hand homeowners a mandatory thickness for each use. The 4, 5 and 6-inch figures above are long-standing trade practice that sits on top of the code minimum.</p>
</div>

<h2>What Each Extra Inch Costs</h2>
<p>An inch of concrete over one square foot is 1/12 of a cubic foot. With <?php echo (int) $waste; ?>% waste and ready-mix at <?php echo esc_html(Slab_Data::money($rm)); ?> per yard, that is about <?php echo esc_html(Slab_Data::money($inch_psf, 2)); ?> per sq ft per inch. Forms get a little taller and labor barely moves, so the total rises much more slowly than the volume. Here is a 20x20 slab (400 sq ft) priced with our calculator's line items:</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Thickness</th><th>Concrete to order (+<?php echo (int) $waste; ?>%)</th><th>Direct cost (full scope)</th><th>Extra vs. 4 in</th><th>Installed range</th></tr></thead>
<tbody>
<?php foreach ($thick as $t => $c) : ?>
<tr><td><strong><?php echo (int) $t; ?> in</strong></td><td><?php echo esc_html(number_format($c['est']['order_yd'], 2)); ?> yd³</td><td><?php echo esc_html($m($c['est']['direct'])); ?></td><td><?php echo $t === 4 ? '-' : esc_html('+' . $m($c['est']['direct'] - $thick[4]['est']['direct'])); ?></td><td><?php echo esc_html($mr($c['installed'])); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption"><?php echo esc_html(Slab_Data::cost_basis_note()); ?></div>
</div>
<p>Going from 4 to 5 inches adds <?php echo (int) round($vol_pct_5); ?>% more concrete but only about <?php echo esc_html(number_format($pct_5, 0)); ?>% to the direct cost of the job (<?php echo esc_html($m($step_5)); ?> on 400 sq ft). Going to 6 inches adds about <?php echo esc_html($m($step_6)); ?>, and 8 inches about <?php echo esc_html($m($step_8)); ?>. In practice an 8-inch slab costs more than this, because it usually comes with heavier steel, a thicker base and an engineer's fee. To price your own size and thickness, use the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a> or the <a href="<?php echo esc_url(home_url('/concrete-volume-calculator/')); ?>">concrete volume calculator</a>.</p>

<div class="formula-box">
<div class="formula-label">Cost of one extra inch, per square foot</div>
<div class="formula-math">1 ft² × (1 ÷ 12) ft ÷ 27 × <?php echo esc_html(number_format(1 + $waste / 100, 2)); ?> × <?php echo esc_html(Slab_Data::money($rm)); ?> = <?php echo esc_html(Slab_Data::money($inch_psf, 2)); ?></div>
<div class="formula-explanation">Multiply by your slab's area. For a 20x20 slab: 400 × <?php echo esc_html(Slab_Data::money($inch_psf, 2)); ?> = <?php echo esc_html($m(400 * $inch_psf)); ?> of extra concrete per inch, before any change in forms or labor.</div>
</div>

<h2>Why the Ground Under the Slab Matters More Than Thickness</h2>
<p>A slab on grade does not span between supports like a floor joist. It rests on the ground, and the ground carries the load. When the subgrade is firm and even, a car tire's weight spreads out through the concrete into a wide patch of soil, and a 4 or 5-inch slab handles it easily. When there is a soft spot, a void, or loose fill under one area, the slab has to bridge that gap on its own, and that is where most structural cracks start.</p>
<ul>
<li><strong>Remove topsoil and organic material.</strong> Sod, roots and dark topsoil compress and rot over time.</li>
<li><strong>Compact the native soil</strong> with a plate compactor before adding any base, and fill soft spots with compacted gravel, not loose dirt.</li>
<li><strong>Use a compacted gravel base</strong> where the soil drains poorly. The IRC calls for a 4-inch base course in most cases (R506.2.2). See <a href="<?php echo esc_url(home_url('/how-much-gravel-do-i-need-under-a-concrete-slab/')); ?>">how much gravel you need under a slab</a>.</li>
<li><strong>Keep the thickness even.</strong> A slab that is 5 inches at the forms but 3 inches over a high spot in the middle is a 3-inch slab where it counts. Check depth with a string line before the pour.</li>
</ul>
<p>Put simply: an extra inch of concrete cannot fix a bad base, but a good base lets a standard thickness do its job. Our <a href="<?php echo esc_url(home_url('/yard-prep-checklist-for-a-slab-pour/')); ?>">yard prep checklist</a> covers the steps in order.</p>

<h2>Thickened Edges and Thickened Spots</h2>
<p>Slab edges are the weak point, because there is soil support on only one side. Two common fixes add depth only where it is needed:</p>
<ul>
<li><strong>Thickened edge (turndown):</strong> the outer <?php echo (int) $mono['edge_width_in']; ?> inches or so of the slab is dug deeper, typically to <?php echo esc_html(Slab_Data::r('monolithic.edge_depth_in')); ?> inches, and poured at the same time as the floor. This is how a monolithic garage or house slab gets its built-in footing. On a 24x24 slab with a 4-inch floor, the deeper edge adds about <?php echo esc_html(number_format($edge_lo, 1)); ?> yd³ of concrete at <?php echo (int) $mono['edge_depth_in'][0]; ?> inches deep, or <?php echo esc_html(number_format($edge_hi, 1)); ?> yd³ at <?php echo (int) $mono['edge_depth_in'][1]; ?> inches (with waste). Where frost is a concern, local code sets the footing depth. See the <a href="<?php echo esc_url(home_url('/concrete-foundation-monolithic-slab-cost/')); ?>">monolithic slab guide</a>.</li>
<li><strong>Driveway apron and edges:</strong> many contractors thicken the edges of a driveway and the apron where it meets the street, because that is where trucks and tires roll on and off the slab.</li>
<li><strong>Point loads:</strong> a car lift post, a heavy safe or a column needs a local thickened pad sized to that load, not a thicker slab everywhere.</li>
</ul>

<h2>Thickness Changes Your Joint Spacing</h2>
<p>Control joints are spaced from the thickness. The usual rule is <?php echo esc_html(Slab_Data::r('joints.ratio')); ?> times the thickness in inches, read as feet, with a cap of about <?php echo (int) $j['cap_ft']; ?> ft for residential slabs. That gives:</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Thickness</th><th>Joint spacing (<?php echo esc_html(Slab_Data::r('joints.ratio')); ?> × thickness)</th><th>Practical maximum</th><th>Joint depth (<?php echo (int) round($j['depth_fraction'] * 100); ?>% of thickness)</th></tr></thead>
<tbody>
<?php foreach ($j['max_ft'] as $t => $max) : ?>
<tr><td><strong><?php echo (int) $t; ?> in</strong></td><td><?php echo esc_html(Slab_Data::num($t * $j['ratio'][0]) . ' to ' . Slab_Data::num(min($t * $j['ratio'][1], $j['cap_ft']))); ?> ft</td><td><?php echo (int) $max; ?> ft</td><td><?php echo esc_html(Slab_Data::num($t * $j['depth_fraction'], 2)); ?> in</td></tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<p>Keep each panel close to square, no longer than about <?php echo esc_html(Slab_Data::num($j['max_aspect'])); ?> times its width. A thicker slab lets you space joints a little farther apart, but it does not let you skip them. The <a href="<?php echo esc_url(home_url('/concrete-slab-cracks-causes-and-prevention/')); ?>">crack prevention guide</a> explains why.</p>

<h2>Match the Strength (PSI) to the Thickness and Exposure</h2>
<p>Thickness handles the load. Strength and air entrainment handle the weather. A 5-inch driveway poured with weak, non-air-entrained concrete can still scale and flake after a few winters of freeze-thaw and salt. Typical pairings:</p>
<ul>
<li><strong>4-inch patio in a mild climate:</strong> <?php echo esc_html($psi($code['psi_exposed'])); ?>, about <?php echo esc_html($mr(Slab_Data::psi_price(3000))); ?> per yard.</li>
<li><strong>4 to 5-inch garage floor or porch in a cold climate:</strong> <?php echo esc_html($psi($code['psi_severe'])); ?> air-entrained (<?php echo esc_html(Slab_Data::r('concrete.air_entrainment_pct')); ?>% air), about <?php echo esc_html($mr(Slab_Data::psi_price(3500))); ?> per yard.</li>
<li><strong>5-inch driveway with road salt, 6-inch RV pad:</strong> <?php echo esc_html($psi($code['psi_deicing_recommended'])); ?> air-entrained, about <?php echo esc_html($mr(Slab_Data::psi_price(4000))); ?> per yard.</li>
</ul>
<p>Moving up one strength step costs only <?php echo esc_html(Slab_Data::mr('concrete.psi_step_price', 0, ' to ')); ?> per yard, so on a 5-inch, 400 sq ft slab the jump from <?php echo esc_html($psi(3000)); ?> to <?php echo esc_html($psi(4000)); ?> adds roughly <?php echo esc_html($mr(Slab_Data::psi_extra(4000, $thick[5]['est']['order_yd']))); ?>. See the <a href="<?php echo esc_url(home_url('/concrete-cost-per-cubic-yard-pricing-explained/')); ?>">cost per cubic yard guide</a> for the full PSI price list.</p>

<h2>When to Go Thicker (and When Not To)</h2>
<ul>
<li><strong>Go thicker</strong> if heavy vehicles will use the slab, if the soil is clay or has been filled, if the slab will carry a lift or heavy machine, or if you are unsure what will park there in ten years. The extra inch is cheap insurance.</li>
<li><strong>Do not go thicker</strong> to make up for skipping the base, the joints or the cure. Those problems crack slabs of any thickness.</li>
<li><strong>Hire an engineer</strong> for anything at 8 inches or more, for slabs that carry a building's walls without separate footings, and for commercial use.</li>
</ul>

<h2>Sources and Method</h2>
<p>Code minimums are from the International Residential Code: R506.1 (3.5-inch minimum floor slab), R506.2.2 (base course) and Table R402.2 (minimum concrete strength by exposure). Construction guidance follows ACI 302.1R (guide to floor and slab construction) and the Portland Cement Association's guidance on concrete floors on ground. Costs come from the same line items and default rates as our calculator; see our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>. Prices vary by region and season.</p>
<?php
$html = ob_get_clean();

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'How thick should a concrete slab be?',
            'a' => '4 inches for patios, walkways, shed floors and interior floors; 5 inches for driveways and garage floors; 6 inches for RV pads and heavy trucks. Slabs 8 inches and thicker are for commercial or heavy-equipment use and should be engineered. The IRC minimum for a floor slab is 3.5 inches.',
        ],
        [
            'q' => 'Is 4 inches of concrete enough for a driveway?',
            'a' => 'It can carry ordinary cars on a firm, well-compacted base, but 5 inches is the standard recommendation for driveways because it handles pickups, SUVs and occasional delivery trucks with more margin. On a 20x20 area the extra inch adds about ' . $m($step_5) . ' to the direct cost.',
        ],
        [
            'q' => 'How much more does a 6-inch slab cost than a 4-inch slab?',
            'a' => 'Each extra inch adds about ' . Slab_Data::money($inch_psf, 2) . ' per sq ft in concrete. On a 20x20 slab, going from 4 to 6 inches adds about ' . $m($step_6) . ' to the direct cost, because the concrete goes from ' . number_format($thick[4]['est']['order_yd'], 2) . ' to ' . number_format($thick[6]['est']['order_yd'], 2) . ' cubic yards while labor stays about the same.',
        ],
        [
            'q' => 'Does ACI 302.1R require a specific slab thickness?',
            'a' => 'No. ACI 302.1R is a guide to good floor and slab construction practice, such as base preparation, placing, finishing, joints and curing. The enforceable residential minimum is the International Residential Code, which requires at least 3.5 inches for concrete floors on ground (R506.1).',
        ],
        [
            'q' => 'Does a thicker slab crack less?',
            'a' => 'Only cracks caused by overloading. Shrinkage cracks happen at any thickness and are controlled with joints spaced at about ' . Slab_Data::r('joints.ratio') . ' times the thickness in inches, in feet. A firm, even base matters more than an extra inch of concrete.',
        ],
    ],
];
