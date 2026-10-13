<?php
/**
 * Compare 2-3 contractor quotes (Bid Checker v2), on /concrete-quote-checker/.
 * JS: initQuoteCompare() in supporting-calculators.js. Each quote is
 * benchmarked against its own scope with the Bid Check tab's rule, then
 * priced up to the most complete scope so the quotes compare like for like.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$scope = [
    'base' => 'Excavation & gravel base',
    'vapor' => 'Vapor barrier',
    'demo' => 'Old slab removal',
    'pump' => 'Concrete pump',
    'permit' => 'Permit',
];
?>
<div id="subcalc-quotecompare" class="subcalc-card" style="border: 2px solid var(--primary-600);">
  <h3 style="font-size: 1.15rem; margin-top: 0;">Compare Quotes Side by Side</h3>
  <div class="input-grid-3">
    <div class="calc-form-group">
      <label for="qc-length" class="calc-label">Length (ft)</label>
      <input type="number" id="qc-length" class="calc-control" value="20" min="0" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="qc-width" class="calc-label">Width (ft)</label>
      <input type="number" id="qc-width" class="calc-control" value="20" min="0" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="qc-thick" class="calc-label">Thickness (in)</label>
      <input type="number" id="qc-thick" class="calc-control" value="4" min="0" step="0.5">
    </div>
  </div>
  <p id="qc-region" class="calc-field-note">Prices: U.S. national average. Pick your state in the calculator above to adjust labor and concrete.</p>

  <div class="qc-grid" style="margin-top: 1rem;">
    <?php foreach ([1, 2, 3] as $i) : ?>
    <div class="qc-quote" data-quote="<?php echo (int) $i; ?>">
      <h3>Quote <?php echo (int) $i; ?><?php echo $i === 3 ? ' (optional)' : ''; ?></h3>
      <div class="calc-form-group">
        <label for="qc-name-<?php echo (int) $i; ?>" class="calc-label">Contractor</label>
        <input type="text" id="qc-name-<?php echo (int) $i; ?>" class="calc-control qc-name" placeholder="Contractor <?php echo esc_attr(chr(64 + $i)); ?>">
      </div>
      <div class="calc-form-group">
        <label for="qc-total-<?php echo (int) $i; ?>" class="calc-label">Total quote ($)</label>
        <input type="number" id="qc-total-<?php echo (int) $i; ?>" class="calc-control qc-total" min="0" step="50" placeholder="e.g. <?php echo esc_attr(3000 + 600 * $i); ?>">
      </div>
      <div class="calc-form-group">
        <label for="qc-reinf-<?php echo (int) $i; ?>" class="calc-label">Reinforcement</label>
        <select id="qc-reinf-<?php echo (int) $i; ?>" class="calc-control qc-reinf">
          <option value="rebar" selected>#4 rebar</option>
          <option value="mesh">Wire mesh</option>
          <option value="none">None / not stated</option>
        </select>
      </div>
      <div class="calc-form-group">
        <label for="qc-finish-<?php echo (int) $i; ?>" class="calc-label">Finish</label>
        <select id="qc-finish-<?php echo (int) $i; ?>" class="calc-control qc-finish">
          <option value="broom" selected>Broom</option>
          <option value="trowel">Smooth trowel</option>
          <option value="exposed">Exposed aggregate</option>
          <option value="stamped">Stamped</option>
        </select>
      </div>
      <div class="qc-checks" role="group" aria-label="Quote <?php echo (int) $i; ?> includes">
        <?php foreach ($scope as $key => $label) : ?>
        <label><input type="checkbox" class="qc-inc" value="<?php echo esc_attr($key); ?>"<?php checked(in_array($key, ['base', 'vapor'], true)); ?>> <?php echo esc_html($label); ?></label>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div id="qc-result" class="qc-result" aria-live="polite" style="margin-top: 1rem;">
    <p class="calc-field-note">Enter at least two quote totals and tick what each one includes.</p>
  </div>
</div>
