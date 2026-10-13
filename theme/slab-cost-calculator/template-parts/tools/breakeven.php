<?php
/**
 * Bags vs. ready-mix tool (/bags-vs-ready-mix-calculator/). JS: initBreakEvenCalculator().
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$sd = Slab_Data::all();
$c = $sd['concrete'];
$dl = $sd['delivery'];
$e = Slab_Data::estimate(10, 10, 4);
$r = Slab_Data::bags_vs_truck($e['net_yd'], true);
$be = Slab_Data::break_even_yd(true);
?>
<div id="subcalc-breakeven" class="subcalc-card" style="border: 2px solid var(--primary-600);">
  <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Bags vs. Ready-Mix</h2>
  <div class="input-grid-3">
    <div class="calc-form-group">
      <label for="be-length" class="calc-label">Length (ft)</label>
      <input type="number" id="be-length" class="calc-control" value="10" min="0" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="be-width" class="calc-label">Width (ft)</label>
      <input type="number" id="be-width" class="calc-control" value="10" min="0" step="0.5">
    </div>
    <div class="calc-form-group">
      <label for="be-thick" class="calc-label">Thickness (in)</label>
      <input type="number" id="be-thick" class="calc-control" value="4" min="0" step="0.5">
    </div>
  </div>
  <details class="bid-itemized" open>
    <summary>Your prices</summary>
    <div class="input-grid-3" style="margin-top: 0.75rem;">
      <div class="calc-form-group">
        <label for="be-bag" class="calc-label">Bag size</label>
        <select id="be-bag" class="calc-control">
          <option value="80" selected>80 lb</option>
          <option value="60">60 lb</option>
          <option value="50">50 lb</option>
          <option value="40">40 lb</option>
        </select>
      </div>
      <div class="calc-form-group">
        <label for="be-bag-price" class="calc-label">Price per bag ($)</label>
        <input type="number" id="be-bag-price" class="calc-control" value="<?php echo esc_attr(number_format($c['bag_price_default'], 2)); ?>" min="0" step="0.25">
      </div>
      <div class="calc-form-group">
        <label for="be-mixer" class="calc-label">Mixer rental ($/day, 0 = hand mix)</label>
        <input type="number" id="be-mixer" class="calc-control" value="<?php echo esc_attr(number_format(array_sum($c['mixer_rental_day']) / 2, 0, '.', '')); ?>" min="0" step="5">
      </div>
    </div>
    <div class="input-grid-2">
      <div class="calc-form-group">
        <label for="be-rm-price" class="calc-label">Ready-mix ($/yd³)</label>
        <input type="number" id="be-rm-price" class="calc-control" value="<?php echo esc_attr(number_format($c['ready_mix_default'], 0, '.', '')); ?>" min="0" step="5">
      </div>
      <div class="calc-form-group">
        <label for="be-fee" class="calc-label">Delivery fee ($)</label>
        <input type="number" id="be-fee" class="calc-control" value="<?php echo esc_attr(number_format($dl['fee_default'], 0, '.', '')); ?>" min="0" step="10">
      </div>
    </div>
    <div class="input-grid-2">
      <div class="calc-form-group">
        <label for="be-short" class="calc-label">Short-load fee ($)</label>
        <input type="number" id="be-short" class="calc-control" value="<?php echo esc_attr(number_format(array_sum($dl['short_load_flat']) / 2, 0, '.', '')); ?>" min="0" step="10">
      </div>
      <div class="calc-form-group">
        <label for="be-min" class="calc-label">Short load below (yd³)</label>
        <input type="number" id="be-min" class="calc-control" value="<?php echo esc_attr(Slab_Data::num($dl['common_min_yd'][0])); ?>" min="0" step="0.5">
      </div>
    </div>
  </details>

  <div class="quantities-grid" style="margin-top: 1.25rem;">
    <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
      <div class="quantity-card-label">Cheaper option</div>
      <div id="be-out-verdict" class="quantity-card-value" style="color: var(--primary-800);"><?php echo $r['cheaper'] === 'bags' ? 'Bags' : 'Ready-mix'; ?></div>
      <div id="be-out-saving" class="quantity-card-note">Saves <?php echo esc_html(Slab_Data::money(abs($r['truck_cost'] - $r['bag_cost']), 0)); ?></div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Bags</div>
      <div id="be-out-bags" class="quantity-card-value"><?php echo esc_html(Slab_Data::money($r['bag_cost'], 0)); ?></div>
      <div id="be-out-bags-note" class="quantity-card-note"><?php echo (int) $r['bags']; ?> bags, about <?php echo esc_html(Slab_Data::num(round($r['mix_hours'], 1))); ?> h of mixing</div>
    </div>
    <div class="quantity-card">
      <div class="quantity-card-label">Ready-mix</div>
      <div id="be-out-truck" class="quantity-card-value"><?php echo esc_html(Slab_Data::money($r['truck_cost'], 0)); ?></div>
      <div id="be-out-truck-note" class="quantity-card-note"><?php echo esc_html(Slab_Data::num($r['order_yd'])); ?> yd³<?php echo $r['short'] ? ' incl. short-load fee' : ''; ?></div>
    </div>
  </div>
  <p id="be-out-even" class="calc-field-note" style="margin-top: 0.75rem;">Break-even: ready-mix is cheaper above about <strong><?php echo esc_html(Slab_Data::num($be)); ?> yd³</strong> with these prices.</p>
  <div id="be-chart" class="be-chart" aria-hidden="true"></div>
</div>
