<?php
/**
 * Fence post concrete tool (/fence-post-concrete-calculator/). JS: initFencePostCalculator().
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$sd = Slab_Data::all();
$p = $sd['posts'];
$c = $sd['concrete'];
$def = Slab_Tool_Pages::post_default();
$r = $def['ten'];
$per = $def['one']['per_hole_80'];
?>
<div id="subcalc-fenceposts" class="subcalc-card" style="border: 2px solid var(--primary-600);">
  <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Fence Post Concrete</h2>
  <div class="input-grid-3">
    <div class="calc-form-group">
      <label for="fp-post" class="calc-label">Post</label>
      <select id="fp-post" class="calc-control">
        <?php foreach ($p['sizes'] as $key => $size) : ?>
          <option value="<?php echo esc_attr($key); ?>"<?php selected($key, $def['post']); ?>><?php echo esc_html($size[0]); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="calc-form-group">
      <label for="fp-count" class="calc-label">Number of posts</label>
      <input type="number" id="fp-count" class="calc-control" value="<?php echo (int) $def['count']; ?>" min="1" step="1">
    </div>
    <div class="calc-form-group">
      <label for="fp-height" class="calc-label">Fence height above ground (ft)</label>
      <input type="number" id="fp-height" class="calc-control" value="<?php echo (int) $def['height_ft']; ?>" min="1" step="0.5">
    </div>
  </div>
  <div class="input-grid-3">
    <div class="calc-form-group">
      <label for="fp-dia" class="calc-label">Hole diameter (in)</label>
      <input type="number" id="fp-dia" class="calc-control" value="<?php echo (int) $def['hole_in']; ?>" min="2" step="1">
    </div>
    <div class="calc-form-group">
      <label for="fp-depth" class="calc-label">Hole depth incl. <?php echo (int) $p['gravel_in']; ?> in gravel (in)</label>
      <input type="number" id="fp-depth" class="calc-control" value="<?php echo (int) $def['depth_in']; ?>" min="6" step="1">
    </div>
    <div class="calc-form-group">
      <label for="fp-mix" class="calc-label">Mix</label>
      <select id="fp-mix" class="calc-control">
        <option value="80" selected>80-lb regular mix (<?php echo esc_html(Slab_Data::money($c['bag_price_default'], 2)); ?>)</option>
        <option value="60">60-lb regular mix (<?php echo esc_html(Slab_Data::money($c['bag_price_sizes']['60'], 2)); ?>)</option>
        <option value="fast"><?php echo (int) $p['fast_set_lb']; ?>-lb fast-setting mix (<?php echo esc_html(Slab_Data::money($p['fast_set_price'], 2)); ?>)</option>
      </select>
    </div>
  </div>
  <p class="calc-field-note">Changing the post or fence height fills in a suggested hole: about <?php echo (int) $p['hole_factor']; ?> × the post width, and 1/3 of the height buried plus <?php echo (int) $p['gravel_in']; ?> in of gravel. Edit them for your soil or frost depth.</p>

  <div class="quantities-grid" style="margin-top: 1.25rem;">
    <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
      <div class="quantity-card-label">Bags for all posts</div>
      <div id="fp-out-total" class="quantity-card-value" style="color: var(--primary-800);"><?php echo (int) $r['bags80']; ?> bags</div>
      <div id="fp-out-cost" class="quantity-card-note"><?php echo esc_html(Slab_Data::money($r['bags80'] * $c['bag_price_default'], 0)); ?> in mix</div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Bags per post</div>
      <div id="fp-out-per" class="quantity-card-value"><?php echo esc_html(number_format($per, 1)); ?></div>
      <div id="fp-out-per-note" class="quantity-card-note"><?php echo esc_html(number_format($def['one']['per_hole_ft3'], 2)); ?> ft³ per hole (+<?php echo (int) $c['waste_pct']; ?>%)</div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Gravel under posts</div>
      <div id="fp-out-gravel" class="quantity-card-value"><?php echo esc_html(number_format($r['gravel_ft3'], 1)); ?> ft³</div>
      <div class="quantity-card-note"><?php echo (int) $p['gravel_in']; ?> in in each hole</div>
    </div>
  </div>
  <p id="fp-out-note" class="calc-field-note" style="margin-top: 0.75rem;"></p>
</div>
