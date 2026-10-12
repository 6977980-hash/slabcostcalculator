<?php
/**
 * ARCHIVE TEMPLATE
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
    <header class="page-header" style="margin-bottom: 2.5rem;">
      <h1 class="hero-title"><?php the_archive_title(); ?></h1>
      <?php the_archive_description('<div class="hero-subtitle">', '</div>'); ?>
    </header>

    <div class="content-grid">
      <div>
        <?php if (have_posts()) : ?>
          <div style="display: flex; flex-direction: column; gap: 2rem;">
            <?php while (have_posts()) : the_post(); ?>
              <article class="sidebar-widget" style="margin-bottom: 0;">
                <h2 style="font-size: 1.5rem; font-weight: 800; margin-top: 0; margin-bottom: 0.5rem; border: none; padding: 0;">
                  <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>
                <div class="article-excerpt" style="color: var(--slate-600); margin-bottom: 1rem;">
                  <?php the_excerpt(); ?>
                </div>
                <a href="<?php the_permalink(); ?>" style="font-weight: 700;">Read Guide &rarr;</a>
              </article>
            <?php endwhile; ?>
          </div>

          <div style="margin-top: 2rem;">
            <?php the_posts_pagination(); ?>
          </div>
        <?php else : ?>
          <p>No posts found in this archive.</p>
        <?php endif; ?>
      </div>

      <aside class="sidebar-col">
        <div class="sidebar-widget">
          <div class="widget-title">Calculators</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/')); ?>">Slab Cost Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-volume-calculator/')); ?>">Volume Calculator</a></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
