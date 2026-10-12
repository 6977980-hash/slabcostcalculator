<?php
/**
 * POST FIGURE CORRECTIONS
 *
 * The 30 articles live in the database, so a theme push cannot edit their
 * text. A few of them still carry figures that contradict the site's one
 * data source (inc/site-data.php): old per-size costs, the old PSI price
 * table, 90°F instead of 85°F, saw-cut timing of 6 to 18 hours, and so on.
 *
 * This filter swaps those exact phrases for the current figures when a post
 * is displayed (and in its excerpt, meta description and llms-full.txt).
 * The database is not changed. If a phrase has been edited in wp-admin it
 * no longer matches and is left alone, so hand edits always win.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_Post_Figures {

    private static $rules = null;

    public static function init() {
        add_filter('the_content', [__CLASS__, 'filter_content'], 999);
        add_filter('get_the_excerpt', [__CLASS__, 'filter_content'], 999);
    }

    public static function filter_content($text) {
        $post = get_post();
        if (!$post || $post->post_type !== 'post') {
            return $text;
        }
        return self::fix($post->post_name, $text);
    }

    /** Apply the corrections for one post slug to any text (HTML or plain). */
    public static function fix($slug, $text) {
        $rules = self::rules();
        if (!is_string($text) || $text === '' || empty($rules[$slug])) {
            return $text;
        }
        foreach ($rules[$slug] as $rule) {
            $text = preg_replace('%' . $rule[0] . '%u', $rule[1], $text);
        }
        return $text;
    }

    /** Escape a replacement so "$7" is not read as a backreference. */
    private static function lit($s) {
        return str_replace(['\\', '$'], ['\\\\', '\\$'], $s);
    }

    /**
     * Slug => list of [regex, replacement]. Patterns accept a plain hyphen,
     * an en dash or its HTML entity, so they match both the stored text and the rendered page.
     */
    private static function rules() {
        if (self::$rules !== null) {
            return self::$rules;
        }

        $D = '(?:–|-|&#8211;|&ndash;)';       // dash
        $L = function ($s) {
            return self::lit($s);
        };
        $sd = Slab_Data::all();
        $m2 = function ($pair) {
            return Slab_Data::money_range($pair, 2, ' to ');
        };
        $m0 = function ($pair, $sep = ' to ') {
            return Slab_Data::money_range($pair, 0, $sep);
        };

        $psf = Slab_Data::installed_psf();
        $diy_psf = Slab_Data::diy_psf();
        $s20 = Slab_Data::size_costs(20, 20, 4);
        $s20_5 = Slab_Data::size_costs(20, 20, 5);
        $s10 = Slab_Data::size_costs(10, 10, 4);
        $s12 = Slab_Data::size_costs(12, 12, 4);
        $s24_5 = Slab_Data::size_costs(24, 24, 5);
        $drive = Slab_Data::size_costs(40, 20, 5);
        $drive_psf = Slab_Data::driveway_psf();
        $drive_stamped = Slab_Data::stamped_psf(40, 20, 5);
        $found = Slab_Data::foundation_psf();
        $rm_range = [Slab_Data::psi_price(3000)[0], Slab_Data::psi_price(4000)[1]];
        $bag_yd = $sd['concrete']['bag_price_default'] / $sd['concrete']['bag_yields']['80'] * 27;
        $j = $sd['joints'];
        $hot = $sd['weather']['hot_f'];

        $psi_list = [];
        foreach ([3000, 3500, 4000, 5000] as $psi) {
            $psi_list[] = number_format($psi) . ' PSI: ' . Slab_Data::money_range(Slab_Data::psi_price($psi), 0, '–') . '/yd³.';
        }

        self::$rules = [
            'how-much-does-a-concrete-slab-cost' => [
                ['\$6\.00 to \$12\.00 per square foot', $L($m2($psf) . ' per square foot')],
                ['\$2,400 and \$4,800', $L(Slab_Data::money($s20['installed'][0], 0) . ' and ' . Slab_Data::money($s20['installed'][1], 0))],
                ['\$2\.50 to \$4\.50 per square foot(</strong>)? \(\$1,000 to \$1,800 for 400 sq\. ft\.\)', $L($m2($diy_psf) . ' per square foot') . '$1 ' . $L('(' . $m0($s20['diy']) . ' for 400 sq. ft.)')],
                ['\$6\.00' . $D . '\$12\.00 per square foot, DIY materials \$2\.50' . $D . '\$4\.50/sq ft', $L(Slab_Data::money_range($psf, 2, '-') . ' per square foot, DIY materials ' . Slab_Data::money_range($diy_psf, 2, '-') . '/sq ft')],
            ],
            'concrete-cost-per-square-foot-pricing-matrix' => [
                ['\$6\.00 to \$12\.00 per square foot', $L($m2($psf) . ' per square foot')],
            ],
            'ready-mix-vs-bagged-concrete' => [
                ['\$135 to \$175 per cubic yard', $L($m0($rm_range) . ' per cubic yard')],
                ['~\$290 per cubic yard', $L('~' . Slab_Data::money($bag_yd, 0) . ' per cubic yard')],
            ],
            '10x10-concrete-slab-cost-breakdown' => [
                ['between \$600 and \$1,200 professionally installed, or \$250 to \$450 DIY\. It requires 1\.35 cubic yards with waste',
                    $L('between ' . Slab_Data::money($s10['installed'][0], 0) . ' and ' . Slab_Data::money($s10['installed'][1], 0) . ' professionally installed, or ' . $m0($s10['diy']) . ' DIY. It requires ' . number_format($s10['est']['order_yd'], 2) . ' cubic yards with waste')],
            ],
            '12x12-concrete-slab-cost-breakdown' => [
                ['between \$850 and \$1,700 installed', $L('between ' . Slab_Data::money($s12['installed'][0], 0) . ' and ' . Slab_Data::money($s12['installed'][1], 0) . ' installed')],
            ],
            '20x20-concrete-slab-cost-breakdown' => [
                ['\$2,400 to \$4,800 for a 4-inch patio, and \$2,800 to \$5,400 for a 5-inch garage floor',
                    $L($m0($s20['installed']) . ' for a 4-inch patio, and ' . $m0($s20_5['installed']) . ' for a 5-inch garage floor')],
            ],
            '24x24-concrete-slab-cost-breakdown' => [
                ['between \$3,500 and \$6,900 installed \(\$6\.00 to \$12\.00/sq ft\)',
                    $L('between ' . Slab_Data::money($s24_5['installed'][0], 0) . ' and ' . Slab_Data::money($s24_5['installed'][1], 0) . ' installed (' . $m2([floor($s24_5['installed_psf'][0]), ceil($s24_5['installed_psf'][1])]) . '/sq ft)')],
            ],
            'how-much-does-a-garage-slab-cost' => [
                ['Installed garage slabs cost \$7\.00 to \$14\.00 per square foot', $L('Installed garage slabs cost ' . $m2(Slab_Data::garage_psf()) . ' per square foot')],
            ],
            'how-much-does-a-concrete-driveway-cost' => [
                ['between \$5,600 and \$10,400 installed \(\$7\.00 to \$13\.00/sq ft\) at a mandatory 5-inch thickness',
                    $L('between ' . Slab_Data::money($drive['installed'][0], 0) . ' and ' . Slab_Data::money($drive['installed'][1], 0) . ' installed (' . $m2($drive_psf) . '/sq ft) at the recommended 5-inch thickness')],
            ],
            'how-to-estimate-concrete-labor-cost' => [
                ['\$160 to \$280/hour for a 3-to-4 person', $L($m0($sd['labor']['crew_hour']) . '/hour (' . $m0($sd['labor']['worker_hour']) . ' per finisher) for a 3-to-4 person')],
            ],
            'concrete-cost-per-cubic-yard-pricing-explained' => [
                ['3,000 PSI: \$135' . $D . '\$155/yd³\. 3,500 PSI: \$145' . $D . '\$165/yd³\. 4,000 PSI: \$155' . $D . '\$175/yd³\. 5,000 PSI: \$180' . $D . '\$215/yd³\.', $L(implode(' ', $psi_list))],
            ],
            'concrete-patio-slab-cost-guide' => [
                ['patios cost \$6\.00 to \$10\.00/sq ft installed', $L('patios cost ' . $m2($psf) . '/sq ft installed')],
                ['patios cost \$12\.00 to \$20\.00/sq ft', $L('patios cost ' . $m2(Slab_Data::stamped_psf()) . '/sq ft')],
            ],
            'concrete-foundation-monolithic-slab-cost' => [
                ['slabs cost \$8\.00 to \$16\.00 per square foot', $L('slabs cost ' . $m2($found) . ' per square foot')],
            ],
            'slab-vs-crawl-space-foundation' => [
                ['foundations typically run \$4 to \$14 per square foot', $L('foundations typically run ' . $m0($found) . ' per square foot')],
                ['\$4' . $D . '\$14/sq ft', $L($m0($found, '–') . '/sq ft')],
            ],
            'concrete-vs-asphalt-driveway' => [
                ['concrete driveways run about \$8 to \$20 per square foot, largely depending on',
                    $L('concrete driveways run about ' . $m0($drive_psf) . ' per square foot for a standard 5-inch broom finish and up to about ' . Slab_Data::money($drive_stamped[1], 0) . ' stamped, largely depending on')],
                ['versus \$6,400 to \$16,000 for concrete', $L('versus ' . $m0($drive['installed']) . ' for broom-finished concrete')],
                ['pegs a standard installation at \$5,600 to \$10,400 for the mandatory 5-inch, rebar-reinforced slab most codes require for vehicle loads',
                    $L('uses the same ' . $m0($drive['installed']) . ' for the 5-inch, rebar-reinforced slab we recommend for vehicle loads')],
                ['most codes require a minimum 5-inch slab with reinforcement', $L('a 5-inch slab with reinforcement is the standard recommendation (local codes vary)')],
                ['\$8' . $D . '\$20/sq ft', $L($m0($drive_psf, '–') . '/sq ft (broom), to ' . Slab_Data::money($drive_stamped[1], 0) . ' stamped')],
            ],
            'concrete-slab-permits-and-costs' => [
                ['\$50 ' . $D . ' \$300', $L(Slab_Data::money_range($sd['permit']['flatwork'], 0, ' – '))],
            ],
            'best-time-of-year-to-pour-concrete' => [
                ['many contractors treat 90°F ambient air temperature', $L('many contractors treat ' . $hot . '°F ambient air temperature')],
                ['trending toward or above 90°F', $L('trending toward or above ' . $hot . '°F')],
            ],
            'concrete-curing-time-guide' => [
                ['Hot weather \(above ~90°F\)', $L('Hot weather (above ~' . $hot . '°F)')],
            ],
            'concrete-slab-cracks-causes-and-prevention' => [
                ['typically 6 to 18 hours after the pour', $L('typically ' . Slab_Data::range($j['sawcut_h']) . ' hours after finishing (' . Slab_Data::range($j['early_entry_h']) . ' hours with an early-entry saw)')],
                ['2 to 3 times the slab(\'|’|&#8217;|&#039;)s thickness in feet', '2 to 3 times the slab$1s thickness in inches, expressed in feet'],
            ],
        ];

        return self::$rules;
    }
}

Slab_Post_Figures::init();
