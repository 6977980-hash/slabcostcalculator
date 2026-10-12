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
        Concrete strength is specified by its 28-day compressive strength rating, measured in <strong>PSI (pounds per square inch)</strong> in the US — the imperial equivalent of the metric M-grade system (e.g., M15, M20, M25) used in regions on the metric standard. Per <strong>ACI 318</strong>, the required strength class depends on the application and its exposure to weather, freeze-thaw cycling, and structural load:
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
            <tr>
              <td><strong>2,500 PSI</strong></td>
              <td>~M15-M17</td>
              <td>Non-structural fill, sidewalks in mild climates</td>
              <td>$125–$145 / yd³</td>
            </tr>
            <tr>
              <td><strong>3,000 PSI</strong></td>
              <td>~M20</td>
              <td>Standard residential patios, walkways, shed pads</td>
              <td>$135–$155 / yd³</td>
            </tr>
            <tr>
              <td><strong>3,500 PSI</strong></td>
              <td>~M22-M25</td>
              <td>Driveways, standard garage floors</td>
              <td>$145–$165 / yd³</td>
            </tr>
            <tr>
              <td><strong>4,000 PSI</strong></td>
              <td>~M25-M28</td>
              <td>Freeze-thaw climates, air-entrained exterior flatwork, footings</td>
              <td>$155–$175 / yd³</td>
            </tr>
            <tr>
              <td><strong>5,000 PSI</strong></td>
              <td>~M30-M35</td>
              <td>Heavy-duty garage/shop floors, structural foundations</td>
              <td>$180–$215 / yd³</td>
            </tr>
          </tbody>
        </table>
      </div>
      <p>
        For freeze-thaw climates, ACI 318 also calls for <strong>air-entrainment</strong> (typically 5%–7% by volume) regardless of strength grade, since entrained air voids relieve the internal pressure of freezing water and prevent surface scaling. This calculator's default pricing assumes a standard 3,000-3,500 PSI residential mix; use the ready-mix price override to reflect your project's actual specified grade.
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
        When loose crushed stone (#57 or aggregate base course) is compacted with a vibratory plate compactor, its volume reduces by approximately <strong>12% to 15%</strong>. Our gravel calculators apply a <strong>1.15 compaction multiplier</strong> ($V_{\text{gravel}} = \text{Area} \times \text{Depth} \times 1.15$) to ensure sufficient material is delivered.
      </p>

      <h2>6. Steel Reinforcement Lap-Splice Multipliers</h2>
      <p>
        In accordance with <strong>ACI 318</strong> structural lap-splice guidelines, continuous rebar sticks must overlap each other by at least 30 bar diameters (approx. 15 inches for #4 rebar). Our rebar estimator incorporates a <strong>10% linear footage lap splice and waste allowance</strong>.
      </p>

      <h2>7. Pricing Data Sourcing &amp; Regional Indexing</h2>
      <p>
        Our default price points represent quarterly aggregated median values from nationwide ready-mix producers, building supply houses (Home Depot, Lowe's, White Cap), and regional concrete flatwork contractor bid sheets. We intentionally allow full user customization of every rate because local supply market dynamics vary.
      </p>

      <h2>8. Platform Limitations &amp; Safety Disclaimers</h2>
      <div class="callout callout-warning">
        <div class="callout-title">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h20.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          Boundary Conditions of Estimating Tools
        </div>
        <p>
          This calculator platform provides budgeting, purchasing, and material estimates. It is not an automated structural engineering tool and cannot evaluate soil bearing capacities ($q_a$), seismic moment resistance, lateral overturning loads, or post-tensioned cable prestressing. Always verify structural plans with a licensed professional engineer (PE) where local building codes require stamped engineering drawings.
        </p>
      </div>

      <h2>9. Related Reading in the Knowledgebase</h2>
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
