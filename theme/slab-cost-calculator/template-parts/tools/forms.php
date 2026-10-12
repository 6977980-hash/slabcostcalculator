<?php
/**
 * Form lumber tool (/concrete-form-calculator/). JS: initFormCalculator().
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$s = Slab_Data::get('site');
$f = Slab_Calculator_Pages::form_result(20, 20, 4, 12, $s['stake_spacing_ft'][0]);
?>
<div id="subcalc-forms" class="subcalc-card" style="border: 2px solid var(--primary-600);">
  <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Form Boards, Stakes &amp; Screws</h2>
  <div class="input-grid-3">
    <div class="calc-form-group">
      <label for="form-length" class="calc-label">Slab Length (ft)</label>
      <input type="number" id="form-length" class="calc-control" value="20" min="0" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="form-width" class="calc-label">Slab Width (ft)</label>
      <input type="number" id="form-width" class="calc-control" value="20" min="0" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="form-thick" class="calc-label">Slab Thickness (inches)</label>
      <input type="number" id="form-thick" class="calc-control" value="4" min="0" step="0.5">
    </div>
  </div>
  <div class="input-grid-2">
    <div class="calc-form-group">
      <label for="form-board-len" class="calc-label">Board Length</label>
      <select id="form-board-len" class="calc-control">
        <?php foreach ($s['form_board_lengths_ft'] as $len) : ?>
          <option value="<?php echo (int) $len; ?>"<?php selected($len, 12); ?>><?php echo (int) $len; ?> ft</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="calc-form-group">
      <label for="form-stake" class="calc-label">Stake Spacing</label>
      <select id="form-stake" class="calc-control">
        <?php foreach ($s['stake_spacing_ft'] as $i => $sp) : ?>
          <option value="<?php echo esc_attr($sp); ?>"<?php selected($i, 0); ?>>Every <?php echo esc_html(Slab_Data::num($sp)); ?> ft</option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="quantities-grid" style="margin-top: 1.25rem;">
    <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
      <div class="quantity-card-label">Form Boards</div>
      <div id="form-out-boards" class="quantity-card-value" style="color: var(--primary-800);"><?php echo (int) $f['boards'] . ' × ' . esc_html($f['board']); ?></div>
      <div id="form-out-perimeter" class="quantity-card-note"><?php echo (int) $f['perimeter']; ?> linear ft of form</div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Stakes</div>
      <div id="form-out-stakes" class="quantity-card-value"><?php echo (int) $f['stakes']; ?> stakes</div>
      <div id="form-out-screws" class="quantity-card-note"><?php echo (int) $f['screws']; ?> screws</div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Forms Budget</div>
      <div id="form-out-cost" class="quantity-card-value"><?php echo esc_html(Slab_Data::money($f['cost'], 0)); ?></div>
      <div class="quantity-card-note">At <?php echo esc_html(Slab_Data::money($s['forms_default'], 2)); ?> per linear ft</div>
    </div>
  </div>
</div>
