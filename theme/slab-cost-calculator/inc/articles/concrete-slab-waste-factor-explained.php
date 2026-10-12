<?php
/**
 * Article: Concrete Slab Waste Factor Explained (post slug concrete-slab-waste-factor-explained).
 * Loaded by inc/post-articles.php. Every figure comes from Slab_Data.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$sd = Slab_Data::all();
$c = $sd['concrete'];
$dl = $sd['delivery'];
$m = function ($n) {
    return Slab_Data::money($n, 0);
};
$mr = function ($pair) {
    return Slab_Data::money_range($pair, 0, ' to ');
};

$waste = $c['waste_pct'];
$wr = $c['waste_pct_range'];
$rm = $c['ready_mix_default'];
$crew_h = $sd['labor']['crew_hour_default'];

// Depth error as a share of a 4-inch slab.
$quarter_pct = 0.25 / 4 * 100;
$half_pct = 0.5 / 4 * 100;

// Worked example: 20x20x4.
$e = Slab_Data::estimate(20, 20, 4);
$net = $e['net_yd'];
$extra_yd = $e['order_yd'] - $net;
$extra_cost = $extra_yd * $rm;
$short_yd = $net * $quarter_pct / 100;          // a quarter inch low on average
$min_yd = $dl['common_min_yd'][0];
$short_flat = $dl['short_load_flat'];
$short_per = [($min_yd - $short_yd) * $dl['short_load_per_yd'][0], ($min_yd - $short_yd) * $dl['short_load_per_yd'][1]];
$fee_lo = min($short_flat[0], $short_per[0]);
$fee_hi = max($short_flat[1], $short_per[1]);
$topup_concrete = $short_yd * $rm;
$top_up = [$dl['fee_default'] + $fee_lo + $topup_concrete, $dl['fee_default'] + $fee_hi + $topup_concrete];

// Waste on a few sizes at the low, default and high factor.
$sizes = [[10, 10, 4], [12, 12, 4], [20, 20, 4], [24, 24, 5], [20, 40, 5]];
$order_at = function ($net_yd, $pct) {
    return $net_yd * (1 + $pct / 100);
};

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> order about <strong><?php echo (int) $waste; ?>% more concrete</strong> than the math says. The extra covers a subgrade that is a little low, forms that bow out, concrete left in the truck, pump and wheelbarrows, and spills. On a 20x20 slab at 4 inches, <?php echo (int) $waste; ?>% is about <?php echo esc_html(number_format($extra_yd, 2)); ?> yd³, or <?php echo esc_html($m($extra_cost)); ?>. Running short costs far more: a second truck with a delivery fee and short-load fee can run <?php echo esc_html($mr($top_up)); ?>, and a delay can leave a cold joint in your slab.</p>

<p>Every concrete estimate starts with length times width times thickness. That number is the volume of a perfect box. Real slabs are not perfect boxes, and concrete cannot be topped up later the way you can buy one more box of tile. This guide explains where the extra goes, how much to add for your situation, and why being a little long is almost always cheaper than being a little short.</p>

<h2>Where the Extra Concrete Goes</h2>
<ul>
<li><strong>Subgrade that is low.</strong> This is the big one. A slab planned at 4 inches but poured over a base that sits a quarter inch low on average is really 4.25 inches thick, and that quarter inch is <?php echo esc_html(Slab_Data::num($quarter_pct, 2)); ?>% more concrete. Half an inch low is <?php echo esc_html(Slab_Data::num($half_pct, 1)); ?>%. Hand-graded gravel easily varies this much.</li>
<li><strong>Forms that bow.</strong> Wet concrete pushes outward hard. A 2x4 form that bows out between stakes makes the slab wider and uses more concrete. Stakes every <?php echo esc_html(Slab_Data::r('site.stake_spacing_ft')); ?> ft keep it in check.</li>
<li><strong>Concrete left behind.</strong> Some stays in the drum, the chute, the pump hopper and line, the wheelbarrows and the mixer.</li>
<li><strong>Spills and overfill.</strong> Some always ends up outside the forms, and screeding pushes a little off the ends.</li>
<li><strong>Things you did not count.</strong> Thickened edges, a deeper apron, a footing at a post, or a step. Count these separately; they are extra volume, not waste.</li>
<li><strong>Measuring error.</strong> A slab that is out of square or a few inches bigger than planned adds up quickly.</li>
</ul>

<h2>How Much to Add for Your Situation</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Situation</th><th>Suggested waste factor</th><th>Why</th></tr></thead>
<tbody>
<tr><td>Laser-graded base, rigid forms, simple rectangle</td><td><?php echo (int) $wr[0]; ?>%</td><td>Depth is very even; little spills or overfill.</td></tr>
<tr><td>Small pour with bagged concrete</td><td><?php echo (int) $wr[0]; ?> to <?php echo (int) $waste; ?>%</td><td>You can mix one more bag, so keep a few extra on hand and return what you do not open.</td></tr>
<tr><td>Typical residential slab, hand-graded base</td><td><strong><?php echo (int) $waste; ?>%</strong></td><td>The standard allowance used by our calculators.</td></tr>
<tr><td>Concrete placed by pump</td><td><?php echo (int) $waste; ?>% or a bit more</td><td>The pump hopper and line hold concrete that does not end up in the slab.</td></tr>
<tr><td>Uneven or freshly dug subgrade, curves, many corners</td><td><?php echo (int) $waste; ?> to <?php echo (int) $wr[1]; ?>%</td><td>Depth varies; curved forms are harder to fill exactly.</td></tr>
<tr><td>Very rough site or unknown depth</td><td><?php echo (int) $wr[1]; ?>%, or measure the depth at several points first</td><td>Better to find a low area with a string line before the pour than with the last truck.</td></tr>
</tbody>
</table>
</div>
<p>Here is what those factors mean in yards for common slabs. Use the <a href="<?php echo esc_url(home_url('/concrete-waste-calculator/')); ?>">concrete waste calculator</a> for your own numbers.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Slab</th><th>Exact volume</th><th>+<?php echo (int) $wr[0]; ?>%</th><th>+<?php echo (int) $waste; ?>%</th><th>+<?php echo (int) $wr[1]; ?>%</th><th>Cost of the <?php echo (int) $waste; ?>% extra</th></tr></thead>
<tbody>
<?php foreach ($sizes as $z) :
    $nv = $z[0] * $z[1] * $z[2] / 12 / 27;
    ?>
<tr><td><strong><?php echo esc_html($z[0] . ' × ' . $z[1] . ' × ' . $z[2] . ' in'); ?></strong></td><td><?php echo esc_html(number_format($nv, 2)); ?> yd³</td><td><?php echo esc_html(number_format($order_at($nv, $wr[0]), 2)); ?></td><td><?php echo esc_html(number_format($order_at($nv, $waste), 2)); ?></td><td><?php echo esc_html(number_format($order_at($nv, $wr[1]), 2)); ?></td><td><?php echo esc_html($m($nv * $waste / 100 * $rm)); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">Extra cost at <?php echo esc_html(Slab_Data::money($rm)); ?> per cubic yard of ready-mix.</div>
</div>

<h2>Short vs. Over: What Each Mistake Costs</h2>
<p>The two mistakes are not equal. Ordering a little too much costs the price of the extra concrete. Ordering too little brings in a second truck, a second set of fees, and a crew that has to wait while the first batch keeps setting.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th></th><th>Over-order by <?php echo (int) $waste; ?>%</th><th>Run short and call a top-up load</th></tr></thead>
<tbody>
<tr><td><strong>Concrete</strong></td><td><?php echo esc_html(number_format($extra_yd, 2)); ?> yd³ × <?php echo esc_html(Slab_Data::money($rm)); ?> = <?php echo esc_html($m($extra_cost)); ?></td><td><?php echo esc_html(number_format($short_yd, 2)); ?> yd³ × <?php echo esc_html(Slab_Data::money($rm)); ?> = <?php echo esc_html($m($topup_concrete)); ?></td></tr>
<tr><td><strong>Delivery fee</strong></td><td>None (same truck)</td><td><?php echo esc_html($m($dl['fee_default'])); ?></td></tr>
<tr><td><strong>Short-load fee</strong></td><td>None</td><td><?php echo esc_html($mr([$fee_lo, $fee_hi])); ?></td></tr>
<tr><td><strong>Crew waiting</strong></td><td>None</td><td>About <?php echo esc_html($m($crew_h)); ?> per crew-hour of waiting</td></tr>
<tr><td><strong>Risk to the slab</strong></td><td>Leftover concrete to place somewhere</td><td>Cold joint or a visible seam if the first concrete sets before the top-up arrives</td></tr>
<tr><td><strong>Total</strong></td><td><strong><?php echo esc_html($m($extra_cost)); ?></strong></td><td><strong><?php echo esc_html($mr($top_up)); ?></strong> plus waiting time</td></tr>
</tbody>
</table>
<div class="table-caption">20x20 slab, 4 in. Short-load fees vary: often <?php echo esc_html(Slab_Data::mr('delivery.short_load_flat', 0, ' to ')); ?> flat, or <?php echo esc_html(Slab_Data::mr('delivery.short_load_per_yd', 0, ' to ')); ?> for each yard below a minimum of about <?php echo esc_html(Slab_Data::r('delivery.common_min_yd')); ?> yards.</div>
</div>

<h2>Worked Example: a 20x20 Patio</h2>
<div class="formula-box">
<div class="formula-label">20 ft × 20 ft × 4 in, with <?php echo (int) $waste; ?>% waste</div>
<div class="formula-math">20 × 20 × (4 ÷ 12) = <?php echo esc_html(number_format(400 * 4 / 12, 1)); ?> ft³ ÷ 27 = <?php echo esc_html(number_format($net, 2)); ?> yd³ × <?php echo esc_html(number_format(1 + $waste / 100, 2)); ?> = <?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³</div>
<div class="formula-explanation">Most plants sell in quarter or half-yard steps, so round up to the next step they offer. If your exact volume were <?php echo esc_html(number_format($net, 2)); ?> yd³ and the base sat a quarter inch low on average, you would need about <?php echo esc_html(number_format($net + $short_yd, 2)); ?> yd³, which is still inside the <?php echo (int) $waste; ?>% allowance.</div>
</div>
<p>Now the other way. Order the bare <?php echo esc_html(number_format($net, 2)); ?> yd³, find the base a quarter inch low, and you come up about <?php echo esc_html(number_format($short_yd, 2)); ?> yd³ short at the far end of the slab. The fix is a small top-up load at <?php echo esc_html($mr($top_up)); ?> with fees, against the <?php echo esc_html($m($extra_cost)); ?> it would have cost to order the extra up front. The <a href="<?php echo esc_url(home_url('/ready-mix-concrete-calculator/')); ?>">ready-mix calculator</a> and <a href="<?php echo esc_url(home_url('/concrete-yard-calculator/')); ?>">yard calculator</a> add the allowance for you.</p>

<div class="callout callout-tip">
<div class="callout-title">How pros avoid running short</div>
<p>On a multi-truck pour, crews measure what is left to fill before calling for the last load. They check depth with a string line or a tape at several points as they go, then order the final truck to match. Ask the dispatcher whether you can "call the balance" this way. Also have a place ready for leftover concrete: a small form for a step, a pad for a trash can, or a few stepping-stone molds.</p>
</div>

<h2>Cold Joints: the Real Cost of Running Short</h2>
<p>A cold joint forms when fresh concrete is placed against concrete that has already started to set. The two pours do not bond fully, so the seam can show as a line in the finish, open into a crack, or let water in. In a driveway or garage floor it becomes a weak line exactly where nobody planned one.</p>
<ul>
<li><strong>Timing matters.</strong> How long you have depends on the mix and the weather. On hot days the window shrinks fast; above about <?php echo (int) $sd['weather']['hot_f']; ?>°F, hot-weather practice from ACI 305R applies.</li>
<li><strong>If a delay is coming,</strong> stop the pour at a straight line, ideally where a control joint was planned anyway, form a clean edge, and treat it as a construction joint. That looks and performs better than a ragged seam in the middle of a panel.</li>
<li><strong>Do not add water</strong> to the first load to buy time. Extra water weakens concrete and increases shrinkage cracking. ASTM C94, the standard for ready-mix, limits how much water can be added on site and how long a load can stay in the truck.</li>
</ul>

<h2>Waste With Bagged Concrete</h2>
<p>Bags change the math. You can buy a few extra and return the unopened ones to most stores, so a short run costs you only a trip, not a fee. The risk is different: mixing by hand is slow, and a mixer full of concrete that sits too long is wasted. Buy the bag count for the <?php echo (int) $waste; ?>% figure, plus a couple spare. For a 10x10 slab at 4 inches that is <?php echo (int) Slab_Data::estimate(10, 10, 4)['bags80']; ?> bags of 80-lb mix. The <a href="<?php echo esc_url(home_url('/concrete-bags-calculator/')); ?>">bags calculator</a> rounds up for you, and <a href="<?php echo esc_url(home_url('/how-many-bags-of-concrete-do-i-need/')); ?>">how many bags you need</a> explains bag yields.</p>

<h2>Ways to Keep Waste Down</h2>
<ul>
<li><strong>Grade carefully.</strong> A flat, compacted base is the best waste control there is. See the <a href="<?php echo esc_url(home_url('/yard-prep-checklist-for-a-slab-pour/')); ?>">yard prep checklist</a>.</li>
<li><strong>Brace the forms</strong> with stakes close together and kickers at corners.</li>
<li><strong>Measure the finished forms,</strong> not the plan, before you order.</li>
<li><strong>Count extras separately:</strong> thickened edges, footings and steps.</li>
<li><strong>Plan truck access</strong> so the chute reaches the forms (about <?php echo esc_html(Slab_Data::r('site.chute_reach_ft')); ?> ft) and you are not wheeling concrete across the yard.</li>
</ul>
<p>To see how waste fits into the whole budget, run your slab through the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a>, which adds the <?php echo (int) $waste; ?>% allowance by default.</p>

<h2>Sources and Method</h2>
<p>Volumes use the standard formula (length × width × thickness ÷ 27). The <?php echo (int) $waste; ?>% allowance is common trade practice for residential flatwork and matches our calculators. Ready-mix delivery and water limits are covered by ASTM C94; hot-weather practice by ACI 305R; slab construction by ACI 302.1R. Delivery and short-load fees are this site's working benchmarks (see our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>) and vary by supplier and region.</p>
<?php
$html = ob_get_clean();

$e24 = Slab_Data::estimate(24, 24, 5);

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'Why add ' . $waste . '% waste to a concrete order?',
            'a' => 'The ' . $waste . '% covers a subgrade that is a little low, forms that bow out, concrete left in the truck, pump and wheelbarrows, and spills. A base that is just a quarter inch low under a 4-inch slab uses ' . Slab_Data::num($quarter_pct, 2) . '% more concrete on its own.',
        ],
        [
            'q' => 'How much concrete should I order for a 20x20 slab?',
            'a' => 'A 20x20 slab at 4 inches is ' . number_format($net, 2) . ' cubic yards exactly. With ' . $waste . '% waste, order ' . number_format($e['order_yd'], 2) . ' yd³, rounded up to the next quarter or half yard your supplier sells.',
        ],
        [
            'q' => 'Is ' . $wr[0] . '% waste enough for concrete?',
            'a' => 'Only on a very flat, laser-graded base with rigid forms and a simple shape. For a typical hand-graded residential slab use ' . $waste . '%, and up to ' . $wr[1] . '% on uneven ground or curved forms.',
        ],
        [
            'q' => 'What happens if I order too little concrete?',
            'a' => 'You need a second truck, which usually means another delivery fee (about ' . $m($dl['fee_default']) . ') plus a short-load fee of ' . Slab_Data::mr('delivery.short_load_flat', 0, ' to ') . ' or more, while your crew waits. If the first concrete starts to set before the top-up arrives, you get a cold joint.',
        ],
        [
            'q' => 'How much extra concrete is ' . $waste . '% on a 24x24 garage slab?',
            'a' => 'A 24x24 slab at 5 inches is ' . number_format($e24['net_yd'], 2) . ' cubic yards exactly. Adding ' . $waste . '% brings the order to ' . number_format($e24['order_yd'], 2) . ' yd³, and the extra costs about ' . $m(($e24['order_yd'] - $e24['net_yd']) * $rm) . ' at ' . Slab_Data::money($rm) . ' per yard.',
        ],
    ],
];
