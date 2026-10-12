<?php
/**
 * Cubic yard order tool (/concrete-yard-calculator/). JS: supporting-calculators.js initYardCalculator().
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$sd = Slab_Data::all();
$waste = $sd['concrete']['waste_pct'];
$e = Slab_Data::estimate(20, 20, 4);
$step = $sd['delivery']['order_step_yd'][0];
$order = ceil($e['order_yd'] / $step - 1e-9) * $step;
?>
<div id="subcalc-yards" class="subcalc-card" style="border: 2px solid var(--primary-600);">
  <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Cubic Yards to Order</h2>
  <div class="input-grid-3">
    <div class="calc-form-group">
      <label for="yd-length" class="calc-label">Length (ft)</label>
      <input type="number" id="yd-length" class="calc-control" value="20" min="0" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="yd-width" class="calc-label">Width (ft)</label>
      <input type="number" id="yd-width" class="calc-control" value="20" min="0" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="yd-thick" class="calc-label">Thickness (inches)</label>
      <input type="number" id="yd-thick" class="calc-control" value="4" min="0" step="0.5">
    </div>
  </div>
  <div class="calc-form-group">
    <label for="yd-waste" class="calc-label">Waste Allowance</label>
    <select id="yd-waste" class="calc-control">
      <?php foreach ($sd['concrete']['waste_levels'] as $pct => $text) : ?>
        <option value="<?php echo (int) $pct; ?>"<?php selected($pct, $waste); ?>><?php echo esc_html($pct . '% - ' . $text); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="quantities-grid" style="margin-top: 1.25rem;">
    <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
      <div class="quantity-card-label">Order This Much</div>
      <div id="yd-out-order" class="quantity-card-value" style="color: var(--primary-800);"><?php echo esc_html(number_format($order, 2)); ?> yd³</div>
      <div class="quantity-card-note">Rounded up to the next <?php echo esc_html(Slab_Data::num($step)); ?> yd</div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">With Waste</div>
      <div id="yd-out-waste" class="quantity-card-value"><?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³</div>
      <div id="yd-out-net" class="quantity-card-note">Net: <?php echo esc_html(number_format($e['net_yd'], 2)); ?> yd³</div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Truckloads</div>
      <div id="yd-out-trucks" class="quantity-card-value"><?php echo (int) ceil($order / $sd['delivery']['truck_capacity_yd'][0] - 1e-9); ?></div>
      <div class="quantity-card-note">At about <?php echo esc_html(Slab_Data::range($sd['delivery']['truck_capacity_yd'], '–')); ?> yd³ per truck</div>
    </div>
  </div>
  <p id="yd-out-note" class="calc-field-note" style="margin-top: 0.75rem;"></p>
</div>
