<?php
/**
 * FRONT PAGE TEMPLATE - MASTER CONCRETE SLAB ESTIMATING HUB
 *
 * @package SlabCostCalculator
 */

get_header();
?>

<!-- Hero Section with Concise Value Proposition -->
<section class="hero-section">
  <div class="container">
    <h1 class="hero-title">Concrete Slab Cost Calculator</h1>
    <p class="hero-subtitle">
      Calculate exact concrete volume in cubic yards and bags, compare ready-mix vs. bagged pricing, and estimate full project costs including site prep, gravel base, rebar reinforcement, and finishing labor.
    </p>

    <!-- Key Engineering Metrics Bar -->
    <div class="hero-key-takeaways">
      <div class="takeaway-card">
        <div class="takeaway-label">Avg. Installed Cost</div>
        <div class="takeaway-value">$6.00 – $12.00 / sq. ft.</div>
      </div>
      <div class="takeaway-card">
        <div class="takeaway-label">Ready-Mix Concrete</div>
        <div class="takeaway-value">$135 – $175 / cubic yard</div>
      </div>
      <div class="takeaway-card">
        <div class="takeaway-label">Standard Thickness</div>
        <div class="takeaway-value">4" (Patio) | 5"-6" (Driveway)</div>
      </div>
      <div class="takeaway-card">
        <div class="takeaway-label">Standard Waste Factor</div>
        <div class="takeaway-value">+10% Recommended</div>
      </div>
    </div>

    <!-- Trust & E-E-A-T Status Bar -->
    <div class="trust-bar">
      <div class="trust-item">
        <span class="trust-badge">&#10003; ACI 302.1R Compliant</span>
      </div>
      <div class="trust-item">
        <strong>Last Updated:</strong> <span><?php echo date('F Y'); ?></span>
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
  </div>
</section>

