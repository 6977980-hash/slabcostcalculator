<?php
/**
 * PRIMARY CONCRETE SLAB COST CALCULATOR COMPONENT
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}
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
      <button type="button" class="unit-btn active" data-unit="imperial">Imperial (ft / in / yd³)</button>
      <button type="button" class="unit-btn" data-unit="metric">Metric (m / cm / m³)</button>
    </div>
  </div>

  <div class="calc-body-grid">
    <!-- Left Column: Input Form Controls -->
    <div class="calc-inputs-col">
      <!-- Project Presets -->
      <div class="preset-selector-group">
        <div class="calc-section-label">Quick Project Presets</div>
        <div class="preset-pills-scroll" role="group" aria-label="Project Type Presets">
          <button type="button" class="preset-btn" data-preset="patio">Patio (12x12)</button>
          <button type="button" class="preset-btn" data-preset="walkway">Walkway (4x30)</button>
          <button type="button" class="preset-btn" data-preset="driveway">Driveway (20x40)</button>
          <button type="button" class="preset-btn" data-preset="garage">2-Car Garage (24x24)</button>
          <button type="button" class="preset-btn" data-preset="shed">Shed Pad (10x10)</button>
          <button type="button" class="preset-btn" data-preset="foundation">Foundation (30x40)</button>
        </div>
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
            <input type="number" id="calc-readymix-price" class="calc-control" value="150" min="50" step="5">
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
            <input type="number" id="calc-bag-price" class="calc-control" value="6.50" min="1" step="0.25">
          </div>
        </div>
      </div>

      <!-- Progressive Scope Breakdowns -->
      <div class="calc-section-label" style="margin-top: 1.5rem;">3. Project Scope, Reinforcement &amp; Labor</div>

      <!-- Accordion: Site Prep & Base -->
      <div class="scope-accordion">
        <button type="button" class="scope-header" aria-expanded="false">
          <div class="scope-title-group">
            <input type="checkbox" id="chk-site-prep" class="scope-toggle-checkbox">
            <span class="scope-title">Site Prep, Excavation &amp; Gravel Base</span>
          </div>
          <span class="scope-chevron">&#9662;</span>
        </button>
        <div class="scope-body">
          <div class="input-grid-2">
            <div class="calc-form-group">
              <label for="calc-excavation-rate" class="calc-label">Excavation &amp; Grading Rate</label>
              <div class="calc-input-wrapper has-addon-left has-addon-right">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-excavation-rate" class="calc-control" value="1.50" step="0.25">
                <span class="calc-input-addon-right">/sq ft</span>
              </div>
            </div>
            <div class="calc-form-group">
              <label for="calc-gravel-depth" class="calc-label">Gravel Sub-Base Depth</label>
              <div class="calc-input-wrapper has-addon-right">
                <input type="number" id="calc-gravel-depth" class="calc-control" value="4" step="1">
                <span class="calc-input-addon-right">inches</span>
              </div>
            </div>
          </div>
          <div class="input-grid-2">
            <div class="calc-form-group">
              <label for="calc-gravel-price" class="calc-label">Crushed Gravel Price</label>
              <div class="calc-input-wrapper has-addon-left has-addon-right">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-gravel-price" class="calc-control" value="45.00" step="5">
                <span class="calc-input-addon-right">/yd³</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Accordion: Forms & Vapor Barrier -->
      <div class="scope-accordion open">
        <button type="button" class="scope-header" aria-expanded="true">
          <div class="scope-title-group">
            <span class="scope-title">Formwork &amp; Moisture Protection</span>
          </div>
          <span class="scope-chevron">&#9662;</span>
        </button>
        <div class="scope-body">
          <div class="input-grid-2">
            <div class="calc-form-group">
              <label class="calc-label" style="display: flex; align-items: center; gap: 0.4rem;">
                <input type="checkbox" id="chk-forms" checked> Include Formwork Lumber
              </label>
              <div class="calc-input-wrapper has-addon-left has-addon-right">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-form-cost" class="calc-control" value="2.20" step="0.20">
                <span class="calc-input-addon-right">/lin ft</span>
              </div>
            </div>

            <div class="calc-form-group">
              <label class="calc-label" style="display: flex; align-items: center; gap: 0.4rem;">
                <input type="checkbox" id="chk-vapor-barrier" checked> Include 10-15mil Vapor Barrier
              </label>
              <div class="calc-input-wrapper has-addon-left has-addon-right">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-vapor-cost" class="calc-control" value="0.35" step="0.05">
                <span class="calc-input-addon-right">/sq ft</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Accordion: Steel Reinforcement -->
      <div class="scope-accordion open">
        <button type="button" class="scope-header" aria-expanded="true">
          <div class="scope-title-group">
            <span class="scope-title">Reinforcement Specification</span>
          </div>
          <span class="scope-chevron">&#9662;</span>
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
          <div class="scope-title-group">
            <span class="scope-title">Surface Finishing Technique</span>
          </div>
          <span class="scope-chevron">&#9662;</span>
        </button>
        <div class="scope-body">
          <div class="input-grid-2">
            <div class="calc-form-group">
              <label for="calc-finish-type" class="calc-label">Finish Style</label>
              <select id="calc-finish-type" class="calc-control">
                <option value="broom" selected>Broom Finish (Standard Outdoor, +$0.75/sq ft)</option>
                <option value="trowel">Smooth Trowel (Interior/Garage, +$1.25/sq ft)</option>
                <option value="exposed">Exposed Aggregate Decorative (+$3.50/sq ft)</option>
                <option value="stamped">Stamped &amp; Stained Pattern (+$7.50/sq ft)</option>
                <option value="none">Basic Float Only ($0.00)</option>
                <option value="custom">Custom Finish Rate</option>
              </select>
            </div>
            <div class="calc-form-group">
              <label for="calc-finish-cost" class="calc-label">Custom Finish Rate</label>
              <div class="calc-input-wrapper has-addon-left has-addon-right">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-finish-cost" class="calc-control" value="0.75" step="0.25">
                <span class="calc-input-addon-right">/sq ft</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Accordion: Labor -->
      <div class="scope-accordion open">
        <button type="button" class="scope-header" aria-expanded="true">
          <div class="scope-title-group">
            <span class="scope-title">Labor &amp; Contractor Installation</span>
          </div>
          <span class="scope-chevron">&#9662;</span>
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
                <input type="number" id="calc-labor-sqft-rate" class="calc-control" value="5.00" min="0" step="0.50">
              </div>
            </div>
          </div>

          <div id="row-labor-hourly" class="input-grid-2" style="display: none;">
            <div class="calc-form-group">
              <label for="calc-labor-hourly-rate" class="calc-label">Crew Rate / Hour</label>
              <div class="calc-input-wrapper has-addon-left">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-labor-hourly-rate" class="calc-control" value="45.00" step="5">
              </div>
            </div>
            <div class="calc-form-group">
              <label for="calc-labor-hours" class="calc-label">Estimated Total Hours</label>
              <input type="number" id="calc-labor-hours" class="calc-control" value="16" step="1">
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
          <div class="scope-title-group">
            <span class="scope-title">Delivery, Pumping &amp; Rentals</span>
          </div>
          <span class="scope-chevron">&#9662;</span>
        </button>
        <div class="scope-body">
          <div class="input-grid-3">
            <div class="calc-form-group">
              <label class="calc-label"><input type="checkbox" id="chk-delivery-fee" checked> Delivery / Short Load</label>
              <div class="calc-input-wrapper has-addon-left">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-delivery-amount" class="calc-control" value="120" step="10">
              </div>
            </div>
            <div class="calc-form-group">
              <label class="calc-label"><input type="checkbox" id="chk-pump-rental"> Concrete Pump Truck</label>
              <div class="calc-input-wrapper has-addon-left">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-pump-amount" class="calc-control" value="650" step="25">
              </div>
            </div>
            <div class="calc-form-group">
              <label class="calc-label"><input type="checkbox" id="chk-equipment-rental"> Equipment Rentals</label>
              <div class="calc-input-wrapper has-addon-left">
                <span class="calc-input-addon-left">$</span>
                <input type="number" id="calc-equipment-amount" class="calc-control" value="150" step="25">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Demolition / Removal -->
      <div class="scope-accordion">
        <button type="button" class="scope-header" aria-expanded="false">
          <div class="scope-title-group">
            <input type="checkbox" id="chk-demo-removal" class="scope-toggle-checkbox">
            <span class="scope-title">Old Slab Demolition &amp; Removal</span>
          </div>
          <span class="scope-chevron">&#9662;</span>
        </button>
        <div class="scope-body">
          <div class="calc-form-group">
            <label for="calc-demo-cost" class="calc-label">Demolition &amp; Disposal Rate</label>
            <div class="calc-input-wrapper has-addon-left has-addon-right">
              <span class="calc-input-addon-left">$</span>
              <input type="number" id="calc-demo-cost" class="calc-control" value="3.50" step="0.25">
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
            <input type="number" id="calc-contingency-percent" class="calc-control" value="5" min="0" max="50" step="1">
            <span class="calc-input-addon-right">%</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Results & Bill of Quantities Dashboard -->
    <div class="calc-results-col">
      <!-- Total Budget Card -->
      <div class="results-hero-card">
        <div class="results-hero-label">Estimated Total Project Budget</div>
        <div id="out-total-cost" class="results-hero-total">$0.00</div>
        <div class="results-hero-subrates">
          <div><strong id="out-cost-per-sqft">$0.00</strong> / sq. ft.</div>
          <div><strong id="out-cost-per-sqm">$0.00</strong> / m²</div>
        </div>
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
          <div class="quantity-card-note">80-lb pre-mix bags</div>
        </div>

        <div class="quantity-card">
          <div class="quantity-card-label">Total Surface Area</div>
          <div id="out-area-sqft" class="quantity-card-value">0 sq ft</div>
          <div id="out-net-meters" class="quantity-card-note">0.00 m³ volume</div>
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
</div>
