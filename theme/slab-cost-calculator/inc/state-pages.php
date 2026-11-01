<?php
/**
 * STATE PRICE PAGES: /concrete-slab-cost-by-state/ AND /texas-concrete-slab-cost/
 *
 * The hub prices a standard slab in all 50 states and DC with the location
 * factors in Slab_Data ('locations'), the same factors the calculator's
 * location selector uses. A location factor is one number per state, so a
 * page that only swapped it in would be the same page 51 times. Only the
 * states below get their own page, each with its own climate, frost,
 * soil and permit notes and its metro areas; every other state is a row
 * in the hub table that opens the calculator on that state.
 *
 * Prices are the national model (Slab_Data::size_costs()) with the
 * state's factor applied to ready-mix and labor, exactly as the calculator
 * does when the state is picked.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_State_Pages {

    /** Reference projects priced on every state page: key => [label, l, w, t, link]. */
    public static function projects() {
        return [
            'patio' => ['12 × 12 ft patio', 12, 12, 4, '/12x12-concrete-slab-cost-breakdown/'],
            'shed' => ['10 × 12 ft shed slab', 12, 10, 4, '/10x12-concrete-slab-cost/'],
            'slab' => ['20 × 20 ft slab', 20, 20, 4, '/20x20-concrete-slab-cost-breakdown/'],
            'garage' => ['24 × 24 ft garage floor (5 in)', 24, 24, 5, '/24x24-concrete-slab-cost-breakdown/'],
            'driveway' => ['20 × 40 ft driveway (5 in)', 40, 20, 5, '/20x40-concrete-slab-cost/'],
            'shop' => ['30 × 40 ft shop floor (6 in)', 40, 30, 6, '/30x40-concrete-slab-cost/'],
        ];
    }

    /**
     * States with their own page, keyed by URL slug. 'psi' is the strength
     * the page recommends for outdoor flatwork (priced from Slab_Data);
     * text fields may use {psi_*}, {air} and {cold_f} tokens.
     */
    public static function states() {
        return [
            'california' => [
                'code' => 'CA', 'metros' => ['sf', 'la'], 'psi' => 3000,
                'intro' => 'California has the highest concrete prices of any mainland state in our model, and the Bay Area runs higher still. Labor and contractor overhead drive the gap; a bag of mix or a sheet of wire mesh costs about the same here as anywhere, so doing the work yourself saves a bigger share in California than in most states.',
                'weathering' => 'Negligible on the coast and in the valleys; severe in the Sierra Nevada and other mountain areas.',
                'psi_text' => '{psi_exposed} PSI for patios and driveways in most of the state; {psi_severe} PSI air-entrained above the snow line.',
                'frost' => 'About 12 in or less in coastal and valley areas. Mountain counties set deeper frost depths, often 18 to 30 in or more.',
                'soil' => 'Expansive clay in parts of the Bay Area, the Sacramento Valley and Southern California. A soils report may call for a thicker base, moisture conditioning or heavier rebar.',
                'season' => 'Year-round on the coast. In the Central Valley and the inland deserts, summer pours should start at first light to stay inside hot-weather limits.',
                'notes' => [
                    'Many California cities limit new paved area to control stormwater runoff. A large new patio or driveway can trigger a drainage review, or a requirement for permeable paving in part of it; ask before you design.',
                    'Driveway work at the street (apron, curb cut or sidewalk) needs a city encroachment permit, separate from any building permit for the slab.',
                    'Because labor is the expensive part here, small slabs are where DIY pays most: the DIY column in the table below is a smaller share of the installed price than the national average.',
                ],
                'faq' => ['q' => 'Why does concrete cost more in California?', 'a' => 'Mostly labor and overhead: finishers\' wages, insurance and business costs are higher, and ready-mix costs more around the big metros. In our model California runs {pct} the national average, and the Bay Area {pct_sf}. Bagged concrete, mesh and rebar cost close to national prices, so DIY saves a larger share here.'],
            ],
            'texas' => [
                'code' => 'TX', 'metros' => ['hou', 'dal'], 'psi' => 3000,
                'intro' => 'Texas is one of the cheaper states to pour concrete in our model, and Houston and Dallas-Fort Worth are close to the state level. What costs more in Texas is the ground: much of the eastern half of the state sits on expansive clay that swells when it is wet and shrinks in drought, and a slab built as if the soil were stable will crack.',
                'weathering' => 'Negligible in South Texas and along the Gulf; moderate across North and Central Texas; severe in the Panhandle.',
                'psi_text' => '{psi_exposed} PSI for most patios and driveways; {psi_severe} PSI air-entrained in the Panhandle.',
                'frost' => 'About 6 to 12 in in most of the state; deeper in the Panhandle (around 18 to 24 in). Your city\'s code sets the depth.',
                'soil' => 'Expansive clay around Houston, the Blackland Prairie (Dallas-Fort Worth to San Antonio) and much of East Texas. House and garage slabs on these soils are usually engineered: thicker select fill, deeper perimeter beams, more steel, or post-tensioned cables.',
                'season' => 'Spring and fall are easiest. Summer pours start at dawn, with a retarder in the mix and wet curing or curing compound right after finishing, because heat and wind dry the surface fast.',
                'notes' => [
                    'Keep the soil moisture even around the slab: a downspout or sprinkler soaking one side of a slab on clay is a common cause of one corner lifting and cracking.',
                    'For a garage or house slab, ask for the engineer\'s foundation design. Post-tensioned slabs are common in Texas subdivisions and are priced differently from a rebar slab.',
                    'Most Texas cities require a permit for a new driveway approach at the street, and many HOAs have their own rules for driveways and patios.',
                ],
                'faq' => ['q' => 'What kind of slab works on Texas clay?', 'a' => 'For a patio or driveway: strip the topsoil, add compacted select fill or gravel, use #4 rebar rather than mesh, and keep water from pooling on one side. For a garage or house, follow an engineer\'s design, often a post-tensioned slab or one with deep perimeter beams; it costs more than the plain slab priced here.'],
            ],
            'florida' => [
                'code' => 'FL', 'metros' => ['mia'], 'psi' => 3000,
                'intro' => 'Florida\'s concrete prices sit below the national average in our model, with the Miami metro a little higher than the rest of the state. There is no frost line to dig below, but Florida has its own items: termite treatment under slabs that will be inside a building, a high water table near the coasts, and afternoon storms that can ruin a fresh finish.',
                'weathering' => 'Negligible statewide.',
                'psi_text' => '{psi_exposed} PSI is standard for patios, driveways and pool decks ({psi_protected} PSI is the code minimum).',
                'frost' => 'No frost depth to speak of. Footings still go at least 12 in below undisturbed ground under the building code.',
                'soil' => 'Mostly sand, which drains and compacts well, but with a high water table near the coasts, pockets of organic muck, and sinkhole-prone limestone in parts of central Florida.',
                'season' => 'Year-round. From June to September, pour in the morning so finishing is done before the afternoon thunderstorms, and keep plastic on hand to cover the slab.',
                'notes' => [
                    'A slab under a house, attached garage or enclosed room needs termite protection before the pour under the Florida Building Code, usually a soil treatment. Ask whether the bid includes it.',
                    'Carport, shed and screen-room kits are anchored to the slab for wind. Check the product approval for the slab thickness and edge distance its anchors need before you pour.',
                    'Pool decks are usually 4 in thick with fiber or mesh; textured cool-deck or paver finishes cost extra.',
                ],
                'faq' => ['q' => 'Do I need termite treatment under a concrete slab in Florida?', 'a' => 'For a slab under a building (a house, an attached garage or an enclosed addition), yes: the Florida Building Code requires termite protection, usually a soil treatment applied just before the pour. A detached patio, driveway or pool deck does not need it.'],
            ],
            'new-york' => [
                'code' => 'NY', 'metros' => ['nyc'], 'psi' => 4000,
                'intro' => 'New York has two price levels. Statewide our model puts concrete well above the national average, and the New York City metro higher still, among the most expensive places in the country to pour a slab. Upstate, the bigger factor is winter: freeze-thaw cycles and road salt decide how long a slab lasts.',
                'weathering' => 'Severe statewide.',
                'psi_text' => '{psi_deicing} PSI air-entrained ({air}% air) for driveways and anything that sees de-icing salt; {psi_severe} PSI air-entrained is the code minimum for outdoor slabs and garage floors.',
                'frost' => 'Usually 36 to 48 in; most of upstate uses 42 to 48 in. Your town\'s code sets the exact depth.',
                'soil' => 'Glacial till with stones, rock close to the surface in many areas, and clay in some valleys. Rock removal can add a lot to excavation.',
                'season' => 'May to October. Late-fall pours need hot water in the mix, insulated blankets and time to gain strength before the first hard freeze.',
                'notes' => [
                    'In New York City, work on the public sidewalk or a driveway curb cut needs city permits, which add cost and lead time; a backyard patio usually does not.',
                    'Keep de-icing salt off new concrete for its first winter, and seal outdoor slabs once they have cured.',
                    'Slabs on rocky upstate lots may need a breaker or a thicker gravel base to level out; get the excavation priced separately.',
                ],
                'faq' => ['q' => 'What PSI concrete should I use for a driveway in New York?', 'a' => '{psi_deicing} PSI air-entrained concrete, with about {air}% air, because New York driveways see both freeze-thaw cycles and de-icing salt. {psi_severe} PSI air-entrained is the building code minimum for exposed slabs in severe weathering areas.'],
            ],
            'pennsylvania' => [
                'code' => 'PA', 'metros' => [], 'psi' => 4000,
                'intro' => 'Pennsylvania prices sit close to the national average in our model. Freeze-thaw winters and road salt make air-entrained concrete a must for anything outdoors.',
                'weathering' => 'Severe statewide.',
                'psi_text' => '{psi_deicing} PSI air-entrained for driveways; {psi_severe} PSI air-entrained minimum for patios, steps and garage floors.',
                'frost' => 'About 30 to 42 in: the Philadelphia area is at the low end, the northern tier deeper. Each municipality sets its own frost depth.',
                'soil' => 'Shale and rock close to the surface in much of the state; limestone areas in the south-central and southeast regions can have sinkholes.',
                'season' => 'April to October. Protect fall pours from early frost with blankets.',
                'notes' => [
                    'Ask your municipality before you pour: permit rules for flatwork vary town to town under the state\'s construction code.',
                    'In limestone country, report any soft spot or sudden settling in the subgrade before the pour; it can be a sinkhole forming.',
                    'Use {air}% air-entrained concrete for steps and walks, which take the most salt.',
                ],
                'faq' => ['q' => 'How deep do footings need to be in Pennsylvania?', 'a' => 'Below the local frost depth, which is about 30 to 42 inches depending on the municipality. A floating patio slab on gravel does not need footings, but a garage or addition slab does; check with your code office.'],
            ],
            'illinois' => [
                'code' => 'IL', 'metros' => ['chi'], 'psi' => 4000,
                'intro' => 'Illinois concrete costs above the national average in our model, and the Chicago metro well above it, driven by labor. Chicago winters, and the salt that comes with them, are hard on driveways, so the concrete\'s strength and air content matter more than its finish.',
                'weathering' => 'Severe statewide.',
                'psi_text' => '{psi_deicing} PSI air-entrained ({air}% air) for driveways and aprons; {psi_severe} PSI air-entrained minimum for patios and garage floors.',
                'frost' => 'About 42 in in Chicago and northern Illinois; around 30 to 36 in further south. Check your village or city code.',
                'soil' => 'Clay across the Chicago area and much of the state. It drains slowly, so a compacted gravel base and a slope away from the house matter.',
                'season' => 'May to October. Ask the plant for hot water and accelerator for early-spring or late-fall pours.',
                'notes' => [
                    'Many Chicago-area villages require a permit and a licensed contractor for driveways and work at the street.',
                    'Slope driveways and patios away from the house; on clay, water that sits under a slab freezes and lifts it.',
                    'Do not use de-icing salt on a new driveway its first winter; use sand for traction.',
                ],
                'faq' => ['q' => 'How much does a concrete driveway cost in Chicago?', 'a' => 'For a 20 x 40 ft, 5-inch driveway our model gives about {drive_chi} in the Chicago metro, against {drive_nat} at national average prices. Tear-out of an old driveway and the street apron are extra.'],
            ],
            'georgia' => [
                'code' => 'GA', 'metros' => ['atl'], 'psi' => 3000,
                'intro' => 'Georgia sits below the national average in our model, with metro Atlanta close to the state level. Red clay is the local challenge: it holds water, moves a little with moisture and turns to mud in a wet week, so base preparation decides how well a slab holds up.',
                'weathering' => 'Moderate in north Georgia; negligible across the south.',
                'psi_text' => '{psi_exposed} PSI for patios and driveways; {psi_severe} PSI air-entrained is worth it in the north Georgia mountains.',
                'frost' => 'About 12 in in most of the state; somewhat deeper in the north Georgia mountains.',
                'soil' => 'Piedmont red clay in the north and center; sandy soils in the coastal plain.',
                'season' => 'Most of the year. Pour early in the day in summer; short winter cold snaps can still freeze a fresh slab overnight.',
                'notes' => [
                    'Strip the topsoil and any soft clay, then compact 4 to 6 in of gravel. Pouring straight onto wet red clay is the most common cause of settling and cracking.',
                    'If the site stays wet, add a drain or regrade first; a slab does not fix a drainage problem.',
                    'Metro Atlanta counties and cities usually need a permit for a new driveway or a curb cut.',
                ],
                'faq' => ['q' => 'Can you pour concrete directly on Georgia red clay?', 'a' => 'It is not a good idea. Remove the topsoil and soft clay, compact a 4 to 6-inch gravel base, and make sure water drains away. Clay that is wet when the slab is poured can settle or swell unevenly and crack it.'],
            ],
            'north-carolina' => [
                'code' => 'NC', 'metros' => [], 'psi' => 3000,
                'intro' => 'North Carolina prices are below the national average in our model. The state spans three climates, from the mild coast to the clay of the Piedmont to mountain towns with real winters, and the right slab spec changes with them.',
                'weathering' => 'Negligible to moderate on the coast and in the Piedmont; severe in the mountains.',
                'psi_text' => '{psi_exposed} PSI on the coast and in the Piedmont; {psi_severe} PSI air-entrained in the mountains.',
                'frost' => '12 in in most of the state; deeper in the mountain counties (often 18 in or more).',
                'soil' => 'Piedmont clay around Charlotte, the Triad and the Triangle; sand and a high water table in the coastal plain; rock in the mountains.',
                'season' => 'Most of the year in the east; spring to fall in the mountains.',
                'notes' => [
                    'On the coast, check the water table before digging a deep base; a slab on wet sand needs a thicker gravel layer and good drainage.',
                    'In the mountains, treat the spec like a northern state: air-entrained concrete and no salt the first winter.',
                    'Piedmont clay needs the same base prep as Georgia red clay: strip, compact, and drain.',
                ],
                'faq' => ['q' => 'What concrete strength should I use in the North Carolina mountains?', 'a' => '{psi_severe} PSI air-entrained concrete for anything outdoors. The mountains are a severe weathering area, so the building code asks for more strength and air than on the coast, where {psi_exposed} PSI is usual.'],
            ],
            'michigan' => [
                'code' => 'MI', 'metros' => [], 'psi' => 4000,
                'intro' => 'Michigan concrete costs about the national average in our model. Its winters set the spec: a Michigan driveway sees many freeze-thaw cycles and a lot of salt every year, so air entrainment, strength and proper curing matter more than in any southern state.',
                'weathering' => 'Severe statewide.',
                'psi_text' => '{psi_deicing} PSI air-entrained ({air}% air) for driveways; {psi_severe} PSI air-entrained minimum for patios and garage floors.',
                'frost' => 'Usually 42 in in the Lower Peninsula; deeper in parts of the Upper Peninsula.',
                'soil' => 'Sand and gravel in much of the state (good drainage); clay in parts of southeast Michigan.',
                'season' => 'May to early October. Below {cold_f}°F the concrete needs cold-weather protection.',
                'notes' => [
                    'Ask for air-entrained concrete by name; non-air concrete outdoors in Michigan scales and flakes within a few winters.',
                    'Wait until spring to seal a fall pour, and keep salt off it the first winter.',
                    'A garage slab needs a thickened edge or frost-depth footings under the walls; the floating slab priced here does not include them.',
                ],
                'faq' => ['q' => 'When is it too cold to pour concrete in Michigan?', 'a' => 'When air temperatures are expected below {cold_f}°F during placement or the following days, the concrete needs cold-weather measures: heated water, accelerator, insulated blankets and no pouring on frozen ground. Most Michigan contractors stop flatwork between about November and April.'],
            ],
            'arizona' => [
                'code' => 'AZ', 'metros' => ['phx'], 'psi' => 3000,
                'intro' => 'Arizona\'s concrete prices sit below the national average in our model, and Phoenix is at about the state level. Heat is the main issue: in summer the slab can stiffen faster than the crew can finish it, so timing and curing are part of the price.',
                'weathering' => 'Negligible in the low desert; severe in the high country (Flagstaff and the White Mountains).',
                'psi_text' => '{psi_exposed} PSI in the low desert; {psi_severe} PSI air-entrained in the high country.',
                'frost' => 'About 12 in around Phoenix and Tucson; much deeper in Flagstaff and the high country. Check the local code.',
                'soil' => 'Caliche, a cement-hard layer of calcium carbonate, lies under many desert yards and can need a breaker to dig. Some areas have expansive clay.',
                'season' => 'October to April is easiest in the low desert. Summer pours start before sunrise; the concrete should stay below about {conc_hot}°F at delivery.',
                'notes' => [
                    'In summer, ask for a retarder in the mix, dampen the base before the pour, and apply curing compound right after finishing.',
                    'Get excavation priced after a test hole: hitting caliche can double the digging time.',
                    'In the high country, treat the spec like a northern state: air-entrained concrete and no salt the first winter.',
                ],
                'faq' => ['q' => 'Can you pour concrete in Phoenix in the summer?', 'a' => 'Yes, but early: crews start before sunrise so placing and finishing are done before the hottest hours. A retarder in the mix, a damp base, sunshade or windbreaks and immediate curing keep the slab from drying too fast and cracking.'],
            ],
            'washington' => [
                'code' => 'WA', 'metros' => ['sea'], 'psi' => 3500,
                'intro' => 'Washington concrete costs above the national average in our model, and the Seattle metro higher again. The state splits at the Cascades: rain and mild winters in the west, real freeze-thaw winters and dry summers in the east.',
                'weathering' => 'Moderate west of the Cascades; severe east of them.',
                'psi_text' => '{psi_exposed} PSI is common west of the Cascades; {psi_severe} PSI air-entrained in eastern Washington and the mountains.',
                'frost' => 'About 12 in around Seattle and on the west side; around 24 in or more in eastern Washington.',
                'soil' => 'Glacial till and clay around Puget Sound, which drain slowly; sandy and silty soils in the east.',
                'season' => 'West side: plan around the rain from about October to April and keep plastic ready to cover a fresh slab. East side: spring to fall.',
                'notes' => [
                    'Rain on a fresh slab washes out the surface and weakens it; west-side crews watch the forecast and tent or cover the pour.',
                    'Slow-draining till under Puget Sound slabs needs a gravel base and a slope away from the house.',
                    'East of the Cascades, use air-entrained concrete and keep salt off new slabs.',
                ],
                'faq' => ['q' => 'Can you pour concrete in the rain in Seattle?', 'a' => 'Light rain before the pour is fine if the base is not muddy or flooded. Rain during finishing is the problem: it adds water to the surface and weakens it. Crews cover the slab with plastic or a tent, and reschedule if heavy rain is forecast.'],
            ],
            'colorado' => [
                'code' => 'CO', 'metros' => ['den'], 'psi' => 4000,
                'intro' => 'Colorado prices sit a little below the national average in our model, with Denver close to average. Front Range builders deal with two things that add real cost: expansive bentonite clay, which can heave a slab several inches, and big temperature swings between day and night.',
                'weathering' => 'Severe statewide.',
                'psi_text' => '{psi_deicing} PSI air-entrained for driveways (de-icers are widely used); {psi_severe} PSI air-entrained minimum for patios and garage floors.',
                'frost' => 'About 30 to 36 in along the Front Range; deeper in the mountains.',
                'soil' => 'Expansive clay and bentonite along the Front Range. Many lots come with a soils report that says how slabs must be built, sometimes with over-excavation and replacement of the clay.',
                'season' => 'Late spring to early fall. Spring and fall nights can drop below freezing even after warm days, so fresh slabs may need blankets.',
                'notes' => [
                    'Read the soils report for your lot before pricing a slab: over-excavation and structural fill can cost more than the concrete.',
                    'Garage and basement slabs in swelling soil are often "floating", separated from walls with an isolation joint so they can move.',
                    'Use air-entrained concrete and seal outdoor slabs; magnesium chloride de-icers are hard on concrete.',
                ],
                'faq' => ['q' => 'Why do concrete slabs heave in Colorado?', 'a' => 'Much of the Front Range sits on expansive clay and bentonite, which swell when they get wet. A slab poured on that soil can lift and crack. Builders follow the lot\'s soils report, which may require removing and replacing the clay, a thicker base, or a slab that is free to move.'],
            ],
        ];
    }

    public static function exists($slug) {
        return isset(self::states()[$slug]);
    }

    public static function path($slug) {
        return '/' . $slug . '-concrete-slab-cost/';
    }

    public static function hub_path() {
        return '/concrete-slab-cost-by-state/';
    }

    public static function route_regex() {
        return '^(' . implode('|', array_map('preg_quote', array_keys(self::states()))) . ')-concrete-slab-cost/?$';
    }

    /** State code => page slug, for the states that have a page. */
    public static function slug_for($code) {
        foreach (self::states() as $slug => $s) {
            if ($s['code'] === $code) {
                return $slug;
            }
        }
        return null;
    }

    /** "13% below" / "25% above" / "about" the national average. */
    public static function vs_national($index) {
        $pct = (int) round(($index - 1) * 100);
        if ($pct === 0) {
            return 'about';
        }
        return abs($pct) . '% ' . ($pct > 0 ? 'above' : 'below');
    }

    /** Prices for every reference project at one location factor. */
    public static function prices($index) {
        $out = [];
        foreach (self::projects() as $key => $p) {
            $c = Slab_Data::size_costs($p[1], $p[2], $p[3], $index);
            $out[$key] = [
                'installed' => $c['installed'],
                'psf' => Slab_Data::round_out($c['installed_psf'], 0.5),
                'diy' => $c['diy'],
            ];
        }
        return $out;
    }

    /** Ready-mix $/yd³ range for a PSI grade at a location factor, to the nearest dollar. */
    public static function ready_mix($psi, $index) {
        $p = Slab_Data::psi_price($psi);
        return [round($p[0] * $index), round($p[1] * $index)];
    }

    /** Labor $/sq ft range at a location factor. */
    public static function labor($index) {
        $r = Slab_Data::get('labor.sqft_range');
        return [$r[0] * $index, $r[1] * $index];
    }

    /** Everything a state page shows. */
    public static function data($slug) {
        static $cache = [];
        if (isset($cache[$slug])) {
            return $cache[$slug];
        }
        if (!self::exists($slug)) {
            return null;
        }
        $s = self::states()[$slug];
        $all = Slab_Data::states();
        $st = $all[$s['code']];
        $metros = [];
        foreach ($s['metros'] as $key) {
            $m = Slab_Data::metros()[$key];
            $metros[$key] = $m + ['prices' => self::prices($m['index'])];
        }
        return $cache[$slug] = $s + [
            'slug' => $slug,
            'name' => $st['name'],
            'index' => $st['index'],
            'prices' => self::prices($st['index']),
            'national' => self::prices(1.0),
            'metro_rows' => $metros,
            'ready_mix' => self::ready_mix(Slab_Data::get('concrete.psi_base'), $st['index']),
            'ready_mix_psi' => self::ready_mix($s['psi'], $st['index']),
            'labor' => self::labor($st['index']),
        ];
    }

    /** Fill tokens in a state's written text. */
    public static function fill($text, $d) {
        $code = Slab_Data::get('code');
        $nat = $d['national']['driveway']['installed'];
        $tokens = [
            '{psi_protected}' => number_format($code['psi_protected']),
            '{psi_exposed}' => number_format($code['psi_exposed']),
            '{psi_severe}' => number_format($code['psi_severe']),
            '{psi_deicing}' => number_format($code['psi_deicing_recommended']),
            '{air}' => Slab_Data::r('concrete.air_entrainment_pct', '-'),
            '{cold_f}' => (string) Slab_Data::get('weather.cold_f'),
            '{conc_hot}' => (string) Slab_Data::get('weather.concrete_temp_limit_f')[0],
            '{pct}' => self::vs_national($d['index']),
            '{drive_nat}' => Slab_Data::money_range($nat, 0, ' to '),
        ];
        foreach ($d['metro_rows'] as $key => $m) {
            $tokens['{pct_' . $key . '}'] = self::vs_national($m['index']);
            $tokens['{drive_' . $key . '}'] = Slab_Data::money_range($m['prices']['driveway']['installed'], 0, ' to ');
        }
        return strtr($text, $tokens);
    }

    /** "Phoenix metro, AZ" -> "Phoenix". */
    public static function metro_city($name) {
        return preg_replace('/( metro)?, [A-Z]{2}$/', '', $name);
    }

    /**
     * States with one big metro name it in the title, since people search
     * the city ("cost of concrete in phoenix", Search Console 2026-11-01).
     */
    public static function title($slug) {
        $d = self::data($slug);
        if (count($d['metro_rows']) === 1) {
            $m = reset($d['metro_rows']);
            $t = 'Concrete Slab Cost in ' . $d['name'] . ' & ' . self::metro_city($m['name']) . ' (2026)';
            if (mb_strlen($t) <= 60) {
                return $t;
            }
        }
        return 'Concrete Slab Cost in ' . $d['name'] . ' (2026 Prices)';
    }

    public static function meta($slug) {
        $d = self::data($slug);
        return sprintf(
            'Concrete slab cost in %s: about %s per sq ft installed, %s the U.S. average. Patio, driveway and garage prices, plus %s frost, soil and PSI notes.',
            $d['name'] . (count($d['metro_rows']) === 1 ? ' and ' . self::metro_city(reset($d['metro_rows'])['name']) : ''),
            Slab_Data::money_range($d['prices']['slab']['psf'], 2, '–'),
            self::vs_national($d['index']),
            $d['name']
        );
    }

    public static function faqs($slug) {
        $d = self::data($slug);
        $p = $d['prices'];
        $n = $d['name'];
        $faqs = [
            [
                'q' => "How much does a concrete slab cost in {$n}?",
                'a' => sprintf(
                    'About %s per sq ft installed for a 4-inch slab with forms, a broom finish and labor, %s the national average. A 12 x 12 ft patio runs about %s and a 24 x 24 ft garage floor (5 in) about %s. Excavation, gravel and permits are extra.',
                    Slab_Data::money_range($p['slab']['psf'], 2, ' to '),
                    self::vs_national($d['index']),
                    Slab_Data::money_range($p['patio']['installed'], 0, ' to '),
                    Slab_Data::money_range($p['garage']['installed'], 0, ' to ')
                ),
            ],
            [
                'q' => "How much does a concrete driveway cost in {$n}?",
                'a' => sprintf(
                    'A 20 x 40 ft driveway, 5 inches thick with rebar and a broom finish, costs about %s installed in %s (%s per sq ft). Removing an old driveway and the street apron are extra.',
                    Slab_Data::money_range($p['driveway']['installed'], 0, ' to '),
                    $n,
                    Slab_Data::money_range($p['driveway']['psf'], 2, ' to ')
                ),
            ],
            [
                'q' => "How much is a yard of concrete in {$n}?",
                'a' => sprintf(
                    'About %s per cubic yard for 3,000 PSI ready-mix before delivery fees, and about %s for %s PSI. Small loads add a short-load fee of %s or more.',
                    Slab_Data::money_range($d['ready_mix'], 0, ' to '),
                    Slab_Data::money_range($d['psi'] > Slab_Data::get('concrete.psi_base') ? $d['ready_mix_psi'] : self::ready_mix(4000, $d['index']), 0, ' to '),
                    number_format($d['psi'] > Slab_Data::get('concrete.psi_base') ? $d['psi'] : 4000),
                    Slab_Data::mr('delivery.short_load_flat', 0, ' to ')
                ),
            ],
        ];
        foreach ($d['metro_rows'] as $m) {
            $city = self::metro_city($m['name']);
            $mp = $m['prices'];
            $faqs[] = [
                'q' => "How much does concrete cost in {$city}?",
                'a' => sprintf(
                    'In the %s area our model puts 3,000 PSI ready-mix at about %s per cubic yard before delivery, and a 4-inch slab at about %s per sq ft installed, %s the U.S. average. A 12 x 12 ft patio runs about %s and a 20 x 40 ft driveway about %s.',
                    $city,
                    Slab_Data::money_range(self::ready_mix(Slab_Data::get('concrete.psi_base'), $m['index']), 0, ' to '),
                    Slab_Data::money_range($mp['slab']['psf'], 2, ' to '),
                    self::vs_national($m['index']),
                    Slab_Data::money_range($mp['patio']['installed'], 0, ' to '),
                    Slab_Data::money_range($mp['driveway']['installed'], 0, ' to ')
                ),
            ];
        }
        $faqs[] = ['q' => self::fill($d['faq']['q'], $d), 'a' => self::fill($d['faq']['a'], $d)];
        $faqs[] = [
            'q' => "How are these {$n} prices worked out?",
            'a' => sprintf(
                'They are the site\'s national cost model with %s\'s location factor (%s) applied to ready-mix and labor, the same thing the calculator does when you pick %s. They are modelled estimates, not prices collected from %s contractors; get local quotes before you build.',
                $n,
                number_format($d['index'], 2),
                $n,
                $n
            ),
        ];
        return $faqs;
    }

    /** Hub table rows: all 50 states and DC. */
    public static function hub_rows() {
        $rows = [];
        foreach (Slab_Data::states() as $code => $s) {
            $c = Slab_Data::size_costs(20, 20, 4, $s['index']);
            $g = Slab_Data::size_costs(24, 24, 5, $s['index']);
            $slug = self::slug_for($code);
            $rows[] = $s + [
                'psf' => Slab_Data::round_out($c['installed_psf'], 0.5),
                'garage' => $g['installed'],
                'ready_mix' => self::ready_mix(Slab_Data::get('concrete.psi_base'), $s['index']),
                'url' => $slug ? self::path($slug) : '/#state-' . strtolower($code),
                'has_page' => (bool) $slug,
            ];
        }
        return $rows;
    }

    /** Locations sorted by factor, for "most and least expensive" lists. */
    public static function ranked() {
        $rows = array_values(Slab_Data::states());
        usort($rows, function ($a, $b) {
            return $b['index'] <=> $a['index'] ?: strcmp($a['name'], $b['name']);
        });
        return $rows;
    }

    public static function hub_faqs() {
        $r = self::ranked();
        $top = array_slice($r, 0, 3);
        $low = array_slice(array_reverse($r), 0, 3);
        $names = function ($rows) {
            return implode(', ', array_map(function ($s) {
                return $s['name'] . ' (' . number_format($s['index'], 2) . ')';
            }, $rows));
        };
        $hi = Slab_Data::size_costs(20, 20, 4, $r[0]['index']);
        $lo = Slab_Data::size_costs(20, 20, 4, end($r)['index']);
        return [
            ['q' => 'Which states have the most expensive concrete?', 'a' => sprintf('In our model: %s. A 20 x 20 ft, 4-inch slab there runs about %s installed, against %s in the cheapest state. Within a state, big metros such as New York City and the San Francisco Bay Area run higher still.', $names($top), Slab_Data::money_range($hi['installed'], 0, ' to '), Slab_Data::money_range($lo['installed'], 0, ' to '))],
            ['q' => 'Which states have the cheapest concrete?', 'a' => sprintf('In our model: %s. Lower labor costs are the main reason; ready-mix prices vary less between states than labor does.', $names($low))],
            ['q' => 'Why do only some states have their own page?', 'a' => sprintf('Our state prices come from one location factor per state, so a page per state would mostly repeat the same text. The %d states with their own page are large markets where climate, frost depth, soil or permits change how a slab is built. For every other state, the table here gives the price and the calculator applies the state\'s factor.', count(self::states()))],
            ['q' => 'Are these prices surveyed from contractors?', 'a' => 'No. They are modelled: the national unit prices in our cost model, with a location factor per state applied to ready-mix and labor. They are a planning starting point; local quotes can be well above or below them.'],
        ];
    }
}
