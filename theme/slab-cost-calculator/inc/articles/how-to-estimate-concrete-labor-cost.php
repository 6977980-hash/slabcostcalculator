<?php
/**
 * Article: How to Estimate Concrete Labor Cost (post slug how-to-estimate-concrete-labor-cost).
 * Loaded by inc/post-articles.php. Every figure comes from Slab_Data.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$sd = Slab_Data::all();
$L = $sd['labor'];
$m = function ($n) {
    return Slab_Data::money($n, 0);
};
$mr = function ($pair) {
    return Slab_Data::money_range($pair, 0, ' to ');
};
$m2 = function ($n) {
    return Slab_Data::money($n, 2);
};

$ch = $L['crew_hour_default'];
$rate_std = $L['crew_hours_per_sqft'];
$rate_stamp = $L['crew_hours_per_sqft_stamped'];
$rate_diff = $L['crew_hours_per_sqft_difficult'];

$sizes = [
    [10, 10, '10 × 10 pad'],
    [12, 12, '12 × 12 patio'],
    [20, 20, '20 × 20 patio or pad'],
    [24, 24, '24 × 24 garage floor'],
    [20, 40, '20 × 40 driveway'],
    [30, 40, '30 × 40 shop floor'],
];

// Worked example: 24x24 garage floor, broom finish.
$a = 576;
$ex_h = $a * $rate_std;
$ex_worker_h = [$ex_h * $L['crew_size'][0], $ex_h * $L['crew_size'][1]];
$ex_crew = $ex_h * $ch;
$ex_crew_range = [$ex_h * $L['crew_hour'][0], $ex_h * $L['crew_hour'][1]];
$ex_sqft = $a * $L['sqft_default'];
$ex_sqft_range = [$a * $L['sqft_range'][0], $a * $L['sqft_range'][1]];
$c24 = Slab_Data::size_costs(24, 24, 5);
$labor_share = $c24['est']['labor'] / $c24['est']['direct'] * 100;

ob_start();
?>
<p class="hero-answer"><strong>Quick answer:</strong> concrete placing and finishing labor runs about <strong><?php echo esc_html(Slab_Data::mr('labor.sqft_range', 2, ' to ')); ?> per sq ft</strong> for a broom-finished slab, with <?php echo esc_html($m2($L['sqft_default'])); ?> a fair middle figure. By the hour, a <?php echo esc_html(Slab_Data::r('labor.crew_size')); ?>-person flatwork crew costs <?php echo esc_html(Slab_Data::mr('labor.crew_hour', 0, ' to ')); ?> per crew-hour (<?php echo esc_html(Slab_Data::mr('labor.worker_hour', 0, ' to ')); ?> per finisher) and needs about <?php echo esc_html(Slab_Data::num($rate_std * 100, 1)); ?> crew-hours per 100 sq ft. Stamped finishes and hard-to-reach sites cost more. Labor is often the largest single line on a slab quote.</p>

<p>Concrete labor is hard to price because the work is lumpy: a crew spends hours on forms and setup, then races the clock once the truck arrives. That is why contractors price it two different ways, and why a small pad can cost nearly as much in labor as one twice its size. This guide shows both methods, gives crew-hour figures for common slab sizes, and explains what moves the number up or down.</p>

<h2>Two Ways to Price Concrete Labor</h2>
<h3>1. Per square foot</h3>
<p>Most residential flatwork contractors quote labor per square foot of slab, because it is easy to compare and scales with the job. Typical rates:</p>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Type of work</th><th>Labor per sq ft</th><th>What is included</th></tr></thead>
<tbody>
<tr><td>Broom-finish slab, easy access</td><td><?php echo esc_html(Slab_Data::mr('labor.sqft_range', 2, ' to ')); ?> (default <?php echo esc_html($m2($L['sqft_default'])); ?>)</td><td>Setting forms, placing, screeding, floating, edging, jointing, broom finish</td></tr>
<tr><td>Difficult access or complex shape</td><td>About <?php echo esc_html($m2($L['sqft_difficult'])); ?></td><td>Wheelbarrowing or pumping, slopes, curves, many corners or steps</td></tr>
<tr><td>Stamped or decorative</td><td>About <?php echo esc_html($m2($L['sqft_stamped'])); ?></td><td>Color, release agent, stamping mats, detailing, sealing</td></tr>
</tbody>
</table>
</div>

<h3>2. Per crew-hour</h3>
<p>Estimators who build a bid from the ground up count hours instead. A crew-hour is one hour of the whole crew working. A typical flatwork crew is <?php echo esc_html(Slab_Data::r('labor.crew_size')); ?> finishers at <?php echo esc_html(Slab_Data::mr('labor.worker_hour', 0, ' to ')); ?> per hour each, which makes <?php echo esc_html(Slab_Data::mr('labor.crew_hour', 0, ' to ')); ?> per crew-hour depending on crew size, wages and region. Our calculator uses <?php echo esc_html($m($ch)); ?>.</p>
<div class="formula-box">
<div class="formula-label">Crew-hour method</div>
<div class="formula-math">Labor = area (sq ft) × crew-hours per sq ft × crew rate per hour</div>
<div class="formula-explanation">Production rates: about <?php echo esc_html(Slab_Data::num($rate_std, 3)); ?> crew-hours per sq ft for a standard broom finish, <?php echo esc_html(Slab_Data::num($rate_diff, 3)); ?> for difficult access, and <?php echo esc_html(Slab_Data::num($rate_stamp, 3)); ?> for stamped work. At <?php echo esc_html($m($ch)); ?> per crew-hour, the standard rate works out to <?php echo esc_html($m2($rate_std * $ch)); ?> per sq ft, close to the per-sq-ft method.</div>
</div>
<p>The two methods land near each other on average jobs. The per-sq-ft method is simpler; the crew-hour method shows you why small jobs and complicated jobs cost more, because hours do not shrink in proportion to the area. Try both in the <a href="<?php echo esc_url(home_url('/concrete-labor-cost-calculator/')); ?>">concrete labor cost calculator</a>.</p>

<h2>Crew Hours and Labor Cost by Slab Size</h2>
<div class="table-responsive">
<table class="data-table">
<thead><tr><th>Slab</th><th>Area</th><th>Crew-hours (standard)</th><th>Labor at <?php echo esc_html($m($ch)); ?>/crew-hr</th><th>Labor at <?php echo esc_html($m2($L['sqft_default'])); ?>/sq ft</th><th>Crew-hours (stamped)</th><th>Crew-hours (difficult)</th></tr></thead>
<tbody>
<?php foreach ($sizes as $z) :
    $ar = $z[0] * $z[1];
    ?>
<tr><td><strong><?php echo esc_html($z[2]); ?></strong></td><td><?php echo esc_html(number_format($ar)); ?> sq ft</td><td><?php echo esc_html(Slab_Data::num($ar * $rate_std, 1)); ?></td><td><?php echo esc_html($m($ar * $rate_std * $ch)); ?></td><td><?php echo esc_html($m($ar * $L['sqft_default'])); ?></td><td><?php echo esc_html(Slab_Data::num($ar * $rate_stamp, 1)); ?></td><td><?php echo esc_html(Slab_Data::num($ar * $rate_diff, 1)); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="table-caption">Placing and finishing only. Excavation, base work and demolition are usually priced separately. Small jobs often carry a minimum charge, so the 10x10 and 12x12 figures are a floor, not a quote.</div>
</div>
<p>To turn crew-hours into finisher-hours, multiply by the crew size. A 20x20 slab needing <?php echo esc_html(Slab_Data::num(400 * $rate_std, 1)); ?> crew-hours with a <?php echo (int) $L['crew_size'][1]; ?>-person crew is <?php echo esc_html(Slab_Data::num(400 * $rate_std * $L['crew_size'][1])); ?> finisher-hours. That matters if you are hiring day labor by the hour instead of a crew by the job.</p>

<h2>What Drives Labor Cost Up or Down</h2>
<ul>
<li><strong>Finish.</strong> A broom finish is the fastest. A smooth steel-trowel finish for a garage or shop takes more passes. Stamped concrete needs more people on the slab at once and more hours: <?php echo esc_html(Slab_Data::num($rate_stamp, 3)); ?> crew-hours per sq ft against <?php echo esc_html(Slab_Data::num($rate_std, 3)); ?>. The finish materials themselves are a separate line (<?php echo esc_html(Slab_Data::mr('finish.broom.range', 2, ' to ')); ?> per sq ft for broom, <?php echo esc_html(Slab_Data::mr('finish.stamped.range', 2, ' to ')); ?> for stamped).</li>
<li><strong>Access.</strong> If the truck can back up and the chute reaches the forms (about <?php echo esc_html(Slab_Data::r('site.chute_reach_ft')); ?> ft), the crew just guides concrete into place. A backyard behind a fence means wheelbarrows or a pump. Wheelbarrowing burns crew-hours; a pump adds <?php echo esc_html(Slab_Data::mr('pump.typical', 0, ' to ')); ?> but saves labor.</li>
<li><strong>Pump time.</strong> Line pumps bill <?php echo esc_html(Slab_Data::mr('pump.line_rate', 0, ' to ')); ?> per hour with a <?php echo esc_html(Slab_Data::r('pump.line_min_h')); ?>-hour minimum, and boom pumps <?php echo esc_html(Slab_Data::mr('pump.boom_rate', 0, ' to ')); ?> per hour. A slow crew makes the pump cost more too.</li>
<li><strong>Size and shape.</strong> Setup, form work and cleanup are nearly fixed, so small slabs cost more per foot. Curves, steps, many corners and blockouts for posts all add hours.</li>
<li><strong>Site prep.</strong> Excavation runs about <?php echo esc_html(Slab_Data::mr('site.excavation_range', 2, ' to ')); ?> per sq ft and is usually a separate line. Breaking out an old slab is another <?php echo esc_html(Slab_Data::mr('site.demo_range', 0, ' to ')); ?> per sq ft.</li>
<li><strong>Weather.</strong> Below about <?php echo (int) $sd['weather']['cold_f']; ?>°F, blankets and heat add labor and slow finishing. Above about <?php echo (int) $sd['weather']['hot_f']; ?>°F, the crew needs more hands to finish before the surface sets. See the <a href="<?php echo esc_url(home_url('/best-time-of-year-to-pour-concrete/')); ?>">best time of year to pour</a>.</li>
<li><strong>Region and season.</strong> Labor rates vary more by location than material prices do. Busy spring and summer schedules push prices up.</li>
</ul>

<h2>Worked Example: a 24x24 Garage Floor</h2>
<div class="formula-box">
<div class="formula-label">576 sq ft, broom finish, truck can reach the forms</div>
<div class="formula-math">576 × <?php echo esc_html(Slab_Data::num($rate_std, 3)); ?> = <?php echo esc_html(Slab_Data::num($ex_h, 1)); ?> crew-hours × <?php echo esc_html($m($ch)); ?> = <?php echo esc_html($m($ex_crew)); ?></div>
<div class="formula-explanation">That is <?php echo esc_html(Slab_Data::num($ex_worker_h[0], 0)); ?> to <?php echo esc_html(Slab_Data::num($ex_worker_h[1], 0)); ?> finisher-hours. Across the full crew-rate range of <?php echo esc_html(Slab_Data::mr('labor.crew_hour', 0, ' to ')); ?>, labor is <?php echo esc_html($mr($ex_crew_range)); ?>. Checked per sq ft: 576 × <?php echo esc_html($m2($L['sqft_default'])); ?> = <?php echo esc_html($m($ex_sqft)); ?>, with a range of <?php echo esc_html($mr($ex_sqft_range)); ?>.</div>
</div>
<p>On a 5-inch 24x24 slab with our calculator's full default scope, labor is <?php echo esc_html($m($c24['est']['labor'])); ?> of a <?php echo esc_html($m($c24['est']['direct'])); ?> direct cost, about <?php echo esc_html(number_format($labor_share, 0)); ?>%. If the same slab were behind the house and needed wheelbarrows, the difficult rate (<?php echo esc_html(Slab_Data::num($rate_diff, 3)); ?> crew-hours per sq ft) would put labor near <?php echo esc_html($m($a * $rate_diff * $ch)); ?>, and that is before any pump charge. Run your own slab in the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a> to see labor next to materials.</p>

<h2>How Labor Shows Up on a Quote</h2>
<p>Contractors present labor in different ways, which makes quotes hard to compare:</p>
<ul>
<li><strong>One lump sum</strong> for the whole job. Ask for at least labor and materials split out.</li>
<li><strong>Per sq ft, all-in.</strong> A single installed price per sq ft that includes concrete, labor and everything else. Ask what is in it: base, steel, vapor barrier, finish, cleanup.</li>
<li><strong>Line items.</strong> Forms, placing and finishing, excavation, base, reinforcement, pump, each priced separately. This is the easiest to check.</li>
</ul>
<p>Every quote also carries overhead and profit (insurance, trucks, tools, the office, the owner's pay), which our benchmarks put at up to about <?php echo (int) round($sd['overhead_profit'] * 100); ?>% on top of direct costs. A quote that looks high on labor may simply be carrying that markup on the labor line. Paste the numbers into the <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">concrete quote checker</a> to see if they are in a fair range.</p>

<div class="callout callout-info">
<div class="callout-title">Questions to ask about labor on any quote</div>
<p>How many people will be on the pour? Who sets the forms, and when? Is excavation and base work included? Is the pump included, and for how many hours? Will they saw-cut the joints, and when? Is there a minimum charge? Clear answers make quotes comparable.</p>
</div>

<h2>DIY: What You Save and What It Costs You</h2>
<p>Doing it yourself saves the labor line, but not the hours. The crew-hour figures above assume skilled finishers; first-timers are slower and get only one chance before the concrete sets. A middle path many homeowners use is to do the excavation, base and forms themselves and hire a crew just for the pour and finish, which is the part that needs speed and skill. The <a href="<?php echo esc_url(home_url('/diy-concrete-slab-what-you-can-and-shouldnt-do/')); ?>">DIY vs. contractor guide</a> covers where that line sits.</p>

<h2>Sources and Method</h2>
<p>Labor rates and production rates (crew-hours per sq ft) are this site's working benchmarks for residential flatwork, built from typical crew sizes and regional wage ranges; they are the same figures our labor calculator and slab calculator use. See our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology</a>. Finishing and curing practice follows ACI 302.1R and ACI 308R; weather thresholds follow ACI 305R and ACI 306R. Actual rates vary widely by region, season and crew.</p>
<?php
$html = ob_get_clean();

$e20h = 400 * $rate_std;

return [
    'updated' => '2026-10-12',
    'html' => $html,
    'faqs' => [
        [
            'q' => 'How much does concrete labor cost per square foot?',
            'a' => 'About ' . Slab_Data::mr('labor.sqft_range', 2, ' to ') . ' per sq ft for placing and finishing a broom-finished slab, with ' . $m2($L['sqft_default']) . ' a typical middle figure. Difficult access runs about ' . $m2($L['sqft_difficult']) . ' and stamped work about ' . $m2($L['sqft_stamped']) . ' per sq ft.',
        ],
        [
            'q' => 'How much does a concrete crew cost per hour?',
            'a' => 'A ' . Slab_Data::r('labor.crew_size') . '-person flatwork crew costs about ' . Slab_Data::mr('labor.crew_hour', 0, ' to ') . ' per crew-hour, based on ' . Slab_Data::mr('labor.worker_hour', 0, ' to ') . ' per finisher per hour.',
        ],
        [
            'q' => 'How many hours does it take to pour a 20x20 slab?',
            'a' => 'About ' . Slab_Data::num($e20h, 1) . ' crew-hours for placing and finishing a broom-finished 20x20 slab, at roughly ' . Slab_Data::num($rate_std, 3) . ' crew-hours per sq ft. Excavation, base and form setup on a separate day are extra.',
        ],
        [
            'q' => 'What is the labor cost for a 24x24 concrete slab?',
            'a' => 'About ' . $m($ex_sqft) . ' at ' . $m2($L['sqft_default']) . ' per sq ft, or ' . $m($ex_crew) . ' using ' . Slab_Data::num($ex_h, 1) . ' crew-hours at ' . $m($ch) . ' per crew-hour. The range is roughly ' . $mr($ex_sqft_range) . ' depending on region, finish and access.',
        ],
        [
            'q' => 'Why does a small concrete slab cost more per square foot in labor?',
            'a' => 'Setup, form work, travel and cleanup take about the same time whether the slab is 100 or 400 sq ft, and many contractors have a minimum charge. Those fixed hours are spread over less area on a small slab.',
        ],
    ],
];
