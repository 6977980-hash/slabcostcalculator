<?php
/**
 * CONCRETE SLAB COST BY STATE — /concrete-slab-cost-by-state/
 *
 * Routed by Slab_Architecture_Router (slab_route=state_hub). Prices a
 * standard slab in all 50 states and DC with the location factors in
 * Slab_Data, links the states that have their own page, and opens the
 * calculator on any other state (inc/state-pages.php).
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
slab_render_breadcrumbs();

$rows = Slab_State_Pages::hub_rows();
$ranked = Slab_State_Pages::ranked();
$nat = Slab_State_Pages::prices(1.0);
$psf = function ($pair) {
    return Slab_Data::money_range($pair, 2, '–');
};
$range = function ($pair) {
    return Slab_Data::money_range($pair, 0, '–');
};
$hi = $ranked[0];
$lo = end($ranked);
?>

<main id="primary" class="site-main">
  <div class="container">
    <div class="content-grid">
      <article class="article-content">
        <header class="page-header" style="margin-bottom: 1.5rem;">
          <h1 class="hero-title">Concrete Slab Cost by State</h1>
          <p class="hero-subtitle">What a concrete slab costs in each of the 50 states and Washington, DC, from the same cost model as our calculator.</p>
          <p class="hero-answer"><strong>Quick answer:</strong> at the U.S. average, a 4-inch slab costs about <?php echo esc_html($psf($nat['slab']['psf'])); ?> per sq ft installed. In our model the range runs from about <?php echo esc_html($psf(Slab_State_Pages::prices($lo['index'])['slab']['psf'])); ?> in <?php echo esc_html($lo['name']); ?> to <?php echo esc_html($psf(Slab_State_Pages::prices($hi['index'])['slab']['psf'])); ?> in <?php echo esc_html($hi['name']); ?>, mostly because labor costs differ.</p>
          <div class="trust-bar">
            <div class="trust-item"><strong>Last Updated:</strong> <time datetime="<?php echo esc_attr(SLAB_CONTENT_UPDATED); ?>"><?php echo esc_html(wp_date('F j, Y', strtotime(SLAB_CONTENT_UPDATED . ' 12:00:00'))); ?></time></div>
            <div class="trust-item"><a href="#method" style="font-weight: 600; text-decoration: underline;">How these prices are worked out &rarr;</a></div>
          </div>
        </header>

        <h2 id="states">Slab Prices in Every State</h2>
        <p>States in <strong>bold links</strong> have their own page with metro prices and local climate, frost and soil notes. Every other state name opens the calculator set to that state.</p>
        <div class="table-responsive">
          <table class="data-table">
            <thead><tr><th>State</th><th>Factor</th><th>Slab per sq ft (4 in)</th><th>24 × 24 garage (5 in)</th><th>Ready-mix, 3,000 PSI</th></tr></thead>
            <tbody>
              <?php foreach ($rows as $r) : ?>
              <tr>
                <td><a href="<?php echo esc_url(home_url($r['url'])); ?>"><?php echo $r['has_page'] ? '<strong>' . esc_html($r['name']) . '</strong>' : esc_html($r['name']); ?></a></td>
                <td><?php echo esc_html(number_format($r['index'], 2)); ?></td>
                <td><?php echo esc_html($psf($r['psf'])); ?></td>
                <td><?php echo esc_html($range($r['garage'])); ?></td>
                <td><?php echo esc_html($range($r['ready_mix'])); ?>/yd³</td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <p class="table-caption">Installed: forms, broom finish and labor, up to vapor barrier, #4 rebar and contractor overhead and profit. Excavation, gravel base and permits are extra. Ready-mix is before delivery and short-load fees.</p>

        <h2 id="metros">Major Metro Areas</h2>
        <div class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Metro</th><th>Factor</th><th>Slab per sq ft</th><th>24 × 24 garage</th></tr></thead>
            <tbody>
              <?php foreach (Slab_Data::metros() as $m) : $mp = Slab_State_Pages::prices($m['index']); $mslug = Slab_State_Pages::slug_for($m['state']); ?>
              <tr>
                <td><?php if ($mslug) : ?><a href="<?php echo esc_url(home_url(Slab_State_Pages::path($mslug) . '#metros')); ?>"><?php echo esc_html($m['name']); ?></a><?php else : ?><?php echo esc_html($m['name']); ?><?php endif; ?></td>
                <td><?php echo esc_html(number_format($m['index'], 2)); ?></td>
                <td><?php echo esc_html($psf($mp['slab']['psf'])); ?></td>
                <td><?php echo esc_html($range($mp['garage']['installed'])); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <h2 id="method">How These Prices Are Worked Out</h2>
        <p>These are modelled prices, not prices collected from contractors in each state. Each slab is priced with the line items of the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a> at national unit prices, then the state's location factor (U.S. average = 1.00) is applied to ready-mix concrete and labor, exactly as the calculator does when you pick a location. Bagged concrete, forms, rebar and the delivery fee stay at national prices, because they vary less from place to place.</p>
        <p>The factors are approximate, set by our editors to reflect typical construction cost differences and weighted toward each state's main metro areas. Rural areas are often cheaper than the state figure and big-city cores more expensive. Use these numbers to budget, then get local quotes; the <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">quote checker</a> uses the same factors. All national figures and the full method are in the <a href="<?php echo esc_url(home_url('/concrete-price-report/')); ?>">2026 concrete price report</a>.</p>

        <section class="faq-section" aria-label="Frequently Asked Questions">
          <h2 id="faq">State Price Questions</h2>
          <?php foreach (Slab_State_Pages::hub_faqs() as $faq) : ?>
          <div class="faq-item">
            <button type="button" class="faq-question" aria-expanded="false"><?php echo esc_html($faq['q']); ?></button>
            <div class="faq-answer"><p><?php echo esc_html($faq['a']); ?></p></div>
          </div>
          <?php endforeach; ?>
        </section>

        <?php get_template_part('template-parts/author-bio'); ?>
      </article>

      <aside class="sidebar-col">
        <div class="sidebar-widget">
          <div class="widget-title">State Guides</div>
          <ul class="calculator-nav-list">
            <?php foreach (Slab_State_Pages::states() as $slug => $s) : $st = Slab_Data::states()[$s['code']]; ?>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url(Slab_State_Pages::path($slug))); ?>"><span><?php echo esc_html($st['name']); ?></span><span class="calculator-nav-badge"><?php echo esc_html(number_format($st['index'], 2)); ?></span></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="sidebar-widget">
          <div class="widget-title">Price Data</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-price-report/')); ?>">2026 concrete price report</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url(Slab_Size_Pages::hub_path())); ?>">Slab cost by size</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">Concrete quote checker</a></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
