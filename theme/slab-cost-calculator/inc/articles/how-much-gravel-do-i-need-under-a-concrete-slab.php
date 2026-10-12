<?php
/**
 * Article: How Much Gravel Do I Need Under a Concrete Slab? (post slug how-much-gravel-do-i-need-under-a-concrete-slab).
 * Loaded by inc/post-articles.php. Every figure comes from Slab_Data.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$sd = Slab_Data::all();
$s = $sd['site'];
$m = function ($n) {
    return Slab_Data::money($n, 0);
};
$mr = function ($pair) {
    return Slab_Data::money_range($pair, 0, ' to ');
};

$comp = $s['gravel_compaction'];
$tpy = $s['gravel_tons_per_yd'];
$comp_pct = (int) round(($comp - 1) * 100);

/** Gravel to order (loose yd³, with compaction) and tons for an area and compacted depth. */
$gravel = function ($area, $depth) use ($comp, $tpy) {
    $yd = $area * $depth / 12 / 27 * $comp;
    return ['yd' => $yd, 'tons' => $yd * $tpy];
};

$sizes = [
    [10, 10, '10 × 10 (shed, AC pad)'],
    [12, 12, '12 × 12 (patio, shed)'],
    [16, 24, '16 × 24 (large patio)'],
    [20, 20, '20 × 20 (patio, 2-car pad)'],
    [24, 24, '24 × 24 (2-car garage)'],
    [20, 40, '20 × 40 (driveway)'],
    [30, 40, '30 × 40 (shop)'],
];

// Worked example: 20x20 at 4 in.
$ex = $gravel(400, 4);
$ex_cuft = 400 * 4 / 12;
$ex6 = $gravel(400, 6);
$ex_cost_yd = [$ex['yd'] * $s['gravel_range'][0], $ex['yd'] * $s['gravel_range'][1]];
$ex_cost_ton = [$ex['tons'] * $s['gravel_ton_range'][0], $ex['tons'] * $s['gravel_ton_range'][1]];
$ex_exc = [400 * $s['excavation_range'][0], 400 * $s['excavation_range'][1]];
$prep = Slab_Data::site_prep_psf();
$lift = $s['gravel_lift_in'];

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> most slabs need a <strong>4-inch compacted gravel base</strong>, which takes about <strong><?php echo esc_html(number_format(100 * 4 / 12 / 27 * $comp, 2)); ?> cubic yards of crushed stone per 100 sq ft</strong> (<?php echo esc_html(number_format(100 * 4 / 12 / 27 * $comp * $tpy, 2)); ?> tons), including <?php echo (int) $comp_pct; ?>% extra for compaction. A 20x20 slab needs about <?php echo esc_html(number_format($ex['yd'], 1)); ?> yd³ (<?php echo esc_html(number_format($ex['tons'], 1)); ?> tons) at 4 inches, or <?php echo esc_html(number_format($ex6['yd'], 1)); ?> yd³ at 6 inches. At <?php echo esc_html(Slab_Data::mr('site.gravel_range', 0, ' to ')); ?> per yard, the 4-inch base costs roughly <?php echo esc_html($mr($ex_cost_yd)); ?> in stone, plus delivery.</p>

<p>The gravel base is the cheapest layer of a slab and one of the most important. It gives the concrete a flat, even surface of uniform support, it drains water away from the underside of the slab, and it keeps fine soil from pumping up through cracks. This guide shows how much to order for common slab sizes, which stone to ask for, how to compact it, and when you can use sand or skip the base.</p>

<h2>Gravel Needed for Common Slab Sizes</h2>
<p>Quantities below are the amount to order: compacted depth, plus <?php echo (int) $comp_pct; ?>% because loose stone settles when it is compacted. Tons use about <?php echo esc_html(Slab_Data::num($tpy)); ?> tons per cubic yard for crushed stone; ask your supplier for their own figure, since it changes with the stone and how wet it is.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Slab size</th><th>Area</th><th>4 in base: yd³</th><th>4 in base: tons</th><th>6 in base: yd³</th><th>6 in base: tons</th></tr></thead>
<tbody>
<?php foreach ($sizes as $z) :
    $a = $z[0] * $z[1];
    $g4 = $gravel($a, 4);
    $g6 = $gravel($a, 6);
    ?>
