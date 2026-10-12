<?php
/**
 * CONCRETE QUOTE CHECKER — /concrete-quote-checker/
 *
 * Routed by Slab_Architecture_Router (slab_route=quote_checker). The full
 * calculator with its Live Estimate panel opened on the Bid Check tab, plus
 * a guide to judging a concrete quote. Figures: inc/quote-checker.php.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
slab_render_breadcrumbs();

$b20 = slab_quote_benchmark(20, 20);
$psf = Slab_Data::installed_psf();
$op = (int) round(Slab_Data::get('overhead_profit') * 100);
$over_pct = (int) round(((1 + Slab_Data::get('overhead_profit')) * 1.30 - 1) * 100);
$m = function ($n) {
    return Slab_Data::money(round($n / 10) * 10, 0);
};
// Worked examples: three sizes, each with a quote that lands in a different band.
$examples = [
    ['b' => slab_quote_benchmark(12, 12), 'label' => '12 × 12 ft patio', 'quote_of' => ['fair', 0.92]],
    ['b' => slab_quote_benchmark(20, 20), 'label' => '20 × 20 ft slab', 'quote_of' => ['fair', 1.15]],
    ['b' => slab_quote_benchmark(24, 24), 'label' => '24 × 24 ft garage floor', 'quote_of' => ['over', 1.10]],
];
foreach ($examples as $i => $ex) {
    $examples[$i]['quote'] = round($ex['b'][$ex['quote_of'][0]] * $ex['quote_of'][1] / 50) * 50;
}
?>

<section class="hero-section quote-hero">
  <div class="container">
    <h1 class="hero-title">Concrete Quote Checker: Is Your Contractor's Price Fair?</h1>
    <p class="hero-subtitle">
      Enter your slab and the price you were quoted. The checker builds a benchmark for the same slab and scope (materials at your prices plus standard labor for your area) and shows whether the quote is below cost, fair, premium or overpriced, and which part of the bid carries the markup.
    </p>
    <p class="hero-answer">
      <strong>Quick answer:</strong> a fair quote usually lands between the job's direct cost and about <?php echo esc_html($op); ?>% above it. For a 20 × 20 ft, 4-inch slab with forms, vapor barrier, #4 rebar and a broom finish that is about <strong><?php echo esc_html($m($b20['fair']) . ' to ' . $m($b20['high'])); ?></strong> at U.S. average prices (<?php echo esc_html(Slab_Data::money_range($psf, 0, ' to ')); ?> per sq ft for most installed slabs).
    </p>

    <div class="trust-bar">
      <div class="trust-item">
        <strong>Last Updated:</strong> <time datetime="<?php echo esc_attr(SLAB_CONTENT_UPDATED); ?>"><?php echo esc_html(wp_date('F j, Y', strtotime(SLAB_CONTENT_UPDATED . ' 12:00:00'))); ?></time>
      </div>
      <div class="trust-item">
        <a href="<?php echo esc_url(home_url('/methodology/')); ?>" style="font-weight: 600; text-decoration: underline;">How the benchmark is calculated &rarr;</a>
      </div>
    </div>

    <?php get_template_part('template-parts/calculator-primary', null, ['tab' => 'bid']); ?>
  </div>
</section>

<main id="primary" class="site-main">
  <div class="container">
    <div class="content-grid">
      <article class="article-content">

        <h2 id="how-it-works">How the Quote Checker Works</h2>
        <p>
          The checker prices the slab you describe in the calculator line by line, the same way an estimator would. Concrete, reinforcement, base, forms, vapor barrier, finish, delivery, pump and demolition use the prices in the calculator (change them to your local prices). Labor uses the standard <?php echo esc_html(Slab_Data::money(Slab_Data::get('labor.sqft_default'), 2)); ?> per sq ft for placing and finishing, adjusted by the location you pick. That total is the <strong>benchmark</strong>: what the job costs before the contractor's overhead and profit.
        </p>
        <p>Your quote is then placed in one of five bands:</p>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr><th>Quote compared with the benchmark</th><th>Verdict</th><th>What to do</th></tr>
            </thead>
            <tbody>
              <tr><td>Less than the materials alone</td><td><strong>Below material cost</strong></td><td>Ask what is left out (base, rebar, finishing, cleanup) and get the scope in writing.</td></tr>
              <tr><td>Up to the benchmark</td><td><strong>Great deal</strong></td><td>Little or no margin. Confirm the scope and check references.</td></tr>
              <tr><td>Up to <?php echo esc_html($op); ?>% above it</td><td><strong>Fair market price</strong></td><td>Normal overhead and profit for a residential slab.</td></tr>
              <tr><td>Up to about <?php echo esc_html($over_pct); ?>% above it</td><td><strong>Premium</strong></td><td>Hard access, a pump, a rush date or a minimum charge can explain it. Ask for an itemized bid.</td></tr>
              <tr><td>More than that</td><td><strong>Overpriced</strong></td><td>Over 30% above the top of the fair range. Get at least two more quotes.</td></tr>
            </tbody>
          </table>
        </div>
        <p>
          If you have the bid's line items, open <em>"enter the bid's line items"</em> in the Bid Check tab. The checker compares concrete and reinforcement, labor, site prep and base, and everything else separately and points to the line with the largest markup.
        </p>

        <h2 id="worked-examples">Worked Examples (U.S. Average Prices)</h2>
        <p>Each example uses the calculator's default scope: 4-inch slab, ready-mix with a delivery fee, forms, vapor barrier, #4 rebar at 18 inches, broom finish and standard labor.</p>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr><th>Slab</th><th>Benchmark</th><th>Fair range</th><th>Quote</th><th>Verdict</th></tr>
            </thead>
            <tbody>
              <?php foreach ($examples as $ex) : $b = $ex['b']; ?>
              <tr>
                <td><strong><?php echo esc_html($ex['label']); ?></strong><br><span class="table-sub"><?php echo esc_html(number_format($b['area'])); ?> sq ft</span></td>
                <td><?php echo esc_html($m($b['fair'])); ?></td>
                <td><?php echo esc_html($m($b['fair']) . ' – ' . $m($b['high'])); ?></td>
                <td><?php echo esc_html(Slab_Data::money($ex['quote'], 0)); ?><br><span class="table-sub"><?php echo esc_html(Slab_Data::money($ex['quote'] / $b['area'], 2)); ?>/sq ft</span></td>
                <td><?php echo esc_html(slab_quote_tier($ex['quote'], $b)); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <p>Set the calculator to your own slab and local prices to get your benchmark; the regional index in the calculator adjusts labor and material rates for your state.</p>

        <h2 id="what-a-quote-should-include">What a Fair Concrete Quote Includes</h2>
        <p>Two quotes can only be compared if they cover the same work. A complete written quote lists:</p>
        <ul>
          <li><strong>Size and thickness</strong> of the slab, and the concrete strength (PSI) and volume in cubic yards.</li>
          <li><strong>Site prep:</strong> excavation, grading, and the depth of the compacted gravel base.</li>
          <li><strong>Forms</strong> and, for garages and enclosed slabs, a vapor barrier.</li>
          <li><strong>Reinforcement:</strong> rebar size and spacing, or wire mesh, with chairs to hold it at mid-depth.</li>
          <li><strong>Finish</strong> (broom, trowel, exposed aggregate, stamped), control joints and curing.</li>
          <li><strong>Delivery, short-load and pump charges</strong>, plus demolition and haul-off if an old slab is removed.</li>
          <li><strong>Permits and inspections</strong>, cleanup, the payment schedule and the warranty.</li>
        </ul>
        <p>Set the same scope in the calculator before you compare. A quote that includes a pump or demolition will rightly be higher than one that does not.</p>

        <h2 id="red-flags">Red Flags in a Concrete Contractor Quote</h2>
        <ul>
          <li><strong>A price below material cost</strong>, which usually means part of the work is missing or will be added later.</li>
          <li><strong>No thickness, PSI or reinforcement</strong> written on the quote.</li>
          <li><strong>A large cash deposit</strong> before any work starts, or pressure to sign the same day.</li>
          <li><strong>No license or insurance details</strong>, and no references for similar slabs.</li>
          <li><strong>"Rebar optional" on a driveway or garage</strong>, where vehicle loads need it.</li>
          <li><strong>A single lump sum</strong> with no breakdown when you ask for one.</li>
        </ul>

        <section class="faq-section" aria-label="Frequently Asked Questions">
          <h2 id="faq">Concrete Quote Questions</h2>
          <?php foreach (slab_quote_checker_faqs() as $faq) : ?>
          <div class="faq-item">
            <button type="button" class="faq-question" aria-expanded="false">
              <?php echo esc_html($faq['q']); ?>
            </button>
            <div class="faq-answer">
              <?php echo esc_html($faq['a']); ?>
            </div>
          </div>
          <?php endforeach; ?>
        </section>

        <?php get_template_part('template-parts/author-bio'); ?>
      </article>

      <aside class="sidebar-col">
        <div class="sidebar-widget">
          <div class="widget-title">Before You Sign</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/how-much-does-a-concrete-slab-cost/')); ?>"><span>Concrete Slab Cost Guide</span><span>&rarr;</span></a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-cost-per-square-foot-pricing-matrix/')); ?>"><span>Cost Per Square Foot</span><span>&rarr;</span></a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/how-to-estimate-concrete-labor-cost/')); ?>"><span>Concrete Labor Cost</span><span>&rarr;</span></a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-slab-permits-and-costs/')); ?>"><span>Slab Permits &amp; Costs</span><span>&rarr;</span></a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/diy-concrete-slab-what-you-can-and-shouldnt-do/')); ?>"><span>DIY vs. Contractor</span><span>&rarr;</span></a></li>
          </ul>
        </div>
        <div class="sidebar-widget">
          <div class="widget-title">Cost by Slab Size</div>
          <ul class="calculator-nav-list">
            <?php foreach ([[10, 10], [12, 12], [20, 20], [24, 24]] as $sz) : $c = Slab_Data::size_costs($sz[0], $sz[1], 4); ?>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/' . $sz[0] . 'x' . $sz[1] . '-concrete-slab-cost-breakdown/')); ?>"><span><?php echo esc_html($sz[0] . 'x' . $sz[1] . ' Slab Cost'); ?></span><span class="calculator-nav-badge"><?php echo esc_html(Slab_Data::money_range($c['installed'], 0, '–')); ?></span></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="sidebar-widget">
          <div class="widget-title">On This Page</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="#how-it-works">How the checker works</a></li>
            <li class="calculator-nav-item"><a href="#worked-examples">Worked examples</a></li>
            <li class="calculator-nav-item"><a href="#what-a-quote-should-include">What a quote should include</a></li>
            <li class="calculator-nav-item"><a href="#red-flags">Red flags</a></li>
            <li class="calculator-nav-item"><a href="#faq">Questions</a></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
