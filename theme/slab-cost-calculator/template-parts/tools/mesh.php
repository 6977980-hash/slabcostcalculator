<?php
/**
 * Welded wire mesh tool (/concrete-wire-mesh-calculator/). JS: initMeshCalculator().
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$r = Slab_Data::get('reinforcement');
$m = Slab_Calculator_Pages::mesh_result(20, 20, 'sheet');
?>
<div id="subcalc-mesh" class="subcalc-card" style="border: 2px solid var(--primary-600);">
  <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Welded Wire Mesh Sheets &amp; Rolls</h2>
  <div class="input-grid-3">
    <div class="calc-form-group">
      <label for="mesh-length" class="calc-label">Slab Length (ft)</label>
      <input type="number" id="mesh-length" class="calc-control" value="20" min="0" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="mesh-width" class="calc-label">Slab Width (ft)</label>
      <input type="number" id="mesh-width" class="calc-control" value="20" min="0" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="mesh-type" class="calc-label">Mesh Format</label>
      <select id="mesh-type" class="calc-control">
        <option value="sheet" selected><?php echo esc_html($r['mesh_sheet_ft'][0] . ' × ' . $r['mesh_sheet_ft'][1] . ' ft sheets'); ?></option>
        <option value="roll"><?php echo esc_html($r['mesh_roll_ft'][0] . ' × ' . $r['mesh_roll_ft'][1] . ' ft rolls'); ?></option>
      </select>
    </div>
  </div>
  <div class="quantities-grid" style="margin-top: 1.25rem;">
    <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
      <div class="quantity-card-label">Mesh Needed</div>
      <div id="mesh-out-count" class="quantity-card-value" style="color: var(--primary-800);"><?php echo (int) $m['count']; ?> sheets</div>
      <div class="quantity-card-note">With <?php echo (int) $r['mesh_overlap_in']; ?> in overlaps at the seams</div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Support Chairs</div>
      <div id="mesh-out-chairs" class="quantity-card-value"><?php echo (int) $m['chairs']; ?> chairs</div>
      <div class="quantity-card-note">About every <?php echo esc_html(Slab_Data::num($r['chair_grid_ft'][1])); ?> ft</div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Mesh Cost</div>
      <div id="mesh-out-cost" class="quantity-card-value"><?php echo esc_html(Slab_Data::money_range($m['cost'], 0, ' – ')); ?></div>
    </div>
  </div>
</div>