<tr><td><strong><?php echo esc_html($z[2]); ?></strong></td><td><?php echo esc_html(number_format($a)); ?> sq ft</td><td><?php echo esc_html(number_format($g4['yd'], 1)); ?></td><td><?php echo esc_html(number_format($g4['tons'], 1)); ?></td><td><?php echo esc_html(number_format($g6['yd'], 1)); ?></td><td><?php echo esc_html(number_format($g6['tons'], 1)); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">Includes <?php echo (int) $comp_pct; ?>% for compaction. Add a little more if the excavation is uneven or the base runs past the forms.</div>
</div>
<p>Have a different size or depth? The <a href="<?php echo esc_url(home_url('/gravel-base-calculator/')); ?>">gravel base calculator</a> does the same math for any shape, and the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a> adds the base to the full project price.</p>

<h2>How to Calculate It Yourself</h2>
<div class="formula-box">
<div class="formula-label">20 ft × 20 ft slab, 4 in compacted base</div>
<div class="formula-math">20 × 20 × (4 ÷ 12) = <?php echo esc_html(number_format($ex_cuft, 1)); ?> ft³ ÷ 27 = <?php echo esc_html(number_format($ex_cuft / 27, 2)); ?> yd³ × <?php echo esc_html(Slab_Data::num($comp, 2)); ?> = <?php echo esc_html(number_format($ex['yd'], 2)); ?> yd³</div>
<div class="formula-explanation">Multiply by <?php echo esc_html(Slab_Data::num($tpy)); ?> for tons: <?php echo esc_html(number_format($ex['yd'], 2)); ?> × <?php echo esc_html(Slab_Data::num($tpy)); ?> = <?php echo esc_html(number_format($ex['tons'], 1)); ?> tons. Round up to what your supplier sells (often whole or half yards or tons).</div>
</div>
<p>The <?php echo esc_html(Slab_Data::num($comp, 2)); ?> factor matters. Order only the geometric volume and a 4-inch base will come out closer to <?php echo esc_html(Slab_Data::num(4 / $comp, 1)); ?> inches after compaction, which is thinner than the IRC's 4-inch base course and leaves your slab thicker (and more expensive) in the low spots.</p>

<h2>What It Costs</h2>
<p>Crushed stone is sold by the cubic yard or by the ton. Local quarry prices vary a lot, and delivery is often a separate charge with a minimum load, so get a delivered price. Using <?php echo esc_html(Slab_Data::mr('site.gravel_range', 0, ' to ')); ?> per yard or <?php echo esc_html(Slab_Data::mr('site.gravel_ton_range', 0, ' to ')); ?> per ton:</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Slab size</th><th>4 in base (priced by yard)</th><th>4 in base (priced by ton)</th><th>6 in base (priced by yard)</th></tr></thead>
<tbody>
<?php foreach ($sizes as $z) :
    $a = $z[0] * $z[1];
    $g4 = $gravel($a, 4);
    $g6 = $gravel($a, 6);
    ?>
<tr><td><strong><?php echo esc_html($z[0] . ' × ' . $z[1]); ?></strong></td><td><?php echo esc_html($mr([$g4['yd'] * $s['gravel_range'][0], $g4['yd'] * $s['gravel_range'][1]])); ?></td><td><?php echo esc_html($mr([$g4['tons'] * $s['gravel_ton_range'][0], $g4['tons'] * $s['gravel_ton_range'][1]])); ?></td><td><?php echo esc_html($mr([$g6['yd'] * $s['gravel_range'][0], $g6['yd'] * $s['gravel_range'][1]])); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">Stone only. Delivery minimums, spreading and compaction labor are extra.</div>
</div>
<p>Digging out sod and soil to make room for the base usually costs more than the stone. Our calculator uses <?php echo esc_html(Slab_Data::mr('site.excavation_range', 2, ' to ')); ?> per sq ft for excavation, which is <?php echo esc_html($mr($ex_exc)); ?> on a 20x20. Excavation plus a 4-inch base together come to about <?php echo esc_html(Slab_Data::money_range($prep, 2, ' to ')); ?> per sq ft.</p>

