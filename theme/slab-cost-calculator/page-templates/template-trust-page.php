<?php
/**
 * Template Name: Trust & Editorial Policy Pages
 *
 * @package SlabCostCalculator
 */

get_header();
slab_render_breadcrumbs();
?>

<main id="primary" class="site-main">
  <div class="container container-narrow">
    <article class="article-content">
      <header class="page-header" style="margin-bottom: 2rem;">
        <h1 class="hero-title"><?php the_title(); ?></h1>
        <div class="trust-bar">
          <div class="trust-item"><strong>Document Type:</strong> <span>Institutional Policy &amp; Standards</span></div>
          <div class="trust-item"><strong>Last Revised:</strong> <span><?php echo date('F Y'); ?></span></div>
        </div>
      </header>

      <?php
      global $post;
      $slug = $post ? $post->post_name : '';
      ?>

      <?php if ($slug === 'about') : ?>
        <h2>About Slab Cost Calculator</h2>
        <p>
          <strong>Slab Cost Calculator</strong> is an independent construction cost engineering platform dedicated to bringing transparency, scientific precision, and practical utility to residential and commercial concrete flatwork estimating.
        </p>
        <p>
          Founded by civil engineers, cost estimators, and construction technology professionals, our mission is to eliminate unexpected project overruns, cold joints from under-ordering, and opaque contractor markups. We provide free, accessible, and mathematically transparent tools for homeowners, general contractors, and developers worldwide.
        </p>

        <h2>Our Core Estimating Principles</h2>
        <ul>
          <li><strong>Zero "Black-Box" Numbers:</strong> All underlying formulas, geometric equations, and conversion constants are openly documented.</li>
          <li><strong>Total Project Scope:</strong> True concrete budgeting requires calculating gravel sub-bases, formwork, vapor barriers, rebar reinforcement, and finishing labor—not just simple cubic volume.</li>
          <li><strong>User-Configurable Real-World Rates:</strong> We never hard-code single "national prices" as universal facts. Users can enter their actual local supplier rates.</li>
        </ul>

      <?php elseif ($slug === 'authors') : ?>
        <h2>Editorial &amp; Review Board</h2>
        <p>
          Every calculator model, cost guide, and technical article on this platform is developed and maintained by construction professionals and estimating analysts with hands-on experience in civil infrastructure and concrete flatwork.
        </p>
        
        <?php get_template_part('template-parts/author-bio'); ?>

        <h2>Review &amp; Fact-Checking Standards</h2>
        <p>
          Our technical review team conducts quarterly audits of concrete mix pricing indexes, ASTM material standards, and regional labor trends. Code examples and mathematical calculators are tested against established American Concrete Institute (ACI) reference manuals.
        </p>

      <?php elseif ($slug === 'editorial-policy') : ?>
        <h2>Editorial &amp; Fact-Checking Policy</h2>
        <p>
          Our editorial commitment is to provide accurate, objective, and mathematically verified construction information.
        </p>
        <h3>Accuracy &amp; Data Verification</h3>
        <p>
          We do not publish unsubstantiated cost claims or fabricated pricing figures. All price ranges displayed in our cost guides cite specific baseline assumptions (e.g., standard 4-inch slabs, 3,000–4,000 PSI ready-mix concrete, standard suburban access).
        </p>
        <h3>Correction &amp; Update Protocol</h3>
        <p>
          Construction material markets and code standards evolve constantly. If you identify an outdated figure or potential formula error, our technical team investigates and deploys verified corrections within 48 hours.
        </p>

      <?php elseif ($slug === 'disclaimer') : ?>
        <h2>Civil Engineering &amp; Cost Disclaimer</h2>
        <div class="callout callout-warning">
          <div class="callout-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h20.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Planning Estimates Only — Not Structural Engineering Advice
          </div>
          <p>
            The tools and information on Slab Cost Calculator are designed strictly for preliminary planning, rough budgeting, and general educational reference. They do not constitute certified civil or structural engineering services.
          </p>
        </div>
        <p>
          Soil bearing capacities, local seismic codes, frost depth requirements, municipal zoning regulations, and drainage mandates differ significantly across geographic jurisdictions. Always consult a licensed local civil engineer, building inspector, or certified concrete contractor before excavating or pouring structural foundations.
        </p>

      <?php elseif ($slug === 'contact') : ?>
        <h2>Contact Our Technical Team</h2>
        <p>
          Have questions regarding our formulas, suggestions for additional construction calculators, or feedback on local pricing data? Reach out to our technical team:
        </p>
        <div style="background: #ffffff; border: 1px solid var(--slate-200); padding: 1.5rem; border-radius: var(--radius-md); margin-top: 1.5rem;">
          <p><strong>Email (technical &amp; editorial):</strong> <a href="mailto:contact@slabcostcalculator.com">contact@slabcostcalculator.com</a></p>
          <p><strong>Response Time:</strong> We typically respond to technical and editorial inquiries within 1–2 business days.</p>
        </div>

      <?php elseif ($slug === 'privacy-policy') : ?>
        <h2>Privacy Policy</h2>
        <p>
          Your privacy is paramount. Slab Cost Calculator does not require user registration or personal identification to use any of our calculation tools.
        </p>
        <h3>Data Collection &amp; Calculator Inputs</h3>
        <p>
          All measurements and dimensions entered into our calculators are processed locally in your web browser via client-side JavaScript. We do not store or transmit your project dimensions or cost calculations to our servers.
        </p>

      <?php elseif ($slug === 'terms') : ?>
        <h2>Terms of Use</h2>
        <p>
          By accessing or using the Slab Cost Calculator website and tools, you agree to these Terms of Use and acknowledge that all estimates are provided "as is" without warranty of any kind.
        </p>
      <?php else : ?>
        <?php
        while (have_posts()) : the_post();
          the_content();
        endwhile;
        ?>
      <?php endif; ?>

    </article>
  </div>
</main>

<?php
get_footer();