<!-- Deep Authoritative Content Architecture -->
<main id="primary" class="site-main">
  <div class="container">
    <div class="content-grid">
      <!-- Main Content Column -->
      <article class="article-content">
        
        <h2>How Much Does a Concrete Slab Cost in 2026?</h2>
        <p>
          On average, pouring a residential concrete slab costs between <strong>$6.00 and $12.00 per square foot</strong> for professional installation. For a standard 4-inch thick slab measuring 400 square feet (20×20 ft), the total installed cost typically ranges from <strong>$2,400 to $4,800</strong>. If you choose to self-perform the work (DIY mode) and purchase only the raw materials and tools, concrete slab costs typically range between <strong>$2.50 and $4.50 per square foot</strong>.
        </p>

        <div class="definition-card">
          <div class="definition-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            Key Cost Distinction: Material Cost vs. Total Installed Cost
          </div>
          <p>
            <strong>Material-only cost</strong> includes ready-mix concrete, gravel base, form boards, vapor barrier, and rebar ($2.50–$4.50/sq ft). <strong>Installed cost</strong> includes professional site excavation, grading, sub-base compaction, form layout, reinforcement tying, screeding, bull floating, power troweling, saw-cut control joints, and curing seals ($6.00–$12.00/sq ft).
          </p>
        </div>

        <!-- Slab Cross Section Diagram -->
        <figure style="margin: 2rem 0; text-align: center;">
          <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/slab-diagram.svg'); ?>" alt="Concrete slab dimensions showing length width and thickness with sub-base and rebar layers" style="border-radius: 12px; box-shadow: var(--shadow-md);" width="800" height="450">
          <figcaption style="font-size: 0.8125rem; color: var(--slate-500); margin-top: 0.5rem; font-style: italic;">
            Figure 1: Standard residential concrete slab structural cross-section showing subgrade, gravel base, vapor barrier, reinforcement, and formwork.
          </figcaption>
        </figure>

        <h2>What Goes Into a Concrete Slab Cost? (Comprehensive Line Items)</h2>
        <p>
          A realistic concrete slab estimate requires breaking down all physical components and labor stages. Below is a detailed inspection of each scope element:
        </p>

        <h3>1. Concrete Material (Ready-Mix or Bags)</h3>
        <p>
          Ready-mix concrete delivered by a transit truck averages <strong>$135 to $175 per cubic yard</strong> (3,000 to 4,000 PSI standard compressive mix). Bagged concrete mix (such as Quikrete or Sakrete) costs approximately <strong>$6.00 to $8.00 per 80-lb bag</strong>. Ready-mix is more economical for projects exceeding 1.5 to 2 cubic yards, while bagged concrete is ideal for small pads or remote locations where ready-mix trucks cannot access.
        </p>

        <h3>2. Site Excavation &amp; Grading</h3>
        <p>
          Preparing the ground involves removing grass, topsoil, and organic matter to a depth of 4 to 8 inches. Excavation costs typically run <strong>$1.00 to $2.50 per square foot</strong>, depending on root systems, slope, and soil density.
        </p>

        <h3>3. Compacted Gravel Sub-Base</h3>
        <p>
          A sturdy 4-inch base of crushed stone (such as #57 stone or crushed aggregate base) distributes vehicle loads, prevents frost heaving, and allows water drainage. Crushed gravel costs approximately <strong>$35 to $55 per cubic yard</strong> (or $25 to $40 per ton).
        </p>

        <h3>4. Perimeter Formwork Lumber</h3>
        <p>
          Wood formwork built with 2×4 or 2×6 dimensional lumber, wood stakes, and duplex nails holds the wet concrete in place during placement and curing. Form lumber and staking materials generally cost <strong>$1.50 to $3.00 per linear foot</strong> of slab perimeter.
        </p>

        <h3>5. Steel Reinforcement (Rebar vs. Wire Mesh)</h3>
        <p>
          Reinforcement absorbs tensile stresses and controls drying shrinkage cracking. Standard <strong>#4 (1/2") steel rebar</strong> tied on 12-inch to 18-inch grid centers costs approximately <strong>$1.00 to $1.75 per square foot</strong> including rebar support chairs. <strong>Welded wire mesh (6×6 W1.4/W2.9)</strong> costs approximately <strong>$0.40 to $0.70 per square foot</strong>.
        </p>

        <h3>6. Vapor Barrier Moisture Retarder</h3>
        <p>
          A heavy-duty 10-mil to 15-mil polyethylene vapor barrier placed beneath interior garage or enclosed shed slabs prevents ground moisture vapor from migrating through the cured concrete. Vapor barriers cost <strong>$0.25 to $0.45 per square foot</strong>.
        </p>

        <h3>7. Surface Finishing Technique</h3>
        <p>
          Finishing determines appearance and slip resistance:
        </p>
        <ul>
          <li><strong>Broom Finish:</strong> Standard textured exterior finish for walkways and driveways (included in standard labor or +$0.50–$0.80/sq ft).</li>
          <li><strong>Smooth Hard Trowel:</strong> Polished interior finish for workshops and garages (+$1.00–$1.50/sq ft).</li>
          <li><strong>Exposed Aggregate:</strong> Decorative stone surface (+$2.50–$4.50/sq ft).</li>
          <li><strong>Stamped &amp; Stained Concrete:</strong> Patterned brick/slate textures (+$6.00–$12.00/sq ft).</li>
        </ul>

        <h3>8. Concrete Finishing Labor</h3>
        <p>
          Experienced concrete finishers charge between <strong>$3.50 and $7.00 per square foot</strong> (or $40 to $75 per crew hour) for setup, pouring, screeding, floating, edging, control joint cutting, and applying curing compound.
        </p>

        <h3>9. Delivery Charges &amp; Pump Rental</h3>
        <p>
          Transit mixer suppliers often impose an environmental or short-load fee of <strong>$100 to $200</strong> on orders smaller than 5 to 6 cubic yards. If the concrete truck cannot park within 15 to 20 feet of the forms due to fences, landscaping, or steep terrain, a trailer line pump or boom pump truck must be rented for <strong>$600 to $1,200 per pour</strong>.
        </p>

        <h2>Concrete Slab Cost by Size (Reference Matrix)</h2>
        <p>
          Below are typical installed and DIY budget benchmarks across popular residential slab footprint dimensions at a standard 4-inch depth:
        </p>

        <?php get_template_part('template-parts/cost-tables'); ?>

        <h2>Concrete Slab Thickness Guide: 4" vs. 5" vs. 6"</h2>
        <p>
          Choosing the correct thickness is critical for structural load capacity and longevity. The chart below illustrates standard depth ratings:
        </p>

        <figure style="margin: 2rem 0; text-align: center;">
          <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/thickness-diagram.svg'); ?>" alt="Concrete slab thickness guide showing 4 inch patio 5 inch driveway and 6 inch heavy vehicle applications" style="border-radius: 12px; box-shadow: var(--shadow-md);" width="800" height="400">
        </figure>

        <ul>
          <li><strong>4-Inch Slab:</strong> Best for foot-traffic applications, including patios, sidewalks, trash bin pads, and light utility sheds (min 3,000 PSI).</li>
          <li><strong>5-Inch Slab:</strong> Ideal for standard residential driveways, 2-car garages, and parking pads supporting passenger sedans and light pickup trucks (3,500–4,000 PSI).</li>
          <li><strong>6-Inch Slab:</strong> Recommended for RV parking pads, heavy-duty workshops, commercial loading docks, and heavy multi-axle vehicle storage (4,000+ PSI with #4 rebar grid).</li>
        </ul>

        <h2>How to Calculate Concrete for a Slab (Mathematical Formulas)</h2>
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
          <li><strong>Convert to Cubic Yards:</strong> 133.33 cu. ft. ÷ 27 = <strong>4.94 cubic yards</strong>.</li>
          <li><strong>Apply 10% Waste Factor:</strong> 4.94 × 1.10 = <strong>5.43 cubic yards</strong> (Order 5.5 yards from your ready-mix supplier).</li>
        </ol>

        <h2>Ready-Mix vs. Bagged Concrete: Which is Better for Your Slab?</h2>
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
                <td>Slabs > 1.5 cubic yards (Patios, Driveways, Garages)</td>
                <td>Small repairs, post holes, pads < 1.0 cu yd (4x4, 6x6)</td>
              </tr>
              <tr>
                <td><strong>Cost Comparison</strong></td>
                <td>$135–$175/yd³ + delivery (~$5.00–$6.50/cu ft)</td>
                <td>$6.50/80-lb bag = $10.83/cu ft (~$290/cu yd)</td>
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

        <h2>Concrete Slab Reinforcement: Rebar Grid vs. Welded Wire Mesh</h2>
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

        <h2>Factors That Increase Concrete Slab Costs</h2>
        <p>
          When requesting contractor quotes, several jobsite variables can increase unit prices:
        </p>
        <ul>
          <li><strong>Restricted Site Access:</strong> If concrete trucks cannot back up directly to the slab forms, wheelbarrowing concrete or hiring a pump truck ($600–$1,200) increases labor and equipment expense.</li>
          <li><strong>Sloped or Uneven Ground:</strong> Sloped terrain requires additional excavation, retaining walls, or thicker edge forms.</li>
          <li><strong>Tree Root Removal &amp; Rock Excavation:</strong> Rocky soil or large root systems require mini-excavator equipment or jackhammers.</li>
          <li><strong>Permits &amp; Inspections:</strong> Municipal flatwork or building permits range from $50 to $350 depending on local jurisdiction.</li>
        </ul>

        <!-- Latest Estimating Guides & Cost Articles Section -->
        <section style="margin: 3.5rem 0;">
          <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; border-bottom: 2px solid var(--slate-200); padding-bottom: 0.75rem;">
            <div>
              <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: var(--primary-600);">Knowledgebase &amp; Articles</span>
              <h2 style="font-size: 1.875rem; font-weight: 800; margin: 0.25rem 0 0 0; border: none; padding: 0;">Concrete Estimating &amp; Cost Guides</h2>
            </div>
            <a href="<?php echo esc_url(home_url('/knowledgebase/')); ?>" style="font-weight: 700;">View All Guides &rarr;</a>
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem;">
            <?php
            $homepage_posts = new WP_Query([
                'post_type' => 'post',
                'posts_per_page' => 20,
                'post_status' => 'publish'
            ]);

            if ($homepage_posts->have_posts()) :
                while ($homepage_posts->have_posts()) : $homepage_posts->the_post();
            ?>
                <article class="subcalc-card" style="margin-bottom: 0; display: flex; flex-direction: column;">
                  <div style="font-size: 0.6875rem; font-weight: 800; text-transform: uppercase; color: var(--primary-600); margin-bottom: 0.4rem;">
                    <?php
                    $categories = get_the_category();
                    if (!empty($categories)) {
                        echo esc_html($categories[0]->name);
                    } else {
                        echo 'Concrete Guide';
                    }
                    ?>
                  </div>
                  <h3 style="font-size: 1.0625rem; font-weight: 800; line-height: 1.35; margin: 0 0 0.5rem 0;">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                  </h3>
                  <div style="font-size: 0.8125rem; color: var(--slate-600); line-height: 1.5; margin-bottom: 1rem; flex-grow: 1;">
                    <?php echo wp_trim_words(get_the_excerpt() ? get_the_excerpt() : get_the_content(), 16, '...'); ?>
                  </div>
                  <a href="<?php the_permalink(); ?>" style="font-weight: 700; font-size: 0.8125rem; margin-top: auto; display: inline-flex; align-items: center; gap: 0.3rem;">
                    Read Guide &rarr;
                  </a>
                </article>
            <?php
                endwhile;
                wp_reset_postdata();
            else :
            ?>
              <p>No articles published yet.</p>
            <?php endif; ?>
          </div>
        </section>

        <!-- FAQ Section with Schema Accordion -->
        <section class="faq-section" aria-label="Frequently Asked Questions">
          <h2>Frequently Asked Questions About Concrete Slab Costs</h2>

          <div class="faq-item">
            <button type="button" class="faq-question" aria-expanded="false">
              How much does a 10x10 concrete slab cost?
            </button>
            <div class="faq-answer">
              A 10x10 foot concrete slab (100 square feet) at standard 4-inch depth costs between <strong>$600 and $1,200</strong> professionally installed. Material-only DIY cost is approximately $250 to $400. It requires approximately 1.23 cubic yards of concrete or 23 bags of 80-lb pre-mix (including 10% waste).
            </div>
          </div>

          <div class="faq-item">
            <button type="button" class="faq-question" aria-expanded="false">
              How much does a 20x20 concrete slab cost?
            </button>
            <div class="faq-answer">
              A 20x20 foot slab (400 square feet) typically costs <strong>$2,400 to $4,800</strong> for a standard 4-inch patio or shed floor, and <strong>$2,800 to $5,400</strong> for a 5-inch or 6-inch garage floor with rebar reinforcement. It requires approximately 5.0 to 5.5 cubic yards of ready-mix concrete.
            </div>
          </div>

          <div class="faq-item">
            <button type="button" class="faq-question" aria-expanded="false">
              How many 80 lb bags of concrete do I need for a cubic yard?
            </button>
            <div class="faq-answer">
              It takes exactly <strong>45 bags of 80-lb concrete mix</strong> to equal one cubic yard (27 cubic feet). One 80-lb bag yields approximately 0.60 cubic feet of mixed wet concrete (27 ÷ 0.60 = 45 bags).
            </div>
          </div>

          <div class="faq-item">
            <button type="button" class="faq-question" aria-expanded="false">
              How long should I wait before driving or parking on a new concrete slab?
            </button>
            <div class="faq-answer">
              Foot traffic is safe after <strong>24 to 48 hours</strong>. Light passenger cars can park after <strong>7 days</strong> (when concrete achieves ~70% design strength). Heavy trucks, RVs, and heavy machinery should wait <strong>28 days</strong> until full compressive curing is reached.
            </div>
          </div>

          <div class="faq-item">
            <button type="button" class="faq-question" aria-expanded="false">
              Do I need a gravel base under a concrete slab?
            </button>
            <div class="faq-answer">
              Yes. A 4-inch to 6-inch compacted crushed stone or gravel base provides uniform subgrade support, facilitates water drainage away from the slab, and dramatically reduces cracking caused by soil settling or freeze-thaw frost heave.
            </div>
          </div>
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
          <div class="widget-title">Cost Breakdown Guides</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/10x10-concrete-slab-cost-breakdown/')); ?>">10x10 Slab Cost ($600–$1.2k)</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/12x12-concrete-slab-cost-breakdown/')); ?>">12x12 Slab Cost ($850–$1.7k)</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/20x20-concrete-slab-cost-breakdown/')); ?>">20x20 Slab Cost ($2.4k–$4.8k)</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/24x24-concrete-slab-cost-breakdown/')); ?>">24x24 Garage Slab ($3.5k–$6.9k)</a></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
