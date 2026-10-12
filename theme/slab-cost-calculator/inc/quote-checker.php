<?php
/**
 * CONCRETE QUOTE CHECKER (/concrete-quote-checker/)
 *
 * Figures for the quote checker page and its FAQ schema. The grading rule
 * is the Bid Check tab's (assets/js/calculator-tools.js, bidBenchmark()):
 *   benchmark = materials at the calculator's default prices + labor at the
 *               standard $/sq ft rate (U.S. average), no overhead or profit
 *   fair high = benchmark x (1 + overhead_profit)
 *   over line = fair high x 1.30
 * Every number comes from Slab_Data, so the page, the FAQ schema and the
 * tool agree.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Benchmark band for a slab with the calculator's default scope. */
function slab_quote_benchmark($l, $w, $t = 4) {
    $c = Slab_Data::size_costs($l, $w, $t);
    $e = $c['est'];
    $fair = $c['bid_fair'][0];
    $high = $c['bid_fair'][1];
    return [
        'l'         => $l,
        'w'         => $w,
        't'         => $t,
        'area'      => $e['area'],
        'materials' => $fair - $e['labor'],
        'labor'     => $e['labor'],
        'fair'      => $fair,
        'high'      => $high,
        'over'      => $high * 1.30,
    ];
}

/** The Bid Check tab's verdict for a quote against a benchmark band. */
function slab_quote_tier($quote, $b) {
    if ($quote < $b['materials']) {
        return 'Below material cost: check the scope';
    }
    if ($quote <= $b['fair']) {
        return 'Great deal / budget-friendly';
    }
    if ($quote <= $b['high']) {
        return 'Fair market price';
    }
    if ($quote <= $b['over']) {
        return 'Premium / elevated cost';
    }
    return 'Overpriced / high markup risk';
}

/** Visible FAQ on the page and the FAQPage schema (inc/schema-generator.php). */
function slab_quote_checker_faqs() {
    $b = slab_quote_benchmark(20, 20);
    $psf = Slab_Data::installed_psf();
    $op = (int) round(Slab_Data::get('overhead_profit') * 100);
    $m = function ($n) {
        return Slab_Data::money(round($n / 10) * 10, 0);
    };
    return [
        [
            'q' => 'How do I know if a concrete quote is fair?',
            'a' => sprintf('Compare it with the direct cost of the same job: materials at local prices plus standard labor. A fair residential quote usually sits between that direct cost and about %d%% above it for the contractor\'s overhead and profit. For a 20 x 20 ft, 4-inch slab with forms, a vapor barrier, #4 rebar and a broom finish, that is about %s to %s at U.S. average prices.', $op, $m($b['fair']), $m($b['high'])),
        ],
        [
            'q' => 'How much should a contractor charge per square foot for a concrete slab?',
            'a' => sprintf('Most installed residential slabs cost %s per square foot. Small slabs cost more per square foot because delivery, forms and setup are spread over less area, and decorative finishes, thicker slabs, a pump or demolition add to it.', Slab_Data::money_range($psf, 0, ' to ')),
        ],
        [
            'q' => 'Why is my concrete quote so high?',
            'a' => 'Common reasons are hard access that needs a pump or wheelbarrows, a small job that triggers a short-load fee or minimum charge, demolition of an old slab, a thicker slab or stronger mix, a stamped or decorative finish, and a busy season. A quote more than about 30% above the high end of the fair range is worth questioning: ask for an itemized bid and get at least two more quotes.',
        ],
        [
            'q' => 'Is a very low concrete quote a red flag?',
            'a' => 'It can be. If a quote is less than the materials alone would cost, something is usually missing: the gravel base, rebar or mesh, forms, finishing, cleanup, or the permit. Get the scope in writing and check the contractor\'s license, insurance and references before you pay a deposit.',
        ],
        [
            'q' => 'What should a concrete slab quote include?',
            'a' => 'The slab size and thickness, concrete strength (PSI) and volume, site prep and gravel base depth, forms, vapor barrier if any, reinforcement type and spacing, finish, control joints, curing, delivery or pump charges, demolition and haul-off if needed, permits, cleanup, the payment schedule, and the warranty.',
        ],
    ];
}
