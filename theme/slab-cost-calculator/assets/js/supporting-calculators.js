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

  function initSupportingInputs() {
    // Generic event binding for instant feedback across supporting tools
  }

})();
