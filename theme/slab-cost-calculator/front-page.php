<?php
/**
 * FRONT PAGE TEMPLATE - MASTER CONCRETE SLAB ESTIMATING HUB
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$sd = Slab_Data::all();
$psf = Slab_Data::installed_psf();
$diy_psf = Slab_Data::diy_psf();
$rm_range = [Slab_Data::psi_price(3000)[0], Slab_Data::psi_price(4000)[1]];
$s20 = Slab_Data::size_costs(20, 20, 4);
$f10 = Slab_Data::size_fact(10, 10);
$f20 = Slab_Data::size_fact(20, 20);
$f = $sd['finish'];
?>

<!-- Hero Section with Concise Value Proposition -->
<section class="hero-section">
  <div class="container">
    <h1 class="hero-title">Concrete Slab Cost Calculator</h1>
    <p class="hero-subtitle">
      Calculate exact concrete volume in cubic yards and bags, compare ready-mix vs. bagged pricing, and estimate full project costs including site prep, gravel base, rebar reinforcement, and finishing labor.
    </p>
    <p class="hero-answer">
      <strong>Quick answer:</strong> a 4-inch concrete slab costs about <strong><?php echo esc_html(Slab_Data::money_range($psf, 0, ' to ')); ?> per sq ft installed</strong>, so a 20 × 20 ft slab runs <?php echo esc_html(Slab_Data::money_range($s20['installed'], 0, ' to ')); ?>. Materials alone for DIY are <?php echo esc_html(Slab_Data::money_range($diy_psf, 2, ' to ')); ?> per sq ft.
    </p>

    <div class="hero-cta-row">
      <a href="#concrete-slab-calculator" class="hero-cta hero-cta-primary">Estimate my slab</a>
      <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>" class="hero-cta hero-cta-quote" data-open-tab="bid">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
        Got a quote? Check if it's fair
      </a>
    </div>

    <!-- Key Engineering Metrics Bar -->
    <div class="hero-key-takeaways">
      <div class="takeaway-card">
        <div class="takeaway-label">Avg. Installed Cost</div>
        <div class="takeaway-value"><?php echo esc_html(Slab_Data::money_range($psf, 2)); ?> / sq. ft.</div>
      </div>
      <div class="takeaway-card">
        <div class="takeaway-label">Ready-Mix Concrete</div>
        <div class="takeaway-value"><?php echo esc_html(Slab_Data::money_range($rm_range, 0)); ?> / cubic yard</div>
      </div>
      <div class="takeaway-card">
        <div class="takeaway-label">Standard Thickness</div>
        <div class="takeaway-value">4" (Patio) | 5"-6" (Driveway)</div>
      </div>
      <div class="takeaway-card">
        <div class="takeaway-label">Standard Waste Factor</div>
        <div class="takeaway-value">+<?php echo esc_html($sd['concrete']['waste_pct']); ?>% Recommended</div>
      </div>
    </div>

    <!-- Trust & E-E-A-T Status Bar -->
    <div class="trust-bar">
      <div class="trust-item">
        <span class="trust-badge">&#10003; ACI 302.1R Compliant</span>
      </div>
      <div class="trust-item">
        <strong>Last Updated:</strong> <time datetime="<?php echo esc_attr(SLAB_CONTENT_UPDATED); ?>"><?php echo esc_html(wp_date('F j, Y', strtotime(SLAB_CONTENT_UPDATED . ' 12:00:00'))); ?></time>
      </div>
      <div class="trust-item">
        <strong>Review Board:</strong> <span>Civil Estimating &amp; Materials Engineering</span>
      </div>
      <div class="trust-item">
        <a href="<?php echo esc_url(home_url('/methodology/')); ?>" style="font-weight: 600; text-decoration: underline;">View Calculation Formulas &amp; Methodology &rarr;</a>
      </div>
    </div>

    <!-- Primary Interactive Project Calculator -->
    <?php get_template_part('template-parts/calculator-primary'); ?>

    <!-- Tools strip: each card opens its tab in the Live Estimate panel -->
    <div class="tools-strip" aria-label="Free slab planning tools">
      <a class="tool-card tool-card-bid" href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>">
        <span class="tool-card-title">Contractor Quote Checker</span>
        <span class="tool-card-text">Compare a bid with a scope-matched benchmark for your slab.</span>
      </a>
      <a class="tool-card" href="#live-estimate" data-open-tab="blueprint">
        <span class="tool-card-title">Slab &amp; Rebar Blueprint</span>
        <span class="tool-card-text">Scaled plan with rebar grid, chairs and saw cuts. Download as PNG.</span>
      </a>
      <a class="tool-card" href="#live-estimate" data-open-tab="weather">
        <span class="tool-card-title">Pour-Day Weather Check</span>
        <span class="tool-card-text">Hot, cold and evaporation-rate checks from ACI 305R and 306R.</span>
      </a>
      <a class="tool-card" href="#live-estimate" data-open-tab="breakdown">
        <span class="tool-card-title">Line-Item Estimate &amp; PDF</span>
        <span class="tool-card-text">Every cost line, then download a PDF report to share.</span>
      </a>
    </div>
  </div>
</section>

<!-- Topic clusters (replaces the latest-posts grid) -->
<div class="container">
  <?php get_template_part('template-parts/topic-clusters'); ?>
</div>

<!-- Deep Authoritative Content Architecture -->
<main id="primary" class="site-main">
  <div class="container">
    <div class="content-grid">
      <!-- Main Content Column -->
      <article class="article-content">
        
        <h2 id="slab-cost-2026">How Much Does a Concrete Slab Cost in 2026?</h2>
        <p>
          On average, pouring a residential concrete slab costs between <strong><?php echo esc_html(Slab_Data::money($psf[0], 2) . ' and ' . Slab_Data::money($psf[1], 2)); ?> per square foot</strong> for professional installation. For a standard 4-inch thick slab measuring 400 square feet (20×20 ft), the total installed cost typically ranges from <strong><?php echo esc_html(Slab_Data::money_range($s20['installed'], 0, ' to ')); ?></strong>. Small slabs cost more per square foot because delivery, forms and setup are spread over less area (a 10×10 ft slab runs <?php echo esc_html($f10['installed']); ?>). If you choose to self-perform the work (DIY mode) and purchase only the raw materials, concrete slab costs typically range between <strong><?php echo esc_html(Slab_Data::money($diy_psf[0], 2) . ' and ' . Slab_Data::money($diy_psf[1], 2)); ?> per square foot</strong>.
        </p>

        <div class="definition-card">
          <div class="definition-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            Key Cost Distinction: Material Cost vs. Total Installed Cost
          </div>
          <p>
            <strong>Material-only cost</strong> includes the concrete (ready-mix or bags), form boards, vapor barrier, and rebar (<?php echo esc_html(Slab_Data::money_range($diy_psf, 2, '–')); ?>/sq ft). <strong>Installed cost</strong> includes form layout, reinforcement tying, screeding, bull floating, power troweling, saw-cut control joints, and curing (<?php echo esc_html(Slab_Data::money_range($psf, 2, '–')); ?>/sq ft). Site excavation, grading and a compacted gravel base add about <?php echo esc_html(Slab_Data::money_range(Slab_Data::site_prep_psf(), 2, '–')); ?>/sq ft when the site needs them.
          </p>
        </div>

        <!-- Slab Cross Section Diagram -->
        <figure style="margin: 2rem 0; text-align: center;">
          <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/slab-diagram.svg'); ?>" alt="Concrete slab dimensions showing length width and thickness with sub-base and rebar layers" style="border-radius: 12px; box-shadow: var(--shadow-md);" width="800" height="450">
          <figcaption style="font-size: 0.8125rem; color: var(--slate-500); margin-top: 0.5rem; font-style: italic;">
            Figure 1: Standard residential concrete slab structural cross-section showing subgrade, gravel base, vapor barrier, reinforcement, and formwork.
          </figcaption>
        </figure>

        <h2 id="cost-line-items">What Goes Into a Concrete Slab Cost? (Comprehensive Line Items)</h2>
        <p>
          A realistic concrete slab estimate requires breaking down all physical components and labor stages. Below is a detailed inspection of each scope element:
        </p>

        <h3>1. Concrete Material (Ready-Mix or Bags)</h3>
        <p>
          Ready-mix concrete delivered by a transit truck averages <strong><?php echo esc_html(Slab_Data::money_range($rm_range, 0, ' to ')); ?> per cubic yard</strong> (3,000 to 4,000 PSI standard compressive mix). Bagged concrete mix (such as Quikrete or Sakrete) costs approximately <strong><?php echo esc_html(Slab_Data::mr('concrete.bag_price_range', 2, ' to ')); ?> per 80-lb bag</strong>. Ready-mix is usually more economical above about <?php echo esc_html(Slab_Data::num($sd['delivery']['ready_mix_wins_above_yd'])); ?> cubic yards, even with a short-load fee, while bagged concrete is ideal for small pads or remote locations where ready-mix trucks cannot access.
        </p>

        <h3>2. Site Excavation &amp; Grading</h3>
        <p>
          Preparing the ground involves removing grass, topsoil, and organic matter to a depth of 4 to 8 inches. Excavation costs typically run <strong><?php echo esc_html(Slab_Data::mr('site.excavation_range', 2, ' to ')); ?> per square foot</strong>, depending on root systems, slope, and soil density.
        </p>

        <h3>3. Compacted Gravel Sub-Base</h3>
        <p>
          A sturdy 4-inch base of crushed stone (such as #57 stone or crushed aggregate base) distributes vehicle loads, prevents frost heaving, and allows water drainage. Crushed gravel costs approximately <strong><?php echo esc_html(Slab_Data::mr('site.gravel_range', 0, ' to ')); ?> per cubic yard</strong> (or <?php echo esc_html(Slab_Data::mr('site.gravel_ton_range', 0, ' to ')); ?> per ton).
        </p>

        <h3>4. Perimeter Formwork Lumber</h3>
        <p>
          Wood formwork built with 2×4 or 2×6 dimensional lumber, wood stakes, and duplex nails holds the wet concrete in place during placement and curing. Form lumber and staking materials generally cost <strong><?php echo esc_html(Slab_Data::mr('site.forms_range', 2, ' to ')); ?> per linear foot</strong> of slab perimeter.
        </p>

        <h3>5. Steel Reinforcement (Rebar vs. Wire Mesh)</h3>
        <p>
          Reinforcement absorbs tensile stresses and controls drying shrinkage cracking. Standard <strong>#4 (1/2") steel rebar</strong> tied on 12-inch to 18-inch grid centers costs approximately <strong><?php echo esc_html(Slab_Data::mr('reinforcement.rebar_range', 2, ' to ')); ?> per square foot</strong> including rebar support chairs. <strong>Welded wire mesh (6×6 W1.4/W2.9)</strong> costs approximately <strong><?php echo esc_html(Slab_Data::mr('reinforcement.mesh_range', 2, ' to ')); ?> per square foot</strong>.
        </p>

        <h3>6. Vapor Barrier Moisture Retarder</h3>
        <p>
          A heavy-duty 10-mil to 15-mil polyethylene vapor barrier placed beneath interior garage or enclosed shed slabs prevents ground moisture vapor from migrating through the cured concrete. Vapor barriers cost <strong><?php echo esc_html(Slab_Data::mr('site.vapor_range', 2, ' to ')); ?> per square foot</strong>.
        </p>

        <h3>7. Surface Finishing Technique</h3>
        <p>
          Finishing determines appearance and slip resistance:
        </p>
        <ul>
          <li><strong>Broom Finish:</strong> Standard textured exterior finish for walkways and driveways (included in standard labor or +<?php echo esc_html(Slab_Data::money_range($f['broom']['range'], 2, '–')); ?>/sq ft).</li>
          <li><strong>Smooth Hard Trowel:</strong> Polished interior finish for workshops and garages (+<?php echo esc_html(Slab_Data::money_range($f['trowel']['range'], 2, '–')); ?>/sq ft).</li>
          <li><strong>Exposed Aggregate:</strong> Decorative stone surface (+<?php echo esc_html(Slab_Data::money_range($f['exposed']['range'], 2, '–')); ?>/sq ft).</li>
          <li><strong>Stamped &amp; Stained Concrete:</strong> Patterned brick/slate textures (+<?php echo esc_html(Slab_Data::money_range($f['stamped']['range'], 2, '–')); ?>/sq ft).</li>
        </ul>

        <h3>8. Concrete Finishing Labor</h3>
        <p>
          Experienced concrete finishers charge between <strong><?php echo esc_html(Slab_Data::money($sd['labor']['sqft_range'][0], 2) . ' and ' . Slab_Data::money($sd['labor']['sqft_range'][1], 2)); ?> per square foot</strong> (about <?php echo esc_html(Slab_Data::mr('labor.worker_hour', 0, ' to ')); ?> per finisher-hour, or <?php echo esc_html(Slab_Data::mr('labor.crew_hour', 0, ' to ')); ?> per hour for a <?php echo esc_html(Slab_Data::r('labor.crew_size', '–')); ?> person crew) for setup, pouring, screeding, floating, edging, control joint cutting, and applying curing compound.
        </p>

        <h3>9. Delivery Charges &amp; Pump Rental</h3>
        <p>
          Transit mixer suppliers often add a short-load fee of <strong><?php echo esc_html(Slab_Data::mr('delivery.short_load_flat', 0, ' to ')); ?></strong> on orders below their minimum, which ranges from <?php echo esc_html(Slab_Data::r('delivery.min_load_yd')); ?> cubic yards and is commonly <?php echo esc_html(Slab_Data::r('delivery.common_min_yd')); ?> cubic yards (<a href="#ready-mix-short-load-fee">fee breakdown</a>). If the concrete truck cannot park within <?php echo esc_html(Slab_Data::r('site.chute_reach_ft')); ?> feet of the forms due to fences, landscaping, or steep terrain, a trailer line pump or boom pump truck must be rented for <strong><?php echo esc_html(Slab_Data::mr('pump.typical', 0, ' to ')); ?> per pour</strong> (<a href="#concrete-pump-cost">line vs. boom pump</a>).
        </p>

        <h2 id="slab-cost-by-size">Concrete Slab Cost by Size (Reference Matrix)</h2>
        <p>
          Below are typical installed and DIY budget benchmarks across popular residential slab footprint dimensions at a standard 4-inch depth:
        </p>

        <?php get_template_part('template-parts/cost-tables'); ?>

        <h2 id="slab-thickness">Concrete Slab Thickness Guide: 4" vs. 5" vs. 6"</h2>
        <p>
          Choosing the correct thickness is critical for structural load capacity and longevity. The chart below illustrates standard depth ratings:
        </p>

        <figure style="margin: 2rem 0; text-align: center;">
          <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/thickness-diagram.svg'); ?>" alt="Concrete slab thickness guide showing 4 inch patio 5 inch driveway and 6 inch heavy vehicle applications" style="border-radius: 12px; box-shadow: var(--shadow-md);" width="800" height="400">
        </figure>

        <ul>
          <li><strong>4-Inch Slab:</strong> Best for foot-traffic applications, including patios, sidewalks, trash bin pads, and light utility sheds (min <?php echo esc_html(number_format($sd['code']['psi_exposed'])); ?> PSI).</li>
          <li><strong>5-Inch Slab:</strong> Ideal for standard residential driveways, 2-car garages, and parking pads supporting passenger sedans and light pickup trucks (<?php echo esc_html(number_format($sd['code']['psi_severe']) . '–' . number_format($sd['code']['psi_deicing_recommended'])); ?> PSI, air-entrained in freeze-thaw areas).</li>
          <li><strong>6-Inch Slab:</strong> Recommended for RV parking pads, heavy-duty workshops, commercial loading docks, and heavy multi-axle vehicle storage (4,000+ PSI with #4 rebar grid).</li>
        </ul>

        <h2 id="concrete-formulas">How to Calculate Concrete for a Slab (Mathematical Formulas)</h2>
        <p>
          Calculating concrete volume is straightforward when using consistent units. All volume calculations follow three fundamental steps:
        </p>

        <div class="formula-box">
          <div class="formula-label">Volume Formulas</div>
          <div class="formula-math">Area (sq ft) = Length (ft) × Width (ft)</div>
          <div class="formula-math">Volume (cu ft) = Area (sq ft) × [Thickness (inches) ÷ 12]</div>
          <div class="formula-math">Cubic Yards (yd³) = Volume (cu ft) ÷ 27</div>
          <div class="formula-math">Cubic Meters (m³) = Volume (cu ft) × 0.0283168</div>
          <div class="formula-explanation">
            To account for subgrade unevenness, form bulging, and spillage, always multiply net cubic yards by a <strong>10% waste factor (× 1.10)</strong>.
          </div>
        </div>

        <figure style="margin: 2rem 0; text-align: center;">
          <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/cubic-yard-diagram.svg'); ?>" alt="Cubic yard 3D volume diagram showing 3ft by 3ft by 3ft equals 27 cubic feet" style="border-radius: 12px; box-shadow: var(--shadow-md);" width="800" height="400">
        </figure>

        <h3>Practical Example: Calculating a 20×20 Concrete Slab at 4 Inches</h3>
        <ol>
          <li><strong>Calculate Surface Area:</strong> 20 ft × 20 ft = 400 sq. ft.</li>
          <li><strong>Convert Thickness to Feet:</strong> 4 inches ÷ 12 = 0.3333 ft.</li>
          <li><strong>Calculate Net Volume:</strong> 400 sq. ft. × 0.3333 ft = 133.33 cubic feet.</li>
          <li><strong>Convert to Cubic Yards:</strong> 133.33 cu. ft. ÷ 27 = <strong><?php echo esc_html($f20['net_yd']); ?> cubic yards</strong>.</li>
          <li><strong>Apply <?php echo esc_html($sd['concrete']['waste_pct']); ?>% Waste Factor:</strong> <?php echo esc_html($f20['net_yd']); ?> × <?php echo esc_html(number_format(1 + $sd['concrete']['waste_pct'] / 100, 2)); ?> = <strong><?php echo esc_html($f20['order_yd']); ?> cubic yards</strong> (Order <?php echo esc_html(Slab_Data::num(ceil($s20['est']['order_yd'] * 2 - 1e-9) / 2)); ?> yards from your ready-mix supplier).</li>
        </ol>

        <h2 id="ready-mix-vs-bagged">Ready-Mix vs. Bagged Concrete: Which is Better for Your Slab?</h2>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Factor</th>
                <th>Ready-Mix Concrete (Truck Delivery)</th>
                <th>Bagged Concrete Mix (Pre-Mixed Bags)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>Best For</strong></td>
                <td>Slabs &gt; <?php echo esc_html(Slab_Data::num($sd['delivery']['ready_mix_wins_above_yd'])); ?> cubic yards (Patios, Driveways, Garages)</td>
                <td>Small repairs, post holes, pads &lt; <?php echo esc_html(Slab_Data::num($sd['delivery']['bags_win_below_yd'], 1)); ?> cu yd (4x4, 6x6)</td>
              </tr>
              <tr>
                <td><strong>Cost Comparison</strong></td>
                <td><?php echo esc_html(Slab_Data::money_range($rm_range, 0, '–')); ?>/yd³ + delivery (~<?php echo esc_html(Slab_Data::money_range([$rm_range[0] / 27, $rm_range[1] / 27], 2, '–')); ?>/cu ft before fees)</td>
                <?php $bp = $sd['concrete']['bag_price_default']; $by = $sd['concrete']['bag_yields']['80']; ?>
                <td><?php echo esc_html(Slab_Data::money($bp, 2)); ?>/80-lb bag = <?php echo esc_html(Slab_Data::money($bp / $by, 2)); ?>/cu ft (~<?php echo esc_html(Slab_Data::money($bp / $by * 27, 0)); ?>/cu yd)</td>
              </tr>
              <tr>
                <td><strong>Mixing Effort</strong></td>
                <td>Discharged from truck chute in 15–30 minutes</td>
                <td>Requires manual shovel mixing or drum mixer for hours</td>
              </tr>
              <tr>
                <td><strong>Strength Consistency</strong></td>
                <td>Engineered batch plant control (exact water/cement ratio)</td>
                <td>Varies per batch based on hand-measured water additions</td>
              </tr>
              <tr>
                <td><strong>Cold Joint Risk</strong></td>
                <td>Zero (continuous monolithic pour)</td>
                <td>High (if mixing cannot keep up with initial set)</td>
              </tr>
            </tbody>
          </table>
        </div>

        <h2 id="rebar-vs-mesh">Concrete Slab Reinforcement: Rebar Grid vs. Welded Wire Mesh</h2>
        <figure style="margin: 2rem 0; text-align: center;">
          <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/reinforcement-diagram.svg'); ?>" alt="Rebar grid vs welded wire mesh comparison for concrete slab reinforcement" style="border-radius: 12px; box-shadow: var(--shadow-md);" width="800" height="400">
        </figure>
        <p>
          Concrete exhibits exceptional compressive strength (resisting downward crushing), but possesses low tensile strength (susceptible to cracking when pulled or bent). Steel reinforcement provides necessary tensile resistance:
        </p>
        <ul>
          <li><strong>#4 (1/2") Steel Rebar Grid:</strong> Essential for vehicle loads (driveways, garages, equipment pads). Tied on 12" to 18" grid squares and held at mid-depth using concrete or plastic rebar chairs.</li>
          <li><strong>Welded Wire Mesh (6×6 W1.4/W2.9):</strong> Adequate for light foot-traffic slabs (patios, walkways, shed floors). Controls drying shrinkage micro-cracking.</li>
        </ul>

        <h2 id="cost-increase-factors">Factors That Increase Concrete Slab Costs</h2>
        <p>
          When requesting contractor quotes, several jobsite variables can increase unit prices:
        </p>
        <ul>
          <li><strong>Restricted Site Access:</strong> If concrete trucks cannot back up directly to the slab forms, wheelbarrowing concrete or hiring a pump truck (<?php echo esc_html(Slab_Data::mr('pump.typical', 0, '–')); ?>) increases labor and equipment expense.</li>
          <li><strong>Sloped or Uneven Ground:</strong> Sloped terrain requires additional excavation, retaining walls, or thicker edge forms.</li>
          <li><strong>Tree Root Removal &amp; Rock Excavation:</strong> Rocky soil or large root systems require mini-excavator equipment or jackhammers.</li>
          <li><strong>Permits &amp; Inspections:</strong> Municipal flatwork or building permits for small residential slabs commonly range from <?php echo esc_html(Slab_Data::mr('permit.flatwork', 0, ' to ')); ?> depending on local jurisdiction.</li>
        </ul>

        <?php get_template_part('template-parts/cost-factor-guides'); ?>

        <!-- FAQ Section with Schema Accordion -->
        <section class="faq-section" aria-label="Frequently Asked Questions">
          <h2 id="faq">Frequently Asked Questions About Concrete Slab Costs</h2>

          <?php foreach (Slab_Schema_Generator::get_home_faqs() as $faq) : ?>
          <div class="faq-item">
            <button type="button" class="faq-question" aria-expanded="false">
              <?php echo esc_html($faq['q']); ?>
            </button>
            <div class="faq-answer">
              <?php echo esc_html($faq['a']); ?>
            </div>
          </div>
          <?php endforeach; ?>
        </section>

        <!-- Author / Review Team Component -->
        <?php get_template_part('template-parts/author-bio'); ?>

      </article>

      <!-- Sidebar Column: Supporting Calculators & Guides -->
      <aside class="sidebar-col">
        <div class="sidebar-widget">
          <div class="widget-title">Supporting Calculators</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item">
              <a href="<?php echo esc_url(home_url('/concrete-volume-calculator/')); ?>">
                <span>Concrete Volume</span>
                <span class="calculator-nav-badge">yd³ &amp; m³</span>
              </a>
            </li>
            <li class="calculator-nav-item">
              <a href="<?php echo esc_url(home_url('/concrete-bags-calculator/')); ?>">
                <span>Concrete Bags</span>
                <span class="calculator-nav-badge">40-80 lb</span>
              </a>
            </li>
            <li class="calculator-nav-item">
              <a href="<?php echo esc_url(home_url('/concrete-yard-calculator/')); ?>">
                <span>Cubic Yard Estimator</span>
                <span class="calculator-nav-badge">+10% Waste</span>
              </a>
            </li>
            <li class="calculator-nav-item">
              <a href="<?php echo esc_url(home_url('/concrete-weight-calculator/')); ?>">
                <span>Concrete Weight</span>
                <span class="calculator-nav-badge">lbs &amp; tons</span>
              </a>
            </li>
            <li class="calculator-nav-item">
              <a href="<?php echo esc_url(home_url('/concrete-cost-per-square-foot-calculator/')); ?>">
                <span>Cost Per Square Foot</span>
                <span class="calculator-nav-badge">$/sq ft</span>
              </a>
            </li>
            <li class="calculator-nav-item">
              <a href="<?php echo esc_url(home_url('/concrete-rebar-calculator/')); ?>">
                <span>Rebar Grid Estimator</span>
                <span class="calculator-nav-badge">#3, #4, #5</span>
              </a>
            </li>
            <li class="calculator-nav-item">
              <a href="<?php echo esc_url(home_url('/gravel-base-calculator/')); ?>">
                <span>Gravel Sub-Base</span>
                <span class="calculator-nav-badge">Tons &amp; yd³</span>
              </a>
            </li>
            <li class="calculator-nav-item">
              <a href="<?php echo esc_url(home_url('/concrete-footing-calculator/')); ?>">
                <span>Concrete Footing / Pier</span>
                <span class="calculator-nav-badge">Strip &amp; Post</span>
              </a>
            </li>
            <li class="calculator-nav-item">
              <a href="<?php echo esc_url(home_url('/concrete-removal-cost-calculator/')); ?>">
                <span>Demolition &amp; Removal</span>
                <span class="calculator-nav-badge">Disposal</span>
              </a>
            </li>
            <li class="calculator-nav-item">
              <a href="<?php echo esc_url(home_url('/concrete-labor-cost-calculator/')); ?>">
                <span>Labor Cost</span>
                <span class="calculator-nav-badge">Crew Hrs</span>
              </a>
            </li>
          </ul>
        </div>

        <div class="sidebar-widget">
          <div class="widget-title">Application Guides</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item">
              <a href="<?php echo esc_url(home_url('/patio/')); ?>">
                <span>Patio Concrete Slabs</span>
                <span>&rarr;</span>
              </a>
            </li>
            <li class="calculator-nav-item">
              <a href="<?php echo esc_url(home_url('/driveway/')); ?>">
                <span>Driveway Concrete</span>
                <span>&rarr;</span>
              </a>
            </li>
            <li class="calculator-nav-item">
              <a href="<?php echo esc_url(home_url('/garage/')); ?>">
                <span>Garage Floor Slabs</span>
                <span>&rarr;</span>
              </a>
            </li>
            <li class="calculator-nav-item">
              <a href="<?php echo esc_url(home_url('/shed/')); ?>">
                <span>Shed Concrete Pads</span>
                <span>&rarr;</span>
              </a>
            </li>
            <li class="calculator-nav-item">
              <a href="<?php echo esc_url(home_url('/foundation/')); ?>">
                <span>Monolithic Foundations</span>
                <span>&rarr;</span>
              </a>
            </li>
          </ul>
        </div>

        <div class="sidebar-widget">
          <div class="widget-title">Cost Factor Guides</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="#concrete-cost-by-psi">Concrete Cost by PSI</a></li>
            <li class="calculator-nav-item"><a href="#concrete-pump-cost">Line vs. Boom Pump Cost</a></li>
            <li class="calculator-nav-item"><a href="#ready-mix-short-load-fee">Ready-Mix Short Load Fee</a></li>
            <li class="calculator-nav-item"><a href="#monolithic-vs-floating-slab">Monolithic vs. Floating Slab</a></li>
            <li class="calculator-nav-item"><a href="#control-joint-spacing">Control Joint Spacing &amp; Timing</a></li>
          </ul>
        </div>

        <div class="sidebar-widget">
          <div class="widget-title">Cost Breakdown Guides</div>
          <ul class="calculator-nav-list">
            <?php
            $short_k = function ($n) {
                return $n >= 1000 ? '$' . rtrim(rtrim(number_format($n / 1000, 1), '0'), '.') . 'k' : '$' . number_format($n);
            };
            foreach ([[10, 10, '10x10 Slab Cost'], [12, 12, '12x12 Slab Cost'], [20, 20, '20x20 Slab Cost'], [24, 24, '24x24 Garage Slab']] as $sz) :
                $c = Slab_Data::size_costs($sz[0], $sz[1], 4);
            ?>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/' . $sz[0] . 'x' . $sz[1] . '-concrete-slab-cost-breakdown/')); ?>"><?php echo esc_html($sz[2] . ' (' . $short_k($c['installed'][0]) . '–' . $short_k($c['installed'][1]) . ')'); ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <!-- Sticky contents list for the long guide -->
        <nav class="sidebar-widget sidebar-toc" aria-label="On this page">
          <div class="widget-title">On This Page</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="#slab-cost-2026">Slab cost in 2026</a></li>
            <li class="calculator-nav-item"><a href="#cost-line-items">What goes into the cost</a></li>
            <li class="calculator-nav-item"><a href="#slab-cost-by-size">Cost by slab size</a></li>
            <li class="calculator-nav-item"><a href="#slab-thickness">Thickness guide</a></li>
            <li class="calculator-nav-item"><a href="#concrete-formulas">Volume formulas</a></li>
            <li class="calculator-nav-item"><a href="#ready-mix-vs-bagged">Ready-mix vs. bagged</a></li>
            <li class="calculator-nav-item"><a href="#rebar-vs-mesh">Rebar vs. mesh</a></li>
            <li class="calculator-nav-item"><a href="#cost-increase-factors">What raises the cost</a></li>
            <li class="calculator-nav-item"><a href="#concrete-cost-by-psi">Cost factor guides</a></li>
            <li class="calculator-nav-item"><a href="#faq">FAQ</a></li>
          </ul>
        </nav>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
