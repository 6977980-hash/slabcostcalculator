<?php
/**
 * CALCULATOR FAQ ACCORDION (visible content + matching FAQPage schema source)
 *
 * Pulls its Q&A pairs from Slab_Calculator_FAQ::get_faqs_for_slug() — the
 * exact same source inc/schema-generator.php uses to build the FAQPage
 * JSON-LD for this page, so the structured data always matches what's
 * actually visible here. Reuses the .faq-item/.faq-question/.faq-answer
 * markup and main.js's existing initFAQAccordions() behavior already used
 * on the homepage.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

global $post;
$__slab_faq_slug = $post ? $post->post_name : get_query_var('slab_slug');
$__slab_faqs = class_exists('Slab_Calculator_FAQ') ? Slab_Calculator_FAQ::get_faqs_for_slug($__slab_faq_slug) : [];

if (empty($__slab_faqs)) {
    return;
}
?>

<section class="faq-section" aria-label="Frequently Asked Questions">
  <h2>Frequently Asked Questions</h2>

  <?php foreach ($__slab_faqs as $__slab_faq) : ?>
    <div class="faq-item">
      <button type="button" class="faq-question" aria-expanded="false">
        <?php echo esc_html($__slab_faq['q']); ?>
      </button>
      <div class="faq-answer">
        <?php echo wp_kses_post($__slab_faq['a']); ?>
      </div>
    </div>
  <?php endforeach; ?>
</section>
