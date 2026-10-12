<?php
/**
 * Template Name: How-To Instructional Guide
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
slab_render_breadcrumbs();

$sd = Slab_Data::all();
?>

<main id="primary" class="site-main">
  <div class="container">
    <div class="content-grid">
      <article class="article-content">
        <header class="page-header" style="margin-bottom: 2rem;">
          <h1 class="hero-title"><?php the_title(); ?></h1>
          <div class="trust-bar" style="margin: 1rem 0;">
            <div class="trust-item"><strong>Guide Type:</strong> <span>Step-by-Step Construction Guide</span></div>
            <div class="trust-item"><strong>Standards:</strong> <span>ACI 302.1R Compliant</span></div>
            <div class="trust-item"><strong>Difficulty:</strong> <span>Intermediate DIY / Pro</span></div>
          </div>
        </header>

        <div class="callout callout-info">
          <div class="callout-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            Summary Checklist for Pouring a Durable Concrete Slab
          </div>
          <p>
            A high-quality, long-lasting slab requires six sequential milestones: (1) Excavation &amp; subgrade compaction, (2) 4" crushed stone base, (3) Square &amp; braced formwork, (4) Rebar grid on support chairs, (5) Monolithic pour &amp; screeding, (6) Proper moisture retention curing for 7+ days.
          </p>
        </div>

        <h2>Step 1: Planning, Layout &amp; Square Staking</h2>
        <p>
          Measure your slab footprint and drive wooden stakes at each corner. Use the <strong>3-4-5 Pythagorean triangle method</strong> (or measure diagonal corner-to-corner distances) to verify that all form angles are exactly 90 degrees.
        </p>

        <h2>Step 2: Excavation and Subgrade Preparation</h2>
        <p>
          Dig out all organic sod, topsoil, and loose material to a total depth equal to the slab thickness plus the gravel base (e.g., 8 inches deep for a 4-inch slab on 4 inches of crushed stone). Run a mechanical plate compactor over the bare earth subgrade.
        </p>

        <h2>Step 3: Installing &amp; Compacting the Gravel Base</h2>
        <p>
          Distribute <?php echo esc_html(Slab_Data::r('site.gravel_depth_range')); ?> inches of #57 crushed stone or dense-graded aggregate. Compact thoroughly in 2-inch lifts. A solid, uniform base prevents cracking and provides critical drainage.
        </p>

        <h2>Step 4: Building &amp; Bracing Formwork</h2>
        <p>
          Erect straight 2x4 (for 4" slabs) or 2x6 (for 5"-6" slabs) lumber forms. Secure them with wood or metal stakes spaced every 3 feet. Slope the forms slightly (<?php echo esc_html(implode(' inch to ', $sd['site']['drainage_slope_in_per_ft'])); ?> inch per linear foot, <?php echo esc_html($sd['site']['drainage_slope_preferred']); ?> inch preferred next to a house) away from any adjacent house foundation to ensure positive rainwater drainage.
        </p>

        <h2>Step 5: Placing Vapor Barrier &amp; Rebar Reinforcement</h2>
        <p>
          Lay down a continuous 10-mil to 15-mil polyethylene vapor retarder sheet. Overlap seams by 6 inches and tape with vapor tape. Place #4 (1/2") steel rebar on a 12-inch to 18-inch grid pattern, supporting the rebar on concrete or plastic chairs so it remains centered within the slab depth.
        </p>

        <h2>Step 6: Pouring, Screeding, Floating &amp; Curing</h2>
        <p>
          Discharge the ready-mix concrete starting from the furthest corner. Screed the wet concrete flat using a straight 2x4 straightedge resting on the form tops. Bull-float the surface immediately to push large aggregate down and bring cement paste to the top. When surface bleed water disappears, apply a non-slip broom texture and plan control joints at <?php echo esc_html(Slab_Data::r('joints.ratio')); ?> times the slab thickness in inches, expressed in feet (every <?php echo esc_html(Slab_Data::range([4 * $sd['joints']['ratio_best'][0], 4 * $sd['joints']['ratio_best'][1]])); ?> feet for a 4-inch slab, never more than <?php echo esc_html($sd['joints']['max_ft'][4]); ?> feet). Tool them in now or saw-cut them <?php echo esc_html(Slab_Data::r('joints.sawcut_h')); ?> hours after finishing, one quarter of the slab depth.
        </p>

        <div style="margin: 2.5rem 0; padding: 1.5rem; background: var(--primary-50); border: 1px solid var(--primary-200); border-radius: var(--radius-lg); text-align: center;">
          <h3 style="margin-top: 0; color: var(--primary-900);">Calculate Your Project's Required Materials</h3>
          <p style="color: var(--slate-700);">Ready to calculate the exact cubic yards, gravel tonnage, rebar sticks, and cost for your slab?</p>
          <a href="<?php echo esc_url(home_url('/')); ?>#concrete-slab-calculator" class="header-cta-btn" style="padding: 0.75rem 1.5rem;">Launch Calculator &rarr;</a>
        </div>

        <?php get_template_part('template-parts/author-bio'); ?>
      </article>

      <aside class="sidebar-col">
        <div class="sidebar-widget">
          <div class="widget-title">Related Guides</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/how-to/')); ?>">How-To Knowledgebase Hub</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-volume-calculator/')); ?>">Volume Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/how-much-does-a-concrete-slab-cost/')); ?>">Cost Breakdown Guide</a></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
