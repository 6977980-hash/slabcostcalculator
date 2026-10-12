<?php
/**
 * AUTHOR ARCHIVE TEMPLATE
 *
 * @package SlabCostCalculator
 */

get_header();
slab_render_breadcrumbs();
?>

<main id="primary" class="site-main">
  <div class="container container-narrow">
    <header class="page-header" style="margin-bottom: 2.5rem;">
      <h1 class="hero-title">Author: <?php echo get_the_author(); ?></h1>
      <p class="hero-subtitle">
        <?php the_author_meta('description'); ?>
      </p>
    </header>

    <?php get_template_part('template-parts/author-bio'); ?>

    <h2>Published Articles &amp; Guides</h2>
    <?php if (have_posts()) : ?>
      <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-top: 1.5rem;">
        <?php while (have_posts()) : the_post(); ?>
          <article class="sidebar-widget" style="margin-bottom: 0;">
            <h3 style="margin-top: 0;"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <p><?php the_excerpt(); ?></p>
            <a href="<?php the_permalink(); ?>" style="font-weight: 700;">Read Guide &rarr;</a>
          </article>
        <?php endwhile; ?>
      </div>
      <div style="margin-top: 2rem;">
        <?php the_posts_pagination(); ?>
      </div>
    <?php else : ?>
      <p>No guides published by this author yet.</p>
    <?php endif; ?>
  </div>
</main>

<?php
get_footer();
