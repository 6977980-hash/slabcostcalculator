<?php
/**
 * Template Name: Calculators Hub Directory
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
  <div class="container">
    <header class="page-header" style="margin-bottom: 2.5rem;">
      <h1 class="hero-title">Concrete &amp; Flatwork Estimating Calculators</h1>
      <p class="hero-subtitle">
        Explore our comprehensive suite of 16+ specialized construction calculators designed for civil engineers, flatwork contractors, and DIY builders. Calculate volume, bags, rebar, gravel sub-base, labor, and total project budgets with zero guesswork.
      </p>
    </header>

    <!-- Master Primary Calculator Callout -->
    <div style="background: linear-gradient(135deg, var(--slate-900) 0%, var(--slate-800) 100%); border-radius: var(--radius-xl); padding: 2rem; color: #fff; margin-bottom: 3rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.5rem; border: 1px solid var(--slate-700);">
      <div style="max-width: 640px;">
        <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: var(--accent-amber);">Flagship Estimating Engine</span>
        <h2 style="font-size: 1.75rem; font-weight: 800; color: #fff; margin: 0.35rem 0 0.5rem 0;">Primary Concrete Slab Cost Calculator</h2>
        <p style="color: var(--slate-300); font-size: 0.9375rem; margin: 0;">Complete multi-variable project budgeting system calculating ready-mix concrete, gravel base, formwork, steel reinforcement, finishing labor, delivery, and markup.</p>
      </div>
      <a href="<?php echo esc_url(home_url('/')); ?>" class="header-cta-btn" style="padding: 0.75rem 1.5rem; font-size: 1rem;">Open Primary Calculator &rarr;</a>
    </div>

    <!-- Group 1: Volume & Material Quantity Calculators -->
    <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--slate-900); margin-bottom: 1.25rem;">Volume, Bag &amp; Material Calculators</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
      
      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-volume-calculator/')); ?>">Concrete Volume Calculator</a></h3>
        <p class="subcalc-desc">Calculate exact cubic feet (ft³), cubic yards (yd³), and cubic meters (m³) for slabs, footings, and rectangular prisms.</p>
        <a href="<?php echo esc_url(home_url('/concrete-volume-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-bags-calculator/')); ?>">Concrete Bags Calculator</a></h3>
        <p class="subcalc-desc">Determine exact quantities of 40-lb, 50-lb, 60-lb, or 80-lb pre-mixed bags required, including adjustable waste factors.</p>
        <a href="<?php echo esc_url(home_url('/concrete-bags-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-yard-calculator/')); ?>">Cubic Yard Calculator</a></h3>
        <p class="subcalc-desc">Convert dimensional flatwork measurements into ready-mix supplier truck order yards with short-load waste margins.</p>
        <a href="<?php echo esc_url(home_url('/concrete-yard-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-weight-calculator/')); ?>">Concrete Weight Calculator</a></h3>
        <p class="subcalc-desc">Calculate wet and dry mass in lbs, US tons, and metric tonnes to determine trailer hauling and equipment limits.</p>
        <a href="<?php echo esc_url(home_url('/concrete-weight-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/ready-mix-concrete-calculator/')); ?>">Ready-Mix Concrete Calculator</a></h3>
        <p class="subcalc-desc">Estimate batch plant order volumes, transit mixer delivery fees, and minimum truckload thresholds.</p>
        <a href="<?php echo esc_url(home_url('/ready-mix-concrete-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-mix-calculator/')); ?>">Concrete Mix Ratio Calculator</a></h3>
        <p class="subcalc-desc">Calculate volumetric recipe batches of Portland cement, sand, gravel aggregates, and water for 3,000–4,000 PSI mixes.</p>
        <a href="<?php echo esc_url(home_url('/concrete-mix-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>
    </div>

    <!-- Group 2: Structural & Site Preparation Calculators -->
    <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--slate-900); margin-bottom: 1.25rem;">Reinforcement, Base &amp; Structural Calculators</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
      
      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-rebar-calculator/')); ?>">Concrete Rebar Calculator</a></h3>
        <p class="subcalc-desc">Calculate #3, #4, and #5 steel rebar requirements, linear footage, 20-foot stick counts, lap splices, and support chairs.</p>
        <a href="<?php echo esc_url(home_url('/concrete-rebar-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-wire-mesh-calculator/')); ?>">Wire Mesh Calculator</a></h3>
        <p class="subcalc-desc">Estimate 6x6 welded wire fabric sheets and roll coverage with standard 10% overlap allowances.</p>
        <a href="<?php echo esc_url(home_url('/concrete-wire-mesh-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/gravel-base-calculator/')); ?>">Gravel Base Calculator</a></h3>
        <p class="subcalc-desc">Calculate sub-base crushed gravel yardage and tonnage required beneath slabs with 15% compaction density factors.</p>
        <a href="<?php echo esc_url(home_url('/gravel-base-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-footing-calculator/')); ?>">Concrete Footing &amp; Pier Calculator</a></h3>
        <p class="subcalc-desc">Estimate continuous strip perimeter footings, deck piers, and cylindrical sonotube concrete volume.</p>
        <a href="<?php echo esc_url(home_url('/concrete-footing-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-form-calculator/')); ?>">Formwork Lumber Calculator</a></h3>
        <p class="subcalc-desc">Determine 2x4 and 2x6 dimensional lumber board feet, perimeter linear footage, wooden stakes, and duplex nails.</p>
        <a href="<?php echo esc_url(home_url('/concrete-form-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>
    </div>

    <!-- Group 3: Cost & Demolition Calculators -->
    <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--slate-900); margin-bottom: 1.25rem;">Cost, Labor &amp; Demolition Calculators</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
      
      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-cost-per-square-foot-calculator/')); ?>">Cost Per Square Foot Calculator</a></h3>
        <p class="subcalc-desc">Deconstruct material vs. labor unit rates to evaluate contractor bids and budget square footage costs.</p>
        <a href="<?php echo esc_url(home_url('/concrete-cost-per-square-foot-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-labor-cost-calculator/')); ?>">Concrete Labor Cost Calculator</a></h3>
        <p class="subcalc-desc">Estimate crew hours, pouring wages, screeding, bull floating, power troweling, and finishing rates.</p>
        <a href="<?php echo esc_url(home_url('/concrete-labor-cost-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-removal-cost-calculator/')); ?>">Concrete Removal &amp; Demo Calculator</a></h3>
        <p class="subcalc-desc">Calculate demolition jackhammer labor, concrete tonnage debris, dumpster roll-off rentals, and landfill haul-off costs.</p>
        <a href="<?php echo esc_url(home_url('/concrete-removal-cost-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-waste-calculator/')); ?>">Concrete Waste Factor Calculator</a></h3>
        <p class="subcalc-desc">Calculate safety buffers (5%, 10%, 15%, 20%) to prevent cold joints and short-load delivery delays.</p>
        <a href="<?php echo esc_url(home_url('/concrete-waste-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-project-cost-calculator/')); ?>">Concrete Project Cost Calculator</a></h3>
        <p class="subcalc-desc">All-in-one comprehensive bid estimator supporting complex multiple-pour projects with contractor markups.</p>
        <a href="<?php echo esc_url(home_url('/concrete-project-cost-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>
    </div>
  </div>
</main>

<?php
get_footer();
