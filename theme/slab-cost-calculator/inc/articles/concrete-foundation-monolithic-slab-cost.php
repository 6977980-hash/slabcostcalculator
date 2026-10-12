<?php
/**
 * Article: Concrete Foundation & Monolithic Slab Cost (post slug concrete-foundation-monolithic-slab-cost).
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
$rm = $sd['concrete']['ready_mix_default'];
$mono = $sd['monolithic'];
$code = $sd['code'];
$found_psf = Slab_Data::foundation_psf();
$ed = $mono['edge_depth_in'];

// Same method as Slab_Data::foundation_psf(), applied to any footprint (4 in floor).
$fnd = function ($l, $w) use ($rm, $op, $ed) {
    $c = Slab_Data::size_costs($l, $w, 4);
    $a = $l * $w;
    $e12 = Slab_Data::edge_yd($l, $w, 4, $ed[0]);
    $e18 = Slab_Data::edge_yd($l, $w, 4, $ed[1]);
    return [
        'c' => $c,
        'e12' => $e12,
        'e18' => $e18,
        'yd' => [$c['est']['order_yd'] + $e12, $c['est']['order_yd'] + $e18],
        'total' => Slab_Data::round_pair([$c['installed_psf'][0] * $a + $e12 * $rm, $c['installed_psf'][1] * $a + $e18 * $rm * $op], 100),
    ];
};
$houses = [
    [30, 40, '1,200 sq ft ranch or large garage/shop'],
    [30, 50, '1,500 sq ft single-story home'],
    [40, 50, '2,000 sq ft single-story home'],
];
$rows = [];
foreach ($houses as $h) {
    $rows[] = ['l' => $h[0], 'w' => $h[1], 'use' => $h[2]] + $fnd($h[0], $h[1]);
}
$ex = $rows[0];
$ring = 30 * 40 - (30 - 2 * $mono['edge_width_in'] / 12) * (40 - 2 * $mono['edge_width_in'] / 12);
$perim = 140;
$prep = Slab_Data::site_prep_psf();
$truck = $sd['delivery']['truck_capacity_yd'];
$trucks = (int) ceil($ex['yd'][1] / $truck[1]);
$trucks_lo = (int) ceil($ex['yd'][0] / $truck[1]);

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> a monolithic slab foundation costs about <strong><?php echo esc_html(Slab_Data::money_range($found_psf, 0, ' to ')); ?> per sq ft</strong> for the concrete work: a 4-inch floor with a thickened perimeter footing <?php echo (int) $mono['edge_width_in']; ?> inches wide and <?php echo esc_html(Slab_Data::r('monolithic.edge_depth_in')); ?> inches deep. A 30x40 (1,200 sq ft) foundation runs about <strong><?php echo esc_html($mr($ex['total'])); ?></strong>. Site work, plumbing rough-in, engineering and permits are extra, and the design must meet your local code.</p>

<p>A monolithic slab is the simplest and usually cheapest way to put a house, garage or shop on concrete. The floor and footing go in as one pour, so there is one set of forms, one concrete delivery day and fewer inspections. That simplicity is a big reason slab foundations are so common in the South and Southwest. In cold climates, or on poor soil, the design gets more involved, and that is where an engineer and the building department come in.</p>

<div class="callout callout-warning">
<div class="callout-title">This is structural work</div>
<p>A house foundation has to meet the building code where you live and pass inspection, and many jurisdictions require stamped engineering for slab foundations, especially on expansive clay, fill or slopes. Use the figures here for budgeting only. Your footing size, rebar, concrete strength and frost protection come from the plans, not from a cost guide.</p>
</div>

<h2>Monolithic Slab Foundation Cost by House Size</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Footprint</th><th>Typical use</th><th>Concrete with edge (+<?php echo (int) $sd['concrete']['waste_pct']; ?>%)</th><th>Foundation concrete work</th></tr></thead>
<tbody>
<?php foreach ($rows as $r) : ?>
<tr><td><strong><?php echo (int) $r['l']; ?> × <?php echo (int) $r['w']; ?> ft</strong></td><td><?php echo esc_html($r['use']); ?></td><td><?php echo esc_html(number_format($r['yd'][0], 1)); ?> to <?php echo esc_html(number_format($r['yd'][1], 1)); ?> yd³</td><td><?php echo esc_html($mr($r['total'])); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">Low end: plain 4-inch slab (basic scope) plus a <?php echo (int) $ed[0]; ?>-inch-deep edge in ready-mix at <?php echo esc_html(Slab_Data::money($rm)); ?>/yd³. High end: the calculator's full scope (vapor barrier, #4 rebar at <?php echo (int) $sd['reinforcement']['rebar_base_spacing_in']; ?> in) plus an <?php echo (int) $ed[1]; ?>-inch edge, with <?php echo (int) round($sd['overhead_profit'] * 100); ?>% overhead and profit. Excavation, grading and utilities are not included.</div>
</div>
<p>Real homes are rarely simple rectangles. Every corner, bump-out and garage step adds forming and edge length, and interior load-bearing walls usually need their own thickened footings under the slab. Expect an L-shaped or complex footprint to land toward the top of the range or above it. For a quick number on your own footprint, start with the <a href="<?php echo esc_url(home_url('/foundation/')); ?>">foundation cost page</a> or the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a>.</p>

<h2>How a Monolithic Slab Is Built</h2>
<p>The floor is usually 4 inches thick (the IRC minimum is 3.5 inches). Around the outside, the concrete turns down into a beam about <?php echo (int) $mono['edge_width_in']; ?> inches wide and <?php echo esc_html(Slab_Data::r('monolithic.edge_depth_in')); ?> inches deep, with <?php echo (int) $mono['edge_bars']; ?> or more continuous #4 bars near the bottom. Underneath sits a 4-inch compacted base (IRC R506.2.2) and a 6-mil vapor retarder (IRC R506.2.3). Anchor bolts or straps for the wall plates are set in the wet edge.</p>
<div class="formula-box">
<div class="formula-label">Edge concrete for a 30x40 slab</div>
<div class="formula-math">30 × 40 − 28 × 38 = <?php echo esc_html(Slab_Data::num($ring)); ?> sq ft of edge ring × (<?php echo (int) $ed[0]; ?> − 4) in ÷ 12 ÷ 27 × <?php echo esc_html(Slab_Data::num(1 + $sd['concrete']['waste_pct'] / 100, 2)); ?> = <?php echo esc_html(number_format($ex['e12'], 2)); ?> yd³</div>
<div class="formula-explanation">The edge ring is the outer foot of the slab. Only the depth below the 4-inch floor is extra. At <?php echo (int) $ed[1]; ?> inches deep the edge adds <?php echo esc_html(number_format($ex['e18'], 2)); ?> yd³. Add that to the floor's <?php echo esc_html(number_format($ex['c']['est']['order_yd'], 2)); ?> yd³ for the order. The <a href="<?php echo esc_url(home_url('/concrete-footing-calculator/')); ?>">footing calculator</a> runs this for other sizes.</div>
</div>
<p>At <?php echo esc_html(number_format($ex['yd'][0], 1)); ?> to <?php echo esc_html(number_format($ex['yd'][1], 1)); ?> cubic yards, even a 30x40 needs <?php echo (int) $trucks_lo; ?> or <?php echo (int) $trucks; ?> mixer trucks (each carries about <?php echo esc_html(Slab_Data::range($truck)); ?> yd³), timed so the pour stays continuous. If trucks cannot reach every side, a boom pump adds about <?php echo esc_html(Slab_Data::mr('pump.boom_job', 0, ' to ')); ?> for the day.</p>

<h2>Concrete Strength</h2>
<p>IRC Table R402.2 sets <?php echo esc_html(number_format($code['psi_protected'])); ?> PSI as the minimum for foundations and interior slabs not exposed to weather. Many plans call for <?php echo esc_html(number_format($code['psi_exposed'])); ?> PSI or more for better durability, and an attached garage floor in a severe-weathering area needs <?php echo esc_html(number_format($code['psi_severe'])); ?> PSI air-entrained concrete. Stronger mixes cost about <?php echo esc_html(Slab_Data::mr('concrete.psi_step_price', 0, ' to ')); ?> more per cubic yard for each <?php echo esc_html(number_format($sd['concrete']['psi_step'])); ?>-PSI step, which is small next to the total. Engineered foundations may specify higher strengths; follow the plans.</p>

<h2>Frost Depth and Frost-Protected Options</h2>
<p>IRC R403.1.4 requires footings to extend below the frost line unless they are protected from frost. A <?php echo esc_html(Slab_Data::r('monolithic.edge_depth_in')); ?>-inch turn-down meets that only where frost is shallow. In the northern US, frost depth is often <?php echo esc_html(Slab_Data::r('freeze_thaw_ft')); ?> inches or more, which leaves two common choices:</p>
<ul>
<li><strong>Stem wall (frost wall) foundation:</strong> footings at frost depth, a short concrete or block wall, then a slab poured inside. It costs much more than a turn-down edge; our <a href="<?php echo esc_url(home_url('/how-much-does-a-garage-slab-cost/')); ?>">garage slab guide</a> shows a full build-up of that cost.</li>
<li><strong>Frost-protected shallow foundation (FPSF):</strong> IRC R403.3 allows a shallow monolithic slab where rigid foam insulation, placed vertically along the edge and horizontally out from the corners, keeps the soil under the footing from freezing. It suits heated buildings and saves deep digging, but the insulation layout must follow the code tables for your climate.</li>
</ul>

<h2>What the Price Includes, and What It Does Not</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Usually included in a concrete foundation bid</th><th>Usually priced separately</th></tr></thead>
<tbody>
<tr><td>Layout, forms and trenching for the edge</td><td>Clearing, excavation, grading and fill (about <?php echo esc_html(Slab_Data::money_range($prep, 2, ' to ')); ?> per sq ft for basic excavation and a 4-inch base)</td></tr>
<tr><td>Concrete, delivery and placing</td><td>Under-slab plumbing rough-in and its inspection, by a licensed plumber</td></tr>
<tr><td>Rebar in the edge and the field</td><td>Electrical conduit or radiant heat tubing in the slab</td></tr>
<tr><td>Vapor retarder</td><td>Soils report and engineering</td></tr>
<tr><td>Finishing and curing</td><td>Termite pretreatment and radon piping where required</td></tr>
<tr><td>Anchor bolts or hold-downs set in the pour</td><td>Under-slab or edge insulation, building permit and surveys</td></tr>
</tbody>
</table>
</div>
<p>Plumbing deserves special attention. Drain and supply lines go in, get tested and get inspected before the slab is poured, and fixing a mistake later means cutting concrete. Ask each bidder exactly where their scope stops. The <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">concrete quote checker</a> helps compare the slab portion of bids side by side.</p>

<h2>Slab vs. Crawl Space</h2>
<p>A slab is usually cheaper and faster than a crawl space, has no space underneath for moisture or pests, and suits warm climates and level lots. A crawl space costs more but gives access to plumbing and ducts, lifts the floor above flood or wet ground, and handles sloped lots better. Our <a href="<?php echo esc_url(home_url('/slab-vs-crawl-space-foundation/')); ?>">slab vs. crawl space comparison</a> covers cost, climate, moisture and resale.</p>

<h2>What Pushes the Cost Up</h2>
<ul>
<li><strong>Expansive clay or poor soil:</strong> may require a soils report, deeper or wider footings, a post-tensioned slab or a structural slab designed by an engineer (IRC R403.1.8 covers expansive soils).</li>
<li><strong>Sloped lots:</strong> need fill, retaining or a deeper edge on the low side.</li>
<li><strong>Cold climate:</strong> stem walls or FPSF insulation, as above.</li>
<li><strong>Complex footprint:</strong> more corners and edge length per square foot.</li>
<li><strong>Thicker floor:</strong> 5 or 6 inches in a shop or garage bay adds concrete but little labor.</li>
</ul>

<h2>Sources and Method</h2>
<p>Foundation figures add the thickened-edge concrete to our standard slab estimate, the same way the calculator does (see our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>); prices vary by region, soil and access. Code references: IRC R403 (footings, including R403.1.4 frost protection, R403.1.8 expansive soils and R403.3 frost-protected shallow foundations), IRC R506 (slabs on ground) and IRC Table R402.2 (concrete strength), with ACI 332 (residential concrete) and ACI 318 (structural concrete) for engineered designs. Always build to stamped plans or your local building department's requirements.</p>
<?php
$html = ob_get_clean();

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'How much does a monolithic slab foundation cost per square foot?',
            'a' => 'About ' . Slab_Data::money_range($found_psf, 0, ' to ') . ' per sq ft for the concrete work on a simple rectangular footprint, including a 4-inch floor and a thickened edge ' . Slab_Data::r('monolithic.edge_depth_in') . ' inches deep. Excavation, plumbing rough-in, engineering and permits are extra.',
        ],
        [
            'q' => 'How much does a slab foundation cost for a 1,500 sq ft house?',
            'a' => 'For a simple 30x50 ft single-story footprint, the concrete foundation work runs about ' . $mr($rows[1]['total']) . '. Site work, plumbing, engineering and permits are extra, and complex footprints cost more.',
        ],
        [
            'q' => 'How deep is the footing on a monolithic slab?',
            'a' => 'The thickened edge is typically ' . $mono['edge_width_in'] . ' inches wide and ' . Slab_Data::r('monolithic.edge_depth_in') . ' inches deep. In cold climates the footing must reach below the local frost line, which can be ' . Slab_Data::r('freeze_thaw_ft') . ' inches or more, unless the slab is a frost-protected shallow foundation with insulation.',
        ],
        [
            'q' => 'What PSI concrete is used for a house slab foundation?',
            'a' => 'IRC Table R402.2 sets a minimum of ' . number_format($code['psi_protected']) . ' PSI for foundations and interior slabs not exposed to weather. Many plans specify ' . number_format($code['psi_exposed']) . ' PSI or more, and an attached garage floor in a severe-weathering area needs ' . number_format($code['psi_severe']) . ' PSI air-entrained concrete.',
        ],
        [
            'q' => 'Do I need an engineer for a slab foundation?',
            'a' => 'Often, yes. Many building departments require engineered plans for house foundations, and an engineer is essential on expansive clay, fill, slopes or in high-wind and seismic areas. Even where prescriptive code designs are allowed, the foundation must pass inspection.',
        ],
    ],
];
