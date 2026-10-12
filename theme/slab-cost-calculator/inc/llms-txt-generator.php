<?php
/**
 * LLMS.TXT MACHINE-READABILITY GENERATOR
 * Serves a standardized /llms.txt file describing website structure, calculators, formulas, and topical resources.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_LLMS_Generator {

    public static function init() {
        add_action('init', [__CLASS__, 'register_rewrite']);
        add_action('template_redirect', [__CLASS__, 'render_llms_txt']);
    }

    public static function register_rewrite() {
        add_rewrite_rule('^llms\.txt$', 'index.php?slab_llms=1', 'top');
        add_rewrite_tag('%slab_llms%', '1');
    }

    public static function render_llms_txt() {
        if (get_query_var('slab_llms') !== '1') {
            return;
        }

        header('Content-Type: text/plain; charset=utf-8');
        header('X-Robots-Tag: noindex, follow');

        $site_url = home_url();

        echo "# Slab Cost Calculator - Construction & Concrete Estimating Platform\n";
        echo "> Authoritative, precision-engineered construction cost calculator suite and topical knowledgebase for concrete slabs, footings, reinforcement, and site preparation.\n\n";

        echo "## Core Purpose\n";
        echo "Provide transparent, line-item project budgeting for concrete slabs combining material volumes (cubic yards/meters, bag yields), base preparation, formwork, steel reinforcement, labor rates, and delivery logistics.\n\n";

        echo "## Primary Calculator\n";
        echo "- [Concrete Slab Cost Calculator]({$site_url}/): Multi-variable project cost and volume estimator.\n\n";

        echo "## Specialized Supporting Calculators\n";
        echo "- [Concrete Volume Calculator]({$site_url}/concrete-volume-calculator/): Length × Width × Thickness 3D volume in cu ft, cu yds, and m³.\n";
        echo "- [Concrete Bags Calculator]({$site_url}/concrete-bags-calculator/): Calculates 40lb, 50lb, 60lb, and 80lb pre-mixed bag requirements.\n";
        echo "- [Concrete Yard Calculator]({$site_url}/concrete-yard-calculator/): Cubic yardage calculator with automated waste allowances.\n";
        echo "- [Concrete Weight Calculator]({$site_url}/concrete-weight-calculator/): Wet and cured concrete mass in lbs, tons, and metric tonnes.\n";
        echo "- [Concrete Cost Per Square Foot Calculator]({$site_url}/concrete-cost-per-square-foot-calculator/): Unit rate financial breakdown.\n";
        echo "- [Concrete Rebar Calculator]({$site_url}/concrete-rebar-calculator/): Steel rebar stick count, overlap lap splices, and support chairs.\n";
        echo "- [Concrete Footing Calculator]({$site_url}/concrete-footing-calculator/): Continuous strip footings and round pier sonotube volume.\n";
        echo "- [Gravel Base Calculator]({$site_url}/gravel-base-calculator/): Crushed stone tonnage and yardage with compaction factors.\n";
        echo "- [Concrete Labor Cost Calculator]({$site_url}/concrete-labor-cost-calculator/): Crew hours and finishing labor cost by complexity.\n";
        echo "- [Concrete Removal Cost Calculator]({$site_url}/concrete-removal-cost-calculator/): Demolition, haul-off, and disposal fees.\n\n";

        echo "## Project Application Guides\n";
        echo "- [Concrete Patio Cost]({$site_url}/patio/): 4-inch stamped and broom-finished patio slabs.\n";
        echo "- [Concrete Driveway Cost]({$site_url}/driveway/): 5-6 inch vehicle-rated slabs with rebar.\n";
        echo "- [Garage Slab Cost]({$site_url}/garage/): Monolithic thickened edge floating garage slabs.\n";
        echo "- [Shed Slab Cost]({$site_url}/shed/): 4-inch utility shed pads.\n";
        echo "- [Foundation Slab Cost]({$site_url}/foundation/): Engineered structural slabs.\n\n";

        echo "## In-Depth Cost Guides\n";
        echo "- [10x10 Concrete Slab Cost]({$site_url}/10x10-concrete-slab-cost-breakdown/)\n";
        echo "- [12x12 Concrete Slab Cost]({$site_url}/12x12-concrete-slab-cost-breakdown/)\n";
        echo "- [20x20 Concrete Slab Cost]({$site_url}/20x20-concrete-slab-cost-breakdown/)\n";
        echo "- [24x24 Concrete Slab Cost]({$site_url}/24x24-concrete-slab-cost-breakdown/)\n";
        echo "- [Concrete Cost Per Cubic Yard]({$site_url}/concrete-cost-per-cubic-yard-pricing-explained/)\n\n";

        echo "## Knowledgebase\n";
        echo "- [Concrete Knowledgebase & Articles]({$site_url}/knowledgebase/): Full library of estimating guides, curing/timing guidance, foundation comparisons, maintenance and repair articles, and planning guides, grouped by category.\n\n";

        echo "## Transparency & Methodology\n";
        echo "- [Methodology & Formulas]({$site_url}/methodology/): Complete open mathematical formulations and testing standards.\n";
        echo "- [Editorial Policy]({$site_url}/editorial-policy/): Fact-checking, pricing data updates, and review process.\n";
        echo "- [Authors & Review Team]({$site_url}/authors/)\n";
        echo "- [Disclaimer]({$site_url}/disclaimer/): Planning guidance vs structural engineering disclaimer.\n";
        exit;
    }
}

Slab_LLMS_Generator::init();
