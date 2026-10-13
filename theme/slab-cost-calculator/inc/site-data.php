<?php
/**
 * SITE DATA: THE ONE SOURCE FOR EVERY FIGURE ON THE SITE
 *
 * Prices, bag yields, waste, PSI pricing, joint spacing, saw-cut timing,
 * lap splice, weather limits, permit fees and every per-size or per-sq-ft
 * cost live here. Templates, FAQs, schema, llms.txt and the calculators
 * (through window.SLAB_DATA, see slab_enqueue_assets()) all read from this
 * file, so a figure changed here changes everywhere at once.
 *
 * Size and per-sq-ft costs are not typed in. They are computed with the
 * same line items the calculator uses (Slab_Data::estimate()), so the
 * tables and FAQs always agree with the calculator and the Bid Checker.
 *
 * When prices change, also update SLAB_CONTENT_UPDATED in functions.php.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_Data {

    private static $cache = null;

    /** Raw figures. Ranges are [low, high]. */
    public static function all() {
        if (self::$cache !== null) {
            return self::$cache;
        }

        self::$cache = [
            'concrete' => [
                'ready_mix_default' => 150.00,     // $/yd³, calculator default (3,000 PSI)
                'psi_base' => 3000,
                'psi_base_price' => [135, 160],    // $/yd³ for 3,000 PSI
                'psi_step' => 500,
                'psi_step_price' => [4, 10],       // $/yd³ added per 500-PSI step
                'psi_grades' => [
                    2500 => ['use' => 'Footings and interior slabs not exposed to weather', 'metric' => '~M15–M17'],
                    3000 => ['use' => 'Patios, walkways, shed floors in mild climates', 'metric' => '~M20'],
                    3500 => ['use' => 'Garage floors, driveways, porches and steps in freeze-thaw areas', 'metric' => '~M22–M25'],
                    4000 => ['use' => 'Driveways that see de-icing salt, RV and truck pads', 'metric' => '~M25–M28'],
                    4500 => ['use' => 'Heavy equipment, shop floors with forklifts, commercial aprons', 'metric' => '~M30'],
                    5000 => ['use' => 'Heavy-duty shop floors and structural foundations', 'metric' => '~M30–M35'],
                ],
                'bag_price_default' => 6.50,       // $ per 80-lb bag
                'bag_price_range' => [6.00, 8.00],
                'bag_yields' => ['80' => 0.60, '60' => 0.45, '50' => 0.375, '40' => 0.30], // ft³ per bag
                'bag_price_sizes' => ['60' => 5.25, '50' => 4.75, '40' => 3.95], // $ per bag, typical big-box price for the smaller sizes (80-lb is bag_price_default)
                'waste_pct' => 10,
                'waste_pct_range' => [5, 15],      // % extra: laser-flat grade and rigid forms (low) to rough or irregular grade (high)
                'density_lb_ft3' => 145,
                'cured_weight_factor' => 0.97,     // cured weight ≈ 97% of wet (bleed-water loss)
                'air_entrainment_pct' => [5, 7],
                // Site-mixed concrete (mix ratio calculator): cement : sand : stone by volume.
                'mix_ratios' => [
                    '1:1.5:3' => 'Rich mix for thin sections, steps and repairs',
                    '1:2:3' => 'Standard mix for slabs, driveways and posts',
                    '1:2:4' => 'General mix for footings and light slabs',
                    '1:3:6' => 'Lean fill under slabs, not for wearing surfaces',
                ],
                'dry_volume_factor' => 1.54,       // dry ingredients shrink ~54% into the wet mix
                'cement_bag_lb' => 94,             // US portland cement bag
                'cement_bag_ft3' => 1.0,           // loose volume of one 94-lb bag
                'cement_bag_price' => [15, 20],    // $ per 94-lb bag, Type I/II
                'sand_tons_per_yd' => 1.35,        // dry concrete sand, loose
                'sand_price_yd' => [30, 50],       // $/yd³ delivered in bulk
                'water_cement_ratio' => 0.5,       // by weight, typical for slabs
                'waste_levels' => [
                    5 => 'Rigid forms on a flat, well-compacted base',
                    10 => 'Typical formed slab (recommended)',
                    15 => 'Uneven or soft subgrade, curves, many edges',
                    20 => 'Dug trenches, footings, rough ground',
                ],
                'bag_psi' => [3500, 4000],         // 28-day PSI: ASTM C387 normal-strength minimum, and the usual rating on general-purpose bag mix
                'water_qt_per_80lb' => 3,          // quarts of water per 80-lb bag, typical maker's direction
                'mixer_rental_day' => [50, 100],   // $ per day, rented electric drum mixer
                'admixture_yd' => ['fiber' => [8, 15], 'accelerator' => [5, 10], 'hot_water' => [5, 10]], // $/yd³ add-ons: synthetic fiber, non-chloride or calcium accelerator, winter hot water
            ],
            'code' => [
                // IRC Table R402.2 minimums
                'psi_protected' => 2500,
                'psi_exposed' => 3000,
                'psi_severe' => 3500,              // air-entrained, garage floors, porches, steps
                'psi_deicing_recommended' => 4000, // where de-icing salt is used
            ],
            'site' => [
                'excavation_default' => 1.50,
                'excavation_range' => [1.00, 2.50], // $/sq ft
                'gravel_default' => 45.00,
                'gravel_range' => [35, 55],         // $/yd³
                'gravel_ton_range' => [25, 40],     // $/ton
                'gravel_depth_in' => 4,
                'gravel_depth_range' => [4, 6],
                'gravel_compaction' => 1.15,
                'gravel_tons_per_yd' => 1.4,
                'gravel_lift_in' => [4, 6],         // max loose lift per pass with a walk-behind plate compactor
                'forms_default' => 2.20,
                'forms_range' => [1.50, 3.00],      // $/lin ft
                'vapor_default' => 0.35,
                'vapor_range' => [0.25, 0.45],      // $/sq ft
                'demo_default' => 3.50,
                'demo_range' => [3, 4],             // $/sq ft, 4" unreinforced slab
                'demo_rate' => 3.25,                // removal calculator base $/sq ft, 4" unreinforced
                'demo_per_inch' => 0.50,            // + $/sq ft per inch over 4"
                'demo_rebar' => 1.00,               // + $/sq ft when the slab has rebar or mesh
                'drainage_slope_in_per_ft' => ['1/8', '1/4'],
                'drainage_slope_preferred' => '1/4',
                'chute_reach_ft' => [15, 20],
                'stake_spacing_ft' => [3, 4],
                'form_board_lengths_ft' => [8, 10, 12, 16],
                // Actual depth of dimensional lumber used as slab forms.
                'form_board_depth_in' => ['2x4' => 3.5, '2x6' => 5.5, '2x8' => 7.25, '2x10' => 9.25, '2x12' => 11.25],
            ],
            'reinforcement' => [
                'rebar_default' => 1.25,            // $/sq ft, #4 @ 18 in with chairs
                'rebar_range' => [1.00, 1.75],
                'rebar_base_spacing_in' => 18,
                'rebar_tight_spacing_in' => 12,     // closer #4 grid for heavy or cold-climate driveways
                'mesh_default' => 0.50,
                'mesh_range' => [0.40, 0.70],
                'lap_bar_diameters' => 40,
                'lap_in_no4' => 20,                 // 40 × 0.5 in
                'splice_allowance' => 0.10,
                'edge_cover_in' => 3,
                'chair_grid_ft' => [3, 4],
                'stick_length_ft' => 20,
                'mesh_sheet_ft' => [5, 10],         // 6x6 W1.4 sheet
                'mesh_roll_ft' => [5, 150],         // 6x6 W1.4 roll (750 sq ft)
                'mesh_overlap_in' => 6,             // lap one full grid square at sheet edges
                'mesh_sheet_price' => [12, 20],     // $ per 5x10 sheet
                'mesh_roll_price' => [180, 300],    // $ per 5x150 roll
            ],
            'finish' => [
                'broom' => ['default' => 0.75, 'range' => [0.50, 0.80]],
                'trowel' => ['default' => 1.25, 'range' => [1.00, 1.50]],
                'exposed' => ['default' => 3.50, 'range' => [2.50, 4.50]],
                'stamped' => ['default' => 7.50, 'range' => [6.00, 12.00]],
                'sealer_psf' => [1.00, 3.00],       // $/sq ft installed, penetrating or acrylic sealer (matches the sealer guide)
            ],
            'labor' => [
                'sqft_default' => 5.00,
                'sqft_range' => [3.50, 7.00],       // $/sq ft, placing and finishing
                'sqft_stamped' => 10.00,
                'sqft_difficult' => 7.50,
                'worker_hour' => [40, 70],          // $ per finisher-hour
                'crew_size' => [3, 4],
                'crew_hour' => [120, 280],          // $ per crew-hour (3-4 finishers)
                'crew_hour_default' => 140.00,
                'crew_hours_per_sqft' => 0.035,
                'crew_hours_per_sqft_stamped' => 0.06,
                'crew_hours_per_sqft_difficult' => 0.05,
                'bags_per_hour_mixer' => [20, 30],  // 80-lb bags a 3-person crew with one drum mixer can mix and place per hour
            ],
            'delivery' => [
                'fee_default' => 120.00,
                'short_load_flat' => [80, 200],
                'short_load_per_yd' => [30, 45],
                'min_load_yd' => [4, 10],
                'common_min_yd' => [5, 6],
                'truck_capacity_yd' => [8, 10],
                'surcharge' => [10, 50],
                'unload_min_per_yd' => [5, 8],
                'bags_win_below_yd' => 1,
                'ready_mix_wins_above_yd' => 1.5,
                'order_step_yd' => [0.25, 0.5],     // plants sell in quarter- or half-yard steps
                'discharge_min' => 90,              // ASTM C94: discharge within 90 minutes of batching unless the buyer waives it
            ],
            'pump' => [
                'default' => 650.00,
                'typical' => [600, 1200],
                'line_rate' => [100, 175],
                'line_min_h' => [2, 4],
                'line_job' => [400, 800],
                'line_reach_ft' => [150, 300],
                'boom_rate' => [150, 350],
                'boom_min_h' => [3, 4],
                'boom_job' => [900, 1800],
                'boom_reach_ft' => [75, 200],
                'pump_mix_extra' => [5, 10],
            ],
            'equipment_default' => 150.00,
            'contingency_pct' => 5,
            'overhead_profit' => 0.25,             // Bid Checker high end = benchmark × 1.25
            'joints' => [
                'ratio' => [2, 3],                  // × thickness (in) = spacing (ft)
                'ratio_best' => [2, 2.5],
                'ratio_blueprint' => 2.5,
                'max_ft' => [4 => 12, 5 => 15, 6 => 15],
                'cap_ft' => 15,
                'depth_fraction' => 0.25,
                'max_aspect' => 1.5,
                'sawcut_h' => [4, 12],              // conventional wet saw, after finishing
                'early_entry_h' => [1, 4],
                'saw_rental_day' => [60, 120],
                'cut_price_lf' => [1, 2],
            ],
            'weather' => [
                'cold_f' => 40,                     // ACI 306R cold-weather concreting
                'cool_f' => 50,
                'hot_f' => 85,                      // caution level for hot-weather practice
                'concrete_temp_limit_f' => [90, 95],
                'evap_limit_lb' => 0.2,
            ],
            'curing' => [
                'foot_traffic_h' => [24, 48],
                'cars_days' => 7,
                'full_days' => 28,
            ],
            'permit' => [
                'flatwork' => [50, 350],            // small residential slab/driveway permit
            ],
            'monolithic' => [
                'edge_width_in' => 12,
                'edge_depth_in' => [12, 18],
                'edge_bars' => 2,
                'edge_rebar' => [100, 150],         // 2 × #4 around a 24×24 slab
                'trenching' => ['12' => [150, 400], '18' => [250, 600]],
                'stem_wall_form_sfca' => [3, 6],    // $/sq ft of form contact area: form, place and strip a short poured stem wall (concrete extra)
                'frost_trench_lf' => [8, 15],       // $/lin ft to machine-dig a footing trench to frost depth and backfill
            ],
            // Calculator quick presets: [length, width, thickness in, label].
            'presets' => [
                'patio' => [12, 12, 4, 'Patio (12x12, 4")'],
                'walkway' => [30, 4, 4, 'Walkway (4x30, 4")'],
                'driveway' => [40, 20, 5, 'Driveway (20x40, 5")'],
                'garage' => [24, 24, 5, '2-Car Garage (24x24, 5")'],
                'shed' => [10, 10, 4, 'Shed Pad (10x10, 4")'],
                'foundation' => [30, 40, 6, 'Foundation (30x40, 6")'],
                'pad' => [8, 8, 4, 'Equipment Pad (8x8, 4")'],
            ],
            'freeze_thaw_ft' => [36, 48],
            'asphalt_driveway_psf' => [3, 7],      // installed, 2-3 in asphalt on gravel base
            // Fence and deck post holes (fence post calculator).
            'posts' => [
                // Post size => [label, cross-section shape, width or diameter in inches (actual)].
                'sizes' => [
                    '4x4' => ['4x4 wood post (3.5 in)', 'square', 3.5],
                    '6x6' => ['6x6 wood post (5.5 in)', 'square', 5.5],
                    '4x6' => ['4x6 wood post (3.5 × 5.5 in)', 'rect', [3.5, 5.5]],
                    'pipe2' => ['2-3/8 in steel pipe (chain link line post)', 'round', 2.375],
                    'pipe3' => ['2-7/8 in steel pipe (chain link terminal post)', 'round', 2.875],
                    'pipe4' => ['4 in steel or round wood post', 'round', 4],
                ],
                'hole_factor' => 3,                 // hole diameter ≈ 3 × post width
                'chain_link_hole_in' => ['pipe2' => 8, 'pipe3' => 10], // line posts, and the bigger hole for terminal (corner, end, gate) posts
                'depth_fraction' => [0.33, 0.5],    // set 1/3 to 1/2 of the post's above-ground height
                'gravel_in' => 6,                   // drainage gravel under the post
                'fast_set_lb' => 50,
                'fast_set_yield' => 0.375,          // ft³ per 50-lb bag of fast-setting mix
                'fast_set_price' => 8.50,           // $ per 50-lb bag
                'fast_set_minutes' => [20, 40],     // set time before the next step (bracing off, fence boards on after ~4 h)
                'fast_set_hold_h' => 4,
            ],
            // Location factors (U.S. national average = 1.00) for the
            // calculator's location selector, the state pages and the price
            // report. Approximate figures set by the site's editors for
            // typical construction cost differences, weighted toward each
            // state's main metro areas; not measured prices for each place.
            // Picking a location scales ready-mix ($/yd³), excavation and
            // labor; other materials stay at national prices.
            'locations' => [
                // key => [name, factor, state code]
                'metros' => [
                    'nyc' => ['New York City metro, NY', 1.35, 'NY'],
                    'sf' => ['San Francisco Bay Area, CA', 1.35, 'CA'],
                    'la' => ['Los Angeles metro, CA', 1.20, 'CA'],
                    'bos' => ['Boston metro, MA', 1.22, 'MA'],
                    'chi' => ['Chicago metro, IL', 1.22, 'IL'],
                    'sea' => ['Seattle metro, WA', 1.12, 'WA'],
                    'dc' => ['Washington, DC metro', 1.05, 'DC'],
                    'den' => ['Denver metro, CO', 0.97, 'CO'],
                    'hou' => ['Houston metro, TX', 0.87, 'TX'],
                    'dal' => ['Dallas-Fort Worth, TX', 0.88, 'TX'],
                    'atl' => ['Atlanta metro, GA', 0.90, 'GA'],
                    'mia' => ['Miami metro, FL', 0.92, 'FL'],
                    'phx' => ['Phoenix metro, AZ', 0.90, 'AZ'],
                ],
                // code => [name, factor]
                'states' => [
                    'AL' => ['Alabama', 0.86], 'AK' => ['Alaska', 1.22], 'AZ' => ['Arizona', 0.90],
                    'AR' => ['Arkansas', 0.85], 'CA' => ['California', 1.25], 'CO' => ['Colorado', 0.95],
                    'CT' => ['Connecticut', 1.12], 'DE' => ['Delaware', 1.03], 'DC' => ['District of Columbia', 1.05],
                    'FL' => ['Florida', 0.88], 'GA' => ['Georgia', 0.88], 'HI' => ['Hawaii', 1.25],
                    'ID' => ['Idaho', 0.92], 'IL' => ['Illinois', 1.10], 'IN' => ['Indiana', 0.93],
                    'IA' => ['Iowa', 0.94], 'KS' => ['Kansas', 0.90], 'KY' => ['Kentucky', 0.90],
                    'LA' => ['Louisiana', 0.86], 'ME' => ['Maine', 0.95], 'MD' => ['Maryland', 0.98],
                    'MA' => ['Massachusetts', 1.18], 'MI' => ['Michigan', 0.98], 'MN' => ['Minnesota', 1.05],
                    'MS' => ['Mississippi', 0.84], 'MO' => ['Missouri', 0.98], 'MT' => ['Montana', 0.93],
                    'NE' => ['Nebraska', 0.90], 'NV' => ['Nevada', 1.02], 'NH' => ['New Hampshire', 1.00],
                    'NJ' => ['New Jersey', 1.15], 'NM' => ['New Mexico', 0.88], 'NY' => ['New York (statewide)', 1.20],
                    'NC' => ['North Carolina', 0.88], 'ND' => ['North Dakota', 0.92], 'OH' => ['Ohio', 0.95],
                    'OK' => ['Oklahoma', 0.86], 'OR' => ['Oregon', 1.03], 'PA' => ['Pennsylvania', 1.02],
                    'RI' => ['Rhode Island', 1.10], 'SC' => ['South Carolina', 0.87], 'SD' => ['South Dakota', 0.88],
                    'TN' => ['Tennessee', 0.88], 'TX' => ['Texas', 0.87], 'UT' => ['Utah', 0.92],
                    'VT' => ['Vermont', 0.95], 'VA' => ['Virginia', 0.92], 'WA' => ['Washington', 1.08],
                    'WV' => ['West Virginia', 0.95], 'WI' => ['Wisconsin', 1.00], 'WY' => ['Wyoming', 0.90],
                ],
            ],
            // Concrete steps (steps calculator). IRC R311.7: riser 7-3/4 in max,
            // tread 10 in min, stair 36 in wide min, landing 36 in deep.
            'steps' => [
                'riser_max_in' => 7.75,
                'tread_min_in' => 10,
                'width_min_in' => 36,
                'landing_min_in' => 36,
                'riser_default_in' => 7,
                'tread_default_in' => 11,
                'installed_per_step' => [300, 700],  // $ per step, 4 ft wide, formed and broom finished by a contractor
            ],
        ];

        return self::$cache;
    }

    /** Dot-path lookup, e.g. Slab_Data::get('joints.sawcut_h'). */
    public static function get($path, $default = null) {
        $node = self::all();
        foreach (explode('.', $path) as $key) {
            if (!is_array($node) || !array_key_exists($key, $node)) {
                return $default;
            }
            $node = $node[$key];
        }
        return $node;
    }

    /* ------------------------------ formatting ----------------------------- */

    /** $1,234 or $6.50. Decimals are dropped for whole dollars when $dec is 'auto'. */
    public static function money($n, $dec = 'auto') {
        if ($dec === 'auto') {
            $dec = (abs($n - round($n)) < 0.005) ? 0 : 2;
        }
        return '$' . number_format((float) $n, (int) $dec);
    }

    /** "$135 – $160". $sep defaults to a spaced en dash. */
    public static function money_range($pair, $dec = 'auto', $sep = ' – ') {
        return self::money($pair[0], $dec) . $sep . self::money($pair[1], $dec);
    }

    /** "4 to 12" style plain range. */
    public static function range($pair, $sep = ' to ') {
        return self::num($pair[0]) . $sep . self::num($pair[1]);
    }

    public static function num($n, $dec = 'auto') {
        if ($dec === 'auto') {
            $dec = (abs($n - round($n)) < 0.0005) ? 0 : (abs($n * 10 - round($n * 10)) < 0.005 ? 1 : 2);
        }
        return number_format((float) $n, (int) $dec);
    }

    /** Get a [low, high] money range by path as text. */
    public static function mr($path, $dec = 'auto', $sep = ' – ') {
        return self::money_range(self::get($path), $dec, $sep);
    }

    /** Get a plain [low, high] range by path as text. */
    public static function r($path, $sep = ' to ') {
        return self::range(self::get($path), $sep);
    }

    /** Round a [low, high] pair outward to a step (low down, high up). */
    public static function round_out($pair, $step) {
        return [floor($pair[0] / $step + 1e-9) * $step, ceil($pair[1] / $step - 1e-9) * $step];
    }

    /** Round both ends of a pair to the nearest step. */
    public static function round_pair($pair, $step) {
        return [round($pair[0] / $step) * $step, round($pair[1] / $step) * $step];
    }

    /* ------------------------------ derived ------------------------------- */

    /** Ready-mix price range for a PSI grade: base ± $4-10 per 500-PSI step. */
    public static function psi_price($psi) {
        $c = self::get('concrete');
        $steps = ($psi - $c['psi_base']) / $c['psi_step'];
        return [$c['psi_base_price'][0] + $steps * $c['psi_step_price'][0], $c['psi_base_price'][1] + $steps * $c['psi_step_price'][1]];
    }

    /** Extra concrete cost vs. 3,000 PSI for a volume, as [low, high] (negative below the base). */
    public static function psi_extra($psi, $yd3) {
        $c = self::get('concrete');
        $steps = ($psi - $c['psi_base']) / $c['psi_step'];
        $a = $steps * $c['psi_step_price'][0] * $yd3;
        $b = $steps * $c['psi_step_price'][1] * $yd3;
        return [min($a, $b), max($a, $b)];
    }

    /** Whole bags, trimming floating-point noise before rounding up (88, not 89). */
    public static function bags($cuft, $size = '80') {
        $yield = self::get('concrete.bag_yields')[$size];
        return (int) ceil(round($cuft / $yield, 6));
    }

    /**
     * Estimate for one slab with the calculator's own line items and default
     * rates. Scopes:
     *  - 'basic': ready-mix + delivery fee, forms, broom finish, labor at the
     *    low end of the labor range; no reinforcement or vapor barrier.
     *  - 'full':  the calculator's default scope: ready-mix + delivery fee,
     *    forms, vapor barrier, #4 rebar @ 18 in, broom finish, $5/sq ft labor.
     *    This is the Bid Checker benchmark ("fair" direct cost).
     */
    public static function estimate($l, $w, $t, $scope = 'full', $index = 1.0) {
        $d = self::all();
        $area = $l * $w;
        $perim = 2 * ($l + $w);
        $net_cuft = $area * $t / 12;
        $waste = 1 + $d['concrete']['waste_pct'] / 100;
        $order_cuft = $net_cuft * $waste;
        $order_yd = $order_cuft / 27;

        $out = [
            'area' => $area,
            'perimeter' => $perim,
            'net_yd' => $net_cuft / 27,
            'order_yd' => $order_yd,
            'bags80' => self::bags($order_cuft, '80'),
            'bags60' => self::bags($order_cuft, '60'),
        ];
        // A location factor scales the ready-mix price and labor, as the
        // calculator's location selector does (delivery fee unchanged).
        $out['ready_mix'] = $order_yd * $d['concrete']['ready_mix_default'] * $index + $d['delivery']['fee_default'];
        $out['bagged'] = $out['bags80'] * $d['concrete']['bag_price_default'];
        $out['forms'] = $perim * $d['site']['forms_default'];
        $out['vapor'] = $area * $d['site']['vapor_default'];
        $out['rebar'] = $area * $d['reinforcement']['rebar_default'];
        $out['broom'] = $area * $d['finish']['broom']['default'];
        $out['labor'] = $area * ($scope === 'basic' ? $d['labor']['sqft_range'][0] : $d['labor']['sqft_default']) * $index;

        $out['direct'] = $out['ready_mix'] + $out['forms'] + $out['broom'] + $out['labor'];
        if ($scope === 'full') {
            $out['direct'] += $out['vapor'] + $out['rebar'];
        }
        // What the calculator shows with its defaults (adds 5% contingency).
        $out['calculator_total'] = $out['direct'] * (1 + $d['contingency_pct'] / 100);
        return $out;
    }

    /**
     * Installed and DIY ranges for a slab size.
     * Installed: low = basic scope; high = full scope + 25% overhead and
     * profit (the top of the Bid Checker's fair band).
     * DIY materials: low = cheaper of ready-mix or bags + forms; high = that
     * plus vapor barrier and rebar.
     */
    public static function size_costs($l, $w, $t = 4, $index = 1.0) {
        $basic = self::estimate($l, $w, $t, 'basic', $index);
        $full = self::estimate($l, $w, $t, 'full', $index);
        $concrete = min($full['ready_mix'], $full['bagged']);
        $diy_lo = $concrete + $full['forms'];
        $diy_hi = $diy_lo + $full['vapor'] + $full['rebar'];
        $installed = [$basic['direct'], $full['direct'] * (1 + self::get('overhead_profit'))];
        return [
            'est' => $full,
            'installed' => self::round_pair($installed, 10),
            'installed_psf' => [$installed[0] / $full['area'], $installed[1] / $full['area']],
            'diy' => self::round_pair([$diy_lo, $diy_hi], 10),
            'diy_psf' => [$diy_lo / $full['area'], $diy_hi / $full['area']],
            'bid_fair' => [$full['direct'], $full['direct'] * (1 + self::get('overhead_profit'))],
        ];
    }

    /** Per-sq-ft installed range for a reference slab, rounded outward to whole dollars. */
    public static function psf($l, $w, $t, $which = 'installed', $step = 1) {
        $c = self::size_costs($l, $w, $t);
        return self::round_out($c[$which . '_psf'], $step);
    }

    /** Headline installed $/sq ft (20×20 ft, 4 in). */
    public static function installed_psf() {
        return self::psf(20, 20, 4);
    }

    /** Headline DIY materials $/sq ft (20×20 ft, 4 in), to the nearest $0.50 outward. */
    public static function diy_psf() {
        return self::psf(20, 20, 4, 'diy', 0.5);
    }

    /** Driveway $/sq ft (20×40 ft, 5 in). */
    public static function driveway_psf() {
        return self::psf(40, 20, 5);
    }

    /** Garage slab $/sq ft (24×24 ft, 5 in). */
    public static function garage_psf() {
        return self::psf(24, 24, 5);
    }

    /** Stamped finish $/sq ft: the installed range with stamped instead of broom. */
    public static function stamped_psf($l = 20, $w = 20, $t = 4) {
        $f = self::get('finish');
        $add = $f['stamped']['default'] - $f['broom']['default'];
        $c = self::size_costs($l, $w, $t);
        return self::round_out([$c['installed_psf'][0] + $add, $c['installed_psf'][1] + $add * (1 + self::get('overhead_profit'))], 1);
    }

    /**
     * Thickened-edge (monolithic) concrete for an L×W slab of thickness t:
     * a ring one edge-width wide, deepened to the edge depth. Returns order
     * yd³ (with waste) for the edge alone.
     */
    public static function edge_yd($l, $w, $t, $edge_depth) {
        $m = self::get('monolithic');
        $ew = $m['edge_width_in'] / 12;
        $ring = $l * $w - ($l - 2 * $ew) * ($w - 2 * $ew);
        $cuft = $ring * max(0, $edge_depth - $t) / 12;
        return $cuft / 27 * (1 + self::get('concrete.waste_pct') / 100);
    }

    /** Excavation plus a compacted 4 in gravel base, $/sq ft, rounded outward to $0.10. */
    public static function site_prep_psf() {
        $s = self::get('site');
        $gravel_yd_per_sqft = $s['gravel_depth_in'] / 12 / 27 * $s['gravel_compaction'];
        return self::round_out([
            $s['excavation_range'][0] + $gravel_yd_per_sqft * $s['gravel_range'][0],
            $s['excavation_range'][1] + $gravel_yd_per_sqft * $s['gravel_range'][1],
        ], 0.1);
    }

    /** Monolithic slab foundation $/sq ft (30×40 ft, 4 in floor, 12-18 in edge). */
    public static function foundation_psf() {
        $m = self::get('monolithic');
        $rm = self::get('concrete.ready_mix_default');
        $c = self::size_costs(40, 30, 4);
        $area = 1200;
        $lo = $c['installed_psf'][0] + self::edge_yd(40, 30, 4, $m['edge_depth_in'][0]) * $rm / $area;
        $hi = $c['installed_psf'][1] + self::edge_yd(40, 30, 4, $m['edge_depth_in'][1]) * $rm * (1 + self::get('overhead_profit')) / $area;
        return self::round_out([$lo, $hi], 1);
    }

    /** The rows of the size table, in display order. */
    public static function size_table() {
        $sizes = [
            [10, 10, '10 × 10 ft'],
            [10, 20, '10 × 20 ft'],
            [12, 12, '12 × 12 ft'],
            [16, 24, '16 × 24 ft'],
            [20, 20, '20 × 20 ft'],
            [24, 24, '24 × 24 ft (2-Car Garage)'],
            [30, 40, '30 × 40 ft (Shop/Barn)'],
        ];
        $rows = [];
        foreach ($sizes as $s) {
            $c = self::size_costs($s[0], $s[1], 4);
            $rows[] = ['label' => $s[2], 'l' => $s[0], 'w' => $s[1]] + $c;
        }
        return $rows;
    }

    /** Quick facts for one size, used by FAQs, llms.txt and post fixes. */
    public static function size_fact($l, $w, $t = 4) {
        $c = self::size_costs($l, $w, $t);
        $e = $c['est'];
        return [
            'net_yd' => number_format($e['net_yd'], 2),
            'order_yd' => number_format($e['order_yd'], 2),
            'bags80' => $e['bags80'],
            'bags60' => $e['bags60'],
            'installed' => self::money_range($c['installed'], 0, ' to '),
            'diy' => self::money_range($c['diy'], 0, ' to '),
            'installed_dash' => self::money_range($c['installed'], 0, '–'),
            'diy_dash' => self::money_range($c['diy'], 0, '–'),
        ];
    }

    /** One sentence saying what the installed and DIY ranges assume. */
    public static function cost_basis_note() {
        $d = self::all();
        return sprintf(
            'Installed low end: plain broom-finish slab with ready-mix at %s/yd³ plus a %s delivery fee, forms and labor at %s/sq ft. High end: the calculator\'s default scope (adds a vapor barrier and #4 rebar at 18 in, labor at %s/sq ft) plus %d%% contractor overhead and profit. DIY materials: the cheaper of ready-mix or %s 80-lb bags, plus forms, up to vapor barrier and rebar.',
            self::money($d['concrete']['ready_mix_default']),
            self::money($d['delivery']['fee_default']),
            self::money($d['labor']['sqft_range'][0], 2),
            self::money($d['labor']['sqft_default'], 2),
            (int) round($d['overhead_profit'] * 100),
            self::money($d['concrete']['bag_price_default'], 2)
        ) . ' Site excavation and a gravel base are extra (about ' . self::money_range(self::site_prep_psf(), 2, ' to ') . ' per sq ft).';
    }

    /**
     * Rebar grid like the calculator's blueprint: #4 bars at the spacing
     * with 3 in edge cover, +10% for laps and offcuts, chairs at grid
     * intersections about 3-4 ft apart.
     */
    public static function rebar_grid($l, $w, $spacing_in = 18) {
        $r = self::get('reinforcement');
        $cover = $r['edge_cover_in'] / 12;
        $sp = $spacing_in / 12;
        $fill = function ($span) use ($cover, $sp) {
            $usable = $span - 2 * $cover;
            $n = (int) floor($usable / $sp + 1e-9) + 1;
            $step = $n > 1 ? $usable / ($n - 1) : 0;
            $out = [];
            for ($i = 0; $i < $n; $i++) {
                $out[] = $cover + $i * $step;
            }
            return $out;
        };
        $x_bars = $fill($w);
        $y_bars = $fill($l);
        $lin = count($x_bars) * ($l - 2 * $cover) + count($y_bars) * ($w - 2 * $cover);
        $k = max(1, (int) round(42 / $spacing_in));
        $every = $k * $spacing_in / 12;
        $pick = function ($arr) use ($k, $every) {
            $out = [];
            foreach ($arr as $i => $v) {
                if ($i % $k === 0) {
                    $out[] = $v;
                }
            }
            $last = end($arr);
            $last_out = end($out);
            if ($last_out !== $last && $last - $last_out > $every / 2) {
                $out[] = $last;
            }
            return $out;
        };
        $with_splice = $lin * (1 + $r['splice_allowance']);
        return [
            'lin_ft' => $with_splice,
            'sticks' => (int) ceil(round($with_splice / $r['stick_length_ft'], 6)),
            'chairs' => count($pick($y_bars)) * count($pick($x_bars)),
        ];
    }

    /**
     * Control-joint layout like the calculator's blueprint
     * (calculator-tools.js, buildGeometry()): joints every 2.5 × thickness
     * (in) in feet, capped at 15 ft, panels kept within 1.5:1.
     */
    public static function joint_layout($l, $w, $t) {
        $j = self::get('joints');
        $target = min(max($t, 3) * $j['ratio_blueprint'], $j['cap_ft']);
        $panels = function ($span, $limit) {
            return max(1, (int) ceil($span / $limit - 1e-9));
        };
        $pw = $panels($w, $target);
        $pl = $panels($l, min($target, $j['max_aspect'] * $w / $pw));
        $pw = $panels($w, min($target, $j['max_aspect'] * $l / $pl));
        return [
            'target_ft' => $target,
            'panels_l' => $pl,
            'panels_w' => $pw,
            'panel_l' => $l / $pl,
            'panel_w' => $w / $pw,
            'lin_ft' => ($pl - 1) * $w + ($pw - 1) * $l,
        ];
    }

    /** Ready-mix yards rounded up to the plant's order step (quarter yard). */
    public static function order_step($yd) {
        $step = self::get('delivery.order_step_yd')[0];
        return ceil($yd / $step - 1e-9) * $step;
    }

    /**
     * Concrete bill for a volume, bagged vs. ready-mix truck, as used by
     * the bags-vs-truck tool (supporting-calculators.js, initBreakEven()).
     * $net_yd is the net volume; waste is added here.
     *  - bags: 80-lb bags at the default price, plus one day's mixer rental
     *    (mid-range) when $mixer is true.
     *  - truck: order rounded to the plant step at the default $/yd, plus
     *    the delivery fee, plus a mid-range short-load fee under the usual
     *    minimum load.
     */
    public static function bags_vs_truck($net_yd, $mixer = true) {
        $d = self::all();
        $order_cuft = $net_yd * 27 * (1 + $d['concrete']['waste_pct'] / 100);
        $bags = $net_yd > 0 ? self::bags($order_cuft, '80') : 0;
        $mixer_cost = $mixer && $bags > 0 ? array_sum($d['concrete']['mixer_rental_day']) / 2 : 0;
        $bag_cost = $bags * $d['concrete']['bag_price_default'] + $mixer_cost;
        $order = self::order_step($order_cuft / 27);
        $short = $order > 0 && $order < $d['delivery']['common_min_yd'][0];
        $short_fee = $short ? array_sum($d['delivery']['short_load_flat']) / 2 : 0;
        $truck_cost = $order > 0 ? $order * $d['concrete']['ready_mix_default'] + $d['delivery']['fee_default'] + $short_fee : 0;
        $per_hour = array_sum($d['labor']['bags_per_hour_mixer']) / 2;
        return [
            'bags' => $bags,
            'bag_cost' => $bag_cost,
            'mixer_cost' => $mixer_cost,
            'mix_hours' => $bags / $per_hour,
            'order_yd' => $order,
            'short' => $short,
            'short_fee' => $short_fee,
            'truck_cost' => $truck_cost,
            'trucks' => $order > 0 ? (int) ceil($order / $d['delivery']['truck_capacity_yd'][0] - 1e-9) : 0,
            'cheaper' => $bag_cost <= $truck_cost ? 'bags' : 'truck',
        ];
    }

    /** Smallest net volume (yd³, 0.05 steps) at which the truck costs no more than bags. */
    public static function break_even_yd($mixer = true) {
        for ($i = 1; $i <= 200; $i++) {
            $yd = $i * 0.05;
            $c = self::bags_vs_truck($yd, $mixer);
            if ($c['truck_cost'] <= $c['bag_cost']) {
                return $yd;
            }
        }
        return null;
    }

    /**
     * Concrete for post holes (fence post calculator). Hole diameter and
     * depth in inches; the bottom gravel layer and the post itself take up
     * room that needs no concrete. Waste allowance added.
     */
    public static function post_hole($post_key, $hole_in, $depth_in, $count = 1, $waste_pct = null) {
        $p = self::get('posts');
        $size = $p['sizes'][$post_key];
        if ($size[1] === 'round') {
            $post_area = M_PI * pow($size[2] / 2, 2);
        } elseif ($size[1] === 'rect') {
            $post_area = $size[2][0] * $size[2][1];
        } else {
            $post_area = $size[2] * $size[2];
        }
        $waste = $waste_pct === null ? self::get('concrete.waste_pct') : $waste_pct;
        $fill_in = max(0, $depth_in - $p['gravel_in']);
        $per_hole = max(0, M_PI * pow($hole_in / 2, 2) - $post_area) * $fill_in / 1728 * (1 + $waste / 100);
        $total = $per_hole * $count;
        $y = self::get('concrete.bag_yields');
        return [
            'per_hole_ft3' => $per_hole,
            'total_ft3' => $total,
            'per_hole_80' => $per_hole / $y['80'],
            'per_hole_50' => $per_hole / $y['50'],
            'per_hole_fast' => $per_hole / $p['fast_set_yield'],
            'bags80' => $total > 0 ? (int) ceil(round($total / $y['80'], 6)) : 0,
            'bags50' => $total > 0 ? (int) ceil(round($total / $y['50'], 6)) : 0,
            'bags_fast' => $total > 0 ? (int) ceil(round($total / $p['fast_set_yield'], 6)) : 0,
            'gravel_ft3' => M_PI * pow($hole_in / 2, 2) * $p['gravel_in'] / 1728 * $count,
        ];
    }

    /** Typical hole diameter for a post: about 3 × its width, to the nearest 2 in. */
    public static function post_hole_diameter($post_key) {
        $chain = self::get('posts.chain_link_hole_in');
        if (isset($chain[$post_key])) {
            return $chain[$post_key];
        }
        $size = self::get('posts.sizes')[$post_key];
        $width = is_array($size[2]) ? max($size[2]) : $size[2];
        return max(6, (int) round($width * self::get('posts.hole_factor') / 2) * 2);
    }

    /**
     * Solid concrete steps (steps calculator): N steps of the given rise
     * and tread, the full width, plus an optional top landing. Inches for
     * rise, tread; feet for width and landing depth.
     */
    public static function steps_volume($n, $width_ft, $rise_in, $tread_in, $landing_ft = 0) {
        $steps_ft3 = $width_ft * ($tread_in / 12) * ($rise_in / 12) * $n * ($n + 1) / 2;
        $landing_ft3 = $width_ft * $landing_ft * ($n * $rise_in / 12);
        $net = $steps_ft3 + $landing_ft3;
        $order_ft3 = $net * (1 + self::get('concrete.waste_pct') / 100);
        // Each side form is the stepped profile; risers are one board per step.
        $side_sqft = ($tread_in / 12) * ($rise_in / 12) * $n * ($n + 1) / 2 + $landing_ft * ($n * $rise_in / 12);
        return [
            'net_ft3' => $net,
            'net_yd' => $net / 27,
            'order_yd' => $order_ft3 / 27,
            'bags80' => $net > 0 ? self::bags($order_ft3, '80') : 0,
            'bags60' => $net > 0 ? self::bags($order_ft3, '60') : 0,
            'total_rise_in' => $n * $rise_in,
            'total_run_in' => $n * $tread_in + $landing_ft * 12,
            'riser_lf' => $n * $width_ft,
            'plywood_sheets' => (int) ceil(round(2 * $side_sqft / 32, 6)),
        ];
    }

    /* ------------------------------ locations ----------------------------- */

    /** Every state and DC: code => ['name' => ..., 'index' => ...], A to Z by name. */
    public static function states() {
        $out = [];
        foreach (self::get('locations.states') as $code => $s) {
            $out[$code] = ['code' => $code, 'name' => preg_replace('/ \(statewide\)$/', '', $s[0]), 'index' => $s[1]];
        }
        uasort($out, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        return $out;
    }

    /** Metro areas: key => ['name', 'index', 'state']. */
    public static function metros() {
        $out = [];
        foreach (self::get('locations.metros') as $key => $m) {
            $out[$key] = ['key' => $key, 'name' => $m[0], 'index' => $m[1], 'state' => $m[2]];
        }
        return $out;
    }

    /** Settings handed to the calculators as window.SLAB_DATA. */
    public static function js_config() {
        $d = self::all();
        return [
            'readyMixPerYard' => $d['concrete']['ready_mix_default'],
            'bagPrice' => $d['concrete']['bag_price_default'],
            'bagYields' => $d['concrete']['bag_yields'],
            'wastePct' => $d['concrete']['waste_pct'],
            'densityLbFt3' => $d['concrete']['density_lb_ft3'],
            'excavation' => $d['site']['excavation_default'],
            'gravelPerYard' => $d['site']['gravel_default'],
            'gravelDepthIn' => $d['site']['gravel_depth_in'],
            'gravelCompaction' => $d['site']['gravel_compaction'],
            'gravelTonsPerYard' => $d['site']['gravel_tons_per_yd'],
            'forms' => $d['site']['forms_default'],
            'vapor' => $d['site']['vapor_default'],
            'demo' => $d['site']['demo_default'],
            'mesh' => $d['reinforcement']['mesh_default'],
            'rebar' => $d['reinforcement']['rebar_default'],
            'rebarBaseSpacingIn' => $d['reinforcement']['rebar_base_spacing_in'],
            'spliceAllowance' => $d['reinforcement']['splice_allowance'],
            'lapBarDiameters' => $d['reinforcement']['lap_bar_diameters'],
            'lapInNo4' => $d['reinforcement']['lap_in_no4'],
            'edgeCoverIn' => $d['reinforcement']['edge_cover_in'],
            'finish' => [
                'broom' => $d['finish']['broom']['default'],
                'trowel' => $d['finish']['trowel']['default'],
                'exposed' => $d['finish']['exposed']['default'],
                'stamped' => $d['finish']['stamped']['default'],
            ],
            'laborPerSqFt' => $d['labor']['sqft_default'],
            'laborStamped' => $d['labor']['sqft_stamped'],
            'laborDifficult' => $d['labor']['sqft_difficult'],
            'crewHour' => $d['labor']['crew_hour_default'],
            'crewHoursPerSqFt' => $d['labor']['crew_hours_per_sqft'],
            'crewHoursPerSqFtStamped' => $d['labor']['crew_hours_per_sqft_stamped'],
            'crewHoursPerSqFtDifficult' => $d['labor']['crew_hours_per_sqft_difficult'],
            'deliveryFee' => $d['delivery']['fee_default'],
            'pump' => $d['pump']['default'],
            'equipment' => $d['equipment_default'],
            'contingencyPct' => $d['contingency_pct'],
            'overheadProfit' => $d['overhead_profit'],
            'jointRatio' => $d['joints']['ratio_blueprint'],
            'jointCapFt' => $d['joints']['cap_ft'],
            'jointMaxAspect' => $d['joints']['max_aspect'],
            'coldF' => $d['weather']['cold_f'],
            'coolF' => $d['weather']['cool_f'],
            'hotF' => $d['weather']['hot_f'],
            'concreteTempLimitF' => $d['weather']['concrete_temp_limit_f'],
            'evapLimitLb' => $d['weather']['evap_limit_lb'],
            // Figures for the single-purpose tools (supporting-calculators.js).
            'curedWeightFactor' => $d['concrete']['cured_weight_factor'],
            'demoRate' => $d['site']['demo_rate'],
            'demoPerInch' => $d['site']['demo_per_inch'],
            'demoRebar' => $d['site']['demo_rebar'],
            'orderStepYd' => $d['delivery']['order_step_yd'][0],
            'truckCapacityYd' => $d['delivery']['truck_capacity_yd'],
            'commonMinYd' => $d['delivery']['common_min_yd'],
            'shortLoadFlat' => $d['delivery']['short_load_flat'],
            'psiPrices' => array_map([__CLASS__, 'psi_price'], array_combine(array_keys($d['concrete']['psi_grades']), array_keys($d['concrete']['psi_grades']))),
            'dryVolumeFactor' => $d['concrete']['dry_volume_factor'],
            'cementBagLb' => $d['concrete']['cement_bag_lb'],
            'cementBagFt3' => $d['concrete']['cement_bag_ft3'],
            'sandTonsPerYd' => $d['concrete']['sand_tons_per_yd'],
            'waterCementRatio' => $d['concrete']['water_cement_ratio'],
            'meshSheetFt' => $d['reinforcement']['mesh_sheet_ft'],
            'meshRollFt' => $d['reinforcement']['mesh_roll_ft'],
            'meshOverlapIn' => $d['reinforcement']['mesh_overlap_in'],
            'meshSheetPrice' => $d['reinforcement']['mesh_sheet_price'],
            'meshRollPrice' => $d['reinforcement']['mesh_roll_price'],
            'chairGridFt' => $d['reinforcement']['chair_grid_ft'],
            'formBoardDepthIn' => $d['site']['form_board_depth_in'],
            // Phase 6 tools: bags vs. truck, fence posts, steps, quote comparison.
            'bagPrices' => ['80' => $d['concrete']['bag_price_default']] + $d['concrete']['bag_price_sizes'],
            'mixerRentalDay' => $d['concrete']['mixer_rental_day'],
            'bagsPerHourMixer' => $d['labor']['bags_per_hour_mixer'],
            'posts' => $d['posts'],
            'steps' => $d['steps'],
            'excavationDefault' => $d['site']['excavation_default'],
            'permitFlatwork' => $d['permit']['flatwork'],
            'stampedFinish' => $d['finish']['stamped']['default'],
            'presets' => array_map(function ($p) {
                return ['l' => $p[0], 'w' => $p[1], 't' => $p[2], 'name' => $p[3]];
            }, $d['presets']),
            // Location selector: [key, name, factor] rows.
            'metros' => array_map(function ($k, $m) {
                return [$k, $m[0], $m[1]];
            }, array_keys($d['locations']['metros']), $d['locations']['metros']),
            'states' => array_map(function ($k, $s) {
                return [$k, $s[0], $s[1]];
            }, array_keys($d['locations']['states']), $d['locations']['states']),
        ];
    }
}