<h2>Which Gravel to Use</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Material</th><th>What it is</th><th>Good for</th><th>Watch out for</th></tr></thead>
<tbody>
<tr><td><strong>Crusher run / dense-graded base</strong> (also called 3/4-minus, road base, ABC, or DGA)</td><td>Crushed stone from about 3/4 in down to dust</td><td>The most common slab base. The fines fill the gaps, so it compacts into a hard, flat layer.</td><td>Drains slower than clean stone. Do not leave it soaked before the pour.</td></tr>
<tr><td><strong>#57 clean stone</strong></td><td>Washed, angular stone about 1/2 to 1 in, no fines</td><td>Wet sites and under interior floors where you want a drainage layer.</td><td>Hard to screed perfectly flat. Many crews top it with a thin layer of fines or use a dense-graded base instead.</td></tr>
<tr><td><strong>Clean sand</strong></td><td>Coarse, graded sand</td><td>Allowed by the IRC as a base course. Easy to grade flat.</td><td>Washes out if water runs under the slab edge. Needs to be kept damp and compacted.</td></tr>
<tr><td><strong>Pea gravel</strong></td><td>Small rounded stone</td><td>Drainage around foundations, not under slabs.</td><td>The round stones roll instead of locking together, so it never compacts firmly. Avoid it as a slab base.</td></tr>
</tbody>
</table>
</div>
<p>Names vary by region, so describe what you want: "crushed, angular stone that compacts" for a dense base, or "washed, crushed stone with no fines" for a drainage layer. The IRC limits base course material to stone that passes a 2-inch sieve.</p>

<h2>4 Inches or 6 Inches?</h2>
<ul>
<li><strong>4 inches</strong> is the IRC base course for floors and the normal choice for patios, sheds and garage floors on reasonably firm soil.</li>
<li><strong>6 inches</strong> makes sense for driveways and RV pads, on clay or soft soil, and where you need to raise the slab or level a sloping site. The extra 2 inches costs about <?php echo esc_html($mr([($ex6['yd'] - $ex['yd']) * $s['gravel_range'][0], ($ex6['yd'] - $ex['yd']) * $s['gravel_range'][1]])); ?> in stone on a 20x20.</li>
<li><strong>More than 6 inches</strong> of fill under a slab should be placed and compacted in layers, and the IRC limits how deep loose fill can go (R506.2.1: no more than 24 inches of clean sand or gravel, or 8 inches of earth, unless it is compacted to engineered standards).</li>
</ul>
<p>Your slab thickness and base depth add up to the dig depth. A 4-inch slab on a 4-inch base needs about 8 inches of excavation below the finished top. See <a href="<?php echo esc_url(home_url('/how-thick-should-a-concrete-slab-be/')); ?>">how thick a slab should be</a> to set the first number.</p>

<h2>Compacting the Base in Lifts</h2>
<p>Gravel only works if it is compacted. Loose stone settles under the weight of wet concrete and later under use, and the slab cracks where it loses support.</p>
<ol>
<li><strong>Compact the subgrade first.</strong> Run a plate compactor over the bare soil until it stops leaving a mark. Dig out and replace any soft, spongy spots.</li>
<li><strong>Spread in lifts.</strong> A walk-behind plate compactor works through about <?php echo esc_html(Slab_Data::range($lift)); ?> inches of loose stone at a time. A 4-inch base can usually go down in one lift; a 6-inch or deeper base should go down in two.</li>
<li><strong>Dampen dense-graded stone</strong> before compacting. Slightly moist stone packs much tighter than bone-dry stone. Do not flood it.</li>
<li><strong>Make several passes</strong> in overlapping rows, then cross the area at a right angle. When you can walk on it without leaving footprints, it is ready.</li>
<li><strong>Check the grade</strong> with a string line or a screed board on the forms. High spots make the slab thin; low spots waste concrete.</li>
</ol>
<p>Our <a href="<?php echo esc_url(home_url('/yard-prep-checklist-for-a-slab-pour/')); ?>">yard prep checklist</a> covers the full order of work, from the 811 call to setting forms.</p>

<div class="callout callout-tip">
<div class="callout-title">Vapor barrier goes on top of the base</div>
<p>For a slab under a house, garage or heated shop, the IRC calls for a 6-mil polyethylene vapor retarder between the base and the concrete (R506.2.3), with joints lapped at least 6 inches. Lay it over the compacted base so stone does not puncture it, and patch any tears before the pour. Outdoor patios and driveways usually do not need one.</p>
</div>

