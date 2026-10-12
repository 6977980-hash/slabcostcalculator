<?php
/**
 * Article: How Much Does a Concrete Slab Cost (post slug how-much-does-a-concrete-slab-cost).
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
$pct = function ($part, $whole) {
    return (int) round($part / $whole * 100);
};

$psf = Slab_Data::installed_psf();
$diy_psf = Slab_Data::diy_psf();
$c20 = Slab_Data::size_costs(20, 20, 4);
$c20_5 = Slab_Data::size_costs(20, 20, 5);
$c20_6 = Slab_Data::size_costs(20, 20, 6);
$e = $c20['est'];
$rows = Slab_Data::size_table();
$op = $sd['overhead_profit'];
$prep = Slab_Data::site_prep_psf();
$dl = $sd['delivery'];
$f = $sd['finish'];
$lab = $sd['labor'];

// Project types, each priced on its reference slab.
$drive = Slab_Data::size_costs(40, 20, 5);
$garage = Slab_Data::size_costs(24, 24, 5);
$found_psf = Slab_Data::foundation_psf();
$stamped_psf = Slab_Data::stamped_psf();
$projects = [
    ['Patio or shed pad', '20 × 20 ft, 4 in', $psf, $c20['installed']],
    ['Driveway (2-car)', '20 × 40 ft, 5 in', Slab_Data::driveway_psf(), $drive['installed']],
    ['Garage floor', '24 × 24 ft, 5 in', Slab_Data::garage_psf(), $garage['installed']],
    ['Monolithic house or shop foundation', '30 × 40 ft, 4 in floor, thickened edge', $found_psf, [$found_psf[0] * 1200, $found_psf[1] * 1200]],
    ['Stamped patio', '20 × 20 ft, 4 in', $stamped_psf, [$stamped_psf[0] * 400, $stamped_psf[1] * 400]],
];

// Line-item shares of the full-scope 20x20 direct cost.
$items = [
    ['Placing and finishing labor', $e['labor']],
    ['Ready-mix concrete and delivery', $e['ready_mix']],
    ['#4 rebar grid', $e['rebar']],
    ['Broom finish', $e['broom']],
    ['Forms', $e['forms']],
    ['Vapor barrier', $e['vapor']],
];

// Factors on the 400 sq ft reference slab.
$psi4k = Slab_Data::round_out(Slab_Data::psi_extra(4000, $e['order_yd']), 10);
$stamp_fin = 400 * ($f['stamped']['default'] - $f['broom']['default']);
$stamp_add = Slab_Data::round_pair([$stamp_fin, $stamp_fin * (1 + $op)], 10);
$difficult_add = 400 * ($lab['sqft_difficult'] - $lab['sqft_default']);
$fair = $c20['bid_fair'];

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> a concrete slab costs about <strong><?php echo esc_html(Slab_Data::money_range($psf, 0, ' to ')); ?> per square foot</strong> installed in 2026, or <strong><?php echo esc_html(Slab_Data::money_range($diy_psf, 2, ' to ')); ?> per square foot</strong> in materials if you pour it yourself. A typical 20x20 slab (400 sq ft, 4 inches thick) runs <strong><?php echo esc_html($mr($c20['installed'])); ?></strong> installed. Driveways and garage floors cost more in total because they are thicker and larger, and small pads cost more per foot because fixed costs are spread over less area.</p>

<p>That headline range is a starting point, not a quote. The real price of your slab depends on its size, thickness and use, what is under it, how the truck gets to it, and how it is finished. This guide gives the benchmarks by size and by project type, shows where the money goes, explains what moves the price up or down in your area, and shows how to read a contractor's quote line by line. Every figure here comes from the same rates our <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a> uses, so you can plug in your own dimensions and get the same answer.</p>

<h2>Concrete Slab Cost by Size</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Size (4 in thick)</th><th>Area</th><th>Concrete to order</th><th>DIY materials</th><th>Installed</th><th>Installed per sq ft</th></tr></thead>
<tbody>
<?php foreach ($rows as $r) : ?>
<tr><td><strong><?php echo esc_html($r['label']); ?></strong></td><td><?php echo esc_html(number_format($r['est']['area'])); ?> sq ft</td><td><?php echo esc_html(number_format($r['est']['order_yd'], 2)); ?> yd³</td><td><?php echo esc_html($mr($r['diy'])); ?></td><td><?php echo esc_html($mr($r['installed'])); ?></td><td><?php echo esc_html(Slab_Data::money_range($r['installed_psf'], 2, ' to ')); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption"><?php echo esc_html(Slab_Data::cost_basis_note()); ?> Concrete includes <?php echo (int) $sd['concrete']['waste_pct']; ?>% waste.</div>
</div>
<p>Notice how the per-square-foot price falls as slabs get bigger. A delivery fee, a contractor's setup time and the forms around the edge are all paid once per job, so a 10x10 pad carries them on 100 sq ft while a 30x40 shop spreads them over 1,200 sq ft. For a closer look at the sizes people ask about most, see the <a href="<?php echo esc_url(home_url('/10x10-concrete-slab-cost-breakdown/')); ?>">10x10</a>, <a href="<?php echo esc_url(home_url('/12x12-concrete-slab-cost-breakdown/')); ?>">12x12</a>, <a href="<?php echo esc_url(home_url('/20x20-concrete-slab-cost-breakdown/')); ?>">20x20</a> and <a href="<?php echo esc_url(home_url('/24x24-concrete-slab-cost-breakdown/')); ?>">24x24</a> breakdowns.</p>

<h2>Cost by Thickness</h2>
<p>Thickness changes only the concrete line, so it moves the total less than people expect. On the 20x20 reference slab:</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Thickness</th><th>Typical use</th><th>Concrete to order</th><th>Installed</th></tr></thead>
<tbody>
<tr><td><strong>4 in</strong></td><td>Patios, walkways, shed floors</td><td><?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³</td><td><?php echo esc_html($mr($c20['installed'])); ?></td></tr>
<tr><td><strong>5 in</strong></td><td>Driveways, garage floors</td><td><?php echo esc_html(number_format($c20_5['est']['order_yd'], 2)); ?> yd³</td><td><?php echo esc_html($mr($c20_5['installed'])); ?></td></tr>
<tr><td><strong>6 in</strong></td><td>RV pads, shops, heavy hot tubs</td><td><?php echo esc_html(number_format($c20_6['est']['order_yd'], 2)); ?> yd³</td><td><?php echo esc_html($mr($c20_6['installed'])); ?></td></tr>
</tbody>
</table>
</div>
<p>IRC section R506 sets 3.5 inches as the minimum for a concrete floor on ground. Our <a href="<?php echo esc_url(home_url('/how-thick-should-a-concrete-slab-be/')); ?>">thickness guide</a> explains which thickness fits which load.</p>

<h2>Cost by Project Type</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Project</th><th>Reference slab</th><th>Per sq ft installed</th><th>Typical total</th></tr></thead>
<tbody>
<?php foreach ($projects as $p) : ?>
<tr><td><strong><?php echo esc_html($p[0]); ?></strong></td><td><?php echo esc_html($p[1]); ?></td><td><?php echo esc_html(Slab_Data::money_range($p[2], 0, ' to ')); ?></td><td><?php echo esc_html($mr(Slab_Data::round_pair($p[3], 10))); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">Per-square-foot ranges are rounded outward to whole dollars. Totals for the foundation and stamped rows are the per-square-foot range times the reference area.</div>
</div>
<ul>
<li><strong>Patios</strong> are the simplest slab: 4 inches, often mesh instead of rebar, and no vapor barrier. Finish is the big variable. See the <a href="<?php echo esc_url(home_url('/concrete-patio-slab-cost-guide/')); ?>">patio cost guide</a>.</li>
<li><strong>Driveways</strong> are 5 inches with rebar and usually <?php echo esc_html(number_format($sd['code']['psi_severe'])); ?> to <?php echo esc_html(number_format($sd['code']['psi_deicing_recommended'])); ?> PSI air-entrained concrete in cold climates. See the <a href="<?php echo esc_url(home_url('/how-much-does-a-concrete-driveway-cost/')); ?>">driveway cost guide</a>.</li>
<li><strong>Garage floors</strong> match driveways in thickness but add a vapor barrier, and the walls need footings. See the <a href="<?php echo esc_url(home_url('/how-much-does-a-garage-slab-cost/')); ?>">garage slab cost guide</a>.</li>
<li><strong>Foundations</strong> add a thickened perimeter footing poured with the floor; frost depth decides whether that is allowed. See the <a href="<?php echo esc_url(home_url('/concrete-foundation-monolithic-slab-cost/')); ?>">monolithic slab guide</a>.</li>
</ul>

<h2>Where the Money Goes</h2>
<p>Here is the full-scope 20x20 slab broken into its line items. This is the calculator's default scope and the benchmark our quote checker compares bids against:</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Line item</th><th>Cost</th><th>Share of direct cost</th></tr></thead>
<tbody>
<?php foreach ($items as $it) : ?>
<tr><td><?php echo esc_html($it[0]); ?></td><td><?php echo esc_html($m($it[1])); ?></td><td><?php echo (int) $pct($it[1], $e['direct']); ?>%</td></tr>
<?php endforeach; ?>
<tr><td><strong>Direct cost</strong></td><td><strong><?php echo esc_html($m($e['direct'])); ?></strong></td><td>All</td></tr>
</tbody>
</table>
</div>
<p>Labor is the biggest single line, close to half the direct cost, and it is also the line that varies most between regions. Concrete is the second. Contractors then add overhead and profit; a markup of up to about <?php echo (int) round($op * 100); ?>% over direct cost is normal. Want the same numbers per unit? The <a href="<?php echo esc_url(home_url('/concrete-cost-per-square-foot-pricing-matrix/')); ?>">cost per square foot matrix</a> breaks them down by area, and the <a href="<?php echo esc_url(home_url('/concrete-cost-per-cubic-yard-pricing-explained/')); ?>">cost per cubic yard guide</a> covers ready-mix pricing by strength.</p>

<h2>What Moves the Price in Your Area</h2>
<p>The benchmarks above assume an open, fairly flat site with good truck access. These are the factors that push a real job up or down, with their effect on the 400 sq ft reference slab:</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Factor</th><th>Typical rate</th><th>Effect on a 20x20</th></tr></thead>
<tbody>
<tr><td>Excavation and compacted gravel base</td><td><?php echo esc_html(Slab_Data::money_range($prep, 2, ' to ')); ?>/sq ft</td><td><?php echo esc_html($m($prep[0] * 400) . ' to ' . $m($prep[1] * 400)); ?></td></tr>
<tr><td>Removing an old slab</td><td><?php echo esc_html(Slab_Data::mr('site.demo_range', 0, ' to ')); ?>/sq ft</td><td><?php echo esc_html($m($sd['site']['demo_range'][0] * 400) . ' to ' . $m($sd['site']['demo_range'][1] * 400)); ?></td></tr>
<tr><td>Pump when the truck cannot reach</td><td>Line pump per job</td><td><?php echo esc_html(Slab_Data::mr('pump.line_job', 0, ' to ')); ?></td></tr>
<tr><td>Stronger concrete (<?php echo esc_html(number_format(4000)); ?> PSI)</td><td><?php echo esc_html(Slab_Data::mr('concrete.psi_step_price', 0, ' to ')); ?>/yd³ per 500 PSI</td><td><?php echo esc_html($mr($psi4k)); ?></td></tr>
<tr><td>Hard site (slope, tight access, wheelbarrows)</td><td>Labor <?php echo esc_html(Slab_Data::money($lab['sqft_difficult'], 2)); ?>/sq ft instead of <?php echo esc_html(Slab_Data::money($lab['sqft_default'], 2)); ?></td><td>about <?php echo esc_html($m($difficult_add)); ?></td></tr>
<tr><td>Stamped finish instead of broom</td><td><?php echo esc_html(Slab_Data::money($f['stamped']['default'], 2)); ?>/sq ft instead of <?php echo esc_html(Slab_Data::money($f['broom']['default'], 2)); ?></td><td><?php echo esc_html($mr($stamp_add)); ?></td></tr>
<tr><td>Short-load fee on small orders</td><td>Flat fee</td><td><?php echo esc_html(Slab_Data::mr('delivery.short_load_flat', 0, ' to ')); ?></td></tr>
<tr><td>Permit (where required)</td><td>Flatwork permit</td><td><?php echo esc_html(Slab_Data::mr('permit.flatwork', 0, ' to ')); ?></td></tr>
</tbody>
</table>
</div>
<p>Regional differences show up mostly in labor and ready-mix prices. Finishers earn roughly <?php echo esc_html(Slab_Data::mr('labor.worker_hour', 0, ' to ')); ?> an hour depending on the market, and ready-mix for standard <?php echo esc_html(number_format($sd['concrete']['psi_base'])); ?> PSI concrete runs about <?php echo esc_html(Slab_Data::mr('concrete.psi_base_price', 0, ' to ')); ?> per yard. Large metro areas and remote rural areas both tend to sit at the high end: one for labor, the other for delivery distance. Season matters too. Below about <?php echo (int) $sd['weather']['cold_f']; ?>°F, ACI 306R cold-weather practice calls for protection such as blankets or heated enclosures, and many crews add a charge for it. In spring, busy contractors quote higher. Our <a href="<?php echo esc_url(home_url('/best-time-of-year-to-pour-concrete/')); ?>">guide to the best time to pour</a> covers the timing side.</p>

<h2>DIY vs. Hiring a Contractor</h2>
<p>Doing it yourself saves the labor line and the contractor's markup, which together are most of the bill. On the 20x20, DIY materials run <?php echo esc_html($mr($c20['diy'])); ?> against <?php echo esc_html($mr($c20['installed'])); ?> installed. The trade-off is risk: concrete gives you one chance, and a slab that is out of level or badly finished costs more to tear out than the labor you saved. Small pads up to about 12x12 are realistic first projects. Driveways, garage floors and anything structural are usually better hired out. See <a href="<?php echo esc_url(home_url('/diy-concrete-slab-what-you-can-and-shouldnt-do/')); ?>">what you can and shouldn't DIY</a>.</p>

<h2>How to Read a Concrete Quote</h2>
<p>Two quotes for the "same" slab can differ by thousands because they are not quoting the same job. Before comparing prices, make sure each quote states:</p>
<ul>
<li><strong>Dimensions, thickness and area.</strong> A 4-inch and a 5-inch slab are different jobs.</li>
<li><strong>Concrete strength and volume.</strong> PSI grade, whether it is air-entrained, and the yards ordered.</li>
<li><strong>Reinforcement.</strong> Rebar size and spacing, or mesh, and whether it sits on chairs.</li>
<li><strong>Base and site prep.</strong> Excavation depth, gravel type and thickness, and compaction. This is the line most often left out.</li>
<li><strong>Vapor barrier, finish, control joints and curing.</strong> Joint spacing and timing of saw cuts.</li>
<li><strong>Extras:</strong> pump, demolition, haul-off, permit, and who pays if the truck waits.</li>
</ul>
<p>As a rough test, a fair full-scope price for the 20x20 reference slab sits between its direct cost of <?php echo esc_html($m($fair[0])); ?> and that plus <?php echo (int) round($op * 100); ?>% overhead and profit, <?php echo esc_html($m($fair[1])); ?>, before site prep. A quote well above that should come with a reason (difficult access, a deep base, a decorative finish). One well below it may be leaving something out. Our <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">concrete quote checker</a> runs this test for your exact slab.</p>

<h2>Sources and Method</h2>
<p>Costs are built from line items (ready-mix, delivery, forms, vapor barrier, rebar, finish and labor) at this site's working benchmark rates; our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a> explains where they come from. They are planning figures, not quotes, and local prices vary by region and season. Code and practice references: International Residential Code section R506 (slabs on ground), section R403 (footings) and Table R402.2 (concrete strength); ACI 302.1R (floor and slab construction); ACI 306R (cold weather); ASTM C94 (ready-mixed concrete).</p>
<?php
$html = ob_get_clean();

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'How much does a concrete slab cost per square foot?',
            'a' => 'About ' . Slab_Data::money_range($psf, 0, ' to ') . ' per square foot installed, or ' . Slab_Data::money_range($diy_psf, 2, ' to ') . ' per square foot for materials if you do it yourself. Small slabs cost more per foot, and site prep is extra.',
        ],
        [
            'q' => 'How much does a 20x20 concrete slab cost?',
            'a' => 'A 20x20 slab 4 inches thick costs about ' . $mr($c20['installed']) . ' installed, or ' . $mr($c20['diy']) . ' in DIY materials. At 5 inches, for a garage floor, it is about ' . $mr($c20_5['installed']) . '.',
        ],
        [
            'q' => 'What is the most expensive part of a concrete slab?',
            'a' => 'Labor. On a 20x20 slab, placing and finishing labor is about ' . $pct($e['labor'], $e['direct']) . '% of the direct cost, and ready-mix concrete with delivery is about ' . $pct($e['ready_mix'], $e['direct']) . '%.',
        ],
        [
            'q' => 'Is site prep included in the price of a concrete slab?',
            'a' => 'Often not. Excavation and a compacted gravel base add about ' . Slab_Data::money_range($prep, 2, ' to ') . ' per square foot. Check that your quote lists base depth and compaction.',
        ],
        [
            'q' => 'How much does a concrete foundation slab cost?',
            'a' => 'A monolithic slab foundation with a thickened edge costs about ' . Slab_Data::money_range($found_psf, 0, ' to ') . ' per square foot. Frost depth in your area decides whether a thickened edge is allowed or a deeper footing is required.',
        ],
        [
            'q' => 'How much more does a stamped concrete slab cost?',
            'a' => 'A stamped slab costs about ' . Slab_Data::money_range($stamped_psf, 0, ' to ') . ' per square foot installed, compared with ' . Slab_Data::money_range($psf, 0, ' to ') . ' for a broom finish.',
        ],
    ],
];
