<?php
/**
 * ONE SLAB SIZE — /12x16-concrete-slab-cost/ etc.
 *
 * Routed by Slab_Architecture_Router (slab_route=size_page, slab_slug=12x16).
 * Text, project type and figures: inc/size-pages.php. Every number is
 * computed from Slab_Data with the calculator's own line items.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$size = sanitize_text_field(get_query_var('slab_slug'));
$d = Slab_Size_Pages::data($size);
$s = Slab_Size_Pages::sizes()[$size];
$use = Slab_Size_Pages::uses()[$d['use']];
$sd = Slab_Data::all();
$e = $d['est'];
$b = $d['bvt'];
$j = $d['joints'];
$money = function ($n) {
    return Slab_Data::money($n, 0);
};
$range = function ($pair) {
    return Slab_Data::money_range($pair, 0, ' – ');
};
$yd = function ($n) {
    return number_format($n, 2) . ' yd³';
};
$psf = Slab_Data::round_out($d['installed_psf'], 0.5);
$dims = Slab_Size_Pages::dims($size);
$an = preg_match('/^(8|11|18)x/', $size) ? 'an' : 'a'; // "an 8x10 slab"
$waste_pct = (int) $sd['concrete']['waste_pct'];
$be = Slab_Data::break_even_yd(true);

// Neighbouring sizes in the hub's order, for "nearby sizes" links.
$rows = Slab_Size_Pages::hub_rows();
$idx = array_search($size, array_column($rows, 'size'), true);
$nearby = array_values(array_filter($rows, function ($r, $i) use ($idx, $size) {
    return $r['size'] !== $size && abs($i - $idx) <= 3;
}, ARRAY_FILTER_USE_BOTH));

get_header();
slab_render_breadcrumbs();
?>

<main id="primary" class="site-main size-page">
  <div class="container">
    <div class="content-grid">
      <article class="article-content">
        <header class="page-header" style="margin-bottom: 1.5rem;">
          <h1 class="hero-title"><?php echo esc_html(Slab_Size_Pages::h1($size)); ?></h1>
          <p class="hero-answer">
            <strong>Quick answer:</strong> <?php echo $an; ?> <?php echo esc_html($dims); ?> slab (<?php echo esc_html(number_format($d['area'])); ?> sq ft) <?php echo (int) $d['t']; ?> inches thick needs <strong><?php echo esc_html($yd($e['net_yd'])); ?></strong> of concrete, <?php echo esc_html($yd($e['order_yd'])); ?> with <?php echo $waste_pct; ?>% waste (order <?php echo esc_html(Slab_Data::num($d['ready_mix_yd'])); ?> yd³ of ready-mix) or <strong><?php echo esc_html(number_format($e['bags80'])); ?> bags of 80-lb mix</strong>. Installed, it costs about <strong><?php echo esc_html($range($d['installed'])); ?></strong> (<?php echo esc_html(Slab_Data::money_range($psf, 2, ' – ')); ?> per sq ft); DIY materials run <?php echo esc_html($range($d['diy'])); ?>.
          </p>
          <div class="trust-bar">
            <div class="trust-item"><strong>Last Updated:</strong> <time datetime="<?php echo esc_attr(SLAB_CONTENT_UPDATED); ?>"><?php echo esc_html(wp_date('F j, Y', strtotime(SLAB_CONTENT_UPDATED . ' 12:00:00'))); ?></time></div>
            <div class="trust-item"><a href="<?php echo esc_url(home_url('/methodology/')); ?>" style="font-weight: 600; text-decoration: underline;">How these numbers are calculated &rarr;</a></div>
          </div>
        </header>

        <div class="quantities-grid size-stats">
          <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
            <div class="quantity-card-label">Installed cost</div>
            <div class="quantity-card-value" style="color: var(--primary-800);"><?php echo esc_html($range($d['installed'])); ?></div>
            <div class="quantity-card-note"><?php echo esc_html(Slab_Data::money_range($psf, 2, ' – ')); ?> / sq ft</div>
          </div>
          <div class="quantity-card">
            <div class="quantity-card-label">Concrete (+<?php echo $waste_pct; ?>%)</div>
            <div class="quantity-card-value"><?php echo esc_html($yd($e['order_yd'])); ?></div>
            <div class="quantity-card-note">Net <?php echo esc_html($yd($e['net_yd'])); ?> at <?php echo (int) $d['t']; ?> in</div>
          </div>
          <div class="quantity-card">
            <div class="quantity-card-label">80-lb bags</div>
            <div class="quantity-card-value"><?php echo esc_html(number_format($e['bags80'])); ?></div>
            <div class="quantity-card-note"><?php echo esc_html(number_format($e['bags60'])); ?> × 60-lb</div>
          </div>
          <div class="quantity-card">
            <div class="quantity-card-label">Best way to buy</div>
            <div class="quantity-card-value"><?php echo $b['cheaper'] === 'bags' ? 'Bags' : 'Ready-mix'; ?></div>
            <div class="quantity-card-note"><?php echo $b['cheaper'] === 'bags' ? esc_html($money($b['bag_cost']) . ' with mixer') : esc_html($b['trucks'] . ($b['trucks'] === 1 ? ' truck' : ' trucks') . ', ' . $money($b['truck_cost'])); ?></div>
          </div>
        </div>

        <p><?php echo esc_html(Slab_Size_Pages::fill($s['intro'], $d)); ?></p>

        <h2 id="concrete-by-thickness">Concrete for <?php echo $an; ?> <?php echo esc_html($size); ?> Slab at 4, 5 and 6 Inches</h2>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr><th>Thickness</th><th>Net volume</th><th>With <?php echo $waste_pct; ?>% waste</th><th>Ready-mix order</th><th>80-lb bags</th><th>60-lb bags</th><th>40-lb bags</th><th>Installed</th></tr>
            </thead>
            <tbody>
              <?php foreach ($d['by_t'] as $th => $r) : ?>
              <tr<?php echo $th === $d['t'] ? ' class="row-highlight"' : ''; ?>>
                <td><strong><?php echo (int) $th; ?> in</strong><?php echo $th === $d['t'] ? ' <span class="table-sub">(typical)</span>' : ''; ?></td>
                <td><?php echo esc_html($yd($r['net_yd'])); ?></td>
                <td><?php echo esc_html($yd($r['order_yd'])); ?></td>
                <td><?php echo esc_html(Slab_Data::num($r['ready_mix_yd'])); ?> yd³</td>
                <td><?php echo esc_html(number_format($r['bags80'])); ?></td>
                <td><?php echo esc_html(number_format($r['bags60'])); ?></td>
                <td><?php echo esc_html(number_format($r['bags40'])); ?></td>
                <td><?php echo esc_html(Slab_Data::money_range($r['installed'], 0, '–')); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div class="table-caption">Bag yields: 80 lb = <?php echo esc_html(Slab_Data::num($sd['concrete']['bag_yields']['80'], 2)); ?> ft³, 60 lb = <?php echo esc_html(Slab_Data::num($sd['concrete']['bag_yields']['60'], 2)); ?> ft³, 40 lb = <?php echo esc_html(Slab_Data::num($sd['concrete']['bag_yields']['40'], 2)); ?> ft³, rounded up to whole bags. Ready-mix rounded up to the next quarter yard. <?php echo esc_html(Slab_Data::cost_basis_note()); ?></div>
        </div>

        <h2 id="waste">How Much Extra to Order</h2>
        <p>The table above adds <?php echo $waste_pct; ?>%, the usual allowance for a formed slab on a graded base. Pick less only for rigid forms on a laser-flat base, more for soft or uneven ground:</p>
        <div class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Waste allowance (<?php echo (int) $d['t']; ?> in slab)</th><th>Concrete</th><th>80-lb bags</th></tr></thead>
            <tbody>
              <?php foreach ($d['waste'] as $pct => $r) : ?>
              <tr><td><?php echo (int) $pct; ?>%<?php echo $pct === 0 ? ' (exact volume)' : ''; ?></td><td><?php echo esc_html($yd($r['yd'])); ?></td><td><?php echo esc_html(number_format($r['bags80'])); ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <h2 id="bags-or-truck">Bags or a Ready-Mix Truck?</h2>
        <div class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Option</th><th>What you buy</th><th>Concrete bill</th></tr></thead>
            <tbody>
              <tr<?php echo $b['cheaper'] === 'bags' ? ' class="row-highlight"' : ''; ?>>
                <td><strong>80-lb bags</strong></td>
                <td><?php echo esc_html(number_format($b['bags'])); ?> bags at <?php echo esc_html(Slab_Data::money($sd['concrete']['bag_price_default'], 2)); ?> + mixer rental <?php echo esc_html($money($b['mixer_cost'])); ?></td>
                <td><?php echo esc_html($money($b['bag_cost'])); ?></td>
              </tr>
              <tr<?php echo $b['cheaper'] === 'truck' ? ' class="row-highlight"' : ''; ?>>
                <td><strong>Ready-mix</strong></td>
                <td><?php echo esc_html(Slab_Data::num($b['order_yd'])); ?> yd³ at <?php echo esc_html(Slab_Data::money($sd['concrete']['ready_mix_default'])); ?> + <?php echo esc_html(Slab_Data::money($sd['delivery']['fee_default'])); ?> delivery<?php echo $b['short'] ? ' + ' . esc_html($money($b['short_fee'])) . ' short-load fee' : ''; ?></td>
                <td><?php echo esc_html($money($b['truck_cost'])); ?></td>
              </tr>
            </tbody>
          </table>
        </div>
        <p>
          <?php if ($b['cheaper'] === 'bags') : ?>
            At <?php echo esc_html($yd($e['order_yd'])); ?>, bags come out <?php echo esc_html($money($b['truck_cost'] - $b['bag_cost'])); ?> cheaper, mostly because a small ready-mix order pays the delivery fee<?php echo $b['short'] ? ' and a short-load fee' : ''; ?>. The trade-off is labor: <?php echo esc_html(number_format($b['bags'])); ?> bags is about <?php echo esc_html(Slab_Data::num(round($b['mix_hours'], 1))); ?> hours of mixing for a small crew with one drum mixer, and every bag weighs 80 lb.
          <?php elseif ($b['bags'] > 150) : ?>
            Ready-mix, without question. <?php echo esc_html(number_format($b['bags'])); ?> bags would cost <?php echo esc_html($money($b['bag_cost'])); ?> and take about <?php echo esc_html(Slab_Data::num(round($b['mix_hours']))); ?> hours of mixing, far longer than concrete stays workable, so the slab would end up with cold joints. Ready-mix trucks bring it as fast as the crew can place it<?php echo $b['trucks'] > 1 ? ' (' . (int) $b['trucks'] . ' trucks of about ' . esc_html(Slab_Data::range($sd['delivery']['truck_capacity_yd'], '–')) . ' yd³ each)' : ''; ?>.
          <?php else : ?>
            Ready-mix is <?php echo esc_html($money($b['bag_cost'] - $b['truck_cost'])); ?> cheaper here and saves about <?php echo esc_html(Slab_Data::num(round($b['mix_hours'], 1))); ?> hours of mixing <?php echo esc_html(number_format($b['bags'])); ?> bags. Bags only win below about <?php echo esc_html(Slab_Data::num($be)); ?> yd³, once the delivery<?php echo $b['short'] ? ' and short-load fees are' : ' fee is'; ?> counted.
          <?php endif; ?>
          Try your own prices in the <a href="<?php echo esc_url(home_url('/bags-vs-ready-mix-calculator/')); ?>">bags vs. ready-mix calculator</a>.
        </p>

        <h2 id="cost-breakdown">What <?php echo $an; ?> <?php echo esc_html($size); ?> Slab Costs</h2>
        <div class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Line item (<?php echo (int) $d['t']; ?> in slab)</th><th>Basis</th><th>Cost</th></tr></thead>
            <tbody>
              <tr><td>Ready-mix concrete + delivery</td><td><?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³ at <?php echo esc_html(Slab_Data::money($sd['concrete']['ready_mix_default'])); ?> + <?php echo esc_html(Slab_Data::money($sd['delivery']['fee_default'])); ?></td><td><?php echo esc_html($money($e['ready_mix'])); ?></td></tr>
              <tr><td>Forms</td><td><?php echo esc_html(number_format($e['perimeter'])); ?> lin ft at <?php echo esc_html(Slab_Data::money($sd['site']['forms_default'], 2)); ?></td><td><?php echo esc_html($money($e['forms'])); ?></td></tr>
              <tr><td>Vapor barrier</td><td><?php echo esc_html(number_format($d['area'])); ?> sq ft at <?php echo esc_html(Slab_Data::money($sd['site']['vapor_default'], 2)); ?></td><td><?php echo esc_html($money($e['vapor'])); ?></td></tr>
              <tr><td>#4 rebar at <?php echo (int) $sd['reinforcement']['rebar_base_spacing_in']; ?> in</td><td><?php echo esc_html(number_format($d['area'])); ?> sq ft at <?php echo esc_html(Slab_Data::money($sd['reinforcement']['rebar_default'], 2)); ?></td><td><?php echo esc_html($money($e['rebar'])); ?></td></tr>
              <tr><td>Broom finish</td><td><?php echo esc_html(number_format($d['area'])); ?> sq ft at <?php echo esc_html(Slab_Data::money($sd['finish']['broom']['default'], 2)); ?></td><td><?php echo esc_html($money($e['broom'])); ?></td></tr>
              <tr><td>Labor (place &amp; finish)</td><td><?php echo esc_html(number_format($d['area'])); ?> sq ft at <?php echo esc_html(Slab_Data::money($sd['labor']['sqft_default'], 2)); ?></td><td><?php echo esc_html($money($e['labor'])); ?></td></tr>
              <tr><td><strong>Direct cost</strong></td><td>Before overhead and profit</td><td><strong><?php echo esc_html($money($e['direct'])); ?></strong></td></tr>
            </tbody>
          </table>
        </div>
        <ul>
          <li><strong>Installed price:</strong> <?php echo esc_html($range($d['installed'])); ?>. The low end is a plain slab with lower-cost labor; the high end is the full scope above plus <?php echo (int) round($sd['overhead_profit'] * 100); ?>% contractor overhead and profit.</li>
          <li><strong>Site prep and gravel base</strong> (excavation and <?php echo (int) $sd['site']['gravel_depth_in']; ?> in of compacted stone): about <?php echo esc_html($range($d['site_prep'])); ?> more.</li>
          <?php if ($d['stamped']) : ?>
          <li><strong>Stamped finish</strong> instead of broom: about <?php echo esc_html($range($d['stamped'])); ?> installed.</li>
          <?php endif; ?>
          <?php if ($d['edge_yd']) : ?>
          <li><strong>Thickened edge</strong> (<?php echo (int) $sd['monolithic']['edge_width_in']; ?> in wide, <?php echo esc_html(Slab_Data::range($sd['monolithic']['edge_depth_in'], '–')); ?> in deep) if the building sits on the slab: <?php echo esc_html(number_format($d['edge_yd'][0], 1) . '–' . number_format($d['edge_yd'][1], 1)); ?> yd³ more concrete, about <?php echo esc_html($range($d['edge_cost'])); ?>, plus trenching and two #4 bars around the edge.</li>
          <?php endif; ?>
          <li><strong>Permit</strong>, where one is needed: about <?php echo esc_html(Slab_Data::mr('permit.flatwork', 0, ' – ')); ?>.</li>
        </ul>
        <p>Have a contractor's price? Put it into the <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">concrete quote checker</a>, or compare two or three bids side by side there.</p>

        <h2 id="materials">Materials List for <?php echo $an; ?> <?php echo esc_html($dims); ?> Slab</h2>
        <div class="table-responsive">
          <table class="data-table">
            <tbody>
              <tr><th scope="row">Concrete</th><td><?php echo esc_html(Slab_Data::num($d['ready_mix_yd'])); ?> yd³ ready-mix, or <?php echo esc_html(number_format($e['bags80'])); ?> × 80-lb / <?php echo esc_html(number_format($d['bags40'])); ?> × 40-lb bags</td></tr>
              <tr><th scope="row">Gravel base (<?php echo (int) $sd['site']['gravel_depth_in']; ?> in compacted)</th><td><?php echo esc_html($yd($d['gravel_yd'])); ?>, about <?php echo esc_html(number_format($d['gravel_tons'], 1)); ?> tons</td></tr>
              <tr><th scope="row">Forms</th><td><?php echo (int) $d['forms']['boards']; ?> × <?php echo esc_html($d['forms']['board']); ?> boards 12 ft long, <?php echo (int) $d['forms']['stakes']; ?> stakes, <?php echo (int) $d['forms']['screws']; ?> screws (<?php echo esc_html(number_format($d['perimeter'])); ?> lin ft)</td></tr>
              <tr><th scope="row">Rebar (#4 at <?php echo (int) $sd['reinforcement']['rebar_base_spacing_in']; ?> in)</th><td><?php echo esc_html(number_format(round($d['rebar']['lin_ft']))); ?> lin ft = <?php echo (int) $d['rebar']['sticks']; ?> × 20-ft sticks, <?php echo (int) $d['rebar']['chairs']; ?> chairs</td></tr>
              <?php if ($d['rebar_tight']) : ?>
              <tr><th scope="row">Rebar (#4 at <?php echo (int) $sd['reinforcement']['rebar_tight_spacing_in']; ?> in, heavy loads)</th><td><?php echo esc_html(number_format(round($d['rebar_tight']['lin_ft']))); ?> lin ft = <?php echo (int) $d['rebar_tight']['sticks']; ?> × 20-ft sticks, <?php echo (int) $d['rebar_tight']['chairs']; ?> chairs</td></tr>
              <?php endif; ?>
              <tr><th scope="row">Or wire mesh (6x6)</th><td><?php echo (int) $d['mesh']['count']; ?> sheets of 5 × 10 ft, about <?php echo esc_html($range($d['mesh']['cost'])); ?></td></tr>
              <tr><th scope="row">Vapor barrier</th><td><?php echo esc_html(number_format($d['area'])); ?> sq ft plus 6 in laps</td></tr>
              <tr><th scope="row">Control joints</th><td>
                <?php if ($j['lin_ft'] > 0) : ?>
                  <?php echo (int) ($j['panels_l'] * $j['panels_w']); ?> panels of about <?php echo esc_html(Slab_Data::num(round($j['panel_l'], 1)) . ' × ' . Slab_Data::num(round($j['panel_w'], 1))); ?> ft: <?php echo esc_html(number_format(round($j['lin_ft']))); ?> lin ft of saw cut, <?php echo esc_html(Slab_Data::num($d['t'] * $sd['joints']['depth_fraction'])); ?> in deep
                <?php else : ?>
                  None needed: both sides are within the <?php echo esc_html(Slab_Data::num($j['target_ft'])); ?> ft joint spacing for <?php echo (int) $d['t']; ?> in
                <?php endif; ?>
              </td></tr>
            </tbody>
          </table>
        </div>

        <h2 id="specs">Recommended Specs for <?php echo preg_match('/^[aeiou]/i', $use['name']) ? 'an' : 'a'; ?> <?php echo esc_html(ucwords($use['name'])); ?></h2>
        <div class="table-responsive">
          <table class="data-table">
            <tbody>
              <?php foreach ($use['specs'] as $spec) : ?>
              <tr><th scope="row" style="width: 28%;"><?php echo esc_html($spec[0]); ?></th><td><?php echo esc_html($spec[1]); ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <ul>
          <?php foreach ($s['notes'] as $note) : ?>
          <li><?php echo esc_html(Slab_Size_Pages::fill($note, $d)); ?></li>
          <?php endforeach; ?>
        </ul>

        <h2 id="pour-day">Pour Day for <?php echo $an; ?> <?php echo esc_html($size); ?> Slab</h2>
        <ul>
          <li><strong>Crew time:</strong> about <?php echo esc_html(Slab_Data::num(max(1, round($d['crew_hours'])))); ?> crew-hours to set forms, place, finish and cut joints (<?php echo esc_html(Slab_Data::num($sd['labor']['crew_hours_per_sqft'], 3)); ?> h per sq ft for a broom finish).</li>
          <?php if ($b['cheaper'] === 'truck' || $b['bags'] > 60) : ?>
          <li><strong>Trucks:</strong> <?php echo esc_html(Slab_Data::num($d['ready_mix_yd'])); ?> yd³ is <?php echo (int) $b['trucks']; ?> <?php echo $b['trucks'] === 1 ? 'truck' : 'trucks'; ?>, about <?php echo esc_html(Slab_Data::num(round($d['unload_min'][0])) . '–' . Slab_Data::num(round($d['unload_min'][1]))); ?> minutes of unloading in total. Concrete must be placed within <?php echo (int) $sd['delivery']['discharge_min']; ?> minutes of batching.</li>
          <?php endif; ?>
          <?php if ($d['chute_short']) : ?>
          <li><strong>Reach:</strong> a truck chute reaches about <?php echo esc_html(Slab_Data::range($sd['site']['chute_reach_ft'], '–')); ?> ft. At <?php echo (int) min($d['l'], $d['w']); ?> ft wide, the middle is out of reach from one side, so plan access on two sides, a buggy, or a pump (<?php echo esc_html(Slab_Data::mr('pump.typical', 0, ' – ')); ?>).</li>
          <?php else : ?>
          <li><strong>Reach:</strong> a truck chute reaches about <?php echo esc_html(Slab_Data::range($sd['site']['chute_reach_ft'], '–')); ?> ft, enough for this slab if the truck can park beside it. If it can't, budget wheelbarrows or a line pump.</li>
          <?php endif; ?>
          <li><strong>Joints:</strong> saw-cut <?php echo esc_html(Slab_Data::range($sd['joints']['sawcut_h'], '–')); ?> hours after finishing with a wet saw, or <?php echo esc_html(Slab_Data::range($sd['joints']['early_entry_h'], '–')); ?> hours with an early-entry saw.</li>
          <li><strong>Curing:</strong> foot traffic after <?php echo esc_html(Slab_Data::range($sd['curing']['foot_traffic_h'], '–')); ?> hours, cars after <?php echo (int) $sd['curing']['cars_days']; ?> days, full strength at <?php echo (int) $sd['curing']['full_days']; ?> days.</li>
        </ul>

        <h2 id="calculator">Price Your Own <?php echo esc_html($size); ?> Slab</h2>
        <p>The calculator below starts at <?php echo esc_html($dims); ?>, <?php echo (int) $d['t']; ?> inches. Change the thickness, finish or reinforcement, pick your state for local prices, and the Live Estimate updates with a blueprint of the joint and rebar layout.</p>
        <div style="margin-bottom: 2.5rem;">
          <?php get_template_part('template-parts/calculator-primary', null, ['dims' => [$d['l'], $d['w'], $d['t']]]); ?>
        </div>

        <section class="faq-section" aria-label="Frequently Asked Questions">
          <h2 id="faq"><?php echo esc_html($size); ?> Slab Questions</h2>
          <?php foreach (Slab_Size_Pages::faqs($size) as $faq) : ?>
          <div class="faq-item">
            <button type="button" class="faq-question" aria-expanded="false"><?php echo esc_html($faq['q']); ?></button>
            <div class="faq-answer"><p><?php echo esc_html($faq['a']); ?></p></div>
          </div>
          <?php endforeach; ?>
        </section>

        <h2>Related Guides</h2>
        <ul>
          <?php foreach ($use['links'] as $link) : ?>
          <li><a href="<?php echo esc_url(home_url($link[1])); ?>"><?php echo esc_html($link[0]); ?></a></li>
          <?php endforeach; ?>
          <li><a href="<?php echo esc_url(home_url(Slab_Size_Pages::hub_path())); ?>">Concrete slab cost by size (all sizes)</a></li>
        </ul>

        <?php get_template_part('template-parts/author-bio'); ?>
      </article>

      <aside class="sidebar-col">
        <div class="sidebar-widget">
          <div class="widget-title">Nearby Slab Sizes</div>
          <ul class="calculator-nav-list">
            <?php foreach ($nearby as $r) : ?>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url($r['url'])); ?>"><span><?php echo esc_html($r['size'] . ' slab'); ?></span><span class="calculator-nav-badge"><?php echo esc_html(Slab_Data::money_range($r['installed'], 0, '–')); ?></span></a></li>
            <?php endforeach; ?>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url(Slab_Size_Pages::hub_path())); ?>"><span>All slab sizes</span><span>&rarr;</span></a></li>
          </ul>
        </div>
        <div class="sidebar-widget">
          <div class="widget-title">On This Page</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="#concrete-by-thickness">Concrete by thickness</a></li>
            <li class="calculator-nav-item"><a href="#bags-or-truck">Bags or truck</a></li>
            <li class="calculator-nav-item"><a href="#cost-breakdown">Cost breakdown</a></li>
            <li class="calculator-nav-item"><a href="#materials">Materials list</a></li>
            <li class="calculator-nav-item"><a href="#specs">Specs</a></li>
            <li class="calculator-nav-item"><a href="#calculator">Calculator</a></li>
            <li class="calculator-nav-item"><a href="#faq">Questions</a></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
