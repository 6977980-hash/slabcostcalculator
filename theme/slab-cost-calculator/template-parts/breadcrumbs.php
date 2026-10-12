<?php
/**
 * BREADCRUMB NAVIGATION TEMPLATE PART
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

if (is_front_page()) {
    return;
}
?>

<nav class="breadcrumb-nav" aria-label="Breadcrumbs">
  <div class="container">
    <ol class="breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">
      <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
        <a href="<?php echo esc_url(home_url('/')); ?>" itemprop="item"><span itemprop="name">Home</span></a>
        <meta itemprop="position" content="1" />
      </li>
      <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>

      <?php
      $position = 2;

      if (is_page()) {
          global $post;
          $ancestors = get_post_ancestors($post->ID);
          if (!empty($ancestors)) {
              $ancestors = array_reverse($ancestors);
              foreach ($ancestors as $ancestor_id) {
                  echo '<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
                  echo '<a href="' . esc_url(get_permalink($ancestor_id)) . '" itemprop="item"><span itemprop="name">' . esc_html(get_the_title($ancestor_id)) . '</span></a>';
                  echo '<meta itemprop="position" content="' . $position++ . '" />';
                  echo '</li>';
                  echo '<span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>';
              }
          }
          echo '<li class="breadcrumb-item breadcrumb-current" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
          echo '<span itemprop="name">' . esc_html(get_the_title($post->ID)) . '</span>';
          echo '<meta itemprop="position" content="' . $position . '" />';
          echo '</li>';
      } elseif (is_single()) {
          $categories = get_the_category();
          if (!empty($categories)) {
              echo '<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
              echo '<a href="' . esc_url(get_category_link($categories[0]->term_id)) . '" itemprop="item"><span itemprop="name">' . esc_html($categories[0]->name) . '</span></a>';
              echo '<meta itemprop="position" content="' . $position++ . '" />';
              echo '</li>';
              echo '<span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>';
          }
          echo '<li class="breadcrumb-item breadcrumb-current" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
          echo '<span itemprop="name">' . esc_html(get_the_title()) . '</span>';
          echo '<meta itemprop="position" content="' . $position . '" />';
          echo '</li>';
      }
      ?>
    </ol>
  </div>
</nav>
