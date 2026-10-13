<?php
/**
 * CONCRETE SLAB COST BY SIZE — /concrete-slab-cost-by-size/
 *
 * Routed by Slab_Architecture_Router (slab_route=size_hub). Lists every size
 * page and the four size articles, grouped by project type, with figures
 * from Slab_Data (inc/size-pages.php).
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
slab_render_breadcrumbs();

$rows = Slab_Size_Pages::hub_rows();
$uses = Slab_Size_Pages::uses();
$by_use = [];
foreach ($rows as $r) {
    $by_use[$r['use']][] = $r;
}
$sd = Slab_Data::all();
$small = Slab_Data::size_costs(8, 10, 4);
$big = Slab_Data::size_costs(60, 40, 4);
?>

<main id="primary" class="site-main">
  <div class="container">
    <div class="content-grid">
      <article class="article-content">
        <header class="page-header" style="margin-bottom: 1.5rem;">
          <h1 class="hero-title">Concrete Slab Cost by Size</h1>
          <p class="hero-subtitle">Concrete, bags and installed price for <?php echo count($rows); ?> common slab sizes, from an 8 × 8 hot tub pad to a 40 × 60 pole barn floor. Each size has its own page with a full materials list, bags vs. ready-mix, pour-day notes and a calculator set to that size.</p>
          <p class="hero-answer"><strong>Quick answer:</strong> installed concrete slabs run about <?php echo esc_html(Slab_Data::money_range(Slab_Data::installed_psf(), 0, ' to ')); ?> per sq ft at 4 inches. Small slabs cost more per foot (an 8 × 10 is about <?php echo esc_html(Slab_Data::money_range(Slab_Data::round_out($small['installed_psf'], 0.5), 2, ' to ')); ?>) because delivery and setup are spread over less area; a 40 × 60 is about <?php echo esc_html(Slab_Data::money_range(Slab_Data::round_out($big['installed_psf'], 0.5), 2, ' to ')); ?>.</p>
          <div class="trust-bar">
            <div class="trust-item"><strong>Last Updated:</strong> <time datetime="<?php echo esc_attr(SLAB_CONTENT_UPDATED); ?>"><?php echo esc_html(wp_date('F j, Y', strtotime(SLAB_CONTENT_UPDATED . ' 12:00:00'))); ?></time></div>
            <div class="trust-item"><a href="<?php echo esc_url(home_url('/methodology/')); ?>" style="font-weight: 600; text-decoration: underline;">How these numbers are calculated &rarr;</a></div>
          </div>
        </header>

        <?php foreach ($uses as $key => $use) : if (empty($by_use[$key])) continue; ?>
        <h2 id="<?php echo esc_attr('sizes-' . $key); ?>"><?php echo esc_html($use['label']); ?></h2>
        <div class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Size</th><th>Area</th><th>Thickness</th><th>Concrete (+<?php echo (int) $sd['concrete']['waste_pct']; ?>%)</th><th>80-lb bags</th><th>Installed</th><th>Per sq ft</th></tr></thead>
            <tbody>
              <?php foreach ($by_use[$key] as $r) : ?>
              <tr>
                <td><a href="<?php echo esc_url(home_url($r['url'])); ?>"><strong><?php echo esc_html($r['size']); ?> slab</strong></a></td>
                <td><?php echo esc_html(number_format($r['area'])); ?> sq ft</td>
                <td><?php echo (int) $r['t']; ?> in</td>
                <td><?php echo esc_html(number_format($r['order_yd'], 2)); ?> yd³</td>
                <td><?php echo esc_html(number_format($r['bags80'])); ?></td>
                <td><?php echo esc_html(Slab_Data::money_range($r['installed'], 0, '–')); ?></td>
                <td><?php echo esc_html(Slab_Data::money_range(Slab_Data::round_out($r['psf'], 0.5), 2, '–')); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endforeach; ?>
        <p class="table-caption"><?php echo esc_html(Slab_Data::cost_basis_note()); ?></p>

        <h2 id="how-sizes-are-priced">How These Prices Are Worked Out</h2>
        <p>Every size is priced with the same line items as the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a>: ready-mix concrete with a delivery fee, forms, vapor barrier, #4 rebar, a broom finish and labor, at U.S. average prices. The thickness shown is the usual one for that kind of slab (4 inches for pads, sheds and patios, 5 for garages and driveways, 6 for shops and barns); each size page also lists 4, 5 and 6 inches. Excavation, a gravel base, a thickened edge, a pump and permits are extra and are listed on each page.</p>
        <p>For a size that isn't here, enter it in the calculator, or use the <a href="<?php echo esc_url(home_url('/concrete-yard-calculator/')); ?>">cubic yard</a> and <a href="<?php echo esc_url(home_url('/concrete-bags-calculator/')); ?>">bags</a> calculators.</p>

        <section class="faq-section" aria-label="Frequently Asked Questions">
          <h2 id="faq">Slab Size Questions</h2>
          <?php foreach (Slab_Size_Pages::hub_faqs() as $faq) : ?>
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
          <div class="widget-title">By Project Type</div>
          <ul class="calculator-nav-list">
            <?php foreach ($uses as $key => $use) : if (empty($by_use[$key])) continue; ?>
            <li class="calculator-nav-item"><a href="#<?php echo esc_attr('sizes-' . $key); ?>"><?php echo esc_html($use['label']); ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="sidebar-widget">
          <div class="widget-title">Tools</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/bags-vs-ready-mix-calculator/')); ?>">Bags vs. ready-mix</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">Concrete quote checker</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-cost-per-square-foot-calculator/')); ?>">Cost per sq ft calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-slab-cost-by-state/')); ?>">Slab cost by state</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-price-report/')); ?>">2026 price report</a></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
