<?php
/**
 * Template Name: Single Supporting Calculator
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
slab_render_breadcrumbs();

// Determine calculator context based on slug
global $post;
$slug = $post ? $post->post_name : get_query_var('slab_slug');

// Placeholder results shown before the JS runs, computed from the same
// figures (inc/site-data.php) the calculators use.
$sd = Slab_Data::all();
$bags_demo = Slab_Data::estimate(10, 10, 4);
$grid_demo = Slab_Data::rebar_grid(20, 20, 18);
$lab = $sd['labor'];

// Which tool this page shows, and its own text (inc/calculator-pages.php).
$tool = Slab_Calculator_Pages::tool($slug);
$calc_page = Slab_Calculator_Pages::get($slug);
if (!$tool) {
    // Unknown slug (a page added later in wp-admin): guess from the slug.
    foreach (['volume' => 'volume', 'yard' => 'yards', 'bag' => 'bags', 'mesh' => 'mesh', 'rebar' => 'rebar', 'gravel' => 'gravel', 'footing' => 'footing', 'weight' => 'weight', 'removal' => 'removal', 'labor' => 'labor'] as $needle => $guess) {
        if (strpos((string) $slug, $needle) !== false) {
            $tool = $guess;
            break;
        }
    }
    $tool = $tool ?: 'primary';
}
$vol_demo = 400 * 4 / 12;
$foot_demo = 80 * 1 * 1 * (1 + $sd['concrete']['waste_pct'] / 100);
$wt_demo = $vol_demo * $sd['concrete']['density_lb_ft3'];
?>

<main id="primary" class="site-main">
  <div class="container">
    <div class="content-grid">
      <article class="article-content">
        
        <header class="page-header" style="margin-bottom: 2rem;">
          <h1 class="hero-title"><?php the_title(); ?></h1>
          <?php if ($calc_page) : ?>
            <p class="hero-subtitle"><?php echo esc_html($calc_page['subtitle']); ?></p>
            <p class="hero-answer"><?php echo wp_kses_post($calc_page['answer']); ?></p>
          <?php else : ?>
            <p class="hero-subtitle">Concrete estimating tool with the formula and assumptions shown below.</p>
          <?php endif; ?>
        </header>

        <!-- Dynamic Specialized Calculator Component -->
        <?php if ($tool === 'volume') : ?>
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
                <div id="vol-out-cuyd" class="quantity-card-value" style="color: var(--primary-800);"><?php echo esc_html(number_format($vol_demo / 27, 2)); ?> yd³</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Volume (Cubic Feet)</div>
                <div id="vol-out-cuft" class="quantity-card-value"><?php echo esc_html(number_format($vol_demo, 2)); ?> ft³</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Volume (Cubic Meters)</div>
                <div id="vol-out-cum" class="quantity-card-value"><?php echo esc_html(number_format($vol_demo * 0.0283168, 2)); ?> m³</div>
              </div>
            </div>
          </div>

        <?php elseif ($tool === 'bags') : ?>
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
                <input type="number" id="bags-waste" class="calc-control" value="<?php echo esc_attr($sd['concrete']['waste_pct']); ?>" step="1">
              </div>
              <div class="calc-form-group">
                <label for="bags-price" class="calc-label">Price / Bag ($)</label>
                <input type="number" id="bags-price" class="calc-control" value="<?php echo esc_attr(number_format($sd['concrete']['bag_price_default'], 2)); ?>" step="0.25">
              </div>
            </div>

            <div class="quantities-grid" style="margin-top: 1.25rem;">
              <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
                <div class="quantity-card-label">Total Bags Required</div>
                <div id="bags-out-total" class="quantity-card-value" style="color: var(--primary-800);"><?php echo esc_html($bags_demo['bags80']); ?> bags</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Estimated Material Cost</div>
                <div id="bags-out-cost" class="quantity-card-value"><?php echo esc_html(Slab_Data::money($bags_demo['bagged'], 2)); ?></div>
              </div>
            </div>
          </div>

        <?php elseif ($tool === 'rebar') : ?>
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
                <div class="quantity-card-label">Total Linear Footage (w/ <?php echo esc_html(round($sd['reinforcement']['splice_allowance'] * 100)); ?>% Splice)</div>
                <div id="rebar-out-linft" class="quantity-card-value" style="color: var(--primary-800);"><?php echo esc_html(round($grid_demo['lin_ft'])); ?> linear ft</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Total Rebar Sticks</div>
                <div id="rebar-out-pieces" class="quantity-card-value"><?php echo esc_html($grid_demo['sticks']); ?> bars (20ft sticks)</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Support Chairs Needed</div>
                <div id="rebar-out-chairs" class="quantity-card-value"><?php echo esc_html($grid_demo['chairs']); ?> rebar chairs</div>
              </div>
            </div>
          </div>

        <?php elseif ($tool === 'gravel') : ?>
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
                <div id="gravel-out-yards" class="quantity-card-value" style="color: var(--primary-800);"><?php echo esc_html(number_format(400 * $sd['site']['gravel_depth_in'] / 12 / 27 * $sd['site']['gravel_compaction'], 2)); ?> cubic yards</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Total Mass (Tons)</div>
                <div id="gravel-out-tons" class="quantity-card-value"><?php echo esc_html(number_format(400 * $sd['site']['gravel_depth_in'] / 12 / 27 * $sd['site']['gravel_compaction'] * $sd['site']['gravel_tons_per_yd'], 2)); ?> tons crushed gravel</div>
              </div>
            </div>
          </div>

        <?php elseif ($tool === 'footing') : ?>
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
                <div id="footing-out-yards" class="quantity-card-value" style="color: var(--primary-800);"><?php echo esc_html(number_format($foot_demo / 27, 2)); ?> cu. yds</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Or Bagged Equivalent</div>
                <div id="footing-out-bags" class="quantity-card-value"><?php echo (int) Slab_Data::bags($foot_demo); ?> bags (80 lb)</div>
              </div>
            </div>
          </div>

        <?php elseif ($tool === 'weight') : ?>
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
                <div id="weight-out-wet-lbs" class="quantity-card-value" style="color: var(--primary-800);"><?php echo esc_html(number_format($wt_demo)); ?> lbs</div>
                <div id="weight-out-wet-tons" class="quantity-card-note"><?php echo esc_html(number_format($wt_demo / 2000, 2)); ?> US tons</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Cured (Dry) Weight</div>
                <div id="weight-out-dry-lbs" class="quantity-card-value"><?php echo esc_html(number_format($wt_demo * $sd['concrete']['cured_weight_factor'])); ?> lbs (~cured)</div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Metric Equivalent</div>
                <div id="weight-out-kg" class="quantity-card-value"><?php echo esc_html(number_format($wt_demo * 0.45359237)); ?> kg</div>
                <div id="weight-out-tonnes" class="quantity-card-note"><?php echo esc_html(number_format($wt_demo * 0.45359237 / 1000, 2)); ?> metric tonnes</div>
              </div>
            </div>
            <p style="font-size: 0.8125rem; color: var(--slate-500); margin-top: 0.75rem;">
              Based on normal-weight concrete at ~<?php echo (int) $sd['concrete']['density_lb_ft3']; ?> lb/cu ft. Cured (dry) weight is an approximation reflecting typical bleed-water loss during the 28-day cure, not a mix-specific lab figure.
            </p>
          </div>

        <?php elseif ($tool === 'removal') : ?>
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

        <?php elseif ($tool === 'labor') : ?>
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
                  <option value="standard" selected>Standard Broom Finish (<?php echo esc_html(Slab_Data::money($lab['sqft_default'], 2)); ?>/sq ft)</option>
                  <option value="stamp">Stamped / Decorative (<?php echo esc_html(Slab_Data::money($lab['sqft_stamped'], 2)); ?>/sq ft)</option>
                  <option value="difficult">Difficult Access / Site (<?php echo esc_html(Slab_Data::money($lab['sqft_difficult'], 2)); ?>/sq ft)</option>
                </select>
              </div>
            </div>

            <div class="quantities-grid" style="margin-top: 1.25rem;">
              <div class="quantity-card" style="background: var(--primary-50); border-color: var(--primary-200);">
                <div class="quantity-card-label">Estimated Labor Cost</div>
                <div id="labor-out-cost" class="quantity-card-value" style="color: var(--primary-800);"><?php echo esc_html(Slab_Data::money(400 * $lab['sqft_default'], 2) . ' (' . Slab_Data::money($lab['sqft_default'], 2) . '/sq ft)'); ?></div>
              </div>
              <div class="quantity-card">
                <div class="quantity-card-label">Estimated Crew Hours</div>
                <div id="labor-out-hours" class="quantity-card-value">~<?php echo esc_html(round(400 * $lab['crew_hours_per_sqft'])); ?> total crew hours</div>
              </div>
            </div>
          </div>

        <?php elseif (in_array($tool, ['yards', 'mix', 'readymix', 'waste', 'mesh', 'forms'], true)) : ?>
          <?php get_template_part('template-parts/tools/' . $tool); ?>

        <?php else : ?>
          <!-- Default to Embedded Primary Calculator -->
          <?php get_template_part('template-parts/calculator-primary'); ?>
        <?php endif; ?>

        <?php if ($calc_page) : ?>
          <?php foreach ($calc_page['sections'] as $section) : ?>
            <h2><?php echo esc_html($section[0]); ?></h2>
            <?php echo wp_kses_post($section[1]); ?>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($tool !== 'primary') : ?>
          <h2>Need a Full Project Cost Estimate?</h2>
          <p>
            This tool works out one part of the job. Our <a href="<?php echo esc_url(home_url('/')); ?>"><strong>Concrete Slab Cost Calculator</strong></a> prices the whole slab: concrete, gravel base, forms, vapor barrier, reinforcement, finish, labor and delivery.
          </p>
          <div style="margin: 2rem 0; text-align: center;">
            <a href="<?php echo esc_url(home_url('/')); ?>#concrete-slab-calculator" class="header-cta-btn" style="padding: 0.85rem 1.75rem; font-size: 1rem;">
              Open the Slab Cost Calculator &rarr;
            </a>
          </div>
        <?php endif; ?>

        <?php get_template_part('template-parts/calculator-faq'); ?>

        <?php get_template_part('template-parts/author-bio'); ?>

      </article>

      <!-- Sidebar -->
      <aside class="sidebar-col">
        <div class="sidebar-widget">
          <div class="widget-title">Other Calculators</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/')); ?>">Slab Cost Calculator</a></li>
            <?php foreach (Slab_Calculator_Pages::directory() as $calc_slug => $calc_info) : if ($calc_slug === $slug) continue; ?>
              <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/' . $calc_slug . '/')); ?>"><?php echo esc_html($calc_info[1]); ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php
get_footer();
