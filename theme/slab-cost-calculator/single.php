<?php
/**
 * SINGLE POST TEMPLATE
 *
 * @package SlabCostCalculator
 */

get_header();
slab_render_breadcrumbs();
?>

<main id="primary" class="site-main">
  <div class="container">
    <div class="content-grid">
      <article class="article-content">
        <?php
        while (have_posts()) :
          the_post();
        ?>
          <header class="page-header" style="margin-bottom: 2rem;">
            <h1 class="hero-title"><?php the_title(); ?></h1>
            <div class="trust-bar" style="margin: 1rem 0;">
              <div class="trust-item"><strong>Published:</strong> <span><?php echo get_the_date('F j, Y'); ?></span></div>
              <div class="trust-item"><strong>Updated:</strong> <span><?php echo get_the_modified_date('F j, Y'); ?></span></div>
              <div class="trust-item"><strong>Author:</strong> <span><?php the_author(); ?></span></div>
            </div>
          </header>

          <div class="entry-content">
            <?php the_content(); ?>
          </div>

          <div style="margin: 3rem 0; padding: 2rem; background: var(--primary-50); border: 1px solid var(--primary-200); border-radius: var(--radius-xl); text-align: center;">
            <h3 style="margin-top: 0; color: var(--primary-900);">Calculate Your Concrete Project Budget</h3>
            <p style="color: var(--slate-700);">Use our comprehensive concrete slab cost calculator to get a complete line-item breakdown of materials, labor, reinforcement, and base preparation.</p>
            <a href="<?php echo esc_url(home_url('/')); ?>#concrete-slab-calculator" class="header-cta-btn" style="padding: 0.85rem 1.75rem; font-size: 1rem;">Launch Concrete Slab Calculator &rarr;</a>
          </div>

          <?php get_template_part('template-parts/author-bio'); ?>

        <?php
        endwhile;
        ?>
      </article>

      <aside class="sidebar-col">
        <div class="sidebar-widget">
          <div class="widget-title">Calculators</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/')); ?>">Slab Cost Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-volume-calculator/')); ?>">Volume Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-bags-calculator/')); ?>">Bags Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-rebar-calculator/')); ?>">Rebar Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/gravel-base-calculator/')); ?>">Gravel Base Calculator</a></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
