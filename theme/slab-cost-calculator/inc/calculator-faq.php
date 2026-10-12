<?php
/**
 * SHARED CALCULATOR FAQ DATA SOURCE
 *
 * Single source of truth for the per-calculator FAQ content used in TWO
 * places: the visible, on-page FAQ accordion (template-parts/calculator-faq.php)
 * and the matching FAQPage JSON-LD (inc/schema-generator.php). Keeping both
 * driven from this one array guarantees the structured data always matches
 * what a visitor actually sees on the page — mismatched/invisible FAQ schema
 * is against Google's structured-data guidelines and is exactly the kind of
 * thing that can trigger a manual action or a "spam" schema warning.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_Calculator_FAQ {

    /**
     * Returns an array of ['q' => ..., 'a' => ...] pairs for the given
     * calculator slug, using the same slug-substring detection the
     * calculator widget itself uses (see template-calculator-single.php).
     */
    public static function get_faqs_for_slug($slug) {
        $slug = (string) $slug;

        if (strpos($slug, 'volume') !== false || strpos($slug, 'yard') !== false) {
            return [
                ['q' => 'How do I calculate concrete volume for a slab?', 'a' => 'Multiply length (ft) × width (ft) × thickness (ft), where thickness in inches is divided by 12. Divide the result by 27 to convert cubic feet to cubic yards, since ready-mix concrete is ordered by the cubic yard.'],
                ['q' => 'Why does the calculator show cubic feet, cubic yards, and cubic meters?', 'a' => 'Ready-mix suppliers price and deliver by the cubic yard (or cubic meter outside the US), while smaller material quantities like bags are easier to plan in cubic feet. Showing all three avoids a manual unit conversion.'],
                ['q' => 'Should I order the exact calculated volume?', 'a' => 'No — add a waste allowance (typically 5%-10%, more on uneven or excavated grade) to cover subgrade irregularities, form deflection, and spillage. Running short mid-pour risks a cold joint.'],
            ];
        }

        if (strpos($slug, 'bag') !== false) {
            return [
                ['q' => 'How many bags of concrete do I need for my slab?', 'a' => 'Calculate the cubic feet of concrete needed (length × width × thickness ÷ 12), then divide by the bag\'s yield: 0.60 cu ft for an 80 lb bag, 0.45 for 60 lb, 0.375 for 50 lb, and 0.30 for 40 lb.'],
                ['q' => 'Is bagged concrete cheaper than ready-mix for my project?', 'a' => 'For small pours (under roughly 1-1.5 cubic yards), bagged pre-mixed concrete is usually more cost-effective once you factor in ready-mix minimum-load and short-load delivery fees. Beyond that volume, ready-mix is typically cheaper per yard.'],
                ['q' => 'Do bag yields already include water and mixing?', 'a' => 'Yes. The published yield (e.g., 0.60 cu ft for an 80 lb bag) is the finished, mixed volume after water is added — you don\'t need to apply any additional dry-to-wet conversion on top of it.'],
            ];
        }

        if (strpos($slug, 'rebar') !== false || strpos($slug, 'mesh') !== false) {
            return [
                ['q' => 'What rebar spacing should I use for a residential slab?', 'a' => 'A #4 (1/2") rebar grid on 18-inch centers is standard for residential driveways and garage floors. Heavier-duty applications (RV pads, commercial) commonly tighten to 12-inch centers; light pedestrian flatwork can use 24-inch spacing or welded wire mesh instead.'],
                ['q' => 'How much rebar overlap (lap splice) do I need?', 'a' => 'Per ACI 318 guidance, splices should overlap by at least 30 bar diameters — about 15 inches for #4 rebar. This calculator adds a 10% linear-footage allowance to account for splices across a full grid.'],
                ['q' => 'Should I use rebar or welded wire mesh?', 'a' => 'Rebar on chairs (held at mid-depth) provides more reliable, consistent reinforcement for driveways and garages carrying vehicle loads. Wire mesh is commonly used for lighter pedestrian flatwork like patios and walkways, though it must also be supported at mid-depth to be effective.'],
            ];
        }

        if (strpos($slug, 'gravel') !== false) {
            return [
                ['q' => 'How thick should the gravel base be under a concrete slab?', 'a' => '4 inches of compacted crushed stone (#57 or similar dense-graded aggregate) is standard for most residential slabs; freeze-thaw climates or poor soil may call for 6 inches or more.'],
                ['q' => 'Why does the calculator add a compaction factor?', 'a' => 'Loose crushed stone compacts down by roughly 12%-15% once run over with a plate compactor, so this calculator applies a 1.15 multiplier to the loose volume to make sure enough material is delivered to reach full compacted depth.'],
                ['q' => 'How many tons of gravel is a cubic yard?', 'a' => 'Crushed stone weighs approximately 1.4 tons per cubic yard (about 2,800 lbs), though density varies somewhat by material type and moisture content.'],
            ];
        }

        if (strpos($slug, 'footing') !== false) {
            return [
                ['q' => 'How do I calculate concrete for a continuous strip footing?', 'a' => 'Multiply the total run length (ft) by the footing width and depth (both converted from inches to feet), then divide by 27 to get cubic yards. This calculator adds your chosen waste allowance on top.'],
                ['q' => 'How is a round pier or sonotube footing calculated differently?', 'a' => 'Pier volume uses the cylinder formula: π × radius² × depth, where the radius is half the sonotube diameter. Continuous strip footings instead use length × width × depth since they\'re a rectangular prism.'],
                ['q' => 'What depth should a footing be poured to?', 'a' => 'Footing depth is primarily driven by your local frost line depth and soil bearing capacity, both of which are set by your local building code — always confirm with your local building department or a licensed engineer before pouring a structural footing.'],
            ];
        }

        if (strpos($slug, 'weight') !== false) {
            return [
                ['q' => 'How much does a concrete slab weigh?', 'a' => 'Normal-weight concrete weighs approximately 145 lbs per cubic foot (about 3,915 lbs per cubic yard). Multiply your slab\'s volume in cubic feet by 145 to estimate total wet weight.'],
                ['q' => 'Does cured concrete weigh less than freshly poured concrete?', 'a' => 'Slightly — cured (dry) concrete typically weighs a few percent less than its freshly-poured "wet" weight due to bleed-water loss during the roughly 28-day cure. This is an approximation, not a precise mix-specific figure.'],
                ['q' => 'Why does slab weight matter for my project?', 'a' => 'Weight determines what a supporting structure, trailer, or piece of equipment needs to be rated for — relevant for elevated slabs, equipment pads, or hauling away demolished concrete.'],
            ];
        }

        if (strpos($slug, 'removal') !== false) {
            return [
                ['q' => 'How much does it cost to remove a concrete slab?', 'a' => 'Demolition and haul-off typically runs $3-$4 per square foot for a standard 4-inch unreinforced slab, increasing with greater thickness and if the slab contains rebar (harder to break up).'],
                ['q' => 'How much does removed concrete debris weigh?', 'a' => 'Roughly the same as the original pour — about 145 lbs per cubic foot — so a 400 sq ft, 4-inch slab produces close to 10 tons of debris that needs to be hauled and disposed of.'],
                ['q' => 'Does rebar in the old slab affect removal cost?', 'a' => 'Yes — reinforced slabs take longer to break apart with a jackhammer and require separating out the steel before disposal, which is reflected as a per-square-foot surcharge in this calculator.'],
            ];
        }

        if (strpos($slug, 'labor') !== false) {
            return [
                ['q' => 'How much does concrete finishing labor cost per square foot?', 'a' => 'Standard broom-finish flatwork labor typically runs $5.00 per square foot; decorative stamped or stained finishes run higher (around $10.00/sq ft) due to the extra tooling and working time involved.'],
                ['q' => 'How many crew hours does a typical slab pour take?', 'a' => 'As a rough planning figure, budget about 0.035 crew-hours per square foot for a standard finish (so roughly 14 hours for a 400 sq ft slab with a typical crew), more for stamped or difficult-access work.'],
                ['q' => 'Does labor cost include site prep and materials?', 'a' => 'No — this estimates finishing/placement labor only. Excavation, gravel base, forms, and concrete materials are each estimated separately in the full project calculator.'],
            ];
        }

        // Default: the multi-scope primary calculator / general project estimating.
        return [
            ['q' => 'What does this calculator include in the total cost?', 'a' => 'Concrete material (ready-mix or bagged), optional site prep and gravel base, formwork, vapor barrier, reinforcement, surface finish, labor, delivery/equipment, plus any markup and contingency you specify.'],
            ['q' => 'Should I use ready-mix or bagged concrete?', 'a' => 'Ready-mix delivered by truck is generally more economical past roughly 1-1.5 cubic yards; smaller pours are usually cheaper and easier with pre-mixed bags.'],
            ['q' => 'How accurate is this estimate?', 'a' => 'It\'s a budgeting and planning tool built on standard engineering formulas and editable rate inputs, not a substitute for a formal contractor bid or a licensed engineer\'s review — actual costs vary by region, site conditions, and supplier pricing.'],
        ];
    }
}
