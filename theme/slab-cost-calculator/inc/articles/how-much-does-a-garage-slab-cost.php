<?php
/**
 * Article: How Much Does a Garage Slab Cost? (post slug how-much-does-a-garage-slab-cost).
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
$waste = 1 + $sd['concrete']['waste_pct'] / 100;
$mono = $sd['monolithic'];
$code = $sd['code'];
$frost = $sd['freeze_thaw_ft']; // inches below grade

// Garage sizes at 5 inches.
$sizes = [
    '1-car' => [14, 22],
    '2-car' => [24, 24],
    '3-car' => [36, 24],
];
$rows = [];
foreach ($sizes as $label => $s) {
    $rows[$label] = ['l' => $s[0], 'w' => $s[1], 'c' => Slab_Data::size_costs($s[0], $s[1], 5)];
}
$g = $rows['2-car']['c'];
$g4 = Slab_Data::size_costs(24, 24, 4);
$e = $g['est'];
$garage_psf = Slab_Data::garage_psf();

// Monolithic thickened edge on the 24x24 at 5 in.
$edge = [];
foreach ($mono['edge_depth_in'] as $depth) {
    $yd = Slab_Data::edge_yd(24, 24, 5, $depth);
    $tr = $mono['trenching'][(string) $depth];
    $edge[$depth] = [
        'yd' => $yd,
        'add' => Slab_Data::round_pair([$yd * $rm + $mono['edge_rebar'][0] + $tr[0], $yd * $rm + $mono['edge_rebar'][1] + $tr[1]], 10),
    ];
}
$mono_total = Slab_Data::round_pair([$g['installed'][0] + $edge[12]['add'][0], $g['installed'][1] + $edge[18]['add'][1] * $op], 10);

// Stem wall + spread footing on the same 24x24, computed from volumes and rates.
// Footing 16 in wide x 8 in deep; stem wall 8 in thick, about as tall as the frost depth.
$perim = 96;
$foot_cuft_lf = (16 / 12) * (8 / 12);
$stem = function ($h_ft, $i) use ($perim, $foot_cuft_lf, $waste, $rm, $sd, $mono) {
    $cuft = $perim * ($foot_cuft_lf + (8 / 12) * $h_ft);
    $yd = $cuft * $waste / 27;
    $sfca = 2 * $perim * $h_ft;
    return [
        'yd' => $yd,
        'concrete' => $yd * $rm,
        'wall_forms' => $sfca * $mono['stem_wall_form_sfca'][$i],
        'sfca' => $sfca,
        'footing_forms' => 2 * $perim * $sd['site']['forms_range'][$i],
        'trench' => $perim * $mono['frost_trench_lf'][$i],
        'rebar' => 2 * $mono['edge_rebar'][$i],
    ];
};
$s_lo = $stem($frost[0] / 12, 0);
$s_hi = $stem($frost[1] / 12, 1);
$sum = function ($s) {
    return $s['concrete'] + $s['wall_forms'] + $s['footing_forms'] + $s['trench'] + $s['rebar'];
};
$stem_add = Slab_Data::round_pair([$sum($s_lo), $sum($s_hi) * $op], 100);
$stem_total = Slab_Data::round_pair([$g['installed'][0] + $stem_add[0], $g['installed'][1] + $stem_add[1]], 100);

// Extras.
$vapor = [576 * $sd['site']['vapor_range'][0], 576 * $sd['site']['vapor_range'][1]];
$psi_extra = Slab_Data::round_pair(Slab_Data::psi_extra($code['psi_severe'], $e['order_yd']), 10);
$prep = Slab_Data::site_prep_psf();
$fall = 24 / 8; // 1/8 in per ft over 24 ft
$joint_max = $sd['joints']['max_ft'][5];

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> a 2-car garage slab (24x24 ft, 576 sq ft) at 5 inches costs about <strong><?php echo esc_html($mr($g['installed'])); ?></strong> as a plain floating slab, or roughly <?php echo esc_html(Slab_Data::money_range($garage_psf, 0, ' to ')); ?> per sq ft. With a monolithic thickened edge it runs about <?php echo esc_html($mr($mono_total)); ?>. In cold climates, a stem wall on frost-depth footings adds roughly <?php echo esc_html($mr($stem_add)); ?>, for a total near <?php echo esc_html($mr($stem_total)); ?>.</p>

<p>The floor itself is the easy part of a garage slab. The real cost question is what holds up the walls. In a warm climate, a slab with a thickened edge does the job in one pour. Where the ground freezes, the building department will usually want footings below the frost line, and that can roughly double the price or more. This guide prices both, with the math shown.</p>

<h2>Garage Slab Cost by Size</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Garage</th><th>Size</th><th>Concrete (5 in, +<?php echo (int) $sd['concrete']['waste_pct']; ?>%)</th><th>Floating slab installed</th><th>Per sq ft</th></tr></thead>
<tbody>
<?php foreach ($rows as $label => $r) : ?>
<tr><td><strong><?php echo esc_html($label); ?></strong></td><td><?php echo (int) $r['l']; ?> × <?php echo (int) $r['w']; ?> ft (<?php echo esc_html(number_format($r['l'] * $r['w'])); ?> sq ft)</td><td><?php echo esc_html(number_format($r['c']['est']['order_yd'], 2)); ?> yd³</td><td><?php echo esc_html($mr($r['c']['installed'])); ?></td><td><?php echo esc_html(Slab_Data::money_range($r['c']['installed_psf'], 2, ' to ')); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption"><?php echo esc_html(Slab_Data::cost_basis_note()); ?></div>
</div>
<p>These are slab-only prices: no footing upgrade, no excavation and no building above. The IRC minimum for a slab on ground is 3.5 inches and plenty of garages are poured at 4 inches (a 4-inch 24x24 runs about <?php echo esc_html($mr($g4['installed'])); ?>). We price at 5 inches because a garage floor carries point loads from tires, jacks and jack stands, and the extra inch costs little. Price your own footprint in the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a> or on the <a href="<?php echo esc_url(home_url('/garage/')); ?>">garage slab page</a>.</p>

<h2>Option 1: Monolithic Slab with a Thickened Edge</h2>
<p>A monolithic (or turn-down) slab is poured in one piece. The floor is 4 to 5 inches thick, and around the outside the concrete turns down into a beam about <?php echo (int) $mono['edge_width_in']; ?> inches wide and <?php echo esc_html(Slab_Data::r('monolithic.edge_depth_in')); ?> inches deep, with <?php echo (int) $mono['edge_bars']; ?> continuous #4 bars in it. That beam is the footing. The walls sit on top, held by anchor bolts set in the wet concrete.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>24x24 garage, 5 in floor</th><th>Floating</th><th><?php echo (int) $mono['edge_depth_in'][0]; ?> in edge</th><th><?php echo (int) $mono['edge_depth_in'][1]; ?> in edge</th></tr></thead>
<tbody>
<tr><td>Extra concrete for the edge</td><td>none</td><td><?php echo esc_html(number_format($edge[12]['yd'], 2)); ?> yd³</td><td><?php echo esc_html(number_format($edge[18]['yd'], 2)); ?> yd³</td></tr>
<tr><td>Edge rebar (<?php echo (int) $mono['edge_bars']; ?> × #4)</td><td>none</td><td><?php echo esc_html(Slab_Data::mr('monolithic.edge_rebar', 0, ' to ')); ?></td><td><?php echo esc_html(Slab_Data::mr('monolithic.edge_rebar', 0, ' to ')); ?></td></tr>
<tr><td>Extra trenching</td><td>none</td><td><?php echo esc_html(Slab_Data::money_range($mono['trenching']['12'], 0, ' to ')); ?></td><td><?php echo esc_html(Slab_Data::money_range($mono['trenching']['18'], 0, ' to ')); ?></td></tr>
<tr><td><strong>Added cost</strong></td><td>baseline</td><td><strong>+<?php echo esc_html($mr($edge[12]['add'])); ?></strong></td><td><strong>+<?php echo esc_html($mr($edge[18]['add'])); ?></strong></td></tr>
</tbody>
</table>
</div>
<p>The edge is cheap because it is mostly concrete and a trench, poured by the same crew on the same day. Its limit is depth: even the <?php echo (int) $mono['edge_depth_in'][1]; ?>-inch edge only reaches below the frost line where the frost line is shallow. Size other footprints with the <a href="<?php echo esc_url(home_url('/concrete-footing-calculator/')); ?>">concrete footing calculator</a>.</p>

<h2>Option 2: Stem Wall on Frost-Depth Footings</h2>
<p>In the northern US, frost can reach <?php echo esc_html(Slab_Data::r('freeze_thaw_ft')); ?> inches or more below grade. Footings above that depth can heave. The usual answer is a spread footing at frost depth, a short concrete stem wall (also called a frost wall) up to slab level, and a floor slab poured inside the walls. It takes two or three separate pours with inspections between them, so it costs much more than a turn-down edge.</p>
<p>No single price list covers stem walls, so here is the build-up for the same 24x24 garage (<?php echo (int) $perim; ?> ft of perimeter), assuming a 16x8-inch footing and an 8-inch wall about as tall as the frost depth:</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Item</th><th><?php echo (int) $frost[0]; ?> in frost depth</th><th><?php echo (int) $frost[1]; ?> in frost depth</th></tr></thead>
<tbody>
<tr><td>Concrete, footing + wall (+<?php echo (int) $sd['concrete']['waste_pct']; ?>%) at <?php echo esc_html(Slab_Data::money($rm)); ?>/yd³</td><td><?php echo esc_html(number_format($s_lo['yd'], 2)); ?> yd³ = <?php echo esc_html($m($s_lo['concrete'])); ?></td><td><?php echo esc_html(number_format($s_hi['yd'], 2)); ?> yd³ = <?php echo esc_html($m($s_hi['concrete'])); ?></td></tr>
<tr><td>Wall forming, both faces</td><td><?php echo esc_html(number_format($s_lo['sfca'])); ?> sq ft at <?php echo esc_html(Slab_Data::money($mono['stem_wall_form_sfca'][0])); ?> = <?php echo esc_html($m($s_lo['wall_forms'])); ?></td><td><?php echo esc_html(number_format($s_hi['sfca'])); ?> sq ft at <?php echo esc_html(Slab_Data::money($mono['stem_wall_form_sfca'][1])); ?> = <?php echo esc_html($m($s_hi['wall_forms'])); ?></td></tr>
<tr><td>Footing forms</td><td><?php echo esc_html($m($s_lo['footing_forms'])); ?></td><td><?php echo esc_html($m($s_hi['footing_forms'])); ?></td></tr>
<tr><td>Trench to frost depth and backfill</td><td><?php echo esc_html($m($s_lo['trench'])); ?></td><td><?php echo esc_html($m($s_hi['trench'])); ?></td></tr>
<tr><td>Footing and wall rebar</td><td><?php echo esc_html($m($s_lo['rebar'])); ?></td><td><?php echo esc_html($m($s_hi['rebar'])); ?></td></tr>
<tr><td><strong>Stem wall added cost</strong></td><td colspan="2"><strong>about <?php echo esc_html($mr($stem_add)); ?></strong> (high end includes <?php echo (int) round($sd['overhead_profit'] * 100); ?>% overhead and profit)</td></tr>
</tbody>
</table>
</div>
<p>That works out to roughly <?php echo esc_html($m($stem_add[0] / $perim) . ' to ' . $m($stem_add[1] / $perim)); ?> per linear foot of wall. Your local footing size, wall height and rebar schedule will come from the building department or an engineer, so treat this as a budgeting range, not a design. A concrete block stem wall on a poured footing is a common alternative; get prices for both if a mason works in your area.</p>

<div class="callout callout-info">
<div class="callout-title">Do you need frost footings at all?</div>
<p>IRC R403.1.4.1 lets a detached, light-frame accessory building of 600 sq ft or less, with an eave height of 10 ft or less, skip frost protection. A detached 24x24 (576 sq ft) can qualify; a detached 3-car garage cannot. Attached garages are part of the house and need frost-protected footings. A frost-protected shallow foundation (IRC R403.3), which uses rigid foam insulation to keep the ground under a shallow footing from freezing, is another option. Your building department decides which applies.</p>
</div>

<h2>Floor Details That Change the Price</h2>
<ul>
<li><strong>PSI:</strong> IRC Table R402.2 sets the minimum for garage floor slabs at <?php echo esc_html(number_format($code['psi_protected'])); ?> PSI in mild climates, <?php echo esc_html(number_format($code['psi_exposed'])); ?> in moderate and <?php echo esc_html(number_format($code['psi_severe'])); ?> PSI air-entrained in severe-weathering areas, since cars bring in snow and salt. Moving a 2-car slab from <?php echo esc_html(number_format($sd['concrete']['psi_base'])); ?> to <?php echo esc_html(number_format($code['psi_severe'])); ?> PSI adds only about <?php echo esc_html($mr($psi_extra)); ?>.</li>
<li><strong>Vapor barrier:</strong> IRC R506.2.3 requires a 6-mil vapor retarder under floor slabs, but its exceptions include detached garages and other unheated accessory buildings. Put one in anyway if you might heat the garage, finish the floor with epoxy or store anything that hates moisture. On 576 sq ft it costs about <?php echo esc_html($mr($vapor)); ?>.</li>
<li><strong>Base:</strong> a 4-inch compacted gravel base (IRC R506.2.2) plus excavation adds about <?php echo esc_html(Slab_Data::money_range($prep, 2, ' to ')); ?> per sq ft, or <?php echo esc_html($mr(Slab_Data::round_pair([$prep[0] * 576, $prep[1] * 576], 10))); ?> for a 2-car garage. Use the <a href="<?php echo esc_url(home_url('/gravel-base-calculator/')); ?>">gravel base calculator</a>.</li>
<li><strong>Slope:</strong> IRC R309.1 requires the parking area of a garage floor to slope toward the vehicle door or a drain. At <?php echo esc_html($sd['site']['drainage_slope_in_per_ft'][0]); ?> inch per foot, a 24 ft deep garage falls <?php echo esc_html(Slab_Data::num($fall)); ?> inches front to back.</li>
<li><strong>Joints:</strong> a 5-inch slab should have joints no more than about <?php echo (int) $joint_max; ?> ft apart. Two joints, one each way, split a 24x24 into four 12x12 panels.</li>
<li><strong>Reinforcement:</strong> #4 rebar at <?php echo (int) $sd['reinforcement']['rebar_base_spacing_in']; ?> inches runs about <?php echo esc_html(Slab_Data::mr('reinforcement.rebar_range', 2, ' to ')); ?> per sq ft and is in the high end of our ranges. See the <a href="<?php echo esc_url(home_url('/concrete-rebar-calculator/')); ?>">rebar calculator</a>.</li>
</ul>

<h2>Which Foundation Should You Choose?</h2>
<p>If you are in a warm or mild climate, or building a small detached garage that qualifies for the frost exemption, a monolithic slab is the cheapest sound choice, with one pour and one footing inspection. If you are in a cold climate and the garage is attached or larger than 600 sq ft, plan on a stem wall or a frost-protected shallow foundation and budget accordingly. A stem wall also raises the wood framing well above grade, which helps with snow, splash and termites. Comparing a 24x24 slab on its own? See the <a href="<?php echo esc_url(home_url('/24x24-concrete-slab-cost-breakdown/')); ?>">24x24 slab cost breakdown</a>.</p>

<h2>DIY or Hire a Pro?</h2>
<p>A garage slab is a structural element that will be inspected, and it takes <?php echo esc_html(number_format($e['order_yd'], 2)); ?> cubic yards for a 2-car floor alone. Even experienced DIYers usually hire a crew for the pour and finish. You can save on demolition, clearing and the final cleanup. Before signing, check the bid with the <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">concrete quote checker</a> and make sure it states floor thickness, edge or footing size, PSI, base depth, vapor barrier and anchor bolts. Wait at least <?php echo (int) $sd['curing']['cars_days']; ?> days before parking on the new floor.</p>

<h2>Sources and Method</h2>
<p>Slab prices use the same line items as our calculator (see our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>). The stem wall build-up uses our ready-mix, forming, trenching and rebar rates applied to the volumes shown. Code references: IRC R403 (footings, including R403.1.4.1 frost exemption and R403.3 frost-protected shallow foundations), IRC R506 (slabs on ground) and IRC Table R402.2 (concrete strength), with ACI 332 for residential concrete and ACI 302.1R for floor construction. Frost depth, footing size and permits are set locally; your building department and, where needed, an engineer have the final say.</p>
<?php
$html = ob_get_clean();

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'How much does a 2-car garage slab cost?',
            'a' => 'A 24x24 ft garage slab at 5 inches costs about ' . $mr($g['installed']) . ' as a floating slab, or ' . $mr($mono_total) . ' as a monolithic slab with a thickened edge. Frost-depth stem wall footings add roughly ' . $mr($stem_add) . ' more.',
        ],
        [
            'q' => 'What is the difference between a monolithic slab and a stem wall garage foundation?',
            'a' => 'A monolithic slab is poured in one piece, with the edge thickened to ' . Slab_Data::r('monolithic.edge_depth_in') . ' inches to act as the footing. A stem wall foundation uses a separate footing at frost depth, a short concrete wall up to slab level, and a floor slab poured inside. Stem walls cost more but are usually needed in cold climates.',
        ],
        [
            'q' => 'How thick should a garage slab be?',
            'a' => 'The IRC minimum is 3.5 inches and many garages are poured at 4 inches. Five inches is a better choice for pickups, jacks and heavier vehicles, and 6 inches for a shop with heavy equipment.',
        ],
        [
            'q' => 'What PSI concrete is required for a garage floor?',
            'a' => 'IRC Table R402.2 requires ' . number_format($code['psi_protected']) . ' PSI in mild climates, ' . number_format($code['psi_exposed']) . ' PSI in moderate climates and ' . number_format($code['psi_severe']) . ' PSI air-entrained concrete in severe-weathering climates.',
        ],
        [
            'q' => 'Does a detached garage need footings below the frost line?',
            'a' => 'Not always. Under IRC R403.1.4.1, a detached light-frame accessory building of 600 sq ft or less with eaves 10 ft or lower can skip frost protection. Larger or attached garages usually need footings at the local frost depth, which can be ' . Slab_Data::r('freeze_thaw_ft') . ' inches or more in northern states. Check with your building department.',
        ],
    ],
];
