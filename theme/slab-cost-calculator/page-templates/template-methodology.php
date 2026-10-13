<?php
/**
 * Template Name: Methodology & Calculation Formulas
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
slab_render_breadcrumbs();

$sd = Slab_Data::all();
?>

<main id="primary" class="site-main">
  <div class="container container-narrow">
    <article class="article-content">
      <header class="page-header" style="margin-bottom: 2rem;">
        <h1 class="hero-title">Calculation Methodology &amp; Engineering Standards</h1>
        <div class="trust-bar">
          <div class="trust-item"><strong>Standards:</strong> <span>ACI 302.1R / ASTM C94</span></div>
          <div class="trust-item"><strong>Review Cycle:</strong> <span>Quarterly Price Re-indexing</span></div>
          <div class="trust-item"><strong>Status:</strong> <span>Open Formula Verification</span></div>
        </div>
      </header>

      <p>
        At <strong>Slab Cost Calculator</strong>, we believe engineering transparency is the bedrock of trustworthy cost estimation. Unlike "black box" calculators that produce opaque numbers, every calculation performed on our platform is grounded in standard civil engineering geometry, ASTM material yield constants, and verified regional construction pricing.
      </p>

      <h2>1. Volume Geometry &amp; Unit Conversion Formulas</h2>
      <p>
        All volume calculations evaluate three-dimensional solid prisms using the following verified equations:
      </p>

      <div class="formula-box">
        <div class="formula-label">Imperial Volume Formulations</div>
        <div class="formula-math">Net Volume (ft³) = Length (ft) × Width (ft) × [Thickness (inches) ÷ 12]</div>
        <div class="formula-math">Cubic Yards (yd³) = Net Volume (ft³) ÷ 27</div>
        <div class="formula-math">Gross Order Volume (yd³) = Net Volume (yd³) × (1 + Waste Factor %)</div>
      </div>

      <div class="formula-box">
        <div class="formula-label">Metric Formulations &amp; Exact Conversion Constants</div>
        <div class="formula-math">Net Volume (m³) = Length (m) × Width (m) × [Thickness (cm) ÷ 100]</div>
        <div class="formula-math">1 Cubic Yard (yd³) = 27 Cubic Feet = 0.764554857984 m³</div>
        <div class="formula-math">1 Cubic Meter (m³) = 35.3146667 Cubic Feet = 1.30795062 yd³</div>
      </div>

      <h2>2. Pre-Mixed Bagged Concrete Yield Constants</h2>
      <p>
        Bag requirements are calculated based on dry packaged density yields established under <strong>ASTM C387</strong>:
      </p>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Bag Mass (lbs)</th>
              <th>Standard Wet Yield (cu. ft.)</th>
              <th>Bags per Cubic Yard (27 cu. ft.)</th>
              <th>Typical Applications</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>80 lb (36.3 kg)</strong></td>
              <td>0.60 cu. ft. (0.017 m³)</td>
              <td>45.0 bags / yd³</td>
              <td>Standard slab pours, footings, structural pads</td>
            </tr>
            <tr>
              <td><strong>60 lb (27.2 kg)</strong></td>
              <td>0.45 cu. ft. (0.0127 m³)</td>
              <td>60.0 bags / yd³</td>
              <td>Light DIY flatwork, fence posts, curb repairs</td>
            </tr>
            <tr>
              <td><strong>50 lb (22.7 kg)</strong></td>
              <td>0.375 cu. ft. (0.0106 m³)</td>
              <td>72.0 bags / yd³</td>
              <td>Fast-setting post anchor mixes</td>
            </tr>
            <tr>
              <td><strong>40 lb (18.1 kg)</strong></td>
              <td>0.30 cu. ft. (0.0085 m³)</td>
              <td>90.0 bags / yd³</td>
              <td>Patchwork, masonry anchoring, light steps</td>
            </tr>
          </tbody>
        </table>
      </div>

      <h2>3. Concrete Mix Strength &amp; PSI Grades</h2>
      <p>
        Concrete strength is specified by its 28-day compressive strength rating, measured in <strong>PSI (pounds per square inch)</strong> in the US — the imperial equivalent of the metric M-grade system (e.g., M15, M20, M25) used in regions on the metric standard. The required strength class depends on the application and its exposure to weather, freeze-thaw cycling, and structural load:
      </p>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>PSI Grade</th>
              <th>Approx. Metric Equivalent</th>
              <th>Typical Applications</th>
              <th>Typical Ready-Mix Price (2026)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ([2500, 3000, 3500, 4000, 5000] as $psi) : $g = $sd['concrete']['psi_grades'][$psi]; ?>
            <tr>
              <td><strong><?php echo esc_html(number_format($psi)); ?> PSI</strong></td>
              <td><?php echo esc_html($g['metric']); ?></td>
              <td><?php echo esc_html($g['use']); ?></td>
              <td><?php echo esc_html(Slab_Data::money_range(Slab_Data::psi_price($psi), 0, '–')); ?> / yd³</td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p>
        Prices follow one rule: <?php echo esc_html(number_format($sd['concrete']['psi_base'])); ?> PSI at <?php echo esc_html(Slab_Data::mr('concrete.psi_base_price', 0, '–')); ?> per yd³, with each <?php echo esc_html($sd['concrete']['psi_step']); ?>-PSI step adding <?php echo esc_html(Slab_Data::mr('concrete.psi_step_price', 0, '–')); ?> per yd³. The <a href="https://codes.iccsafe.org/" rel="noopener" target="_blank">International Residential Code (Table R402.2)</a> sets the minimums for homes: <?php echo esc_html(number_format($sd['code']['psi_protected'])); ?> PSI where concrete is protected from weather, <?php echo esc_html(number_format($sd['code']['psi_exposed'])); ?> PSI for exposed work, and <?php echo esc_html(number_format($sd['code']['psi_severe'])); ?> PSI, air-entrained (typically <?php echo esc_html(Slab_Data::r('concrete.air_entrainment_pct', '%–')); ?>% air), for garage floors, porches and steps in severe-weathering regions. Where de-icing salt is used, <?php echo esc_html(number_format($sd['code']['psi_deicing_recommended'])); ?> PSI is a common step up. Entrained air voids relieve the internal pressure of freezing water and prevent surface scaling. This calculator's default price (<?php echo esc_html(Slab_Data::money($sd['concrete']['ready_mix_default'])); ?>/yd³) is for a standard <?php echo esc_html(number_format($sd['concrete']['psi_base'])); ?> PSI residential mix; use the ready-mix price field to reflect your project's actual specified grade.
      </p>

      <h2>4. Waste Factor Analysis (+10% Standard)</h2>
      <p>
        Ordering exact theoretical volume invariably leads to shortfalls because excavations are never perfectly laser-level. We standardize waste recommendations according to subgrade conditions:
      </p>
      <ul>
        <li><strong>5% Waste:</strong> Smooth indoor basement floors or precision laser-leveled commercial forms.</li>
        <li><strong>10% Waste (Default Recommended):</strong> Standard residential backyard patios, driveways, and shed pads.</li>
        <li><strong>15%–20% Waste:</strong> Rough excavated earth, sloping bedrock, or high-spillage pump pours.</li>
      </ul>

      <h2>5. Crushed Stone Sub-Base &amp; Compaction Factors</h2>
      <p>
        When loose crushed stone (#57 or aggregate base course) is compacted with a vibratory plate compactor, its volume reduces by approximately <strong>12% to 15%</strong>. Our gravel calculators apply a <strong><?php echo esc_html(number_format($sd['site']['gravel_compaction'], 2)); ?> compaction multiplier</strong> (Gravel volume = Area × Depth × <?php echo esc_html(number_format($sd['site']['gravel_compaction'], 2)); ?>) to ensure sufficient material is delivered.
      </p>

      <h2>6. Steel Reinforcement Lap-Splice Multipliers</h2>
      <p>
        For slab-on-grade reinforcement we use the common residential rule of lapping bars <strong><?php echo esc_html($sd['reinforcement']['lap_bar_diameters']); ?> bar diameters</strong>, which is <?php echo esc_html($sd['reinforcement']['lap_in_no4']); ?> inches for #4 rebar. Structural members designed to ACI 318 can need longer tension splices, so follow an engineer's drawings where they exist. Our rebar estimator and slab blueprint add a <strong><?php echo esc_html(round($sd['reinforcement']['splice_allowance'] * 100)); ?>% linear footage allowance</strong> for laps and offcuts, keep bars <?php echo esc_html($sd['reinforcement']['edge_cover_in']); ?> inches in from the slab edge, and place support chairs about <?php echo esc_html(Slab_Data::r('reinforcement.chair_grid_ft')); ?> feet apart.
      </p>

      <h2>7. Control Joints, Saw-Cut Timing &amp; Weather Limits</h2>
      <p>
        Control joints are spaced <?php echo esc_html(Slab_Data::r('joints.ratio')); ?> times the slab thickness in inches, expressed in feet (<?php echo esc_html(Slab_Data::r('joints.ratio_best')); ?> times gives the best crack control), cut to one quarter of the slab depth, with panels no more than <?php echo esc_html(Slab_Data::num($sd['joints']['max_aspect'])); ?> times longer than wide. The slab blueprint uses <?php echo esc_html(Slab_Data::num($sd['joints']['ratio_blueprint'])); ?> times the thickness, capped at <?php echo esc_html($sd['joints']['cap_ft']); ?> ft. Saw cuts go in <?php echo esc_html(Slab_Data::r('joints.sawcut_h')); ?> hours after finishing with a conventional saw, or <?php echo esc_html(Slab_Data::r('joints.early_entry_h')); ?> hours with an early-entry saw (<a href="https://www.concrete.org/" rel="noopener" target="_blank">ACI 302.1R</a>).
      </p>
      <p>
        The pour-day weather check flags cold-weather practice at or below <?php echo esc_html($sd['weather']['cold_f']); ?>°F (<a href="https://www.concrete.org/" rel="noopener" target="_blank">ACI 306R</a>), a cool-weather notice below <?php echo esc_html($sd['weather']['cool_f']); ?>°F, and hot-weather precautions above <?php echo esc_html($sd['weather']['hot_f']); ?>°F air temperature. <a href="https://www.concrete.org/" rel="noopener" target="_blank">ACI 305R</a> sets no single hot-weather temperature; many specifications cap fresh concrete at <?php echo esc_html(Slab_Data::r('weather.concrete_temp_limit_f')); ?>°F, and an evaporation rate above <?php echo esc_html($sd['weather']['evap_limit_lb']); ?> lb/ft²/h is the level where plastic shrinkage cracking becomes likely.
      </p>

      <h2>8. How Size and Per-Square-Foot Costs Are Calculated</h2>
      <p>
        Every cost table, FAQ and guide on this site is computed from the same line items and default rates as the calculator, so they cannot disagree with it. <?php echo esc_html(Slab_Data::cost_basis_note()); ?> The high end equals the top of the Bid Checker's "fair quote" band.
      </p>

      <h2>9. Pricing Data Sourcing &amp; Regional Indexing</h2>
      <p>
        Default prices are editorial estimates of typical U.S. prices, kept in one data file that the calculator and every page read. They are not averages from a survey of contractors or suppliers. Picking a location applies an approximate location factor (U.S. average = 1.00) to ready-mix, excavation and labor. Every rate can be changed in the calculator, because local prices vary. All default prices and location factors are listed in the <a href="<?php echo esc_url(home_url('/concrete-price-report/')); ?>">2026 concrete price report</a> and the <a href="<?php echo esc_url(home_url('/concrete-slab-cost-by-state/')); ?>">state price table</a>.
      </p>

      <h2>10. Platform Limitations &amp; Safety Disclaimers</h2>
      <div class="callout callout-warning">
        <div class="callout-title">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h20.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          Boundary Conditions of Estimating Tools
        </div>
        <p>
          This calculator platform provides budgeting, purchasing, and material estimates. It is not an automated structural engineering tool and cannot evaluate soil bearing capacities ($q_a$), seismic moment resistance, lateral overturning loads, or post-tensioned cable prestressing. Always verify structural plans with a licensed professional engineer (PE) where local building codes require stamped engineering drawings.
        </p>
      </div>

      <h2>11. Sources &amp; Standards</h2>
      <ul>
        <li><a href="https://www.concrete.org/" rel="noopener" target="_blank">ACI 302.1R, Guide for Concrete Floor and Slab Construction</a> (joint spacing, saw-cut timing, slab finishing).</li>
        <li><a href="https://www.concrete.org/" rel="noopener" target="_blank">ACI 305R, Guide to Hot Weather Concreting</a> (evaporation rate, hot-weather precautions).</li>
        <li><a href="https://www.concrete.org/" rel="noopener" target="_blank">ACI 306R, Guide to Cold Weather Concreting</a> (40°F threshold, protection until about 500 psi).</li>
        <li><a href="https://www.cement.org/" rel="noopener" target="_blank">Portland Cement Association (PCA, now the American Cement Association)</a> (curing, air entrainment, mix basics).</li>
        <li><a href="https://codes.iccsafe.org/" rel="noopener" target="_blank">International Residential Code, Chapter 4 (Table R402.2, R403.1.4.1)</a> (minimum concrete strength, frost protection exemptions).</li>
        <li><a href="https://www.astm.org/" rel="noopener" target="_blank">ASTM C94, Standard Specification for Ready-Mixed Concrete</a> (how ready-mix is batched and delivered).</li>
      </ul>

      <h2>12. Related Reading in the Knowledgebase</h2>
      <p>
        These calculators and formulas connect directly to several in-depth guides in our <a href="<?php echo esc_url(home_url('/knowledgebase/')); ?>">Knowledgebase</a>:
      </p>
      <ul>
        <li><a href="<?php echo esc_url(home_url('/concrete-curing-time-guide/')); ?>">Concrete Curing Time Guide</a> &mdash; the day-by-day strength timeline referenced by the 28-day design strength used throughout this page.</li>
        <li><a href="<?php echo esc_url(home_url('/best-time-of-year-to-pour-concrete/')); ?>">Best Time of Year to Pour Concrete</a> &mdash; the ACI 306/305 cold- and hot-weather thresholds behind our waste factor and scheduling guidance.</li>
        <li><a href="<?php echo esc_url(home_url('/slab-vs-crawl-space-foundation/')); ?>">Slab-on-Grade vs. Crawl Space Foundation</a> &mdash; how the thickened-edge footing design in Section 1 compares to an alternative foundation type.</li>
        <li><a href="<?php echo esc_url(home_url('/yard-prep-checklist-for-a-slab-pour/')); ?>">Yard Prep Checklist</a> &mdash; the sub-base compaction and gravel base steps that this methodology's material formulas assume are done correctly.</li>
      </ul>

      <?php get_template_part('template-parts/author-bio'); ?>

    </article>
  </div>
</main>

<?php
get_footer();
