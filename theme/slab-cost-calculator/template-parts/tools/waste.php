<?php
/**
 * Waste allowance tool (/concrete-waste-calculator/). JS: initWasteCalculator().
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$sd = Slab_Data::all();
$c = $sd['concrete'];
$e = Slab_Data::estimate(20, 20, 4);
$extra = $e['order_yd'] - $e['net_yd'];
?>
<div id="subcalc-waste" class="subcalc-card" style="border: 2px solid var(--primary-600);">
  <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Waste Allowance &amp; Its Cost</h2>
  <div class="input-grid-3">
    <div class="calc-form-group">
      <label for="wst-length" class="calc-label">Length (ft)</label>
      <input type="number" id="wst-length" class="calc-control" value="20" min="0" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="wst-width" class="calc-label">Width (ft)</label>
      <input type="number" id="wst-width" class="calc-control" value="20" min="0" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="wst-thick" class="calc-label">Thickness (inches)</label>
      <input type="number" id="wst-thick" class="calc-control" value="4" min="0" step="0.5">
    </div>
  </div>
  <div class="input-grid-2">
    <div class="calc-form-group">
      <label for="wst-level" class="calc-label">Site Condition</label>
      <select id="wst-level" class="calc-control">
        <?php foreach ($c['waste_levels'] as $pct => $text) : ?>
          <option value="<?php echo (int) $pct; ?>"<?php selected($pct, $c['waste_pct']); ?>><?php echo esc_html($pct . '% - ' . $text); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="calc-form-group">
      <label for="wst-price" class="calc-label">Ready-Mix Price ($/yd³)</label>
      <input type="number" id="wst-price" class="calc-control" value="<?php echo esc_attr(number_format($c['ready_mix_default'], 2, '.', '')); ?>" min="0" step="1">
    </div>
  </div>
  <div class="quantities-grid" style="margin-top: 1.25rem;">
    <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
      <div class="quantity-card-label">Order Volume</div>
      <div id="wst-out-order" class="quantity-card-value" style="color: var(--primary-800);"><?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³</div>
      <div id="wst-out-net" class="quantity-card-note">Net: <?php echo esc_html(number_format($e['net_yd'], 2)); ?> yd³</div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Extra Concrete</div>
      <div id="wst-out-extra" class="quantity-card-value"><?php echo esc_html(number_format($extra, 2)); ?> yd³</div>
      <div id="wst-out-extra-cost" class="quantity-card-note">Costs about <?php echo esc_html(Slab_Data::money($extra * $c['ready_mix_default'], 0)); ?></div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">If You Run Short</div>
      <div id="wst-out-short" class="quantity-card-value"><?php echo esc_html(Slab_Data::money_range([$sd['delivery']['fee_default'] + $sd['delivery']['short_load_flat'][0], $sd['delivery']['fee_default'] + $sd['delivery']['short_load_flat'][1]], 0, ' – ')); ?></div>
      <div class="quantity-card-note">Second delivery + short-load fee</div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Or in 80-lb Bags</div>
      <div id="wst-out-bags" class="quantity-card-value"><?php echo (int) $e['bags80']; ?> bags</div>
    </div>
  </div>
</div>
