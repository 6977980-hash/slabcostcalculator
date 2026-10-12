<?php
/**
 * FULL ARTICLES FOR THE 20 STUB POSTS
 *
 * Twenty of the 30 imported posts were 28 to 105-word stubs, and they sit on
 * the site's main money keywords (10x10 cost, cost per yard, how many bags,
 * driveway, garage...). Post text lives in the database, which a theme push
 * cannot change, so the full articles live here instead: one file per post
 * slug in inc/articles/, built from the figures in inc/site-data.php.
 *
 * When a post is shown, its body is swapped for the full article, but only
 * while the stored text is still the short stub (STUB_MAX_WORDS or fewer).
 * If someone writes a longer article in wp-admin, theirs is shown instead.
 * The database is never changed.
 *
 * Each article file returns:
 *   'updated' => 'Y-m-d'  date the article text last changed
 *   'html'    => body HTML (without the FAQ)
 *   'faqs'    => [['q' => ..., 'a' => ...], ...]  shown as the FAQ section
 *                 and marked up as FAQPage schema (inc/schema-generator.php)
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_Post_Articles {

    /** Stored posts with this many words or fewer count as the old stub. */
    const STUB_MAX_WORDS = 250;

    private static $cache = [];

    public static function init() {
        add_filter('the_content', [__CLASS__, 'filter_content'], 20);
        add_filter('get_the_modified_date', [__CLASS__, 'filter_modified_date'], 10, 3);
        add_filter('wp_sitemaps_posts_entry', [__CLASS__, 'filter_sitemap_entry'], 10, 2);
    }

    private static function file($slug) {
        $slug = sanitize_title($slug);
        return get_template_directory() . '/inc/articles/' . $slug . '.php';
    }

    /** The article array for a slug, or null if there is none. */
    public static function get($slug) {
        if (!$slug) {
            return null;
        }
        if (!array_key_exists($slug, self::$cache)) {
            $file = self::file($slug);
            $art = is_readable($file) ? self::load($file) : null;
            self::$cache[$slug] = (is_array($art) && !empty($art['html'])) ? $art : null;
        }
        return self::$cache[$slug];
    }

    private static function load($file) {
        return include $file;
    }

    /** True when this post shows the theme's full article instead of its stored text. */
    public static function applies($post) {
        $post = get_post($post);
        if (!$post || $post->post_type !== 'post' || !self::get($post->post_name)) {
            return false;
        }
        $words = str_word_count(wp_strip_all_tags(strip_shortcodes((string) $post->post_content)));
        return $words <= self::STUB_MAX_WORDS;
    }

    /** Article body plus its FAQ section, as HTML. */
    public static function html($post) {
        $post = get_post($post);
        $art = self::get($post->post_name);
        return $art['html'] . self::faq_html($art['faqs'] ?? []);
    }

    /** The post's body for display or export: the full article if it applies, else the stored text. */
    public static function body($post, $stored) {
        return self::applies($post) ? self::html($post) : $stored;
    }

    public static function faqs($post) {
        if (!self::applies($post)) {
            return [];
        }
        $art = self::get(get_post($post)->post_name);
        return $art['faqs'] ?? [];
    }

    private static function faq_html($faqs) {
        if (empty($faqs)) {
            return '';
        }
        $o = '<section class="faq-section" aria-label="Frequently Asked Questions"><h2 id="faq">Frequently Asked Questions</h2>';
        foreach ($faqs as $f) {
            $o .= '<div class="faq-item"><button type="button" class="faq-question" aria-expanded="false">' . esc_html($f['q']) . '</button>'
                . '<div class="faq-answer"><p>' . wp_kses_post($f['a']) . '</p></div></div>';
        }
        return $o . '</section>';
    }

    public static function filter_content($content) {
        $post = get_post();
        if (!$post || !in_the_loop() || !is_main_query() || !self::applies($post)) {
            return $content;
        }
        return self::html($post);
    }

    /** Show the article's own "updated" date when it is newer than the stored post's. */
    public static function filter_modified_date($the_date, $format, $post) {
        $post = get_post($post);
        if (!$post || !self::applies($post)) {
            return $the_date;
        }
        $updated = self::get($post->post_name)['updated'] ?? '';
        if (!$updated || strtotime($updated) <= strtotime($post->post_modified)) {
            return $the_date;
        }
        $format = $format ?: get_option('date_format');
        return mysql2date($format, $updated . ' 12:00:00');
    }

    public static function filter_sitemap_entry($entry, $post) {
        if (self::applies($post)) {
            $updated = self::get($post->post_name)['updated'] ?? '';
            if ($updated && strtotime($updated) > strtotime($post->post_modified_gmt)) {
                $entry['lastmod'] = mysql2date('c', $updated . ' 12:00:00', false);
            }
        }
        return $entry;
    }
}

Slab_Post_Articles::init();
