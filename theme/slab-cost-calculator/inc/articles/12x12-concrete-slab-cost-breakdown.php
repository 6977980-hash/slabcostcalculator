<?php
/**
 * Article: 12x12 Concrete Slab Cost (post slug 12x12-concrete-slab-cost-breakdown).
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

$area = 144;
$c4 = Slab_Data::size_costs(12, 12, 4);
$c5 = Slab_Data::size_costs(12, 12, 5);
$c6 = Slab_Data::size_costs(12, 12, 6);
$e = $c4['est'];
$e6 = $c6['est'];
$basic = Slab_Data::estimate(12, 12, 4, 'basic');
$waste = $sd['concrete']['waste_pct'];
$dl = $sd['delivery'];
$y = $sd['concrete']['bag_yields'];
$bag = $sd['concrete']['bag_price_default'];
$f = $sd['finish'];
$lab = $sd['labor'];
$op = 1 + $sd['overhead_profit'];
$prep = Slab_Data::site_prep_psf();
$grid = Slab_Data::rebar_grid(12, 12, $sd['reinforcement']['rebar_base_spacing_in']);

// Patio: plain slab, no vapor barrier. Mesh is the common light-duty option.
$patio_lo = $basic['direct'];
$patio_hi = ($e['direct'] - $e['vapor'] - $e['rebar'] + $area * $sd['reinforcement']['mesh_default']) * $op;
$patio = Slab_Data::round_pair([$patio_lo, $patio_hi], 10);
// Shed: the calculator's full scope at 4 in (vapor barrier + rebar).
$shed = Slab_Data::round_pair([$e['direct'], $e['direct'] * $op], 10);
// Spa pad: full scope at 6 in.
$spa = Slab_Data::round_pair([$e6['direct'], $e6['direct'] * $op], 10);

// Stamped patio upgrade: finish material difference plus the labor-rate difference.
$stamp_fin = $area * ($f['stamped']['default'] - $f['broom']['default']);
$stamp_add = Slab_Data::round_pair([$stamp_fin, $stamp_fin * $op], 10);

// Ready-mix with a short-load fee vs. bags.
$rm_short = [$e['ready_mix'] + $dl['short_load_flat'][0], $e['ready_mix'] + $dl['short_load_flat'][1]];
$short_yd = max(0, $dl['common_min_yd'][0] - $e['order_yd']);
$rm_short_yd = [$e['ready_mix'] + $short_yd * $dl['short_load_per_yd'][0], $e['ready_mix'] + $short_yd * $dl['short_load_per_yd'][1]];
$tons = $e['bags80'] * 80 / 2000;

// Slope for a patio over 12 ft (inches of fall), from the drainage slope range.
$slope_lo = 12 * 1 / 8;
$slope_hi = 12 * 1 / 4;

$crew_h = $area * $lab['crew_hours_per_sqft'];
$joint_ft = 4 * $sd['joints']['ratio_best'][1];

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> a 12x12 concrete slab (144 sq ft) at 4 inches thick costs about <strong><?php echo esc_html($mr($c4['installed'])); ?></strong> installed, or <strong><?php echo esc_html($mr($c4['diy'])); ?></strong> in materials if you pour it yourself. A plain patio sits near the low end, a shed floor with a vapor barrier and rebar near the middle, and a 6-inch hot tub pad at <?php echo esc_html($mr($spa)); ?>. You need <?php echo esc_html(number_format($e['order_yd'], 2)); ?> cubic yards of concrete with <?php echo (int) $waste; ?>% waste, or <?php echo (int) $e['bags80']; ?> bags of 80-lb mix.</p>

<p>Twelve by twelve is one of the most requested slab sizes because it fits three common jobs: a small patio for a table and four chairs, a floor for a 10x12 or 12x12 shed, and a pad for a hot tub with room to step around it. Those three jobs use the same footprint but not the same slab. The thickness, the reinforcement and even whether the surface should slope are different, and that is where the price differences come from. This guide prices each one.</p>

<h2>12x12 Slab Cost by Use</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Use</th><th>Typical spec</th><th>Installed cost</th></tr></thead>
<tbody>
<tr><td><strong>Patio</strong></td><td>4 in, broom finish, wire mesh or none, no vapor barrier, sloped to drain</td><td><?php echo esc_html($mr($patio)); ?></td></tr>
<tr><td><strong>Shed floor</strong></td><td>4 in, 6-mil vapor barrier, #4 rebar at <?php echo (int) $sd['reinforcement']['rebar_base_spacing_in']; ?> in, level</td><td><?php echo esc_html($mr($shed)); ?></td></tr>
<tr><td><strong>Hot tub / spa pad</strong></td><td>6 in, #4 rebar, level, conduit for power set before the pour</td><td><?php echo esc_html($mr($spa)); ?></td></tr>
<tr><td>Stamped patio upgrade</td><td>Stamped finish instead of broom</td><td>add <?php echo esc_html($mr($stamp_add)); ?></td></tr>
</tbody>
</table>
<div class="table-caption">Each range runs from the direct cost of that scope (the low end of a fair bid) to the same scope plus <?php echo (int) round($sd['overhead_profit'] * 100); ?>% contractor overhead and profit. The patio low end uses labor at <?php echo esc_html(Slab_Data::money($lab['sqft_range'][0], 2)); ?>/sq ft. Excavation and a gravel base are extra.</div>
</div>

<p>The spread between the three is smaller than people expect. Going from a patio to a spa pad adds about <?php echo esc_html($m($e6['order_yd'] * $sd['concrete']['ready_mix_default'] - $e['order_yd'] * $sd['concrete']['ready_mix_default'])); ?> of concrete, plus the rebar and vapor barrier. Labor, forms and delivery stay about the same, because a 6-inch slab takes the same crew the same morning to place and finish.</p>

<h2>Cost by Thickness</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Thickness</th><th>Concrete to order (+<?php echo (int) $waste; ?>%)</th><th>80-lb bags</th><th>DIY materials</th><th>Installed</th></tr></thead>
<tbody>
<?php foreach ([4 => $c4, 5 => $c5, 6 => $c6] as $t => $c) : ?>
<tr><td><strong><?php echo (int) $t; ?> in</strong></td><td><?php echo esc_html(number_format($c['est']['order_yd'], 2)); ?> yd³</td><td><?php echo (int) $c['est']['bags80']; ?></td><td><?php echo esc_html($mr($c['diy'])); ?></td><td><?php echo esc_html($mr($c['installed'])); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption"><?php echo esc_html(Slab_Data::cost_basis_note()); ?></div>
</div>
<p>Per square foot, a 4-inch 12x12 works out to <?php echo esc_html(Slab_Data::money_range($c4['installed_psf'], 2, ' to ')); ?> installed. That is a little cheaper per foot than a 10x10, because the delivery fee and the crew's setup time are spread over 44 more square feet.</p>

<h2>Patio: What Matters for Price</h2>
<p>A patio is the simplest of the three. Four inches is plenty for foot traffic and furniture, and IRC section R506 sets 3.5 inches as the floor-slab minimum, so 4 inches gives a little margin for uneven ground. Many patio contractors use welded wire mesh (about <?php echo esc_html(Slab_Data::money($sd['reinforcement']['mesh_default'], 2)); ?>/sq ft) instead of a rebar grid, and skip the vapor barrier because nothing above the slab needs to stay dry.</p>
<p>The one thing a patio needs that the other two do not is slope. A fall of <?php echo esc_html($sd['site']['drainage_slope_in_per_ft'][0]); ?> to <?php echo esc_html($sd['site']['drainage_slope_in_per_ft'][1]); ?> inch per foot away from the house means the far edge sits about <?php echo esc_html(Slab_Data::num($slope_lo, 1)); ?> to <?php echo esc_html(Slab_Data::num($slope_hi)); ?> inches lower than the edge at the wall. Set the forms to that slope before the pour; it costs nothing extra but is hard to fix later.</p>
<p>Finish is the big price lever on a patio. A stamped 12x12 adds roughly <?php echo esc_html($mr($stamp_add)); ?> to the bill at typical rates: the stamped finish runs <?php echo esc_html(Slab_Data::money_range($f['stamped']['range'], 2, ' to ')); ?> per sq ft against <?php echo esc_html(Slab_Data::money_range($f['broom']['range'], 2, ' to ')); ?> for broom. Complex patterns, multiple colors or a crew that bills stamping at a higher labor rate (around <?php echo esc_html(Slab_Data::money($lab['sqft_stamped'], 2)); ?> per sq ft instead of <?php echo esc_html(Slab_Data::money($lab['sqft_default'], 2)); ?>) push it higher. See the <a href="<?php echo esc_url(home_url('/concrete-patio-slab-cost-guide/')); ?>">patio slab cost guide</a> for the full finish comparison.</p>

<h2>Shed Floor: What Matters for Price</h2>
<p>A shed slab has to be flat and square more than it has to be strong. Prefab shed kits are built to sit on a level surface, so the forms should be checked with a level and the diagonals measured equal before any concrete goes in. Because the shed is closed in, a 6-mil polyethylene vapor retarder under the slab (as IRC R506.2.3 asks for under floors of enclosed spaces) keeps ground moisture from wicking up into tools and the shed's wood floor framing. On a 12x12 that adds about <?php echo esc_html($m($e['vapor'])); ?>.</p>
<p>Set anchor bolts while the concrete is still plastic, lined up with the shed's sill plate. If a riding mower or ATV will live inside, go to 5 inches; that adds only about <?php echo esc_html($m($c5['est']['direct'] - $e['direct'])); ?> at full scope. Our <a href="<?php echo esc_url(home_url('/concrete-shed-pad-cost-and-construction/')); ?>">shed pad guide</a> compares a slab with a gravel pad.</p>

<h2>Hot Tub and Spa Pad: What Matters for Price</h2>
<p>A filled hot tub with people in it is one of the heaviest loads a backyard slab carries, and the weight sits on a small footprint. That is why most installers pour spa pads at 6 inches with a rebar grid, and why many hot tub makers list their own pad requirements in the installation manual. Check that manual first: if it calls for a specific thickness or flatness, the manufacturer may treat a weaker pad as a warranty issue.</p>
<ul>
<li><strong>Level, not sloped.</strong> A spa shell should sit flat, so set the forms level instead of sloping them like a patio. Keep the top of the pad a little above the surrounding ground so rain runs off the edges.</li>
<li><strong>Power first.</strong> Run electrical conduit through the forms before the pour if the disconnect or wiring will come up through or beside the slab. Cutting a trench in a finished pad costs far more than a length of conduit. The wiring itself is a job for a licensed electrician.</li>
<li><strong>Size.</strong> A 12x12 leaves a walkway around most residential tubs, with room for steps and a cover lifter. Measure your tub plus its steps before you set the forms.</li>
</ul>
<p>The 6-inch pad needs <?php echo esc_html(number_format($e6['order_yd'], 2)); ?> yd³ of concrete with waste, or <?php echo (int) $e6['bags80']; ?> bags of 80-lb mix. At that volume a truck is the practical choice.</p>

<h2>Bags or Ready-Mix for a 12x12?</h2>
<p>At <?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³ for 4 inches, a 12x12 is past the point (about <?php echo esc_html(Slab_Data::num($dl['ready_mix_wins_above_yd'])); ?> yards) where a truck usually beats bags, but still well under most plants' usual minimum of <?php echo esc_html(Slab_Data::range($dl['common_min_yd'], '–')); ?> yards. So the real comparison is bags against ready-mix plus a short-load fee:</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Route (4 in, <?php echo (int) $waste; ?>% waste)</th><th>Concrete cost</th></tr></thead>
<tbody>
<tr><td><?php echo (int) $e['bags80']; ?> × 80-lb bags at <?php echo esc_html(Slab_Data::money($bag, 2)); ?></td><td><?php echo esc_html($m($e['bagged'])); ?></td></tr>
<tr><td>Ready-mix + <?php echo esc_html($m($dl['fee_default'])); ?> delivery, no short-load fee</td><td><?php echo esc_html($m($e['ready_mix'])); ?></td></tr>
<tr><td>Ready-mix + flat short-load fee (<?php echo esc_html(Slab_Data::money_range($dl['short_load_flat'], 0, ' to ')); ?>)</td><td><?php echo esc_html($mr($rm_short)); ?></td></tr>
<tr><td>Ready-mix + per-yard short-load fee (<?php echo esc_html(Slab_Data::money_range($dl['short_load_per_yd'], 0, ' to ')); ?> per yard under a <?php echo esc_html(Slab_Data::num($dl['common_min_yd'][0])); ?>-yard minimum)</td><td><?php echo esc_html($mr($rm_short_yd)); ?></td></tr>
</tbody>
</table>
</div>
<p>Even with a short-load fee, ready-mix is close to or below the bag price, and it saves you from mixing about <?php echo esc_html(Slab_Data::num($tons, 1)); ?> tons of concrete by hand. Bags make sense only when a truck cannot get within chute reach (about <?php echo esc_html(Slab_Data::r('site.chute_reach_ft')); ?> ft) and a pump would cost more than the slab. Compare both with the <a href="<?php echo esc_url(home_url('/concrete-bags-calculator/')); ?>">concrete bags calculator</a> and the <a href="<?php echo esc_url(home_url('/ready-mix-concrete-calculator/')); ?>">ready-mix calculator</a>.</p>

<h2>Worked Example: 12x12 Shed Floor</h2>
<div class="formula-box">
<div class="formula-label">12 ft × 12 ft × 4 in</div>
<div class="formula-math">12 × 12 × (4 ÷ 12) = <?php echo esc_html(number_format($area * 4 / 12, 0)); ?> ft³ ÷ 27 = <?php echo esc_html(number_format($e['net_yd'], 2)); ?> yd³ → <?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³ with waste</div>
<div class="formula-explanation">Ready-mix <?php echo esc_html($m($e['ready_mix'])); ?> + forms <?php echo esc_html($m($e['forms'])); ?> + vapor barrier <?php echo esc_html($m($e['vapor'])); ?> + rebar <?php echo esc_html($m($e['rebar'])); ?> + broom finish <?php echo esc_html($m($e['broom'])); ?> + labor <?php echo esc_html($m($e['labor'])); ?> = <?php echo esc_html($m($e['direct'])); ?> direct cost.</div>
</div>
<p>The rebar grid at <?php echo (int) $sd['reinforcement']['rebar_base_spacing_in']; ?> in centers takes <?php echo (int) $grid['sticks']; ?> twenty-foot #4 bars and about <?php echo (int) $grid['chairs']; ?> chairs. One control joint each way splits the slab into four 6x6 panels, inside the usual limit of about <?php echo esc_html(Slab_Data::num($joint_ft)); ?> ft for a 4-inch slab. A contractor would quote somewhere between that direct cost and <?php echo esc_html($m($e['direct'] * $op)); ?> once overhead and profit are added. If the site needs digging out and a gravel base, add <?php echo esc_html($m($prep[0] * $area) . ' to ' . $m($prep[1] * $area)); ?>.</p>

<h2>DIY or Hire a Pro?</h2>
<p>A 12x12 patio or shed floor is a reasonable first DIY slab if you have two or three helpers and the truck can reach the forms. A crew spends roughly <?php echo esc_html(Slab_Data::num(round($crew_h, 1))); ?> crew-hours on it, which is a long half-day for beginners. The money saved is mostly labor: the gap between the DIY materials range (<?php echo esc_html($mr($c4['diy'])); ?>) and the installed range. A spa pad is the one to think twice about. It has to be flat, thick and right the first time, and a rework under a filled tub is expensive. Read our <a href="<?php echo esc_url(home_url('/diy-concrete-slab-what-you-can-and-shouldnt-do/')); ?>">DIY vs. contractor guide</a>, and run any quote through the <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">quote checker</a>.</p>
<p>Other sizes: <a href="<?php echo esc_url(home_url('/10x10-concrete-slab-cost-breakdown/')); ?>">10x10</a>, <a href="<?php echo esc_url(home_url('/20x20-concrete-slab-cost-breakdown/')); ?>">20x20</a> and <a href="<?php echo esc_url(home_url('/24x24-concrete-slab-cost-breakdown/')); ?>">24x24</a>, or price your exact dimensions in the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a>.</p>

<h2>Sources and Method</h2>
<p>Volumes use length × width × thickness with published bag yields. Costs use the same line items and default rates as our calculator (see the <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>); they are working benchmarks and vary by region and season. Thickness, vapor retarder and base guidance follow the International Residential Code (section R506) and ACI 302.1R. Hot tub pad requirements come from each manufacturer, so their installation manual has the final word.</p>
<?php
$html = ob_get_clean();

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'How much does a 12x12 concrete slab cost?',
            'a' => 'About ' . $mr($c4['installed']) . ' installed at 4 inches thick, or ' . $mr($c4['diy']) . ' for materials if you pour it yourself. Excavation and a gravel base are extra.',
        ],
        [
            'q' => 'How much does a 12x12 hot tub pad cost?',
            'a' => 'A 6-inch pad with a rebar grid costs about ' . $mr($spa) . ' installed. It needs ' . number_format($e6['order_yd'], 2) . ' cubic yards of concrete with ' . $waste . '% waste. Check the hot tub maker\'s pad requirements before you pour.',
        ],
        [
            'q' => 'How many bags of concrete for a 12x12 slab?',
            'a' => 'At 4 inches with ' . $waste . '% waste you need ' . $e['bags80'] . ' bags of 80-lb mix or ' . $e['bags60'] . ' bags of 60-lb mix. At 6 inches it is ' . $e6['bags80'] . ' bags of 80-lb mix.',
        ],
        [
            'q' => 'How many yards of concrete is a 12x12 slab?',
            'a' => 'A 12x12 slab 4 inches thick is ' . number_format($e['net_yd'], 2) . ' cubic yards. Order ' . number_format($e['order_yd'], 2) . ' yd³ with ' . $waste . '% waste. At 5 inches order ' . number_format($c5['est']['order_yd'], 2) . ' yd³, and at 6 inches ' . number_format($e6['order_yd'], 2) . ' yd³.',
        ],
        [
            'q' => 'Is ready-mix or bagged concrete cheaper for a 12x12 slab?',
            'a' => 'Usually ready-mix. Bags cost about ' . $m($e['bagged']) . ' for a 4-inch slab, while ready-mix costs about ' . $m($e['ready_mix']) . ' including delivery, or ' . $mr($rm_short) . ' if the supplier adds a flat short-load fee.',
        ],
    ],
];
