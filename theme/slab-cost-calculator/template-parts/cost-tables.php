<?php
/**
 * CONCRETE COST & SIZING MATRICES
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Sizing & Cost Matrix Table -->
<div class="table-responsive">
  <table class="data-table">
    <thead>
      <tr>
        <th>Slab Dimensions</th>
        <th>Surface Area</th>
        <th>Cubic Yards (4" Thick)</th>
        <th>Order Volume (+10% Waste)</th>
        <th>80-lb Bags (+10% Waste)</th>
        <th>60-lb Bags (+10% Waste)</th>
        <th>Materials Only (DIY)</th>
        <th>Installed Cost</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach (Slab_Data::size_table() as $row) : $e = $row['est']; ?>
      <tr>
        <td><strong><?php echo esc_html($row['label']); ?></strong></td>
        <td><?php echo esc_html(number_format($e['area'])); ?> sq. ft.</td>
        <td><?php echo esc_html(number_format($e['net_yd'], 2)); ?> yd³</td>
        <td><?php echo esc_html(number_format($e['order_yd'], 2)); ?> yd³</td>
        <td><?php echo esc_html(number_format($e['bags80'])); ?> bags</td>
        <td><?php echo esc_html(number_format($e['bags60'])); ?> bags</td>
        <td><?php echo esc_html(Slab_Data::money_range($row['diy'], 0)); ?></td>
        <td><?php echo esc_html(Slab_Data::money_range($row['installed'], 0)); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="table-caption">
    *Note: All volumes are for a 4-inch slab with <?php echo esc_html(Slab_Data::get('concrete.waste_pct')); ?>% waste. Bag counts use 0.60 cu ft per 80-lb bag (45 bags per yd³) and 0.45 cu ft per 60-lb bag (60 bags per yd³), rounded up. Costs use the calculator's own line items and default rates. <?php echo esc_html(Slab_Data::cost_basis_note()); ?> Actual costs vary by locality and access; use the regional index in the calculator for your area.
  </div>
</div>
