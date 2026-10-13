<?php
/**
 * 2026 CONCRETE PRICE REPORT — /concrete-price-report/ and its CSV
 *
 * One page that sets out every price the site uses, the installed costs
 * they add up to, and how they are derived, with the same figures as a
 * downloadable CSV (/concrete-price-report.csv) so other sites and AI
 * answers can cite one stable source. Every number comes from Slab_Data.
 *
 * The figures are modelled estimates, not survey results, and the page
 * says so. Nothing here claims a sample size, a survey or a data source
 * the site does not have.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_Price_Report {

    const YEAR = '2026';

    public static function init() {
        // Before redirect_canonical (priority 10), so the .csv URL is never
        // given a trailing slash.
        add_action('template_redirect', [__CLASS__, 'serve_csv'], 0);
    }

    public static function path() {
        return '/concrete-price-report/';
    }

    public static function csv_path() {
        return '/concrete-price-report.csv';
    }

    public static function title() {
        return self::YEAR . ' Concrete Price Report: U.S. Slab Costs';
    }

    public static function meta() {
        return sprintf(
            '%s U.S. concrete prices: ready-mix %s/yd³, slabs %s/sq ft installed, labor, pumps, bags and all 50 states. Free CSV, method explained.',
            self::YEAR,
            Slab_Data::mr('concrete.psi_base_price', 0, '–'),
            Slab_Data::money_range(Slab_Data::installed_psf(), 0, '–')
        );
    }

    /** Pricing groups shown on the page and written to the CSV: [section, item, unit, low, high, typical]. */
    public static function rows() {
        $d = Slab_Data::all();
        $rows = [];
        $add = function ($section, $item, $unit, $pair, $typical = null) use (&$rows) {
            $rows[] = [$section, $item, $unit, $pair[0], $pair[1], $typical];
        };

        foreach (array_keys($d['concrete']['psi_grades']) as $psi) {
            $add('Ready-mix concrete', number_format($psi) . ' PSI', 'per cubic yard, before delivery', Slab_Data::psi_price($psi), $psi === $d['concrete']['psi_base'] ? $d['concrete']['ready_mix_default'] : null);
        }
        $add('Ready-mix concrete', 'Delivery fee', 'per load', [$d['delivery']['fee_default'], $d['delivery']['fee_default']], $d['delivery']['fee_default']);
        $add('Ready-mix concrete', 'Short-load fee (flat)', 'per load under the plant minimum', $d['delivery']['short_load_flat']);
        $add('Ready-mix concrete', 'Short-load fee (per yard)', 'per missing cubic yard', $d['delivery']['short_load_per_yd']);
        $add('Ready-mix concrete', 'Fiber admixture', 'per cubic yard', $d['concrete']['admixture_yd']['fiber']);
        $add('Ready-mix concrete', 'Accelerator', 'per cubic yard', $d['concrete']['admixture_yd']['accelerator']);
        $add('Ready-mix concrete', 'Hot water (winter)', 'per cubic yard', $d['concrete']['admixture_yd']['hot_water']);

        $add('Bagged concrete', '80-lb bag', 'per bag', $d['concrete']['bag_price_range'], $d['concrete']['bag_price_default']);
        foreach ($d['concrete']['bag_price_sizes'] as $size => $price) {
            $add('Bagged concrete', $size . '-lb bag', 'per bag', [$price, $price], $price);
        }
        $add('Bagged concrete', 'Drum mixer rental', 'per day', $d['concrete']['mixer_rental_day']);

        $add('Labor', 'Place and finish a slab', 'per sq ft', $d['labor']['sqft_range'], $d['labor']['sqft_default']);
        $add('Labor', 'Finisher', 'per hour', $d['labor']['worker_hour']);
        $add('Labor', 'Crew of ' . Slab_Data::range($d['labor']['crew_size'], '-'), 'per hour', $d['labor']['crew_hour'], $d['labor']['crew_hour_default']);

        $add('Concrete pump', 'Line pump', 'per residential job', $d['pump']['line_job']);
        $add('Concrete pump', 'Boom pump', 'per residential job', $d['pump']['boom_job']);
        $add('Concrete pump', 'Line pump hourly', 'per hour', $d['pump']['line_rate']);
        $add('Concrete pump', 'Boom pump hourly', 'per hour', $d['pump']['boom_rate']);

        $add('Site work', 'Excavation and grading', 'per sq ft', $d['site']['excavation_range'], $d['site']['excavation_default']);
        $add('Site work', 'Gravel base', 'per cubic yard', $d['site']['gravel_range'], $d['site']['gravel_default']);
        $add('Site work', 'Gravel base', 'per ton', $d['site']['gravel_ton_range']);
        $add('Site work', 'Forms', 'per linear ft', $d['site']['forms_range'], $d['site']['forms_default']);
        $add('Site work', 'Vapor barrier', 'per sq ft', $d['site']['vapor_range'], $d['site']['vapor_default']);
        $add('Site work', 'Old slab removal (4 in)', 'per sq ft', $d['site']['demo_range'], $d['site']['demo_default']);
        $add('Site work', 'Permit (small residential slab)', 'per permit', $d['permit']['flatwork']);

        $add('Reinforcement', '#4 rebar grid at 18 in', 'per sq ft', $d['reinforcement']['rebar_range'], $d['reinforcement']['rebar_default']);
        $add('Reinforcement', '6x6 welded wire mesh', 'per sq ft', $d['reinforcement']['mesh_range'], $d['reinforcement']['mesh_default']);
        $add('Reinforcement', 'Wire mesh sheet 5 x 10 ft', 'per sheet', $d['reinforcement']['mesh_sheet_price']);

        foreach (['broom' => 'Broom finish', 'trowel' => 'Smooth trowel finish', 'exposed' => 'Exposed aggregate', 'stamped' => 'Stamped finish'] as $k => $label) {
            $add('Finishes', $label, 'per sq ft', $d['finish'][$k]['range'], $d['finish'][$k]['default']);
        }
        $add('Finishes', 'Sealer', 'per sq ft', $d['finish']['sealer_psf']);

        $add('Installed cost', 'Concrete slab, 4 in (20 x 20 ft)', 'per sq ft', Slab_Data::installed_psf());
        $add('Installed cost', 'Concrete driveway, 5 in (20 x 40 ft)', 'per sq ft', Slab_Data::driveway_psf());
        $add('Installed cost', 'Garage floor, 5 in (24 x 24 ft)', 'per sq ft', Slab_Data::garage_psf());
        $add('Installed cost', 'Stamped patio, 4 in (20 x 20 ft)', 'per sq ft', Slab_Data::stamped_psf());
        $add('Installed cost', 'Monolithic slab foundation (30 x 40 ft)', 'per sq ft', Slab_Data::foundation_psf());
        $add('Installed cost', 'Concrete steps, 4 ft wide', 'per step', $d['steps']['installed_per_step']);
        $add('Installed cost', 'DIY slab materials, 4 in (20 x 20 ft)', 'per sq ft', Slab_Data::diy_psf());
        $add('Installed cost', 'Asphalt driveway (for comparison)', 'per sq ft', $d['asphalt_driveway_psf']);

        return $rows;
    }

    /** Installed price per slab size, for the page and the CSV. */
    public static function size_rows() {
        return Slab_Size_Pages::hub_rows();
    }

    /** Short, quotable findings, each computed from Slab_Data. */
    public static function findings() {
        $d = Slab_Data::all();
        $e = Slab_Data::estimate(20, 20, 4, 'full');
        $small = Slab_Data::size_costs(10, 8, 4);
        $big = Slab_Data::size_costs(60, 40, 4);
        $r = Slab_State_Pages::ranked();
        $hi = $r[0];
        $lo = end($r);
        $hi_c = Slab_Data::size_costs(20, 20, 4, $hi['index']);
        $lo_c = Slab_Data::size_costs(20, 20, 4, $lo['index']);
        $ties = array_filter($r, function ($s) use ($hi) {
            return $s['index'] === $hi['index'];
        });
        $hi_names = implode(' and ', array_column($ties, 'name'));
        $concrete_share = ($e['ready_mix']) / $e['direct'];
        return [
            sprintf('A 4-inch concrete slab costs about %s per sq ft installed and %s per sq ft in DIY materials at U.S. average prices.', Slab_Data::money_range(Slab_Data::installed_psf(), 0, ' to '), Slab_Data::money_range(Slab_Data::diy_psf(), 2, ' to ')),
            sprintf('Ready-mix concrete at 3,000 PSI costs about %s per cubic yard before delivery; each 500-PSI step up adds %s per yard.', Slab_Data::mr('concrete.psi_base_price', 0, ' to '), Slab_Data::mr('concrete.psi_step_price', 0, ' to ')),
            sprintf('Labor is the biggest line item: on a 20 x 20 ft slab it is %d%% of the direct cost, against %d%% for the concrete itself including delivery.', round($e['labor'] / $e['direct'] * 100), round($concrete_share * 100)),
            sprintf('Small slabs cost more per square foot: about %s for an 8 x 10 ft slab against %s for a 40 x 60 ft slab, because delivery and setup are spread over less area.', Slab_Data::money_range(Slab_Data::round_out($small['installed_psf'], 0.5), 2, ' to '), Slab_Data::money_range(Slab_Data::round_out($big['installed_psf'], 0.5), 2, ' to ')),
            sprintf('Bagged concrete is cheaper than a ready-mix truck only below about %s cubic yards (with a rented mixer), roughly a 4-inch slab of %d sq ft.', Slab_Data::num(Slab_Data::break_even_yd(true)), (int) round(Slab_Data::break_even_yd(true) * 27 * 12 / 4 / 10) * 10),
            sprintf('Location matters: the same 20 x 20 ft slab runs about %s in %s and %s in %s, about %d%% more.', Slab_Data::money_range($lo_c['installed'], 0, ' to '), $lo['name'], Slab_Data::money_range($hi_c['installed'], 0, ' to '), $hi_names, round((array_sum($hi_c['installed']) / array_sum($lo_c['installed']) - 1) * 100)),
            sprintf('A stamped finish raises an installed patio to about %s per sq ft, against %s for a plain broom finish.', Slab_Data::money_range(Slab_Data::stamped_psf(), 0, ' to '), Slab_Data::money_range(Slab_Data::installed_psf(), 0, ' to ')),
        ];
    }

    public static function citation() {
        return sprintf('SlabCostCalculator.com, "%s Concrete Price Report", %s, %s', self::YEAR, wp_date('F Y', strtotime(SLAB_CONTENT_UPDATED . ' 12:00:00')), home_url(self::path()));
    }

    public static function faqs() {
        $d = Slab_Data::all();
        return [
            ['q' => 'How much does concrete cost per yard in ' . self::YEAR . '?', 'a' => sprintf('About %s per cubic yard for 3,000 PSI ready-mix at U.S. average prices, before a delivery fee of about %s and any short-load fee (%s) on small orders. 4,000 PSI runs about %s.', Slab_Data::mr('concrete.psi_base_price', 0, ' to '), Slab_Data::money($d['delivery']['fee_default']), Slab_Data::mr('delivery.short_load_flat', 0, ' to '), Slab_Data::money_range(Slab_Data::psi_price(4000), 0, ' to '))],
            ['q' => 'How much does a concrete slab cost per square foot in ' . self::YEAR . '?', 'a' => sprintf('About %s per sq ft installed for a 4-inch slab, %s for a 5-inch driveway and %s for a 5-inch garage floor. DIY materials run about %s per sq ft.', Slab_Data::money_range(Slab_Data::installed_psf(), 0, ' to '), Slab_Data::money_range(Slab_Data::driveway_psf(), 0, ' to '), Slab_Data::money_range(Slab_Data::garage_psf(), 0, ' to '), Slab_Data::money_range(Slab_Data::diy_psf(), 2, ' to '))],
            ['q' => 'Where do these prices come from?', 'a' => 'They are modelled estimates. Our editors set typical U.S. unit prices for each item (ready-mix, labor, rebar, forms and so on) and the installed costs are calculated from those, with the same line items as the calculator. They are not the results of a survey of contractors or suppliers.'],
            ['q' => 'Can I use these figures in my article or tool?', 'a' => 'Yes. The data is free to reuse under the Creative Commons Attribution 4.0 license: credit SlabCostCalculator.com and link to this report. A suggested citation is on this page.'],
            ['q' => 'How often is the report updated?', 'a' => 'When the prices in our cost model change. The date at the top of the page and in the CSV shows the last update; the calculator, size pages and state pages always use the same figures as this report.'],
        ];
    }

    /** /concrete-price-report.csv */
    public static function serve_csv() {
        if (get_query_var('slab_route') !== 'price_report_csv') {
            return;
        }
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: inline; filename="concrete-price-report-' . self::YEAR . '.csv"');
        header('Link: <' . home_url(self::path()) . '>; rel="canonical"');
        header('X-Robots-Tag: noindex');
        echo self::csv();
        exit;
    }

    public static function csv() {
        $out = fopen('php://temp', 'w+');
        $put = function ($row) use ($out) {
            fputcsv($out, $row, ',', '"', '');
        };
        $num = function ($n) {
            return $n === null ? '' : rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
        };
        $put(['# ' . self::YEAR . ' Concrete Price Report, SlabCostCalculator.com. Prices last reviewed ' . SLAB_CONTENT_UPDATED . '. Modelled U.S. average estimates in USD, not survey data. Method: ' . home_url(self::path()) . '#method. License: CC BY 4.0, credit SlabCostCalculator.com with a link.']);
        $put(['section', 'item', 'unit', 'low_usd', 'high_usd', 'typical_usd']);
        foreach (self::rows() as $r) {
            $put([$r[0], $r[1], $r[2], $num($r[3]), $num($r[4]), $num($r[5])]);
        }
        foreach (self::size_rows() as $r) {
            $put(['Installed cost by slab size', $r['size'] . ' ft slab, ' . $r['t'] . ' in', 'per slab', $num($r['installed'][0]), $num($r['installed'][1]), '']);
        }
        foreach (Slab_State_Pages::hub_rows() as $r) {
            $put(['Installed cost by state', $r['name'] . ' (location factor ' . number_format($r['index'], 2) . '), 4 in slab', 'per sq ft', $num($r['psf'][0]), $num($r['psf'][1]), '']);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);
        return $csv;
    }
}

Slab_Price_Report::init();
