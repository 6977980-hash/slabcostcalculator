/**
 * CONCRETE SLAB COST CALCULATOR ENGINE
 * Production Engineering & Estimating Suite
 * Real-time reactive calculation, imperial/metric conversions, and line-item budgeting.
 */

(function () {
  'use strict';

  // Constants & Standards
  const YIELD_CUBIC_FEET_PER_YARD = 27;
  const CUBIC_FEET_TO_CUBIC_METERS = 0.0283168466;
  const CUBIC_METERS_TO_CUBIC_YARDS = 1.30795;
  const FEET_TO_METERS = 0.3048;
  const INCHES_TO_FEET = 1 / 12;
  const CM_TO_METERS = 0.01;
  const DENSITY_CONCRETE_LBS_PER_CUFT = 145; // ~3,915 lbs per cubic yard
  const DENSITY_CONCRETE_KG_PER_CUM = 2322;

  // Bag standard yields (in cubic feet)
  const BAG_YIELDS = {
    '80': 0.60,
    '60': 0.45,
    '50': 0.375,
    '40': 0.30
  };

  // Default inputs. Reset restores every key from here.
  const DEFAULTS = {
    unitSystem: 'imperial', // 'imperial' or 'metric'
    length: 20,
    width: 20,
    thickness: 4, // inches or cm
    quantity: 1,
    wastePercent: 10,
    materialType: 'readymix', // 'readymix' or 'bagged'
    bagSize: '80',
    bagPrice: 6.50,
    readyMixPricePerYard: 150.00,
    readyMixPricePerM3: 196.19, // $150/yd³ x 1.30795 yd³ per m³, so both unit modes price the same pour alike

    // Site Preparation
    enableSitePrep: false,
    excavationCostPerSqFt: 1.50,
    gravelDepthInches: 4,
    gravelPricePerYard: 45.00,
    enableForms: true,
    formCostPerLinearFt: 2.20,
    enableVaporBarrier: true,
    vaporBarrierCostPerSqFt: 0.35,
    enableDemoRemoval: false,
    demoCostPerSqFt: 3.50,

    // Reinforcement
    reinforcementType: 'rebar', // 'none', 'mesh', 'rebar', 'custom'
    meshCostPerSqFt: 0.50,
    rebarCostPerSqFt: 1.25, // #4 grid at REBAR_BASE_SPACING_IN, chairs included
    rebarSpacingIn: 18,
    customReinforceCost: 0,

    // Finish
    finishType: 'broom', // 'none', 'broom', 'trowel', 'exposed', 'stamped', 'custom'
    finishCostPerSqFt: 0.75,

    // Labor
    laborMode: 'sqft', // 'diy', 'sqft', 'hourly', 'flat'
    laborRatePerSqFt: 5.00,
    laborHourlyRate: 45.00,
    laborEstimatedHours: 16,
    laborFlatCost: 0,

    // Delivery & Equipment
    enableDeliveryFee: true,
    deliveryFee: 120.00,
    enablePumpRental: false,
    pumpCost: 650.00,
    enableEquipmentRental: false,
    equipmentRentalCost: 150.00,

    // Markup & Contingency
    markupPercent: 0,
    contingencyPercent: 5
  };

  // State Management
  const state = Object.assign({}, DEFAULTS);

  // The rebar rate is for an 18 in grid. Steel per sq ft scales with
  // 1 / spacing, so a 12 in grid costs 1.5x and a 24 in grid 0.75x.
  const REBAR_BASE_SPACING_IN = 18;

  // Bag counts are whole bags, but volumes like 52.8 ft3 / 0.6 come out as
  // 88.00000000000001 in floating point. Trim that noise before rounding up
  // so a 12x12x4 slab needs 88 bags, not 89.
  function ceilCount(x) {
    return Math.ceil(parseFloat(x.toFixed(6)));
  }

  // Reads a number field: blank or invalid gives 0, negatives clamp to 0.
  function readNum(el) {
    const v = parseFloat(el.value);
    return Number.isFinite(v) ? Math.max(0, v) : 0;
  }

  // Project Type Presets (Length, Width, Thickness in standard Imperial)
  const PRESETS = {
    'patio': { l: 12, w: 12, t: 4, name: 'Patio (12x12, 4")' },
    'walkway': { l: 30, w: 4, t: 4, name: 'Walkway (4x30, 4")' },
    'driveway': { l: 40, w: 20, t: 5, name: 'Driveway (20x40, 5")' },
    'garage': { l: 24, w: 24, t: 5, name: '2-Car Garage (24x24, 5")' },
    'shed': { l: 10, w: 10, t: 4, name: 'Shed Pad (10x10, 4")' },
    'foundation': { l: 30, w: 40, t: 6, name: 'Foundation (30x40, 6")' },
    'pad': { l: 8, w: 8, t: 4, name: 'Equipment Pad (8x8, 4")' }
  };

  // DOM Elements Cache
  let dom = {};

  // Last computed structured result -- THE single source of truth shared by
  // the on-screen dashboard/BOQ table (updateDOM below) and the PDF report
  // generator (assets/js/pdf-report.js, loaded after this file). Neither
  // consumer recomputes any figure independently; both read from this
  // object so the two can never disagree or drift out of reconciliation.
  let lastResult = null;
  let activePresetKey = null;
  let activePresetName = null;

  function initDOM() {
    const root = document.getElementById('concrete-slab-calculator');
    if (!root) return false;

    dom = {
      root: root,
      unitBtns: root.querySelectorAll('.unit-btn'),
      // [data-preset] only: the weather tool's Cold/Mild/Hot buttons share
      // the pill style but are not project presets.
      presetBtns: root.querySelectorAll('.preset-btn[data-preset]'),

      // Inputs
      inputLength: root.querySelector('#calc-length'),
      inputWidth: root.querySelector('#calc-width'),
      inputThickness: root.querySelector('#calc-thickness'),
      inputQuantity: root.querySelector('#calc-quantity'),
      inputWaste: root.querySelector('#calc-waste'),

      // Material
      selectMaterialType: root.querySelector('#calc-material-type'),
      rowReadyMix: root.querySelector('#row-readymix'),
      rowBagged: root.querySelector('#row-bagged'),
      inputReadyMixPrice: root.querySelector('#calc-readymix-price'),
      selectBagSize: root.querySelector('#calc-bag-size'),
      inputBagPrice: root.querySelector('#calc-bag-price'),

      // Site Prep
      chkSitePrep: root.querySelector('#chk-site-prep'),
      bodySitePrep: root.querySelector('#body-site-prep'),
      inputExcavationCost: root.querySelector('#calc-excavation-rate'),
      inputGravelDepth: root.querySelector('#calc-gravel-depth'),
      inputGravelPrice: root.querySelector('#calc-gravel-price'),
      chkForms: root.querySelector('#chk-forms'),
      inputFormCost: root.querySelector('#calc-form-cost'),
      chkVaporBarrier: root.querySelector('#chk-vapor-barrier'),
      inputVaporCost: root.querySelector('#calc-vapor-cost'),
      chkDemo: root.querySelector('#chk-demo-removal'),
      inputDemoCost: root.querySelector('#calc-demo-cost'),

      // Reinforcement
      selectReinforcement: root.querySelector('#calc-reinforce-type'),
      selectRebarSpacing: root.querySelector('#calc-rebar-spacing'),
      inputReinforceCost: root.querySelector('#calc-reinforce-cost'),

      // Finish
      selectFinish: root.querySelector('#calc-finish-type'),
      inputFinishCost: root.querySelector('#calc-finish-cost'),

      // Labor
      selectLaborMode: root.querySelector('#calc-labor-mode'),
      rowLaborSqft: root.querySelector('#row-labor-sqft'),
      rowLaborHourly: root.querySelector('#row-labor-hourly'),
      rowLaborFlat: root.querySelector('#row-labor-flat'),
      inputLaborSqft: root.querySelector('#calc-labor-sqft-rate'),
      inputLaborHourlyRate: root.querySelector('#calc-labor-hourly-rate'),
      inputLaborHours: root.querySelector('#calc-labor-hours'),
      inputLaborFlat: root.querySelector('#calc-labor-flat'),

      // Delivery & Equipment
      chkDelivery: root.querySelector('#chk-delivery-fee'),
      inputDeliveryFee: root.querySelector('#calc-delivery-amount'),
      chkPump: root.querySelector('#chk-pump-rental'),
      inputPumpCost: root.querySelector('#calc-pump-amount'),
      chkEquipment: root.querySelector('#chk-equipment-rental'),
      inputEquipmentCost: root.querySelector('#calc-equipment-amount'),

      // Markup / Contingency
      inputMarkup: root.querySelector('#calc-markup-percent'),
      inputContingency: root.querySelector('#calc-contingency-percent'),

      // Outputs
      outTotalCost: root.querySelector('#out-total-cost'),
      outCostPerSqFt: root.querySelector('#out-cost-per-sqft'),
      outCostPerSqM: root.querySelector('#out-cost-per-sqm'),
      outNetCubicYards: root.querySelector('#out-net-yards'),
      outWasteCubicYards: root.querySelector('#out-waste-yards'),
      outNetCubicMeters: root.querySelector('#out-net-meters'),
      outTotalBags: root.querySelector('#out-total-bags'),
      outBagNote: root.querySelector('#out-bag-note'),
      validationMsg: root.querySelector('#calc-validation-msg'),
      outAreaSqFt: root.querySelector('#out-area-sqft'),
      outPerimeterFt: root.querySelector('#out-perimeter-ft'),
      outTotalWeight: root.querySelector('#out-total-weight'),

      // Breakdown Table Body
      boqTableBody: root.querySelector('#boq-table-body'),

      // Cost bar segments
      segMaterial: root.querySelector('#seg-material'),
      segLabor: root.querySelector('#seg-labor'),
      segBase: root.querySelector('#seg-base'),
      segReinforce: root.querySelector('#seg-reinforce'),
      segForms: root.querySelector('#seg-forms'),
      segOther: root.querySelector('#seg-other'),

      // Action Buttons
      btnReset: root.querySelector('#btn-calc-reset'),
      btnPrint: root.querySelector('#btn-calc-print'),
      btnCopy: root.querySelector('#btn-calc-copy'),
      btnExport: root.querySelector('#btn-calc-export'),
      toast: document.getElementById('calc-toast')
    };

    return true;
  }

  function bindEvents() {
    // Unit switchers
    if (dom.unitBtns) {
      dom.unitBtns.forEach(btn => {
        btn.addEventListener('click', function () {
          const newUnit = this.getAttribute('data-unit');
          if (newUnit !== state.unitSystem) {
            setUnitSystem(newUnit);
          }
        });
      });
    }

    // Preset Buttons
    if (dom.presetBtns) {
      dom.presetBtns.forEach(btn => {
        btn.addEventListener('click', function () {
          const presetKey = this.getAttribute('data-preset');
          applyPreset(presetKey);
          setActivePreset(this);
        });
      });
    }

    // Accordion Headers Toggle
    const accordions = dom.root.querySelectorAll('.scope-accordion');
    accordions.forEach(acc => {
      const header = acc.querySelector('.scope-header');
      if (header) {
        header.addEventListener('click', function (e) {
          const open = acc.classList.toggle('open');
          header.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
      }
    });

    // Material Dropdown Toggle
    if (dom.selectMaterialType) {
      dom.selectMaterialType.addEventListener('change', function () {
        state.materialType = this.value;
        syncConditionalRows();
        recalculate();
      });
    }

    // Labor Mode Dropdown Toggle
    if (dom.selectLaborMode) {
      dom.selectLaborMode.addEventListener('change', function () {
        state.laborMode = this.value;
        syncConditionalRows();
        recalculate();
      });
    }

    // All form input changes trigger recalculate. Scoped to the main form so
    // the tool panels below it (bid checker, weather check) don't reset the
    // preset label every time someone types in them.
    const inputs = dom.root.querySelectorAll('.calc-body-grid input, .calc-body-grid select');
    inputs.forEach(input => {
      input.addEventListener('input', () => {
        syncStateFromDOM();
        recalculate();
      });
      input.addEventListener('change', () => {
        syncStateFromDOM();
        recalculate();
      });
    });

    // Action Buttons
    if (dom.btnReset) dom.btnReset.addEventListener('click', resetCalculator);
    // PDF REPORT FIX: this used to be window.print(), which hands the
    // browser's print dialog the live page DOM (styled down by print.css)
    // -- effectively a screenshot/print of the calculator screen, exactly
    // what the new report replaces. assets/js/pdf-report.js (enqueued
    // right after this file) registers window.SlabPDFReport.generate() and
    // builds a real, structured PDF straight from `lastResult` -- the same
    // object updateDOM() below used to paint the on-screen numbers, so the
    // report and the screen can never disagree. If that script failed to
    // load for any reason, this still falls back to the original
    // window.print() behavior rather than doing nothing.
    if (dom.btnPrint) {
      dom.btnPrint.addEventListener('click', function () {
        if (!ensureValid()) return;
        const report = window.SlabPDFReport;
        if (!report || typeof report.load !== 'function') {
          window.print();
          return;
        }
        // jsPDF is fetched on the first click only (see pdf-report.js).
        report.load()
          .then(function () { report.generate(lastResult || computeEstimate()); })
          .catch(function () { window.print(); });
      });
    }
    if (dom.btnCopy) dom.btnCopy.addEventListener('click', copySummaryToClipboard);
    if (dom.btnExport) dom.btnExport.addEventListener('click', exportEstimateFile);
  }

  function setUnitSystem(newUnit) {
    if (newUnit === 'metric') {
      // Convert Imperial to Metric
      state.length = parseFloat((state.length * FEET_TO_METERS).toFixed(2));
      state.width = parseFloat((state.width * FEET_TO_METERS).toFixed(2));
      state.thickness = parseFloat((state.thickness * 2.54).toFixed(1)); // in -> cm
      state.readyMixPricePerM3 = parseFloat((state.readyMixPricePerYard * CUBIC_METERS_TO_CUBIC_YARDS).toFixed(2));
      state.unitSystem = 'metric';
    } else {
      // Convert Metric to Imperial. One decimal, so a round trip brings
      // 20 ft back as 20 ft instead of 20.01 ft (6.10 m / 0.3048).
      state.length = parseFloat((state.length / FEET_TO_METERS).toFixed(1));
      state.width = parseFloat((state.width / FEET_TO_METERS).toFixed(1));
      state.thickness = parseFloat((state.thickness / 2.54).toFixed(1)); // cm -> in
      state.readyMixPricePerYard = parseFloat((state.readyMixPricePerM3 / CUBIC_METERS_TO_CUBIC_YARDS).toFixed(2));
      state.unitSystem = 'imperial';
    }

    // Update labels and placeholders in DOM
    updateUnitLabels();
    syncDOMFromState();
    recalculate();
  }

  function updateUnitLabels() {
    dom.unitBtns.forEach(btn => {
      const on = btn.getAttribute('data-unit') === state.unitSystem;
      btn.classList.toggle('active', on);
      btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    });

    const isImp = state.unitSystem === 'imperial';
    dom.root.querySelectorAll('.unit-len-label').forEach(el => el.textContent = isImp ? 'ft' : 'm');
    dom.root.querySelectorAll('.unit-thick-label').forEach(el => el.textContent = isImp ? 'inches' : 'cm');
    dom.root.querySelectorAll('.unit-area-label').forEach(el => el.textContent = isImp ? 'sq. ft.' : 'm²');
    dom.root.querySelectorAll('.unit-vol-label').forEach(el => el.textContent = isImp ? 'cu. yds' : 'm³');
  }

  function applyPreset(key) {
    const p = PRESETS[key];
    if (!p) return;

    activePresetKey = key;
    activePresetName = p.name;

    if (state.unitSystem === 'imperial') {
      state.length = p.l;
      state.width = p.w;
      state.thickness = p.t;
    } else {
      state.length = parseFloat((p.l * FEET_TO_METERS).toFixed(2));
      state.width = parseFloat((p.w * FEET_TO_METERS).toFixed(2));
      state.thickness = parseFloat((p.t * 2.54).toFixed(1));
    }

    syncDOMFromState();
    recalculate();
  }

  function syncStateFromDOM() {
    // Any manual edit invalidates the active preset label (the project no
    // longer matches that preset's stock dimensions/scope exactly), so the
    // PDF cover page doesn't keep claiming e.g. "Patio (12x12, 4")" after
    // the user has since changed the thickness.
    activePresetKey = null;
    activePresetName = null;

    state.length = readNum(dom.inputLength);
    state.width = readNum(dom.inputWidth);
    state.thickness = readNum(dom.inputThickness);
    state.quantity = Math.max(1, parseInt(dom.inputQuantity.value, 10) || 1);
    state.wastePercent = readNum(dom.inputWaste);

    // Materials
    state.materialType = dom.selectMaterialType ? dom.selectMaterialType.value : 'readymix';
    // The price box is labelled /yd³ or /m³ by the unit toggle, so store it
    // in the matching field: computeEstimate() prices metric pours with
    // readyMixPricePerM3, which used to never receive the typed value.
    if (dom.inputReadyMixPrice) {
      const price = readNum(dom.inputReadyMixPrice);
      if (state.unitSystem === 'imperial') state.readyMixPricePerYard = price;
      else state.readyMixPricePerM3 = price;
    }
    if (dom.selectBagSize) state.bagSize = dom.selectBagSize.value;
    if (dom.inputBagPrice) state.bagPrice = readNum(dom.inputBagPrice);

    // Site Prep
    if (dom.chkSitePrep) state.enableSitePrep = dom.chkSitePrep.checked;
    if (dom.inputExcavationCost) state.excavationCostPerSqFt = readNum(dom.inputExcavationCost);
    // Gravel depth is shown in inches or cm with the unit toggle but kept
    // in inches in state, which is what computeEstimate() and the PDF use.
    if (dom.inputGravelDepth) {
      const depth = readNum(dom.inputGravelDepth);
      state.gravelDepthInches = state.unitSystem === 'imperial' ? depth : parseFloat((depth / 2.54).toFixed(2));
    }
    if (dom.inputGravelPrice) state.gravelPricePerYard = readNum(dom.inputGravelPrice);
    if (dom.chkForms) state.enableForms = dom.chkForms.checked;
    if (dom.inputFormCost) state.formCostPerLinearFt = readNum(dom.inputFormCost);
    if (dom.chkVaporBarrier) state.enableVaporBarrier = dom.chkVaporBarrier.checked;
    if (dom.inputVaporCost) state.vaporBarrierCostPerSqFt = readNum(dom.inputVaporCost);
    if (dom.chkDemo) state.enableDemoRemoval = dom.chkDemo.checked;
    if (dom.inputDemoCost) state.demoCostPerSqFt = readNum(dom.inputDemoCost);

    // Reinforce
    if (dom.selectReinforcement) state.reinforcementType = dom.selectReinforcement.value;
    if (dom.selectRebarSpacing) state.rebarSpacingIn = parseFloat(dom.selectRebarSpacing.value) || REBAR_BASE_SPACING_IN;
    if (dom.inputReinforceCost) state.customReinforceCost = readNum(dom.inputReinforceCost);

    // Finish
    if (dom.selectFinish) state.finishType = dom.selectFinish.value;
    if (dom.inputFinishCost) state.finishCostPerSqFt = readNum(dom.inputFinishCost);

    // Labor
    if (dom.selectLaborMode) state.laborMode = dom.selectLaborMode.value;
    if (dom.inputLaborSqft) state.laborRatePerSqFt = readNum(dom.inputLaborSqft);
    if (dom.inputLaborHourlyRate) state.laborHourlyRate = readNum(dom.inputLaborHourlyRate);
    if (dom.inputLaborHours) state.laborEstimatedHours = readNum(dom.inputLaborHours);
    if (dom.inputLaborFlat) state.laborFlatCost = readNum(dom.inputLaborFlat);

    // Delivery / Equip
    if (dom.chkDelivery) state.enableDeliveryFee = dom.chkDelivery.checked;
    if (dom.inputDeliveryFee) state.deliveryFee = readNum(dom.inputDeliveryFee);
    if (dom.chkPump) state.enablePumpRental = dom.chkPump.checked;
    if (dom.inputPumpCost) state.pumpCost = readNum(dom.inputPumpCost);
    if (dom.chkEquipment) state.enableEquipmentRental = dom.chkEquipment.checked;
    if (dom.inputEquipmentCost) state.equipmentRentalCost = readNum(dom.inputEquipmentCost);

    // Markup
    if (dom.inputMarkup) state.markupPercent = readNum(dom.inputMarkup);
    if (dom.inputContingency) state.contingencyPercent = readNum(dom.inputContingency);
  }

  function syncDOMFromState() {
    if (dom.inputLength) dom.inputLength.value = state.length;
    if (dom.inputWidth) dom.inputWidth.value = state.width;
    if (dom.inputThickness) dom.inputThickness.value = state.thickness;
    if (dom.inputQuantity) dom.inputQuantity.value = state.quantity;
    if (dom.inputWaste) dom.inputWaste.value = state.wastePercent;
    const isImp = state.unitSystem === 'imperial';
    if (dom.inputReadyMixPrice) dom.inputReadyMixPrice.value = (isImp ? state.readyMixPricePerYard : state.readyMixPricePerM3).toFixed(2);
    if (dom.inputGravelDepth) dom.inputGravelDepth.value = isImp ? state.gravelDepthInches : parseFloat((state.gravelDepthInches * 2.54).toFixed(1));
  }

  // Writes every remaining field (selects, checkboxes, rates) from state.
  // Used by reset so the screen matches the defaults it restores.
  function syncAllFieldsFromState() {
    syncDOMFromState();
    const set = (el, v) => { if (el) el.value = v; };
    const check = (el, v) => { if (el) el.checked = v; };
    set(dom.selectMaterialType, state.materialType);
    set(dom.selectBagSize, state.bagSize);
    set(dom.inputBagPrice, state.bagPrice.toFixed(2));
    check(dom.chkSitePrep, state.enableSitePrep);
    set(dom.inputExcavationCost, state.excavationCostPerSqFt.toFixed(2));
    set(dom.inputGravelPrice, state.gravelPricePerYard.toFixed(2));
    check(dom.chkForms, state.enableForms);
    set(dom.inputFormCost, state.formCostPerLinearFt.toFixed(2));
    check(dom.chkVaporBarrier, state.enableVaporBarrier);
    set(dom.inputVaporCost, state.vaporBarrierCostPerSqFt.toFixed(2));
    check(dom.chkDemo, state.enableDemoRemoval);
    set(dom.inputDemoCost, state.demoCostPerSqFt.toFixed(2));
    set(dom.selectReinforcement, state.reinforcementType);
    set(dom.selectRebarSpacing, String(state.rebarSpacingIn));
    set(dom.inputReinforceCost, state.customReinforceCost);
    set(dom.selectFinish, state.finishType);
    set(dom.inputFinishCost, state.finishCostPerSqFt.toFixed(2));
    set(dom.selectLaborMode, state.laborMode);
    set(dom.inputLaborSqft, state.laborRatePerSqFt.toFixed(2));
    set(dom.inputLaborHourlyRate, state.laborHourlyRate.toFixed(2));
    set(dom.inputLaborHours, state.laborEstimatedHours);
    set(dom.inputLaborFlat, state.laborFlatCost);
    check(dom.chkDelivery, state.enableDeliveryFee);
    set(dom.inputDeliveryFee, state.deliveryFee);
    check(dom.chkPump, state.enablePumpRental);
    set(dom.inputPumpCost, state.pumpCost);
    check(dom.chkEquipment, state.enableEquipmentRental);
    set(dom.inputEquipmentCost, state.equipmentRentalCost);
    set(dom.inputMarkup, state.markupPercent);
    set(dom.inputContingency, state.contingencyPercent);
    syncConditionalRows();
  }

  // Shows the price row that matches the concrete source and labor method.
  function syncConditionalRows() {
    const bagged = state.materialType === 'bagged';
    if (dom.rowReadyMix) dom.rowReadyMix.style.display = bagged ? 'none' : 'block';
    if (dom.rowBagged) dom.rowBagged.style.display = bagged ? 'grid' : 'none';
    if (dom.rowLaborSqft) dom.rowLaborSqft.style.display = state.laborMode === 'sqft' ? 'block' : 'none';
    if (dom.rowLaborHourly) dom.rowLaborHourly.style.display = state.laborMode === 'hourly' ? 'grid' : 'none';
    if (dom.rowLaborFlat) dom.rowLaborFlat.style.display = state.laborMode === 'flat' ? 'block' : 'none';
  }

  function setActivePreset(btn) {
    if (!dom.presetBtns) return;
    dom.presetBtns.forEach(b => {
      const on = b === btn;
      b.classList.toggle('active', on);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
  }

  /**
   * SINGLE SOURCE OF TRUTH FOR ALL CALCULATIONS.
   *
   * Pure function: reads `state`, returns one structured result object, and
   * touches nothing in the DOM. This is the exact same math the calculator
   * has always run (no formula was changed by this refactor -- see the
   * PDF-report delivery notes for the audit that confirmed that). The only
   * change is that every intermediate figure (not just the handful the old
   * code echoed straight into dom.out* elements) is now captured on the
   * returned object, because the new PDF report needs to show its work --
   * area, thickness conversion, net volume, waste, order quantity, every
   * line item's own quantity/unit/rate, category subtotals, markup and
   * contingency math, and the two unit-cost figures -- using the identical
   * values the on-screen dashboard displays, not a re-derived copy.
   *
   * updateDOM() below (called from recalculate()) and
   * assets/js/pdf-report.js both consume this same object. Neither ever
   * recomputes a figure independently.
   */
  function computeEstimate() {
    const isImp = state.unitSystem === 'imperial';

    // 1. Area & Perimeter
    let areaSqFt = 0;
    let areaSqM = 0;
    let perimeterFt = 0;
    let perimeterM = 0;

    if (isImp) {
      areaSqFt = state.length * state.width * state.quantity;
      areaSqM = areaSqFt * 0.092903;
      perimeterFt = 2 * (state.length + state.width) * state.quantity;
      perimeterM = perimeterFt * FEET_TO_METERS;
    } else {
      areaSqM = state.length * state.width * state.quantity;
      areaSqFt = areaSqM / 0.092903;
      perimeterM = 2 * (state.length + state.width) * state.quantity;
      perimeterFt = perimeterM / FEET_TO_METERS;
    }

    // 2. Net Volume Calculations
    let netCubicFt = 0;
    let netCubicYds = 0;
    let netCubicM = 0;
    let thicknessFt = 0;
    let thicknessM = 0;

    if (isImp) {
      thicknessFt = state.thickness * INCHES_TO_FEET;
      netCubicFt = areaSqFt * thicknessFt;
      netCubicYds = netCubicFt / YIELD_CUBIC_FEET_PER_YARD;
      netCubicM = netCubicFt * CUBIC_FEET_TO_CUBIC_METERS;
    } else {
      thicknessM = state.thickness * CM_TO_METERS;
      netCubicM = areaSqM * thicknessM;
      netCubicFt = netCubicM / CUBIC_FEET_TO_CUBIC_METERS;
      netCubicYds = netCubicFt / YIELD_CUBIC_FEET_PER_YARD;
    }

    // A slab needs a length, width and thickness above zero. Without them
    // no line item is priced (lump sums like delivery would otherwise still
    // show up as a total for a slab that doesn't exist).
    const valid = areaSqFt > 0 && netCubicFt > 0;

    // 3. Waste-Adjusted Volume
    const wasteFactor = 1 + (state.wastePercent / 100);
    const orderCubicYds = netCubicYds * wasteFactor;
    const orderCubicM = netCubicM * wasteFactor;
    const orderCubicFt = netCubicFt * wasteFactor;
    const wasteCubicYds = orderCubicYds - netCubicYds;
    const wasteCubicM = orderCubicM - netCubicM;

    // 4. Weight
    const weightLbs = orderCubicFt * DENSITY_CONCRETE_LBS_PER_CUFT;
    const weightKg = orderCubicM * DENSITY_CONCRETE_KG_PER_CUM;

    // 5. Bag Quantities
    const bagYieldCuFt = BAG_YIELDS[state.bagSize] || 0.60;
    const totalBags = ceilCount(orderCubicFt / bagYieldCuFt);

    // ================= LINE-ITEM COSTS =================
    const items = [];
    const detail = {};

    // Concrete Material Cost
    let concreteCost = 0;
    let concreteUnitRate = 0;
    let concreteQty = 0;
    let concreteUnit = '';
    if (state.materialType === 'readymix') {
      if (isImp) {
        concreteCost = orderCubicYds * state.readyMixPricePerYard;
        concreteUnitRate = state.readyMixPricePerYard;
        concreteQty = orderCubicYds;
        concreteUnit = 'yd³';
        items.push({
          category: 'material',
          name: `Ready-Mix Concrete (${orderCubicYds.toFixed(2)} yd³ @ $${state.readyMixPricePerYard.toFixed(2)}/yd³)`,
          cost: concreteCost,
          qty: concreteQty,
          unit: concreteUnit,
          rate: concreteUnitRate
        });
      } else {
        concreteCost = orderCubicM * state.readyMixPricePerM3;
        concreteUnitRate = state.readyMixPricePerM3;
        concreteQty = orderCubicM;
        concreteUnit = 'm³';
        items.push({
          category: 'material',
          name: `Ready-Mix Concrete (${orderCubicM.toFixed(2)} m³ @ $${state.readyMixPricePerM3.toFixed(2)}/m³)`,
          cost: concreteCost,
          qty: concreteQty,
          unit: concreteUnit,
          rate: concreteUnitRate
        });
      }
    } else {
      concreteCost = totalBags * state.bagPrice;
      concreteUnitRate = state.bagPrice;
      concreteQty = totalBags;
      concreteUnit = 'bags';
      items.push({
        category: 'material',
        name: `Bagged Concrete (${totalBags} bags of ${state.bagSize}lb @ $${state.bagPrice.toFixed(2)}/bag)`,
        cost: concreteCost,
        qty: concreteQty,
        unit: concreteUnit,
        rate: concreteUnitRate
      });
    }
    detail.concrete = {
      materialType: state.materialType,
      orderCubicYds: orderCubicYds,
      orderCubicM: orderCubicM,
      unitRate: concreteUnitRate,
      qty: concreteQty,
      unit: concreteUnit,
      cost: concreteCost,
      bagSize: state.bagSize,
      bagYieldCuFt: bagYieldCuFt,
      totalBags: totalBags,
      bagPrice: state.bagPrice
    };

    // Site Prep: Excavation / Grading
    let excavationCost = 0;
    let gravelCost = 0;
    let gravelYards = 0;
    let gravelVolCuFt = 0;
    const gravelCompactionFactor = 1.15;
    if (state.enableSitePrep) {
      excavationCost = areaSqFt * state.excavationCostPerSqFt;
      items.push({
        category: 'base',
        name: `Site Excavation & Grading (${areaSqFt.toFixed(0)} sq ft)`,
        cost: excavationCost,
        qty: areaSqFt,
        unit: 'sq ft',
        rate: state.excavationCostPerSqFt
      });

      // Gravel Base
      if (state.gravelDepthInches > 0) {
        gravelVolCuFt = areaSqFt * (state.gravelDepthInches * INCHES_TO_FEET);
        gravelYards = (gravelVolCuFt / 27) * gravelCompactionFactor; // 15% compaction factor
        gravelCost = gravelYards * state.gravelPricePerYard;
        items.push({
          category: 'base',
          name: isImp
            ? `Compacted Gravel Sub-Base (${state.gravelDepthInches}" depth, ${gravelYards.toFixed(1)} yd³)`
            : `Compacted Gravel Sub-Base (${(state.gravelDepthInches * 2.54).toFixed(1)} cm depth, ${(gravelYards * 27 * CUBIC_FEET_TO_CUBIC_METERS).toFixed(1)} m³)`,
          cost: gravelCost,
          qty: gravelYards,
          unit: 'yd³',
          rate: state.gravelPricePerYard
        });
      }
    }
    detail.excavation = {
      enabled: state.enableSitePrep,
      areaSqFt: areaSqFt,
      rate: state.excavationCostPerSqFt,
      cost: excavationCost
    };
    detail.gravel = {
      enabled: state.enableSitePrep && state.gravelDepthInches > 0,
      areaSqFt: areaSqFt,
      depthInches: state.gravelDepthInches,
      rawVolCuFt: gravelVolCuFt,
      compactionFactor: gravelCompactionFactor,
      gravelYards: gravelYards,
      rate: state.gravelPricePerYard,
      cost: gravelCost
    };

    // Formwork
    let formCost = 0;
    if (state.enableForms) {
      formCost = perimeterFt * state.formCostPerLinearFt;
      items.push({
        category: 'forms',
        name: `Lumber Formwork & Staking (${perimeterFt.toFixed(0)} lin ft)`,
        cost: formCost,
        qty: perimeterFt,
        unit: 'lin ft',
        rate: state.formCostPerLinearFt
      });
    }
    detail.forms = {
      enabled: state.enableForms,
      perimeterFt: perimeterFt,
      rate: state.formCostPerLinearFt,
      cost: formCost
    };

    // Vapor Barrier
    let vaporCost = 0;
    if (state.enableVaporBarrier) {
      vaporCost = areaSqFt * state.vaporBarrierCostPerSqFt;
      items.push({
        category: 'base',
        name: `Vapor Barrier (10-15 mil Under-Slab Poly Sheet)`,
        cost: vaporCost,
        qty: areaSqFt,
        unit: 'sq ft',
        rate: state.vaporBarrierCostPerSqFt
      });
    }
    detail.vaporBarrier = {
      enabled: state.enableVaporBarrier,
      areaSqFt: areaSqFt,
      rate: state.vaporBarrierCostPerSqFt,
      cost: vaporCost
    };

    // Demolition / Existing Concrete Removal
    let demoCost = 0;
    if (state.enableDemoRemoval) {
      demoCost = areaSqFt * state.demoCostPerSqFt;
      items.push({
        category: 'other',
        name: `Existing Slab Demolition & Disposal`,
        cost: demoCost,
        qty: areaSqFt,
        unit: 'sq ft',
        rate: state.demoCostPerSqFt
      });
    }
    detail.demolition = {
      enabled: state.enableDemoRemoval,
      areaSqFt: areaSqFt,
      rate: state.demoCostPerSqFt,
      cost: demoCost
    };

    // Reinforcement
    let reinforceCost = 0;
    let reinforceLabel = 'None (Plain concrete)';
    let reinforceBasis = null;
    if (state.reinforcementType === 'mesh') {
      reinforceCost = areaSqFt * state.meshCostPerSqFt;
      reinforceLabel = 'Welded Wire Mesh (6x6 W1.4/W2.9 sheets + chairs)';
      reinforceBasis = { qty: areaSqFt, unit: 'sq ft', rate: state.meshCostPerSqFt };
      items.push({ category: 'reinforce', name: reinforceLabel, cost: reinforceCost, qty: areaSqFt, unit: 'sq ft', rate: state.meshCostPerSqFt });
    } else if (state.reinforcementType === 'rebar') {
      const spacingIn = state.rebarSpacingIn > 0 ? state.rebarSpacingIn : REBAR_BASE_SPACING_IN;
      const rebarRate = state.rebarCostPerSqFt * REBAR_BASE_SPACING_IN / spacingIn;
      reinforceCost = areaSqFt * rebarRate;
      reinforceLabel = `#4 (1/2") Steel Rebar Grid @ ${spacingIn} in o.c. & Support Chairs`;
      reinforceBasis = { qty: areaSqFt, unit: 'sq ft', rate: rebarRate };
      items.push({ category: 'reinforce', name: reinforceLabel, cost: reinforceCost, qty: areaSqFt, unit: 'sq ft', rate: rebarRate });
    } else if (state.reinforcementType === 'custom') {
      reinforceCost = state.customReinforceCost;
      reinforceLabel = 'Custom Reinforcement Specification';
      reinforceBasis = { qty: 1, unit: 'lump sum', rate: state.customReinforceCost };
      items.push({ category: 'reinforce', name: reinforceLabel, cost: reinforceCost, qty: 1, unit: 'lump sum', rate: state.customReinforceCost });
    }
    detail.reinforcement = {
      type: state.reinforcementType,
      label: reinforceLabel,
      basis: reinforceBasis,
      cost: reinforceCost
    };

    // Finish
    let finishCost = 0;
    let finishLabel = 'Basic Float Only';
    let finishRate = 0;
    if (state.finishType === 'broom') {
      finishRate = 0.75;
      finishCost = areaSqFt * finishRate;
      finishLabel = 'Broom Finish (Standard Non-Slip)';
      items.push({ category: 'other', name: finishLabel, cost: finishCost, qty: areaSqFt, unit: 'sq ft', rate: finishRate });
    } else if (state.finishType === 'trowel') {
      finishRate = 1.25;
      finishCost = areaSqFt * finishRate;
      finishLabel = 'Smooth Hard Trowel Finish (Interior/Garage)';
      items.push({ category: 'other', name: finishLabel, cost: finishCost, qty: areaSqFt, unit: 'sq ft', rate: finishRate });
    } else if (state.finishType === 'exposed') {
      finishRate = 3.50;
      finishCost = areaSqFt * finishRate;
      finishLabel = 'Exposed Aggregate Decorative Finish';
      items.push({ category: 'other', name: finishLabel, cost: finishCost, qty: areaSqFt, unit: 'sq ft', rate: finishRate });
    } else if (state.finishType === 'stamped') {
      finishRate = 7.50;
      finishCost = areaSqFt * finishRate;
      finishLabel = 'Stamped Concrete Pattern & Color Release';
      items.push({ category: 'other', name: finishLabel, cost: finishCost, qty: areaSqFt, unit: 'sq ft', rate: finishRate });
    } else if (state.finishType === 'custom') {
      finishRate = state.finishCostPerSqFt;
      finishCost = finishRate * areaSqFt;
      finishLabel = 'Custom Concrete Surface Finish';
      items.push({ category: 'other', name: finishLabel, cost: finishCost, qty: areaSqFt, unit: 'sq ft', rate: finishRate });
    }
    detail.finishing = {
      type: state.finishType,
      label: finishLabel,
      areaSqFt: areaSqFt,
      rate: finishRate,
      cost: finishCost
    };

    // Labor
    let laborCost = 0;
    let laborLabel = '';
    if (state.laborMode === 'sqft') {
      laborCost = areaSqFt * state.laborRatePerSqFt;
      laborLabel = `Pouring & Finishing Labor ($${state.laborRatePerSqFt.toFixed(2)}/sq ft)`;
      items.push({ category: 'labor', name: laborLabel, cost: laborCost, qty: areaSqFt, unit: 'sq ft', rate: state.laborRatePerSqFt });
    } else if (state.laborMode === 'hourly') {
      laborCost = state.laborHourlyRate * state.laborEstimatedHours;
      laborLabel = `Crew Labor (${state.laborEstimatedHours} hrs @ $${state.laborHourlyRate.toFixed(2)}/hr)`;
      items.push({ category: 'labor', name: laborLabel, cost: laborCost, qty: state.laborEstimatedHours, unit: 'hrs', rate: state.laborHourlyRate });
    } else if (state.laborMode === 'flat') {
      laborCost = state.laborFlatCost;
      laborLabel = `Contractor Flat Labor Estimate`;
      items.push({ category: 'labor', name: laborLabel, cost: laborCost, qty: 1, unit: 'lump sum', rate: state.laborFlatCost });
    } else if (state.laborMode === 'diy') {
      // No cost, but still list it so the BOQ table doesn't silently drop
      // the labor line the user explicitly selected.
      laborLabel = `DIY / Self-Performed Labor (no contractor cost)`;
      items.push({
        category: 'labor',
        name: laborLabel,
        cost: 0,
        qty: null,
        unit: null,
        rate: null
      });
    }
    detail.labor = {
      mode: state.laborMode,
      label: laborLabel,
      areaSqFt: areaSqFt,
      ratePerSqFt: state.laborRatePerSqFt,
      hourlyRate: state.laborHourlyRate,
      hours: state.laborEstimatedHours,
      flatCost: state.laborFlatCost,
      cost: laborCost
    };

    // Delivery & Equipment
    // The truck delivery / short-load fee only applies to ready-mix.
    let deliveryCost = 0;
    const deliveryOn = state.enableDeliveryFee && state.deliveryFee > 0 && state.materialType === 'readymix';
    if (deliveryOn) {
      deliveryCost = state.deliveryFee;
      items.push({ category: 'other', name: `Ready-Mix Truck Delivery / Short-Load Fee`, cost: deliveryCost, qty: 1, unit: 'lump sum', rate: deliveryCost });
    }
    detail.delivery = { enabled: deliveryOn, cost: deliveryCost };

    let pumpCost = 0;
    if (state.enablePumpRental && state.pumpCost > 0) {
      pumpCost = state.pumpCost;
      items.push({ category: 'other', name: `Concrete Pump Truck Rental (Line/Boom)`, cost: pumpCost, qty: 1, unit: 'lump sum', rate: pumpCost });
    }
    detail.pump = { enabled: state.enablePumpRental && state.pumpCost > 0, cost: pumpCost };

    let equipCost = 0;
    if (state.enableEquipmentRental && state.equipmentRentalCost > 0) {
      equipCost = state.equipmentRentalCost;
      items.push({ category: 'other', name: `Vibrator, Power Trowel & Buggy Rental`, cost: equipCost, qty: 1, unit: 'lump sum', rate: equipCost });
    }
    detail.equipment = { enabled: state.enableEquipmentRental && state.equipmentRentalCost > 0, cost: equipCost };

    if (!valid) items.length = 0;

    // SUBTOTAL DIRECT COSTS
    const directSubtotal = items.reduce((sum, it) => sum + it.cost, 0);

    // Category subtotals (identical grouping/logic to updateCostDistributionBar,
    // computed once here so both the cost bar and the PDF read the same numbers).
    const categoryTotals = { material: 0, labor: 0, base: 0, reinforce: 0, forms: 0, other: 0 };
    items.forEach(it => {
      if (categoryTotals[it.category] !== undefined) {
        categoryTotals[it.category] += it.cost;
      } else {
        categoryTotals.other += it.cost;
      }
    });

    // Markup & Contingency
    // NOTE ON ORDER OF OPERATIONS (documented per the audit requirement):
    // markup is applied to the direct-cost subtotal only; contingency is
    // then applied to (direct subtotal + markup), i.e. contingency is
    // calculated AFTER markup, on the marked-up base. This is the
    // calculator's actual, pre-existing behavior -- unchanged by this
    // refactor -- and the PDF's markup/contingency section documents it
    // exactly this way rather than assuming either order.
    const markupCost = directSubtotal * (state.markupPercent / 100);
    const contingencyCost = (directSubtotal + markupCost) * (state.contingencyPercent / 100);
    const totalEstimatedCost = directSubtotal + markupCost + contingencyCost;

    const costPerSqFt = areaSqFt > 0 ? (totalEstimatedCost / areaSqFt) : 0;
    const costPerSqM = areaSqM > 0 ? (totalEstimatedCost / areaSqM) : 0;

    return {
      meta: {
        generatedAt: new Date(),
        unitSystem: state.unitSystem,
        presetKey: activePresetKey,
        presetName: activePresetName,
        // Set by assets/js/calculator-tools.js when a region is picked.
        region: window.SlabCalculatorRegion || null
      },
      inputsSnapshot: JSON.parse(JSON.stringify(state)),
      isImperial: isImp,
      valid: valid,
      dimensions: {
        length: state.length,
        width: state.width,
        thickness: state.thickness,
        quantity: state.quantity,
        wastePercent: state.wastePercent,
        areaSqFt: areaSqFt,
        areaSqM: areaSqM,
        perimeterFt: perimeterFt,
        perimeterM: perimeterM,
        thicknessFt: thicknessFt,
        thicknessM: thicknessM,
        netCubicFt: netCubicFt,
        netCubicYds: netCubicYds,
        netCubicM: netCubicM,
        wasteFactor: wasteFactor,
        orderCubicYds: orderCubicYds,
        orderCubicM: orderCubicM,
        orderCubicFt: orderCubicFt,
        wasteCubicYds: wasteCubicYds,
        wasteCubicM: wasteCubicM,
        weightLbs: weightLbs,
        weightKg: weightKg
      },
      detail: detail,
      items: items,
      categoryTotals: categoryTotals,
      costs: {
        directSubtotal: directSubtotal,
        markupPercent: state.markupPercent,
        markupCost: markupCost,
        contingencyPercent: state.contingencyPercent,
        contingencyCost: contingencyCost,
        totalEstimatedCost: totalEstimatedCost
      },
      unitCosts: {
        costPerSqFt: costPerSqFt,
        costPerSqM: costPerSqM
      },
      bags: {
        bagYieldCuFt: bagYieldCuFt,
        totalBags: totalBags,
        bagSize: state.bagSize,
        bagPrice: state.bagPrice
      }
    };
  }

  /**
   * Paints the on-screen dashboard/BOQ table/cost bar from an already-
   * computed result object. Pulls every figure from `result` -- it never
   * re-derives a number from `state` itself, so this and the PDF generator
   * are guaranteed to display the same values for the same result.
   */
  function updateDOM(result) {
    const d = result.dimensions;
    const c = result.costs;
    const u = result.unitCosts;
    const isImp = result.isImperial;

    if (dom.outTotalCost) dom.outTotalCost.textContent = `$${formatNumber(c.totalEstimatedCost, 2)}`;
    if (dom.outCostPerSqFt) dom.outCostPerSqFt.textContent = `$${formatNumber(u.costPerSqFt, 2)}`;
    if (dom.outCostPerSqM) dom.outCostPerSqM.textContent = `$${formatNumber(u.costPerSqM, 2)}`;

    if (dom.validationMsg) {
      dom.validationMsg.hidden = result.valid;
      dom.validationMsg.textContent = result.valid ? '' : 'Enter a length, width and thickness greater than zero to see an estimate.';
    }

    if (dom.outNetCubicYards) dom.outNetCubicYards.textContent = `Net: ${d.netCubicYds.toFixed(2)} yd³`;
    if (dom.outWasteCubicYards) dom.outWasteCubicYards.textContent = `${d.orderCubicYds.toFixed(2)} yd³`;
    if (dom.outNetCubicMeters) dom.outNetCubicMeters.textContent = `Order volume: ${d.orderCubicM.toFixed(2)} m³`;
    if (dom.outTotalBags) dom.outTotalBags.textContent = `${result.bags.totalBags} bags`;
    if (dom.outBagNote) dom.outBagNote.textContent = `${result.bags.bagSize}-lb pre-mix bags`;
    if (dom.outAreaSqFt) dom.outAreaSqFt.textContent = `${d.areaSqFt.toFixed(0)} sq ft (${d.areaSqM.toFixed(1)} m²)`;
    if (dom.outPerimeterFt) dom.outPerimeterFt.textContent = `${d.perimeterFt.toFixed(0)} ft`;
    if (dom.outTotalWeight) dom.outTotalWeight.textContent = isImp ? `${formatNumber(d.weightLbs, 0)} lbs` : `${formatNumber(d.weightKg, 0)} kg`;

    // Render BOQ Table
    if (dom.boqTableBody) {
      let html = '';
      result.items.forEach(it => {
        html += `
          <tr>
            <td>${it.name}</td>
            <td style="text-align: right; font-weight: 600;">$${formatNumber(it.cost, 2)}</td>
          </tr>
        `;
      });

      if (c.markupPercent > 0) {
        html += `
          <tr>
            <td>Contractor Overhead &amp; Markup (${c.markupPercent}%)</td>
            <td style="text-align: right; font-weight: 600;">$${formatNumber(c.markupCost, 2)}</td>
          </tr>
        `;
      }

      if (c.contingencyPercent > 0) {
        html += `
          <tr>
            <td>Project Contingency Buffer (${c.contingencyPercent}%)</td>
            <td style="text-align: right; font-weight: 600;">$${formatNumber(c.contingencyCost, 2)}</td>
          </tr>
        `;
      }

      html += `
        <tr class="total-row">
          <td><strong>Estimated Total Project Cost</strong></td>
          <td style="text-align: right; color: var(--primary-700);"><strong>$${formatNumber(c.totalEstimatedCost, 2)}</strong></td>
        </tr>
      `;
      dom.boqTableBody.innerHTML = html;
    }

    // Update Cost Bar Visualizer
    updateCostDistributionBar(result.items, c.totalEstimatedCost);
  }

  function recalculate() {
    const result = computeEstimate();
    lastResult = result;
    updateDOM(result);
    // Lets calculator-tools.js redraw the blueprint and re-grade the bid
    // from the same result object the dashboard just painted.
    document.dispatchEvent(new CustomEvent('slabcalc:updated', { detail: result }));
  }

  function updateCostDistributionBar(items, total) {
    if (!dom.segMaterial) return;
    if (total <= 0) {
      [dom.segMaterial, dom.segLabor, dom.segBase, dom.segReinforce, dom.segForms, dom.segOther]
        .forEach(seg => { if (seg) seg.style.width = '0%'; });
      return;
    }

    let catTotals = {
      material: 0,
      labor: 0,
      base: 0,
      reinforce: 0,
      forms: 0,
      other: 0
    };

    items.forEach(it => {
      if (catTotals[it.category] !== undefined) {
        catTotals[it.category] += it.cost;
      } else {
        catTotals.other += it.cost;
      }
    });

    const pMat = (catTotals.material / total) * 100;
    const pLab = (catTotals.labor / total) * 100;
    const pBase = (catTotals.base / total) * 100;
    const pReinf = (catTotals.reinforce / total) * 100;
    const pForm = (catTotals.forms / total) * 100;
    const pOther = 100 - (pMat + pLab + pBase + pReinf + pForm);

    dom.segMaterial.style.width = `${pMat.toFixed(1)}%`;
    dom.segLabor.style.width = `${pLab.toFixed(1)}%`;
    dom.segBase.style.width = `${pBase.toFixed(1)}%`;
    dom.segReinforce.style.width = `${pReinf.toFixed(1)}%`;
    dom.segForms.style.width = `${pForm.toFixed(1)}%`;
    dom.segOther.style.width = `${Math.max(0, pOther).toFixed(1)}%`;
  }

  function formatNumber(num, decimals) {
    return num.toLocaleString('en-US', {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    });
  }

  function resetCalculator() {
    Object.assign(state, DEFAULTS);
    activePresetKey = null;
    activePresetName = null;
    setActivePreset(null);

    updateUnitLabels();
    syncAllFieldsFromState();
    recalculate();
    showToast('Calculator has been reset to defaults.');
  }

  // Blocks copy/export/PDF while the dimensions can't make a slab.
  function ensureValid() {
    const r = lastResult || computeEstimate();
    if (r.valid) return true;
    showToast('Enter a length, width and thickness greater than zero first.');
    return false;
  }

  function copySummaryToClipboard() {
    if (!ensureValid()) return;
    const total = dom.outTotalCost ? dom.outTotalCost.textContent : '$0';
    const rate = dom.outCostPerSqFt ? dom.outCostPerSqFt.textContent : '$0';
    const yds = dom.outWasteCubicYards ? dom.outWasteCubicYards.textContent : '0';
    const bags = dom.outTotalBags ? dom.outTotalBags.textContent : '0';

    const text = `CONCRETE SLAB ESTIMATE SUMMARY\n` +
      `------------------------------------\n` +
      `Dimensions: ${state.length} x ${state.width} ${state.unitSystem === 'imperial' ? 'ft' : 'm'} @ ${state.thickness} ${state.unitSystem === 'imperial' ? 'in' : 'cm'} thick\n` +
      `Area: ${dom.outAreaSqFt.textContent}\n` +
      `Order Volume (incl. ${state.wastePercent}% waste): ${yds} (~${bags})\n` +
      `Estimated Total Project Cost: ${total}\n` +
      `Estimated Rate: ${rate}/sq ft\n` +
      `Calculated with Slab Cost Calculator (https://slabcostcalculator.com)`;

    navigator.clipboard.writeText(text).then(() => {
      showToast('Estimate summary copied to clipboard!');
    }).catch(() => {
      showToast('Error copying to clipboard.');
    });
  }

  function exportEstimateFile() {
    if (!ensureValid()) return;
    const data = {
      project: 'Concrete Slab Project Estimate',
      date: new Date().toISOString().split('T')[0],
      inputs: {
        length: state.length,
        width: state.width,
        thickness: state.thickness,
        unitSystem: state.unitSystem,
        wastePercent: state.wastePercent,
        reinforcement: state.reinforcementType,
        finish: state.finishType,
        laborMode: state.laborMode
      },
      results: {
        totalCost: dom.outTotalCost.textContent,
        costPerSqFt: dom.outCostPerSqFt.textContent,
        orderCubicYards: dom.outWasteCubicYards.textContent,
        orderBags: dom.outTotalBags.textContent
      }
    };

    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `concrete-slab-estimate-${state.length}x${state.width}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    showToast('Estimate exported as JSON file!');
  }

  function showToast(msg) {
    if (!dom.toast) return;
    dom.toast.textContent = msg;
    dom.toast.style.display = 'block';
    setTimeout(() => {
      dom.toast.style.display = 'none';
    }, 3500);
  }

  // Public engine API consumed by assets/js/pdf-report.js. Deliberately
  // minimal: the PDF generator only ever needs the latest structured
  // result (or the ability to compute one fresh, e.g. if it runs before
  // the first recalculate() -- shouldn't happen since recalculate() runs
  // on DOMContentLoaded, but this keeps the contract safe either way).
  window.SlabCalculatorEngine = {
    getLastResult: function () {
      return lastResult;
    },
    computeEstimate: computeEstimate
  };

  // Initialize on DOM Ready
  document.addEventListener('DOMContentLoaded', () => {
    if (initDOM()) {
      bindEvents();
      updateUnitLabels();
      recalculate();
    }
  });

})();
