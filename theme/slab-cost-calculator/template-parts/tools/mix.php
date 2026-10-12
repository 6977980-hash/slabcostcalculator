<?php
/**
 * Site-mix materials tool (/concrete-mix-calculator/). JS: initMixCalculator().
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$sd = Slab_Data::all();
$c = $sd['concrete'];
$m = Slab_Calculator_Pages::mix_result(10, 10, 4, '1:2:3', $c['waste_pct']);
?>
<div id="subcalc-mix" class="subcalc-card" style="border: 2px solid var(--primary-600);">
  <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Cement, Sand, Stone &amp; Water</h2>
  <div class="input-grid-3">
    <div class="calc-form-group">
      <label for="mix-length" class="calc-label">Length (ft)</label>
      <input type="number" id="mix-length" class="calc-control" value="10" min="0" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="mix-width" class="calc-label">Width (ft)</label>
      <input type="number" id="mix-width" class="calc-control" value="10" min="0" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="mix-thick" class="calc-label">Thickness (inches)</label>
      <input type="number" id="mix-thick" class="calc-control" value="4" min="0" step="0.5">
    </div>
  </div>
  <div class="input-grid-2">
    <div class="calc-form-group">
      <label for="mix-ratio" class="calc-label">Mix Ratio (cement : sand : stone)</label>
      <select id="mix-ratio" class="calc-control">
        <?php foreach ($c['mix_ratios'] as $ratio => $use) : ?>
          <option value="<?php echo esc_attr($ratio); ?>"<?php selected($ratio, '1:2:3'); ?>><?php echo esc_html($ratio . ' - ' . $use); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="calc-form-group">
      <label for="mix-waste" class="calc-label">Waste Allowance (%)</label>
      <input type="number" id="mix-waste" class="calc-control" value="<?php echo (int) $c['waste_pct']; ?>" min="0" step="1">
    </div>
  </div>
  <div class="quantities-grid" style="margin-top: 1.25rem;">
    <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
      <div class="quantity-card-label">Portland Cement</div>
      <div id="mix-out-cement" class="quantity-card-value" style="color: var(--primary-800);"><?php echo (int) $m['cement_bags']; ?> bags</div>
      <div class="quantity-card-note"><?php echo (int) $c['cement_bag_lb']; ?>-lb bags</div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Sand</div>
      <div id="mix-out-sand" class="quantity-card-value"><?php echo esc_html(number_format($m['sand_tons'], 2)); ?> tons</div>
      <div id="mix-out-sand-yd" class="quantity-card-note"><?php echo esc_html(number_format($m['sand_yd'], 2)); ?> yd³</div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Gravel / Stone</div>
      <div id="mix-out-stone" class="quantity-card-value"><?php echo esc_html(number_format($m['stone_tons'], 2)); ?> tons</div>
      <div id="mix-out-stone-yd" class="quantity-card-note"><?php echo esc_html(number_format($m['stone_yd'], 2)); ?> yd³</div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Water</div>
      <div id="mix-out-water" class="quantity-card-value"><?php echo (int) round($m['water_gal']); ?> gallons</div>
      <div class="quantity-card-note">At a <?php echo esc_html(Slab_Data::num($c['water_cement_ratio'], 2)); ?> water-cement ratio</div>
    </div>
  </div>
</div>
