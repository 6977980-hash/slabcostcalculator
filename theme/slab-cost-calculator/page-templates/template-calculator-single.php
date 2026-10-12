<?php
/**
 * Template Name: Single Supporting Calculator
 *
 * @package SlabCostCalculator
 */

get_header();
slab_render_breadcrumbs();

// Determine calculator context based on slug
global $post;
$slug = $post ? $post->post_name : get_query_var('slab_slug');
?>

<main id="primary" class="site-main">
  <div class="container">
    <div class="content-grid">
      <article class="article-content">
        
        <header class="page-header" style="margin-bottom: 2rem;">
          <h1 class="hero-title"><?php the_title(); ?></h1>
          <p class="hero-subtitle">
            Specialized engineering calculation tool for accurate material volume estimating, contractor planning, and building code compliance.
          </p>
        </header>

        <!-- Dynamic Specialized Calculator Component -->
        <?php if (strpos($slug, 'volume') !== false || strpos($slug, 'yard') !== false) : ?>
          <!-- Subcalc: Volume / Yards -->
          <div id="subcalc-volume" class="subcalc-card" style="border: 2px solid var(--primary-600);">
            <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Volume &amp; Cubic Yard Calculator</h2>
            <div class="input-grid-3">
              <div class="calc-form-group">
                <label for="vol-length" class="calc-label">Length (ft)</label>
                <input type="number" id="vol-length" class="calc-control" value="20" step="0.5">
              </div>
              <div class="calc-form-group">
                <label for="vol-width" class="calc-label">Width (ft)</label>
                <input type="number" id="vol-width" class="calc-control" value="20" step="0.5">
              </div>
              <div class="calc-form-group">
                <label for="vol-thick" class="calc-label">Thickness (inches)</label>
                <input type="number" id="vol-thick" class="calc-control" value="4" step="0.5">
              </div>
            </div>

            <div class="quantities-grid" style="margin-top: 1.25rem;">
              <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
                <div class="quantity-card-label">Volume (Cubic Yards)</div>
                <div id="vol-out-cuyd" class="quantity-card-value" style="color: var(--primary-800);">4.94 yd³</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Volume (Cubic Feet)</div>
                <div id="vol-out-cuft" class="quantity-card-value">133.33 ft³</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Volume (Cubic Meters)</div>
                <div id="vol-out-cum" class="quantity-card-value">3.77 m³</div>
              </div>
            </div>
          </div>

        <?php elseif (strpos($slug, 'bag') !== false) : ?>
          <!-- Subcalc: Bags -->
          <div id="subcalc-bags" class="subcalc-card" style="border: 2px solid var(--primary-600);">
            <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Concrete Bags Calculator</h2>
            <div class="input-grid-2">
              <div class="calc-form-group">
                <label for="bags-sqft" class="calc-label">Total Surface Area (sq ft)</label>
                <input type="number" id="bags-sqft" class="calc-control" value="100" step="5">
              </div>
              <div class="calc-form-group">
                <label for="bags-thick" class="calc-label">Thickness (inches)</label>
                <input type="number" id="bags-thick" class="calc-control" value="4" step="0.5">
              </div>
            </div>
            <div class="input-grid-3">
              <div class="calc-form-group">
                <label for="bags-size" class="calc-label">Bag Size</label>
                <select id="bags-size" class="calc-control">
                  <option value="80" selected>80 lb Bag (0.60 cu ft yield)</option>
                  <option value="60">60 lb Bag (0.45 cu ft yield)</option>
                  <option value="50">50 lb Bag (0.375 cu ft yield)</option>
                  <option value="40">40 lb Bag (0.30 cu ft yield)</option>
                </select>
              </div>
              <div class="calc-form-group">
                <label for="bags-waste" class="calc-label">Waste Allowance (%)</label>
                <input type="number" id="bags-waste" class="calc-control" value="10" step="1">
              </div>
              <div class="calc-form-group">
                <label for="bags-price" class="calc-label">Price / Bag ($)</label>
                <input type="number" id="bags-price" class="calc-control" value="6.50" step="0.25">
              </div>
            </div>

            <div class="quantities-grid" style="margin-top: 1.25rem;">
              <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
                <div class="quantity-card-label">Total Bags Required</div>
                <div id="bags-out-total" class="quantity-card-value" style="color: var(--primary-800);">62 bags</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Estimated Material Cost</div>
                <div id="bags-out-cost" class="quantity-card-value">$403.00</div>
              </div>
            </div>
          </div>

        <?php elseif (strpos($slug, 'rebar') !== false || strpos($slug, 'mesh') !== false) : ?>
          <!-- Subcalc: Rebar -->
          <div id="subcalc-rebar" class="subcalc-card" style="border: 2px solid var(--primary-600);">
            <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Concrete Rebar &amp; Reinforcement Estimator</h2>
            <div class="input-grid-2">
              <div class="calc-form-group">
                <label for="rebar-length" class="calc-label">Slab Length (ft)</label>
                <input type="number" id="rebar-length" class="calc-control" value="20" step="1">
              </div>
              <div class="calc-form-group">
                <label for="rebar-width" class="calc-label">Slab Width (ft)</label>
                <input type="number" id="rebar-width" class="calc-control" value="20" step="1">
              </div>
            </div>
            <div class="input-grid-2">
              <div class="calc-form-group">
                <label for="rebar-spacing" class="calc-label">Grid Center Spacing</label>
                <select id="rebar-spacing" class="calc-control">
                  <option value="12">12 inches (Heavy Duty)</option>
                  <option value="18" selected>18 inches (Standard Residential)</option>
                  <option value="24">24 inches (Light Duty)</option>
                </select>
              </div>
              <div class="calc-form-group">
                <label for="rebar-bar-length" class="calc-label">Rebar Stick Length</label>
                <select id="rebar-bar-length" class="calc-control">
                  <option value="20" selected>20 ft standard stick</option>
                  <option value="10">10 ft pre-cut stick</option>
                </select>
              </div>
            </div>

            <div class="quantities-grid" style="margin-top: 1.25rem;">
              <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
                <div class="quantity-card-label">Total Linear Footage (w/ 10% Splice)</div>
                <div id="rebar-out-linft" class="quantity-card-value" style="color: var(--primary-800);">616 lin ft</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Total Rebar Sticks</div>
                <div id="rebar-out-pieces" class="quantity-card-value">31 sticks (20ft)</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Support Chairs Needed</div>
                <div id="rebar-out-chairs" class="quantity-card-value">49 chairs</div>
              </div>
            </div>
          </div>

        <?php elseif (strpos($slug, 'gravel') !== false) : ?>
          <!-- Subcalc: Gravel -->
          <div id="subcalc-gravel" class="subcalc-card" style="border: 2px solid var(--primary-600);">
            <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Gravel Base Calculator</h2>
            <div class="input-grid-2">
              <div class="calc-form-group">
                <label for="gravel-sqft" class="calc-label">Surface Area (sq ft)</label>
                <input type="number" id="gravel-sqft" class="calc-control" value="400" step="10">
              </div>
              <div class="calc-form-group">
                <label for="gravel-depth" class="calc-label">Gravel Depth (inches)</label>
                <input type="number" id="gravel-depth" class="calc-control" value="4" step="1">
              </div>
            </div>

            <div class="quantities-grid" style="margin-top: 1.25rem;">
              <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
                <div class="quantity-card-label">Crushed Gravel Volume (w/ 15% Compaction)</div>
                <div id="gravel-out-yards" class="quantity-card-value" style="color: var(--primary-800);">5.68 cubic yards</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Total Mass (Tons)</div>
                <div id="gravel-out-tons" class="quantity-card-value">7.95 tons</div>
              </div>
            </div>
          </div>

        <?php elseif (strpos($slug, 'footing') !== false) : ?>
          <!-- Subcalc: Footing & Pier -->
          <div id="subcalc-footing" class="subcalc-card" style="border: 2px solid var(--primary-600);">
            <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Concrete Footing &amp; Pier Calculator</h2>
            <div class="input-grid-2">
              <div class="calc-form-group">
                <label for="footing-type" class="calc-label">Footing Type</label>
                <select id="footing-type" class="calc-control">
                  <option value="continuous" selected>Continuous Strip Footing</option>
                  <option value="pier">Round Pier / Sonotube</option>
                </select>
              </div>
              <div class="calc-form-group">
                <label for="footing-qty" class="calc-label">Quantity</label>
                <input type="number" id="footing-qty" class="calc-control" value="1" min="1" step="1">
              </div>
            </div>
            <div class="input-grid-3">
              <div class="calc-form-group">
                <label for="footing-length" class="calc-label">Run Length (ft) <span style="font-weight:400;">(strip only)</span></label>
                <input type="number" id="footing-length" class="calc-control" value="80" step="1">
              </div>
              <div class="calc-form-group">
                <label for="footing-width" class="calc-label">Width / Diameter (inches)</label>
                <input type="number" id="footing-width" class="calc-control" value="12" step="1">
              </div>
              <div class="calc-form-group">
                <label for="footing-depth" class="calc-label">Depth / Height (inches)</label>
                <input type="number" id="footing-depth" class="calc-control" value="12" step="1">
              </div>
            </div>
            <div class="calc-form-group">
              <label for="footing-waste" class="calc-label">Waste Allowance</label>
              <select id="footing-waste" class="calc-control">
                <option value="0">0% (Exact)</option>
                <option value="5">5% (Simple)</option>
                <option value="10" selected>10% (Recommended)</option>
                <option value="15">15% (Uneven Grade)</option>
                <option value="20">20% (Excavated/Rough)</option>
              </select>
            </div>

            <div class="quantities-grid" style="margin-top: 1.25rem;">
              <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
                <div class="quantity-card-label">Concrete Needed (w/ waste)</div>
                <div id="footing-out-yards" class="quantity-card-value" style="color: var(--primary-800);">3.26 cu. yds</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Or Bagged Equivalent</div>
                <div id="footing-out-bags" class="quantity-card-value">147 bags (80 lb)</div>
              </div>
            </div>
          </div>

        <?php elseif (strpos($slug, 'weight') !== false) : ?>
          <!-- Subcalc: Weight -->
          <div id="subcalc-weight" class="subcalc-card" style="border: 2px solid var(--primary-600);">
            <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Concrete Weight Calculator</h2>
            <div class="input-grid-3">
              <div class="calc-form-group">
                <label for="weight-length" class="calc-label">Length (ft)</label>
                <input type="number" id="weight-length" class="calc-control" value="20" step="0.5">
              </div>
              <div class="calc-form-group">
                <label for="weight-width" class="calc-label">Width (ft)</label>
                <input type="number" id="weight-width" class="calc-control" value="20" step="0.5">
              </div>
              <div class="calc-form-group">
                <label for="weight-thick" class="calc-label">Thickness (inches)</label>
                <input type="number" id="weight-thick" class="calc-control" value="4" step="0.5">
              </div>
            </div>

            <div class="quantities-grid" style="margin-top: 1.25rem;">
              <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
                <div class="quantity-card-label">Wet Weight (As Poured)</div>
                <div id="weight-out-wet-lbs" class="quantity-card-value" style="color: var(--primary-800);">19,333 lbs</div>
                <div id="weight-out-wet-tons" class="quantity-card-note">9.67 US tons</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Cured (Dry) Weight</div>
                <div id="weight-out-dry-lbs" class="quantity-card-value">18,753 lbs (~cured)</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Metric Equivalent</div>
                <div id="weight-out-kg" class="quantity-card-value">8,769 kg</div>
                <div id="weight-out-tonnes" class="quantity-card-note">8.77 metric tonnes</div>
              </div>
            </div>
            <p style="font-size: 0.8125rem; color: var(--slate-500); margin-top: 0.75rem;">
              Based on normal-weight concrete at ~145 lb/cu ft. Cured (dry) weight is an approximation reflecting typical bleed-water loss during the 28-day cure, not a mix-specific lab figure.
            </p>
          </div>

        <?php elseif (strpos($slug, 'removal') !== false) : ?>
          <!-- Subcalc: Demolition / Removal -->
          <div id="subcalc-removal" class="subcalc-card" style="border: 2px solid var(--primary-600);">
            <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Concrete Removal &amp; Demolition Calculator</h2>
            <div class="input-grid-2">
              <div class="calc-form-group">
                <label for="demo-sqft" class="calc-label">Demolition Area (sq ft)</label>
                <input type="number" id="demo-sqft" class="calc-control" value="400" step="10">
              </div>
              <div class="calc-form-group">
                <label for="demo-thick" class="calc-label">Existing Slab Thickness (inches)</label>
                <input type="number" id="demo-thick" class="calc-control" value="4" step="1">
              </div>
            </div>
            <div class="calc-form-group">
              <label class="calc-label"><input type="checkbox" id="demo-rebar" checked> Existing slab has steel rebar reinforcement (Harder to break)</label>
            </div>

            <div class="quantities-grid" style="margin-top: 1.25rem;">
              <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
                <div class="quantity-card-label">Estimated Removal &amp; Disposal Cost</div>
                <div id="demo-out-cost" class="quantity-card-value" style="color: var(--primary-800);">$1,700.00 ($4.25/sq ft)</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Estimated Debris Tonnage</div>
                <div id="demo-out-weight" class="quantity-card-value">9.7 tons</div>
              </div>
            </div>
          </div>

        <?php elseif (strpos($slug, 'labor') !== false) : ?>
          <!-- Subcalc: Labor -->
          <div id="subcalc-labor" class="subcalc-card" style="border: 2px solid var(--primary-600);">
            <h2 style="font-size: 1.25rem; margin-top: 0; border: none;">Concrete Labor Cost Calculator</h2>
            <div class="input-grid-2">
              <div class="calc-form-group">
                <label for="labor-sqft" class="calc-label">Slab Area (sq ft)</label>
                <input type="number" id="labor-sqft" class="calc-control" value="400" step="10">
              </div>
              <div class="calc-form-group">
                <label for="labor-complexity" class="calc-label">Finish Complexity</label>
                <select id="labor-complexity" class="calc-control">
                  <option value="standard" selected>Standard Broom Finish ($5.00/sq ft)</option>
                  <option value="stamp">Stamped / Decorative ($10.00/sq ft)</option>
                  <option value="difficult">Difficult Access / Site ($7.50/sq ft)</option>
                </select>
              </div>
            </div>

            <div class="quantities-grid" style="margin-top: 1.25rem;">
              <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
                <div class="quantity-card-label">Estimated Labor Cost</div>
                <div id="labor-out-cost" class="quantity-card-value" style="color: var(--primary-800);">$2,000.00 ($5.00/sq ft)</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Estimated Crew Hours</div>
                <div id="labor-out-hours" class="quantity-card-value">~14 total crew hours</div>
              </div>
            </div>
          </div>

        <?php else : ?>
          <!-- Default to Embedded Primary Calculator -->
          <?php get_template_part('template-parts/calculator-primary'); ?>
        <?php endif; ?>

        <!-- Detailed Technical Explanatory Content -->
        <h2>How This Calculation Works (Engineering Principles)</h2>
        <p>
          Accurate flatwork estimates prevent expensive concrete shortages ("cold joints") and avoid over-ordering charges. All volume calculations utilize standard civil engineering formulas:
        </p>

        <div class="formula-box">
          <div class="formula-label">Core Formula</div>
          <div class="formula-math">Volume = Length × Width × (Thickness ÷ 12) ÷ 27</div>
          <div class="formula-explanation">
            Result in cubic yards (yd³). Always apply a 10% safety buffer to account for subgrade ground fluctuations and form deflection.
          </div>
        </div>

        <h2>Need a Full Project Cost Estimate?</h2>
        <p>
          While this tool calculates individual material quantities, our <a href="<?php echo esc_url(home_url('/')); ?>"><strong>Concrete Slab Cost Calculator</strong></a> provides a full budget breakdown including ready-mix concrete, gravel base, formwork lumber, vapor retarders, rebar grid, finishing labor, truck delivery, and contractor overhead.
        </p>

        <div style="margin: 2rem 0; text-align: center;">
          <a href="<?php echo esc_url(home_url('/')); ?>#concrete-slab-calculator" class="header-cta-btn" style="padding: 0.85rem 1.75rem; font-size: 1rem;">
            Launch Full Concrete Slab Project Estimator &rarr;
          </a>
        </div>

        <?php get_template_part('template-parts/calculator-faq'); ?>

        <?php get_template_part('template-parts/author-bio'); ?>

      </article>

      <!-- Sidebar -->
      <aside class="sidebar-col">
        <div class="sidebar-widget">
          <div class="widget-title">Other Calculators</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/')); ?>">Primary Slab Cost Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-volume-calculator/')); ?>">Volume Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-bags-calculator/')); ?>">Bags Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-weight-calculator/')); ?>">Weight Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-cost-per-square-foot-calculator/')); ?>">Cost Per Sq Ft Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-rebar-calculator/')); ?>">Rebar Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/gravel-base-calculator/')); ?>">Gravel Base Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-footing-calculator/')); ?>">Footing &amp; Pier Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-labor-cost-calculator/')); ?>">Labor Cost Calculator</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-removal-cost-calculator/')); ?>">Demolition Calculator</a></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
