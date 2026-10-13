/**
 * SUPPORTING CONCRETE CALCULATORS SUITE
 * Handles standalone specialized calculations across the topical authority network.
 */

(function () {
  'use strict';

  // Site-wide figures from inc/site-data.php (window.SLAB_DATA).
  const SD = window.SLAB_DATA || {};
  const sd = (key, fallback) => (SD[key] !== undefined && SD[key] !== null ? SD[key] : fallback);

  // Number field reader that keeps a typed 0 (parseFloat(x) || def would
  // turn 0% waste into 10% and 0" into 4"). Blank gives def; negatives clamp to 0.
  function numOr(el, def) {
    const v = el ? parseFloat(el.value) : NaN;
    return Number.isFinite(v) ? Math.max(0, v) : def;
  }

  // Whole-unit round up without float noise (52.8 / 0.6 = 88.00000000000001).
  function ceilCount(x) {
    return Math.ceil(parseFloat(x.toFixed(6)));
  }

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

    // 9. Single-purpose tools (Phase 5)
    initYardCalculator();
    initReadyMixCalculator();
    initMixCalculator();
    initWasteCalculator();
    initMeshCalculator();
    initFormCalculator();

    // 10. Phase 6 tools
    initFencePostCalculator();
    initStepsCalculator();
    initBreakEvenCalculator();
    initQuoteCompare();

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
      const l = numOr(len, 0);
      const w = numOr(wid, 0);
      const t = numOr(thk, 0);
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
      const area = numOr(sqft, 0);
      const t = numOr(thk, 4);
      const size = bagSize ? bagSize.value : '80';
      const wPct = numOr(waste, sd('wastePct', 10));
      const price = numOr(bagPrice, sd('bagPrice', 6.50));

      const yields = sd('bagYields', { '80': 0.60, '60': 0.45, '50': 0.375, '40': 0.30 });
      const bagYield = yields[size] || 0.60;

      const cuft = area * (t / 12);
      const withWaste = cuft * (1 + (wPct / 100));
      const count = ceilCount(withWaste / bagYield);
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
      const l = numOr(len, 0);
      const w = numOr(wid, 0);
      const sp = (parseFloat(spacing.value) || 18) / 12; // in feet
      const bLen = parseFloat(barLen ? barLen.value : 20) || 20;

      // Same grid as the calculator's slab blueprint: bars start one edge
      // cover in from each side, chairs at grid intersections about 3-4 ft
      // apart, plus an allowance for lap splices and offcuts.
      const cover = sd('edgeCoverIn', 3) / 12;
      const spIn = sp * 12;
      const fill = (span) => {
        const usable = span - 2 * cover;
        if (usable <= 0) return [];
        const n = Math.floor(usable / sp + 1e-9) + 1;
        const step = n > 1 ? usable / (n - 1) : 0;
        const out = [];
        for (let i = 0; i < n; i++) out.push(cover + i * step);
        return out;
      };
      const xBars = fill(w);
      const yBars = fill(l);
      const totalLinFt = xBars.length * Math.max(0, l - 2 * cover) + yBars.length * Math.max(0, w - 2 * cover);
      const totalWithSplice = totalLinFt * (1 + sd('spliceAllowance', 0.10));
      const pieces = ceilCount(totalWithSplice / bLen);
      const k = Math.max(1, Math.round(42 / spIn));
      const every = (k * spIn) / 12;
      const pick = (arr) => {
        const out = arr.filter((_, i) => i % k === 0);
        if (out.length && out[out.length - 1] !== arr[arr.length - 1] && arr[arr.length - 1] - out[out.length - 1] > every / 2) {
          out.push(arr[arr.length - 1]);
        }
        return out;
      };
      const chairs = pick(yBars).length * pick(xBars).length;

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
      const l = numOr(len, 0);
      const w = numOr(wid, 0) / 12; // in feet
      const d = numOr(dep, 0) / 12; // in feet
      const count = Math.max(1, parseInt(qty ? qty.value : 1, 10) || 1);
      // Optional wastage allowance input (default 10% if the field isn't present)
      const wastePct = waste ? numOr(waste, 0) : 10;
      const wasteFactor = 1 + (wastePct / 100);

      let cuft = 0;
      if (isPier) {
        const radius = w / 2;
        cuft = Math.PI * Math.pow(radius, 2) * d * count;
      } else {
        cuft = l * w * d * count;
      }

      const cuyds = (cuft / 27) * wasteFactor;
      const bags80 = ceilCount((cuft * wasteFactor) / sd('bagYields', { '80': 0.60 })['80']);

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
    const WET_DENSITY_LBS_PER_CUFT = sd('densityLbFt3', 145);
    // Cured/dry weight runs a few percent lighter than freshly-poured wet
    // weight due to bleed-water loss during the ~28-day cure — this is an
    // approximation (typically cited in the 2%-4% range), not a precise
    // mix-specific figure.
    const DRY_FACTOR = sd('curedWeightFactor', 0.97);
    const LBS_PER_KG = 0.45359237;

    function calc() {
      const l = numOr(len, 0);
      const w = numOr(wid, 0);
      const t = (numOr(thk, 0)) / 12; // inches -> feet

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
      const area = numOr(sqft, 0);
      const d = numOr(depth, 4) / 12; // in ft

      const cuft = area * d;
      // 15% compaction factor
      const cuyds = (cuft / 27) * sd('gravelCompaction', 1.15);
      // 1 cubic yard of crushed gravel ~ 1.4 tons (2,800 lbs)
      const tons = cuyds * sd('gravelTonsPerYard', 1.4);

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
      const area = numOr(sqft, 0);
      const t = numOr(thick, 4);
      const hasRebar = rebar && rebar.checked;

      // Base demo rate: $3.00 - $4.00 per sq ft for 4" unreinforced slab
      let ratePerSqFt = sd('demoRate', 3.25);
      if (t > 4) ratePerSqFt += (t - 4) * sd('demoPerInch', 0.50);
      if (hasRebar) ratePerSqFt += sd('demoRebar', 1.00); // harder to break with jackhammer

      const totalCost = area * ratePerSqFt;
      const weightLbs = area * (t / 12) * sd('densityLbFt3', 145);
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
      const area = numOr(sqft, 0);
      const comp = complexity ? complexity.value : 'standard';

      let rate = sd('laborPerSqFt', 5.00); // $/sqft
      let crewHoursFactor = sd('crewHoursPerSqFt', 0.035); // crew hours per sqft

      if (comp === 'stamp') {
        rate = sd('laborStamped', 10.00);
        crewHoursFactor = sd('crewHoursPerSqFtStamped', 0.06);
      } else if (comp === 'difficult') {
        rate = sd('laborDifficult', 7.50);
        crewHoursFactor = sd('crewHoursPerSqFtDifficult', 0.05);
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

  /* ---------------- Single-purpose tools (Phase 5) ----------------
   * Same maths as Slab_Calculator_Pages in inc/calculator-pages.php, which
   * prints the starting results before this script runs.
   */
  const money0 = (n) => '$' + Math.round(n).toLocaleString('en-US');
  const moneyRange = (a, b) => money0(a) + ' – ' + money0(b);
  function bind(inputs, fn) {
    inputs.forEach(inp => {
      if (inp) {
        inp.addEventListener('input', fn);
        inp.addEventListener('change', fn);
      }
    });
    fn();
  }
  function setText(el, text) { if (el) el.textContent = text; }
  function roundUpStep(x, step) { return Math.ceil(x / step - 1e-9) * step; }

  function initYardCalculator() {
    const el = document.getElementById('subcalc-yards');
    if (!el) return;
    const q = (id) => el.querySelector('#' + id);
    const inputs = [q('yd-length'), q('yd-width'), q('yd-thick'), q('yd-waste')];
    bind(inputs, () => {
      const net = numOr(inputs[0], 0) * numOr(inputs[1], 0) * numOr(inputs[2], 0) / 12 / 27;
      const withWaste = net * (1 + numOr(inputs[3], 0) / 100);
      const order = roundUpStep(withWaste, sd('orderStepYd', 0.25));
      const cap = sd('truckCapacityYd', [8, 10]);
      setText(q('yd-out-order'), order.toFixed(2) + ' yd³');
      setText(q('yd-out-waste'), withWaste.toFixed(2) + ' yd³');
      setText(q('yd-out-net'), 'Net: ' + net.toFixed(2) + ' yd³');
      setText(q('yd-out-trucks'), String(Math.max(order > 0 ? 1 : 0, Math.ceil(order / cap[0] - 1e-9))));
      const min = sd('commonMinYd', [5, 6]);
      setText(q('yd-out-note'), order > 0 && order < min[0] ? 'Under ' + min[0] + ' yd³: many plants add a short-load fee. Compare with bags.' : '');
    });
  }

  function initReadyMixCalculator() {
    const el = document.getElementById('subcalc-readymix');
    if (!el) return;
    const q = (id) => el.querySelector('#' + id);
    const inputs = [q('rm-length'), q('rm-width'), q('rm-thick'), q('rm-psi'), q('rm-waste')];
    bind(inputs, () => {
      const net = numOr(inputs[0], 0) * numOr(inputs[1], 0) * numOr(inputs[2], 0) / 12 / 27;
      const order = roundUpStep(net * (1 + numOr(inputs[4], 0) / 100), sd('orderStepYd', 0.25));
      const prices = sd('psiPrices', {});
      const price = prices[inputs[3].value] || [135, 160];
      const fee = sd('deliveryFee', 120);
      const min = sd('commonMinYd', [5, 6]);
      const shortFee = (order > 0 && order < min[0]) ? sd('shortLoadFlat', [80, 200]) : [0, 0];
      const cap = sd('truckCapacityYd', [8, 10]);
      const trucks = order > 0 ? Math.ceil(order / cap[0] - 1e-9) : 0;
      const lo = order > 0 ? order * price[0] + fee + shortFee[0] : 0;
      const hi = order > 0 ? order * price[1] + fee + shortFee[1] : 0;
      setText(q('rm-out-total'), moneyRange(lo, hi));
      setText(q('rm-out-total-note'), 'Concrete + ' + money0(fee) + ' delivery' + (shortFee[1] ? ' + short-load fee' : ''));
      setText(q('rm-out-yards'), order.toFixed(2) + ' yd³');
      setText(q('rm-out-trucks'), trucks + (trucks === 1 ? ' truck' : ' trucks'));
      setText(q('rm-out-price'), moneyRange(price[0], price[1]));
      setText(q('rm-out-note'), shortFee[1] ? 'Below the usual ' + min[0] + '–' + min[1] + ' yd³ minimum: a short-load fee of ' + moneyRange(shortFee[0], shortFee[1]) + ' is included.' : '');
    });
  }

  function initMixCalculator() {
    const el = document.getElementById('subcalc-mix');
    if (!el) return;
    const q = (id) => el.querySelector('#' + id);
    const inputs = [q('mix-length'), q('mix-width'), q('mix-thick'), q('mix-ratio'), q('mix-waste')];
    bind(inputs, () => {
      const parts = inputs[3].value.split(':').map(parseFloat);
      const sum = parts.reduce((a, b) => a + b, 0) || 1;
      const wet = numOr(inputs[0], 0) * numOr(inputs[1], 0) * numOr(inputs[2], 0) / 12 * (1 + numOr(inputs[4], 0) / 100);
      const dry = wet * sd('dryVolumeFactor', 1.54);
      const cementFt3 = dry * parts[0] / sum;
      const sandYd = dry * parts[1] / sum / 27;
      const stoneYd = dry * parts[2] / sum / 27;
      const bagFt3 = sd('cementBagFt3', 1);
      const cementLb = cementFt3 / bagFt3 * sd('cementBagLb', 94);
      setText(q('mix-out-cement'), ceilCount(cementFt3 / bagFt3) + ' bags');
      setText(q('mix-out-sand'), (sandYd * sd('sandTonsPerYd', 1.35)).toFixed(2) + ' tons');
      setText(q('mix-out-sand-yd'), sandYd.toFixed(2) + ' yd³');
      setText(q('mix-out-stone'), (stoneYd * sd('gravelTonsPerYard', 1.4)).toFixed(2) + ' tons');
      setText(q('mix-out-stone-yd'), stoneYd.toFixed(2) + ' yd³');
      setText(q('mix-out-water'), Math.round(cementLb * sd('waterCementRatio', 0.5) / 8.34) + ' gallons');
    });
  }

  function initWasteCalculator() {
    const el = document.getElementById('subcalc-waste');
    if (!el) return;
    const q = (id) => el.querySelector('#' + id);
    const inputs = [q('wst-length'), q('wst-width'), q('wst-thick'), q('wst-level'), q('wst-price')];
    bind(inputs, () => {
      const netFt3 = numOr(inputs[0], 0) * numOr(inputs[1], 0) * numOr(inputs[2], 0) / 12;
      const orderFt3 = netFt3 * (1 + numOr(inputs[3], 0) / 100);
      const extraYd = (orderFt3 - netFt3) / 27;
      const yields = sd('bagYields', { '80': 0.60 });
      setText(q('wst-out-order'), (orderFt3 / 27).toFixed(2) + ' yd³');
      setText(q('wst-out-net'), 'Net: ' + (netFt3 / 27).toFixed(2) + ' yd³');
      setText(q('wst-out-extra'), extraYd.toFixed(2) + ' yd³');
      setText(q('wst-out-extra-cost'), 'Costs about ' + money0(extraYd * numOr(inputs[4], sd('readyMixPerYard', 150))));
      setText(q('wst-out-bags'), ceilCount(orderFt3 / yields['80']) + ' bags');
    });
  }

  function initMeshCalculator() {
    const el = document.getElementById('subcalc-mesh');
    if (!el) return;
    const q = (id) => el.querySelector('#' + id);
    const inputs = [q('mesh-length'), q('mesh-width'), q('mesh-type')];
    bind(inputs, () => {
      const l = numOr(inputs[0], 0);
      const w = numOr(inputs[1], 0);
      const lap = sd('meshOverlapIn', 6) / 12;
      const chairFt = sd('chairGridFt', [3, 4])[1];
      const up = (x) => Math.ceil(x - 1e-9);
      let count, price, unit;
      if (inputs[2].value === 'roll') {
        const roll = sd('meshRollFt', [5, 150]);
        const lane = roll[0] - lap;
        const run = Math.min(up(w / lane) * l, up(l / lane) * w);
        count = up(run / roll[1]);
        price = sd('meshRollPrice', [180, 300]);
        unit = count === 1 ? 'roll' : 'rolls';
      } else {
        const sh = sd('meshSheetFt', [5, 10]);
        const a = sh[0] - lap;
        const b = sh[1] - lap;
        count = Math.min(up(l / b) * up(w / a), up(l / a) * up(w / b));
        price = sd('meshSheetPrice', [12, 20]);
        unit = count === 1 ? 'sheet' : 'sheets';
      }
      const chairs = (l > 0 && w > 0) ? (Math.floor(l / chairFt) + 1) * (Math.floor(w / chairFt) + 1) : 0;
      setText(q('mesh-out-count'), count + ' ' + unit);
      setText(q('mesh-out-chairs'), chairs + ' chairs');
      setText(q('mesh-out-cost'), moneyRange(count * price[0], count * price[1]));
    });
  }

  function initFormCalculator() {
    const el = document.getElementById('subcalc-forms');
    if (!el) return;
    const q = (id) => el.querySelector('#' + id);
    const inputs = [q('form-length'), q('form-width'), q('form-thick'), q('form-board-len'), q('form-stake')];
    bind(inputs, () => {
      const l = numOr(inputs[0], 0);
      const w = numOr(inputs[1], 0);
      const t = numOr(inputs[2], 0);
      const boardLen = parseFloat(inputs[3].value) || 12;
      const stakeFt = parseFloat(inputs[4].value) || 3;
      const depths = sd('formBoardDepthIn', { '2x4': 3.5, '2x6': 5.5, '2x8': 7.25, '2x10': 9.25, '2x12': 11.25 });
      let board = '2x12';
      for (const name of Object.keys(depths)) {
        if (depths[name] >= t - 0.5) { board = name; break; }
      }
      let boards = 0, stakes = 0;
      [l, l, w, w].forEach(side => {
        if (side <= 0) return;
        const n = Math.ceil(side / boardLen - 1e-9);
        boards += n;
        stakes += Math.ceil(side / stakeFt - 1e-9) + (n - 1);
      });
      const perim = 2 * (l + w);
      setText(q('form-out-boards'), boards + ' × ' + board);
      setText(q('form-out-perimeter'), Math.round(perim) + ' linear ft of form');
      setText(q('form-out-stakes'), stakes + ' stakes');
      setText(q('form-out-screws'), (stakes * 2) + ' screws');
      setText(q('form-out-cost'), money0(perim * sd('forms', 2.20)));
    });
  }

  /* ---------------- Phase 6 tools ----------------
   * Same maths as Slab_Data::post_hole(), steps_volume() and
   * bags_vs_truck() in inc/site-data.php, which print the starting results.
   */
  const wastePct = () => sd('wastePct', 10);

  // Bags vs. truck bill for a net volume (yd³) with the given prices.
  function concreteBill(netYd, o) {
    const orderFt3 = netYd * 27 * (1 + wastePct() / 100);
    const bags = netYd > 0 ? ceilCount(orderFt3 / o.bagYield) : 0;
    const bagCost = bags * o.bagPrice + (bags > 0 ? o.mixer : 0);
    const order = roundUpStep(orderFt3 / 27, sd('orderStepYd', 0.25));
    const short = order > 0 && order < o.minYd;
    const truckCost = order > 0 ? order * o.rmPrice + o.fee + (short ? o.shortFee : 0) : 0;
    const perHour = (sd('bagsPerHourMixer', [20, 30])[0] + sd('bagsPerHourMixer', [20, 30])[1]) / 2;
    const cap = sd('truckCapacityYd', [8, 10])[0];
    return {
      bags: bags, bagCost: bagCost, mixHours: bags / perHour,
      order: order, short: short, truckCost: truckCost,
      trucks: order > 0 ? Math.ceil(order / cap - 1e-9) : 0,
      cheaper: bagCost <= truckCost ? 'bags' : 'truck'
    };
  }
  function defaultBillOpts() {
    const mid = (pair) => (pair[0] + pair[1]) / 2;
    return {
      bagYield: sd('bagYields', { '80': 0.6 })['80'],
      bagPrice: sd('bagPrice', 6.5),
      mixer: mid(sd('mixerRentalDay', [50, 100])),
      rmPrice: sd('readyMixPerYard', 150),
      fee: sd('deliveryFee', 120),
      shortFee: mid(sd('shortLoadFlat', [80, 200])),
      minYd: sd('commonMinYd', [5, 6])[0]
    };
  }
  const fmtNum = (n, d) => parseFloat(n.toFixed(d)).toLocaleString('en-US');

  function initFencePostCalculator() {
    const el = document.getElementById('subcalc-fenceposts');
    if (!el) return;
    const q = (id) => el.querySelector('#' + id);
    const P = sd('posts', null);
    if (!P) return;
    const yields = sd('bagYields', { '80': 0.6, '60': 0.45 });
    const prices = sd('bagPrices', { '80': 6.5, '60': 5.25 });
    const postW = (key) => { const s = P.sizes[key][2]; return Array.isArray(s) ? Math.max(s[0], s[1]) : s; };
    const postArea = (key) => {
      const s = P.sizes[key];
      if (s[1] === 'round') return Math.PI * Math.pow(s[2] / 2, 2);
      if (s[1] === 'rect') return s[2][0] * s[2][1];
      return s[2] * s[2];
    };
    // Suggested hole for the post and fence height (Slab_Data::post_hole_diameter()).
    function suggest() {
      const key = q('fp-post').value;
      const chain = P.chain_link_hole_in || {};
      q('fp-dia').value = chain[key] || Math.max(6, Math.round(postW(key) * P.hole_factor / 2) * 2);
      const h = numOr(q('fp-height'), 6);
      q('fp-depth').value = Math.round(h * 12 * P.depth_fraction[0] / 6) * 6 + P.gravel_in;
    }
    q('fp-post').addEventListener('change', () => { suggest(); calc(); });
    q('fp-height').addEventListener('input', () => { suggest(); calc(); });
    function calc() {
      const key = q('fp-post').value;
      const count = Math.max(0, Math.round(numOr(q('fp-count'), 0)));
      const dia = numOr(q('fp-dia'), 0);
      const depth = numOr(q('fp-depth'), 0);
      const mix = q('fp-mix').value;
      const fill = Math.max(0, depth - P.gravel_in);
      const perHole = Math.max(0, Math.PI * Math.pow(dia / 2, 2) - postArea(key)) * fill / 1728 * (1 + wastePct() / 100);
      const yieldFt3 = mix === 'fast' ? P.fast_set_yield : yields[mix];
      const price = mix === 'fast' ? P.fast_set_price : prices[mix];
      const total = perHole * count;
      const bags = total > 0 ? ceilCount(total / yieldFt3) : 0;
      const label = mix === 'fast' ? P.fast_set_lb + '-lb fast-set' : mix + '-lb';
      setText(q('fp-out-total'), bags + ' bags');
      setText(q('fp-out-cost'), money0(bags * price) + ' in ' + label + ' mix');
      setText(q('fp-out-per'), fmtNum(perHole / yieldFt3, 1));
      setText(q('fp-out-per-note'), perHole.toFixed(2) + ' ft³ per hole (+' + wastePct() + '%)');
      setText(q('fp-out-gravel'), fmtNum(Math.PI * Math.pow(dia / 2, 2) * P.gravel_in / 1728 * count, 1) + ' ft³');
      const s = P.sizes[key];
      const w = postW(key);
      setText(q('fp-out-note'), dia > 0 && dia < w + 4 ? 'The hole is barely wider than the post: aim for about ' + P.hole_factor + ' × the post width so there is concrete all around it.' : (s && fill <= 0 ? 'The hole is no deeper than the gravel layer.' : ''));
    }
    bind([q('fp-count'), q('fp-dia'), q('fp-depth'), q('fp-mix')], calc);
  }

  function initStepsCalculator() {
    const el = document.getElementById('subcalc-steps');
    if (!el) return;
    const q = (id) => el.querySelector('#' + id);
    const ST = sd('steps', null);
    if (!ST) return;
    const inputs = [q('st-count'), q('st-width'), q('st-landing'), q('st-rise'), q('st-tread')];
    const yields = sd('bagYields', { '80': 0.6, '60': 0.45 });
    bind(inputs, () => {
      const n = Math.max(0, Math.round(numOr(inputs[0], 0)));
      const w = numOr(inputs[1], 0);
      const landing = numOr(inputs[2], 0);
      const rise = numOr(inputs[3], 0);
      const tread = numOr(inputs[4], 0);
      const stepsFt3 = w * (tread / 12) * (rise / 12) * n * (n + 1) / 2;
      const net = stepsFt3 + w * landing * (n * rise / 12);
      const orderFt3 = net * (1 + wastePct() / 100);
      const sideSqft = (tread / 12) * (rise / 12) * n * (n + 1) / 2 + landing * (n * rise / 12);
      const bill = concreteBill(net / 27, defaultBillOpts());
      const per = ST.installed_per_step;
      const k = Math.max(1, w / 4);
      setText(q('st-out-yards'), (orderFt3 / 27).toFixed(2) + ' yd³');
      setText(q('st-out-bags'), (net > 0 ? ceilCount(orderFt3 / yields['80']) : 0) + ' × 80-lb or ' + (net > 0 ? ceilCount(orderFt3 / yields['60']) : 0) + ' × 60-lb bags');
      setText(q('st-out-diy'), money0(bill.cheaper === 'bags' ? bill.bagCost : bill.truckCost));
      setText(q('st-out-diy-note'), 'Cheapest: ' + (bill.cheaper === 'bags' ? 'bags' : 'ready-mix'));
      setText(q('st-out-pro'), moneyRange(Math.round(per[0] * n * k / 10) * 10, Math.round(per[1] * n * k / 10) * 10));
      setText(q('st-out-size'), fmtNum(n * rise, 2) + ' in × ' + fmtNum(n * tread + landing * 12, 1) + ' in');
      setText(q('st-out-forms'), fmtNum(n * w, 1) + ' lin ft riser boards');
      setText(q('st-out-ply'), ceilCount(2 * sideSqft / 32) + ' sheet(s) of 4 × 8 plywood for the sides');
      const issues = [];
      if (rise > ST.riser_max_in) issues.push('rise is over ' + ST.riser_max_in + ' in');
      if (tread > 0 && tread < ST.tread_min_in) issues.push('tread is under ' + ST.tread_min_in + ' in');
      if (w > 0 && w * 12 < ST.width_min_in) issues.push('width is under ' + ST.width_min_in + ' in');
      if (landing > 0 && landing * 12 < ST.landing_min_in) issues.push('landing is under ' + ST.landing_min_in + ' in deep');
      const note = q('st-out-code');
      if (note) {
        note.textContent = issues.length
          ? 'Check the code: ' + issues.join(', ') + ' (IRC R311.7).'
          : 'Meets IRC limits: rise ≤ ' + ST.riser_max_in + ' in, tread ≥ ' + ST.tread_min_in + ' in, width ≥ ' + ST.width_min_in + ' in.' + (n >= 4 ? ' Four or more risers need a handrail.' : '');
        note.classList.toggle('calc-field-warn', issues.length > 0);
      }
    });
  }

  function initBreakEvenCalculator() {
    const el = document.getElementById('subcalc-breakeven');
    if (!el) return;
    const q = (id) => el.querySelector('#' + id);
    const yields = sd('bagYields', { '80': 0.6 });
    const prices = sd('bagPrices', { '80': 6.5 });
    // Picking a bag size fills in its typical price.
    q('be-bag').addEventListener('change', () => {
      const p = prices[q('be-bag').value];
      if (p) q('be-bag-price').value = Number(p).toFixed(2);
    });
    const inputs = ['be-length', 'be-width', 'be-thick', 'be-bag', 'be-bag-price', 'be-mixer', 'be-rm-price', 'be-fee', 'be-short', 'be-min'].map(q);
    bind(inputs, () => {
      const net = numOr(inputs[0], 0) * numOr(inputs[1], 0) * numOr(inputs[2], 0) / 12 / 27;
      const o = {
        bagYield: yields[inputs[3].value] || 0.6,
        bagPrice: numOr(inputs[4], 0),
        mixer: numOr(inputs[5], 0),
        rmPrice: numOr(inputs[6], 0),
        fee: numOr(inputs[7], 0),
        shortFee: numOr(inputs[8], 0),
        minYd: numOr(inputs[9], 0)
      };
      const r = concreteBill(net, o);
      let even = null;
      for (let i = 1; i <= 200; i++) {
        const b = concreteBill(i * 0.05, o);
        if (b.truckCost <= b.bagCost) { even = i * 0.05; break; }
      }
      setText(q('be-out-verdict'), net > 0 ? (r.cheaper === 'bags' ? 'Bags' : 'Ready-mix') : '–');
      setText(q('be-out-saving'), net > 0 ? 'Saves ' + money0(Math.abs(r.truckCost - r.bagCost)) : '');
      setText(q('be-out-bags'), money0(r.bagCost));
      setText(q('be-out-bags-note'), r.bags + ' bags, about ' + fmtNum(r.mixHours, 1) + ' h of mixing');
      setText(q('be-out-truck'), money0(r.truckCost));
      setText(q('be-out-truck-note'), fmtNum(r.order, 2) + ' yd³' + (r.short ? ' incl. short-load fee' : '') + (r.trucks > 1 ? ', ' + r.trucks + ' trucks' : ''));
      const evenEl = q('be-out-even');
      if (evenEl) {
        evenEl.innerHTML = even !== null
          ? 'Break-even: ready-mix is cheaper above about <strong>' + fmtNum(even, 2) + ' yd³</strong> with these prices (a 4-in slab of about ' + Math.round(even * 27 * 3 / 5) * 5 + ' sq ft).'
          : 'With these prices bags stay cheaper up to 10 yd³.';
      }
      drawChart(q('be-chart'), net, even, o);
    });
  }

  // Bag and truck cost against volume, with the user's pour marked.
  function drawChart(box, net, even, o) {
    if (!box) return;
    const maxYd = Math.max(3, Math.ceil(Math.max(net, even || 0) * 1.4));
    const W = 640, H = 260, L = 56, R = 16, T = 16, B = 40;
    const pts = [];
    let maxCost = 0;
    for (let i = 0; i <= 120; i++) {
      const v = maxYd * i / 120;
      const b = concreteBill(v, o);
      pts.push([v, b.bagCost, b.truckCost]);
      maxCost = Math.max(maxCost, b.bagCost, b.truckCost);
    }
    maxCost = Math.max(100, Math.ceil(maxCost / 250) * 250);
    const x = (v) => L + (W - L - R) * v / maxYd;
    const y = (c) => T + (H - T - B) * (1 - c / maxCost);
    const line = (k) => pts.map((p, i) => (i ? 'L' : 'M') + x(p[0]).toFixed(1) + ' ' + y(p[k]).toFixed(1)).join(' ');
    let grid = '';
    for (let i = 0; i <= 4; i++) {
      const c = maxCost * i / 4;
      grid += '<line x1="' + L + '" x2="' + (W - R) + '" y1="' + y(c) + '" y2="' + y(c) + '" stroke="#e2e8f0"/>'
        + '<text x="' + (L - 6) + '" y="' + (y(c) + 4) + '" text-anchor="end" font-size="11" fill="#64748b">' + money0(c) + '</text>';
    }
    for (let v = 0; v <= maxYd; v += maxYd > 6 ? 2 : 1) {
      grid += '<text x="' + x(v) + '" y="' + (H - B + 16) + '" text-anchor="middle" font-size="11" fill="#64748b">' + v + '</text>';
    }
    grid += '<text x="' + ((L + W - R) / 2) + '" y="' + (H - 4) + '" text-anchor="middle" font-size="11" fill="#64748b">cubic yards (net)</text>';
    let marks = '';
    if (even !== null && even <= maxYd) {
      marks += '<line x1="' + x(even) + '" x2="' + x(even) + '" y1="' + T + '" y2="' + (H - B) + '" stroke="#16a34a" stroke-dasharray="4 4"/>'
        + '<text x="' + (x(even) + 4) + '" y="' + (T + 12) + '" font-size="11" fill="#15803d">break-even ' + fmtNum(even, 2) + ' yd³</text>';
    }
    if (net > 0 && net <= maxYd) {
      marks += '<line x1="' + x(net) + '" x2="' + x(net) + '" y1="' + T + '" y2="' + (H - B) + '" stroke="#0f172a" stroke-width="1.5"/>'
        + '<text x="' + (x(net) + 4) + '" y="' + (T + 26) + '" font-size="11" fill="#0f172a">your pour</text>';
    }
    box.innerHTML = '<svg viewBox="0 0 ' + W + ' ' + H + '" role="img" aria-label="Bag and ready-mix cost by volume" style="width:100%;height:auto;display:block">'
      + grid
      + '<path d="' + line(1) + '" fill="none" stroke="#d97706" stroke-width="2.5"/>'
      + '<path d="' + line(2) + '" fill="none" stroke="#2563eb" stroke-width="2.5"/>'
      + marks
      + '</svg><div class="be-legend"><span class="be-key be-key-bags"></span>Bags' + (o.mixer > 0 ? ' + mixer' : '') + ' <span class="be-key be-key-truck"></span>Ready-mix truck</div>';
  }

  /* Compare 2-3 quotes (Bid Checker v2, /concrete-quote-checker/).
   * Each quote gets a benchmark for its own scope (same rule as the Bid
   * Check tab: materials at default prices + standard labor, x region
   * index; fair up to +overhead/profit, over beyond +30% of that). Missing
   * items are then priced in at the middle of the fair band so the quotes
   * compare like for like. */
  function initQuoteCompare() {
    const el = document.getElementById('subcalc-quotecompare');
    if (!el) return;
    const q = (id) => el.querySelector('#' + id);
    const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const OP = sd('overheadProfit', 0.25);
    const LABELS = { base: 'excavation & gravel', vapor: 'vapor barrier', demo: 'old slab removal', pump: 'pump', permit: 'permit', rebar: 'rebar', mesh: 'wire mesh' };
    const cards = Array.from(el.querySelectorAll('.qc-quote'));

    function readQuote(card, i) {
      const inc = {};
      card.querySelectorAll('.qc-inc').forEach(cb => { inc[cb.value] = cb.checked; });
      const reinf = card.querySelector('.qc-reinf').value;
      inc.rebar = reinf === 'rebar';
      inc.mesh = reinf === 'mesh';
      const name = card.querySelector('.qc-name').value.trim() || ('Contractor ' + String.fromCharCode(65 + i));
      return { name: name, total: parseFloat(card.querySelector('.qc-total').value), finish: card.querySelector('.qc-finish').value, inc: inc };
    }

    function itemCosts(l, w, t) {
      const idx = (window.SlabCalculatorRegion && window.SlabCalculatorRegion.index) || 1;
      const area = l * w;
      const orderYd = area * t / 12 / 27 * (1 + wastePct() / 100);
      const gravelYd = area * sd('gravelDepthIn', 4) / 12 / 27 * sd('gravelCompaction', 1.15);
      const permit = sd('permitFlatwork', [50, 350]);
      return {
        idx: idx,
        area: area,
        core: orderYd * sd('readyMixPerYard', 150) * idx + sd('deliveryFee', 120) + 2 * (l + w) * sd('forms', 2.2),
        labor: { normal: area * sd('laborPerSqFt', 5) * idx, stamped: area * sd('laborStamped', 10) * idx },
        finish: sd('finish', { broom: 0.75 }),
        base: area * sd('excavation', 1.5) * idx + gravelYd * sd('gravelPerYard', 45),
        vapor: area * sd('vapor', 0.35),
        rebar: area * sd('rebar', 1.25),
        mesh: area * sd('mesh', 0.5),
        demo: area * sd('demo', 3.5),
        pump: sd('pump', 650),
        permit: (permit[0] + permit[1]) / 2
      };
    }

    function benchmark(c, quote) {
      const stamped = quote.finish === 'stamped';
      const labor = stamped ? c.labor.stamped : c.labor.normal;
      let direct = c.core + labor + c.area * (c.finish[quote.finish] || 0);
      ['base', 'vapor', 'rebar', 'mesh', 'demo', 'pump', 'permit'].forEach(k => { if (quote.inc[k]) direct += c[k]; });
      return { direct: direct, materials: direct - labor, high: direct * (1 + OP), over: direct * (1 + OP) * 1.3 };
    }

    function tier(price, b) {
      if (price < b.materials) return ['Below material cost', 'tier-low'];
      if (price <= b.direct) return ['Great deal', 'tier-great'];
      if (price <= b.high) return ['Fair', 'tier-fair'];
      if (price <= b.over) return ['Premium', 'tier-premium'];
      return ['Overpriced', 'tier-over'];
    }

    function calc() {
      const l = numOr(q('qc-length'), 0);
      const w = numOr(q('qc-width'), 0);
      const t = numOr(q('qc-thick'), 0);
      const region = window.SlabCalculatorRegion;
      setText(q('qc-region'), region && region.index !== 1
        ? 'Prices adjusted for ' + region.name + ' (index ' + region.index.toFixed(2) + '), from the calculator above.'
        : 'Prices: U.S. national average. Pick your state in the calculator above to adjust labor and concrete.');
      const quotes = cards.map(readQuote).filter(x => x.total > 0);
      const box = q('qc-result');
      if (!box) return;
      if (!(l > 0 && w > 0 && t > 0)) {
        box.innerHTML = '<p class="calc-field-note">Enter the slab size above zero.</p>';
        return;
      }
      if (quotes.length < 2) {
        box.innerHTML = '<p class="calc-field-note">Enter at least two quote totals and tick what each one includes.</p>';
        return;
      }
      const c = itemCosts(l, w, t);
      // The most complete scope across the quotes: every item any of them includes.
      const union = {};
      quotes.forEach(x => Object.keys(x.inc).forEach(k => { if (x.inc[k]) union[k] = true; }));
      if (union.rebar) delete union.mesh; // rebar covers the mesh line
      const rows = quotes.map(x => {
        const b = benchmark(c, x);
        const missing = Object.keys(union).filter(k => !x.inc[k] && !(k === 'mesh' && x.inc.rebar));
        let add = 0;
        missing.forEach(k => {
          const cost = (k === 'rebar' && x.inc.mesh) ? c.rebar - c.mesh : c[k];
          add += cost * (1 + OP / 2);
        });
        return { q: x, b: b, tier: tier(x.total, b), missing: missing, adjusted: x.total + add };
      });
      const fairRows = rows.filter(r => r.tier[1] !== 'tier-low');
      const best = (fairRows.length ? fairRows : rows).reduce((a, r) => (r.adjusted < a.adjusted ? r : a));
      let html = '<div class="table-responsive"><table class="data-table"><thead><tr><th>Quote</th><th>Price</th><th>Fair range for its scope</th><th>Verdict</th><th>Not included</th><th>Like-for-like price</th></tr></thead><tbody>';
      rows.forEach(r => {
        html += '<tr><td><strong>' + esc(r.q.name) + '</strong></td>'
          + '<td>' + money0(r.q.total) + '<br><span class="table-sub">$' + (r.q.total / c.area).toFixed(2) + '/sq ft</span></td>'
          + '<td>' + moneyRange(r.b.direct, r.b.high) + '</td>'
          + '<td><span class="bid-grade ' + r.tier[1] + '" style="display:inline-flex"><span class="bid-grade-dot"></span>' + esc(r.tier[0]) + '</span></td>'
          + '<td>' + (r.missing.length ? esc(r.missing.map(k => LABELS[k]).join(', ')) : 'Nothing') + '</td>'
          + '<td' + (r === best ? ' class="qc-best"' : '') + '>' + money0(r.adjusted) + (r === best ? ' ✓' : '') + '</td></tr>';
      });
      html += '</tbody></table></div>';
      const lowQuote = rows.find(r => r.tier[1] === 'tier-low');
      html += '<p class="calc-field-note">"Like-for-like" adds what each quote leaves out of the most complete one, priced at the middle of a fair bid, so the quotes compare on the same scope. '
        + '<strong>' + esc(best.q.name) + '</strong> is the best value on that basis' + (best.missing.length ? ' once its missing items are added' : '') + '.'
        + (lowQuote ? ' ' + esc(lowQuote.q.name) + ' is below the cost of materials for its scope: ask what is left out before you sign.' : '')
        + '</p>';
      box.innerHTML = html;
    }

    const inputs = Array.from(el.querySelectorAll('input, select'));
    bind(inputs, calc);
    // The region picker in the calculator above changes labor and concrete prices.
    document.addEventListener('slabcalc:updated', calc);
  }

  function initSupportingInputs() {
    // Generic event binding for instant feedback across supporting tools
  }

})();
