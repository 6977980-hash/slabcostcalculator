<?php
/**
 * EMBED OUR CALCULATOR — /embed-concrete-calculator/
 *
 * Routed by Slab_Architecture_Router (slab_route=embed_info). Shows the
 * widget (inc/embed-widget.php) live and the code to copy, with an option
 * to open it on one state.
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
    <div class="content-grid">
      <article class="article-content">
        <header class="page-header" style="margin-bottom: 1.5rem;">
          <h1 class="hero-title">Add a Free Concrete Calculator to Your Website</h1>
          <p class="hero-answer"><strong>Paste two lines of code</strong> into your page and your readers get a concrete slab calculator on your page: cubic yards, bags of mix, and an installed and DIY price range for any size, adjusted for their state. It is free, needs no sign-up, and its prices update with ours.</p>
        </header>

        <h2 id="preview">Live Preview</h2>
        <div class="embed-preview">
          <iframe id="embed-preview-frame" src="<?php echo esc_url(home_url(Slab_Embed_Widget::frame_path())); ?>" title="Concrete slab calculator" width="100%" height="560" style="border:0;max-width:640px;" loading="lazy"></iframe>
        </div>

        <h2 id="code">Get the Code</h2>
        <div class="calc-form-group" style="max-width: 360px;">
          <label for="embed-state" class="calc-label">Open the widget on</label>
          <select id="embed-state" class="calc-control">
            <option value="">U.S. average (visitors pick their state)</option>
            <?php foreach (Slab_Data::states() as $code => $st) : ?>
            <option value="<?php echo esc_attr(strtolower($code)); ?>"><?php echo esc_html($st['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <label for="embed-code" class="calc-label">Paste this into your page (in WordPress, a Custom HTML block)</label>
        <textarea id="embed-code" class="calc-control embed-code" rows="5" readonly data-base="<?php echo esc_attr(home_url(Slab_Embed_Widget::frame_path())); ?>"><?php echo esc_textarea(Slab_Embed_Widget::snippet()); ?></textarea>
        <p><button type="button" class="calc-action-btn btn-primary" id="embed-copy" style="width:auto;display:inline-flex;">Copy code</button> <span id="embed-copied" class="calc-field-note" aria-live="polite"></span></p>

        <h2 id="terms">Using the Widget</h2>
        <ul>
          <li>Free for any website, commercial or not.</li>
          <li>Keep the credit line under the widget ("Concrete calculator by SlabCostCalculator.com"). It is a normal link your readers can follow to the full calculator.</li>
          <li>The widget loads from our site, so it always shows our current prices, the same ones as our <a href="<?php echo esc_url(home_url('/concrete-price-report/')); ?>">2026 price report</a>. It sets no cookies and has no ads or tracking.</li>
          <li>Prices are modelled U.S. estimates for planning, adjusted by a location factor per state; they are not quotes. The <a href="<?php echo esc_url(home_url('/concrete-price-report/#method')); ?>">method</a> is public.</li>
          <li>Need a different size or a feature? Write to <a href="mailto:contact@slabcostcalculator.com">contact@slabcostcalculator.com</a>.</li>
        </ul>

        <section class="faq-section" aria-label="Frequently Asked Questions">
          <h2 id="faq">Widget Questions</h2>
          <?php foreach (Slab_Embed_Widget::faqs() as $faq) : ?>
          <div class="faq-item">
            <button type="button" class="faq-question" aria-expanded="false"><?php echo esc_html($faq['q']); ?></button>
            <div class="faq-answer"><p><?php echo esc_html($faq['a']); ?></p></div>
          </div>
          <?php endforeach; ?>
        </section>
      </article>

      <aside class="sidebar-col">
        <div class="sidebar-widget">
          <div class="widget-title">Free Data</div>
          <ul class="calculator-nav-list">
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url('/concrete-price-report/')); ?>">2026 concrete price report</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url(Slab_State_Pages::hub_path())); ?>">Slab cost by state</a></li>
            <li class="calculator-nav-item"><a href="<?php echo esc_url(home_url(Slab_Size_Pages::hub_path())); ?>">Slab cost by size</a></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</main>

<script>
(function () {
  var sel = document.getElementById('embed-state');
  var code = document.getElementById('embed-code');
  var frame = document.getElementById('embed-preview-frame');
  var copy = document.getElementById('embed-copy');
  var note = document.getElementById('embed-copied');
  if (!sel || !code) return;
  var base = code.getAttribute('data-base');
  var template = code.value;
  sel.addEventListener('change', function () {
    var src = base + (sel.value ? '?state=' + sel.value : '');
    code.value = template.replace(base, src);
    if (frame) frame.src = src;
  });
  copy.addEventListener('click', function () {
    code.select();
    var done = function () { note.textContent = 'Copied.'; };
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(code.value).then(done, function () { document.execCommand('copy'); done(); });
    } else {
      document.execCommand('copy');
      done();
    }
  });
})();
</script>

<?php
get_footer();
