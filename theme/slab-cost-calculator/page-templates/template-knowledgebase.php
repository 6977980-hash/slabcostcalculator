<?php
/**
 * KNOWLEDGEBASE HUB — /knowledgebase/
 *
 * Stable, self-contained replacement for the old page_for_posts-dependent
 * "Articles" link. Lists every published post (post_type=post), grouped by
 * category, so new posts appear here automatically the moment they're
 * published — no manual menu or hub-page editing required. Reuses the exact
 * .subcalc-card grid pattern already established on the homepage's
 * "Knowledgebase & Articles" section for visual consistency.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
slab_render_breadcrumbs();

/**
 * This single template now renders THREE distinct nav routes:
 * /knowledgebase/ (every article), /cost-guides/ (pricing-focused subset),
 * and /how-to/ (build/repair-instruction subset) — see the router fix in
 * inc/custom-post-types.php's route_custom_templates(). The category
 * names below are the site's own existing taxonomy, assigned per-article
 * in inc/demo-content-importer.php, not an invented grouping.
 */
$slab_route = get_query_var('slab_route');

$hub_config = [
    'cost_guide_hub' => [
        // Matched by category SLUG, not display name — see the bug-fix
        // note below the loop for why.
        'category_slugs' => ['cost-guides', 'concrete-costs'],
        'h1'              => 'Concrete Slab Cost Guides',
        'intro'           => 'Real 2026 pricing for concrete slabs by size, application, and unit rate — per square foot, per cubic yard, and full project breakdowns. Every figure here matches the numbers our <a href="' . esc_url(home_url('/')) . '#concrete-slab-calculator">interactive calculator</a> produces.',
    ],
    'how_to_hub' => [
        'category_slugs' => ['concrete-construction', 'concrete-maintenance-repair'],
        'h1'              => 'How-To: Concrete Slab Guides',
        'intro'           => 'Step-by-step guides for pouring, reinforcing, curing, and repairing a concrete slab — aligned with ACI 302.1R practice and cross-referenced with our <a href="' . esc_url(home_url('/methodology/')) . '">calculation methodology</a>.',
    ],
];

$is_filtered_hub = isset($hub_config[$slab_route]);
$active_config = $is_filtered_hub ? $hub_config[$slab_route] : null;

// Pull every published post; filtered hubs narrow it to their own category
// subset below. New posts appear automatically on next page load — no
// manual hub-page editing required, on any of the three routes.
$all_posts = get_posts([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'numberposts'    => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
]);

$grouped = [];
foreach ($all_posts as $kb_post) {
    $cats = get_the_category($kb_post->ID);
    $cat_name = !empty($cats) ? $cats[0]->name : 'Concrete Guides';
    // BUG FIX (found on live re-check): this used to compare $cat_name
    // (the term's DISPLAY name, e.g. "Concrete Maintenance & Repair")
    // against a hardcoded plain-text array with in_array(..., true).
    // WordPress's term-display filtering doesn't consistently return the
    // exact same byte sequence for a name containing '&' across every
    // call site, so that strict string comparison silently failed for
    // both categories with an ampersand in the name ("Concrete
    // Maintenance & Repair", "Planning & Decisions") — the /how-to/ hub
    // was quietly dropping all 3 maintenance/repair articles even though
    // they displayed correctly everywhere else (e.g. /knowledgebase/,
    // which doesn't filter by name). Category SLUGS are always plain
    // ASCII and don't have this problem, so match on slug instead.
    $cat_slug = !empty($cats) ? $cats[0]->slug : 'concrete-guides';
    if ($is_filtered_hub && !in_array($cat_slug, $active_config['category_slugs'], true)) {
        continue;
    }
    if (!isset($grouped[$cat_name])) {
        $grouped[$cat_name] = [];
    }
    $grouped[$cat_name][] = $kb_post;
}
ksort($grouped);
$total_articles = array_sum(array_map('count', $grouped));
?>

