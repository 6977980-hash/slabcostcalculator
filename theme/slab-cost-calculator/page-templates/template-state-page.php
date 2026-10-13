<?php
/**
 * ONE STATE — /texas-concrete-slab-cost/ etc.
 *
 * Routed by Slab_Architecture_Router (slab_route=state_page, slab_slug=texas).
 * Written notes and figures: inc/state-pages.php. Prices are the national
 * model with the state's location factor, the same as the calculator.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$slug = sanitize_key(get_query_var('slab_slug'));
$d = Slab_State_Pages::data($slug);
$p = $d['prices'];
$nat = $d['national'];
$name = $d['name'];
$range = function ($pair) {
    return Slab_Data::money_range($pair, 0, ' – ');
};
$psf = function ($pair) {
    return Slab_Data::money_range($pair, 2, ' – ');
};
$fill = function ($text) use ($d) {
    return Slab_State_Pages::fill($text, $d);
};
$vs = Slab_State_Pages::vs_national($d['index']);
$code = Slab_Data::get('code');

get_header();
slab_render_breadcrumbs();
?>

<main id="primary" class="site-main state-page">
  <div class="container">
    <div class="content-grid">
      <article class="article-content">
        <header class="page-header" style="margin-bottom: 1.5rem;">
          <h1 class="hero-title">Concrete Slab Cost in <?php echo esc_html($name); ?></h1>
          <p class="hero-answer">
            <strong>Quick answer:</strong> a 4-inch concrete slab in <?php echo esc_html($name); ?> costs about <strong><?php echo esc_html($psf($p['slab']['psf'])); ?> per sq ft</strong> installed, <?php echo esc_html($vs); ?> the U.S. average. A 12 × 12 ft patio runs about <?php echo esc_html($range($p['patio']['installed'])); ?>, a 24 × 24 ft garage floor <?php echo esc_html($range($p['garage']['installed'])); ?> and a 20 × 40 ft driveway <?php echo esc_html($range($p['driveway']['installed'])); ?>.
          </p>
          <div class="trust-bar">
            <div class="trust-item"><strong>Last Updated:</strong> <time datetime="<?php echo esc_attr(SLAB_CONTENT_UPDATED); ?>"><?php echo esc_html(wp_date('F j, Y', strtotime(SLAB_CONTENT_UPDATED . ' 12:00:00'))); ?></time></div>
            <div class="trust-item"><a href="#how-priced" style="font-weight: 600; text-decoration: underline;">How these prices are worked out &rarr;</a></div>
          </div>
        </header>

        <div class="quantities-grid size-stats">
          <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
            <div class="quantity-card-label">Installed slab</div>
            <div class="quantity-card-value" style="color: var(--primary-800);"><?php echo esc_html($psf($p['slab']['psf'])); ?></div>
            <div class="quantity-card-note">per sq ft, 4 in</div>
          </div>
          <div class="quantity-card">
            <div class="quantity-card-label">Location factor</div>
            <div class="quantity-card-value"><?php echo esc_html(number_format($d['index'], 2)); ?></div>
            <div class="quantity-card-note">U.S. average = 1.00</div>
          </div>
          <div class="quantity-card">
            <div class="quantity-card-label">Ready-mix, 3,000 PSI</div>
            <div class="quantity-card-value"><?php echo esc_html($range($d['ready_mix'])); ?></div>
            <div class="quantity-card-note">per yd³ before delivery</div>
          </div>
          <div class="quantity-card">
            <div class="quantity-card-label">Labor</div>
            <div class="quantity-card-value"><?php echo esc_html($psf($d['labor'])); ?></div>
            <div class="quantity-card-note">per sq ft, place &amp; finish</div>
          </div>
        </div>

        <p><?php echo esc_html($fill($d['intro'])); ?></p>

        <h2 id="prices">Concrete Prices in <?php echo esc_html($name); ?> by Project</h2>
        <div class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Project</th><th><?php echo esc_html($name); ?> installed</th><th>Per sq ft</th><th>U.S. average</th><th>DIY materials</th></tr></thead>
            <tbody>
              <?php foreach (Slab_State_Pages::projects() as $key => $proj) : ?>
              <tr>
                <td><a href="<?php echo esc_url(home_url($proj[4])); ?>"><strong><?php echo esc_html($proj[0]); ?></strong></a></td>
                <td><?php echo esc_html($range($p[$key]['installed'])); ?></td>
                <td><?php echo esc_html($psf($p[$key]['psf'])); ?></td>
                <td><?php echo esc_html($range($nat[$key]['installed'])); ?></td>
                <td><?php echo esc_html($range($p[$key]['diy'])); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <p class="table-caption">4-inch slabs unless shown. Installed: forms, broom finish and labor, up to vapor barrier, #4 rebar and contractor overhead and profit. DIY: concrete (ready-mix or 80-lb bags, whichever is cheaper) and forms, up to vapor barrier and rebar. Excavation, gravel base and permits are extra.</p>

        <?php if ($d['metro_rows']) : ?>
        <h2 id="metros">Metro Areas</h2>
        <p>Prices in the biggest metro areas differ from the state as a whole, mainly because of labor costs.</p>
        <div class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Area</th><th>Factor</th><th>Slab per sq ft</th><th>12 × 12 patio</th><th>24 × 24 garage</th><th>20 × 40 driveway</th></tr></thead>
            <tbody>
              <tr>
                <td><strong><?php echo esc_html($name); ?> (statewide)</strong></td>
                <td><?php echo esc_html(number_format($d['index'], 2)); ?></td>
                <td><?php echo esc_html($psf($p['slab']['psf'])); ?></td>
                <td><?php echo esc_html($range($p['patio']['installed'])); ?></td>
                <td><?php echo esc_html($range($p['garage']['installed'])); ?></td>
                <td><?php echo esc_html($range($p['driveway']['installed'])); ?></td>
              </tr>
              <?php foreach ($d['metro_rows'] as $m) : ?>
              <tr>
                <td><strong><?php echo esc_html($m['name']); ?></strong></td>
                <td><?php echo esc_html(number_format($m['index'], 2)); ?></td>
                <td><?php echo esc_html($psf($m['prices']['slab']['psf'])); ?></td>
                <td><?php echo esc_html($range($m['prices']['patio']['installed'])); ?></td>
                <td><?php echo esc_html($range($m['prices']['garage']['installed'])); ?></td>
                <td><?php echo esc_html($range($m['prices']['driveway']['installed'])); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>

        <h2 id="building">Building a Slab in <?php echo esc_html($name); ?></h2>
        <div class="table-responsive">
          <table class="data-table">
            <tbody>
              <tr><th scope="row" style="width: 28%;">Weathering (IRC)</th><td><?php echo esc_html($d['weathering']); ?></td></tr>
              <tr><th scope="row">Concrete strength</th><td><?php echo esc_html($fill($d['psi_text'])); ?> <?php echo esc_html(number_format($d['psi'])); ?> PSI ready-mix runs about <?php echo esc_html($range($d['ready_mix_psi'])); ?> per yd³ here.</td></tr>
              <tr><th scope="row">Frost depth</th><td><?php echo esc_html($d['frost']); ?></td></tr>
              <tr><th scope="row">Soil</th><td><?php echo esc_html($d['soil']); ?></td></tr>
              <tr><th scope="row">Pouring season</th><td><?php echo esc_html($fill($d['season'])); ?></td></tr>
            </tbody>
          </table>
        </div>
        <ul>
          <?php foreach ($d['notes'] as $note) : ?>
          <li><?php echo esc_html($fill($note)); ?></li>
          <?php endforeach; ?>
        </ul>
        <p>Frost depths and permit rules are set by your city or county; check with the local building department before you dig. A permit for a small residential slab or driveway typically costs about <?php echo esc_html(Slab_Data::mr('permit.flatwork', 0, ' – ')); ?>.</p>

        <h2 id="calculator">Price Your Own Slab in <?php echo esc_html($name); ?></h2>
        <p>The calculator below starts at <?php echo esc_html($name); ?> prices. Enter your size, thickness, finish and reinforcement; if you have local prices for ready-mix or labor, type them over the defaults.</p>
        <div style="margin-bottom: 2.5rem;">
          <?php get_template_part('template-parts/calculator-primary', null, ['region' => 's-' . $d['code']]); ?>
        </div>

        <h2 id="how-priced">How These Prices Are Worked Out</h2>
        <p>These are modelled prices, not quotes collected from <?php echo esc_html($name); ?> contractors. Each project is priced with the same line items as the <a href="<?php echo esc_url(home_url('/')); ?>">slab cost calculator</a> at national unit prices, then <?php echo esc_html($name); ?>'s location factor of <?php echo esc_html(number_format($d['index'], 2)); ?> is applied to ready-mix concrete and labor, as the calculator does when you pick <?php echo esc_html($name); ?>. Bagged concrete, forms, rebar and the delivery fee stay at national prices. The factors are approximate, set by our editors to reflect typical construction cost differences between states; the full method is in the <a href="<?php echo esc_url(home_url('/concrete-price-report/')); ?>">2026 concrete price report</a>.</p>
        <p>Have a quote already? Check it with the <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">concrete quote checker</a>, which uses the same factors.</p>

        <section class="faq-section" aria-label="Frequently Asked Questions">
          <h2 id="faq"><?php echo esc_html($name); ?> Concrete Questions</h2>
          <?php foreach (Slab_State_Pages::faqs($slug) as $faq) : ?>
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
          <div class="widget-title">Other States</div>
          <ul class="calculator-nav-list">
            <?php foreach (Slab_State_Pages::states() as $other => $s) : if ($other === $slug) continue; $os = Slab_Data::states()[$s['code']]; ?>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url(Slab_State_Pages::path($other))); ?>"><span><?php echo esc_html($os['name']); ?></span><span class="calculator-nav-badge"><?php echo esc_html(number_format($os['index'], 2)); ?></span></a></li>
            <?php endforeach; ?>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url(Slab_State_Pages::hub_path())); ?>"><span>All 50 states</span><span>&rarr;</span></a></li>
          </ul>
        </div>
        <div class="sidebar-widget">
          <div class="widget-title">On This Page</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="#prices">Prices by project</a></li>
            <?php if ($d['metro_rows']) : ?><li class="calculator-nav-item"><a href="#metros">Metro areas</a></li><?php endif; ?>
            <li class="calculator-nav-item"><a href="#building">Climate, frost &amp; soil</a></li>
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
