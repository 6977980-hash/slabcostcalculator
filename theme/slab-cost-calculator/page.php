<?php
/**
 * DEFAULT PAGE TEMPLATE
 *
 * @package SlabCostCalculator
 */

get_header();
slab_render_breadcrumbs();
?>

<main id="primary" class="site-main">
  <div class="container container-narrow">
    <article class="article-content">
      <?php
      while (have_posts()) :
        the_post();
      ?>
        <header class="page-header" style="margin-bottom: 2rem;">
          <h1 class="hero-title"><?php the_title(); ?></h1>
        </header>

        <div class="entry-content">
          <?php the_content(); ?>
        </div>
      <?php
      endwhile;
      ?>
    </article>
  </div>
</main>

<?php
get_footer();
