<?php
/**
 * HOMEPAGE TOPIC CLUSTERS
 *
 * Replaces the old "latest 20 posts" grid. The homepage is the pillar page
 * for "concrete slab cost"; each cluster links the strongest pages on one
 * sub-topic so internal links go to the pages that should rank, grouped
 * the way a reader looks for them.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

$u = function ($path) {
    return esc_url(home_url($path));
};
$short_k = function ($n) {
    return $n >= 1000 ? '$' . rtrim(rtrim(number_format($n / 1000, 1), '0'), '.') . 'k' : '$' . number_format($n);
};
$size_links = [];
// The four size articles plus the most searched size pages (inc/size-pages.php).
foreach ([[10, 10, '/10x10-concrete-slab-cost-breakdown/'], [12, 16, null], [20, 20, '/20x20-concrete-slab-cost-breakdown/'], [24, 24, '/24x24-concrete-slab-cost-breakdown/'], [30, 40, null], [40, 60, null]] as $sz) {
    $key = $sz[0] . 'x' . $sz[1];
    $info = Slab_Size_Pages::exists($key) ? Slab_Size_Pages::sizes()[$key] : null;
    $c = Slab_Data::size_costs($sz[0], $sz[1], $info ? $info['t'] : 4);
    $size_links[] = [
        $sz[2] ?: Slab_Size_Pages::path($key),
        $key . ' slab cost',
        $short_k($c['installed'][0]) . '–' . $short_k($c['installed'][1]),
    ];
}

$clusters = [
    [
        'key'   => 'size',
        'title' => 'Cost by Slab Size',
        'desc'  => 'Installed prices, yards and bags for common slab sizes, from hot tub pads to pole barns.',
        'hub'   => ['/concrete-slab-cost-by-size/', 'All slab sizes'],
        'links' => array_merge($size_links, [
            ['/concrete-cost-per-square-foot-pricing-matrix/', 'Cost per square foot', ''],
            ['/concrete-cost-per-cubic-yard-pricing-explained/', 'Cost per cubic yard', ''],
            ['/concrete-slab-cost-by-state/', 'Cost by state', '50 states'],
            ['/concrete-price-report/', '2026 concrete price report', 'Data'],
        ]),
    ],
    [
        'key'   => 'factors',
        'title' => 'What Drives the Price',
        'desc'  => 'The line items that move a slab estimate up or down.',
        'hub'   => ['#concrete-cost-by-psi', 'Cost factor guides below'],
        'links' => [
            ['#concrete-cost-by-psi', 'Concrete cost by PSI', ''],
            ['#concrete-pump-cost', 'Line vs. boom pump cost', ''],
            ['#ready-mix-short-load-fee', 'Short-load fee', ''],
            ['/how-to-estimate-concrete-labor-cost/', 'Labor cost', ''],
            ['/concrete-slab-reinforcement-rebar-vs-wire-mesh/', 'Rebar vs. wire mesh', ''],
            ['/ready-mix-vs-bagged-concrete/', 'Ready-mix vs. bagged', ''],
        ],
    ],
    [
        'key'   => 'calculators',
        'title' => 'Quantity Calculators',
        'desc'  => 'Work out exactly how much concrete, steel and stone to order.',
        'hub'   => ['/calculators/', 'All calculators'],
        'links' => [
            ['/concrete-volume-calculator/', 'Concrete volume', 'yd³ & m³'],
            ['/concrete-bags-calculator/', 'Concrete bags', '40–80 lb'],
            ['/concrete-yard-calculator/', 'Cubic yards', '+waste'],
            ['/concrete-rebar-calculator/', 'Rebar grid', '#3–#5'],
            ['/gravel-base-calculator/', 'Gravel base', 'tons'],
            ['/concrete-footing-calculator/', 'Footings & piers', ''],
            ['/fence-post-concrete-calculator/', 'Fence posts', 'bags/post'],
            ['/concrete-steps-calculator/', 'Concrete steps', ''],
            ['/bags-vs-ready-mix-calculator/', 'Bags vs. ready-mix', 'break-even'],
        ],
    ],
    [
        'key'   => 'projects',
        'title' => 'Project Types',
        'desc'  => 'Thickness, reinforcement and cost for each kind of slab.',
        'hub'   => ['/patio/', 'Application guides'],
        'links' => [
            ['/patio/', 'Patio slabs', ''],
            ['/driveway/', 'Driveways', ''],
            ['/garage/', 'Garage floors', ''],
            ['/shed/', 'Shed pads', ''],
            ['/foundation/', 'Monolithic foundations', ''],
            ['/concrete-vs-asphalt-driveway/', 'Concrete vs. asphalt', ''],
        ],
    ],
    [
        'key'   => 'plan',
        'title' => 'Plan & Check',
        'desc'  => 'Check a contractor\'s price and get the pour right.',
        'hub'   => ['/concrete-quote-checker/', 'Concrete quote checker'],
        'links' => [
            ['/concrete-quote-checker/', 'Is my concrete quote fair?', 'Tool'],
            ['/concrete-quote-checker/#compare-quotes', 'Compare 2-3 quotes', 'Tool'],
            ['/best-time-of-year-to-pour-concrete/', 'Best time to pour', ''],
            ['#control-joint-spacing', 'Control joint spacing', ''],
            ['/concrete-curing-time-guide/', 'Curing time', ''],
            ['/concrete-slab-permits-and-costs/', 'Permits & costs', ''],
            ['/yard-prep-checklist-for-a-slab-pour/', 'Yard prep checklist', ''],
        ],
    ],
];
?>

<section id="concrete-topics" class="topic-clusters" aria-labelledby="concrete-topics-title">
  <div class="topic-clusters-head">
    <div>
      <span class="topic-eyebrow">Knowledgebase &amp; Tools</span>
      <h2 id="concrete-topics-title">Concrete Estimating &amp; Cost Guides</h2>
    </div>
    <a href="<?php echo $u('/knowledgebase/'); ?>" class="topic-all-link">View All Guides &rarr;</a>
  </div>

  <div class="topic-cluster-grid">
    <?php foreach ($clusters as $cl) : ?>
    <div class="topic-cluster topic-<?php echo esc_attr($cl['key']); ?>">
      <h3 class="topic-cluster-title"><?php echo esc_html($cl['title']); ?></h3>
      <p class="topic-cluster-desc"><?php echo esc_html($cl['desc']); ?></p>
      <ul class="topic-cluster-links">
        <?php foreach ($cl['links'] as $l) : ?>
        <li>
          <a href="<?php echo strpos($l[0], '#') === 0 ? esc_attr($l[0]) : $u($l[0]); ?>"><?php echo esc_html($l[1]); ?></a>
          <?php if ($l[2] !== '') : ?><span class="topic-badge"><?php echo esc_html($l[2]); ?></span><?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
      <a class="topic-cluster-hub" href="<?php echo strpos($cl['hub'][0], '#') === 0 ? esc_attr($cl['hub'][0]) : $u($cl['hub'][0]); ?>"><?php echo esc_html($cl['hub'][1]); ?> &rarr;</a>
    </div>
    <?php endforeach; ?>
  </div>
</section>
