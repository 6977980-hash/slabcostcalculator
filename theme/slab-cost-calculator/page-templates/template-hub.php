<?php
/**
 * Template Name: Application Category Hub
 *
 * Project-type pages (/patio/, /driveway/, /garage/, /shed/, /foundation/,
 * /floor/). Each page's own sizes, specs, notes and FAQ come from
 * inc/applications.php; every figure comes from inc/site-data.php.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
slab_render_breadcrumbs();

global $post;
$sd = Slab_Data::all();
$app_slug = $post ? $post->post_name : '';
$app = Slab_Applications::get($app_slug);
?>

<main id="primary" class="site-main">
  <div class="container">
    <div class="content-grid">
      <article class="article-content">
        <header class="page-header" style="margin-bottom: 2rem;">
          <h1 class="hero-title"><?php the_title(); ?></h1>
          <?php if ($app) : ?>
            <p class="hero-subtitle"><?php echo esc_html($app['subtitle']); ?></p>
            <p class="hero-answer"><?php echo wp_kses_post($app['answer']); ?></p>
          <?php else : ?>
            <p class="hero-subtitle">Thickness, strength, base, reinforcement and cost guidance for this project type.</p>
          <?php endif; ?>
        </header>

        <!-- Embedded Interactive Estimator -->
        <div style="margin-bottom: 2.5rem;">
          <?php get_template_part('template-parts/calculator-primary', null, ['preset' => $app ? $app['preset'] : '']); ?>
        </div>

        <?php if ($app) : ?>

          <h2><?php echo esc_html($app['size_title']); ?></h2>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Project</th>
                  <th>Size</th>
                  <th>Concrete (+<?php echo (int) $sd['concrete']['waste_pct']; ?>% waste)</th>
                  <?php if ($app['sizes'][0]['diy'] !== null) : ?><th>DIY Materials</th><?php endif; ?>
                  <th>Installed</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($app['sizes'] as $row) : ?>
                  <tr>
                    <td><strong><?php echo esc_html($row['label']); ?></strong></td>
                    <td><?php echo esc_html($row['dims'] . ' (' . number_format($row['area']) . ' sq ft)'); ?></td>
                    <td><?php echo esc_html(number_format($row['yd'], 2)); ?> yd³<?php if ($row['bags'] && $row['yd'] < 3) : ?> <span class="table-sub">(<?php echo (int) $row['bags']; ?> × 80-lb bags)</span><?php endif; ?></td>
                    <?php if ($row['diy'] !== null) : ?><td><?php echo esc_html(Slab_Data::money_range($row['diy'], 0, '–')); ?></td><?php endif; ?>
                    <td><?php echo esc_html(Slab_Data::money_range($row['installed'], 0, '–')); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <div class="table-caption"><?php echo esc_html($app_slug === 'foundation'
                ? 'Installed range uses the monolithic slab rate of ' . Slab_Data::money_range(Slab_Data::foundation_psf(), 0, '–') . '/sq ft (4 in floor with thickened edge, see our methodology). Concrete includes the thickened edge and waste.'
                : Slab_Data::cost_basis_note()); ?></div>
          </div>

          <h2>Recommended Specifications</h2>
          <div class="table-responsive">
            <table class="data-table">
              <tbody>
                <?php foreach ($app['specs'] as $spec) : ?>
                  <tr><th scope="row" style="width: 30%;"><?php echo esc_html($spec[0]); ?></th><td><?php echo esc_html($spec[1]); ?></td></tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <?php foreach ($app['sections'] as $section) : ?>
            <h2><?php echo esc_html($section[0]); ?></h2>
            <?php echo wp_kses_post($section[1]); ?>
          <?php endforeach; ?>

          <section class="faq-section" aria-label="Frequently Asked Questions">
            <h2>Frequently Asked Questions</h2>
            <?php foreach ($app['faqs'] as $faq) : ?>
              <div class="faq-item">
                <button type="button" class="faq-question" aria-expanded="false"><?php echo esc_html($faq['q']); ?></button>
                <div class="faq-answer"><p><?php echo esc_html($faq['a']); ?></p></div>
              </div>
            <?php endforeach; ?>
          </section>

          <h2>Related Guides</h2>
          <ul>
            <?php foreach ($app['links'] as $link) : ?>
              <li><a href="<?php echo esc_url(home_url($link[1])); ?>"><?php echo esc_html($link[0]); ?></a></li>
            <?php endforeach; ?>
            <li><a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">Check a contractor quote</a></li>
          </ul>

        <?php else : ?>

          <h2>Installed vs. DIY Material Cost Matrix</h2>
          <?php get_template_part('template-parts/cost-tables'); ?>

        <?php endif; ?>

        <?php get_template_part('template-parts/author-bio'); ?>
      </article>

      <aside class="sidebar-col">
        <div class="sidebar-widget">
          <div class="widget-title">Other Project Types</div>
          <ul class="calculator-nav-list">
            <?php foreach (Slab_Applications::labels() as $slug => $label) : if ($slug === $app_slug) continue; ?>
              <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/' . $slug . '/')); ?>"><?php echo esc_html($label); ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
