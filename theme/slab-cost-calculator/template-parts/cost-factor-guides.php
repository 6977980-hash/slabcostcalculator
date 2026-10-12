<?php
/**
 * COST FACTOR GUIDES (front page)
 *
 * Five focused sections for searches the main guide did not answer:
 * concrete cost by PSI, line vs. boom pump cost, ready-mix short-load fees,
 * monolithic vs. floating slabs, and control joint spacing and saw-cut
 * timing. Worked examples use the site's standard figures ($150/yd³
 * ready-mix, $6.50 per 80-lb bag, 45 bags per cubic yard, 10% waste) so
 * they match the calculator and the sizing table above.
 *
 * Each section has an id so the sidebar and FAQ can link straight to it.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$sd = Slab_Data::all();
$rm = $sd['concrete']['ready_mix_default'];
$fee = $sd['delivery']['fee_default'];
$ref = Slab_Data::estimate(20, 20, 4);          // 20×20 ft, 4 in reference slab
$ref_yd = round($ref['order_yd'], 2);
$small = Slab_Data::estimate(10, 10, 4);        // 10×10 ft, 4 in
$garage = Slab_Data::estimate(24, 24, 4);       // 24×24 ft, 4 in
$step = $sd['concrete']['psi_step_price'];
$extra4000 = Slab_Data::round_pair(Slab_Data::psi_extra(4000, $ref_yd), 5);
$m = $sd['monolithic'];
$j = $sd['joints'];
$fmt_extra = function ($pair) {
    if ($pair[0] == 0 && $pair[1] == 0) {
        return 'Baseline';
    }
    $sign = $pair[0] < 0 ? '−' : '+';
    return $sign . Slab_Data::money(abs($pair[0] < 0 ? $pair[1] : $pair[0]), 0) . ' to ' . $sign . Slab_Data::money(abs($pair[0] < 0 ? $pair[0] : $pair[1]), 0);
};
?>

<h2 id="concrete-cost-by-psi">Concrete Slab Cost by PSI Strength (3000 vs. 3500 vs. 4000 vs. 4500 PSI)</h2>
<p>
  PSI is the compressive strength the mix reaches after 28 days of curing. Stronger mixes use more cement per yard, so each 500-PSI step usually adds about <strong><?php echo esc_html(Slab_Data::money_range($step, 0, ' to ')); ?> per cubic yard</strong> to the ready-mix price. Air entrainment, fiber, or accelerators are billed on top of that.
</p>
<div class="table-responsive">
  <table class="data-table">
    <thead>
      <tr>
        <th>Mix Strength</th>
        <th>Typical Price per yd³</th>
        <th>Typical Residential Use</th>
        <th>Extra Cost on a 20×20 ft, 4" Slab (<?php echo esc_html(number_format($ref_yd, 2)); ?> yd³)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ([2500, 3000, 3500, 4000, 4500] as $psi) : ?>
      <tr>
        <td><strong><?php echo esc_html(number_format($psi) . ($psi === 4500 ? '+' : '')); ?> PSI</strong></td>
        <td><?php echo esc_html(Slab_Data::money_range(Slab_Data::psi_price($psi), 0)); ?></td>
        <td><?php echo esc_html($sd['concrete']['psi_grades'][$psi]['use']); ?></td>
        <td><?php echo esc_html($fmt_extra(Slab_Data::round_pair(Slab_Data::psi_extra($psi, $ref_yd), 5))); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<p>
  The International Residential Code (Table R402.2) sets the minimums: 2,500 PSI where concrete is protected from weather, 3,000 PSI for exposed walls and foundations, and <strong>3,500 PSI, air-entrained, for garage floors, porches and steps in severe-weathering regions</strong>. On the 20×20 ft slab above, moving from 3,000 to 4,000 PSI adds roughly <?php echo esc_html(Slab_Data::money_range($extra4000, 0, ' to ')); ?>, which is small next to the cost of replacing a scaling or cracked driveway. Thickness, base compaction and joint spacing affect durability far more than going beyond 4,000 PSI on a home slab.
</p>

<h2 id="concrete-pump-cost">Concrete Pumping Cost: Line Pump vs. Boom Pump</h2>
<p>
  A ready-mix truck's chute reaches only about <strong><?php echo esc_html(Slab_Data::r('site.chute_reach_ft')); ?> feet</strong> past where the truck can park. When the forms sit behind a house, a fence or soft lawn, you either wheelbarrow the concrete or pay for a pump. Most residential pours land in the <strong><?php echo esc_html(Slab_Data::mr('pump.typical', 0, ' to ')); ?></strong> range, and the type of pump decides where you fall.
</p>
<div class="table-responsive">
  <table class="data-table">
    <thead>
      <tr>
        <th>Factor</th>
        <th>Line (Trailer) Pump</th>
        <th>Boom Pump Truck</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Typical Rate</strong></td>
        <td><?php echo esc_html(Slab_Data::mr('pump.line_rate')); ?> per hour, <?php echo esc_html(Slab_Data::r('pump.line_min_h', '–')); ?> hour minimum</td>
        <td><?php echo esc_html(Slab_Data::mr('pump.boom_rate')); ?> per hour, <?php echo esc_html(Slab_Data::r('pump.boom_min_h', '–')); ?> hour minimum</td>
      </tr>
      <tr>
        <td><strong>Typical Residential Pour</strong></td>
        <td><?php echo esc_html(Slab_Data::mr('pump.line_job')); ?></td>
        <td><?php echo esc_html(Slab_Data::mr('pump.boom_job')); ?>+</td>
      </tr>
      <tr>
        <td><strong>Reach</strong></td>
        <td>Hose laid on the ground, commonly <?php echo esc_html(Slab_Data::r('pump.line_reach_ft', '–')); ?> ft from the truck</td>
        <td>Boom arm reaches roughly <?php echo esc_html(Slab_Data::r('pump.boom_reach_ft', '–')); ?> ft over roofs and fences</td>
      </tr>
      <tr>
        <td><strong>Output</strong></td>
        <td>Slower; fine for patios, backyard slabs and footings</td>
        <td>Fast; suits large garage slabs, foundations and second-floor pours</td>
      </tr>
      <tr>
        <td><strong>Mix Requirement</strong></td>
        <td>Often needs a pump mix with 3/8" pea gravel (about <?php echo esc_html(Slab_Data::mr('pump.pump_mix_extra', 0, '–')); ?> more per yd³)</td>
        <td>Usually pumps standard 3/4" aggregate mixes</td>
      </tr>
    </tbody>
  </table>
</div>
<p>
  Because of the hourly minimum, a pump costs about the same for 3 yards as for 10. On a small slab, compare the pump quote with two or three helpers and wheelbarrows: a 10×10 ft slab (<?php echo esc_html(number_format($small['order_yd'], 2)); ?> yd³) is only about a dozen wheelbarrow trips, while a 24×24 ft garage slab is closer to 70. Add the pump to your estimate with the <em>Concrete Pump Truck</em> option in the calculator above.
</p>

<h2 id="ready-mix-short-load-fee">Ready-Mix Short Load Fee Breakdown</h2>
<p>
  A transit mixer carries <?php echo esc_html(Slab_Data::r('delivery.truck_capacity_yd')); ?> cubic yards. When you order less than the plant's minimum, the supplier adds a <strong>short-load fee</strong> to cover the driver and truck time. Minimums and fees vary by plant, so always ask for both when you call for a price.
</p>
<ul>
  <li><strong>Flat fee:</strong> commonly <strong><?php echo esc_html(Slab_Data::mr('delivery.short_load_flat', 0, ' to ')); ?> per load</strong> on orders below the minimum.</li>
  <li><strong>Per-yard fee:</strong> some plants charge <strong><?php echo esc_html(Slab_Data::mr('delivery.short_load_per_yd', 0, ' to ')); ?> for each yard</strong> you are short of the minimum.</li>
  <li><strong>Where the minimum sits:</strong> most suppliers set it between <?php echo esc_html(Slab_Data::r('delivery.min_load_yd', ' and ')); ?> yd³, and many residential suppliers use <?php echo esc_html(Slab_Data::r('delivery.common_min_yd')); ?> yd³.</li>
  <li><strong>Other add-ons:</strong> fuel or environmental surcharges (<?php echo esc_html(Slab_Data::mr('delivery.surcharge', 0, '–')); ?>) and overtime if the truck waits past the free unloading time, usually <?php echo esc_html(Slab_Data::r('delivery.unload_min_per_yd', '–')); ?> minutes per yard.</li>
</ul>
<p>
  <?php
  $order_yd = ceil($small['order_yd'] * 2 - 1e-9) / 2; // plants sell in half-yard steps
  $rm_total = $order_yd * $rm + $fee;
  ?>
  <strong>Worked example (10×10 ft, 4" slab):</strong> the slab needs <?php echo esc_html(number_format($small['order_yd'], 2)); ?> yd³ with <?php echo esc_html($sd['concrete']['waste_pct']); ?>% waste. Plants sell in quarter- or half-yard steps, so you order <?php echo esc_html(Slab_Data::num($order_yd)); ?> yd³ at <?php echo esc_html(Slab_Data::money($rm)); ?> = <?php echo esc_html(Slab_Data::money($order_yd * $rm, 0)); ?>, plus a typical <?php echo esc_html(Slab_Data::money($fee)); ?> short-load fee, for <strong>about <?php echo esc_html(Slab_Data::money($rm_total, 0)); ?> delivered</strong>. The same slab in bags is <?php echo esc_html($small['bags80']); ?> × 80-lb bags at <?php echo esc_html(Slab_Data::money($sd['concrete']['bag_price_default'], 2)); ?> = <strong><?php echo esc_html(Slab_Data::money($small['bagged'], 0)); ?></strong>, plus roughly <?php echo esc_html(number_format($small['bags80'] * 80 / 2000, 1)); ?> tons of hand mixing. Below about <?php echo esc_html(Slab_Data::num($sd['delivery']['bags_win_below_yd'])); ?> cubic yard bags usually win; above <?php echo esc_html(Slab_Data::num($sd['delivery']['ready_mix_wins_above_yd'])); ?> yards ready-mix usually wins even with the fee. A short-load fee is also a reason to combine pours, such as a patio and a walkway on the same day.
</p>

<h2 id="monolithic-vs-floating-slab">Monolithic Turn-Down Slab vs. Floating Slab Foundation Cost</h2>
<p>
  A <strong>floating slab</strong> is a flat slab of uniform thickness that rests on compacted gravel. A <strong>monolithic (turn-down) slab</strong> is poured in one piece with a thickened edge that acts as its footing, usually <strong><?php echo esc_html($m['edge_width_in']); ?>" wide and <?php echo esc_html($m['edge_depth_in'][0]); ?>" to <?php echo esc_html($m['edge_depth_in'][1]); ?>" deep</strong>, with two continuous #4 bars in the edge. Garages and larger sheds often need the turn-down edge to pass inspection.
</p>
<div class="table-responsive">
  <table class="data-table">
    <thead>
      <tr>
        <th>24×24 ft Garage, 4" Slab</th>
        <th>Floating Slab</th>
        <th>Monolithic, 12" Edge</th>
        <th>Monolithic, 18" Edge</th>
      </tr>
    </thead>
    <?php
    $float_yd = $garage['order_yd'];
    $e12 = $float_yd + Slab_Data::edge_yd(24, 24, 4, 12);
    $e18 = $float_yd + Slab_Data::edge_yd(24, 24, 4, 18);
    $add12 = Slab_Data::round_pair([($e12 - $float_yd) * $rm + $m['edge_rebar'][0] + $m['trenching']['12'][0], ($e12 - $float_yd) * $rm + $m['edge_rebar'][1] + $m['trenching']['12'][1]], 5);
    $add18 = Slab_Data::round_pair([($e18 - $float_yd) * $rm + $m['edge_rebar'][0] + $m['trenching']['18'][0], ($e18 - $float_yd) * $rm + $m['edge_rebar'][1] + $m['trenching']['18'][1]], 5);
    ?>
    <tbody>
      <tr>
        <td><strong>Concrete (with <?php echo esc_html($sd['concrete']['waste_pct']); ?>% waste)</strong></td>
        <td><?php echo esc_html(number_format($float_yd, 2)); ?> yd³</td>
        <td><?php echo esc_html(number_format($e12, 2)); ?> yd³</td>
        <td><?php echo esc_html(number_format($e18, 2)); ?> yd³</td>
      </tr>
      <tr>
        <td><strong>Concrete Cost at <?php echo esc_html(Slab_Data::money($rm)); ?>/yd³</strong></td>
        <td><?php echo esc_html(Slab_Data::money($float_yd * $rm, 0)); ?></td>
        <td><?php echo esc_html(Slab_Data::money($e12 * $rm, 0)); ?></td>
        <td><?php echo esc_html(Slab_Data::money($e18 * $rm, 0)); ?></td>
      </tr>
      <tr>
        <td><strong>Edge Rebar (2 × #4, 192 lin. ft)</strong></td>
        <td>—</td>
        <td><?php echo esc_html(Slab_Data::mr('monolithic.edge_rebar')); ?></td>
        <td><?php echo esc_html(Slab_Data::mr('monolithic.edge_rebar')); ?></td>
      </tr>
      <tr>
        <td><strong>Extra Trenching</strong></td>
        <td>—</td>
        <td><?php echo esc_html(Slab_Data::money_range($m['trenching']['12'])); ?></td>
        <td><?php echo esc_html(Slab_Data::money_range($m['trenching']['18'])); ?></td>
      </tr>
      <tr>
        <td><strong>Approximate Added Cost</strong></td>
        <td>Baseline</td>
        <td>+<?php echo esc_html(Slab_Data::money_range($add12, 0)); ?></td>
        <td>+<?php echo esc_html(Slab_Data::money_range($add18, 0)); ?></td>
      </tr>
    </tbody>
  </table>
</div>
<p>
  The edge volume comes from the footing ring under the slab: (24×24 − 22×22) = 92 sq ft of footprint, deepened 8" (12" edge) or 14" (18" edge) below the 4" slab. Use the <a href="<?php echo esc_url(home_url('/concrete-footing-calculator/')); ?>">concrete footing calculator</a> for other sizes.
</p>
<p>
  <strong>When a floating slab is allowed:</strong> under IRC R403.1.4.1, a freestanding accessory building of 600 sq ft or less, light-frame construction, with eaves 10 ft or lower does not need frost protection, so a floating slab is usually fine for sheds and small detached garages. Attached garages and anything larger normally need footings at the local frost depth. A 12"–18" turn-down meets that only where frost depth is shallow; in colder areas it needs a deeper frost wall or an insulated frost-protected shallow foundation. Your local building department has the final say.
</p>

<h2 id="control-joint-spacing">Concrete Control Joint Spacing, Saw-Cut Timing &amp; Linear Footage</h2>
<p>
  Concrete shrinks as it dries and will crack somewhere. Control joints decide where. The usual rule (ACI 302.1R) is to space joints <strong><?php echo esc_html(Slab_Data::r('joints.ratio')); ?> times the slab thickness in inches, in feet</strong>. Staying near <?php echo esc_html(Slab_Data::r('joints.ratio_best')); ?> times gives the best crack control, and panels should be close to square, no more than <?php echo esc_html(Slab_Data::num($j['max_aspect'])); ?> times longer than wide.
</p>
<div class="table-responsive">
  <table class="data-table">
    <thead>
      <tr>
        <th>Slab Thickness</th>
        <th>Joint Spacing (Recommended)</th>
        <th>Maximum Spacing</th>
        <th>Saw-Cut Depth (¼ of Thickness)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ([4 => '1"', 5 => '1¼"', 6 => '1½"'] as $tk => $cut) : ?>
      <tr>
        <td><strong><?php echo esc_html($tk); ?> inches</strong></td>
        <td><?php echo esc_html(Slab_Data::num($tk * $j['ratio_best'][0]) . ' – ' . Slab_Data::num($tk * $j['ratio_best'][1])); ?> ft</td>
        <td><?php echo esc_html($j['max_ft'][$tk]); ?> ft</td>
        <td><?php echo esc_html($cut); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<p>
  <strong>Saw-cut timing:</strong> cut as soon as the surface holds a blade without the edges ravelling, typically <strong><?php echo esc_html(Slab_Data::r('joints.sawcut_h')); ?> hours after finishing</strong> with a conventional wet saw. Hot, windy days shorten that window and cool weather stretches it. Early-entry dry saws can cut <?php echo esc_html(Slab_Data::r('joints.early_entry_h')); ?> hours after finishing. Waiting until the next day often means the slab has already cracked on its own. Tooled joints made with a groover while the concrete is still plastic avoid the timing problem on small slabs.
</p>
<p>
  <strong>Linear footage:</strong> for a slab L × W with joint spacing S, the number of joints across the length is the length divided by S, rounded up, minus one; each of those runs the full width, and the same applies the other way.
</p>
<ul>
  <li><strong>20×20 ft, 4" slab at 10 ft:</strong> 1 joint each way = 20 + 20 = <strong>40 linear ft</strong> (four 10×10 panels).</li>
  <li><strong>24×24 ft, 4" slab at 8 ft:</strong> 2 joints each way = 48 + 48 = <strong>96 linear ft</strong> (nine 8×8 panels).</li>
</ul>
<p>
  A walk-behind saw with a diamond blade rents for about <strong><?php echo esc_html(Slab_Data::mr('joints.saw_rental_day', 0, ' to ')); ?> per day</strong>. Contractors commonly charge around <strong><?php echo esc_html(Slab_Data::mr('joints.cut_price_lf', 0, ' to ')); ?> per linear foot</strong> when saw-cutting is priced separately, though most include it in the finishing labor.
</p>
