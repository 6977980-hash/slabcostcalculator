<?php
/**
 * Ready-mix order and price tool (/ready-mix-concrete-calculator/). JS: initReadyMixCalculator().
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$sd = Slab_Data::all();
$r = Slab_Calculator_Pages::readymix_result(20, 20, 4, 3000);
?>
<div id="subcalc-readymix" class="subcalc-card" style="border: 2px solid var(--primary-600);">
  <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Ready-Mix Order &amp; Price</h2>
  <div class="input-grid-3">
    <div class="calc-form-group">
      <label for="rm-length" class="calc-label">Length (ft)</label>
      <input type="number" id="rm-length" class="calc-control" value="20" min="0" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="rm-width" class="calc-label">Width (ft)</label>
      <input type="number" id="rm-width" class="calc-control" value="20" min="0" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="rm-thick" class="calc-label">Thickness (inches)</label>
      <input type="number" id="rm-thick" class="calc-control" value="4" min="0" step="0.5">
    </div>
  </div>
  <div class="input-grid-2">
    <div class="calc-form-group">
      <label for="rm-psi" class="calc-label">Strength</label>
      <select id="rm-psi" class="calc-control">
        <?php foreach ($sd['concrete']['psi_grades'] as $psi => $g) : ?>
          <option value="<?php echo (int) $psi; ?>"<?php selected($psi, 3000); ?>><?php echo esc_html(number_format($psi) . ' PSI - ' . $g['use']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="calc-form-group">
      <label for="rm-waste" class="calc-label">Waste Allowance (%)</label>
      <input type="number" id="rm-waste" class="calc-control" value="<?php echo (int) $sd['concrete']['waste_pct']; ?>" min="0" step="1">
    </div>
  </div>
  <div class="quantities-grid" style="margin-top: 1.25rem;">
    <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
      <div class="quantity-card-label">Estimated Concrete Bill</div>
      <div id="rm-out-total" class="quantity-card-value" style="color: var(--primary-800);"><?php echo esc_html(Slab_Data::money_range($r['total'], 0, ' – ')); ?></div>
      <div id="rm-out-total-note" class="quantity-card-note">Concrete + <?php echo esc_html(Slab_Data::money($sd['delivery']['fee_default'])); ?> delivery<?php echo $r['short'] ? ' + short-load fee' : ''; ?></div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Order Volume</div>
      <div id="rm-out-yards" class="quantity-card-value"><?php echo esc_html(number_format($r['order_yd'], 2)); ?> yd³</div>
      <div id="rm-out-trucks" class="quantity-card-note"><?php echo (int) $r['trucks']; ?> truck<?php echo $r['trucks'] > 1 ? 's' : ''; ?></div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Price per Yard</div>
      <div id="rm-out-price" class="quantity-card-value"><?php echo esc_html(Slab_Data::money_range($r['price'], 0, ' – ')); ?></div>
      <div class="quantity-card-note">Before delivery and fees</div>
    </div>
  </div>
  <p id="rm-out-note" class="calc-field-note" style="margin-top: 0.75rem;"><?php if ($r['short']) : ?>Below the usual <?php echo esc_html(Slab_Data::range($sd['delivery']['common_min_yd'], '–')); ?> yd³ minimum: a short-load fee of <?php echo esc_html(Slab_Data::money_range($sd['delivery']['short_load_flat'], 0, '–')); ?> is included.<?php endif; ?></p>
</div>
