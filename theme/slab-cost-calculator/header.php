<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/svg+xml" href="<?php echo esc_url(get_template_directory_uri() . '/assets/images/favicon.svg'); ?>">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header" role="banner">
  <div class="container header-inner">
    <div class="site-branding">
      <a href="<?php echo esc_url(home_url('/')); ?>" rel="home" title="Slab Cost Calculator Home">
        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo.svg'); ?>" alt="Slab Cost Calculator Logo" class="site-logo" width="220" height="44">
      </a>
    </div>

    <nav class="primary-nav" role="navigation" aria-label="Main Navigation">
      <ul class="nav-links">
        <li class="nav-item nav-item-home <?php echo slab_is_home_page() ? 'active' : ''; ?>">
          <a href="<?php echo esc_url(home_url('/')); ?>" class="nav-link">Home</a>
        </li>
        
        <li class="nav-item">
          <a href="<?php echo esc_url(home_url('/calculators/')); ?>" class="nav-link">
            Calculators
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
          </a>
          <ul class="dropdown-menu">
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/concrete-volume-calculator/')); ?>">Concrete Volume Calculator</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/concrete-bags-calculator/')); ?>">Concrete Bags Calculator</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/concrete-yard-calculator/')); ?>">Concrete Yard Calculator</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/concrete-weight-calculator/')); ?>">Concrete Weight Calculator</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/concrete-cost-per-square-foot-calculator/')); ?>">Cost Per Sq Ft Calculator</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/concrete-rebar-calculator/')); ?>">Concrete Rebar Calculator</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/gravel-base-calculator/')); ?>">Gravel Base Calculator</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/concrete-footing-calculator/')); ?>">Concrete Footing Calculator</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/concrete-labor-cost-calculator/')); ?>">Concrete Labor Cost Calculator</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/concrete-removal-cost-calculator/')); ?>">Demolition &amp; Removal Calculator</a></li>
          </ul>
        </li>

        <li class="nav-item">
          <a href="<?php echo esc_url(home_url('/cost-guides/')); ?>" class="nav-link">
            Cost Guides
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
          </a>
          <ul class="dropdown-menu">
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/how-much-does-a-concrete-slab-cost/')); ?>">Concrete Slab Cost Guide</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/concrete-cost-per-square-foot-pricing-matrix/')); ?>">Cost Per Square Foot (2026)</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/10x10-concrete-slab-cost-breakdown/')); ?>">10x10 Concrete Slab Cost</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/12x12-concrete-slab-cost-breakdown/')); ?>">12x12 Concrete Slab Cost</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/20x20-concrete-slab-cost-breakdown/')); ?>">20x20 Concrete Slab Cost</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/24x24-concrete-slab-cost-breakdown/')); ?>">24x24 Garage Slab Cost</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/concrete-cost-per-cubic-yard-pricing-explained/')); ?>">Cost Per Cubic Yard Guide</a></li>
          </ul>
        </li>

        <li class="nav-item">
          <a href="<?php echo esc_url(home_url('/patio/')); ?>" class="nav-link">
            Applications
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
          </a>
          <ul class="dropdown-menu">
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/patio/')); ?>">Patio Slabs</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/driveway/')); ?>">Driveway Slabs</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/garage/')); ?>">Garage Floors</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/shed/')); ?>">Shed Pads</a></li>
            <li class="dropdown-item"><a href="<?php echo esc_url(home_url('/foundation/')); ?>">Foundations</a></li>
          </ul>
        </li>

        <li class="nav-item">
          <a href="<?php echo esc_url(home_url('/how-to/')); ?>" class="nav-link">How-To</a>
        </li>

        <li class="nav-item">
          <a href="<?php echo esc_url(home_url('/knowledgebase/')); ?>" class="nav-link">Knowledgebase</a>
        </li>

        <li class="nav-item">
          <a href="<?php echo esc_url(home_url('/methodology/')); ?>" class="nav-link">Methodology</a>
        </li>

        <!-- Shown in the phone menu; wide screens get the button below. -->
        <li class="nav-item nav-item-quote <?php echo get_query_var('slab_route') === 'quote_checker' ? 'active' : ''; ?>">
          <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>" class="nav-link">Check a Quote</a>
        </li>
      </ul>

      <a href="<?php echo esc_url(home_url('/concrete-quote-checker/')); ?>" class="header-cta-btn header-cta-quote">Check a Quote</a>
      <a href="<?php echo esc_url(home_url('/')); ?>#concrete-slab-calculator" class="header-cta-btn">Calculate Slab</a>

      <button class="mobile-menu-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
      </button>
    </nav>
  </div>
</header>
