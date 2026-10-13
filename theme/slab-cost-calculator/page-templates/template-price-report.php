<?php
/**
 * 2026 CONCRETE PRICE REPORT — /concrete-price-report/
 *
 * Routed by Slab_Architecture_Router (slab_route=price_report). Every
 * figure comes from inc/price-report.php, which reads Slab_Data.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
slab_render_breadcrumbs();

$money = function ($n) {
    return $n === null ? '' : Slab_Data::money($n);
};
$pair = function ($low, $high) {
    return abs($low - $high) < 0.005 ? Slab_Data::money($low) : Slab_Data::money($low) . ' – ' . Slab_Data::money($high);
};
$groups = [];
foreach (Slab_Price_Report::rows() as $r) {
    $groups[$r[0]][] = $r;
}
$ranked = Slab_State_Pages::ranked();
$updated = wp_date('F j, Y', strtotime(SLAB_CONTENT_UPDATED . ' 12:00:00'));
$sd = Slab_Data::all();
?>

<main id="primary" class="site-main price-report">
  <div class="container">
    <div class="content-grid">
      <article class="article-content">
        <header class="page-header" style="margin-bottom: 1.5rem;">
          <h1 class="hero-title"><?php echo esc_html(Slab_Price_Report::YEAR); ?> Concrete Price Report</h1>
          <p class="hero-subtitle">U.S. prices for ready-mix, bagged concrete, labor, pumps, reinforcement and finishes, the installed slab costs they add up to, and how they vary by state. Free to cite, with the full data as a CSV.</p>
          <div class="trust-bar">
            <div class="trust-item"><strong>Prices last reviewed:</strong> <time datetime="<?php echo esc_attr(SLAB_CONTENT_UPDATED); ?>"><?php echo esc_html($updated); ?></time></div>
            <div class="trust-item"><a href="<?php echo esc_url(home_url(Slab_Price_Report::csv_path())); ?>" style="font-weight: 600; text-decoration: underline;">Download the data (CSV)</a></div>
            <div class="trust-item"><a href="#method" style="font-weight: 600; text-decoration: underline;">Method</a></div>
          </div>
        </header>

        <div class="callout callout-info">
          <div class="callout-title">What these numbers are</div>
          <p>Modelled U.S. average estimates, not survey results. Our editors set a typical price for each item, and every installed cost here is calculated from those prices with the same line items as our calculator. Local prices can be well above or below them. <a href="#method">How the figures are derived</a>.</p>
        </div>

        <h2 id="findings">Key Findings</h2>
        <ul>
          <?php foreach (Slab_Price_Report::findings() as $f) : ?>
          <li><?php echo esc_html($f); ?></li>
          <?php endforeach; ?>
        </ul>

        <?php foreach ($groups as $section => $rows) : ?>
        <h2 id="<?php echo esc_attr(sanitize_title($section)); ?>"><?php echo esc_html($section); ?></h2>
        <div class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Item</th><th>Unit</th><th>Price range</th><th>Calculator default</th></tr></thead>
            <tbody>
              <?php foreach ($rows as $r) : ?>
              <tr><td><strong><?php echo esc_html($r[1]); ?></strong></td><td><?php echo esc_html($r[2]); ?></td><td><?php echo esc_html($pair($r[3], $r[4])); ?></td><td><?php echo esc_html($money($r[5])); ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php if ($section === 'Ready-mix concrete') : ?>
        <p>Plants commonly set a minimum load of <?php echo esc_html(Slab_Data::r('delivery.min_load_yd', '–')); ?> yd³ (often <?php echo esc_html(Slab_Data::r('delivery.common_min_yd', '–')); ?>); smaller orders pay the short-load fee. A truck carries <?php echo esc_html(Slab_Data::r('delivery.truck_capacity_yd', '–')); ?> yd³. See <a href="<?php echo esc_url(home_url('/concrete-cost-per-cubic-yard-pricing-explained/')); ?>">concrete cost per cubic yard</a>.</p>
        <?php elseif ($section === 'Bagged concrete') : ?>
        <p>An 80-lb bag makes <?php echo esc_html(Slab_Data::num($sd['concrete']['bag_yields']['80'])); ?> ft³ of concrete, so a cubic yard takes about <?php echo (int) ceil(27 / $sd['concrete']['bag_yields']['80']); ?> bags: about <?php echo esc_html(Slab_Data::money(ceil(27 / $sd['concrete']['bag_yields']['80']) * $sd['concrete']['bag_price_default'], 0)); ?> per yard at the typical bag price. Compare with your own prices in the <a href="<?php echo esc_url(home_url('/bags-vs-ready-mix-calculator/')); ?>">bags vs. ready-mix calculator</a>.</p>
        <?php elseif ($section === 'Installed cost') : ?>
        <p class="table-caption"><?php echo esc_html(Slab_Data::cost_basis_note()); ?></p>
        <?php endif; ?>
        <?php endforeach; ?>

        <h2 id="by-size">Installed Cost by Slab Size</h2>
        <div class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Size</th><th>Thickness</th><th>Concrete (+<?php echo (int) $sd['concrete']['waste_pct']; ?>%)</th><th>Installed</th><th>Per sq ft</th></tr></thead>
            <tbody>
              <?php foreach (Slab_Price_Report::size_rows() as $r) : ?>
              <tr>
                <td><a href="<?php echo esc_url(home_url($r['url'])); ?>"><strong><?php echo esc_html($r['size']); ?></strong></a></td>
                <td><?php echo (int) $r['t']; ?> in</td>
                <td><?php echo esc_html(number_format($r['order_yd'], 2)); ?> yd³</td>
                <td><?php echo esc_html(Slab_Data::money_range($r['installed'], 0, ' – ')); ?></td>
                <td><?php echo esc_html(Slab_Data::money_range(Slab_Data::round_out($r['psf'], 0.5), 2, ' – ')); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <h2 id="by-state">Cost by State</h2>
        <p>The same 4-inch slab, priced with each state's location factor (U.S. average = 1.00). The five most and least expensive states in our model:</p>
        <div class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Most expensive</th><th>Factor</th><th>Per sq ft</th><th>Least expensive</th><th>Factor</th><th>Per sq ft</th></tr></thead>
            <tbody>
              <?php $rev = array_reverse($ranked); for ($i = 0; $i < 5; $i++) : $a = $ranked[$i]; $b = $rev[$i]; $ap = Slab_State_Pages::prices($a['index']); $bp = Slab_State_Pages::prices($b['index']); ?>
              <tr>
                <td><strong><?php echo esc_html($a['name']); ?></strong></td><td><?php echo esc_html(number_format($a['index'], 2)); ?></td><td><?php echo esc_html(Slab_Data::money_range($ap['slab']['psf'], 2, ' – ')); ?></td>
                <td><strong><?php echo esc_html($b['name']); ?></strong></td><td><?php echo esc_html(number_format($b['index'], 2)); ?></td><td><?php echo esc_html(Slab_Data::money_range($bp['slab']['psf'], 2, ' – ')); ?></td>
              </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
        <p>All 50 states, DC and 13 metro areas are on the <a href="<?php echo esc_url(home_url(Slab_State_Pages::hub_path())); ?>">concrete slab cost by state</a> page, with guides for <?php
          $links = [];
          foreach (Slab_State_Pages::states() as $slug => $s) {
              $links[] = '<a href="' . esc_url(home_url(Slab_State_Pages::path($slug))) . '">' . esc_html(Slab_Data::states()[$s['code']]['name']) . '</a>';
          }
          echo wp_kses_post(implode(', ', $links));
        ?>.</p>

        <h2 id="method">Method</h2>
        <ol>
          <li><strong>Unit prices.</strong> Each item above has a typical U.S. price and a range, set by our editors and kept in one data file that every page and calculator on this site reads. They are editorial estimates of typical <?php echo esc_html(Slab_Price_Report::YEAR); ?> prices. They are not averages from a survey of contractors or suppliers, and no sample size applies.</li>
          <li><strong>Installed costs.</strong> Slab costs are calculated, not collected. Concrete volume is length × width × thickness plus <?php echo (int) $sd['concrete']['waste_pct']; ?>% waste; the low end is a plain broom-finish slab with labor at <?php echo esc_html(Slab_Data::money($sd['labor']['sqft_range'][0], 2)); ?>/sq ft; the high end adds a vapor barrier and #4 rebar, labor at <?php echo esc_html(Slab_Data::money($sd['labor']['sqft_default'], 2)); ?>/sq ft and <?php echo (int) round($sd['overhead_profit'] * 100); ?>% contractor overhead and profit. Excavation, gravel base, pumping and permits are listed separately.</li>
          <li><strong>Location.</strong> Each state and metro has one approximate location factor, set by our editors to reflect typical construction cost differences and weighted toward the state's main metro areas. It is applied to ready-mix and labor only, as in the calculator. It is not a measured price for each place.</li>
          <li><strong>Updates.</strong> When a price in the data file changes, this report, the CSV, the calculator, the size pages and the state pages all change together. The date above is the last review.</li>
        </ol>
        <p>Formulas for volume, bags, rebar and joints are on the <a href="<?php echo esc_url(home_url('/methodology/')); ?>">methodology page</a>.</p>

        <h2 id="data">Download &amp; Cite</h2>
        <p>The full data, including every size and every state, is in one CSV file: <a href="<?php echo esc_url(home_url(Slab_Price_Report::csv_path())); ?>"><?php echo esc_html(ltrim(Slab_Price_Report::csv_path(), '/')); ?></a>. You may reuse it under the <a href="https://creativecommons.org/licenses/by/4.0/" rel="noopener license" target="_blank">Creative Commons Attribution 4.0</a> license: credit SlabCostCalculator.com and link to this page.</p>
        <p><strong>Suggested citation:</strong></p>
        <blockquote class="report-citation"><p><?php echo esc_html(Slab_Price_Report::citation()); ?></p></blockquote>
        <p>Want the calculator itself on your site? See <a href="<?php echo esc_url(home_url(Slab_Embed_Widget::info_path())); ?>">embed our concrete calculator</a>.</p>

        <section class="faq-section" aria-label="Frequently Asked Questions">
          <h2 id="faq">Questions About This Report</h2>
          <?php foreach (Slab_Price_Report::faqs() as $faq) : ?>
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
          <div class="widget-title">In This Report</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="#findings">Key findings</a></li>
            <?php foreach (array_keys($groups) as $section) : ?>
            <li class="calculator-nav-item"><a href="#<?php echo esc_attr(sanitize_title($section)); ?>"><?php echo esc_html($section); ?></a></li>
            <?php endforeach; ?>
            <li class="calculator-nav-item"><a href="#by-size">By slab size</a></li>
            <li class="calculator-nav-item"><a href="#by-state">By state</a></li>
            <li class="calculator-nav-item"><a href="#method">Method</a></li>
            <li class="calculator-nav-item"><a href="#data">Download &amp; cite</a></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
