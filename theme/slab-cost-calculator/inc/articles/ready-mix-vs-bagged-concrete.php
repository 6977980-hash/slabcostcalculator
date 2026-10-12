<?php
/**
 * Article: Ready-Mix vs. Bagged Concrete (post slug ready-mix-vs-bagged-concrete).
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
$dl = $sd['delivery'];
$rm = $c['ready_mix_default'];
$fee = $dl['fee_default'];
$flat = $dl['short_load_flat'];
$bag = $c['bag_price_default'];
$y80 = $c['bag_yields']['80'];
$bag_yd = $bag / $y80 * 27;
$step = $dl['order_step_yd'][1];
$bph = $sd['labor']['bags_per_hour_mixer'];
$mixer = $c['mixer_rental_day'];
$waste = $c['waste_pct'];
$cover4 = 27 * 12 / 4;

// Ready-mix cost for a volume: rounded up to the plant's order step, plus delivery fee and optional short-load fee.
$rm_cost = function ($yd, $extra = 0) use ($rm, $fee, $step) {
    $order = ceil($yd / $step - 1e-9) * $step;
    return $order * $rm + $fee + $extra;
};

// Continuous break-even volume (yd³) where bags cost the same as the truck.
$be_lo = $fee / ($bag_yd - $rm);
$be_hi = ($fee + $flat[1]) / ($bag_yd - $rm);

// Worked example: 12x12 patio, 4 in.
$ex = Slab_Data::estimate(12, 12, 4);
$ex_order = ceil($ex['order_yd'] / $step - 1e-9) * $step;
$ex_rm = [$rm_cost($ex['order_yd']), $rm_cost($ex['order_yd'], $flat[1])];
$ex_bags = [$ex['bagged'] + $mixer[0], $ex['bagged'] + $mixer[1]];
$ex_hours = [$ex['bags80'] / $bph[1], $ex['bags80'] / $bph[0]];
$ex_tons = $ex['bags80'] * 80 / 2000;
$ex_unload = [$ex_order * $dl['unload_min_per_yd'][0], $ex_order * $dl['unload_min_per_yd'][1]];

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> bagged concrete costs about <strong><?php echo esc_html($m($bag_yd)); ?> per cubic yard</strong> in 80-lb bags, while ready-mix runs about <strong><?php echo esc_html(Slab_Data::mr('concrete.psi_base_price', 0, ' to ')); ?></strong> per yard plus a delivery fee. Bags usually win below about <?php echo esc_html(Slab_Data::num($dl['bags_win_below_yd'])); ?> cubic yard, ready-mix usually wins above about <?php echo esc_html(Slab_Data::num($dl['ready_mix_wins_above_yd'])); ?> yards, and in between it depends on your supplier's delivery and short-load fees. Ready-mix is also more consistent, because the plant controls the water.</p>

<p>The choice between a truck and a pallet of bags comes down to three things: money, labor and quality. Bags have no delivery fee, so they are cheap for tiny jobs. But each bag must be lifted, mixed and placed by hand, and that work grows fast. Ready-mix has a fixed delivery cost, so it gets cheaper per yard the more you order. This guide puts real numbers on each side.</p>

<h2>The Cost Per Yard Gap</h2>
<p>An 80-lb bag yields <?php echo esc_html(Slab_Data::num($y80, 2)); ?> cubic feet, so a cubic yard (27 ft³) takes <?php echo (int) Slab_Data::bags(27, '80'); ?> bags. At about <?php echo esc_html(Slab_Data::money($bag, 2)); ?> per bag, that is <?php echo esc_html($m($bag_yd)); ?> per yard, roughly twice the <?php echo esc_html($m($rm)); ?> our calculator uses for 3,000 PSI ready-mix. Smaller bags cost even more per yard (see our <a href="<?php echo esc_url(home_url('/concrete-cost-per-cubic-yard-pricing-explained/')); ?>">cost per cubic yard guide</a>). The truck wins on unit price every time. What it loses on is the fixed fee.</p>

<h2>Break-Even Table</h2>
<p>Here is what each route costs at common job sizes. Ready-mix is rounded up to the next <?php echo esc_html(Slab_Data::num($step)); ?> yard, with the <?php echo esc_html($m($fee)); ?> delivery fee our calculator uses. The last column adds a short-load fee at the top of the usual <?php echo esc_html($mr($flat)); ?> range, which many plants charge on orders under their minimum.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Concrete needed</th><th>Covers at 4 in</th><th>80-lb bags</th><th>Bag cost</th><th>Ready-mix (delivery fee)</th><th>Ready-mix + high short-load fee</th><th>Cheaper</th></tr></thead>
<tbody>
<?php foreach ([0.25, 0.5, 0.75, 1, 1.5, 2, 3, 4] as $yd) :
    $n = Slab_Data::bags($yd * 27, '80');
    $bc = $n * $bag;
    $r1 = $rm_cost($yd);
    $r2 = $rm_cost($yd, $flat[1]);
    if ($bc <= $r1) {
        $win = 'Bags';
    } elseif ($bc <= $r2) {
        $win = 'Depends on fees';
    } else {
        $win = 'Ready-mix';
    }
    ?>
<tr><td><strong><?php echo esc_html(Slab_Data::num($yd, 2)); ?> yd³</strong></td><td><?php echo esc_html(number_format($yd * $cover4)); ?> sq ft</td><td><?php echo (int) $n; ?></td><td><?php echo esc_html($m($bc)); ?></td><td><?php echo esc_html($m($r1)); ?></td><td><?php echo esc_html($m($r2)); ?></td><td><strong><?php echo esc_html($win); ?></strong></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">80-lb bags at <?php echo esc_html(Slab_Data::money($bag, 2)); ?>; ready-mix at <?php echo esc_html($m($rm)); ?>/yd³. Mixer rental, fuel surcharges and pump fees not included. Add <?php echo (int) $waste; ?>% waste to your measured volume before using this table.</div>
</div>
<p>On price alone, the break-even sits at about <?php echo esc_html(Slab_Data::num($be_lo, 2)); ?> yards when the only extra is a <?php echo esc_html($m($fee)); ?> delivery fee, and moves out to about <?php echo esc_html(Slab_Data::num($be_hi, 2)); ?> yards when a plant adds a <?php echo esc_html($m($flat[1])); ?> short-load fee on top. That is where the rule of thumb comes from. Call two local plants and ask for the full delivered price on your volume before you buy a single bag.</p>

<h2>Labor and Time: The Hidden Cost of Bags</h2>
<p>Price is only half the story. A 3-person crew with one rented drum mixer can mix and place roughly <?php echo esc_html(Slab_Data::r('labor.bags_per_hour_mixer')); ?> 80-lb bags an hour. That sounds fast until you do the math on a slab:</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Concrete</th><th>80-lb bags</th><th>Weight to lift</th><th>Mixing time (3 people, 1 mixer)</th></tr></thead>
<tbody>
<?php foreach ([0.5, 1, 1.5, 2, 3] as $yd) : $n = Slab_Data::bags($yd * 27, '80'); ?>
<tr><td><strong><?php echo esc_html(Slab_Data::num($yd, 2)); ?> yd³</strong></td><td><?php echo (int) $n; ?></td><td><?php echo esc_html(number_format($n * 80 / 2000, 1)); ?> tons</td><td><?php echo esc_html(Slab_Data::num($n / $bph[1], 1) . ' to ' . Slab_Data::num($n / $bph[0], 1)); ?> hours</td></tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<p>Every bag gets lifted at least twice (into the cart, then into the mixer), so the real work is more than the tonnage suggests. Add a mixer rental of about <?php echo esc_html($mr($mixer)); ?> a day. A ready-mix truck, by contrast, unloads in about <?php echo esc_html(Slab_Data::r('delivery.unload_min_per_yd')); ?> minutes per yard. Your crew's job is spreading, screeding and finishing, not mixing.</p>
<div class="callout callout-warning">
<div class="callout-title">Cold joints on long bag pours</div>
<p>If the first batches start to stiffen before the last ones are placed, the slab ends up with weak seams between batches. On a pour that will take more than a couple of hours, either add another mixer and more helpers, or split the slab into sections with planned joints.</p>
</div>

<h2>Strength and Consistency: ASTM C94 vs. ASTM C387</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th></th><th>Ready-mix</th><th>Bagged mix</th></tr></thead>
<tbody>
<tr><td><strong>Standard</strong></td><td>ASTM C94, Standard Specification for Ready-Mixed Concrete</td><td>ASTM C387, Packaged Dry, Combined Materials for Concrete</td></tr>
<tr><td><strong>Strength</strong></td><td>You order the grade, from <?php echo esc_html(number_format(min(array_keys($c['psi_grades'])))); ?> to <?php echo esc_html(number_format(max(array_keys($c['psi_grades'])))); ?> PSI and up</td><td>C387 normal-strength mix must reach at least <?php echo esc_html(number_format($c['bag_psi'][0])); ?> PSI at 28 days; many general-purpose bags are rated <?php echo esc_html(number_format($c['bag_psi'][1])); ?> PSI</td></tr>
<tr><td><strong>Water control</strong></td><td>Batched by weight at the plant; slump agreed in advance</td><td>Depends on you. About <?php echo (int) $c['water_qt_per_80lb']; ?> quarts per 80-lb bag is typical; more water means a weaker slab</td></tr>
<tr><td><strong>Air entrainment</strong></td><td>Available on request (<?php echo esc_html(Slab_Data::r('concrete.air_entrainment_pct', '% to ')); ?>% air) for freeze-thaw exposure</td><td>Most general-purpose bags are not air-entrained; check the label</td></tr>
<tr><td><strong>Batch to batch</strong></td><td>One uniform load</td><td>Varies with each batch's water and mixing time</td></tr>
<tr><td><strong>Time limit</strong></td><td>Discharge within <?php echo (int) $dl['discharge_min']; ?> minutes of batching (C94 default)</td><td>Each batch has its own clock; you set the pace</td></tr>
</tbody>
</table>
</div>
<p>Both can make a strong slab. A bag rated at <?php echo esc_html(number_format($c['bag_psi'][1])); ?> PSI is stronger on paper than the <?php echo esc_html(number_format($sd['code']['psi_exposed'])); ?> PSI minimum the IRC (Table R402.2) sets for exposed concrete in mild climates. The difference is control. A plant weighs every ingredient; a homeowner with a hose rarely adds the same water twice. In freeze-thaw areas, where the IRC calls for <?php echo esc_html(number_format($sd['code']['psi_severe'])); ?> PSI air-entrained concrete on garage floors, porches and steps, ready-mix is the safer route for anything large.</p>

<h2>Worked Example: 12x12 Patio, 4 Inches Thick</h2>
<div class="formula-box">
<div class="formula-label">12 ft × 12 ft × 4 in, with <?php echo (int) $waste; ?>% waste</div>
<div class="formula-math"><?php echo esc_html(number_format($ex['order_yd'], 2)); ?> yd³ = <?php echo (int) $ex['bags80']; ?> bags (80 lb) or <?php echo esc_html(Slab_Data::num($ex_order)); ?> yd³ ready-mix</div>
<div class="formula-explanation">Bags: <?php echo esc_html($m($ex['bagged'])); ?> plus a mixer rental = <?php echo esc_html($mr($ex_bags)); ?>, <?php echo esc_html(number_format($ex_tons, 1)); ?> tons to lift and <?php echo esc_html(Slab_Data::num($ex_hours[0], 1) . ' to ' . Slab_Data::num($ex_hours[1], 1)); ?> hours of mixing. Ready-mix: <?php echo esc_html($mr($ex_rm)); ?> delivered depending on the short-load fee, unloaded in about <?php echo esc_html(Slab_Data::range($ex_unload)); ?> minutes.</div>
</div>
<p><?php echo esc_html($ex_rm[1] < $ex_bags[0] ? 'For this patio, once you count the mixer rental, ready-mix costs less even with a high short-load fee, and it saves an afternoon of heavy labor.' : 'For this patio, ready-mix costs less with a normal delivery fee and about the same with a high short-load fee, and it saves an afternoon of heavy labor.'); ?> The catch is access: the truck needs to get within about <?php echo esc_html(Slab_Data::r('site.chute_reach_ft')); ?> ft of the forms, or you need wheelbarrows and helpers, or a line pump at <?php echo esc_html(Slab_Data::mr('pump.line_job', 0, ' to ')); ?>.</p>

<h2>When Each One Wins</h2>
<p><strong>Choose bags when:</strong></p>
<ul>
<li>The job is under about <?php echo esc_html(Slab_Data::num($dl['bags_win_below_yd'])); ?> yard: fence posts, a small landing, an AC pad, a few footings.</li>
<li>The site is far from the street, behind a house, or up steps where a truck and pump would cost more than the concrete.</li>
<li>You want to work in sections over a weekend at your own pace.</li>
<li>You need a special product sold in bags, such as fast-setting mix for posts or high-strength mix for a small repair.</li>
</ul>
<p><strong>Choose ready-mix when:</strong></p>
<ul>
<li>The job is over about <?php echo esc_html(Slab_Data::num($dl['ready_mix_wins_above_yd'])); ?> yards: patios, driveways, garage floors and anything bigger.</li>
<li>The slab must be poured in one piece with no cold joints.</li>
<li>You need a specific strength, air entrainment or fiber for freeze-thaw, road salt or vehicle loads.</li>
<li>You do not have three strong helpers and a mixer for half a day.</li>
</ul>

<h2>Run Your Numbers</h2>
<p>Measure your slab and get the volume from the <a href="<?php echo esc_url(home_url('/concrete-volume-calculator/')); ?>">concrete volume calculator</a>, then compare routes with the <a href="<?php echo esc_url(home_url('/concrete-bags-calculator/')); ?>">concrete bags calculator</a> and the <a href="<?php echo esc_url(home_url('/ready-mix-concrete-calculator/')); ?>">ready-mix concrete calculator</a>. For a full project budget including forms, base and labor, use the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a>. If you are going the bag route, our guide to <a href="<?php echo esc_url(home_url('/how-many-bags-of-concrete-do-i-need/')); ?>">how many bags of concrete you need</a> covers every bag size.</p>

<h2>Sources and Method</h2>
<p>Bag yields are the published yields for 80, 60, 50 and 40-lb bags of general-purpose concrete mix. Prices and mixing rates are this site's 2026 working figures (see our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>) and vary by region and store. Ready-mix requirements come from ASTM C94; packaged mix requirements come from ASTM C387. Strength minimums follow the International Residential Code, Table R402.2. Your local plant's minimum load and fees decide where your own break-even falls.</p>
<?php
$html = ob_get_clean();

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'Is it cheaper to use bags or ready-mix concrete?',
            'a' => 'For small jobs, bags. For anything over about ' . Slab_Data::num($dl['ready_mix_wins_above_yd']) . ' cubic yards, ready-mix. Bagged concrete costs about ' . $m($bag_yd) . ' per yard in 80-lb bags against roughly ' . $m($rm) . ' for ready-mix, but ready-mix adds a delivery fee and often a short-load fee of ' . $mr($flat) . ' on small orders.',
        ],
        [
            'q' => 'At what point is ready-mix cheaper than bags?',
            'a' => 'On price alone, at about ' . Slab_Data::num($be_lo, 2) . ' cubic yards when the only extra is a ' . $m($fee) . ' delivery fee, and at about ' . Slab_Data::num($be_hi, 2) . ' yards if the plant adds a ' . $m($flat[1]) . ' short-load fee. Below about ' . Slab_Data::num($dl['bags_win_below_yd']) . ' yard, bags usually win.',
        ],
        [
            'q' => 'Is bagged concrete as strong as ready-mix?',
            'a' => 'It can be. ASTM C387 requires normal-strength bag mix to reach at least ' . number_format($c['bag_psi'][0]) . ' PSI at 28 days, and many general-purpose bags are rated ' . number_format($c['bag_psi'][1]) . ' PSI. The weak point is water: adding more than the bag calls for (about ' . (int) $c['water_qt_per_80lb'] . ' quarts per 80-lb bag) lowers strength. Ready-mix is batched by weight at the plant, so it is more consistent.',
        ],
        [
            'q' => 'How long does it take to mix a yard of bagged concrete?',
            'a' => 'A yard is ' . Slab_Data::bags(27, '80') . ' bags of 80-lb mix. A 3-person crew with one drum mixer handles about ' . Slab_Data::r('labor.bags_per_hour_mixer') . ' bags an hour, so plan on ' . Slab_Data::num(Slab_Data::bags(27, '80') / $bph[1], 1) . ' to ' . Slab_Data::num(Slab_Data::bags(27, '80') / $bph[0], 1) . ' hours per yard.',
        ],
        [
            'q' => 'How many bags of concrete equal one yard?',
            'a' => Slab_Data::bags(27, '80') . ' bags of 80-lb mix, ' . Slab_Data::bags(27, '60') . ' bags of 60-lb mix, ' . Slab_Data::bags(27, '50') . ' bags of 50-lb mix, or ' . Slab_Data::bags(27, '40') . ' bags of 40-lb mix.',
        ],
    ],
];
