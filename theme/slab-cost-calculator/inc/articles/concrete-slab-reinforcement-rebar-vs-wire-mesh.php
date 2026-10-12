<?php
/**
 * Article: Concrete Slab Reinforcement, Rebar Grid vs. Welded Wire Mesh
 * (post slug concrete-slab-reinforcement-rebar-vs-wire-mesh).
 * Loaded by inc/post-articles.php. Every figure comes from Slab_Data.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$sd = Slab_Data::all();
$r = $sd['reinforcement'];
$j = $sd['joints'];
$m = function ($n) {
    return Slab_Data::money($n, 0);
};
$mr = function ($pair) {
    return Slab_Data::money_range($pair, 0, ' to ');
};

$sp = $r['rebar_base_spacing_in'];
$tight = $r['rebar_tight_spacing_in'];
$sizes = [[10, 10], [12, 12], [20, 20], [24, 24], [20, 40], [30, 40]];

// 20x20 example.
$g18 = Slab_Data::rebar_grid(20, 20, $sp);
$g12 = Slab_Data::rebar_grid(20, 20, $tight);
$more_12 = ($g12['lin_ft'] / $g18['lin_ft'] - 1) * 100;
$rebar_20 = [400 * $r['rebar_range'][0], 400 * $r['rebar_range'][1]];
$mesh_20 = [400 * $r['mesh_range'][0], 400 * $r['mesh_range'][1]];
$diff_20 = 400 * ($r['rebar_default'] - $r['mesh_default']);
$c20 = Slab_Data::size_costs(20, 20, 4);
$diff_pct = $diff_20 / $c20['est']['direct'] * 100;
$lap_ft = $r['lap_in_no4'] / 12;

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> use a <strong>#4 rebar grid</strong> (on <?php echo (int) $sp; ?>-inch centers, set on chairs) for driveways, garage floors and any slab that carries vehicles. It costs about <?php echo esc_html(Slab_Data::mr('reinforcement.rebar_range', 2, ' to ')); ?> per sq ft installed. <strong>Welded wire mesh</strong> at <?php echo esc_html(Slab_Data::mr('reinforcement.mesh_range', 2, ' to ')); ?> per sq ft is fine for patios, walkways and shed floors, as long as it is held up in the slab and not left on the ground. Neither one stops a slab from cracking: control joints decide <em>where</em> it cracks, and reinforcement keeps those cracks tight.</p>

<p>Reinforcement is one of the most misunderstood parts of a slab. Some homeowners expect steel to make a slab crack-proof; others are told it is a waste of money. The truth is in between. This guide explains what steel actually does in a slab on grade, how much a rebar grid takes for common sizes, what each option costs, and how to place it so it does any good.</p>

<h2>What Reinforcement Does (and Doesn't Do)</h2>
<p>All concrete shrinks as it dries. In a slab, that shrinkage is restrained by the ground underneath, so the slab cracks somewhere. That is normal and expected. Here is what each tool does about it:</p>
<ul>
<li><strong>Control joints</strong> are planned weak lines cut or tooled into the slab, spaced at about <?php echo esc_html(Slab_Data::r('joints.ratio')); ?> times the thickness in inches (in feet). The slab cracks along the joints instead of at random. This is the main crack control.</li>
<li><strong>Reinforcement</strong> crosses any crack that forms and holds the two sides together. The crack stays a tight hairline instead of opening up, and one side cannot settle lower than the other and become a trip edge.</li>
<li><strong>Steel does not add much strength</strong> to a residential slab on grade before it cracks. The concrete, its thickness and the base carry the load. Steel earns its keep after cracking, which is why it matters most where vehicles roll over the slab.</li>
</ul>
<p>So a reinforced slab still needs joints, a good base and a proper cure. Our <a href="<?php echo esc_url(home_url('/concrete-slab-cracks-causes-and-prevention/')); ?>">crack causes and prevention guide</a> goes deeper.</p>

<h2>Rebar Grid vs. Wire Mesh at a Glance</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th></th><th>#4 rebar grid</th><th>Welded wire mesh</th></tr></thead>
<tbody>
<tr><td><strong>Installed cost</strong></td><td><?php echo esc_html(Slab_Data::mr('reinforcement.rebar_range', 2, ' to ')); ?> per sq ft</td><td><?php echo esc_html(Slab_Data::mr('reinforcement.mesh_range', 2, ' to ')); ?> per sq ft</td></tr>
<tr><td><strong>What it is</strong></td><td>1/2-inch deformed bars tied in a square grid</td><td>Light wires welded in a grid, commonly 6x6 inch squares, sold in rolls or flat sheets</td></tr>
<tr><td><strong>Best for</strong></td><td>Driveways, garage floors, RV pads, shop floors</td><td>Patios, walkways, shed floors, interior floors</td></tr>
<tr><td><strong>Staying in position</strong></td><td>Stiff; stays on its chairs when people walk on it</td><td>Flexible; gets trampled down unless supported on chairs or sheets are used</td></tr>
<tr><td><strong>Holding cracks tight</strong></td><td>Much more steel crossing each crack</td><td>Enough for light shrinkage cracks</td></tr>
<tr><td><strong>DIY friendly</strong></td><td>Needs cutting, bending and tying</td><td>Quick to lay; rolls fight you, sheets are easier</td></tr>
</tbody>
</table>
</div>

<h2>How Much Rebar a Slab Needs</h2>
<p>The quantities below use the same layout as our <a href="<?php echo esc_url(home_url('/concrete-rebar-calculator/')); ?>">rebar calculator</a>: #4 bars with <?php echo (int) $r['edge_cover_in']; ?> inches of cover at the edges, <?php echo (int) round($r['splice_allowance'] * 100); ?>% added for laps and offcuts, and <?php echo (int) $r['stick_length_ft']; ?>-foot sticks.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Slab size</th><th>Bars at <?php echo (int) $sp; ?> in centers</th><th>Linear ft</th><th>Chairs</th><th>Bars at <?php echo (int) $tight; ?> in centers</th><th>Installed cost (rebar at <?php echo (int) $sp; ?> in)</th><th>Installed cost (mesh)</th></tr></thead>
<tbody>
<?php foreach ($sizes as $z) :
    $a = $z[0] * $z[1];
    $ga = Slab_Data::rebar_grid($z[0], $z[1], $sp);
    $gb = Slab_Data::rebar_grid($z[0], $z[1], $tight);
    ?>
<tr><td><strong><?php echo esc_html($z[0] . ' × ' . $z[1]); ?></strong></td><td><?php echo (int) $ga['sticks']; ?></td><td><?php echo esc_html(number_format($ga['lin_ft'])); ?></td><td><?php echo (int) $ga['chairs']; ?></td><td><?php echo (int) $gb['sticks']; ?></td><td><?php echo esc_html($mr([$a * $r['rebar_range'][0], $a * $r['rebar_range'][1]])); ?></td><td><?php echo esc_html($mr([$a * $r['mesh_range'][0], $a * $r['mesh_range'][1]])); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">Bar counts are <?php echo (int) $r['stick_length_ft']; ?>-foot sticks of #4 rebar. Installed costs include material, chairs, tie wire and placing labor.</div>
</div>
<p>Tightening the grid from <?php echo (int) $sp; ?> to <?php echo (int) $tight; ?> inches adds about <?php echo esc_html(number_format($more_12, 0)); ?>% more steel. That is common on driveways in cold climates and on slabs over questionable soil. Lighter slabs sometimes use a wider grid, but <?php echo (int) $sp; ?> inches is a sensible default for vehicle loads.</p>

<h2>What the Difference Costs on a Real Slab</h2>
<p>On a 20x20 slab (400 sq ft), rebar runs <?php echo esc_html($mr($rebar_20)); ?> and mesh <?php echo esc_html($mr($mesh_20)); ?>. At our calculator's defaults (<?php echo esc_html(Slab_Data::money($r['rebar_default'], 2)); ?> vs. <?php echo esc_html(Slab_Data::money($r['mesh_default'], 2)); ?> per sq ft), choosing rebar adds about <?php echo esc_html($m($diff_20)); ?>, roughly <?php echo esc_html(number_format($diff_pct, 0)); ?>% of the slab's <?php echo esc_html($m($c20['est']['direct'])); ?> direct cost. For a driveway or garage that is cheap insurance against cracks that open and step.</p>
<div class="formula-box">
<div class="formula-label">Rebar grid for a 20 ft × 20 ft slab, #4 at <?php echo (int) $sp; ?> in</div>
<div class="formula-math">Usable span = 20 − 2 × (<?php echo (int) $r['edge_cover_in']; ?> ÷ 12) = <?php echo esc_html(number_format(20 - 2 * $r['edge_cover_in'] / 12, 1)); ?> ft → <?php echo (int) (floor((20 - 2 * $r['edge_cover_in'] / 12) / ($sp / 12)) + 1); ?> bars each way</div>
<div class="formula-explanation">2 × <?php echo (int) (floor((20 - 2 * $r['edge_cover_in'] / 12) / ($sp / 12)) + 1); ?> bars × <?php echo esc_html(number_format(20 - 2 * $r['edge_cover_in'] / 12, 1)); ?> ft, plus <?php echo (int) round($r['splice_allowance'] * 100); ?>% for laps = <?php echo esc_html(number_format($g18['lin_ft'])); ?> linear ft ÷ <?php echo (int) $r['stick_length_ft']; ?> = <?php echo (int) $g18['sticks']; ?> sticks, with about <?php echo (int) $g18['chairs']; ?> chairs at the grid crossings.</div>
</div>

<h2>Placement: Chairs, Cover and Height</h2>
<p>Steel lying on the gravel does almost nothing. It has to sit inside the concrete, in the middle to upper part of the slab, where shrinkage cracks open widest.</p>
<ul>
<li><strong>Use chairs.</strong> Plastic or wire chairs (or concrete "dobies") hold the grid at the right height. Space them about every <?php echo esc_html(Slab_Data::r('reinforcement.chair_grid_ft')); ?> ft in both directions so the steel does not sag between them when people walk on it.</li>
<li><strong>Pick the chair height for the slab.</strong> A chair about half the slab's thickness puts the steel near mid-depth, so 5 and 6-inch slabs need taller chairs than a 4-inch slab. Leave enough concrete on top to protect the steel from the surface, and keep the grid about <?php echo (int) $r['edge_cover_in']; ?> inches in from the forms.</li>
<li><strong>Do not "hook" mesh.</strong> Laying mesh on the ground and pulling it up with a rake during the pour is a common shortcut. It rarely ends up where it should and usually ends up at the bottom. Put it on chairs instead.</li>
<li><strong>Tie the grid.</strong> Tie rebar at most crossings with wire ties so it moves as one mat. Tie mesh to its chairs and at overlaps.</li>
</ul>

<div class="callout callout-warning">
<div class="callout-title">Do not run steel through control joints on a driveway or patio unless you mean to</div>
<p>Continuous steel across a sawcut can hold the joint closed and push the crack somewhere else. Some contractors cut every other bar at joint lines, or use smooth dowels at construction joints, so each panel can move. Ask your contractor how they handle it. For larger jobs ACI 302.1R discusses joint and reinforcement details.</p>
</div>

<h2>Lap Splices</h2>
<p>Where two bars meet end to end, they overlap and are tied together. A common rule for slab bars is a lap of <?php echo (int) $r['lap_bar_diameters']; ?> bar diameters, which for #4 (1/2-inch) bar is <?php echo (int) $r['lap_in_no4']; ?> inches (about <?php echo esc_html(Slab_Data::num($lap_ft, 1)); ?> ft). Structural members such as footings and walls are governed by ACI 318 or ACI 332 and the engineer's drawings, which can call for different lap lengths. Stagger splices so they do not all fall on one line.</p>
<p>Wire mesh sheets also overlap: at least one full grid square plus a couple of inches, tied together, is the usual practice. The <a href="<?php echo esc_url(home_url('/concrete-wire-mesh-calculator/')); ?>">wire mesh calculator</a> adds the overlap when it counts sheets or rolls.</p>

<h2>What About Fiber?</h2>
<p>Synthetic fibers (polypropylene or similar) are mixed into the concrete at the plant. They reduce early plastic shrinkage cracking at the surface, which helps on hot, windy days, and they cannot end up in the wrong place. Heavier macro-synthetic and steel fibers can replace light mesh on some slabs. Fibers do not replace a rebar grid on a driveway or garage floor unless an engineer or the fiber maker's design says so. Ask your ready-mix supplier for their per-yard price, since it varies by product and dose.</p>

<h2>When You Can Skip Reinforcement</h2>
<p>Plenty of sound slabs have no steel at all. Skipping it is reasonable for:</p>
<ul>
<li>Small pads (AC units, trash cans, a grill) and narrow walkways that are well jointed.</li>
<li>Patios on firm, well-drained soil where a hairline crack is acceptable.</li>
<li>Slabs where the budget is tight and the money is better spent on a good base and proper joints.</li>
</ul>
<p>Do not skip it on driveways, garage floors, RV pads, slabs on clay or filled soil, or anywhere a plan, permit or inspector calls for it. The IRC does not require steel in a plain floor slab on ground, but local codes and HOA rules can. Use the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a> to compare rebar, mesh and no reinforcement for your own size, and see the <a href="<?php echo esc_url(home_url('/driveway/')); ?>">driveway</a> and <a href="<?php echo esc_url(home_url('/garage/')); ?>">garage</a> pages for those projects.</p>

<h2>Sources and Method</h2>
<p>Placement and joint guidance follows ACI 302.1R (guide to floor and slab construction) and the Portland Cement Association's guidance on slabs on ground. Structural splice and cover rules come from ACI 318 and ACI 332, which apply to footings, walls and engineered slabs. Quantities use our calculator's grid layout; installed costs are this site's working benchmarks (see our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>) and vary by region and steel prices.</p>
<?php
$html = ob_get_clean();

$g24 = Slab_Data::rebar_grid(24, 24, $sp);

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'Is rebar or wire mesh better for a concrete slab?',
            'a' => 'Rebar is better for driveways, garage floors and anything carrying vehicles, because it holds cracks tighter and stays in place on its chairs. Welded wire mesh is fine for patios, walkways and shed floors if it is supported on chairs so it ends up inside the slab.',
        ],
        [
            'q' => 'How much more does rebar cost than wire mesh?',
            'a' => 'Rebar runs about ' . Slab_Data::mr('reinforcement.rebar_range', 2, ' to ') . ' per sq ft installed and mesh about ' . Slab_Data::mr('reinforcement.mesh_range', 2, ' to ') . '. On a 20x20 slab that is roughly ' . $m($diff_20) . ' more for rebar at typical rates.',
        ],
        [
            'q' => 'How much rebar do I need for a 24x24 slab?',
            'a' => 'With #4 bars on ' . $sp . '-inch centers, ' . $r['edge_cover_in'] . ' inches of edge cover and ' . (int) round($r['splice_allowance'] * 100) . '% for laps, a 24x24 slab needs about ' . number_format($g24['lin_ft']) . ' linear feet, or ' . $g24['sticks'] . ' twenty-foot bars, plus about ' . $g24['chairs'] . ' chairs.',
        ],
        [
            'q' => 'Does rebar stop concrete from cracking?',
            'a' => 'No. Concrete shrinks and cracks whether or not it has steel. Control joints spaced at about ' . Slab_Data::r('joints.ratio') . ' times the slab thickness in inches (in feet) decide where it cracks; rebar holds those cracks tight so they do not widen or step.',
        ],
        [
            'q' => 'How much should #4 rebar overlap?',
            'a' => 'A common rule for slab bars is ' . $r['lap_bar_diameters'] . ' bar diameters, which is ' . $r['lap_in_no4'] . ' inches for #4 bar. Footings, walls and engineered slabs follow the lap lengths on the plans.',
        ],
        [
            'q' => 'Can I pour a slab without rebar or mesh?',
            'a' => 'Yes for small pads, walkways and patios on firm soil with proper control joints. Use reinforcement for driveways, garage floors, RV pads and slabs on clay or fill, or wherever local code or the plans call for it.',
        ],
    ],
];
