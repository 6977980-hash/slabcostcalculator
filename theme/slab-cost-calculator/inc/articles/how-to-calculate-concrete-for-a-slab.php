<?php
/**
 * Article: How to Calculate Concrete for a Slab (post slug how-to-calculate-concrete-for-a-slab).
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
$n2 = function ($n) {
    return number_format($n, 2);
};

$c = $sd['concrete'];
$waste = $c['waste_pct'];
$wf = 1 + $waste / 100;
$steps = $sd['delivery']['order_step_yd'];
$q = $steps[0];
$h = $steps[1];
$up = function ($yd, $step) {
    return ceil($yd / $step - 1e-9) * $step;
};

// 10x10x4 quick example.
$e10 = Slab_Data::estimate(10, 10, 4);

// Example A: 12x16 patio, 4 in.
$a_cuft = 12 * 16 * 4 / 12;
$a_yd = $a_cuft / 27;
$a_order = $a_yd * $wf;

// Example B: 14 ft circle, 4 in.
$b_area = M_PI * 7 * 7;
$b_cuft = $b_area * 4 / 12;
$b_yd = $b_cuft / 27;
$b_order = $b_yd * $wf;

// Example C: 24x24 garage, 5 in, thickened edge.
$mono = $sd['monolithic'];
$edge_d = $mono['edge_depth_in'][0];
$g = Slab_Data::estimate(24, 24, 5);
$g_edge = Slab_Data::edge_yd(24, 24, 5, $edge_d);
$g_total = $g['order_yd'] + $g_edge;

// Example D: L-shape 20x10 plus 10x10, 4 in.
$d_cuft = (20 * 10 + 10 * 10) * 4 / 12;
$d_order = $d_cuft / 27 * $wf;

// Subgrade half an inch low across a 4-inch slab.
$low_pct = 0.5 / 4 * 100;

$weight_yd = $c['density_lb_ft3'] * 27;

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> multiply length (ft) × width (ft) × thickness (in) ÷ 12 to get cubic feet, then divide by 27 for cubic yards. Add <?php echo (int) $waste; ?>% for waste and round up to what your supplier sells. A 10x10 slab 4 inches thick is <?php echo esc_html($n2($e10['net_yd'])); ?> yd³, or <?php echo esc_html($n2($e10['order_yd'])); ?> yd³ with waste, so you would order <?php echo esc_html(Slab_Data::num($up($e10['order_yd'], $h))); ?> yards of ready-mix or buy <?php echo (int) $e10['bags80']; ?> bags of 80-lb mix.</p>

<p>Concrete is sold by volume, but you measure a slab by its length, width and thickness. The math to connect them is simple. Most ordering mistakes come from mixing inches and feet, forgetting thickened edges, or rounding the wrong way. This guide walks through the formulas step by step, covers circles, L-shapes and footings, and finishes with the mistakes that leave people short on pour day.</p>

<h2>Step by Step</h2>
<ol>
<li><strong>Measure length and width in feet.</strong> Convert inches to decimal feet by dividing by 12. Ten feet six inches is 10.5 ft.</li>
<li><strong>Convert thickness to feet.</strong> Divide inches by 12. Four inches is 0.333 ft.</li>
<li><strong>Multiply</strong> length × width × thickness to get cubic feet.</li>
<li><strong>Divide by 27</strong> to get cubic yards (a cubic yard is 3 ft × 3 ft × 3 ft).</li>
<li><strong>Add waste:</strong> multiply by <?php echo esc_html(Slab_Data::num($wf, 2)); ?> for <?php echo (int) $waste; ?>% extra.</li>
<li><strong>Add extras:</strong> thickened edges, footings, piers or steps poured at the same time.</li>
<li><strong>Round up</strong> to the supplier's order step, or convert to bags.</li>
</ol>

<h2>The Formulas</h2>
<div class="formula-box">
<div class="formula-label">Rectangle or square</div>
<div class="formula-math">yd³ = L × W × (T ÷ 12) ÷ 27</div>
<div class="formula-explanation">L and W in feet, T in inches.</div>
</div>
<div class="formula-box">
<div class="formula-label">Circle</div>
<div class="formula-math">yd³ = 3.1416 × r² × (T ÷ 12) ÷ 27</div>
<div class="formula-explanation">r is the radius in feet (half the diameter). Good for round patios, fire-pit pads and hot tub pads.</div>
</div>
<div class="formula-box">
<div class="formula-label">Triangle</div>
<div class="formula-math">yd³ = (base × height ÷ 2) × (T ÷ 12) ÷ 27</div>
<div class="formula-explanation">For corner pads and angled ends. Height is measured square to the base.</div>
</div>
<div class="formula-box">
<div class="formula-label">Irregular shapes</div>
<div class="formula-math">Split into rectangles, circles and triangles → add the areas → × (T ÷ 12) ÷ 27</div>
<div class="formula-explanation">For a free-form patio, measure the width every few feet along its length, average those widths and multiply by the length. Add a little extra waste for curved forms.</div>
</div>
<div class="formula-box">
<div class="formula-label">Thickened edge (turned-down slab)</div>
<div class="formula-math">Edge yd³ = perimeter ring area × (edge depth − slab thickness) ÷ 12 ÷ 27</div>
<div class="formula-explanation">The ring is the slab edge, typically <?php echo (int) $mono['edge_width_in']; ?> in wide, dug to <?php echo esc_html(Slab_Data::r('monolithic.edge_depth_in')); ?> in deep. Add it to the main slab volume.</div>
</div>
<p>If the slab slopes for drainage but keeps the same thickness, the slope does not change the volume. If the thickness varies (for example, because the base is uneven), use the average thickness from several measurements.</p>

<h2>Unit Conversions</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Thickness</th><th>In feet</th><th>Cubic feet per 100 sq ft</th><th>Cubic yards per 100 sq ft</th><th>Sq ft covered by 1 yd³</th></tr></thead>
<tbody>
<?php foreach ([3.5, 4, 5, 6, 8] as $t) : ?>
<tr><td><strong><?php echo esc_html(Slab_Data::num($t)); ?> in</strong></td><td><?php echo esc_html(number_format($t / 12, 3)); ?></td><td><?php echo esc_html(Slab_Data::num(100 * $t / 12, 1)); ?></td><td><?php echo esc_html($n2(100 * $t / 12 / 27)); ?></td><td><?php echo esc_html(Slab_Data::num(27 * 12 / $t, 1)); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">Before waste. 1 yd³ = 27 ft³ = 0.765 m³. Cured concrete weighs about <?php echo (int) $c['density_lb_ft3']; ?> lb per ft³, or about <?php echo esc_html(number_format($weight_yd)); ?> lb per yd³.</div>
</div>
<p>The last column is handy for a quick check: at 4 inches, every <?php echo esc_html(Slab_Data::num(27 * 12 / 4)); ?> sq ft of slab needs about a yard before waste.</p>

<h2>Waste and Rounding</h2>
<p>Add <?php echo (int) $waste; ?>% to cover spillage, form bowing, concrete left in the chute and a subgrade that is a little low in spots. Go as low as <?php echo (int) $c['waste_pct_range'][0]; ?>% on a laser-graded base with rigid forms, or up to <?php echo (int) $c['waste_pct_range'][1]; ?>% on rough or irregular ground. The <a href="<?php echo esc_url(home_url('/concrete-waste-calculator/')); ?>">concrete waste calculator</a> shows the difference.</p>
<p>Then round up to the supplier's step. Most ready-mix plants sell in steps of <?php echo esc_html(Slab_Data::num($q, 2)); ?> or <?php echo esc_html(Slab_Data::num($h)); ?> yard. For bags, divide cubic feet by the bag yield (<?php echo esc_html(Slab_Data::num($c['bag_yields']['80'], 2)); ?> ft³ for an 80-lb bag) and round up to a whole bag. Never round down: a short load means a cold joint and a second delivery fee, while a little leftover concrete can fill a post hole or a few stepping-stone forms.</p>

<h2>Worked Examples</h2>
<p><strong>A. 12x16 patio, 4 inches thick</strong></p>
<div class="formula-box">
<div class="formula-label">Rectangle</div>
<div class="formula-math">12 × 16 × (4 ÷ 12) = <?php echo esc_html(Slab_Data::num($a_cuft)); ?> ft³ ÷ 27 = <?php echo esc_html($n2($a_yd)); ?> yd³</div>
<div class="formula-explanation">× <?php echo esc_html(Slab_Data::num($wf, 2)); ?> = <?php echo esc_html($n2($a_order)); ?> yd³. Order <?php echo esc_html(Slab_Data::num($up($a_order, $q), 2)); ?> yd³ from a quarter-yard plant or <?php echo esc_html(Slab_Data::num($up($a_order, $h), 2)); ?> from a half-yard plant, or buy <?php echo (int) Slab_Data::bags($a_order * 27, '80'); ?> bags of 80-lb mix.</div>
</div>

<p><strong>B. Round patio, 14 ft across, 4 inches thick</strong></p>
<div class="formula-box">
<div class="formula-label">Circle, radius 7 ft</div>
<div class="formula-math">3.1416 × 7² = <?php echo esc_html(Slab_Data::num($b_area, 1)); ?> sq ft × (4 ÷ 12) = <?php echo esc_html(Slab_Data::num($b_cuft, 1)); ?> ft³ ÷ 27 = <?php echo esc_html($n2($b_yd)); ?> yd³</div>
<div class="formula-explanation">× <?php echo esc_html(Slab_Data::num($wf, 2)); ?> = <?php echo esc_html($n2($b_order)); ?> yd³. Order <?php echo esc_html(Slab_Data::num($up($b_order, $q), 2)); ?> yd³. Curved forms flex, so lean toward the top of the waste range.</div>
</div>

<p><strong>C. 24x24 garage slab, 5 inches thick, <?php echo (int) $edge_d; ?>-inch thickened edge</strong></p>
<div class="formula-box">
<div class="formula-label">Slab plus edge</div>
<div class="formula-math">Slab: 24 × 24 × (5 ÷ 12) ÷ 27 × <?php echo esc_html(Slab_Data::num($wf, 2)); ?> = <?php echo esc_html($n2($g['order_yd'])); ?> yd³. Edge: <?php echo esc_html($n2($g_edge)); ?> yd³</div>
<div class="formula-explanation">The edge is a <?php echo (int) $mono['edge_width_in']; ?>-inch ring around the perimeter, <?php echo (int) ($edge_d - 5); ?> inches deeper than the slab. Total <?php echo esc_html($n2($g_total)); ?> yd³, so order <?php echo esc_html(Slab_Data::num($up($g_total, $h), 2)); ?> yd³. Forgetting the edge would leave you <?php echo esc_html($n2($g_edge)); ?> yd³ short.</div>
</div>

<p><strong>D. L-shaped patio: 20x10 plus a 10x10 wing, 4 inches thick</strong></p>
<div class="formula-box">
<div class="formula-label">Split into rectangles</div>
<div class="formula-math">(20 × 10) + (10 × 10) = <?php echo (int) (20 * 10 + 10 * 10); ?> sq ft × (4 ÷ 12) = <?php echo esc_html(Slab_Data::num($d_cuft)); ?> ft³ ÷ 27 × <?php echo esc_html(Slab_Data::num($wf, 2)); ?> = <?php echo esc_html($n2($d_order)); ?> yd³</div>
<div class="formula-explanation">Order <?php echo esc_html(Slab_Data::num($up($d_order, $h), 2)); ?> yd³. Measure each rectangle so they do not overlap.</div>
</div>
<p>Want to price these? The <a href="<?php echo esc_url(home_url('/concrete-cost-per-cubic-yard-pricing-explained/')); ?>">cost per cubic yard guide</a> covers ready-mix pricing and fees, and the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a> turns a volume into a full project budget.</p>

<h2>Common Mistakes</h2>
<ul>
<li><strong>Multiplying by inches instead of feet.</strong> 10 × 10 × 4 = 400 is not cubic feet. Divide the thickness by 12 first.</li>
<li><strong>Trusting lumber sizes.</strong> A 2x4 form board is actually 3.5 inches tall and a 2x6 is 5.5 inches. If you screed to the top of a 2x4, your slab is 3.5 inches, the IRC minimum for a floor slab, not 4.</li>
<li><strong>Ignoring a low subgrade.</strong> If the base is half an inch low across a 4-inch slab, you need about <?php echo esc_html(Slab_Data::num($low_pct, 1)); ?>% more concrete. Check depth with a string line in several places before the truck comes.</li>
<li><strong>Forgetting extras.</strong> Thickened edges, interior footings, piers and steps poured at the same time all add volume. The <a href="<?php echo esc_url(home_url('/concrete-footing-calculator/')); ?>">concrete footing calculator</a> handles them.</li>
<li><strong>Skipping waste, or rounding down.</strong> Both lead to the same place: a short load in the middle of a pour.</li>
<li><strong>Using the wrong thickness for the job.</strong> 4 inches suits patios and walks. Driveways and garage floors usually need 5 to 6. See <a href="<?php echo esc_url(home_url('/how-thick-should-a-concrete-slab-be/')); ?>">how thick a concrete slab should be</a>.</li>
</ul>
<div class="callout callout-tip">
<div class="callout-title">Let the calculator check your math</div>
<p>Enter your dimensions in the <a href="<?php echo esc_url(home_url('/concrete-volume-calculator/')); ?>">concrete volume calculator</a> to confirm your hand calculation. If you are using bags, our guide to <a href="<?php echo esc_url(home_url('/how-many-bags-of-concrete-do-i-need/')); ?>">how many bags of concrete you need</a> has tables for every bag size.</p>
</div>

<h2>Sources and Method</h2>
<p>Volumes use standard geometry and the fixed conversions of 12 inches per foot and 27 cubic feet per cubic yard. The waste allowance, bag yields and order steps are this site's working figures (see our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>). Minimum slab thickness follows the International Residential Code, section R506, and thickened-edge dimensions follow common practice for slab-on-grade foundations under IRC section R403. ACI 302.1R, the American Concrete Institute's guide to floor and slab construction, covers subgrade tolerance and slab thickness.</p>
<?php
$html = ob_get_clean();

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'What is the formula for calculating concrete for a slab?',
            'a' => 'Cubic yards = length (ft) × width (ft) × thickness (in) ÷ 12 ÷ 27. Then add ' . $waste . '% for waste and round up to your supplier\'s order step or to whole bags.',
        ],
        [
            'q' => 'How many yards of concrete do I need for a 12x16 slab?',
            'a' => 'At 4 inches thick it is ' . $n2($a_yd) . ' cubic yards, or ' . $n2($a_order) . ' yd³ with ' . $waste . '% waste. Order ' . Slab_Data::num($up($a_order, $q), 2) . ' yd³ if your plant sells in quarter yards.',
        ],
        [
            'q' => 'How many square feet does a yard of concrete cover?',
            'a' => 'About ' . Slab_Data::num(27 * 12 / 4) . ' square feet at 4 inches thick, ' . Slab_Data::num(27 * 12 / 5, 1) . ' at 5 inches and ' . Slab_Data::num(27 * 12 / 6) . ' at 6 inches, before waste.',
        ],
        [
            'q' => 'How much extra concrete should I order?',
            'a' => 'Add ' . $waste . '% for a typical slab. Use as little as ' . $c['waste_pct_range'][0] . '% on a laser-graded base with rigid forms, and up to ' . $c['waste_pct_range'][1] . '% for rough ground or curved forms. Always round up to the next order step.',
        ],
        [
            'q' => 'How do I calculate concrete for a round slab?',
            'a' => 'Multiply 3.1416 × radius² (in feet) × thickness in inches ÷ 12, then divide by 27. A 14-foot round patio 4 inches thick is ' . $n2($b_yd) . ' cubic yards, or ' . $n2($b_order) . ' yd³ with waste.',
        ],
    ],
];
