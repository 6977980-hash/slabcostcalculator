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
?>

<h2 id="concrete-cost-by-psi">Concrete Slab Cost by PSI Strength (3000 vs. 3500 vs. 4000 vs. 4500 PSI)</h2>
<p>
  PSI is the compressive strength the mix reaches after 28 days of curing. Stronger mixes use more cement per yard, so each 500-PSI step usually adds about <strong>$4 to $10 per cubic yard</strong> to the ready-mix price. Air entrainment, fiber, or accelerators are billed on top of that.
</p>
<div class="table-responsive">
  <table class="data-table">
    <thead>
      <tr>
        <th>Mix Strength</th>
        <th>Typical Price per yd³</th>
        <th>Typical Residential Use</th>
        <th>Extra Cost on a 20×20 ft, 4" Slab (5.43 yd³)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>2,500 PSI</strong></td>
        <td>$130 – $155</td>
        <td>Footings and interior slabs not exposed to weather</td>
        <td>−$20 to −$55</td>
      </tr>
      <tr>
        <td><strong>3,000 PSI</strong></td>
        <td>$135 – $160</td>
        <td>Patios, walkways, shed floors in mild climates</td>
        <td>Baseline</td>
      </tr>
      <tr>
        <td><strong>3,500 PSI</strong></td>
        <td>$140 – $170</td>
        <td>Garage floors, driveways, porches and steps in freeze-thaw areas</td>
        <td>+$20 to +$55</td>
      </tr>
      <tr>
        <td><strong>4,000 PSI</strong></td>
        <td>$145 – $180</td>
        <td>Driveways that see de-icing salt, RV and truck pads</td>
        <td>+$45 to +$110</td>
      </tr>
      <tr>
        <td><strong>4,500+ PSI</strong></td>
        <td>$155 – $195</td>
        <td>Heavy equipment, shop floors with forklifts, commercial aprons</td>
        <td>+$65 to +$165</td>
      </tr>
    </tbody>
  </table>
</div>
<p>
  The International Residential Code (Table R402.2) sets the minimums: 2,500 PSI where concrete is protected from weather, 3,000 PSI for exposed walls and foundations, and <strong>3,500 PSI, air-entrained, for garage floors, porches and steps in severe-weathering regions</strong>. On a typical slab, moving from 3,000 to 4,000 PSI adds roughly $45 to $110, which is small next to the cost of replacing a scaling or cracked driveway. Thickness, base compaction and joint spacing affect durability far more than going beyond 4,000 PSI on a home slab.
</p>

<h2 id="concrete-pump-cost">Concrete Pumping Cost: Line Pump vs. Boom Pump</h2>
<p>
  A ready-mix truck's chute reaches only about <strong>15 to 20 feet</strong> past where the truck can park. When the forms sit behind a house, a fence or soft lawn, you either wheelbarrow the concrete or pay for a pump. Most residential pours land in the <strong>$600 to $1,200</strong> range, and the type of pump decides where you fall.
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
        <td>$100 – $175 per hour, 2–4 hour minimum</td>
        <td>$150 – $350 per hour, 3–4 hour minimum</td>
      </tr>
      <tr>
        <td><strong>Typical Residential Pour</strong></td>
        <td>$400 – $800</td>
        <td>$900 – $1,800+</td>
      </tr>
      <tr>
        <td><strong>Reach</strong></td>
        <td>Hose laid on the ground, commonly 150–300 ft from the truck</td>
        <td>Boom arm reaches roughly 75–200 ft over roofs and fences</td>
      </tr>
      <tr>
        <td><strong>Output</strong></td>
        <td>Slower; fine for patios, backyard slabs and footings</td>
        <td>Fast; suits large garage slabs, foundations and second-floor pours</td>
      </tr>
      <tr>
        <td><strong>Mix Requirement</strong></td>
        <td>Often needs a pump mix with 3/8" pea gravel (about $5–$10 more per yd³)</td>
        <td>Usually pumps standard 3/4" aggregate mixes</td>
      </tr>
    </tbody>
  </table>
</div>
<p>
  Because of the hourly minimum, a pump costs about the same for 3 yards as for 10. On a small slab, compare the pump quote with two or three helpers and wheelbarrows: a 10×10 ft slab (1.36 yd³) is only about a dozen wheelbarrow trips, while a 24×24 ft garage slab is closer to 70. Add the pump to your estimate with the <em>Concrete Pump Truck</em> option in the calculator above.
</p>

<h2 id="ready-mix-short-load-fee">Ready-Mix Short Load Fee Breakdown</h2>
<p>
  A transit mixer carries 8 to 10 cubic yards. When you order less than the plant's minimum, the supplier adds a <strong>short-load fee</strong> to cover the driver and truck time. Minimums and fees vary by plant, so always ask for both when you call for a price.