<h2>When Sand or No Base Is OK</h2>
<p>The IRC requires a 4-inch base course of clean graded sand, gravel or crushed stone under floor slabs, with one exception: no base is required where the slab sits on well-drained or sand-gravel soils (Group I soils in the code's soil classification table, R405.1). In practice:</p>
<ul>
<li><strong>Sandy, gravelly native soil</strong> that drains well and has not been disturbed can take a slab directly once it is compacted. Many patios and walks in sandy regions are poured this way.</li>
<li><strong>Clay, silt, or wet ground</strong> needs a base. These soils hold water, swell, and can heave when they freeze.</li>
<li><strong>Sand as the base</strong> is acceptable under the code and grades flat easily, but it can wash out at the edges. It works best inside a perimeter footing or on a slab with good drainage away from the edges.</li>
<li><strong>Driveways and garage floors</strong> almost always benefit from a gravel base, even on decent soil, because vehicle loads and freeze-thaw cycles are hard on any weak spot.</li>
</ul>
<p>Local code can be stricter than the IRC, and an inspector may check base depth before the pour. See <a href="<?php echo esc_url(home_url('/concrete-slab-permits-and-costs/')); ?>">slab permits and costs</a>.</p>

<h2>DIY or Hire It Out?</h2>
<p>Base work is the most DIY-friendly part of a slab project. A plate compactor rents by the day, the stone can be dumped close to the site, and mistakes are easy to fix before the concrete arrives. The hard part is moving the stone: <?php echo esc_html(number_format($ex['tons'], 1)); ?> tons for a 20x20 is a lot of wheelbarrow trips. Many homeowners do the excavation and base themselves and hire a crew only for the pour and finish, which the <a href="<?php echo esc_url(home_url('/diy-concrete-slab-what-you-can-and-shouldnt-do/')); ?>">DIY vs. contractor guide</a> discusses.</p>

<h2>Sources and Method</h2>
<p>Base requirements are from the International Residential Code: R506.2.1 (fill), R506.2.2 (base course and its exception for well-drained soils), R506.2.3 (vapor retarder) and R405.1 (soil classification). Base preparation guidance follows ACI 302.1R and the Portland Cement Association's guidance on subgrades and subbases. Quantities use the standard volume formula with a <?php echo esc_html(Slab_Data::num($comp, 2)); ?> compaction factor and about <?php echo esc_html(Slab_Data::num($tpy)); ?> tons per cubic yard; prices are this site's working benchmarks (see our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>) and vary by region.</p>
<?php
$html = ob_get_clean();

$g10 = $gravel(100, 4);
$g24 = $gravel(576, 4);

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'How much gravel do I need under a concrete slab?',
            'a' => 'For a 4-inch compacted base, about ' . number_format($g10['yd'], 2) . ' cubic yards (' . number_format($g10['tons'], 2) . ' tons) per 100 sq ft, including ' . $comp_pct . '% for compaction. A 20x20 slab needs about ' . number_format($ex['yd'], 1) . ' yd³ and a 24x24 about ' . number_format($g24['yd'], 1) . ' yd³.',
        ],
        [
            'q' => 'How many tons of gravel do I need for a 20x20 slab?',
            'a' => 'About ' . number_format($ex['tons'], 1) . ' tons for a 4-inch compacted base, or ' . number_format($ex6['tons'], 1) . ' tons for 6 inches, using about ' . Slab_Data::num($tpy) . ' tons per cubic yard of crushed stone.',
        ],
        [
            'q' => 'What is the best gravel to put under a concrete slab?',
            'a' => 'A dense-graded crushed stone (crusher run, 3/4-minus or road base) is the most common choice because the fines help it compact into a firm, flat layer. Washed #57 stone drains better and suits wet sites. Avoid rounded pea gravel, which does not compact.',
        ],
        [
            'q' => 'Can you pour concrete without a gravel base?',
            'a' => 'Sometimes. The IRC requires a 4-inch base course under floor slabs but makes an exception for slabs on well-drained or sand-gravel soils. On clay, silt or wet ground, or under a driveway, use a compacted gravel base.',
        ],
        [
            'q' => 'How much does gravel for a slab base cost?',
            'a' => 'Crushed stone runs about ' . Slab_Data::mr('site.gravel_range', 0, ' to ') . ' per cubic yard or ' . Slab_Data::mr('site.gravel_ton_range', 0, ' to ') . ' per ton. A 4-inch base for a 20x20 slab costs roughly ' . $mr($ex_cost_yd) . ' in stone, plus delivery.',
        ],
    ],
];
