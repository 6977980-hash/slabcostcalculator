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

  // State Management
  const state = {
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
    readyMixPricePerM3: 160.00,

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
    rebarCostPerSqFt: 1.25,
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
      presetBtns: root.querySelectorAll('.preset-btn'),

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
          dom.presetBtns.forEach(b => b.classList.remove('active'));
          this.classList.add('active');
        });
      });
    }

    // Accordion Headers Toggle
    const accordions = dom.root.querySelectorAll('.scope-accordion');
    accordions.forEach(acc => {
      const header = acc.querySelector('.scope-header');
      if (header) {
        header.addEventListener('click', function (e) {
          if (e.target.tagName.toLowerCase() === 'input') return; // don't toggle if clicking checkbox directly
          acc.classList.toggle('open');
        });
      }
    });

    // Material Dropdown Toggle
    if (dom.selectMaterialType) {
      dom.selectMaterialType.addEventListener('change', function () {
        state.materialType = this.value;
        if (state.materialType === 'readymix') {
          dom.rowReadyMix.style.display = 'block';
          dom.rowBagged.style.display = 'none';
        } else {
          dom.rowReadyMix.style.display = 'none';
          dom.rowBagged.style.display = 'block';
        }
        recalculate();
      });
    }

    // Labor Mode Dropdown Toggle
    if (dom.selectLaborMode) {
      dom.selectLaborMode.addEventListener('change', function () {
        state.laborMode = this.value;
        dom.rowLaborSqft.style.display = state.laborMode === 'sqft' ? 'block' : 'none';
        dom.rowLaborHourly.style.display = state.laborMode === 'hourly' ? 'grid' : 'none';
        dom.rowLaborFlat.style.display = state.laborMode === 'flat' ? 'block' : 'none';
        recalculate();
      });
    }

    // All form input changes trigger recalculate
    const inputs = dom.root.querySelectorAll('input, select');
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
        if (window.SlabPDFReport && typeof window.SlabPDFReport.generate === 'function') {
          window.SlabPDFReport.generate(lastResult || computeEstimate());
        } else {
          window.print();
        }
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
      state.unitSystem = 'metric';
    } else {
      // Convert Metric to Imperial
      state.length = parseFloat((state.length / FEET_TO_METERS).toFixed(2));
      state.width = parseFloat((state.width / FEET_TO_METERS).toFixed(2));
      state.thickness = parseFloat((state.thickness / 2.54).toFixed(1)); // cm -> in
      state.unitSystem = 'imperial';
    }

    // Update labels and placeholders in DOM
    updateUnitLabels();
    syncDOMFromState();
    recalculate();
  }

  function updateUnitLabels() {
    dom.unitBtns.forEach(btn => {
      btn.classList.toggle('active', btn.getAttribute('data-unit') === state.unitSystem);
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

    state.length = parseFloat(dom.inputLength.value) || 0;
    state.width = parseFloat(dom.inputWidth.value) || 0;
    state.thickness = parseFloat(dom.inputThickness.value) || 0;
    state.quantity = parseInt(dom.inputQuantity.value, 10) || 1;
    state.wastePercent = parseFloat(dom.inputWaste.value) || 0;

    // Materials
    state.materialType = dom.selectMaterialType ? dom.selectMaterialType.value : 'readymix';
    if (dom.inputReadyMixPrice) state.readyMixPricePerYard = parseFloat(dom.inputReadyMixPrice.value) || 0;
    if (dom.selectBagSize) state.bagSize = dom.selectBagSize.value;
    if (dom.inputBagPrice) state.bagPrice = parseFloat(dom.inputBagPrice.value) || 0;

    // Site Prep
    if (dom.chkSitePrep) state.enableSitePrep = dom.chkSitePrep.checked;
    if (dom.inputExcavationCost) state.excavationCostPerSqFt = parseFloat(dom.inputExcavationCost.value) || 0;
    if (dom.inputGravelDepth) state.gravelDepthInches = parseFloat(dom.inputGravelDepth.value) || 0;
    if (dom.inputGravelPrice) state.gravelPricePerYard = parseFloat(dom.inputGravelPrice.value) || 0;
    if (dom.chkForms) state.enableForms = dom.chkForms.checked;
    if (dom.inputFormCost) state.formCostPerLinearFt = parseFloat(dom.inputFormCost.value) || 0;
    if (dom.chkVaporBarrier) state.enableVaporBarrier = dom.chkVaporBarrier.checked;
    if (dom.inputVaporCost) state.vaporBarrierCostPerSqFt = parseFloat(dom.inputVaporCost.value) || 0;
    if (dom.chkDemo) state.enableDemoRemoval = dom.chkDemo.checked;
    if (dom.inputDemoCost) state.demoCostPerSqFt = parseFloat(dom.inputDemoCost.value) || 0;

    // Reinforce
    if (dom.selectReinforcement) state.reinforcementType = dom.selectReinforcement.value;
    if (dom.inputReinforceCost) state.customReinforceCost = parseFloat(dom.inputReinforceCost.value) || 0;

    // Finish
    if (dom.selectFinish) state.finishType = dom.selectFinish.value;
    if (dom.inputFinishCost) state.finishCostPerSqFt = parseFloat(dom.inputFinishCost.value) || 0;

    // Labor
    if (dom.selectLaborMode) state.laborMode = dom.selectLaborMode.value;
    if (dom.inputLaborSqft) state.laborRatePerSqFt = parseFloat(dom.inputLaborSqft.value) || 0;
    if (dom.inputLaborHourlyRate) state.laborHourlyRate = parseFloat(dom.inputLaborHourlyRate.value) || 0;
    if (dom.inputLaborHours) state.laborEstimatedHours = parseFloat(dom.inputLaborHours.value) || 0;
    if (dom.inputLaborFlat) state.laborFlatCost = parseFloat(dom.inputLaborFlat.value) || 0;

    // Delivery / Equip
    if (dom.chkDelivery) state.enableDeliveryFee = dom.chkDelivery.checked;
    if (dom.inputDeliveryFee) state.deliveryFee = parseFloat(dom.inputDeliveryFee.value) || 0;
    if (dom.chkPump) state.enablePumpRental = dom.chkPump.checked;
    if (dom.inputPumpCost) state.pumpCost = parseFloat(dom.inputPumpCost.value) || 0;
    if (dom.chkEquipment) state.enableEquipmentRental = dom.chkEquipment.checked;
    if (dom.inputEquipmentCost) state.equipmentRentalCost = parseFloat(dom.inputEquipmentCost.value) || 0;

    // Markup
    if (dom.inputMarkup) state.markupPercent = parseFloat(dom.inputMarkup.value) || 0;
    if (dom.inputContingency) state.contingencyPercent = parseFloat(dom.inputContingency.value) || 0;
  }

  function syncDOMFromState() {
    if (dom.inputLength) dom.inputLength.value = state.length;
    if (dom.inputWidth) dom.inputWidth.value = state.width;
    if (dom.inputThickness) dom.inputThickness.value = state.thickness;
    if (dom.inputQuantity) dom.inputQuantity.value = state.quantity;
    if (dom.inputWaste) dom.inputWaste.value = state.wastePercent;
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
    const totalBags = Math.ceil(orderCubicFt / bagYieldCuFt);

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
          name: `Compacted Gravel Sub-Base (${state.gravelDepthInches}" depth, ${gravelYards.toFixed(1)} yd³)`,
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
      reinforceCost = areaSqFt * state.rebarCostPerSqFt;
      reinforceLabel = '#4 (1/2") Steel Rebar Grid & Support Chairs';
      reinforceBasis = { qty: areaSqFt, unit: 'sq ft', rate: state.rebarCostPerSqFt };
      items.push({ category: 'reinforce', name: reinforceLabel, cost: reinforceCost, qty: areaSqFt, unit: 'sq ft', rate: state.rebarCostPerSqFt });
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
    let deliveryCost = 0;
    if (state.enableDeliveryFee && state.deliveryFee > 0) {
      deliveryCost = state.deliveryFee;
      items.push({ category: 'other', name: `Ready-Mix Truck Delivery / Short-Load Fee`, cost: deliveryCost, qty: 1, unit: 'lump sum', rate: deliveryCost });
    }
    detail.delivery = { enabled: state.enableDeliveryFee && state.deliveryFee > 0, cost: deliveryCost };

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
        presetName: activePresetName
      },
      inputsSnapshot: JSON.parse(JSON.stringify(state)),
      isImperial: isImp,
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

    if (dom.outNetCubicYards) dom.outNetCubicYards.textContent = `${d.netCubicYds.toFixed(2)} yd³`;
    if (dom.outWasteCubicYards) dom.outWasteCubicYards.textContent = `${d.orderCubicYds.toFixed(2)} yd³`;
    if (dom.outNetCubicMeters) dom.outNetCubicMeters.textContent = `${d.orderCubicM.toFixed(2)} m³`;
    if (dom.outTotalBags) dom.outTotalBags.textContent = `${result.bags.totalBags} bags`;
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
  }

  function updateCostDistributionBar(items, total) {
    if (!dom.segMaterial || total <= 0) return;

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
    state.length = 20;
    state.width = 20;
    state.thickness = 4;
    state.quantity = 1;
    state.wastePercent = 10;
    state.materialType = 'readymix';
    state.bagSize = '80';
    state.enableSitePrep = false;
    state.enableForms = true;
    state.enableVaporBarrier = true;
    state.enableDemoRemoval = false;
    state.reinforcementType = 'rebar';
    state.finishType = 'broom';
    state.laborMode = 'sqft';
    state.enableDeliveryFee = true;
    state.enablePumpRental = false;
    state.enableEquipmentRental = false;
    state.markupPercent = 0;
    state.contingencyPercent = 5;
    activePresetKey = null;
    activePresetName = null;

    syncDOMFromState();
    if (dom.selectMaterialType) dom.selectMaterialType.value = 'readymix';
    if (dom.rowReadyMix) dom.rowReadyMix.style.display = 'block';
    if (dom.rowBagged) dom.rowBagged.style.display = 'none';
    if (dom.selectLaborMode) dom.selectLaborMode.value = 'sqft';
    if (dom.rowLaborSqft) dom.rowLaborSqft.style.display = 'block';
    if (dom.rowLaborHourly) dom.rowLaborHourly.style.display = 'none';
    if (dom.rowLaborFlat) dom.rowLaborFlat.style.display = 'none';
    if (dom.chkSitePrep) dom.chkSitePrep.checked = false;
    if (dom.chkForms) dom.chkForms.checked = true;
    if (dom.chkVaporBarrier) dom.chkVaporBarrier.checked = true;
    if (dom.chkDemo) dom.chkDemo.checked = false;
    if (dom.selectReinforcement) dom.selectReinforcement.value = 'rebar';
    if (dom.selectFinish) dom.selectFinish.value = 'broom';
    if (dom.chkDelivery) dom.chkDelivery.checked = true;
    if (dom.chkPump) dom.chkPump.checked = false;
    if (dom.chkEquipment) dom.chkEquipment.checked = false;

    recalculate();
    showToast('Calculator has been reset to defaults.');
  }

  function copySummaryToClipboard() {
    const total = dom.outTotalCost ? dom.outTotalCost.textContent : '$0';
    const rate = dom.outCostPerSqFt ? dom.outCostPerSqFt.textContent : '$0';
    const yds = dom.outWasteCubicYards ? dom.outWasteCubicYards.textContent : '0';
    const bags = dom.outTotalBags ? dom.outTotalBags.textContent : '0';

    const text = `CONCRETE SLAB ESTIMATE SUMMARY\n` +
      `------------------------------------\n` +
      `Dimensions: ${state.length} x ${state.width} @ ${state.thickness}" (${state.unitSystem})\n` +
      `Area: ${dom.outAreaSqFt.textContent}\n` +
      `Order Volume (incl. ${state.wastePercent}% waste): ${yds} (~${bags})\n` +
      `Estimated Total Project Cost: ${total}\n` +
      `Estimated Rate: ${rate}/sq ft\n` +
      `Calculated with Slab Cost Calculator (https://slabcostcalculator.org)`;

    navigator.clipboard.writeText(text).then(() => {
      showToast('Estimate summary copied to clipboard!');
    }).catch(() => {
      showToast('Error copying to clipboard.');
    });
  }

  function exportEstimateFile() {
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