</p>
<ul>
  <li><strong>Flat fee:</strong> commonly <strong>$80 to $200 per load</strong> on orders below the minimum.</li>
  <li><strong>Per-yard fee:</strong> some plants charge <strong>$30 to $45 for each yard</strong> you are short of the minimum.</li>
  <li><strong>Where the minimum sits:</strong> most suppliers set it between 4 and 10 yd³, and many residential suppliers use 5 to 6 yd³.</li>
  <li><strong>Other add-ons:</strong> fuel or environmental surcharges ($10–$50) and overtime if the truck waits past the free unloading time, usually 5–8 minutes per yard.</li>
</ul>
<p>
  <strong>Worked example (10×10 ft, 4" slab):</strong> the slab needs 1.36 yd³ with 10% waste. Plants sell in quarter- or half-yard steps, so you order 1.5 yd³ at $150 = $225, plus a typical $120 short-load fee, for <strong>about $345 delivered</strong>. The same slab in bags is 62 × 80-lb bags at $6.50 = <strong>$403</strong>, plus roughly 2.5 tons of hand mixing. Below about 1 cubic yard bags usually win; above 1.5 yards ready-mix usually wins even with the fee. A short-load fee is also a reason to combine pours, such as a patio and a walkway on the same day.
</p>

<h2 id="monolithic-vs-floating-slab">Monolithic Turn-Down Slab vs. Floating Slab Foundation Cost</h2>
<p>
  A <strong>floating slab</strong> is a flat slab of uniform thickness that rests on compacted gravel. A <strong>monolithic (turn-down) slab</strong> is poured in one piece with a thickened edge that acts as its footing, usually <strong>12" wide and 12" to 18" deep</strong>, with two continuous #4 bars in the edge. Garages and larger sheds often need the turn-down edge to pass inspection.
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
    <tbody>
      <tr>
        <td><strong>Concrete (with 10% waste)</strong></td>
        <td>7.82 yd³</td>
        <td>10.32 yd³</td>
        <td>12.20 yd³</td>
      </tr>
      <tr>
        <td><strong>Concrete Cost at $150/yd³</strong></td>
        <td>$1,173</td>
        <td>$1,548</td>
        <td>$1,830</td>
      </tr>
      <tr>
        <td><strong>Edge Rebar (2 × #4, 192 lin. ft)</strong></td>
        <td>—</td>
        <td>$100 – $150</td>
        <td>$100 – $150</td>
      </tr>
      <tr>
        <td><strong>Extra Trenching</strong></td>
        <td>—</td>
        <td>$150 – $400</td>
        <td>$250 – $600</td>
      </tr>
      <tr>
        <td><strong>Approximate Added Cost</strong></td>
        <td>Baseline</td>
        <td>+$625 – $925</td>
        <td>+$1,000 – $1,400</td>
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
  Concrete shrinks as it dries and will crack somewhere. Control joints decide where. The usual rule (ACI 302.1R) is to space joints <strong>2 to 3 times the slab thickness in inches, in feet</strong>. Staying near 2 to 2.5 times gives the best crack control, and panels should be close to square, no more than 1.5 times longer than wide.
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
      <tr>
        <td><strong>4 inches</strong></td>
        <td>8 – 10 ft</td>
        <td>12 ft</td>
        <td>1"</td>
      </tr>
      <tr>
        <td><strong>5 inches</strong></td>
        <td>10 – 12.5 ft</td>
        <td>15 ft</td>
        <td>1¼"</td>
      </tr>
      <tr>
        <td><strong>6 inches</strong></td>
        <td>12 – 15 ft</td>
        <td>15 ft</td>
        <td>1½"</td>
      </tr>
    </tbody>
  </table>
</div>
<p>
  <strong>Saw-cut timing:</strong> cut as soon as the surface holds a blade without the edges ravelling, typically <strong>4 to 12 hours after finishing</strong> with a conventional wet saw. Hot, windy days shorten that window and cool weather stretches it. Early-entry dry saws can cut 1 to 4 hours after finishing. Waiting until the next day often means the slab has already cracked on its own. Tooled joints made with a groover while the concrete is still plastic avoid the timing problem on small slabs.
</p>
<p>
  <strong>Linear footage:</strong> for a slab L × W with joint spacing S, the number of joints across the length is the length divided by S, rounded up, minus one; each of those runs the full width, and the same applies the other way.
</p>
<ul>
  <li><strong>20×20 ft, 4" slab at 10 ft:</strong> 1 joint each way = 20 + 20 = <strong>40 linear ft</strong> (four 10×10 panels).</li>
  <li><strong>24×24 ft, 4" slab at 8 ft:</strong> 2 joints each way = 48 + 48 = <strong>96 linear ft</strong> (nine 8×8 panels).</li>
</ul>
<p>
  A walk-behind saw with a diamond blade rents for about <strong>$60 to $120 per day</strong>. Contractors commonly charge around <strong>$1 to $2 per linear foot</strong> when saw-cutting is priced separately, though most include it in the finishing labor.
</p>
