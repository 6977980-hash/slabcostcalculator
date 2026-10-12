/**
 * SUPPORTING CONCRETE CALCULATORS SUITE
 * Handles standalone specialized calculations across the topical authority network.
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    // 1. Concrete Volume / Cubic Yard Calculator
    initVolumeCalculator();
    
    // 2. Concrete Bags Calculator
    initBagsCalculator();

    // 3. Concrete Rebar & Wire Mesh Calculator
    initRebarCalculator();

    // 4. Concrete Footing & Pier Calculator
    initFootingCalculator();

    // 4b. Concrete Weight Calculator
    initWeightCalculator();

    // 5. Gravel Base / Sub-base Calculator
    initGravelCalculator();

    // 6. Concrete Removal & Demolition Cost Calculator
    initRemovalCalculator();

    // 7. Concrete Labor Cost Calculator
    initLaborCalculator();

    // 8. General Interactive Sliders / Inputs
    initSupportingInputs();
  });

  // 1. VOLUME / YARDS CALCULATOR
  function initVolumeCalculator() {
    const el = document.getElementById('subcalc-volume');
    if (!el) return;

    const len = el.querySelector('#vol-length');
    const wid = el.querySelector('#vol-width');
    const thk = el.querySelector('#vol-thick');
    const unit = el.querySelector('#vol-unit');
    const outCuFt = el.querySelector('#vol-out-cuft');
    const outCuYd = el.querySelector('#vol-out-cuyd');
    const outCuM = el.querySelector('#vol-out-cum');

    function calc() {
      const l = parseFloat(len.value) || 0;
      const w = parseFloat(wid.value) || 0;
      const t = parseFloat(thk.value) || 0;
      const isImp = !unit || unit.value === 'imperial';

      let cuft = 0, cuyd = 0, cum = 0;
      if (isImp) {
        cuft = l * w * (t / 12);
        cuyd = cuft / 27;
        cum = cuft * 0.0283168;
      } else {
        cum = l * w * (t / 100);
        cuft = cum / 0.0283168;
        cuyd = cuft / 27;
      }

      if (outCuFt) outCuFt.textContent = cuft.toFixed(2) + ' ft³';
      if (outCuYd) outCuYd.textContent = cuyd.toFixed(2) + ' yd³';
      if (outCuM) outCuM.textContent = cum.toFixed(2) + ' m³';
    }

    [len, wid, thk, unit].forEach(inp => {
      if (inp) {
        inp.addEventListener('input', calc);
        inp.addEventListener('change', calc);
      }
    });
    calc();
  }

  // 2. BAGS CALCULATOR
  function initBagsCalculator() {
    const el = document.getElementById('subcalc-bags');
    if (!el) return;

    const sqft = el.querySelector('#bags-sqft');
    const thk = el.querySelector('#bags-thick');
    const bagSize = el.querySelector('#bags-size');
    const waste = el.querySelector('#bags-waste');
    const outBags = el.querySelector('#bags-out-total');
    const outCost = el.querySelector('#bags-out-cost');
    const bagPrice = el.querySelector('#bags-price');

    function calc() {
      const area = parseFloat(sqft.value) || 0;
      const t = parseFloat(thk.value) || 4;
      const size = bagSize ? bagSize.value : '80';
      const wPct = parseFloat(waste.value) || 10;
      const price = parseFloat(bagPrice ? bagPrice.value : 6.50) || 6.50;

      const yields = { '80': 0.60, '60': 0.45, '50': 0.375, '40': 0.30 };
      const bagYield = yields[size] || 0.60;

      const cuft = area * (t / 12);
      const withWaste = cuft * (1 + (wPct / 100));
      const count = Math.ceil(withWaste / bagYield);
      const cost = count * price;

      if (outBags) outBags.textContent = `${count} bags (${size} lbs each)`;
      if (outCost) outCost.textContent = `$${cost.toFixed(2)}`;
    }

    [sqft, thk, bagSize, waste, bagPrice].forEach(inp => {
      if (inp) {
        inp.addEventListener('input', calc);
        inp.addEventListener('change', calc);
      }
    });
    calc();
  }

  // 3. REBAR & WIRE MESH CALCULATOR
  function initRebarCalculator() {
    const el = document.getElementById('subcalc-rebar');
    if (!el) return;

    const len = el.querySelector('#rebar-length');
    const wid = el.querySelector('#rebar-width');
    const spacing = el.querySelector('#rebar-spacing'); // inches: 12, 18, 24
    const barLen = el.querySelector('#rebar-bar-length'); // 20ft standard
    const outLinFt = el.querySelector('#rebar-out-linft');
    const outPieces = el.querySelector('#rebar-out-pieces');
    const outChairs = el.querySelector('#rebar-out-chairs');

    function calc() {
      const l = parseFloat(len.value) || 0;
      const w = parseFloat(wid.value) || 0;
      const sp = (parseFloat(spacing.value) || 18) / 12; // in feet
      const bLen = parseFloat(barLen ? barLen.value : 20) || 20;

      // Number of bars running lengthwise & widthwise
      const numAlongWidth = Math.floor(w / sp) + 1;
      const numAlongLength = Math.floor(l / sp) + 1;

      const totalLinFt = (numAlongWidth * l) + (numAlongLength * w);
      // Adding 10% overlap splice factor
      const totalWithSplice = totalLinFt * 1.10;
      const pieces = Math.ceil(totalWithSplice / bLen);
      // Rebar chairs placed every 3-4 feet in a grid
      const chairs = Math.ceil((l / 3) * (w / 3));

      if (outLinFt) outLinFt.textContent = `${Math.round(totalWithSplice)} linear ft`;
      if (outPieces) outPieces.textContent = `${pieces} bars (${bLen}ft sticks)`;
      if (outChairs) outChairs.textContent = `${chairs} rebar chairs`;
    }

    [len, wid, spacing, barLen].forEach(inp => {
      if (inp) {
        inp.addEventListener('input', calc);
        inp.addEventListener('change', calc);
      }
    });
    calc();
  }

  // 4. FOOTING CALCULATOR
  function initFootingCalculator() {
    const el = document.getElementById('subcalc-footing');
    if (!el) return;

    const fType = el.querySelector('#footing-type'); // 'continuous' or 'pier'
    const len = el.querySelector('#footing-length');
    const wid = el.querySelector('#footing-width'); // or diameter
    const dep = el.querySelector('#footing-depth');
    const qty = el.querySelector('#footing-qty');
    const waste = el.querySelector('#footing-waste');
    const outYards = el.querySelector('#footing-out-yards');
    const outBags = el.querySelector('#footing-out-bags');

    function calc() {
      const isPier = fType && fType.value === 'pier';
      const l = parseFloat(len.value) || 0;
      const w = (parseFloat(wid.value) || 0) / 12; // in feet
      const d = (parseFloat(dep.value) || 0) / 12; // in feet
      const count = parseInt(qty ? qty.value : 1, 10) || 1;
      // Optional wastage allowance input (default 10% if the field isn't present)
      const wastePct = waste ? (parseFloat(waste.value) || 0) : 10;
      const wasteFactor = 1 + (wastePct / 100);

      let cuft = 0;
      if (isPier) {
        const radius = w / 2;
        cuft = Math.PI * Math.pow(radius, 2) * d * count;
      } else {
        cuft = l * w * d * count;
      }

      const cuyds = (cuft / 27) * wasteFactor;
      const bags80 = Math.ceil((cuft * wasteFactor) / 0.60);

      if (outYards) outYards.textContent = `${cuyds.toFixed(2)} cu. yds (w/ ${wastePct}% waste)`;
      if (outBags) outBags.textContent = `${bags80} bags (80 lb)`;
    }

    [fType, len, wid, dep, qty, waste].forEach(inp => {
      if (inp) {
        inp.addEventListener('input', calc);
        inp.addEventListener('change', calc);
      }
    });
    calc();
  }

  // 4b. WEIGHT CALCULATOR
  function initWeightCalculator() {
    const el = document.getElementById('subcalc-weight');
    if (!el) return;

    const len = el.querySelector('#weight-length');
    const wid = el.querySelector('#weight-width');
    const thk = el.querySelector('#weight-thick');
    const outWetLbs = el.querySelector('#weight-out-wet-lbs');
    const outWetTons = el.querySelector('#weight-out-wet-tons');
    const outDryLbs = el.querySelector('#weight-out-dry-lbs');
    const outKg = el.querySelector('#weight-out-kg');
    const outTonnes = el.querySelector('#weight-out-tonnes');

    // Consistent with the site-wide density constant used by the primary
    // calculator (145 lb/cu ft ≈ 2,322 kg/m³ for normal-weight concrete).
    const WET_DENSITY_LBS_PER_CUFT = 145;
    // Cured/dry weight runs a few percent lighter than freshly-poured wet
    // weight due to bleed-water loss during the ~28-day cure — this is an
    // approximation (typically cited in the 2%-4% range), not a precise
    // mix-specific figure.
    const DRY_FACTOR = 0.97;
    const LBS_PER_KG = 0.45359237;

    function calc() {
      const l = parseFloat(len.value) || 0;
      const w = parseFloat(wid.value) || 0;
      const t = (parseFloat(thk.value) || 0) / 12; // inches -> feet

      const cuft = l * w * t;
      const wetLbs = cuft * WET_DENSITY_LBS_PER_CUFT;
      const dryLbs = wetLbs * DRY_FACTOR;
      const wetTons = wetLbs / 2000; // US (short) tons
      const wetKg = wetLbs * LBS_PER_KG;
      const wetTonnes = wetKg / 1000; // metric tonnes

      if (outWetLbs) outWetLbs.textContent = `${Math.round(wetLbs).toLocaleString('en-US')} lbs`;
      if (outWetTons) outWetTons.textContent = `${wetTons.toFixed(2)} US tons`;
      if (outDryLbs) outDryLbs.textContent = `${Math.round(dryLbs).toLocaleString('en-US')} lbs (~cured)`;
      if (outKg) outKg.textContent = `${Math.round(wetKg).toLocaleString('en-US')} kg`;
      if (outTonnes) outTonnes.textContent = `${wetTonnes.toFixed(2)} metric tonnes`;
    }

    [len, wid, thk].forEach(inp => {
      if (inp) {
        inp.addEventListener('input', calc);
        inp.addEventListener('change', calc);
      }
    });
    calc();
  }

  // 5. GRAVEL BASE CALCULATOR
  function initGravelCalculator() {
    const el = document.getElementById('subcalc-gravel');
    if (!el) return;

    const sqft = el.querySelector('#gravel-sqft');
    const depth = el.querySelector('#gravel-depth');
    const outYards = el.querySelector('#gravel-out-yards');
    const outTons = el.querySelector('#gravel-out-tons');

    function calc() {
      const area = parseFloat(sqft.value) || 0;
      const d = (parseFloat(depth.value) || 4) / 12; // in ft

      const cuft = area * d;
      // 15% compaction factor
      const cuyds = (cuft / 27) * 1.15;
      // 1 cubic yard of crushed gravel ~ 1.4 tons (2,800 lbs)
      const tons = cuyds * 1.4;

      if (outYards) outYards.textContent = `${cuyds.toFixed(2)} cubic yards`;
      if (outTons) outTons.textContent = `${tons.toFixed(2)} tons crushed gravel`;
    }

    [sqft, depth].forEach(inp => {
      if (inp) {
        inp.addEventListener('input', calc);
        inp.addEventListener('change', calc);
      }
    });
    calc();
  }

  // 6. REMOVAL / DEMO CALCULATOR
  function initRemovalCalculator() {
    const el = document.getElementById('subcalc-removal');
    if (!el) return;

    const sqft = el.querySelector('#demo-sqft');
    const thick = el.querySelector('#demo-thick');
    const rebar = el.querySelector('#demo-rebar');
    const outCost = el.querySelector('#demo-out-cost');
    const outWeight = el.querySelector('#demo-out-weight');

    function calc() {
      const area = parseFloat(sqft.value) || 0;
      const t = parseFloat(thick.value) || 4;
      const hasRebar = rebar && rebar.checked;

      // Base demo rate: $3.00 - $4.00 per sq ft for 4" unreinforced slab
      let ratePerSqFt = 3.25;
      if (t > 4) ratePerSqFt += (t - 4) * 0.50;
      if (hasRebar) ratePerSqFt += 1.00; // harder to break with jackhammer

      const totalCost = area * ratePerSqFt;
      const weightLbs = area * (t / 12) * 145;
      const tons = weightLbs / 2000;

      if (outCost) outCost.textContent = `$${totalCost.toFixed(2)} ($${ratePerSqFt.toFixed(2)}/sq ft)`;
      if (outWeight) outWeight.textContent = `${tons.toFixed(1)} tons demo debris`;
    }

    [sqft, thick, rebar].forEach(inp => {
      if (inp) {
        inp.addEventListener('input', calc);
        inp.addEventListener('change', calc);
      }
    });
    calc();
  }

  // 7. LABOR CALCULATOR
  function initLaborCalculator() {
    const el = document.getElementById('subcalc-labor');
    if (!el) return;

    const sqft = el.querySelector('#labor-sqft');
    const complexity = el.querySelector('#labor-complexity'); // standard, stamp, difficult
    const outCost = el.querySelector('#labor-out-cost');
    const outHours = el.querySelector('#labor-out-hours');

    function calc() {
      const area = parseFloat(sqft.value) || 0;
      const comp = complexity ? complexity.value : 'standard';

      let rate = 5.00; // $/sqft
      let crewHoursFactor = 0.035; // crew hours per sqft

      if (comp === 'stamp') {
        rate = 10.00;
        crewHoursFactor = 0.06;
      } else if (comp === 'difficult') {
        rate = 7.50;
        crewHoursFactor = 0.05;
      }

      const totalLabor = area * rate;
      const hours = Math.round(area * crewHoursFactor);

      if (outCost) outCost.textContent = `$${totalLabor.toFixed(2)} ($${rate.toFixed(2)}/sq ft)`;
      if (outHours) outHours.textContent = `~${hours} total crew hours`;
    }

    [sqft, complexity].forEach(inp => {
      if (inp) {
        inp.addEventListener('input', calc);
        inp.addEventListener('change', calc);
      }
    });
    calc();
  }

  function initSupportingInputs() {
    // Generic event binding for instant feedback across supporting tools
  }

})();