<main id="primary" class="site-main">
  <div class="container">
    <div class="content-grid">
      <article class="article-content">
        <header class="page-header" style="margin-bottom: 2rem;">
          <h1 class="hero-title"><?php echo $is_filtered_hub ? esc_html($active_config['h1']) : 'Concrete Knowledgebase &amp; Articles'; ?></h1>
          <div class="trust-bar" style="margin: 1rem 0;">
            <div class="trust-item"><strong>Articles:</strong> <span><?php echo (int) $total_articles; ?> Published Guides</span></div>
            <div class="trust-item"><strong>Categories:</strong> <span><?php echo (int) count($grouped); ?></span></div>
            <div class="trust-item"><strong>Review:</strong> <span>Verified Estimating Team</span></div>
          </div>
        </header>

        <p>
          <?php if ($is_filtered_hub) : ?>
            <?php echo $active_config['intro']; // phpcs:ignore -- fixed, escaped internal HTML ?>
            See the complete, unfiltered library in our <a href="<?php echo esc_url(home_url('/knowledgebase/')); ?>">full Knowledgebase</a>.
          <?php else : ?>
            Every cost breakdown, formula explainer, and project-planning guide published on Slab Cost Calculator lives here, organized by topic. Each article is written to pair directly with our <a href="<?php echo esc_url(home_url('/calculators/')); ?>">interactive calculators</a> and our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">calculation methodology</a>, so the numbers you read here match the numbers the tools produce.
          <?php endif; ?>
        </p>

        <?php if (!empty($grouped)) : ?>

          <nav aria-label="Article categories" style="margin: 1.5rem 0 2.5rem 0; display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <?php foreach ($grouped as $cat_name => $cat_posts) :
                $anchor = sanitize_title($cat_name);
            ?>
              <a href="#kb-<?php echo esc_attr($anchor); ?>" style="font-size: 0.8125rem; font-weight: 700; padding: 0.4rem 0.85rem; border-radius: 999px; background: var(--primary-50); border: 1px solid var(--primary-200); color: var(--primary-700);">
                <?php echo esc_html($cat_name); ?> (<?php echo (int) count($cat_posts); ?>)
              </a>
            <?php endforeach; ?>
          </nav>

          <?php foreach ($grouped as $cat_name => $cat_posts) :
              $anchor = sanitize_title($cat_name);
          ?>
            <section id="kb-<?php echo esc_attr($anchor); ?>" style="margin: 0 0 3rem 0; scroll-margin-top: 90px;">
              <h2 style="border-bottom: 2px solid var(--slate-200); padding-bottom: 0.75rem;"><?php echo esc_html($cat_name); ?></h2>
              <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem;">
                <?php foreach ($cat_posts as $cat_post) : ?>
                  <article class="subcalc-card" style="margin-bottom: 0; display: flex; flex-direction: column;">
                    <div style="font-size: 0.6875rem; font-weight: 800; text-transform: uppercase; color: var(--primary-600); margin-bottom: 0.4rem;">
                      <?php echo esc_html($cat_name); ?>
                    </div>
                    <h3 style="font-size: 1.0625rem; font-weight: 800; line-height: 1.35; margin: 0 0 0.5rem 0;">
                      <a href="<?php echo esc_url(get_permalink($cat_post)); ?>"><?php echo esc_html(get_the_title($cat_post)); ?></a>
                    </h3>
                    <div style="font-size: 0.8125rem; color: var(--slate-600); line-height: 1.5; margin-bottom: 1rem; flex-grow: 1;">
                      <?php
                      $excerpt = has_excerpt($cat_post) ? get_the_excerpt($cat_post) : wp_trim_words(wp_strip_all_tags($cat_post->post_content), 20, '...');
                      echo esc_html(wp_trim_words($excerpt, 20, '...'));
                      ?>
                    </div>
                    <a href="<?php echo esc_url(get_permalink($cat_post)); ?>" style="font-weight: 700; font-size: 0.8125rem; margin-top: auto; display: inline-flex; align-items: center; gap: 0.3rem;">
                      Read Guide &rarr;
                    </a>
                  </article>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endforeach; ?>

        <?php else : ?>

          <div class="callout callout-info">
            <div class="callout-title">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
              Articles Publishing Soon
            </div>
            <p>
              New guides have not been published yet. In the meantime, jump straight into a <a href="<?php echo esc_url(home_url('/calculators/')); ?>">calculator</a> to get an instant estimate, or read our <a href="<?php echo esc_url(home_url('/methodology/')); ?>">calculation methodology</a> to see exactly how the numbers are produced.
            </p>
          </div>

        <?php endif; ?>

        <?php get_template_part('template-parts/author-bio'); ?>

      </article>

      <aside class="sidebar-col">
        <div class="sidebar-widget">
          <div class="widget-title">Explore the Site</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/calculators/')); ?>">All Calculators</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/cost-guides/')); ?>">Cost Guides Hub</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/how-to/')); ?>">How-To Hub</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/methodology/')); ?>">Calculation Methodology</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/')); ?>#concrete-slab-calculator">Slab Cost Calculator</a></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
