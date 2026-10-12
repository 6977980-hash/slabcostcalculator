<?php
/**
 * Template Name: Application Category Hub
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
          <p class="hero-subtitle">
            Comprehensive planning, thickness specifications, reinforcement requirements, and cost estimating guide for this project type.
          </p>
        </header>

        <!-- Embedded Interactive Estimator -->
        <div style="margin-bottom: 2.5rem;">
          <?php get_template_part('template-parts/calculator-primary'); ?>
        </div>

        <h2>Recommended Specifications for This Application</h2>
        <ul>
          <li><strong>Standard Slab Thickness:</strong> 4 inches for pedestrian flatwork; 5 to 6 inches for passenger vehicles and light trucks; 6+ inches for RV parking pads.</li>
          <li><strong>Concrete Mix Strength:</strong> Minimum <?php echo esc_html(number_format($sd['code']['psi_exposed'])); ?> PSI for exposed slabs in mild climates; <?php echo esc_html(number_format($sd['code']['psi_severe'])); ?> PSI air-entrained (<?php echo esc_html(Slab_Data::r('concrete.air_entrainment_pct', '%–')); ?>% air) where freeze-thaw weathering is severe (the IRC minimum), and <?php echo esc_html(number_format($sd['code']['psi_deicing_recommended'])); ?> PSI where de-icing salt is used.</li>
          <li><strong>Sub-Base Preparation:</strong> <?php echo esc_html(Slab_Data::r('site.gravel_depth_range')); ?> inches of compacted #57 crushed stone base to allow water drainage and mitigate soil movement.</li>
          <li><strong>Reinforcement:</strong> #4 (1/2") steel rebar spaced at 12" to 18" grid centers on support chairs, or 6x6 welded wire mesh.</li>
        </ul>

        <h2>Installed vs. DIY Material Cost Matrix</h2>
        <?php get_template_part('template-parts/cost-tables'); ?>

        <h2>Important Construction Considerations</h2>
        <p>
          Slope the slab <?php echo esc_html(implode(' to ', $sd['site']['drainage_slope_in_per_ft'])); ?> inch per foot away from adjoining building walls for drainage (<?php echo esc_html($sd['site']['drainage_slope_preferred']); ?> inch per foot is preferred next to a house). Space control joints <?php echo esc_html(Slab_Data::r('joints.ratio')); ?> times the slab thickness in inches, in feet, and saw-cut them to 1/4 of the slab thickness <?php echo esc_html(Slab_Data::r('joints.sawcut_h')); ?> hours after finishing (<?php echo esc_html(Slab_Data::r('joints.early_entry_h')); ?> hours with an early-entry saw) to prevent uncontrolled cracking.
        </p>

        <?php get_template_part('template-parts/author-bio'); ?>
      </article>

      <aside class="sidebar-col">
        <div class="sidebar-widget">
          <div class="widget-title">Other Applications</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/patio/')); ?>">Patio Slabs</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/driveway/')); ?>">Driveway Slabs</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/garage/')); ?>">Garage Floor Slabs</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/shed/')); ?>">Shed Pads</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/foundation/')); ?>">Monolithic Foundations</a></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
