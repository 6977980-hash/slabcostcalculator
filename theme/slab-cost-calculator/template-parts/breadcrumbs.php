<?php
/**
 * BREADCRUMB NAVIGATION TEMPLATE PART
 *
 * Visible trail only. The BreadcrumbList structured data is printed once,
 * as JSON-LD, by inc/schema-generator.php from the same
 * slab_get_breadcrumb_trail() steps; microdata here made it two.
 * Separators come from CSS (.breadcrumb-item + .breadcrumb-item::before),
 * since an <ol> may only contain <li> elements.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

if (slab_is_home_page()) {
    return;
}

$slab_trail = slab_get_breadcrumb_trail();
if (empty($slab_trail)) {
    return;
}
$slab_last = count($slab_trail) - 1;
?>

<nav class="breadcrumb-nav" aria-label="Breadcrumbs">
  <div class="container">
    <ol class="breadcrumb-list">
      <li class="breadcrumb-item"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
      <?php foreach ($slab_trail as $slab_i => $slab_step) : ?>
        <?php if ($slab_i === $slab_last || empty($slab_step['item'])) : ?>
          <li class="breadcrumb-item breadcrumb-current"<?php echo $slab_i === $slab_last ? ' aria-current="page"' : ''; ?>><?php echo esc_html(wp_strip_all_tags($slab_step['name'])); ?></li>
        <?php else : ?>
          <li class="breadcrumb-item"><a href="<?php echo esc_url($slab_step['item']); ?>"><?php echo esc_html(wp_strip_all_tags($slab_step['name'])); ?></a></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ol>
  </div>
</nav>
