<?php
/**
 * Concrete steps tool (/concrete-steps-calculator/). JS: initStepsCalculator().
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$sd = Slab_Data::all();
$def = Slab_Tool_Pages::steps_default();
$v = $def['v'];
$bill = Slab_Tool_Pages::concrete_bill($v['net_yd']);
?>
<div id="subcalc-steps" class="subcalc-card" style="border: 2px solid var(--primary-600);">
  <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Concrete Steps</h2>
  <div class="input-grid-3">
    <div class="calc-form-group">
      <label for="st-count" class="calc-label">Number of steps (risers)</label>
      <input type="number" id="st-count" class="calc-control" value="<?php echo (int) $def['n']; ?>" min="1" max="20" step="1">
    </div>
    <div class="calc-form-group">
      <label for="st-width" class="calc-label">Width (ft)</label>
      <input type="number" id="st-width" class="calc-control" value="<?php echo esc_attr(Slab_Data::num($def['width_ft'])); ?>" min="1" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="st-landing" class="calc-label">Landing depth at top (ft)</label>
      <input type="number" id="st-landing" class="calc-control" value="<?php echo esc_attr(Slab_Data::num($def['landing_ft'])); ?>" min="0" step="0.5">
    </div>
  </div>
  <div class="input-grid-2">
    <div class="calc-form-group">
      <label for="st-rise" class="calc-label">Rise per step (in)</label>
      <input type="number" id="st-rise" class="calc-control" value="<?php echo esc_attr(Slab_Data::num($def['rise_in'])); ?>" min="1" step="0.25">
    </div>
    <div class="calc-form-group">
      <label for="st-tread" class="calc-label">Tread depth (in)</label>
      <input type="number" id="st-tread" class="calc-control" value="<?php echo esc_attr(Slab_Data::num($def['tread_in'])); ?>" min="1" step="0.5">
    </div>
  </div>

  <div class="quantities-grid" style="margin-top: 1.25rem;">
    <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
      <div class="quantity-card-label">Concrete (+<?php echo (int) $sd['concrete']['waste_pct']; ?>%)</div>
      <div id="st-out-yards" class="quantity-card-value" style="color: var(--primary-800);"><?php echo esc_html(number_format($v['order_yd'], 2)); ?> yd³</div>
      <div id="st-out-bags" class="quantity-card-note"><?php echo (int) $v['bags80']; ?> × 80-lb or <?php echo (int) $v['bags60']; ?> × 60-lb bags</div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">DIY concrete</div>
      <div id="st-out-diy" class="quantity-card-value"><?php echo esc_html(Slab_Data::money($bill[0], 0)); ?></div>
      <div id="st-out-diy-note" class="quantity-card-note">Cheapest: <?php echo esc_html($bill[1]); ?></div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Contractor price</div>
      <div id="st-out-pro" class="quantity-card-value"><?php echo esc_html(Slab_Data::money_range(Slab_Tool_Pages::steps_installed($def['n'], $def['width_ft']), 0, ' – ')); ?></div>
      <div class="quantity-card-note"><?php echo esc_html(Slab_Data::mr('steps.installed_per_step', 0, '–')); ?> per 4-ft step</div>
    </div>
  </div>
  <div class="quantities-grid" style="margin-top: 0.75rem;">
    <div class="quantity-card">
      <div class="quantity-card-label">Total rise × run</div>
      <div id="st-out-size" class="quantity-card-value"><?php echo esc_html(Slab_Data::num($v['total_rise_in']) . ' in × ' . Slab_Data::num($v['total_run_in']) . ' in'); ?></div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Forms</div>
      <div id="st-out-forms" class="quantity-card-value"><?php echo esc_html(Slab_Data::num($v['riser_lf']) . ' lin ft riser boards'); ?></div>
      <div id="st-out-ply" class="quantity-card-note"><?php echo (int) $v['plywood_sheets']; ?> sheet(s) of 4 × 8 plywood for the sides</div>
    </div>
  </div>
  <p id="st-out-code" class="calc-field-note" style="margin-top: 0.75rem;">Meets IRC limits: rise ≤ <?php echo esc_html(Slab_Data::num($sd['steps']['riser_max_in'])); ?> in, tread ≥ <?php echo (int) $sd['steps']['tread_min_in']; ?> in, width ≥ <?php echo (int) $sd['steps']['width_min_in']; ?> in.</p>
</div>
