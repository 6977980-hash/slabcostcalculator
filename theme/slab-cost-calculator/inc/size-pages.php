<?php
/**
 * SLAB SIZE PAGES: /12x16-concrete-slab-cost/ AND /concrete-slab-cost-by-size/
 *
 * One page per common slab size, served by a rewrite rule (no Page rows in
 * the database, see inc/custom-post-types.php). Every number on a size page
 * is computed from Slab_Data with the calculator's own line items, so the
 * page, its FAQ schema and the calculator embedded on it always agree.
 *
 * Each size also carries its own written intro, project type and notes, so
 * the pages are not the same text with the numbers swapped: the project
 * type sets the recommended thickness, strength and reinforcement, and the
 * computed sections change with the size (bags or truck, how many trucks,
 * whether the chute reaches, how many joint panels, crew hours).
 *
 * The four sizes that already have full articles (10x10, 12x12, 20x20,
 * 24x24) are not repeated here; the hub links to those articles instead.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_Size_Pages {

    /** Sizes covered by full articles; listed on the hub, linked to the article. */
    public static function article_sizes() {
        return [
            '10x10' => [10, 10, 4, 'shed', '/10x10-concrete-slab-cost-breakdown/'],
            '12x12' => [12, 12, 4, 'patio', '/12x12-concrete-slab-cost-breakdown/'],
            '20x20' => [20, 20, 4, 'patio', '/20x20-concrete-slab-cost-breakdown/'],
            '24x24' => [24, 24, 5, 'garage', '/24x24-concrete-slab-cost-breakdown/'],
        ];
    }

    /** Project types: what each kind of slab needs. */
    public static function uses() {
        $code = Slab_Data::get('code');
        $p = function ($n) {
            return number_format($n) . ' PSI';
        };
        $gravel = Slab_Data::range(Slab_Data::get('site.gravel_depth_range'), ' to ') . ' in of compacted crushed stone';
        return [
            'pad' => [
                'label' => 'Hot tub & equipment pads',
                'name' => 'hot tub or equipment pad',
                'specs' => [
                    ['Thickness', '4 in; 6 in where the tub maker asks for it (common for large or swim spas)'],
                    ['Strength', $p($code['psi_exposed']) . '; ' . $p($code['psi_severe']) . ' air-entrained where it freezes'],
                    ['Base', $gravel . ', compacted in lifts'],
                    ['Reinforcement', '6x6 welded wire mesh or #3 rebar at 18 in, on chairs'],
                    ['Surface', 'Dead level (no drainage slope), broom or light trowel finish'],
                ],
                'links' => [['Concrete bags calculator', '/concrete-bags-calculator/'], ['Wire mesh calculator', '/concrete-wire-mesh-calculator/'], ['Shed pad guide', '/concrete-shed-pad-cost-and-construction/']],
            ],
            'shed' => [
                'label' => 'Sheds & storage buildings',
                'name' => 'shed floor',
                'specs' => [
                    ['Thickness', '4 in; thicken the edge to 8-12 in under the walls in frost country, or follow your permit'],
                    ['Strength', $p($code['psi_exposed']) . '; ' . $p($code['psi_severe']) . ' air-entrained where it freezes'],
                    ['Base', $gravel],
                    ['Reinforcement', '6x6 wire mesh, or #4 rebar at 18 in if a mower, ATV or car will park inside'],
                    ['Anchors', '1/2 in J-bolts in the wet concrete, 1-3/4 in in from the edge, about every 4-6 ft and within 12 in of each corner'],
                ],
                'links' => [['Shed pad cost & build guide', '/concrete-shed-pad-cost-and-construction/'], ['Shed slab specs', '/shed/'], ['Concrete form calculator', '/concrete-form-calculator/']],
            ],
            'patio' => [
                'label' => 'Patios',
                'name' => 'patio',
                'specs' => [
                    ['Thickness', '4 in'],
                    ['Strength', $p($code['psi_exposed']) . '; ' . $p($code['psi_severe']) . ' air-entrained where it freezes'],
                    ['Base', $gravel],
                    ['Reinforcement', '6x6 wire mesh or fiber mix; #3-#4 rebar on expansive clay'],
                    ['Drainage', Slab_Data::get('site.drainage_slope_in_per_ft')[0] . ' to ' . Slab_Data::get('site.drainage_slope_preferred') . ' in per foot away from the house'],
                ],
                'links' => [['Patio slab guide', '/concrete-patio-slab-cost-guide/'], ['Patio specs', '/patio/'], ['Concrete sealer types', '/do-you-need-to-seal-concrete/']],
            ],
            'carport' => [
                'label' => 'Carports & parking pads',
                'name' => 'carport or parking pad',
                'specs' => [
                    ['Thickness', '4 in for cars; 5 in for pickups, trailers and RVs'],
                    ['Strength', $p($code['psi_severe']) . ' air-entrained; ' . $p($code['psi_deicing_recommended']) . ' where de-icing salt is tracked in'],
                    ['Base', $gravel],
                    ['Reinforcement', '#4 rebar at 18 in on chairs'],
                    ['Posts', 'Carport posts on their own footings (or thickened slab under each post), sized by the carport kit or engineer'],
                ],
                'links' => [['Driveway slab specs', '/driveway/'], ['Rebar calculator', '/concrete-rebar-calculator/'], ['Footing & pier calculator', '/concrete-footing-calculator/']],
            ],
            'garage' => [
                'label' => 'Garages',
                'name' => 'garage floor',
                'specs' => [
                    ['Thickness', '4 in minimum, 5 in typical; 6 in for heavy trucks or a lift'],
                    ['Strength', $p($code['psi_severe']) . ' air-entrained (IRC minimum for garage floors in severe weathering areas)'],
                    ['Base', $gravel . ', plus a 10-mil vapor barrier under heated or finished garages'],
                    ['Reinforcement', '#4 rebar at 18 in on chairs'],
                    ['Edge', 'Monolithic thickened edge (12 in wide, 12-18 in deep) or a stem wall on footings, per your permit'],
                ],
                'links' => [['Garage slab cost guide', '/how-much-does-a-garage-slab-cost/'], ['Garage floor specs', '/garage/'], ['Monolithic slab cost', '/concrete-foundation-monolithic-slab-cost/']],
            ],
            'shop' => [
                'label' => 'Shops, pole barns & large buildings',
                'name' => 'shop or barn floor',
                'specs' => [
                    ['Thickness', '6 in for shops with heavy vehicles, lifts or machinery; 4-5 in for light storage'],
                    ['Strength', $p($code['psi_deicing_recommended']) . ' for heavy trucks and de-icing salt; ' . $p($code['psi_severe']) . ' air-entrained minimum'],
                    ['Base', $gravel . ', plus a vapor barrier if the building will be heated'],
                    ['Reinforcement', '#4 rebar at 12-18 in on chairs; heavier where an engineer specifies it'],
                    ['Edge', 'Thickened edge or footings at the walls; post-frame buildings set their posts on separate footings'],
                ],
                'links' => [['Monolithic slab foundation cost', '/concrete-foundation-monolithic-slab-cost/'], ['Foundation specs', '/foundation/'], ['Ready-mix calculator', '/ready-mix-concrete-calculator/']],
            ],
            'driveway' => [
                'label' => 'Driveways',
                'name' => 'driveway',
                'specs' => [
                    ['Thickness', '4 in for cars; 5 in standard; 6 in for RVs and delivery trucks'],
                    ['Strength', $p($code['psi_deicing_recommended']) . ' air-entrained where de-icing salt is used, otherwise ' . $p($code['psi_severe'])],
                    ['Base', $gravel],
                    ['Reinforcement', '#4 rebar at 18 in (12 in for heavy loads or cold climates) on chairs'],
                    ['Drainage', 'At least ' . Slab_Data::get('site.drainage_slope_in_per_ft')[0] . ' in per foot, away from the garage door'],
                ],
                'links' => [['Concrete driveway cost', '/how-much-does-a-concrete-driveway-cost/'], ['Driveway specs', '/driveway/'], ['Concrete vs asphalt', '/concrete-vs-asphalt-driveway/']],
            ],
        ];
    }

    /**
     * Size registry, in display order. 'l' x 'w' in feet, 't' the
     * thickness the page leads with (the project type's usual depth).
     * Tokens in 'intro' are filled from the computed figures (fill()).
     */
    public static function sizes() {
        return [
            '8x8' => ['l' => 8, 'w' => 8, 't' => 4, 'use' => 'pad',
                'intro' => 'An 8 × 8 ft pad is the usual footprint for a two- to four-person hot tub, a standby generator or a pair of heat-pump condensers. At {area} sq ft it is a small pour, {bags80} bags of 80-lb mix, but it carries a heavy, concentrated load: a filled 7 × 7 ft tub with people in it puts roughly 100 lb on every square foot. The base and a level top matter more here than on a patio of the same size.',
                'notes' => [
                    'Read the tub or generator install guide before you pour. Most ask for at least 4 in of concrete on a compacted base; some large tubs ask for 6 in.',
                    'Keep the top level. A patio gets a drainage slope; a hot tub pad should not, or the tub will sit out of level.',
                    'If the electrical feed comes up through the pad, set the conduit and sweep before the pour. Cutting into a finished pad later costs more than the pad.',
                ],
                'faq' => ['q' => 'Is a 4-inch slab enough for an 8x8 hot tub pad?', 'a' => 'For most two- to four-person tubs, yes, on a compacted gravel base with wire mesh or light rebar. Larger tubs and swim spas often call for 6 inches; follow the maker\'s install guide, because the warranty can depend on it.'],
            ],
            '8x10' => ['l' => 10, 'w' => 8, 't' => 4, 'use' => 'shed',
                'intro' => 'An 8 × 10 ft slab ({area} sq ft) is the floor for the smallest prefab storage sheds, a trash and recycling enclosure, or a grill station off the back patio. It is a one-day DIY job: {order_yd} yd³ with waste, or {bags80} bags of 80-lb mix, which two people can mix and place with a rented drum mixer in an afternoon.',
                'notes' => [
                    'Match the slab to the shed kit\'s floor frame, not its roof: the walls should sit on the slab with the siding lapping just past the edge so rain drips off instead of running underneath.',
                    'Set J-bolts or plan for wedge anchors if the shed must be tied down; many towns require it in high-wind areas.',
                    'At this size you can skip a ready-mix truck entirely; see the bags vs. truck numbers below.',
                ],
                'faq' => ['q' => 'Do I need a permit for an 8x10 shed slab?', 'a' => 'Often not: many towns exempt sheds under 120 or 200 sq ft from a building permit, but zoning setbacks still apply and some places permit any concrete flatwork. Call your building department before you dig.'],
            ],
            '10x12' => ['l' => 12, 'w' => 10, 't' => 4, 'use' => 'shed',
                'intro' => 'A 10 × 12 ft slab is the most common storage shed size in the country, big enough for a riding mower and garden tools. At {area} sq ft and 4 in thick it needs {order_yd} yd³ with waste: right at the point where bags and a small ready-mix load cost about the same, so the choice comes down to your back and your local short-load fee.',
                'notes' => [
                    'If a riding mower or ATV lives inside, use #4 rebar at 18 in rather than mesh, and pour 4 in minimum everywhere (check the depth along the forms).',
                    'A 10 × 12 shed is under most "accessory building" size limits, so a floating slab on gravel is normally accepted; frost-prone towns may still want a thickened edge.',
                    'Put a 4-ft ramp apron or a thickened lip at the door if you will drive a mower in and out.',
                ],
                'faq' => ['q' => 'How thick should a 10x12 shed slab be?', 'a' => '4 inches on 4 to 6 inches of compacted gravel is standard. Go to 5 inches, or thicken the door edge, if you will park a riding mower, ATV or small tractor in the shed.'],
            ],
            '10x16' => ['l' => 16, 'w' => 10, 't' => 4, 'use' => 'shed',
                'intro' => 'A 10 × 16 ft slab ({area} sq ft) suits a larger storage shed, a lean-to against a garage wall, or a narrow side-yard patio. Its long, narrow shape needs a control joint across the middle so it cracks where you choose; the layout below splits it into panels no longer than 1.5 times their width.',
                'notes' => [
                    'Narrow slabs crack across their width first. Cut the joint across the 10-ft side within the saw-cut window rather than hoping the slab holds together.',
                    'For a lean-to or side-yard slab, slope it away from the house wall and keep the top at least 6 in below siding.',
                    'Compare the bag and truck totals below with your local prices: the short-load fee is the number that decides it at this size.',
                ],
                'faq' => ['q' => 'Where should the control joint go on a 10x16 slab?', 'a' => 'Across the middle of the 16-ft length, cut 1 inch deep on a 4-inch slab. That makes two panels of about 10 × 8 ft, close to square, which is what keeps random cracks away.'],
            ],
            '10x20' => ['l' => 20, 'w' => 10, 't' => 4, 'use' => 'carport',
                'intro' => 'A 10 × 20 ft slab ({area} sq ft) is a parking pad for one car, a boat or utility trailer, or a long side-yard storage area. It is just over the size where ready-mix starts to win on price, and because it may carry a vehicle, it gets rebar rather than mesh.',
                'notes' => [
                    'For a trailer or pickup pad, pour 5 in thick: the 5-in row in the table below adds about a quarter more concrete.',
                    'Slope the pad along its length so water runs off one short end, away from any building.',
                    'Leave a gap with expansion joint material where the pad meets an existing driveway or garage slab.',
                ],
                'faq' => ['q' => 'Is a 10x20 slab big enough to park a car?', 'a' => 'Yes for one compact or mid-size car: 10 ft gives enough width to open doors on one side. Make it 12 ft wide if you need to open doors on both sides or park a full-size pickup.'],
            ],
            '12x16' => ['l' => 16, 'w' => 12, 't' => 4, 'use' => 'shed',
                'intro' => 'A 12 × 16 ft slab is the classic backyard workshop and large-shed size: {area} sq ft, room for a workbench along one wall and a mower along the other. At 4 in thick it takes {order_yd} yd³ with waste, so a small ready-mix load usually beats {bags80} bags on both price and labor.',
                'notes' => [
                    'If the shed will be a workshop, plan outlets and any floor-mounted equipment before the pour; a sleeve under the slab for a power feed costs a few dollars now.',
                    'Many towns cap permit-free sheds at 120 or 200 sq ft, and a 12 × 16 is {area} sq ft, so check before you build.',
                    'Use #4 rebar at 18 in if you will park a tractor or roll heavy tools; mesh is fine for storage only.',
                ],
                'faq' => ['q' => 'Do I need a permit for a 12x16 shed slab?', 'a' => 'Often yes: at {area} sq ft it is over the 120 sq ft exemption many towns use, though some exempt up to 200 sq ft. Check with your building department; zoning setbacks apply either way.'],
            ],
            '12x20' => ['l' => 20, 'w' => 12, 't' => 4, 'use' => 'patio',
                'intro' => 'A 12 × 20 ft slab ({area} sq ft) is a good-sized patio for a dining table and a lounge area, or the floor of a large shed or single carport. On a patio, the 12-ft depth from the house gives room to pull chairs out; the 20-ft run gets two control joints so the panels stay close to square.',
                'notes' => [
                    'Slope the patio away from the house, about 1/4 in per foot, so the 12-ft depth drops roughly 3 in.',
                    'Want a stamped or colored finish? The stamped cost row below shows what it adds for this size.',
                    'Keep the patio top 1-2 in below the door threshold and 6 in below wood siding.',
                ],
                'faq' => ['q' => 'How much does a stamped 12x20 patio cost?', 'a' => 'A stamped finish runs about {stamped_range} per sq ft, against {broom_range} for a broom finish. The cost section on this page shows the full installed range for a stamped 12 × 20 slab.'],
            ],
            '12x24' => ['l' => 24, 'w' => 12, 't' => 4, 'use' => 'carport',
                'intro' => 'A 12 × 24 ft slab ({area} sq ft) fits a single carport or a one-car parking pad with room to walk around the vehicle, or a long storage building. Carport kits in this size set their posts on the slab edge or on separate footings, so plan the slab and the posts together.',
                'notes' => [
                    'Check where the carport kit wants its posts: many need a 12 × 12 in footing or a thickened slab under each post, not a 4-in slab edge.',
                    'Pour 5 in thick if a pickup, trailer or RV will park on it.',
                    'At {order_yd} yd³ this is a ready-mix job; {bags80} bags would be several hours of mixing.',
                ],
                'faq' => ['q' => 'How thick should a 12x24 carport slab be?', 'a' => '4 inches with #4 rebar is fine for cars; use 5 inches for pickups and trailers. The posts usually need their own footings or a thickened section, as the carport kit specifies.'],
            ],
            '16x20' => ['l' => 20, 'w' => 16, 't' => 4, 'use' => 'patio',
                'intro' => 'A 16 × 20 ft slab ({area} sq ft) is a large family patio with room for dining, a grill and a seating group, or the floor of a backyard studio. It is a full ready-mix pour of {order_yd} yd³, and at this size a second finisher and a bull float make the difference between a smooth surface and a rushed one.',
                'notes' => [
                    'Plan where the truck parks: the chute reaches about 15-20 ft, so a patio behind the house may need a wheelbarrow crew or a pump.',
                    'Slope the patio away from the house at about 1/4 in per foot; over 16 ft that is a 4-in drop, which the forms must show.',
                    'Seal the patio after it cures (about a month) to hold up to freeze-thaw and stains.',
                ],
                'faq' => ['q' => 'How many yards of concrete for a 16x20 patio?', 'a' => 'At 4 inches thick, {net4} cubic yards net and {wst4} with waste, so order {rm4} yards. That is below many plants\' minimum load, so ask about a short-load fee.'],
            ],
            '16x24' => ['l' => 24, 'w' => 16, 't' => 4, 'use' => 'shed',
                'intro' => 'A 16 × 24 ft slab ({area} sq ft) is the floor for a large storage building, a backyard workshop or a one-car garage-style shed. It is usually above the size most towns let you build without a permit, so the inspector may set the edge detail; the cost section shows both a floating slab and the thickened-edge add-on.',
                'notes' => [
                    'Over 200 sq ft almost every town wants a building permit, and in frost areas a thickened edge or footing below the frost line.',
                    'If it will be heated or finished inside, put a vapor barrier under the slab now.',
                    'Leave a 2-ft-wide apron at the door if you will drive equipment in.',
                ],
                'faq' => ['q' => 'How much concrete for a 16x24 slab?', 'a' => 'At 4 inches thick it is {net4} cubic yards net, {wst4} with waste, so order {rm4} yards. At 6 inches it is {net6} yards net.'],
            ],
            '20x24' => ['l' => 24, 'w' => 20, 't' => 5, 'use' => 'garage',
                'intro' => 'A 20 × 24 ft slab ({area} sq ft) is a compact two-car garage: two cars fit, with little room for a workbench. Garage floors are usually poured 5 in thick with #4 rebar, which takes {order_yd} yd³ with waste.',
                'notes' => [
                    'Pour 3,500 PSI air-entrained concrete; it is the code minimum for garage floors in freeze-thaw areas.',
                    'Slope the floor toward the overhead door, about 1/8 in per foot, so melting snow drains out.',
                    'If the garage will be built on the slab, it needs a thickened edge or stem wall; the cost section shows that add-on.',
                ],
                'faq' => ['q' => 'Is 20x24 big enough for a two-car garage?', 'a' => 'It fits two cars, but tightly. 24 × 24 ft is the more common two-car size because it leaves room to open doors and walk around; see our 24x24 garage slab guide.'],
            ],
            '20x30' => ['l' => 30, 'w' => 20, 't' => 5, 'use' => 'garage',
                'intro' => 'A 20 × 30 ft slab ({area} sq ft) is a two-car garage with a full-width workshop at the back, or a large patio and outdoor kitchen. At 5 in thick it is {order_yd} yd³ with waste, more than one standard truck carries, so the plant sends {trucks} trucks and the timing between them matters.',
                'notes' => [
                    'Ask the plant to space the second truck so the first load is still workable when it arrives; a cold joint between loads is a weak spot.',
                    'The 30-ft length gets two interior control joints; mark them on the forms before the pour.',
                    'For a workshop end, plan floor drains or a sleeve for a future air line before the pour.',
                ],
                'faq' => ['q' => 'How many yards of concrete for a 20x30 slab?', 'a' => 'At 4 inches, {net4} cubic yards net ({wst4} with waste). At 5 inches, {net5} net and {wst5} with waste, so order {rm5} yards, which takes {trucks} trucks.'],
            ],
            '24x30' => ['l' => 30, 'w' => 24, 't' => 5, 'use' => 'garage',
                'intro' => 'A 24 × 30 ft slab ({area} sq ft) is a roomy two-car garage with space for a workbench, storage and a second fridge. Poured 5 in thick it needs {order_yd} yd³ in {trucks} trucks, and a crew of three or four finishers working together before it sets.',
                'notes' => [
                    'Use 3,500 PSI air-entrained concrete, or 4,000 PSI if salt-covered cars park inside.',
                    'Ask for a vapor barrier if you may heat or finish the garage later.',
                    'A thickened edge or stem wall is needed under the walls; the cost section shows the thickened-edge add-on.',
                ],
                'faq' => ['q' => 'How much does a 24x30 garage slab cost?', 'a' => 'The installed range on this page covers a 5-inch slab with forms, vapor barrier, #4 rebar and a broom finish. A monolithic thickened edge adds the amount shown in the cost section; excavation and gravel are extra.'],
            ],
            '24x36' => ['l' => 36, 'w' => 24, 't' => 5, 'use' => 'garage',
                'intro' => 'A 24 × 36 ft slab ({area} sq ft) is a three-car garage, or a two-car garage with a deep shop bay. It is a big residential pour: {order_yd} yd³ at 5 in, which the plant sends in {trucks} trucks, and a slab this wide is hard to reach from the chute on every side.',
                'notes' => [
                    'Plan truck access to at least two sides, or budget for a line pump.',
                    'A three-car garage often has one taller bay for a pickup or boat; keep the slab level across the bays and slope it to the doors.',
                    'Mark control joints between the bays so they line up with the door openings.',
                ],
                'faq' => ['q' => 'How many yards of concrete for a 24x36 garage slab?', 'a' => 'At 5 inches thick, {net5} cubic yards net and {wst5} with waste, so order {rm5} yards in {trucks} trucks. At 4 inches it is {net4} yards net.'],
            ],
            '30x30' => ['l' => 30, 'w' => 30, 't' => 6, 'use' => 'shop',
                'intro' => 'A 30 × 30 ft slab ({area} sq ft) is a popular detached shop: two cars or one car and a lift, plus benches and tools. Shops with a two-post lift or heavy equipment are usually poured 6 in thick with 4,000 PSI concrete, {order_yd} yd³ here, so the lift anchors have enough concrete to grip.',
                'notes' => [
                    'Check the lift maker\'s anchor rules (minimum thickness, PSI and distance from joints) before placing the control joints.',
                    'Mark the lift location on the forms so no control joint runs under its posts.',
                    'Plan floor drains, an air line sleeve and the electrical feed before the pour.',
                ],
                'faq' => ['q' => 'How thick should a 30x30 shop slab be?', 'a' => '6 inches with #4 rebar is the common choice for a shop with a car lift or heavy equipment. A light-use storage shop can be 4 to 5 inches. Follow your lift maker\'s anchor requirements.'],
            ],
            '30x40' => ['l' => 40, 'w' => 30, 't' => 6, 'use' => 'shop',
                'intro' => 'A 30 × 40 ft slab ({area} sq ft) is the most common pole barn, metal building and barndominium size. At 6 in it is {order_yd} yd³ of concrete in {trucks} trucks, a pour that needs a full crew, a plan for truck access, and usually a pump or a buggy.',
                'notes' => [
                    'Metal and post-frame buildings set their columns on separate footings or piers; pour those first and form the slab around them.',
                    'For a barndominium or any heated space, add a vapor barrier and, where it freezes, perimeter insulation.',
                    'Ask about a laser screed or a second finishing crew: {area} sq ft is a lot of surface to finish before it sets.',
                ],
                'faq' => ['q' => 'How much does a 30x40 concrete slab cost?', 'a' => 'The installed range on this page is for a 6-inch slab with forms, vapor barrier, #4 rebar and a broom finish. Excavation, gravel, a thickened edge and a pump are extra; the page lists each.'],
            ],
            '30x50' => ['l' => 50, 'w' => 30, 't' => 6, 'use' => 'shop',
                'intro' => 'A 30 × 50 ft slab ({area} sq ft) is a large shop or pole barn with room for an RV bay or equipment. At 6 in it is {order_yd} yd³, {trucks} trucks, and enough area that many crews pour it in two halves with a construction joint.',
                'notes' => [
                    'Splitting the pour into two days with a doweled construction joint is normal at this size; schedule the joint on a control-joint line.',
                    'RV bays need 6 in minimum with #4 rebar at 12 in under the wheel paths.',
                    'Budget a pump or a truck path along the long side; the chute will not reach the middle from one side.',
                ],
                'faq' => ['q' => 'Can a 30x50 slab be poured in one day?', 'a' => 'Yes with a large crew, a pump and well-spaced trucks, but many contractors pour it in two halves with a doweled construction joint to keep each pour manageable.'],
            ],
            '40x40' => ['l' => 40, 'w' => 40, 't' => 6, 'use' => 'shop',
                'intro' => 'A 40 × 40 ft slab ({area} sq ft) is a square shop, a farm equipment building or a commercial-style garage. Being square, it gets the same joint grid both ways; at 6 in it takes {order_yd} yd³ and a crew of five or more with power trowels.',
                'notes' => [
                    'The middle of a 40-ft-wide slab is out of chute reach from any side; plan a pump or a buggy.',
                    'Power trowels (riding or walk-behind) are standard at this size; ask whether the bid includes them.',
                    'Curing compound or wet curing over 1,600 sq ft is worth asking about in the bid; a poorly cured shop floor dusts and curls.',
                ],
                'faq' => ['q' => 'How many yards of concrete for a 40x40 slab?', 'a' => 'At 4 inches, {net4} cubic yards net. At 6 inches, {net6} net and {wst6} with waste, which is {trucks} truckloads.'],
            ],
            '40x60' => ['l' => 60, 'w' => 40, 't' => 6, 'use' => 'shop',
                'intro' => 'A 40 × 60 ft slab ({area} sq ft) is the standard big pole barn, equipment shed or shop building. At 6 in it is {order_yd} yd³, {trucks} trucks, and a job most crews pour in two or three sections over several days.',
                'notes' => [
                    'Get the slab and building engineered together: column footings, door openings and the joint plan all interact.',
                    'Use 4,000 PSI with #4 rebar at 12-18 in for equipment and trucks; ask whether fiber is added for surface cracking.',
                    'At this size small price differences per yard add up: compare at least three ready-mix suppliers.',
                ],
                'faq' => ['q' => 'How much does a 40x60 concrete slab cost?', 'a' => 'The installed range on this page is for a 6-inch slab with forms, vapor barrier, #4 rebar and a broom finish at national average prices. Site work, gravel, footings and a pump are extra and are listed on the page.'],
            ],
            '12x40' => ['l' => 40, 'w' => 12, 't' => 5, 'use' => 'driveway',
                'intro' => 'A 12 × 40 ft slab ({area} sq ft) is a single-car driveway from the street to a one-car garage, with room to open doors on both sides. Driveways are poured 5 in thick in most places; this one takes {order_yd} yd³, and its long narrow shape gets a joint about every 12 ft.',
                'notes' => [
                    'Keep the joints square to the length and at most 1.5 times the 12-ft width apart, so the panels do not crack across their middle.',
                    'Where the driveway meets the street apron or garage slab, use expansion joint material.',
                    'Use 4,000 PSI air-entrained concrete if you use de-icing salt.',
                ],
                'faq' => ['q' => 'How wide should a single-car driveway be?', 'a' => '10 feet is the minimum and 12 feet is more comfortable, leaving room to open doors and step onto the slab. A 12 × 40 ft driveway covers a typical front setback.'],
            ],
            '20x40' => ['l' => 40, 'w' => 20, 't' => 5, 'use' => 'driveway',
                'intro' => 'A 20 × 40 ft slab ({area} sq ft) is a two-car-wide driveway: two cars side by side, two deep. At 5 in it is {order_yd} yd³ with waste in {trucks} trucks, and the joint grid splits it into {panels} panels.',
                'notes' => [
                    'Run a center joint down the length so each lane is its own row of panels.',
                    'A driveway this size usually needs a permit and an approved street apron (curb cut); check before you form.',
                    'Wait {cars_days} days before parking on it, longer for trucks.',
                ],
                'faq' => ['q' => 'How much does a 20x40 concrete driveway cost?', 'a' => 'The installed range on this page is for a 5-inch driveway with forms, #4 rebar and a broom finish. Tear-out of an old driveway, excavation and the street apron are extra.'],
            ],
        ];
    }

    /** True for a registered size slug ('12x16'). */
    public static function exists($size) {
        $all = self::sizes();
        return isset($all[$size]);
    }

    public static function path($size) {
        return '/' . $size . '-concrete-slab-cost/';
    }

    public static function hub_path() {
        return '/concrete-slab-cost-by-size/';
    }

    /** Regex alternatives for the rewrite rule: only registered sizes route. */
    public static function route_regex() {
        return '^(' . implode('|', array_map('preg_quote', array_keys(self::sizes()))) . ')-concrete-slab-cost/?$';
    }

    /** Every computed figure for one size page. */
    public static function data($size) {
        $all = self::sizes();
        if (!isset($all[$size])) {
            return null;
        }
        $s = $all[$size];
        $sd = Slab_Data::all();
        $l = $s['l'];
        $w = $s['w'];
        $t = $s['t'];
        $c = Slab_Data::size_costs($l, $w, $t);
        $e = $c['est'];
        $order_cuft = $e['order_yd'] * 27;
        $by_t = [];
        foreach ([4, 5, 6] as $th) {
            $ct = Slab_Data::size_costs($l, $w, $th);
            $et = $ct['est'];
            $by_t[$th] = [
                'net_yd' => $et['net_yd'],
                'order_yd' => $et['order_yd'],
                'ready_mix_yd' => Slab_Data::order_step($et['order_yd']),
                'bags80' => $et['bags80'],
                'bags60' => $et['bags60'],
                'bags40' => Slab_Data::bags($et['order_yd'] * 27, '40'),
                'installed' => $ct['installed'],
            ];
        }
        $waste = [];
        foreach ([0, 5, 10, 15] as $pct) {
            $cuft = $e['net_yd'] * 27 * (1 + $pct / 100);
            $waste[$pct] = ['yd' => $cuft / 27, 'bags80' => Slab_Data::bags($cuft, '80')];
        }
        $gravel_yd = $e['area'] * $sd['site']['gravel_depth_in'] / 12 / 27 * $sd['site']['gravel_compaction'];
        $heavy = in_array($s['use'], ['shop', 'driveway'], true);
        $m = $sd['monolithic'];
        $edge = in_array($s['use'], ['garage', 'shop', 'shed'], true) && $e['area'] >= 150
            ? [Slab_Data::edge_yd($l, $w, $t, $m['edge_depth_in'][0]), Slab_Data::edge_yd($l, $w, $t, $m['edge_depth_in'][1])]
            : null;
        $f = $sd['finish'];
        $stamped_add = $e['area'] * ($f['stamped']['default'] - $f['broom']['default']);

        return [
            'size' => $size,
            'l' => $l,
            'w' => $w,
            't' => $t,
            'use' => $s['use'],
            'area' => $e['area'],
            'perimeter' => $e['perimeter'],
            'est' => $e,
            'installed' => $c['installed'],
            'installed_psf' => $c['installed_psf'],
            'diy' => $c['diy'],
            'by_t' => $by_t,
            'waste' => $waste,
            'bvt' => Slab_Data::bags_vs_truck($e['net_yd'], true),
            'bvt_hand' => Slab_Data::bags_vs_truck($e['net_yd'], false),
            'ready_mix_yd' => Slab_Data::order_step($e['order_yd']),
            'bags40' => Slab_Data::bags($order_cuft, '40'),
            'gravel_yd' => $gravel_yd,
            'gravel_tons' => $gravel_yd * $sd['site']['gravel_tons_per_yd'],
            'forms' => Slab_Calculator_Pages::form_result($l, $w, $t, 12, $sd['site']['stake_spacing_ft'][0]),
            'rebar' => Slab_Data::rebar_grid($l, $w, $sd['reinforcement']['rebar_base_spacing_in']),
            'rebar_tight' => $heavy ? Slab_Data::rebar_grid($l, $w, $sd['reinforcement']['rebar_tight_spacing_in']) : null,
            'mesh' => Slab_Calculator_Pages::mesh_result($l, $w, 'sheet'),
            'joints' => Slab_Data::joint_layout($l, $w, $t),
            'crew_hours' => $e['area'] * $sd['labor']['crew_hours_per_sqft'],
            'unload_min' => [Slab_Data::order_step($e['order_yd']) * $sd['delivery']['unload_min_per_yd'][0], Slab_Data::order_step($e['order_yd']) * $sd['delivery']['unload_min_per_yd'][1]],
            'chute_short' => min($l, $w) > $sd['site']['chute_reach_ft'][1],
            'edge_yd' => $edge,
            'edge_cost' => $edge ? Slab_Data::round_pair([$edge[0] * $sd['concrete']['ready_mix_default'], $edge[1] * $sd['concrete']['ready_mix_default']], 10) : null,
            'stamped' => $s['use'] === 'patio' ? Slab_Data::round_pair([$c['installed'][0] + $stamped_add, $c['installed'][1] + $stamped_add * (1 + $sd['overhead_profit'])], 10) : null,
            'site_prep' => Slab_Data::round_pair([$e['area'] * Slab_Data::site_prep_psf()[0], $e['area'] * Slab_Data::site_prep_psf()[1]], 10),
        ];
    }

    /** Fill {tokens} in the written text with this size's figures. */
    public static function fill($text, $d) {
        return strtr($text, [
            '{area}' => number_format($d['area']),
            '{order_yd}' => number_format($d['est']['order_yd'], 2),
            '{bags80}' => number_format($d['est']['bags80']),
            '{cars_days}' => (string) Slab_Data::get('curing.cars_days'),
            '{net4}' => number_format($d['by_t'][4]['net_yd'], 2),
            '{net5}' => number_format($d['by_t'][5]['net_yd'], 2),
            '{net6}' => number_format($d['by_t'][6]['net_yd'], 2),
            '{wst4}' => number_format($d['by_t'][4]['order_yd'], 2),
            '{wst5}' => number_format($d['by_t'][5]['order_yd'], 2),
            '{wst6}' => number_format($d['by_t'][6]['order_yd'], 2),
            '{rm4}' => Slab_Data::num($d['by_t'][4]['ready_mix_yd']),
            '{rm5}' => Slab_Data::num($d['by_t'][5]['ready_mix_yd']),
            '{rm6}' => Slab_Data::num($d['by_t'][6]['ready_mix_yd']),
            '{trucks}' => (string) $d['bvt']['trucks'],
            '{panels}' => (string) ($d['joints']['panels_l'] * $d['joints']['panels_w']),
            '{stamped_range}' => Slab_Data::mr('finish.stamped.range', 0, ' to '),
            '{broom_range}' => Slab_Data::mr('finish.broom.range', 2, ' to '),
        ]);
    }

    /** "12 × 16 ft" */
    public static function dims($size) {
        list($a, $b) = explode('x', $size);
        return $a . ' × ' . $b . ' ft';
    }

    public static function title($size) {
        return $size . ' Concrete Slab Cost: Bags, Yards & Price';
    }

    public static function h1($size) {
        return $size . ' Concrete Slab: Cost, Yards & Bags of Concrete';
    }

    public static function meta($size) {
        $d = self::data($size);
        return sprintf(
            '%s %s concrete slab (%s sq ft) costs %s installed. At %d in thick it takes %s yd³ with waste or %s bags of 80-lb mix. Full materials list.',
            preg_match('/^(8|11|18)x/', $size) ? 'An' : 'A',
            $size,
            number_format($d['area']),
            Slab_Data::money_range($d['installed'], 0, '–'),
            $d['t'],
            number_format($d['est']['order_yd'], 2),
            number_format($d['est']['bags80'])
        );
    }

    /** The visible FAQ and FAQPage schema for one size. */
    public static function faqs($size) {
        $d = self::data($size);
        $s = self::sizes()[$size];
        $sd = Slab_Data::all();
        $dims = $d['l'] . ' x ' . $d['w'];
        $bt = $d['by_t'];
        $b = $d['bvt'];
        $faqs = [
            [
                'q' => "How much does a {$size} concrete slab cost?",
                'a' => sprintf(
                    'About %s installed for a %d-inch slab with forms, a broom finish and labor (%s per sq ft), at U.S. average prices. DIY materials run about %s. Site prep and a gravel base add about %s.',
                    Slab_Data::money_range($d['installed'], 0, ' to '),
                    $d['t'],
                    Slab_Data::money_range(Slab_Data::round_out($d['installed_psf'], 0.5), 2, ' to '),
                    Slab_Data::money_range($d['diy'], 0, ' to '),
                    Slab_Data::money_range($d['site_prep'], 0, ' to ')
                ),
            ],
            [
                'q' => "How many bags of concrete do I need for a {$size} slab?",
                'a' => sprintf(
                    'At 4 inches thick with %d%% waste: %s bags of 80-lb mix, %s bags of 60-lb mix or %s bags of 40-lb mix. At %d inches: %s bags of 80-lb mix.',
                    $sd['concrete']['waste_pct'],
                    number_format($bt[4]['bags80']),
                    number_format($bt[4]['bags60']),
                    number_format($bt[4]['bags40']),
                    $d['t'] === 4 ? 6 : $d['t'],
                    number_format($bt[$d['t'] === 4 ? 6 : $d['t']]['bags80'])
                ),
            ],
            [
                'q' => "How many yards of concrete for a {$size} slab?",
                'a' => sprintf(
                    'At 4 inches, %s cubic yards (order %s with waste); at 5 inches, %s (order %s); at 6 inches, %s (order %s). Ready-mix is sold in quarter yards, so round up.',
                    number_format($bt[4]['net_yd'], 2), number_format($bt[4]['ready_mix_yd'], 2),
                    number_format($bt[5]['net_yd'], 2), number_format($bt[5]['ready_mix_yd'], 2),
                    number_format($bt[6]['net_yd'], 2), number_format($bt[6]['ready_mix_yd'], 2)
                ),
            ],
            [
                'q' => "Should I use bags or ready-mix for a {$size} slab?",
                'a' => $b['cheaper'] === 'bags'
                    ? sprintf('Bags are a little cheaper here: %s bags cost about %s with a rented mixer, against about %s for a %s-yard ready-mix load with delivery and short-load fees. Mixing takes about %s hours for a small crew.', number_format($b['bags']), Slab_Data::money($b['bag_cost'], 0), Slab_Data::money($b['truck_cost'], 0), Slab_Data::num($b['order_yd']), Slab_Data::num(round($b['mix_hours'], 1)))
                    : sprintf('Ready-mix. %s yards delivered costs about %s, while %s bags would cost about %s and take roughly %s hours of mixing. Bags only win below about %s cubic yards.', Slab_Data::num($b['order_yd']), Slab_Data::money($b['truck_cost'], 0), number_format($b['bags']), Slab_Data::money($b['bag_cost'], 0), Slab_Data::num(round($b['mix_hours'])), Slab_Data::num(Slab_Data::break_even_yd(true))),
            ],
            [
                'q' => "How much rebar do I need for a {$size} slab?",
                'a' => sprintf(
                    'A #4 grid at %d inches each way needs about %s linear feet of bar including laps: %d twenty-foot sticks and about %d chairs. Welded wire mesh instead takes %d sheets of 5 x 10 ft.',
                    $sd['reinforcement']['rebar_base_spacing_in'],
                    number_format(round($d['rebar']['lin_ft'])),
                    $d['rebar']['sticks'],
                    $d['rebar']['chairs'],
                    $d['mesh']['count']
                ),
            ],
        ];
        if (!empty($s['faq'])) {
            $faqs[] = ['q' => self::fill($s['faq']['q'], $d), 'a' => self::fill($s['faq']['a'], $d)];
        }
        return $faqs;
    }

    /** Hub rows: every size, page or article, grouped by project type. */
    public static function hub_rows() {
        $rows = [];
        foreach (self::sizes() as $size => $s) {
            $rows[] = ['size' => $size, 'l' => $s['l'], 'w' => $s['w'], 't' => $s['t'], 'use' => $s['use'], 'url' => self::path($size)];
        }
        foreach (self::article_sizes() as $size => $a) {
            $rows[] = ['size' => $size, 'l' => $a[0], 'w' => $a[1], 't' => $a[2], 'use' => $a[3], 'url' => $a[4]];
        }
        usort($rows, function ($a, $b) {
            return $a['l'] * $a['w'] <=> $b['l'] * $b['w'];
        });
        foreach ($rows as $i => $r) {
            $c = Slab_Data::size_costs($r['l'], $r['w'], $r['t']);
            $rows[$i]['area'] = $r['l'] * $r['w'];
            $rows[$i]['order_yd'] = $c['est']['order_yd'];
            $rows[$i]['bags80'] = $c['est']['bags80'];
            $rows[$i]['installed'] = $c['installed'];
            $rows[$i]['psf'] = $c['installed_psf'];
        }
        return $rows;
    }

    public static function hub_faqs() {
        $small = Slab_Data::size_costs(8, 10, 4);
        $big = Slab_Data::size_costs(60, 40, 4);
        $be = Slab_Data::break_even_yd(true);
        return [
            ['q' => 'Why does a small slab cost more per square foot?', 'a' => sprintf('Delivery fees, setup and the minimum crew time are about the same for a small slab as a large one. At 4 inches, an 8 x 10 slab works out to about %s per sq ft installed and a 40 x 60 slab to about %s.', Slab_Data::money_range(Slab_Data::round_out($small['installed_psf'], 0.5), 2, ' to '), Slab_Data::money_range(Slab_Data::round_out($big['installed_psf'], 0.5), 2, ' to '))],
            ['q' => 'At what size should I order ready-mix instead of bags?', 'a' => sprintf('Around %s cubic yards with a rented mixer, which is a 4-inch slab of roughly %d sq ft. Below that, bags usually cost less once the delivery and short-load fees are counted.', Slab_Data::num($be), (int) round($be * 27 * 12 / 4 / 10) * 10)],
            ['q' => 'What thickness do these size pages assume?', 'a' => 'Each size page leads with the usual thickness for its project type: 4 inches for pads, sheds and patios, 5 inches for garages and driveways, and 6 inches for shops and barns. Every page also lists 4, 5 and 6-inch quantities.'],
        ];
    }
}
