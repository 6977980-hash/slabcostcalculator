<?php
/**
 * 404 ERROR PAGE TEMPLATE
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main">
  <div class="container container-narrow" style="text-align: center; padding: 4rem 1rem;">
    <div style="font-size: 5rem; font-weight: 900; color: var(--primary-600); line-height: 1;">404</div>
    <h1 class="hero-title" style="margin-top: 1rem;">Page or Calculator Not Found</h1>
    <p class="hero-subtitle" style="margin: 1rem auto 2rem auto;">
      The concrete calculating resource you are looking for may have moved or had its URL updated. Use our primary calculator or browse popular tools below.
    </p>

    <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="header-cta-btn" style="padding: 0.75rem 1.5rem;">Go to Primary Slab Calculator</a>
      <a href="<?php echo esc_url(home_url('/calculators/')); ?>" class="calc-action-btn" style="padding: 0.75rem 1.5rem; text-decoration: none;">Browse All Calculators</a>
    </div>
  </div>
</main>

<?php
get_footer();
