<?php
/**
 * PRIMARY CONCRETE SLAB COST CALCULATOR COMPONENT
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

// Default field values come from inc/site-data.php, the same figures the
// calculator JS (window.SLAB_DATA) and the page text use.
$sd = Slab_Data::all();
$v = function ($n, $dec = 2) {
    return esc_attr(number_format((float) $n, $dec, '.', ''));
};
$fin = $sd['finish'];

// Which Live Estimate tab is open first. The quote checker page opens on
// the bid check: get_template_part('template-parts/calculator-primary', null, ['tab' => 'bid']).
$tabs = [
    'blueprint' => 'Blueprint',
    'breakdown' => 'Breakdown',
    'bid'       => 'Bid Check',
    'weather'   => 'Weather',
];
$default_tab = (isset($args['tab']) && isset($tabs[$args['tab']])) ? $args['tab'] : 'blueprint';
?>

<div id="concrete-slab-calculator" class="calculator-app-wrapper" role="region" aria-label="Concrete Slab Cost Estimator">
  <!-- Calculator Top Header Bar -->
  <div class="calc-header">
    <div class="calc-header-title">
      <h2>Concrete Slab Cost &amp; Volume Estimator</h2>
      <p>Precision multi-layer project budgeting: Materials, Sub-Base, Reinforcement, Labor &amp; Equipment</p>
    </div>

    <!-- Unit System Toggle -->
    <div class="unit-toggle-group" role="group" aria-label="Unit System Selection">
      <button type="button" class="unit-btn active" data-unit="imperial" aria-pressed="true">Imperial (ft / in / yd³)</button>
      <button type="button" class="unit-btn" data-unit="metric" aria-pressed="false">Metric (m / cm / m³)</button>
    </div>
  </div>

  <div class="calc-body-grid">
    <!-- Left Column: Input Form Controls -->
    <div class="calc-inputs-col">
      <!-- Project Presets -->
      <div class="preset-selector-group">
        <div class="calc-section-label">Quick Project Presets</div>
        <div class="preset-pills-scroll" role="group" aria-label="Project Type Presets">
          <button type="button" class="preset-btn" data-preset="patio" aria-pressed="false">Patio (12x12)</button>
          <button type="button" class="preset-btn" data-preset="walkway" aria-pressed="false">Walkway (4x30)</button>
          <button type="button" class="preset-btn" data-preset="driveway" aria-pressed="false">Driveway (20x40)</button>
          <button type="button" class="preset-btn" data-preset="garage" aria-pressed="false">2-Car Garage (24x24)</button>
          <button type="button" class="preset-btn" data-preset="shed" aria-pressed="false">Shed Pad (10x10)</button>
          <button type="button" class="preset-btn" data-preset="foundation" aria-pressed="false">Foundation (30x40)</button>
        </div>
      </div>

      <!-- Regional Price Index -->
      <div class="calc-form-group region-selector-group">
        <label for="calc-region" class="calc-label">Project Location (Regional Price Index)</label>
        <select id="calc-region" class="calc-control">
          <option value="us" selected>U.S. National Average (1.00)</option>
        </select>
        <p id="calc-region-note" class="calc-field-note">Picking a location sets the ready-mix, excavation and labor rates below to that area's typical level. You can still type your own local prices afterwards.</p>
      </div>

      <!-- Basic Dimensions Section -->
      <div class="calc-section-label">1. Slab Dimensions &amp; Quantity</div>
      
      <div class="input-grid-2">
        <div class="calc-form-group">
          <label for="calc-length" class="calc-label">Length (<span class="unit-len-label">ft</span>)</label>
          <div class="calc-input-wrapper has-addon-right">
            <input type="number" id="calc-length" class="calc-control" value="20" min="0.1" step="0.5" required>
            <span class="calc-input-addon-right unit-len-label">ft</span>
          </div>
        </div>

        <div class="calc-form-group">
          <label for="calc-width" class="calc-label">Width (<span class="unit-len-label">ft</span>)</label>
          <div class="calc-input-wrapper has-addon-right">
            <input type="number" id="calc-width" class="calc-control" value="20" min="0.1" step="0.5" required>
            <span class="calc-input-addon-right unit-len-label">ft</span>
          </div>
        </div>
      </div>

      <div class="input-grid-3">
        <div class="calc-form-group">
          <label for="calc-thickness" class="calc-label">Thickness (<span class="unit-thick-label">inches</span>)</label>
          <div class="calc-input-wrapper has-addon-right">
            <input type="number" id="calc-thickness" class="calc-control" value="4" min="1" max="48" step="0.5" required>
            <span class="calc-input-addon-right unit-thick-label">in</span>
          </div>
        </div>

        <div class="calc-form-group">
          <label for="calc-quantity" class="calc-label">Quantity</label>
          <input type="number" id="calc-quantity" class="calc-control" value="1" min="1" max="100" step="1">
        </div>

        <div class="calc-form-group">
          <label for="calc-waste" class="calc-label">Waste Allowance</label>
          <select id="calc-waste" class="calc-control">
            <option value="0">0% (Exact)</option>
            <option value="5">5% (Simple)</option>
            <option value="10" selected>10% (Recommended)</option>
            <option value="15">15% (Uneven Grade)</option>
            <option value="20">20% (Excavated/Rough)</option>
          </select>
        </div>
      </div>
      <p id="calc-validation-msg" class="calc-validation-msg" role="alert" hidden></p>

      <!-- Concrete Material Selection -->
      <div class="calc-section-label" style="margin-top: 1.5rem;">2. Concrete Material Type &amp; Pricing</div>
      
      <div class="input-grid-2">
        <div class="calc-form-group">
          <label for="calc-material-type" class="calc-label">Concrete Source</label>
          <select id="calc-material-type" class="calc-control">
            <option value="readymix" selected>Ready-Mix (Delivered by Truck)</option>
            <option value="bagged">Bagged Concrete (Pre-mixed)</option>
          </select>
        </div>

        <!-- Ready Mix Price Row -->
        <div id="row-readymix" class="calc-form-group">
          <label for="calc-readymix-price" class="calc-label">Ready-Mix Price / <span class="unit-vol-label">cu. yds</span></label>
          <div class="calc-input-wrapper has-addon-left">
            <span class="calc-input-addon-left">$</span>
            <input type="number" id="calc-readymix-price" class="calc-control" value="<?php echo $v($sd['concrete']['ready_mix_default'], 0); ?>" min="50" step="5">
          </div>
        </div>
      </div>

      <!-- Bagged Concrete Sub-options -->
      <div id="row-bagged" class="input-grid-2" style="display: none;">
        <div class="calc-form-group">
          <label for="calc-bag-size" class="calc-label">Bag Size</label>
          <select id="calc-bag-size" class="calc-control">
            <option value="80" selected>80 lb Bag (0.60 cu ft yield)</option>
            <option value="60">60 lb Bag (0.45 cu ft yield)</option>
            <option value="50">50 lb Bag (0.375 cu ft yield)</option>
            <option value="40">40 lb Bag (0.30 cu ft yield)</option>
          </select>
        </div>

        <div class="calc-form-group">
          <label for="calc-bag-price" class="calc-label">Price Per Bag</label>
          <div class="calc-input-wrapper has-addon-left">
            <span class="calc-input-addon-left">$</span>
            <input type="number" id="calc-bag-price" class="calc-control" value="<?php echo $v($sd['concrete']['bag_price_default']); ?>" min="1" step="0.25">
          </div>
        </div>
      </div>

      <!-- Progressive Scope Breakdowns -->
      <div class="calc-section-label" style="margin-top: 1.5rem;">3. Project Scope, Reinforcement &amp; Labor</div>

      <!-- Accordion: Site Prep & Base -->
      <div class="scope-accordion">
        <div class="scope-header-row">
          <input type="checkbox" id="chk-site-prep" class="scope-toggle-checkbox" aria-label="Include site prep, excavation and gravel base">
          <button type="button" class="scope-header" aria-expanded="false" aria-controls="body-site-prep">
            <span class="scope-title-group">
              <span class="scope-title">Site Prep, Excavation &amp; Gravel Base</span>
              <span class="scope-cost" data-scope-cost="site"></span>
            </span>
            <span class="scope-chevron" aria-hidden="true">&#9662;</span>
          </button>
        </div>
        <div class="scope-body" id="body-site-prep">
          <div class="input-grid-2">
            <div class="calc-form-group">
              <label for="calc-excavation-rate" class="calc-label">Excavation &amp; Grading Rate</label>
              <div class="calc-input-wrapper has-addon-left has-addon-right">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-excavation-rate" class="calc-control" value="<?php echo $v($sd['site']['excavation_default']); ?>" step="0.25">
                <span class="calc-input-addon-right">/sq ft</span>
              </div>
            </div>
            <div class="calc-form-group">
              <label for="calc-gravel-depth" class="calc-label">Gravel Sub-Base Depth</label>
              <div class="calc-input-wrapper has-addon-right">
                <input type="number" id="calc-gravel-depth" class="calc-control" value="<?php echo $v($sd['site']['gravel_depth_in'], 0); ?>" step="1">
                <span class="calc-input-addon-right unit-thick-label">inches</span>
              </div>
            </div>
          </div>
          <div class="input-grid-2">
            <div class="calc-form-group">
              <label for="calc-gravel-price" class="calc-label">Crushed Gravel Price</label>
              <div class="calc-input-wrapper has-addon-left has-addon-right">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-gravel-price" class="calc-control" value="<?php echo $v($sd['site']['gravel_default']); ?>" step="5">
                <span class="calc-input-addon-right">/yd³</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Accordion: Forms & Vapor Barrier -->
      <div class="scope-accordion">
        <button type="button" class="scope-header" aria-expanded="false">
          <span class="scope-title-group">
            <span class="scope-title">Formwork &amp; Moisture Protection</span>
            <span class="scope-cost" data-scope-cost="forms"></span>
          </span>
          <span class="scope-chevron" aria-hidden="true">&#9662;</span>
        </button>
        <div class="scope-body">
          <div class="input-grid-2">
            <div class="calc-form-group">
              <label class="calc-label" style="display: flex; align-items: center; gap: 0.4rem;">
                <input type="checkbox" id="chk-forms" checked> Include Formwork Lumber
              </label>
              <div class="calc-input-wrapper has-addon-left has-addon-right">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-form-cost" class="calc-control" aria-label="Formwork cost per linear foot" value="<?php echo $v($sd['site']['forms_default']); ?>" step="0.20">
                <span class="calc-input-addon-right">/lin ft</span>
              </div>
            </div>

            <div class="calc-form-group">
              <label class="calc-label" style="display: flex; align-items: center; gap: 0.4rem;">
                <input type="checkbox" id="chk-vapor-barrier" checked> Include 10-15mil Vapor Barrier
              </label>
              <div class="calc-input-wrapper has-addon-left has-addon-right">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-vapor-cost" class="calc-control" aria-label="Vapor barrier cost per square foot" value="<?php echo $v($sd['site']['vapor_default']); ?>" step="0.05">
                <span class="calc-input-addon-right">/sq ft</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Accordion: Steel Reinforcement -->
      <div class="scope-accordion">
        <button type="button" class="scope-header" aria-expanded="false">
          <span class="scope-title-group">
            <span class="scope-title">Reinforcement Specification</span>
            <span class="scope-cost" data-scope-cost="reinforce"></span>
          </span>
          <span class="scope-chevron" aria-hidden="true">&#9662;</span>
        </button>
        <div class="scope-body">
          <div class="input-grid-2">
            <div class="calc-form-group">
              <label for="calc-reinforce-type" class="calc-label">Reinforcement Option</label>
              <select id="calc-reinforce-type" class="calc-control">
                <option value="none">None (Plain concrete)</option>
                <option value="mesh">Welded Wire Mesh (6x6 W1.4)</option>
                <option value="rebar" selected>#4 (1/2") Steel Rebar Grid</option>
                <option value="custom">Custom Specified Amount</option>
              </select>
            </div>
            <div class="calc-form-group">
              <label for="calc-rebar-spacing" class="calc-label">Rebar Grid Spacing (on center)</label>
              <select id="calc-rebar-spacing" class="calc-control">
                <option value="12">12 in (30 cm)</option>
                <option value="16">16 in (40 cm)</option>
                <option value="18" selected>18 in (45 cm)</option>
                <option value="24">24 in (60 cm)</option>
              </select>
              <p class="calc-field-note">Closer spacing uses more steel and raises the rebar cost.</p>
            </div>
            <div class="calc-form-group">
              <label for="calc-reinforce-cost" class="calc-label">Custom Cost Override</label>
              <div class="calc-input-wrapper has-addon-left">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-reinforce-cost" class="calc-control" value="0" step="25">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Accordion: Surface Finish -->
      <div class="scope-accordion">
        <button type="button" class="scope-header" aria-expanded="false">
          <span class="scope-title-group">
            <span class="scope-title">Surface Finishing Technique</span>
            <span class="scope-cost" data-scope-cost="finish"></span>
          </span>
          <span class="scope-chevron" aria-hidden="true">&#9662;</span>
        </button>
        <div class="scope-body">
          <div class="input-grid-2">
            <div class="calc-form-group">
              <label for="calc-finish-type" class="calc-label">Finish Style</label>
              <select id="calc-finish-type" class="calc-control">
                <option value="broom" selected>Broom Finish (Standard Outdoor, +$<?php echo $v($fin['broom']['default']); ?>/sq ft)</option>
                <option value="trowel">Smooth Trowel (Interior/Garage, +$<?php echo $v($fin['trowel']['default']); ?>/sq ft)</option>
                <option value="exposed">Exposed Aggregate Decorative (+$<?php echo $v($fin['exposed']['default']); ?>/sq ft)</option>
                <option value="stamped">Stamped &amp; Stained Pattern (+$<?php echo $v($fin['stamped']['default']); ?>/sq ft)</option>
                <option value="none">Basic Float Only ($0.00)</option>
                <option value="custom">Custom Finish Rate</option>
              </select>
            </div>
            <div class="calc-form-group">
              <label for="calc-finish-cost" class="calc-label">Custom Finish Rate</label>
              <div class="calc-input-wrapper has-addon-left has-addon-right">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-finish-cost" class="calc-control" value="<?php echo $v($fin['broom']['default']); ?>" step="0.25">
                <span class="calc-input-addon-right">/sq ft</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Accordion: Labor -->
      <div class="scope-accordion">
        <button type="button" class="scope-header" aria-expanded="false">
          <span class="scope-title-group">
            <span class="scope-title">Labor &amp; Contractor Installation</span>
            <span class="scope-cost" data-scope-cost="labor"></span>
          </span>
          <span class="scope-chevron" aria-hidden="true">&#9662;</span>
        </button>
        <div class="scope-body">
          <div class="input-grid-2">
            <div class="calc-form-group">
              <label for="calc-labor-mode" class="calc-label">Labor Estimating Method</label>
              <select id="calc-labor-mode" class="calc-control">
                <option value="sqft" selected>Per Square Foot (Standard)</option>
                <option value="hourly">Hourly Crew Rate</option>
                <option value="flat">Fixed Contractor Quote</option>
                <option value="diy">DIY / Self-Performed ($0.00 Labor)</option>
              </select>
            </div>

            <div id="row-labor-sqft" class="calc-form-group">
              <label for="calc-labor-sqft-rate" class="calc-label">Labor Rate / Sq. Ft.</label>
              <div class="calc-input-wrapper has-addon-left">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-labor-sqft-rate" class="calc-control" value="<?php echo $v($sd['labor']['sqft_default']); ?>" min="0" step="0.50">
              </div>
            </div>
          </div>

          <div id="row-labor-hourly" class="input-grid-2" style="display: none;">
            <div class="calc-form-group">
              <label for="calc-labor-hourly-rate" class="calc-label">Whole-Crew Rate / Hour</label>
              <div class="calc-input-wrapper has-addon-left">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-labor-hourly-rate" class="calc-control" value="<?php echo $v($sd['labor']['crew_hour_default']); ?>" step="5">
              </div>
            </div>
            <div class="calc-form-group">
              <label for="calc-labor-hours" class="calc-label">Estimated Total Hours</label>
              <input type="number" id="calc-labor-hours" class="calc-control" value="<?php echo esc_attr(round(400 * $sd['labor']['crew_hours_per_sqft'])); ?>" step="1">
            </div>
          </div>

          <div id="row-labor-flat" class="calc-form-group" style="display: none;">
            <label for="calc-labor-flat" class="calc-label">Flat Contractor Labor Quote</label>
            <div class="calc-input-wrapper has-addon-left">
              <span class="calc-input-addon-left">$</span>
              <input type="number" id="calc-labor-flat" class="calc-control" value="1500" step="50">
            </div>
          </div>
        </div>
      </div>

      <!-- Accordion: Delivery & Equipment -->
      <div class="scope-accordion">
        <button type="button" class="scope-header" aria-expanded="false">
          <span class="scope-title-group">
            <span class="scope-title">Delivery, Pumping &amp; Rentals</span>
            <span class="scope-cost" data-scope-cost="delivery"></span>
          </span>
          <span class="scope-chevron" aria-hidden="true">&#9662;</span>
        </button>
        <div class="scope-body">
          <div class="input-grid-3">
            <div class="calc-form-group">
              <label class="calc-label"><input type="checkbox" id="chk-delivery-fee" checked> Ready-Mix Delivery / Short Load</label>
              <p class="calc-field-note">Not charged when bagged concrete is selected.</p>
              <div class="calc-input-wrapper has-addon-left">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-delivery-amount" class="calc-control" aria-label="Ready-mix delivery or short-load fee" value="<?php echo $v($sd['delivery']['fee_default'], 0); ?>" step="10">
              </div>
            </div>
            <div class="calc-form-group">
              <label class="calc-label"><input type="checkbox" id="chk-pump-rental"> Concrete Pump Truck</label>
              <div class="calc-input-wrapper has-addon-left">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-pump-amount" class="calc-control" aria-label="Concrete pump truck cost" value="<?php echo $v($sd['pump']['default'], 0); ?>" step="25">
              </div>
            </div>
            <div class="calc-form-group">
              <label class="calc-label"><input type="checkbox" id="chk-equipment-rental"> Equipment Rentals</label>
              <div class="calc-input-wrapper has-addon-left">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-equipment-amount" class="calc-control" aria-label="Equipment rental cost" value="<?php echo $v($sd['equipment_default'], 0); ?>" step="25">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Demolition / Removal -->
      <div class="scope-accordion">
        <div class="scope-header-row">
          <input type="checkbox" id="chk-demo-removal" class="scope-toggle-checkbox" aria-label="Include old slab demolition and removal">
          <button type="button" class="scope-header" aria-expanded="false" aria-controls="body-demo-removal">
            <span class="scope-title-group">
              <span class="scope-title">Old Slab Demolition &amp; Removal</span>
              <span class="scope-cost" data-scope-cost="demo"></span>
            </span>
            <span class="scope-chevron" aria-hidden="true">&#9662;</span>
          </button>
        </div>
        <div class="scope-body" id="body-demo-removal">
          <div class="calc-form-group">
            <label for="calc-demo-cost" class="calc-label">Demolition &amp; Disposal Rate</label>
            <div class="calc-input-wrapper has-addon-left has-addon-right">
              <span class="calc-input-addon-left">$</span>
              <input type="number" id="calc-demo-cost" class="calc-control" value="<?php echo $v($sd['site']['demo_default']); ?>" step="0.25">
              <span class="calc-input-addon-right">/sq ft</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Contingency & Markup -->
      <div class="input-grid-2" style="margin-top: 1.25rem;">
        <div class="calc-form-group">
          <label for="calc-markup-percent" class="calc-label">Contractor Markup (%)</label>
          <div class="calc-input-wrapper has-addon-right">
            <input type="number" id="calc-markup-percent" class="calc-control" value="0" min="0" max="100" step="1">
            <span class="calc-input-addon-right">%</span>
          </div>
        </div>
        <div class="calc-form-group">
          <label for="calc-contingency-percent" class="calc-label">Contingency Buffer (%)</label>
          <div class="calc-input-wrapper has-addon-right">
            <input type="number" id="calc-contingency-percent" class="calc-control" value="<?php echo esc_attr($sd['contingency_pct']); ?>" min="0" max="50" step="1">
            <span class="calc-input-addon-right">%</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Live Estimate panel. Sticky beside the inputs on
         desktop; on phones it opens as a bottom sheet from the estimate bar. -->
    <div class="calc-results-col" id="live-estimate" aria-label="Live estimate">
      <div class="live-panel-head">
        <span class="live-panel-title"><span class="live-dot" aria-hidden="true"></span> Live Estimate</span>
        <button type="button" class="live-sheet-close" aria-label="Close estimate details">&times;</button>
      </div>
      <div class="live-summary">
        <!-- Total Budget Card -->
        <div class="results-hero-card">
          <div class="results-hero-label">Estimated Total Project Budget</div>
          <div id="out-total-cost" class="results-hero-total">$0.00</div>
          <div class="results-hero-subrates">
            <div><strong id="out-cost-per-sqft">$0.00</strong> / sq. ft.</div>
            <div><strong id="out-cost-per-sqm">$0.00</strong> / m²</div>
          </div>
          <div id="out-region-tag" class="results-region-tag">Based on U.S. national average rates</div>
        </div>

        <!-- Required Materials & Quantities Matrix -->
        <div class="quantities-grid">
          <div class="quantity-card">
            <div class="quantity-card-label">Order Volume (w/ Waste)</div>
            <div id="out-waste-yards" class="quantity-card-value">0.00 yd³</div>
            <div id="out-net-yards" class="quantity-card-note">Net: 0.00 yd³</div>
          </div>

          <div class="quantity-card">
            <div class="quantity-card-label">Or Bagged Equivalent</div>
            <div id="out-total-bags" class="quantity-card-value">0 bags</div>
            <div id="out-bag-note" class="quantity-card-note">80-lb pre-mix bags</div>
          </div>

          <div class="quantity-card">
            <div class="quantity-card-label">Total Surface Area</div>
            <div id="out-area-sqft" class="quantity-card-value">0 sq ft</div>
            <div id="out-net-meters" class="quantity-card-note">Order volume: 0.00 m³</div>
          </div>

          <div class="quantity-card">
            <div class="quantity-card-label">Estimated Wet Weight</div>
            <div id="out-total-weight" class="quantity-card-value">0 lbs</div>
            <div id="out-perimeter-ft" class="quantity-card-note">Perimeter: 0 ft</div>
          </div>
        </div>

        <!-- Visual Cost Breakdown Bar -->
        <div class="visual-cost-bar-wrapper">
          <div class="calc-section-label">Cost Distribution Breakdown</div>
          <div class="cost-bar-container">
            <div id="seg-material" class="cost-bar-seg seg-material" title="Concrete Material" style="width: 35%;"></div>
            <div id="seg-labor" class="cost-bar-seg seg-labor" title="Labor" style="width: 35%;"></div>
            <div id="seg-base" class="cost-bar-seg seg-base" title="Base & Prep" style="width: 10%;"></div>
            <div id="seg-reinforce" class="cost-bar-seg seg-reinforce" title="Reinforcement" style="width: 10%;"></div>
            <div id="seg-forms" class="cost-bar-seg seg-forms" title="Forms" style="width: 5%;"></div>
            <div id="seg-other" class="cost-bar-seg seg-other" title="Delivery & Misc" style="width: 5%;"></div>
          </div>
          <div class="cost-legend">
            <div class="legend-item"><span class="legend-dot" style="background:#2563eb;"></span> Concrete</div>
            <div class="legend-item"><span class="legend-dot" style="background:#10b981;"></span> Labor</div>
            <div class="legend-item"><span class="legend-dot" style="background:#f59e0b;"></span> Prep/Base</div>
            <div class="legend-item"><span class="legend-dot" style="background:#8b5cf6;"></span> Rebar</div>
            <div class="legend-item"><span class="legend-dot" style="background:#ec4899;"></span> Forms</div>
          </div>
        </div>

      </div>

      <!-- Planning tools: blueprint, breakdown, bid checker, weather check (assets/js/calculator-tools.js) -->
      <div id="calc-tools" class="calc-tools">
        <div class="calc-tools-tabs" role="tablist" aria-label="Slab planning tools">
          <?php foreach ($tabs as $key => $label) : $on = ($key === $default_tab); ?>
          <button type="button" class="calc-tools-tab<?php echo $on ? ' active' : ''; ?>" role="tab" id="tab-<?php echo esc_attr($key); ?>" aria-controls="tool-<?php echo esc_attr($key); ?>" aria-selected="<?php echo $on ? 'true' : 'false'; ?>"<?php echo $on ? '' : ' tabindex="-1"'; ?>><?php echo esc_html($label); ?></button>
          <?php endforeach; ?>
        </div>

        <!-- Blueprint -->
        <div id="tool-blueprint" class="calc-tool-panel" role="tabpanel" aria-labelledby="tab-blueprint"<?php echo $default_tab === 'blueprint' ? '' : ' hidden'; ?>>
          <p class="calc-tool-intro">A scaled plan of your slab: rebar grid at the spacing you chose, support chairs about every 3 to 4 ft, and the recommended control-joint saw cuts (2.5 times the thickness in inches, in feet, capped at 15 ft). It redraws as you change the inputs.</p>
          <div id="blueprint-wrap" class="blueprint-wrap" aria-live="polite"></div>
          <div id="blueprint-summary" class="tool-summary-grid"></div>
          <div class="calc-actions-bar tool-actions">
            <button type="button" id="btn-blueprint-png" class="calc-action-btn btn-primary">Download Blueprint (PNG)</button>
          </div>
        </div>


        <!-- Breakdown -->
        <div id="tool-breakdown" class="calc-tool-panel" role="tabpanel" aria-labelledby="tab-breakdown"<?php echo $default_tab === 'breakdown' ? '' : ' hidden'; ?>>
          <!-- Line-Item Bill of Quantities Table -->
          <div class="boq-table-card">
            <table class="boq-table">
              <thead>
                <tr>
                  <th>Scope Item / Material Description</th>
                  <th style="text-align: right;">Amount</th>
                </tr>
              </thead>
              <tbody id="boq-table-body">
                <!-- Dynamically injected rows -->
              </tbody>
            </table>
          </div>

        </div>

        <!-- Bid checker -->
        <div id="tool-bid" class="calc-tool-panel" role="tabpanel" aria-labelledby="tab-bid"<?php echo $default_tab === 'bid' ? '' : ' hidden'; ?>>
          <p class="calc-tool-intro">Enter the total your contractor quoted. We compare it with a benchmark for the same slab and scope you set in the calculator: materials at your entered prices plus labor at the regional standard rate, before any overhead or profit. Make sure the calculator scope (base, rebar, finish, pump, demolition) matches what the bid includes.</p>
          <div id="bid-scope-line" class="bid-scope-line"></div>
          <div class="input-grid-2">
            <div class="calc-form-group">
              <label for="bid-total" class="calc-label">Total Contractor Quote</label>
              <div class="calc-input-wrapper has-addon-left">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="bid-total" class="calc-control" min="0" step="50" placeholder="e.g. 5200">
              </div>
            </div>
          </div>
          <details class="bid-itemized">
            <summary>Optional: enter the bid's line items to see where the markup sits</summary>
            <div class="input-grid-2">
              <div class="calc-form-group">
                <label for="bid-item-material" class="calc-label">Concrete &amp; reinforcement</label>
                <div class="calc-input-wrapper has-addon-left"><span class="calc-input-addon-left">$</span><input type="number" id="bid-item-material" class="calc-control" min="0" step="25"></div>
              </div>
              <div class="calc-form-group">
                <label for="bid-item-labor" class="calc-label">Labor (place &amp; finish)</label>
                <div class="calc-input-wrapper has-addon-left"><span class="calc-input-addon-left">$</span><input type="number" id="bid-item-labor" class="calc-control" min="0" step="25"></div>
              </div>
              <div class="calc-form-group">
                <label for="bid-item-base" class="calc-label">Site prep, base, forms &amp; vapor barrier</label>
                <div class="calc-input-wrapper has-addon-left"><span class="calc-input-addon-left">$</span><input type="number" id="bid-item-base" class="calc-control" min="0" step="25"></div>
              </div>
              <div class="calc-form-group">
                <label for="bid-item-other" class="calc-label">Other (finish, delivery, pump, demo)</label>
                <div class="calc-input-wrapper has-addon-left"><span class="calc-input-addon-left">$</span><input type="number" id="bid-item-other" class="calc-control" min="0" step="25"></div>
              </div>
            </div>
          </details>
          <div id="bid-result" class="bid-result" aria-live="polite"></div>
        </div>

        <!-- Weather -->
        <div id="tool-weather" class="calc-tool-panel" role="tabpanel" aria-labelledby="tab-weather"<?php echo $default_tab === 'weather' ? '' : ' hidden'; ?>>
          <p class="calc-tool-intro">Enter the forecast for pour day. The check follows ACI 306R (cold weather) and ACI 305R (hot weather) and estimates the surface evaporation rate that drives plastic shrinkage cracking.</p>
          <div class="input-grid-3">
            <div class="calc-form-group">
              <label for="wx-temp" class="calc-label">Air Temperature (<span class="wx-temp-unit">°F</span>)</label>
              <input type="number" id="wx-temp" class="calc-control" value="70" step="1">
            </div>
            <div class="calc-form-group">
              <label for="wx-rh" class="calc-label">Relative Humidity (%)</label>
              <input type="number" id="wx-rh" class="calc-control" value="50" min="0" max="100" step="5">
            </div>
            <div class="calc-form-group">
              <label for="wx-wind" class="calc-label">Wind Speed (<span class="wx-wind-unit">mph</span>)</label>
              <input type="number" id="wx-wind" class="calc-control" value="10" min="0" step="1">
            </div>
          </div>
          <div class="wx-quick" role="group" aria-label="Quick temperature presets">
            <button type="button" class="preset-btn wx-btn" data-wx="cold">Cold day</button>
            <button type="button" class="preset-btn wx-btn" data-wx="mild">Mild day</button>
            <button type="button" class="preset-btn wx-btn" data-wx="hot">Hot day</button>
          </div>
          <div id="wx-result" class="wx-result" aria-live="polite"></div>
        </div>
      </div>

      <!-- Action Toolbar -->
      <div class="calc-actions-bar">
        <button type="button" id="btn-calc-print" class="calc-action-btn btn-primary" title="Download a professional PDF cost estimate & calculation report">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/><path d="M12 18v-6M9 15l3 3 3-3"/></svg>
          Download PDF Report
        </button>
        <button type="button" id="btn-calc-copy" class="calc-action-btn" title="Copy project summary to clipboard">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
          Copy Summary
        </button>
        <button type="button" id="btn-calc-export" class="calc-action-btn" title="Export as JSON data">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
          Export
        </button>
        <button type="button" id="btn-calc-reset" class="calc-action-btn" title="Reset all fields to standard values">
          Reset
        </button>
      </div>
    </div>
  </div>
  <!-- Phone / tablet: estimate bar fixed to the bottom of the screen while the
       calculator is on screen. Tapping it opens the Live Estimate panel above
       as a bottom sheet (calculator-tools.js). -->
  <div class="live-bar" id="live-bar" aria-hidden="true">
    <button type="button" class="live-bar-btn" aria-controls="live-estimate" aria-expanded="false" tabindex="-1">
      <span class="live-bar-total" id="bar-total">$0</span>
      <span class="live-bar-meta"><span id="bar-yards">0.00 yd³</span> &middot; <span id="bar-bags">0 bags</span></span>
      <span class="live-bar-cta">Blueprint &amp; breakdown <span aria-hidden="true">&#9650;</span></span>
    </button>
  </div>
  <div class="live-sheet-backdrop" hidden></div>
</div>
