<?php
/**
 * Template Name: Cost Guide Deep-Dive
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
slab_render_breadcrumbs();
?>

<main id="primary" class="site-main">
  <div class="container">
    <div class="content-grid">
      <article class="article-content">
        <header class="page-header" style="margin-bottom: 2rem;">
          <h1 class="hero-title"><?php the_title(); ?></h1>
          <div class="trust-bar" style="margin: 1rem 0;">
            <div class="trust-item"><strong>Category:</strong> <span>Concrete Flatwork Pricing</span></div>
            <div class="trust-item"><strong>Market Data:</strong> <span><?php echo esc_html(wp_date('F Y', strtotime(SLAB_CONTENT_UPDATED . ' 12:00:00'))); ?> Benchmark</span></div>
            <div class="trust-item"><strong>Review:</strong> <span>Verified Estimating Team</span></div>
          </div>
        </header>

        <p>
          Planning a concrete slab project requires understanding current regional pricing schedules for ready-mix batch plants, aggregate suppliers, and finishing labor. This comprehensive guide breaks down installed costs, DIY material pricing, and budgeting line items.
        </p>

        <!-- Dynamic Embedded Pricing Table -->
        <h2>Standard Residential Slab Cost Matrix (2026 Data)</h2>
        <?php get_template_part('template-parts/cost-tables'); ?>

        <h2>Material vs. Labor Cost Breakdown</h2>
        <p>
          For a standard residential flatwork installation, project budgets typically distribute across the following percentage ranges:
        </p>

        <ul>
          <li><strong>Finishing &amp; Placement Labor (40% – 50%):</strong> Site excavation, formwork erection, grading, sub-base compaction, placement, screeding, bull-floating, power troweling, and control joint grooving ($3.50–$7.00/sq. ft.).</li>
          <li><strong>Ready-Mix Concrete Materials (30% – 35%):</strong> 3,000 to 4,000 PSI concrete delivered via transit mixer ($135–$175/yd³).</li>
          <li><strong>Gravel Sub-Base &amp; Preparation (10% – 15%):</strong> 4 inches of compacted #57 crushed stone or crushed aggregate base ($35–$55/yd³).</li>
          <li><strong>Reinforcement &amp; Form Lumber (5% – 10%):</strong> #4 rebar grid, support chairs, 2x4/2x6 lumber, stakes, and 10-mil vapor barriers.</li>
        </ul>

        <h2>Interactive Estimate Generator</h2>
        <p>
          Need a customized estimate tailored to your exact slab dimensions, local labor rates, and project specifications? Use our interactive calculator:
        </p>

        <div style="margin: 2rem 0; text-align: center;">
          <a href="<?php echo esc_url(home_url('/')); ?>#concrete-slab-calculator" class="header-cta-btn" style="padding: 0.85rem 1.75rem; font-size: 1.0625rem;">
            Open Concrete Slab Cost Calculator &rarr;
          </a>
        </div>

        <h2>Regional Pricing Variations &amp; Why Quotes Differ</h2>
        <p>
          It is common to receive quotes differing by 20% to 40% from two qualified contractors in the same municipality. Key reasons include:
        </p>
        <ol>
          <li><strong>Site Accessibility:</strong> Easy truck access vs. long wheelbarrow runs or specialized pump trucks ($600+ rental).</li>
          <li><strong>Soil Subgrade Stability:</strong> Stable sand/gravel subgrade vs. expansive clay requiring deep excavation and heavy crushed aggregate backfill.</li>
          <li><strong>Frost Line Depth Requirements:</strong> Northern climates requiring thickened edge footings reaching 36–48 inches below grade to prevent frost heave.</li>
          <li><strong>Surface Finish Requirements:</strong> Standard broom finish vs. multi-stage color stamped decorative flatwork.</li>
        </ol>

        <?php get_template_part('template-parts/author-bio'); ?>

      </article>

      <!-- Sidebar -->
      <aside class="sidebar-col">
        <div class="sidebar-widget">
          <div class="widget-title">Calculators</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/')); ?>">Slab Cost Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-volume-calculator/')); ?>">Volume Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-bags-calculator/')); ?>">Bags Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-rebar-calculator/')); ?>">Rebar Calculator</a></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
