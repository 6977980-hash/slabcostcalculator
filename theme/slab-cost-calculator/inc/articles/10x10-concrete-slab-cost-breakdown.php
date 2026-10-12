<?php
/**
 * Article: 10x10 Concrete Slab Cost (post slug 10x10-concrete-slab-cost-breakdown).
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

$c4 = Slab_Data::size_costs(10, 10, 4);
$c5 = Slab_Data::size_costs(10, 10, 5);
$c6 = Slab_Data::size_costs(10, 10, 6);
$e = $c4['est'];
$basic = Slab_Data::estimate(10, 10, 4, 'basic');
$grid = Slab_Data::rebar_grid(10, 10, $sd['reinforcement']['rebar_base_spacing_in']);
$prep = Slab_Data::site_prep_psf();
$dl = $sd['delivery'];
$y = $sd['concrete']['bag_yields'];
$bag = $sd['concrete']['bag_price_default'];
$waste = $sd['concrete']['waste_pct'];
$order_cuft = $e['order_yd'] * 27;
$bags40 = Slab_Data::bags($order_cuft, '40');
$joint_ft = 4 * $sd['joints']['ratio_best'][1];
$crew_h = 100 * $sd['labor']['crew_hours_per_sqft'];

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> a 10x10 concrete slab (100 sq ft) at 4 inches thick costs about <strong><?php echo esc_html($mr($c4['installed'])); ?></strong> installed by a contractor, or <strong><?php echo esc_html($mr($c4['diy'])); ?></strong> in materials if you pour it yourself. It needs <?php echo esc_html(Slab_Data::num($e['net_yd'], 2)); ?> cubic yards of concrete, or <?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³ with <?php echo (int) $waste; ?>% waste, which is <?php echo (int) $e['bags80']; ?> bags of 80-lb mix.</p>

<p>A 10x10 slab is the classic size for a small shed, a hot tub or AC pad, a grill area or a landing outside a back door. It is small enough to pour by hand in a morning, but big enough that the choice between bags and a ready-mix truck really changes the price. This guide shows where every dollar goes, what changes at 5 and 6 inches, and how to decide whether to do it yourself.</p>

<h2>10x10 Slab Cost at a Glance</h2>
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

<p>Four inches is right for a shed, patio or walkway. Go to 5 inches if a riding mower, ATV or small car will sit on it, and 6 inches for a hot tub that holds a lot of water or for anything heavier. Each extra inch adds about a quarter more concrete, but labor and forms barely change, so the total rises more slowly than the volume.</p>

<h2>Line-Item Breakdown for a 4-Inch 10x10 Slab</h2>
<p>These are the same line items and default rates our <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a> uses. The full-scope total is the calculator's default scope, which a fair contractor price should sit close to before overhead and profit.</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Item</th><th>Basis</th><th>Cost</th></tr></thead>
<tbody>
<tr><td>Ready-mix concrete</td><td><?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³ at <?php echo esc_html(Slab_Data::money($sd['concrete']['ready_mix_default'])); ?>/yd³ + <?php echo esc_html($m($dl['fee_default'])); ?> delivery</td><td><?php echo esc_html($m($e['ready_mix'])); ?></td></tr>
<tr><td>Forms</td><td><?php echo (int) $e['perimeter']; ?> ft of edge at <?php echo esc_html(Slab_Data::money($sd['site']['forms_default'], 2)); ?>/ft</td><td><?php echo esc_html($m($e['forms'])); ?></td></tr>
<tr><td>Vapor barrier</td><td>100 sq ft at <?php echo esc_html(Slab_Data::money($sd['site']['vapor_default'], 2)); ?>/sq ft</td><td><?php echo esc_html($m($e['vapor'])); ?></td></tr>
<tr><td>#4 rebar grid</td><td><?php echo (int) $sd['reinforcement']['rebar_base_spacing_in']; ?> in centers, <?php echo (int) $grid['sticks']; ?> twenty-foot bars, <?php echo (int) $grid['chairs']; ?> chairs</td><td><?php echo esc_html($m($e['rebar'])); ?></td></tr>
<tr><td>Broom finish</td><td>100 sq ft at <?php echo esc_html(Slab_Data::money($sd['finish']['broom']['default'], 2)); ?>/sq ft</td><td><?php echo esc_html($m($e['broom'])); ?></td></tr>
<tr><td>Placing and finishing labor</td><td>100 sq ft at <?php echo esc_html(Slab_Data::money($sd['labor']['sqft_default'], 2)); ?>/sq ft</td><td><?php echo esc_html($m($e['labor'])); ?></td></tr>
<tr><td><strong>Direct cost (full scope)</strong></td><td>Before overhead and profit</td><td><strong><?php echo esc_html($m($e['direct'])); ?></strong></td></tr>
</tbody>
</table>
</div>
<p>A bare-bones version with no rebar or vapor barrier and labor at <?php echo esc_html(Slab_Data::money($sd['labor']['sqft_range'][0], 2)); ?>/sq ft comes to about <?php echo esc_html($m($basic['direct'])); ?>, which is the low end of the installed range. The high end adds <?php echo (int) round($sd['overhead_profit'] * 100); ?>% for contractor overhead and profit on the full scope. Excavation and a compacted gravel base are not in either number; budget roughly <?php echo esc_html(Slab_Data::money_range($prep, 2, ' to ')); ?> per sq ft (<?php echo esc_html($m($prep[0] * 100) . ' to ' . $m($prep[1] * 100)); ?> for 100 sq ft) if the ground needs digging out and a base brought in.</p>

<div class="callout callout-info">
<div class="callout-title">Why a small slab costs more per square foot</div>
<p>At 100 sq ft, fixed costs such as the truck delivery fee, a contractor's minimum charge and the time to set up forms are spread over very little area. That is why a 10x10 works out to <?php echo esc_html(Slab_Data::money_range($c4['installed_psf'], 2, ' to ')); ?> per sq ft installed, while the same work on a 20x20 slab averages <?php echo esc_html(Slab_Data::money_range(Slab_Data::installed_psf(), 0, ' to ')); ?>. Many contractors quote small pads as a flat job price rather than per foot.</p>
</div>

<h2>Bags or Ready-Mix for a 10x10?</h2>
<p>At <?php echo esc_html(number_format($e['order_yd'], 2)); ?> cubic yards, a 10x10 sits right on the line. Here is what each route takes for a 4-inch slab with <?php echo (int) $waste; ?>% waste:</p>
<ul>
<li><strong>80-lb bags:</strong> <?php echo (int) $e['bags80']; ?> bags (<?php echo esc_html(Slab_Data::num($y['80'], 2)); ?> cu ft each) at about <?php echo esc_html(Slab_Data::money($bag, 2)); ?> = <?php echo esc_html($m($e['bagged'])); ?>. That is over <?php echo esc_html(number_format($e['bags80'] * 80 / 2000, 1)); ?> tons to lift, mix and place.</li>
<li><strong>60-lb bags:</strong> <?php echo (int) $e['bags60']; ?> bags; <strong>40-lb bags:</strong> <?php echo (int) $bags40; ?> bags. Smaller bags are easier to carry but cost more per cubic foot.</li>
<li><strong>Ready-mix:</strong> about <?php echo esc_html($m($e['ready_mix'])); ?> including the delivery fee, if the supplier will bring a small load. Many plants have a minimum of <?php echo esc_html(Slab_Data::range($dl['common_min_yd'], '–')); ?> yards and add a short-load fee (often <?php echo esc_html(Slab_Data::money_range($dl['short_load_flat'], 0, ' to ')); ?> flat, or <?php echo esc_html(Slab_Data::money_range($dl['short_load_per_yd'], 0, ' to ')); ?> per yard you are short), so call two suppliers before deciding.</li>
</ul>
<p>Rule of thumb: below about <?php echo esc_html(Slab_Data::num($dl['bags_win_below_yd'])); ?> cubic yard, bags usually win. Above about <?php echo esc_html(Slab_Data::num($dl['ready_mix_wins_above_yd'])); ?> yards, a truck usually wins. A 10x10 is in between, so the deciding factors are the short-load fee in your area, whether the truck can reach the forms (a chute reaches about <?php echo esc_html(Slab_Data::range($sd['site']['chute_reach_ft'], ' to ')); ?> ft), and whether you have the help to mix <?php echo (int) $e['bags80']; ?> bags before the first ones start to set. A rented mixer and three people make the bag route realistic. Our <a href="<?php echo esc_url(home_url('/ready-mix-vs-bagged-concrete/')); ?>">ready-mix vs. bagged concrete guide</a> walks through the break-even in more detail.</p>

<h2>DIY Materials List for a 10x10 Slab</h2>
<ul>
<li><strong>Concrete:</strong> <?php echo (int) $e['bags80']; ?> × 80-lb bags, or <?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³ ready-mix (order to the nearest quarter yard the supplier allows).</li>
<li><strong>Gravel base:</strong> about <?php echo esc_html(number_format(100 * $sd['site']['gravel_depth_in'] / 12 / 27 * $sd['site']['gravel_compaction'], 2)); ?> yd³ of crushed stone for a <?php echo (int) $sd['site']['gravel_depth_in']; ?>-inch compacted base (includes <?php echo (int) round(($sd['site']['gravel_compaction'] - 1) * 100); ?>% for compaction). See the <a href="<?php echo esc_url(home_url('/gravel-base-calculator/')); ?>">gravel base calculator</a>.</li>
<li><strong>Forms:</strong> <?php echo (int) $e['perimeter']; ?> linear ft of 2x4 (or 2x6 for a 5 or 6-inch slab), plus stakes every <?php echo esc_html(Slab_Data::r('site.stake_spacing_ft')); ?> ft and screws.</li>
<li><strong>Reinforcement:</strong> <?php echo (int) $grid['sticks']; ?> bars of #4 rebar at <?php echo (int) $sd['reinforcement']['rebar_base_spacing_in']; ?> in centers and about <?php echo (int) $grid['chairs']; ?> chairs, or a 10x10 area of welded wire mesh on chairs for light foot-traffic use. The <a href="<?php echo esc_url(home_url('/concrete-rebar-calculator/')); ?>">rebar calculator</a> lays out the grid.</li>
<li><strong>Vapor barrier:</strong> 6-mil polyethylene if the slab will be under a shed or other enclosed space.</li>
<li><strong>Tools:</strong> mixer (rent), wheelbarrow, screed board, bull float, edger, groover, broom, and curing compound or plastic sheeting.</li>
</ul>

<h2>Worked Example</h2>
<div class="formula-box">
<div class="formula-label">10 ft × 10 ft × 4 in</div>
<div class="formula-math">10 × 10 × (4 ÷ 12) = <?php echo esc_html(number_format(100 * 4 / 12, 2)); ?> ft³ ÷ 27 = <?php echo esc_html(number_format($e['net_yd'], 2)); ?> yd³</div>
<div class="formula-explanation">Add <?php echo (int) $waste; ?>% waste: <?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³ = <?php echo esc_html(number_format($order_cuft, 1)); ?> ft³. Divide by <?php echo esc_html(Slab_Data::num($y['80'], 2)); ?> ft³ per 80-lb bag and round up: <?php echo (int) $e['bags80']; ?> bags.</div>
</div>
<p>One control joint across the middle in each direction splits the slab into four 5x5 panels. That keeps every panel well inside the usual rule of <?php echo esc_html(Slab_Data::range($sd['joints']['ratio'])); ?> times the thickness in inches, in feet (no more than about <?php echo esc_html(Slab_Data::num($joint_ft)); ?> ft for a 4-inch slab). Cut or tool the joints to a quarter of the slab depth.</p>

<h2>What Pushes the Price Up</h2>
<ul>
<li><strong>Site prep:</strong> digging out sod and soft soil and bringing in a gravel base adds roughly <?php echo esc_html($m($prep[0] * 100) . ' to ' . $m($prep[1] * 100)); ?> on 100 sq ft.</li>
<li><strong>Access:</strong> if the truck cannot get within chute reach, a line pump costs <?php echo esc_html(Slab_Data::money_range($sd['pump']['line_job'], 0, ' to ')); ?> for the job, which can double the price of a small pad. Wheelbarrowing is the cheaper alternative.</li>
<li><strong>Finish:</strong> a stamped finish runs about <?php echo esc_html(Slab_Data::money_range($sd['finish']['stamped']['range'], 2, ' to ')); ?> per sq ft instead of <?php echo esc_html(Slab_Data::money_range($sd['finish']['broom']['range'], 2, ' to ')); ?> for broom, and the finishing labor rate rises too.</li>
<li><strong>Old slab removal:</strong> breaking out and hauling away an existing 4-inch slab runs about <?php echo esc_html(Slab_Data::money_range($sd['site']['demo_range'], 0, ' to ')); ?> per sq ft. Try the <a href="<?php echo esc_url(home_url('/concrete-removal-cost-calculator/')); ?>">removal cost calculator</a>.</li>
<li><strong>Permits:</strong> many towns do not require one for a small detached pad, but some do, at roughly <?php echo esc_html(Slab_Data::money_range($sd['permit']['flatwork'], 0, ' to ')); ?>. See <a href="<?php echo esc_url(home_url('/concrete-slab-permits-and-costs/')); ?>">do you need a permit for a concrete slab</a>.</li>
</ul>

<h2>DIY or Hire a Pro?</h2>
<p>A 10x10 is the most DIY-friendly slab size there is. The concrete fits in one session, the screed board is short, and a mistake costs hundreds rather than thousands. A contractor crew will spend roughly <?php echo esc_html(Slab_Data::num($crew_h, 1)); ?> crew-hours placing and finishing it, and most of what you pay for is their setup time and minimum charge. Hire out if the slab will carry a structure that has to be level (a prefab shed with a tight floor tolerance), if the site needs real excavation, or if you have never finished concrete and the slab will be on show. Our <a href="<?php echo esc_url(home_url('/diy-concrete-slab-what-you-can-and-shouldnt-do/')); ?>">DIY vs. contractor guide</a> covers the trade-offs, and if you already have a quote, check it against a fair price with the <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">concrete quote checker</a>.</p>

<p>Comparing sizes? See the <a href="<?php echo esc_url(home_url('/12x12-concrete-slab-cost-breakdown/')); ?>">12x12</a>, <a href="<?php echo esc_url(home_url('/20x20-concrete-slab-cost-breakdown/')); ?>">20x20</a> and <a href="<?php echo esc_url(home_url('/24x24-concrete-slab-cost-breakdown/')); ?>">24x24</a> breakdowns, or the <a href="<?php echo esc_url(home_url('/concrete-cost-per-square-foot-pricing-matrix/')); ?>">cost per square foot matrix</a>.</p>

<h2>Sources and Method</h2>
<p>Quantities use the standard volume formula and published bag yields. Prices are this site's working benchmarks (see our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>), and they vary by region and season. Thickness and base guidance follows the International Residential Code (section R506, concrete floors on ground) and ACI 302.1R, the American Concrete Institute's guide to floor and slab construction.</p>
<?php
$html = ob_get_clean();

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'How much does a 10x10 concrete slab cost?',
            'a' => 'About ' . $mr($c4['installed']) . ' installed at 4 inches thick, or ' . $mr($c4['diy']) . ' for materials if you do it yourself. Site prep and a gravel base are extra.',
        ],
        [
            'q' => 'How many bags of concrete do I need for a 10x10 slab?',
            'a' => 'For 4 inches thick with ' . $waste . '% waste: ' . $e['bags80'] . ' bags of 80-lb mix or ' . $e['bags60'] . ' bags of 60-lb mix. At 5 inches it is ' . $c5['est']['bags80'] . ' bags of 80-lb mix, and at 6 inches ' . $c6['est']['bags80'] . '.',
        ],
        [
            'q' => 'How many yards of concrete is a 10x10 slab?',
            'a' => 'A 10x10 slab 4 inches thick is ' . number_format($e['net_yd'], 2) . ' cubic yards. Order ' . number_format($e['order_yd'], 2) . ' yd³ to allow ' . $waste . '% for waste and uneven ground.',
        ],
        [
            'q' => 'Is it cheaper to use bags or ready-mix for a 10x10 slab?',
            'a' => 'It is close. Bags cost about ' . $m($e['bagged']) . ' for a 4-inch slab, and ready-mix about ' . $m($e['ready_mix']) . ' including delivery, but many suppliers add a short-load fee for small orders. Price both before you decide.',
        ],
        [
            'q' => 'How thick should a 10x10 slab be?',
            'a' => 'Four inches for a shed, patio or walkway. Five inches if a vehicle or riding mower will park on it, and 6 inches for a large hot tub or heavier loads.',
        ],
    ],
];
