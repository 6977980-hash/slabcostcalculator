<?php
/**
 * ROUTED TOOL PAGE — /fence-post-concrete-calculator/,
 * /concrete-steps-calculator/, /bags-vs-ready-mix-calculator/
 *
 * Routed by Slab_Architecture_Router (slab_route=tool_page, slab_slug=...).
 * Text and figures: inc/tool-pages.php; tool markup:
 * template-parts/tools/<part>.php; JS: supporting-calculators.js.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$slug = sanitize_title(get_query_var('slab_slug'));
$tool_page = Slab_Tool_Pages::get($slug);
$part = Slab_Tool_Pages::directory()[$slug][0];

get_header();
slab_render_breadcrumbs();
?>

<main id="primary" class="site-main">
  <div class="container">
    <div class="content-grid">
      <article class="article-content">
        <header class="page-header" style="margin-bottom: 2rem;">
          <h1 class="hero-title"><?php echo esc_html($tool_page['h1']); ?></h1>
          <p class="hero-subtitle"><?php echo esc_html($tool_page['subtitle']); ?></p>
          <p class="hero-answer"><?php echo wp_kses_post($tool_page['answer']); ?></p>
        </header>

        <?php get_template_part('template-parts/tools/' . $part); ?>

        <?php foreach ($tool_page['sections'] as $section) : ?>
          <h2><?php echo esc_html($section[0]); ?></h2>
          <?php echo wp_kses_post($section[1]); ?>
        <?php endforeach; ?>

        <section class="faq-section" aria-label="Frequently Asked Questions">
          <h2>Frequently Asked Questions</h2>
          <?php foreach ($tool_page['faqs'] as $faq) : ?>
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
          <div class="widget-title">Other Calculators</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/')); ?>">Slab Cost Calculator</a></li>
            <?php foreach (Slab_Tool_Pages::directory() as $tool_slug => $info) : if ($tool_slug === $slug) continue; ?>
              <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/' . $tool_slug . '/')); ?>"><?php echo esc_html($info[1]); ?></a></li>
            <?php endforeach; ?>
            <?php foreach (Slab_Calculator_Pages::directory() as $calc_slug => $calc_info) : ?>
              <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/' . $calc_slug . '/')); ?>"><?php echo esc_html($calc_info[1]); ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
