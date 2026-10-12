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
        <?php echo (int) Slab_Calculator_Pages::tool_count(); ?> concrete calculators for contractors and DIY builders, each with its formula shown. Calculate volume, bags, rebar, gravel sub-base, labor, and total project budgets with zero guesswork.
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
        <p class="subcalc-desc">Yards to order, price by PSI strength, truck count, delivery fee and short-load fees for small orders.</p>
        <a href="<?php echo esc_url(home_url('/ready-mix-concrete-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-mix-calculator/')); ?>">Concrete Mix Ratio Calculator</a></h3>
        <p class="subcalc-desc">Bags of portland cement, tons of sand and stone, and gallons of water for site-mixed concrete at 1:2:3, 1:2:4 and other ratios.</p>
        <a href="<?php echo esc_url(home_url('/concrete-mix-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>
    </div>

    <!-- Group 2: Structural & Site Preparation Calculators -->
    <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--slate-900); margin-bottom: 1.25rem;">Reinforcement, Base &amp; Structural Calculators</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
      
      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-rebar-calculator/')); ?>">Concrete Rebar Calculator</a></h3>
        <p class="subcalc-desc">Lay out a #4 rebar grid at 12, 18 or 24 in: linear footage with laps, 20-foot stick counts and support chairs.</p>
        <a href="<?php echo esc_url(home_url('/concrete-rebar-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-wire-mesh-calculator/')); ?>">Wire Mesh Calculator</a></h3>
        <p class="subcalc-desc">Count 5x10 sheets or 5x150 rolls of 6x6 welded wire mesh with 6 in overlaps, plus chairs and cost.</p>
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
        <p class="subcalc-desc">Form boards (2x4, 2x6 and up), stakes and screws for the slab edge, plus the forms budget.</p>
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
        <p class="subcalc-desc">Estimate the cost to break out and haul away an old slab, and the tons of debris it makes.</p>
        <a href="<?php echo esc_url(home_url('/concrete-removal-cost-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-waste-calculator/')); ?>">Concrete Waste Factor Calculator</a></h3>
        <p class="subcalc-desc">Pick a waste allowance (5% to 20%) for your site and see the extra yards and their cost against running short.</p>
        <a href="<?php echo esc_url(home_url('/concrete-waste-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>

      <div class="subcalc-card">
        <h3 class="subcalc-title"><a href="<?php echo esc_url(home_url('/concrete-project-cost-calculator/')); ?>">Concrete Project Cost Calculator</a></h3>
        <p class="subcalc-desc">Every line item of a slab job, from base and forms to finish, labor, delivery, markup and contingency.</p>
        <a href="<?php echo esc_url(home_url('/concrete-project-cost-calculator/')); ?>" style="font-weight: 700; font-size: 0.875rem;">Launch Calculator &rarr;</a>
      </div>
    </div>
  </div>
</main>

<?php
get_footer();
