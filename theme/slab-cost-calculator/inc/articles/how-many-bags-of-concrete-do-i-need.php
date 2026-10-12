<?php
/**
 * Article: How Many Bags of Concrete Do I Need? (post slug how-many-bags-of-concrete-do-i-need).
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

$c = $sd['concrete'];
$y = $c['bag_yields'];
$waste = $c['waste_pct'];
$wf = 1 + $waste / 100;
$prices = ['80' => $c['bag_price_default']] + $c['bag_price_sizes'];
$sizes = ['80', '60', '50', '40'];

// Bags for a rectangle with waste.
$slab_bags = function ($l, $w, $t, $size) use ($wf) {
    return Slab_Data::bags($l * $w * $t / 12 * $wf, $size);
};

// Bags for a round post hole (diameter and depth in inches), less a 4x4 post's volume.
$hole_cuft = function ($dia_in, $depth_in, $post = true) {
    $r = $dia_in / 2 / 12;
    $v = M_PI * $r * $r * ($depth_in / 12);
    if ($post) {
        $v -= (3.5 / 12) * (3.5 / 12) * ($depth_in / 12);
    }
    return $v;
};

$e10 = Slab_Data::estimate(10, 10, 4);

// Worked example: 8x12 shed pad, 4 in.
$ex_cuft = 8 * 12 * 4 / 12;
$ex_order = $ex_cuft * $wf;
$ex80 = Slab_Data::bags($ex_order, '80');
$ex60 = Slab_Data::bags($ex_order, '60');
$ex_price80 = $ex80 * $prices['80'];
$ex_price60 = $ex60 * $prices['60'];

$switch_yd = $sd['delivery']['ready_mix_wins_above_yd'];
$water = $c['water_qt_per_80lb'];
$bph = $sd['labor']['bags_per_hour_mixer'];

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> one 80-lb bag of concrete makes <?php echo esc_html(Slab_Data::num($y['80'], 2)); ?> cubic feet, so you need <strong><?php echo (int) Slab_Data::bags(27, '80'); ?> bags per cubic yard</strong> (<?php echo (int) Slab_Data::bags(27, '60'); ?> of the 60-lb size, <?php echo (int) Slab_Data::bags(27, '50'); ?> of the 50-lb, <?php echo (int) Slab_Data::bags(27, '40'); ?> of the 40-lb). A 10x10 slab 4 inches thick takes <strong><?php echo (int) $e10['bags80']; ?> bags of 80-lb mix</strong> or <?php echo (int) $e10['bags60']; ?> bags of 60-lb mix, including <?php echo (int) $waste; ?>% extra for waste.</p>

<p>Bagged concrete is the easy way to pour a small slab, a landing, a set of fence posts or a few deck footings. The only trick is buying the right number of bags. Buy too few and you end up with a cold joint while someone races back to the store. This guide gives you bag yields for every common size, ready-made tables for typical slabs, and the formula to work out anything else.</p>

<h2>Bag Yields by Size</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Bag size</th><th>Yield per bag</th><th>Bags per cubic foot</th><th>Bags per cubic yard</th><th>Covers at 4 in thick</th><th>Typical price</th></tr></thead>
<tbody>
<?php foreach ($sizes as $s) : ?>
<tr><td><strong><?php echo esc_html($s); ?> lb</strong></td><td><?php echo esc_html(Slab_Data::num($y[$s], 3)); ?> ft³</td><td><?php echo esc_html(Slab_Data::num(1 / $y[$s], 2)); ?></td><td><?php echo (int) Slab_Data::bags(27, $s); ?></td><td><?php echo esc_html(Slab_Data::num($y[$s] / (4 / 12), 2)); ?> sq ft</td><td><?php echo esc_html(Slab_Data::money($prices[$s], 2)); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">Yields are for general-purpose concrete mix. Fast-setting and high-strength mixes can yield slightly differently, so check the bag.</div>
</div>
<p>Per cubic foot, the 80-lb bag is the cheapest (<?php echo esc_html(Slab_Data::money($prices['80'] / $y['80'], 2)); ?> per ft³ against <?php echo esc_html(Slab_Data::money($prices['40'] / $y['40'], 2)); ?> for the 40-lb size). Smaller bags make sense if you cannot safely lift 80 lb many times over, or if you are filling a few post holes where a big bag would be left half used.</p>

<h2>The Formula</h2>
<div class="formula-box">
<div class="formula-label">Bags needed</div>
<div class="formula-math">Bags = Length (ft) × Width (ft) × (Thickness (in) ÷ 12) × <?php echo esc_html(Slab_Data::num($wf, 2)); ?> ÷ yield per bag</div>
<div class="formula-explanation">The <?php echo esc_html(Slab_Data::num($wf, 2)); ?> adds <?php echo (int) $waste; ?>% for spillage, uneven ground and form bowing. Use <?php echo esc_html(Slab_Data::num($y['80'], 2)); ?> for 80-lb bags, <?php echo esc_html(Slab_Data::num($y['60'], 2)); ?> for 60-lb, <?php echo esc_html(Slab_Data::num($y['50'], 3)); ?> for 50-lb and <?php echo esc_html(Slab_Data::num($y['40'], 2)); ?> for 40-lb. Always round up to a whole bag.</div>
</div>
<p>If your ground is rough or the edges are irregular, go toward <?php echo (int) $c['waste_pct_range'][1]; ?>%. On a perfectly graded base with rigid forms, <?php echo (int) $c['waste_pct_range'][0]; ?>% can be enough. Our guide to the <a href="<?php echo esc_url(home_url('/concrete-slab-waste-factor-explained/')); ?>">concrete waste factor</a> explains when to use which.</p>

<h2>Bags Needed for Common Slab Sizes (4 Inches Thick)</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Slab size</th><th>Area</th><th>80-lb bags</th><th>60-lb bags</th><th>50-lb bags</th><th>40-lb bags</th></tr></thead>
<tbody>
<?php foreach ([[3, 3], [4, 4], [4, 8], [6, 6], [8, 8], [8, 12], [10, 10], [10, 12], [12, 12]] as $d) : ?>
<tr><td><strong><?php echo (int) $d[0]; ?> × <?php echo (int) $d[1]; ?> ft</strong></td><td><?php echo (int) ($d[0] * $d[1]); ?> sq ft</td><?php foreach ($sizes as $s) : ?><td><?php echo (int) $slab_bags($d[0], $d[1], 4, $s); ?></td><?php endforeach; ?></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">Includes <?php echo (int) $waste; ?>% waste. 4 inches is the usual thickness for patios, walks and shed pads; the IRC minimum for a concrete floor slab on ground is 3.5 inches.</div>
</div>

<h2>Bags Per 100 Square Feet by Thickness</h2>
<p>For any other size, find the bags per 100 sq ft for your thickness and scale it. A 150 sq ft slab needs one and a half times the number shown.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Thickness</th><th>Concrete with waste</th><th>80-lb bags</th><th>60-lb bags</th><th>50-lb bags</th><th>40-lb bags</th></tr></thead>
<tbody>
<?php foreach ([3.5, 4, 5, 6] as $t) : ?>
<tr><td><strong><?php echo esc_html(Slab_Data::num($t)); ?> in</strong></td><td><?php echo esc_html(Slab_Data::num(100 * $t / 12 * $wf / 27, 2)); ?> yd³</td><?php foreach ($sizes as $s) : ?><td><?php echo (int) $slab_bags(10, 10, $t, $s); ?></td><?php endforeach; ?></tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<p>Go to 5 inches if a riding mower or small vehicle will sit on the slab, and 6 inches for a hot tub or heavier loads. Past about <?php echo esc_html(Slab_Data::num($switch_yd)); ?> cubic yards (<?php echo (int) Slab_Data::bags($switch_yd * 27, '80'); ?> bags of 80-lb mix) a ready-mix truck is usually cheaper and far less work. See <a href="<?php echo esc_url(home_url('/ready-mix-vs-bagged-concrete/')); ?>">ready-mix vs. bagged concrete</a> for the break-even.</p>

<h2>Worked Example: 8x12 Shed Pad</h2>
<div class="formula-box">
<div class="formula-label">8 ft × 12 ft × 4 in</div>
<div class="formula-math">8 × 12 × (4 ÷ 12) = <?php echo esc_html(Slab_Data::num($ex_cuft)); ?> ft³ × <?php echo esc_html(Slab_Data::num($wf, 2)); ?> = <?php echo esc_html(Slab_Data::num($ex_order, 1)); ?> ft³</div>
<div class="formula-explanation"><?php echo esc_html(Slab_Data::num($ex_order, 1)); ?> ÷ <?php echo esc_html(Slab_Data::num($y['80'], 2)); ?> = <?php echo (int) $ex80; ?> bags of 80-lb mix (about <?php echo esc_html($m($ex_price80)); ?>), or <?php echo esc_html(Slab_Data::num($ex_order, 1)); ?> ÷ <?php echo esc_html(Slab_Data::num($y['60'], 2)); ?> = <?php echo (int) $ex60; ?> bags of 60-lb mix (about <?php echo esc_html($m($ex_price60)); ?>).</div>
</div>
<p>That is <?php echo esc_html(number_format($ex80 * 80 / 2000, 1)); ?> tons of dry mix. Check your vehicle's payload before you load it, or pay for store delivery. With a rented mixer and three people working steadily at <?php echo esc_html(Slab_Data::r('labor.bags_per_hour_mixer')); ?> bags an hour, the pour takes about <?php echo esc_html(Slab_Data::num($ex80 / $bph[1], 1) . ' to ' . Slab_Data::num($ex80 / $bph[0], 1)); ?> hours. Our <a href="<?php echo esc_url(home_url('/concrete-bags-calculator/')); ?>">concrete bags calculator</a> does this math for any size, and the <a href="<?php echo esc_url(home_url('/concrete-shed-pad-cost-and-construction/')); ?>">shed pad guide</a> covers the rest of the build.</p>

<h2>Fence Posts and Footings</h2>
<p>For round holes, use the cylinder formula and subtract the post if it sits in the concrete:</p>
<div class="formula-box">
<div class="formula-label">Round hole</div>
<div class="formula-math">Volume (ft³) = 3.1416 × (diameter in inches ÷ 24)² × depth in inches ÷ 12</div>
<div class="formula-explanation">For a 4x4 post set in the hole, subtract about <?php echo esc_html(Slab_Data::num((3.5 / 12) * (3.5 / 12), 3)); ?> ft³ for every foot of post below grade (a 3.5 × 3.5 inch post).</div>
</div>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Hole</th><th>Use</th><th>Concrete</th><th>80-lb bags</th><th>50-lb bags</th></tr></thead>
<tbody>
<?php
$holes = [
    [8, 24, true, '4x4 fence post, shallow frost'],
    [10, 36, true, '4x4 fence or mailbox post'],
    [12, 42, false, 'Deck footing in a tube form'],
];
foreach ($holes as $h) :
    $v = $hole_cuft($h[0], $h[1], $h[2]);
    ?>
<tr><td><strong><?php echo (int) $h[0]; ?> in wide × <?php echo (int) $h[1]; ?> in deep</strong></td><td><?php echo esc_html($h[3]); ?></td><td><?php echo esc_html(Slab_Data::num($v, 2)); ?> ft³</td><td><?php echo (int) Slab_Data::bags($v, '80'); ?></td><td><?php echo (int) Slab_Data::bags($v, '50'); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">No waste added; holes are rarely dug to exact size, so buy one spare bag for every few holes. Footing depth must reach below your local frost line.</div>
</div>
<p>For square pads, piers and stepped footings, the <a href="<?php echo esc_url(home_url('/concrete-footing-calculator/')); ?>">concrete footing calculator</a> handles the shapes.</p>

<h2>Mixing Tips That Save Bags (and Your Slab)</h2>
<ul>
<li><strong>Measure the water.</strong> About <?php echo (int) $water; ?> quarts per 80-lb bag is typical; follow the amount printed on your bag. Extra water makes mixing easier but weakens the concrete and adds shrinkage cracks.</li>
<li><strong>Mix whole bags.</strong> Splitting bags throws off the water ratio from batch to batch.</li>
<li><strong>Rent a mixer for anything over a few bags.</strong> A drum mixer runs about <?php echo esc_html($mr($c['mixer_rental_day'])); ?> a day and saves your back. Hand mixing in a tub works for posts and small patches.</li>
<li><strong>Stage the bags beside the mixer</strong> before you start, and keep them off wet ground and covered from rain.</li>
<li><strong>Keep a steady pace.</strong> Place each batch against the last one while it is still workable to avoid weak seams.</li>
<li><strong>Watch the weather.</strong> Below about <?php echo (int) $sd['weather']['cold_f']; ?>°F concrete gains strength slowly and needs protection (ACI 306R); above about <?php echo (int) $sd['weather']['hot_f']; ?>°F it sets fast and needs shade and extra hands (ACI 305R).</li>
<li><strong>Keep it moist while it cures.</strong> Light foot traffic is usually fine after <?php echo esc_html(Slab_Data::r('curing.foot_traffic_h')); ?> hours.</li>
</ul>
<div class="callout callout-tip">
<div class="callout-title">Buy a couple of extra bags</div>
<p>Most stores take back unopened bags. Running short in the middle of a pour costs far more than returning two spare bags the next day.</p>
</div>

<h2>Sources and Method</h2>
<p>Bag counts use the standard volume formula, the published yields for general-purpose concrete mix and <?php echo (int) $waste; ?>% waste, rounded up to whole bags. Prices are typical 2026 big-box prices and vary by store and region (see our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>). Packaged concrete mixes are covered by ASTM C387. The minimum slab thickness comes from the International Residential Code, section R506. For a full project cost with forms, base and labor, use the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a>.</p>
<?php
$html = ob_get_clean();

$e12 = Slab_Data::estimate(12, 12, 4);

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'How many 80-lb bags of concrete make a yard?',
            'a' => Slab_Data::bags(27, '80') . ' bags. Each 80-lb bag yields ' . Slab_Data::num($y['80'], 2) . ' cubic feet, and a cubic yard is 27 cubic feet. With the 60-lb size you need ' . Slab_Data::bags(27, '60') . ' bags per yard.',
        ],
        [
            'q' => 'How many bags of concrete do I need for a 10x10 slab?',
            'a' => 'At 4 inches thick with ' . $waste . '% waste: ' . $e10['bags80'] . ' bags of 80-lb mix, ' . $e10['bags60'] . ' bags of 60-lb mix, ' . $slab_bags(10, 10, 4, '50') . ' bags of 50-lb mix or ' . $slab_bags(10, 10, 4, '40') . ' bags of 40-lb mix.',
        ],
        [
            'q' => 'How many bags of concrete do I need for a 12x12 slab?',
            'a' => 'At 4 inches thick with ' . $waste . '% waste, ' . $e12['bags80'] . ' bags of 80-lb mix or ' . $e12['bags60'] . ' bags of 60-lb mix. That is close to ' . number_format($e12['order_yd'], 2) . ' cubic yards, where a ready-mix truck is often cheaper.',
        ],
        [
            'q' => 'How much area does one 80-lb bag of concrete cover?',
            'a' => 'About ' . Slab_Data::num($y['80'] / (4 / 12), 2) . ' square feet at 4 inches thick, or about ' . Slab_Data::num($y['80'] / (6 / 12), 2) . ' square feet at 6 inches. Add ' . $waste . '% when you buy.',
        ],
        [
            'q' => 'How many bags of concrete do I need for a fence post?',
            'a' => 'For a 4x4 post in a 10-inch hole 3 feet deep, about ' . Slab_Data::bags($hole_cuft(10, 36), '80') . ' bags of 80-lb mix or ' . Slab_Data::bags($hole_cuft(10, 36), '50') . ' bags of 50-lb mix. Hole size varies, so buy a spare bag.',
        ],
    ],
];
