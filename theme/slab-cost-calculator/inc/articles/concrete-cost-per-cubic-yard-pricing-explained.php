<?php
/**
 * Article: Concrete Cost Per Cubic Yard (post slug concrete-cost-per-cubic-yard-pricing-explained).
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
$pump = $sd['pump'];
$code = $sd['code'];
$rm = $c['ready_mix_default'];
$fee = $dl['fee_default'];
$min = $dl['common_min_yd'][0];
$flat = $dl['short_load_flat'];
$p3000 = Slab_Data::psi_price(3000);
$p4000 = Slab_Data::psi_price(4000);
$step_half = $dl['order_step_yd'][1];

// Bagged concrete expressed per cubic yard, for each bag size.
$bag_prices = ['80' => $c['bag_price_default']] + $c['bag_price_sizes'];
$bag_rows = [];
foreach ($c['bag_yields'] as $size => $yield) {
    $n = Slab_Data::bags(27, (string) $size);
    $bag_rows[$size] = ['n' => $n, 'price' => $bag_prices[$size], 'yd' => $n * $bag_prices[$size]];
}

// Delivered cost per yard at different order sizes.
$delivered = function ($yd) use ($rm, $fee, $min, $flat) {
    $short = $yd < $min;
    $lo = $yd * $rm + $fee + ($short ? $flat[0] : 0);
    $hi = $yd * $rm + $fee + ($short ? $flat[1] : 0);
    return ['short' => $short, 'total' => [$lo, $hi], 'per' => [$lo / $yd, $hi / $yd]];
};

// Worked example: 24x24 garage, 5 in, 4,000 PSI.
$g = Slab_Data::estimate(24, 24, 5);
$g_order = ceil($g['order_yd'] / $step_half - 1e-9) * $step_half;
$g_conc = [$g_order * $p4000[0], $g_order * $p4000[1]];
$g_total = [$g_conc[0] + $fee + $dl['surcharge'][0], $g_conc[1] + $fee + $dl['surcharge'][1]];
$g_unload = [$g_order * $dl['unload_min_per_yd'][0], $g_order * $dl['unload_min_per_yd'][1]];

// One yard at 4 in covers 81 sq ft.
$cover4 = 27 * 12 / 4;

// Trucks for a 30x40 shop floor at 4 in.
$shop = Slab_Data::estimate(30, 40, 4);
$shop_trucks = (int) ceil($shop['order_yd'] / $dl['truck_capacity_yd'][1] - 1e-9);

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> standard 3,000 PSI ready-mix concrete costs about <strong><?php echo esc_html($mr($p3000)); ?> per cubic yard</strong> in 2026 before delivery, and 4,000 PSI about <strong><?php echo esc_html($mr($p4000)); ?></strong>. Small orders cost far more per yard once delivery and short-load fees are added: <?php echo esc_html(Slab_Data::num(2)); ?> yards works out to about <?php echo esc_html($mr($delivered(2)['per'])); ?> per yard delivered. Bagged concrete in 80-lb bags works out to about <?php echo esc_html($m($bag_rows['80']['yd'])); ?> per yard.</p>

<p>The price per cubic yard is the number every concrete supplier quotes, but it is rarely the number you pay. Strength grade, the size of your order, delivery fees, how long the truck waits and whether you need a pump all change the real cost. This guide walks through each piece so you can read a ready-mix quote and know what it should total.</p>

<h2>Ready-Mix Price by PSI Grade</h2>
<p>PSI is the compressive strength the mix reaches at 28 days. Each <?php echo esc_html(number_format($c['psi_step'])); ?>-PSI step up adds roughly <?php echo esc_html(Slab_Data::mr('concrete.psi_step_price', 0, ' to ')); ?> per yard, mostly for extra cement.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Strength</th><th>Price per yd³</th><th>Typical use</th></tr></thead>
<tbody>
<?php foreach ($c['psi_grades'] as $psi => $g_info) : ?>
<tr><td><strong><?php echo esc_html(number_format($psi)); ?> PSI</strong></td><td><?php echo esc_html($mr(Slab_Data::psi_price($psi))); ?></td><td><?php echo esc_html($g_info['use']); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">Price for the concrete only, before delivery, short-load fees, surcharges and admixtures. Prices vary by region and plant.</div>
</div>
<p>The International Residential Code (Table R402.2) sets minimums for homes: <?php echo esc_html(number_format($code['psi_protected'])); ?> PSI where concrete is protected from weather, <?php echo esc_html(number_format($code['psi_exposed'])); ?> PSI for exposed work in mild climates, and <?php echo esc_html(number_format($code['psi_severe'])); ?> PSI with <?php echo esc_html(Slab_Data::r('concrete.air_entrainment_pct', '% to ')); ?>% entrained air for garage floors, porches and steps where weathering is severe. Where road salt is used, many contractors step up to <?php echo esc_html(number_format($code['psi_deicing_recommended'])); ?> PSI. The jump from 3,000 to 4,000 PSI adds only <?php echo esc_html($mr(Slab_Data::psi_extra(4000, 1))); ?> per yard, so it is rarely worth skimping on a driveway.</p>

<h2>What You Actually Pay Per Yard</h2>
<p>Every load carries a delivery charge, and orders below the plant's minimum (commonly <?php echo esc_html(Slab_Data::r('delivery.common_min_yd')); ?> yards, anywhere from <?php echo esc_html(Slab_Data::r('delivery.min_load_yd')); ?> depending on the plant) usually add a short-load fee. Spread over a small order, those fees swamp the concrete price.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Order size</th><th>Concrete at <?php echo esc_html($m($rm)); ?>/yd³</th><th>Short-load fee?</th><th>Total delivered</th><th>Effective cost per yd³</th></tr></thead>
<tbody>
<?php foreach ([1, 2, 3, 4, 6, 8, 10] as $yd) : $d = $delivered($yd); ?>
<tr><td><strong><?php echo (int) $yd; ?> yd³</strong></td><td><?php echo esc_html($m($yd * $rm)); ?></td><td><?php echo $d['short'] ? esc_html('Yes, ' . $mr($flat)) : esc_html('No'); ?></td><td><?php echo esc_html($d['short'] ? $mr($d['total']) : $m($d['total'][0])); ?></td><td><?php echo esc_html($d['short'] ? $mr($d['per']) : $m($d['per'][0])); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">3,000 PSI at the calculator default of <?php echo esc_html($m($rm)); ?>/yd³, plus a <?php echo esc_html($m($fee)); ?> delivery fee on every order, plus a flat short-load fee below <?php echo esc_html(Slab_Data::num($min)); ?> yd³. Fuel surcharges are extra.</div>
</div>
<p>Some plants charge the short-load fee per missing yard instead: <?php echo esc_html(Slab_Data::mr('delivery.short_load_per_yd', 0, ' to ')); ?> for each yard you are below the minimum. Either way, the lesson is the same. If you can combine pours (a patio and a walkway, or a slab and its footings) into one order, the cost per yard drops quickly.</p>

<h2>Truck Capacity, Order Steps and Unloading Time</h2>
<ul>
<li><strong>Truck size:</strong> a standard transit mixer carries <?php echo esc_html(Slab_Data::r('delivery.truck_capacity_yd')); ?> yards. A 30x40 shop floor at 4 inches needs <?php echo esc_html(number_format($shop['order_yd'], 1)); ?> yd³ with waste, so plan on <?php echo (int) $shop_trucks; ?> trucks or more, scheduled so the second arrives as the first finishes.</li>
<li><strong>Order steps:</strong> plants sell in steps of <?php echo esc_html(Slab_Data::num($dl['order_step_yd'][0], 2)); ?> or <?php echo esc_html(Slab_Data::num($dl['order_step_yd'][1])); ?> yard. Round your order up, never down. Running short mid-pour leaves a cold joint and a second delivery fee.</li>
<li><strong>Free unloading time:</strong> most plants allow about <?php echo esc_html(Slab_Data::r('delivery.unload_min_per_yd')); ?> minutes per yard. After that, waiting time is billed. Have forms, tools and helpers ready before the truck arrives.</li>
<li><strong>Time limit:</strong> ASTM C94, the standard for ready-mixed concrete, calls for the load to be discharged within <?php echo (int) $dl['discharge_min']; ?> minutes of batching unless the buyer agrees otherwise. Long drives from the plant eat into your working time.</li>
</ul>

<h2>Common Add-Ons and Surcharges</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Item</th><th>Typical cost</th><th>When you need it</th></tr></thead>
<tbody>
<tr><td>Delivery fee</td><td><?php echo esc_html($m($fee)); ?> per load (calculator default)</td><td>Every order</td></tr>
<tr><td>Short-load fee</td><td><?php echo esc_html($mr($flat)); ?> flat, or <?php echo esc_html(Slab_Data::mr('delivery.short_load_per_yd', 0, ' to ')); ?> per yard short</td><td>Orders below the plant minimum</td></tr>
<tr><td>Fuel or environmental surcharge</td><td><?php echo esc_html(Slab_Data::mr('delivery.surcharge', 0, ' to ')); ?> per load</td><td>Most plants, varies with fuel prices</td></tr>
<tr><td>Synthetic fiber</td><td><?php echo esc_html($mr($c['admixture_yd']['fiber'])); ?> per yd³</td><td>Helps control plastic shrinkage cracks; not a substitute for rebar</td></tr>
<tr><td>Set accelerator</td><td><?php echo esc_html($mr($c['admixture_yd']['accelerator'])); ?> per yd³</td><td>Cool weather, faster finishing</td></tr>
<tr><td>Hot water (winter)</td><td><?php echo esc_html($mr($c['admixture_yd']['hot_water'])); ?> per yd³</td><td>Pours near or below <?php echo (int) $sd['weather']['cold_f']; ?>°F</td></tr>
<tr><td>Pump mix (smaller stone)</td><td><?php echo esc_html($mr($pump['pump_mix_extra'])); ?> more per yd³</td><td>Line pumps</td></tr>
<tr><td>Line pump</td><td><?php echo esc_html($mr($pump['line_job'])); ?> per job</td><td>Truck cannot get within <?php echo esc_html(Slab_Data::r('site.chute_reach_ft')); ?> ft of the forms</td></tr>
<tr><td>Boom pump</td><td><?php echo esc_html($mr($pump['boom_job'])); ?> per job</td><td>Large slabs, over a house or fence</td></tr>
</tbody>
</table>
</div>
<p>Air entrainment is usually built into the exterior mix price in cold regions, but ask. A pump is billed by the job with an hourly minimum, so it costs about the same for 3 yards as for 10. On a small slab, a few helpers with wheelbarrows are often cheaper.</p>

<h2>Bagged Concrete: Cost Per Yard Equivalent</h2>
<p>Bags are priced per bag, but converting them to a yard makes the comparison fair.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Bag size</th><th>Yield per bag</th><th>Bags per yd³</th><th>Price per bag</th><th>Cost per yd³</th></tr></thead>
<tbody>
<?php foreach ($bag_rows as $size => $b) : ?>
<tr><td><strong><?php echo esc_html($size); ?> lb</strong></td><td><?php echo esc_html(Slab_Data::num($c['bag_yields'][$size], 3)); ?> ft³</td><td><?php echo (int) $b['n']; ?></td><td><?php echo esc_html(Slab_Data::money($b['price'], 2)); ?></td><td><?php echo esc_html($m($b['yd'])); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">Typical big-box prices; 80-lb bags commonly run <?php echo esc_html(Slab_Data::mr('concrete.bag_price_range', 2, ' to ')); ?>.</div>
</div>
<p>So bagged concrete costs roughly twice as much per yard as ready-mix. It still wins on very small jobs because there is no delivery fee or short-load fee. Below about <?php echo esc_html(Slab_Data::num($dl['bags_win_below_yd'])); ?> yard, bags usually come out ahead; above about <?php echo esc_html(Slab_Data::num($dl['ready_mix_wins_above_yd'])); ?> yards, the truck usually does. Our <a href="<?php echo esc_url(home_url('/ready-mix-vs-bagged-concrete/')); ?>">ready-mix vs. bagged concrete guide</a> has the full break-even table.</p>

<h2>Worked Example: 24x24 Garage Slab at 4,000 PSI</h2>
<div class="formula-box">
<div class="formula-label">24 ft × 24 ft × 5 in, 4,000 PSI</div>
<div class="formula-math"><?php echo esc_html(number_format($g['order_yd'], 2)); ?> yd³ with waste → order <?php echo esc_html(Slab_Data::num($g_order)); ?> yd³ × <?php echo esc_html($mr($p4000)); ?> = <?php echo esc_html($mr($g_conc)); ?></div>
<div class="formula-explanation">Add the <?php echo esc_html($m($fee)); ?> delivery fee and a <?php echo esc_html(Slab_Data::mr('delivery.surcharge', 0, ' to ')); ?> surcharge: <?php echo esc_html($mr($g_total)); ?> delivered. The order fits one <?php echo esc_html(Slab_Data::num($dl['truck_capacity_yd'][1])); ?>-yard truck and clears most plant minimums, so no short-load fee. Free unloading time is about <?php echo esc_html(Slab_Data::range($g_unload)); ?> minutes.</div>
</div>
<p>To see what that means per square foot: one cubic yard covers <?php echo esc_html(Slab_Data::num($cover4)); ?> sq ft at 4 inches, so <?php echo esc_html($m($rm)); ?> concrete is about <?php echo esc_html(Slab_Data::money($rm / $cover4, 2)); ?> per sq ft before waste. Concrete is usually less than a third of an installed slab's price. The <a href="<?php echo esc_url(home_url('/concrete-cost-per-square-foot-pricing-matrix/')); ?>">cost per square foot matrix</a> shows the rest.</p>

<h2>How to Get an Accurate Ready-Mix Quote</h2>
<ol>
<li><strong>Measure and calculate the volume</strong> with waste. The <a href="<?php echo esc_url(home_url('/concrete-yard-calculator/')); ?>">concrete yard calculator</a> does this in seconds, and our guide on <a href="<?php echo esc_url(home_url('/how-to-calculate-concrete-for-a-slab/')); ?>">how to calculate concrete for a slab</a> explains the math.</li>
<li><strong>Specify the mix:</strong> PSI, air entrainment for exterior work in cold climates, slump (how wet), and stone size if you are pumping.</li>
<li><strong>Ask for the full price:</strong> price per yard, delivery fee, minimum load, short-load fee, fuel surcharge, free unloading time and the overtime rate.</li>
<li><strong>Confirm access:</strong> tell the dispatcher how close the truck can get. A loaded truck is heavy and can crack a driveway or sink into a soft lawn.</li>
<li><strong>Get two or three quotes.</strong> Prices and fees vary more between plants than most people expect.</li>
</ol>
<p>Once you have numbers, plug them into the <a href="<?php echo esc_url(home_url('/ready-mix-concrete-calculator/')); ?>">ready-mix concrete calculator</a> or the full <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a>. If a contractor has quoted the whole job, run it through the <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">concrete quote checker</a>.</p>

<div class="callout callout-warning">
<div class="callout-title">Do not let the driver add water to "help"</div>
<p>Extra water on site makes concrete easier to place but lowers its strength and raises shrinkage cracking. If the mix is too stiff, ask for a plasticizer when you order, or agree on the slump in advance.</p>
</div>

<h2>Sources and Method</h2>
<p>PSI prices follow this site's rule: <?php echo esc_html(number_format($c['psi_base'])); ?> PSI at <?php echo esc_html($mr($c['psi_base_price'])); ?> per yard, plus <?php echo esc_html(Slab_Data::mr('concrete.psi_step_price', 0, ' to ')); ?> per <?php echo esc_html(number_format($c['psi_step'])); ?>-PSI step. Delivery, short-load and pump figures are typical US ranges; your local plant sets its own (see our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>). Strength minimums come from the International Residential Code, Table R402.2. Delivery time limits come from ASTM C94, Standard Specification for Ready-Mixed Concrete, and bag mixes are covered by ASTM C387. Cold-weather guidance follows ACI 306R.</p>
<?php
$html = ob_get_clean();

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'How much does a yard of concrete cost in 2026?',
            'a' => 'About ' . $mr($p3000) . ' per cubic yard for standard 3,000 PSI ready-mix, before delivery. 4,000 PSI runs about ' . $mr($p4000) . '. Add a delivery fee (around ' . $m($fee) . '), a fuel surcharge of ' . Slab_Data::mr('delivery.surcharge', 0, ' to ') . ', and a short-load fee of ' . $mr($flat) . ' if you order less than the plant minimum.',
        ],
        [
            'q' => 'What is the minimum order for ready-mix concrete?',
            'a' => 'It depends on the plant. Minimums range from ' . Slab_Data::r('delivery.min_load_yd') . ' cubic yards and are commonly ' . Slab_Data::r('delivery.common_min_yd') . '. You can usually order less, but expect a short-load fee of ' . $mr($flat) . ' flat or ' . Slab_Data::mr('delivery.short_load_per_yd', 0, ' to ') . ' per yard below the minimum.',
        ],
        [
            'q' => 'How many yards of concrete fit in a truck?',
            'a' => 'A standard transit mixer carries ' . Slab_Data::r('delivery.truck_capacity_yd') . ' cubic yards. Larger pours need more than one truck, scheduled back to back.',
        ],
        [
            'q' => 'Is bagged concrete cheaper than ready-mix per yard?',
            'a' => 'No. Bagged concrete in 80-lb bags costs about ' . $m($bag_rows['80']['yd']) . ' per cubic yard, about twice the price of ready-mix. Bags only win on small jobs, roughly under ' . Slab_Data::num($dl['bags_win_below_yd']) . ' yard, because they avoid delivery and short-load fees.',
        ],
        [
            'q' => 'How much more does 4,000 PSI concrete cost than 3,000 PSI?',
            'a' => 'About ' . Slab_Data::money_range(Slab_Data::psi_extra(4000, 1), 0, ' to ') . ' more per cubic yard. On a typical driveway or garage slab that is a small share of the total, and it buys better resistance to freeze-thaw and road salt.',
        ],
    ],
];
