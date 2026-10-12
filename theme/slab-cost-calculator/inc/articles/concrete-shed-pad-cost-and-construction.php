<?php
/**
 * Article: Concrete Shed Pad Cost and Construction Guide (post slug concrete-shed-pad-cost-and-construction).
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
$s = $sd['site'];
$dl = $sd['delivery'];
$rm = $sd['concrete']['ready_mix_default'];

$sizes = [[8, 10], [10, 12], [12, 16], [12, 20]];
$rows = [];
foreach ($sizes as $z) {
    $rows[] = ['l' => $z[0], 'w' => $z[1], 'c' => Slab_Data::size_costs($z[0], $z[1], 4)];
}

// Gravel pad: compacted crushed stone at the shallow and deep end of the base range.
$gravel = function ($area, $depth_in) use ($s) {
    $yd = $area * $depth_in / 12 / 27 * $s['gravel_compaction'];
    return [
        'yd' => $yd,
        'tons' => $yd * $s['gravel_tons_per_yd'],
        'diy' => [$yd * $s['gravel_range'][0], $yd * $s['gravel_range'][1]],
        'pro' => [$yd * $s['gravel_range'][0] + $area * $s['excavation_range'][0], $yd * $s['gravel_range'][1] + $area * $s['excavation_range'][1]],
    ];
};
$gd = $s['gravel_depth_range'];
$cmp = [];
foreach ([[10, 12], [12, 16]] as $z) {
    $a = $z[0] * $z[1];
    $cmp[] = [
        'label' => $z[0] . ' × ' . $z[1],
        'c' => Slab_Data::size_costs($z[0], $z[1], 4),
        'g_lo' => $gravel($a, $gd[0]),
        'g_hi' => $gravel($a, $gd[1]),
    ];
}

// Worked example: 10x12.
$ex = Slab_Data::size_costs(10, 12, 4);
$e = $ex['est'];
$edge_yd = Slab_Data::edge_yd(12, 16, 4, $sd['monolithic']['edge_depth_in'][0]);
$prep = Slab_Data::site_prep_psf();
$vapor = [120 * $s['vapor_range'][0], 120 * $s['vapor_range'][1]];
$base_yd = 120 * $s['gravel_depth_in'] / 12 / 27 * $s['gravel_compaction'];
$jmax = $sd['joints']['max_ft'][4];

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> a 4-inch concrete shed pad costs about <strong><?php echo esc_html($mr($rows[0]['c']['installed'])); ?></strong> installed for an 8x10 shed, <?php echo esc_html($mr($rows[1]['c']['installed'])); ?> for a 10x12 and <?php echo esc_html($mr($rows[2]['c']['installed'])); ?> for a 12x16. DIY materials run about <?php echo esc_html($mr($rows[1]['c']['diy'])); ?> for a 10x12. A compacted gravel pad for the same 10x12 shed costs only about <?php echo esc_html($mr(Slab_Data::round_pair([$cmp[0]['g_lo']['diy'][0], $cmp[0]['g_hi']['diy'][1]], 10))); ?> in stone, but it is a different kind of foundation.</p>

<p>Whether a shed needs concrete at all depends on what goes in it and what kind of shed it is. A wood shed with its own floor frame sits happily on gravel. A shed with no floor, a workshop, or anything storing a mower, tractor or heavy tools is better on a slab. This guide prices both, then covers how to build the slab so the shed stays square, dry and anchored.</p>

<h2>Concrete Shed Pad Cost by Size</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Shed size</th><th>Concrete (4 in, +<?php echo (int) $sd['concrete']['waste_pct']; ?>%)</th><th>80-lb bags</th><th>DIY materials</th><th>Installed</th></tr></thead>
<tbody>
<?php foreach ($rows as $r) : ?>
<tr><td><strong><?php echo (int) $r['l']; ?> × <?php echo (int) $r['w']; ?> ft</strong> (<?php echo (int) ($r['l'] * $r['w']); ?> sq ft)</td><td><?php echo esc_html(number_format($r['c']['est']['order_yd'], 2)); ?> yd³</td><td><?php echo (int) $r['c']['est']['bags80']; ?></td><td><?php echo esc_html($mr($r['c']['diy'])); ?></td><td><?php echo esc_html($mr($r['c']['installed'])); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption"><?php echo esc_html(Slab_Data::cost_basis_note()); ?></div>
</div>
<p>Small pads cost more per square foot than big slabs because the delivery fee, setup and a contractor's minimum charge are spread over less area. Some contractors price shed pads as a flat job. Four inches is right for most sheds; use 5 inches if you will drive a riding mower or ATV in and out every week. Try other sizes in the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a> or on the <a href="<?php echo esc_url(home_url('/shed/')); ?>">shed pad page</a>.</p>

<h2>Concrete Slab vs. Gravel Pad</h2>
<p>A gravel pad is a layer of compacted crushed stone held in by a timber or block border. Here is how the costs compare. Gravel figures are compacted stone at <?php echo esc_html(Slab_Data::range($gd, ' or ')); ?> inches deep, with excavation added for the installed price. The timber or block border is extra.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Shed</th><th>Gravel pad, DIY stone</th><th>Gravel pad, installed</th><th>Concrete, DIY</th><th>Concrete, installed</th></tr></thead>
<tbody>
<?php foreach ($cmp as $c) : ?>
<tr><td><strong><?php echo esc_html($c['label']); ?></strong></td><td><?php echo esc_html($mr(Slab_Data::round_pair([$c['g_lo']['diy'][0], $c['g_hi']['diy'][1]], 10))); ?></td><td><?php echo esc_html($mr(Slab_Data::round_pair([$c['g_lo']['pro'][0], $c['g_hi']['pro'][1]], 10))); ?></td><td><?php echo esc_html($mr($c['c']['diy'])); ?></td><td><?php echo esc_html($mr($c['c']['installed'])); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th></th><th>Gravel pad</th><th>Concrete slab</th></tr></thead>
<tbody>
<tr><td><strong>Best for</strong></td><td>Wood sheds with a built-in floor on skids or joists</td><td>Floorless sheds, workshops, garden tractors, heavy storage</td></tr>
<tr><td><strong>Drainage</strong></td><td>Water drains straight through; keeps skids dry</td><td>Needs slope and a raised edge so water runs off</td></tr>
<tr><td><strong>Level and square</strong></td><td>Can settle; may need re-leveling</td><td>Stays flat; gives a square, level base</td></tr>
<tr><td><strong>Anchoring</strong></td><td>Ground anchors (augers) or anchors into the border</td><td>Anchor bolts cast in, or concrete anchors drilled later</td></tr>
<tr><td><strong>Pests</strong></td><td>Rodents can nest underneath a raised floor</td><td>No gap underneath if the shed sits directly on the slab</td></tr>
<tr><td><strong>Moving the shed later</strong></td><td>Easy</td><td>The slab stays</td></tr>
</tbody>
</table>
</div>
<p>A 10x12 gravel pad at <?php echo (int) $gd[0]; ?> inches needs about <?php echo esc_html(number_format($cmp[0]['g_lo']['yd'], 2)); ?> yd³ of stone (about <?php echo esc_html(Slab_Data::num($cmp[0]['g_lo']['tons'], 1)); ?> tons). Many builders also extend a gravel pad past the shed on each side so roof runoff does not splash mud onto the siding. Use the <a href="<?php echo esc_url(home_url('/gravel-base-calculator/')); ?>">gravel base calculator</a> for your size.</p>

<h2>How to Build a Concrete Shed Pad</h2>
<ol>
<li><strong>Check the rules first.</strong> Call 811 before digging, and check setbacks from property lines (see permits below).</li>
<li><strong>Size the slab to the shed.</strong> For a shed built on the slab, make the slab the same size as the wall frame, or a touch smaller, so the siding laps over the edge and sheds water outside the slab. A slab that sticks out past the walls catches rain and channels it under the sill.</li>
<li><strong>Dig and build the base.</strong> Strip sod and soft soil, then place and compact a 4-inch gravel base. On a 10x12 that is about <?php echo esc_html(number_format($base_yd, 2)); ?> yd³ of stone. Excavation and base together run about <?php echo esc_html(Slab_Data::money_range($prep, 2, ' to ')); ?> per sq ft if you hire it out.</li>
<li><strong>Set forms.</strong> 2x4 forms for a 4-inch slab, staked every <?php echo esc_html(Slab_Data::r('site.stake_spacing_ft')); ?> ft, with the top about an inch or two above grade so the sill stays out of splash and wet soil. Slope the top slightly toward the door or to one side so any water inside can drain out.</li>
<li><strong>Add vapor barrier and reinforcement.</strong> 6-mil plastic under the slab keeps ground moisture out of stored items; on a 10x12 it costs about <?php echo esc_html($mr($vapor)); ?>. Welded wire mesh on chairs is usually enough for a shed, or #4 rebar for heavy loads.</li>
<li><strong>Pour, finish and joint.</strong> A 10x12 needs <?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³: <?php echo (int) $e['bags80']; ?> bags of 80-lb mix or a small ready-mix load. Joints should be no more than about <?php echo (int) $jmax; ?> ft apart, so one joint across the middle is enough on most shed pads.</li>
<li><strong>Set anchors and cure.</strong> Press anchor bolts into the wet concrete along the wall lines, then keep the slab damp or covered while it cures. Walk on it after <?php echo esc_html(Slab_Data::r('curing.foot_traffic_h')); ?> hours; wait about <?php echo (int) $sd['curing']['cars_days']; ?> days before building on it and longer before heavy loads.</li>
</ol>

<h2>Anchoring and Thickened Edges</h2>
<p>For a site-built shed, anchor bolts set in the slab hold the bottom plate down. IRC R403.1.6 calls for 1/2-inch bolts no more than 6 ft apart and within 12 inches of each plate end for houses, and many shed plans use the same layout. For a prefab shed, follow the maker's anchoring kit; many use concrete wedge or screw anchors drilled after the slab cures. In high-wind or hurricane areas your town may require a specific tie-down.</p>
<p>A thickened edge (a turn-down about <?php echo (int) $sd['monolithic']['edge_width_in']; ?> inches wide and <?php echo (int) $sd['monolithic']['edge_depth_in'][0]; ?> inches deep around the perimeter) stiffens the slab edge where the walls bear and resists frost undermining the edge. On a 12x16 pad it adds about <?php echo esc_html(number_format($edge_yd, 2)); ?> yd³ of concrete, roughly <?php echo esc_html($m(round($edge_yd * $rm / 10) * 10)); ?> in ready-mix plus some extra digging. It is a good idea on larger sheds and workshops, and some building departments require it once a permit is involved. The <a href="<?php echo esc_url(home_url('/concrete-footing-calculator/')); ?>">footing calculator</a> sizes it.</p>

<h2>Do You Need a Permit?</h2>
<p>The IRC (section R105.2) exempts one-story detached accessory buildings up to 200 sq ft from a building permit, so an 8x10, 10x12 or 12x16 shed often needs none, while a 12x20 (240 sq ft) usually does. Many towns change that limit, and zoning setbacks, easements and HOA rules apply even when no permit is needed. Where a permit is required, small slab permits typically cost <?php echo esc_html(Slab_Data::mr('permit.flatwork', 0, ' to ')); ?>. For frost, IRC R403.1.4.1 lets a detached light-frame accessory building of 600 sq ft or less sit on a slab without frost-depth footings. See <a href="<?php echo esc_url(home_url('/concrete-slab-permits-and-costs/')); ?>">do you need a permit for a concrete slab</a>.</p>

<h2>Bags or Ready-Mix?</h2>
<p>Shed pads sit right on the line. Below about <?php echo esc_html(Slab_Data::num($dl['bags_win_below_yd'])); ?> cubic yard, bags usually win; above about <?php echo esc_html(Slab_Data::num($dl['ready_mix_wins_above_yd'])); ?> yards, a truck usually wins. An 8x10 (<?php echo esc_html(number_format($rows[0]['c']['est']['order_yd'], 2)); ?> yd³, <?php echo (int) $rows[0]['c']['est']['bags80']; ?> bags) is in between, and most DIYers do it with bags, a rented mixer and two helpers. A 10x12 or larger is easier with ready-mix, but expect a short-load fee of about <?php echo esc_html(Slab_Data::mr('delivery.short_load_flat', 0, ' to ')); ?> on small orders. Our <a href="<?php echo esc_url(home_url('/ready-mix-vs-bagged-concrete/')); ?>">ready-mix vs. bagged guide</a> walks through the break-even.</p>

<h2>DIY or Hire a Pro?</h2>
<p>A shed pad is one of the best concrete jobs to do yourself. It is small, it does not need a decorative finish, and the main skill is getting the forms square and level. Hire out if the site needs real excavation, the truck cannot reach, or the shed is a large workshop that needs a permit and inspection. If you get a bid, check it with the <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">concrete quote checker</a>.</p>

<h2>Sources and Method</h2>
<p>Concrete prices use the same line items as our calculator; gravel pad prices use our gravel, compaction and excavation rates (see our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>). Local prices vary. Code references: IRC R105.2 (work exempt from permit), IRC R403.1.4.1 (frost protection exemption), IRC R403.1.6 (anchor bolts) and IRC R506 (slabs on ground), with ACI 302.1R for slab construction. Your local building department has the final say.</p>
<?php
$html = ob_get_clean();

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'How much does a concrete shed pad cost?',
            'a' => 'At 4 inches thick, about ' . $mr($rows[0]['c']['installed']) . ' installed for an 8x10 shed, ' . $mr($rows[1]['c']['installed']) . ' for a 10x12 and ' . $mr($rows[2]['c']['installed']) . ' for a 12x16. DIY materials for a 10x12 run about ' . $mr($rows[1]['c']['diy']) . '.',
        ],
        [
            'q' => 'Is a gravel or concrete shed pad better?',
            'a' => 'Gravel is cheaper and drains well, which suits a wood shed with its own floor frame. Concrete costs more but gives a flat, permanent floor for floorless sheds, workshops and heavy equipment, and it is easier to anchor to.',
        ],
        [
            'q' => 'How many bags of concrete do I need for a 10x12 shed pad?',
            'a' => 'A 10x12 pad at 4 inches needs ' . number_format($e['order_yd'], 2) . ' cubic yards including ' . $sd['concrete']['waste_pct'] . '% waste, which is ' . $e['bags80'] . ' bags of 80-lb mix or ' . $e['bags60'] . ' bags of 60-lb mix.',
        ],
        [
            'q' => 'How thick should a concrete shed slab be?',
            'a' => 'Four inches for most sheds. Use 5 inches if you will regularly drive a riding mower, ATV or garden tractor inside, and consider a thickened edge on larger sheds and workshops.',
        ],
        [
            'q' => 'Do I need a permit for a shed slab?',
            'a' => 'Often not for a small shed: the IRC exempts one-story detached accessory buildings up to 200 sq ft, though many towns set their own limit. Setbacks and HOA rules still apply. Where a permit is needed, small slab permits typically cost ' . Slab_Data::mr('permit.flatwork', 0, ' to ') . '.',
        ],
    ],
];
