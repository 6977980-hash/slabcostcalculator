/**
 * CONCRETE SLAB COST ESTIMATE & CALCULATION REPORT (PDF)
 *
 * Generates a real, structured, multi-page PDF report directly from the
 * calculator's own structured calculation result (see computeEstimate() in
 * primary-calculator.js). This is NOT a screenshot, NOT a browser-print of
 * the page, and NOT html2canvas -- every number, table, and formula in this
 * report is drawn as vector text/tables from the shared calculation-result
 * object, which is the exact same object the on-screen dashboard and BOQ
 * table are painted from (see updateDOM() in primary-calculator.js). There
 * is one calculation engine; this file only formats its output as a PDF.
 *
 * Requires assets/js/vendor/jspdf.umd.min.js and
 * assets/js/vendor/jspdf.plugin.autotable.min.js, which are loaded on
 * demand by load() (URLs from window.SlabPDFConfig, set in functions.php)
 * so that window.jspdf.jsPDF and the doc.autoTable() plugin method are
 * available before generate() runs.
 */

(function () {
  'use strict';

  // ---------------------------------------------------------------------
  // Layout constants
  // ---------------------------------------------------------------------
  const PAGE = { width: 612, height: 792 }; // US Letter, points (72pt/in)
  const MARGIN = { left: 42, right: 42, top: 54, bottom: 50 };
  const CONTENT_WIDTH = PAGE.width - MARGIN.left - MARGIN.right;

  const COLOR = {
    ink: [26, 32, 44],          // near-black body text
    heading: [15, 42, 74],      // dark blue section headings
    accent: [37, 99, 235],      // brand blue (matches site --primary-600 family)
    accentSoft: [219, 234, 254],// light blue fill
    grayLine: [203, 213, 225],
    grayText: [100, 116, 139],
    tableHeadBg: [15, 42, 74],
    tableHeadText: [255, 255, 255],
    tableAltRow: [246, 248, 251],
    good: [5, 122, 85],
    warnBg: [254, 249, 231]
  };

  const CATEGORY_LABELS = {
    material: 'Concrete Material',
    labor: 'Labor',
    base: 'Site Preparation & Base',
    reinforce: 'Reinforcement',
    forms: 'Formwork',
    other: 'Delivery, Equipment, Finishing & Other'
  };

  // ---------------------------------------------------------------------
  // Formatting helpers
  // ---------------------------------------------------------------------
  function money(n) {
    const v = (typeof n === 'number' && isFinite(n)) ? n : 0;
    return '$' + v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function num(n, decimals) {
    const v = (typeof n === 'number' && isFinite(n)) ? n : 0;
    return v.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
  }
  function pct(n) {
    return (typeof n === 'number' && isFinite(n) ? n : 0) + '%';
  }
  function makeReportId(result) {
    const d = result.meta.generatedAt;
    const stamp = [
      d.getFullYear(),
      String(d.getMonth() + 1).padStart(2, '0'),
      String(d.getDate()).padStart(2, '0'),
      '-',
      String(d.getHours()).padStart(2, '0'),
      String(d.getMinutes()).padStart(2, '0'),
      String(d.getSeconds()).padStart(2, '0')
    ].join('');
    const rand = Math.random().toString(36).slice(2, 6).toUpperCase();
    return `SC-${stamp}-${rand}`;
  }
  function safeFileSlug(s) {
    return String(s).replace(/[^a-z0-9-]+/gi, '-');
  }

  // GLYPH-SAFETY FIX: jsPDF's built-in standard-14 fonts (Helvetica etc.)
  // do not carry glyphs for the superscript "²"/"³" characters --
  // "sq m"/"cu yd"/"cu m" render fine, but "m²"/"yd³"/"m³"
  // silently drop that character and leave a gap (confirmed by rendering
  // and visually inspecting a test PDF). Every other unit label in this
  // report is composed locally as plain ASCII, but the calculation
  // engine's own item names/units (assets/js/primary-calculator.js) use
  // real Unicode "yd³"/"m³"/"m²" for the on-screen BOQ table,
  // which is fine there (browsers render Unicode natively) and is left
  // unchanged -- these two helpers just translate that same text to an
  // ASCII-safe equivalent at the point it is drawn into the PDF, so the
  // report and the on-screen calculator can keep using the exact same
  // underlying item data without the PDF inheriting a font limitation.
  function sanitizeLabel(s) {
    if (typeof s !== 'string') return s;
    return s.replace(/yd³/g, 'cu yd').replace(/m³/g, 'cu m').replace(/m²/g, 'sq m');
  }
  function asciiUnit(u) {
    if (u === 'yd³') return 'cu yd';
    if (u === 'm³') return 'cu m';
    if (u === 'm²') return 'sq m';
    return u;
  }

  // ---------------------------------------------------------------------
  // Layout / page-flow helpers
  // ---------------------------------------------------------------------
  function newCtx(doc, reportId, result) {
    return { doc: doc, y: MARGIN.top, reportId: reportId, result: result, pageInSection: 1 };
  }

  function ensureSpace(ctx, needed) {
    if (ctx.y + needed > PAGE.height - MARGIN.bottom) {
      ctx.doc.addPage();
      ctx.y = MARGIN.top;
    }
  }

  function sectionHeading(ctx, title) {
    ensureSpace(ctx, 34);
    const doc = ctx.doc;
    doc.setFillColor.apply(doc, COLOR.accentSoft);
    doc.rect(MARGIN.left, ctx.y, CONTENT_WIDTH, 22, 'F');
    doc.setTextColor.apply(doc, COLOR.heading);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12.5);
    doc.text(title, MARGIN.left + 8, ctx.y + 15);
    ctx.y += 22 + 10;
    doc.setTextColor.apply(doc, COLOR.ink);
  }

  function subHeading(ctx, title) {
    ensureSpace(ctx, 20);
    const doc = ctx.doc;
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(10.5);
    doc.setTextColor.apply(doc, COLOR.heading);
    doc.text(title, MARGIN.left, ctx.y);
    ctx.y += 14;
    doc.setTextColor.apply(doc, COLOR.ink);
    doc.setFont('helvetica', 'normal');
  }

  function paragraph(ctx, text, opts) {
    opts = opts || {};
    const doc = ctx.doc;
    doc.setFont('helvetica', opts.bold ? 'bold' : 'normal');
    doc.setFontSize(opts.size || 9);
    doc.setTextColor.apply(doc, opts.color || COLOR.ink);
    const width = opts.width || CONTENT_WIDTH;
    const lines = doc.splitTextToSize(text, width);
    ensureSpace(ctx, lines.length * (opts.lineHeight || 12) + 2);
    doc.text(lines, MARGIN.left, ctx.y);
    ctx.y += lines.length * (opts.lineHeight || 12) + (opts.gapAfter != null ? opts.gapAfter : 6);
  }

  // A single "math step" line: label, then an indented formula/substitution
  // line, then a bold indented result line. Used throughout Section 3.
  function calcStep(ctx, label, formulaLine, resultLine) {
    const doc = ctx.doc;
    ensureSpace(ctx, 42);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(9.5);
    doc.setTextColor.apply(doc, COLOR.heading);
    doc.text(label, MARGIN.left, ctx.y);
    ctx.y += 12;

    doc.setFont('courier', 'normal');
    doc.setFontSize(9);
    doc.setTextColor.apply(doc, COLOR.grayText);
    doc.text(formulaLine, MARGIN.left + 12, ctx.y);
    ctx.y += 12;

    doc.setFont('courier', 'bold');
    doc.setFontSize(9.5);
    doc.setTextColor.apply(doc, COLOR.good);
    doc.text('= ' + resultLine, MARGIN.left + 12, ctx.y);
    ctx.y += 16;

    doc.setTextColor.apply(doc, COLOR.ink);
    doc.setFont('helvetica', 'normal');
  }

  // Two-column "label / value" table with no header row -- used for the
  // Project Inputs groups (Section 2) and several Material Quantity blocks
  // (Section 4). `rows` = [[label, value], ...].
  function kvTable(ctx, rows) {
    ctx.doc.autoTable({
      startY: ctx.y,
      body: rows,
      theme: 'plain',
      styles: { fontSize: 9, cellPadding: { top: 2.5, bottom: 2.5, left: 2, right: 2 }, textColor: COLOR.ink },
      columnStyles: {
        0: { cellWidth: CONTENT_WIDTH * 0.52, fontStyle: 'normal', textColor: COLOR.grayText },
        1: { cellWidth: CONTENT_WIDTH * 0.48, fontStyle: 'bold', textColor: COLOR.ink }
      },
      margin: { left: MARGIN.left, right: MARGIN.right },
      didParseCell: function (data) {
        data.cell.styles.lineWidth = 0;
      },
      alternateRowStyles: { fillColor: COLOR.tableAltRow }
    });
    ctx.y = ctx.doc.lastAutoTable.finalY + 10;
  }

  // Full-width data table with a styled header row.
  function dataTable(ctx, head, body, columnStyles, opts) {
    opts = opts || {};
    ctx.doc.autoTable({
      startY: ctx.y,
      head: [head],
      body: body,
      theme: 'grid',
      styles: { fontSize: 8.5, cellPadding: 4, textColor: COLOR.ink, lineColor: COLOR.grayLine, lineWidth: 0.5 },
      headStyles: { fillColor: COLOR.tableHeadBg, textColor: COLOR.tableHeadText, fontStyle: 'bold', fontSize: 8.5 },
      alternateRowStyles: { fillColor: COLOR.tableAltRow },
      columnStyles: columnStyles || {},
      margin: { left: MARGIN.left, right: MARGIN.right },
      foot: opts.foot,
      footStyles: { fillColor: COLOR.accentSoft, textColor: COLOR.heading, fontStyle: 'bold', fontSize: 9 }
    });
    ctx.y = ctx.doc.lastAutoTable.finalY + 12;
  }

  function hr(ctx) {
    ensureSpace(ctx, 10);
    ctx.doc.setDrawColor.apply(ctx.doc, COLOR.grayLine);
    ctx.doc.setLineWidth(0.75);
    ctx.doc.line(MARGIN.left, ctx.y, PAGE.width - MARGIN.right, ctx.y);
    ctx.y += 12;
  }

  // ---------------------------------------------------------------------
  // PAGE 1 -- COVER / EXECUTIVE SUMMARY
  // ---------------------------------------------------------------------
  function renderCover(ctx) {
    const doc = ctx.doc;
    const r = ctx.result;

    doc.setFillColor.apply(doc, COLOR.heading);
    doc.rect(0, 0, PAGE.width, 92, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(19);
    doc.text('Concrete Slab Cost Estimate', MARGIN.left, 40);
    doc.text('& Calculation Report', MARGIN.left, 62);
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.text('SlabCostCalculator.com', MARGIN.left, 80);

    ctx.y = 112;
    doc.setTextColor.apply(doc, COLOR.ink);

    const genDate = r.meta.generatedAt;
    const metaRows = [
      ['Report ID', ctx.reportId],
      ['Generated', genDate.toLocaleString('en-US', { dateStyle: 'long', timeStyle: 'short' })],
      ['Calculator', 'Slab Cost Calculator -- Concrete Slab Cost & Volume Estimator'],
      ['Unit System', r.isImperial ? 'Imperial (ft / in / cu yd)' : 'Metric (m / cm / cu m)'],
      ['Project Type / Preset', r.meta.presetName || 'Custom Project (manually specified dimensions)']
    ];
    if (r.meta.region && r.meta.region.index !== 1) {
      metaRows.push(['Regional Cost Index', `${r.meta.region.name} (x${r.meta.region.index.toFixed(2)} on ready-mix, excavation & labor)`]);
    }
    kvTable(ctx, metaRows);

    ctx.y += 6;
    ensureSpace(ctx, 118);
    doc.setFillColor.apply(doc, COLOR.accentSoft);
    doc.roundedRect(MARGIN.left, ctx.y, CONTENT_WIDTH, 110, 6, 6, 'F');

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(10);
    doc.setTextColor.apply(doc, COLOR.grayText);
    doc.text('FINAL ESTIMATED PROJECT COST', MARGIN.left + 16, ctx.y + 24);

    doc.setFontSize(30);
    doc.setTextColor.apply(doc, COLOR.heading);
    doc.text(money(r.costs.totalEstimatedCost), MARGIN.left + 16, ctx.y + 56);

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10.5);
    doc.setTextColor.apply(doc, COLOR.ink);
    doc.text(`${money(r.unitCosts.costPerSqFt)} / sq. ft.`, MARGIN.left + 16, ctx.y + 80);
    doc.text(`${money(r.unitCosts.costPerSqM)} / sq m`, MARGIN.left + 200, ctx.y + 80);
    doc.setFontSize(9);
    doc.setTextColor.apply(doc, COLOR.grayText);
    doc.text(
      `${num(r.dimensions.areaSqFt, 0)} sq ft total area  •  ${num(r.dimensions.orderCubicYds, 2)} cu yd order volume`,
      MARGIN.left + 16, ctx.y + 98
    );

    ctx.y += 110 + 18;
    doc.setTextColor.apply(doc, COLOR.ink);
  }

  // ---------------------------------------------------------------------
  // SECTION 2 -- PROJECT INPUTS
  // ---------------------------------------------------------------------
  function renderInputs(ctx) {
    const r = ctx.result;
    const d = r.dimensions;
    const det = r.detail;
    const imp = r.isImperial;
    const lenUnit = imp ? 'ft' : 'm';
    const thickUnit = imp ? 'in' : 'cm';

    sectionHeading(ctx, '2. Project Inputs');

    subHeading(ctx, 'Slab Dimensions');
    kvTable(ctx, [
      ['Length', `${num(d.length, 2)} ${lenUnit}`],
      ['Width', `${num(d.width, 2)} ${lenUnit}`],
      ['Thickness', `${num(d.thickness, 2)} ${thickUnit}`],
      ['Quantity (number of slabs)', `${d.quantity}`],
      ['Waste Allowance', pct(d.wastePercent)]
    ]);

    subHeading(ctx, 'Concrete Material');
    const matRows = [['Concrete Source', det.concrete.materialType === 'readymix' ? 'Ready-Mix (Delivered by Truck)' : 'Bagged Concrete (Pre-mixed)']];
    if (det.concrete.materialType === 'readymix') {
      matRows.push(['Ready-Mix Price', imp ? `${money(r.inputsSnapshot.readyMixPricePerYard)} / cu yd` : `${money(r.inputsSnapshot.readyMixPricePerM3)} / cu m`]);
    } else {
      matRows.push(['Bag Size', `${det.concrete.bagSize} lb (${num(det.concrete.bagYieldCuFt, 3)} cu ft yield)`]);
      matRows.push(['Price Per Bag', money(det.concrete.bagPrice)]);
    }
    kvTable(ctx, matRows);

    subHeading(ctx, 'Site Preparation & Gravel');
    if (det.excavation.enabled) {
      kvTable(ctx, [
        ['Excavation / Grading Rate', `${money(det.excavation.rate)} / sq ft`],
        ['Gravel Sub-Base Depth', det.gravel.enabled ? `${num(det.gravel.depthInches, 1)} in` : 'Not Included'],
        ['Crushed Gravel Price', det.gravel.enabled ? `${money(det.gravel.rate)} / cu yd` : 'Not Included']
      ]);
    } else {
      kvTable(ctx, [['Site Prep, Excavation & Gravel Base', 'Not Included']]);
    }

    subHeading(ctx, 'Formwork & Moisture Protection');
    kvTable(ctx, [
      ['Formwork', det.forms.enabled ? `Included @ ${money(det.forms.rate)} / linear ft` : 'Not Included'],
      ['Vapor Barrier', det.vaporBarrier.enabled ? `Included @ ${money(det.vaporBarrier.rate)} / sq ft` : 'Not Included']
    ]);

    subHeading(ctx, 'Reinforcement');
    const reinfRows = [['Reinforcement Type', det.reinforcement.label]];
    if (det.reinforcement.type === 'custom') {
      reinfRows.push(['Custom Reinforcement Cost Override', money(det.reinforcement.cost)]);
    }
    kvTable(ctx, reinfRows);

    subHeading(ctx, 'Surface Finishing');
    const finRows = [['Finish Type', det.finishing.label]];
    if (det.finishing.type === 'custom') {
      finRows.push(['Custom Finish Rate', `${money(det.finishing.rate)} / sq ft`]);
    }
    kvTable(ctx, finRows);

    subHeading(ctx, 'Labor');
    const lab = det.labor;
    const laborRows = [['Labor Estimating Method', laborMethodDisplayName(lab.mode)]];
    if (lab.mode === 'sqft') laborRows.push(['Labor Rate / Sq Ft', `${money(lab.ratePerSqFt)} / sq ft`]);
    if (lab.mode === 'hourly') {
      laborRows.push(['Crew Hourly Rate', `${money(lab.hourlyRate)} / hr`]);
      laborRows.push(['Estimated Crew Hours', `${num(lab.hours, 1)} hrs`]);
    }
    if (lab.mode === 'flat') laborRows.push(['Flat Contractor Labor Quote', money(lab.flatCost)]);
    if (lab.mode === 'diy') laborRows.push(['Labor Cost', 'Not Included (self-performed)']);
    kvTable(ctx, laborRows);

    subHeading(ctx, 'Delivery / Equipment');
    kvTable(ctx, [
      ['Delivery / Short-Load Charge', det.delivery.enabled ? money(det.delivery.cost) : 'Not Included'],
      ['Concrete Pump Charge', det.pump.enabled ? money(det.pump.cost) : 'Not Included'],
      ['Equipment Rental Charge', det.equipment.enabled ? money(det.equipment.cost) : 'Not Included']
    ]);

    subHeading(ctx, 'Demolition');
    kvTable(ctx, [
      ['Demolition / Removal Rate', det.demolition.enabled ? `${money(det.demolition.rate)} / sq ft` : 'Not Included']
    ]);

    subHeading(ctx, 'Project Adjustments');
    kvTable(ctx, [
      ['Contractor Markup', pct(r.costs.markupPercent)],
      ['Contingency Buffer', pct(r.costs.contingencyPercent)]
    ]);
  }

  function laborMethodDisplayName(mode) {
    switch (mode) {
      case 'sqft': return 'Per Square Foot (Standard)';
      case 'hourly': return 'Hourly Crew Rate';
      case 'flat': return 'Fixed Contractor Quote';
      case 'diy': return 'DIY / Self-Performed';
      default: return mode;
    }
  }

  // ---------------------------------------------------------------------
  // SECTION 3 -- SLAB DIMENSION CALCULATIONS (step-by-step)
  // ---------------------------------------------------------------------
  function renderDimensionCalcs(ctx) {
    const r = ctx.result;
    const d = r.dimensions;
    const imp = r.isImperial;

    sectionHeading(ctx, '3. Slab Dimension Calculations');

    const qtyNote = d.quantity > 1 ? ` × ${d.quantity} slabs` : '';
    if (imp) {
      calcStep(ctx, 'Surface Area', `${num(d.length, 2)} ft × ${num(d.width, 2)} ft${qtyNote}`, `${num(d.areaSqFt, 2)} sq ft  (${num(d.areaSqM, 2)} sq m)`);
      calcStep(ctx, 'Perimeter (for formwork)', `2 × (${num(d.length, 2)} ft + ${num(d.width, 2)} ft)${qtyNote}`, `${num(d.perimeterFt, 2)} ft`);
      calcStep(ctx, 'Thickness Conversion', `${num(d.thickness, 2)} in ÷ 12`, `${num(d.thicknessFt, 4)} ft`);
      calcStep(ctx, 'Net Concrete Volume', `${num(d.areaSqFt, 2)} sq ft × ${num(d.thicknessFt, 4)} ft`, `${num(d.netCubicFt, 2)} cu ft`);
      calcStep(ctx, 'Cubic Yard Conversion', `${num(d.netCubicFt, 2)} cu ft ÷ 27`, `${num(d.netCubicYds, 4)} cu yd`);
    } else {
      calcStep(ctx, 'Surface Area', `${num(d.length, 2)} m × ${num(d.width, 2)} m${qtyNote}`, `${num(d.areaSqM, 2)} sq m  (${num(d.areaSqFt, 2)} sq ft)`);
      calcStep(ctx, 'Perimeter (for formwork)', `2 × (${num(d.length, 2)} m + ${num(d.width, 2)} m)${qtyNote}`, `${num(d.perimeterM, 2)} m`);
      calcStep(ctx, 'Thickness Conversion', `${num(d.thickness, 2)} cm ÷ 100`, `${num(d.thicknessM, 4)} m`);
      calcStep(ctx, 'Net Concrete Volume', `${num(d.areaSqM, 2)} sq m × ${num(d.thicknessM, 4)} m`, `${num(d.netCubicM, 4)} cu m  (${num(d.netCubicFt, 2)} cu ft)`);
      calcStep(ctx, 'Cubic Yard Reference', `${num(d.netCubicFt, 2)} cu ft ÷ 27`, `${num(d.netCubicYds, 4)} cu yd (reference)`);
    }

    calcStep(ctx, `Waste Calculation (${pct(d.wastePercent)} allowance)`,
      imp ? `${num(d.netCubicYds, 4)} cu yd × ${pct(d.wastePercent)}` : `${num(d.netCubicM, 4)} cu m × ${pct(d.wastePercent)}`,
      imp ? `${num(d.wasteCubicYds, 4)} cu yd waste` : `${num(d.wasteCubicM, 4)} cu m waste`
    );

    calcStep(ctx, 'Order Quantity (net + waste)',
      imp ? `${num(d.netCubicYds, 4)} + ${num(d.wasteCubicYds, 4)}` : `${num(d.netCubicM, 4)} + ${num(d.wasteCubicM, 4)}`,
      imp ? `${num(d.orderCubicYds, 4)} cu yd` : `${num(d.orderCubicM, 4)} cu m  (${num(d.orderCubicYds, 4)} cu yd reference)`
    );

    calcStep(ctx, 'Estimated Wet Weight',
      imp ? `${num(d.orderCubicFt, 2)} cu ft × 145 lbs/cu ft` : `${num(d.orderCubicM, 4)} cu m × 2,322 kg/cu m`,
      imp ? `${num(d.weightLbs, 0)} lbs` : `${num(d.weightKg, 0)} kg`
    );
  }

  // ---------------------------------------------------------------------
  // SECTION 4 -- MATERIAL QUANTITY CALCULATIONS
  // ---------------------------------------------------------------------
  function renderMaterialQuantities(ctx) {
    const r = ctx.result;
    const det = r.detail;
    const imp = r.isImperial;

    sectionHeading(ctx, '4. Material Quantity Calculations');

    subHeading(ctx, 'Concrete');
    const c = det.concrete;
    const concreteRows = [
      ['Net Volume', imp ? `${num(r.dimensions.netCubicYds, 4)} cu yd` : `${num(r.dimensions.netCubicM, 4)} cu m`],
      ['Waste Percentage', pct(r.dimensions.wastePercent)],
      ['Waste Quantity', imp ? `${num(r.dimensions.wasteCubicYds, 4)} cu yd` : `${num(r.dimensions.wasteCubicM, 4)} cu m`],
      ['Order Quantity', imp ? `${num(r.dimensions.orderCubicYds, 4)} cu yd` : `${num(r.dimensions.orderCubicM, 4)} cu m`],
      ['Bag Equivalent (if using bagged mix)', `${r.bags.totalBags} bags of ${r.bags.bagSize} lb`],
      ['Selected Material Source', c.materialType === 'readymix' ? 'Ready-Mix (Delivered)' : 'Bagged Concrete'],
      ['Unit Rate', `${money(c.unitRate)} / ${asciiUnit(c.unit)}`],
      ['Material Cost', money(c.cost)]
    ];
    kvTable(ctx, concreteRows);

    subHeading(ctx, 'Gravel');
    if (det.gravel.enabled) {
      kvTable(ctx, [
        ['Surface Area', `${num(det.gravel.areaSqFt, 0)} sq ft`],
        ['Base Depth', `${num(det.gravel.depthInches, 1)} in`],
        ['Raw Volume (before compaction factor)', `${num(det.gravel.rawVolCuFt / 27, 3)} cu yd`],
        ['Compaction Factor Applied', `${num((det.gravel.compactionFactor - 1) * 100, 0)}% (× ${num(det.gravel.compactionFactor, 2)})`],
        ['Final Quantity', `${num(det.gravel.gravelYards, 2)} cu yd`],
        ['Unit Rate', `${money(det.gravel.rate)} / cu yd`],
        ['Total Cost', money(det.gravel.cost)]
      ]);
    } else {
      kvTable(ctx, [['Gravel Sub-Base', 'Not Included']]);
    }

    subHeading(ctx, 'Formwork');
    if (det.forms.enabled) {
      kvTable(ctx, [
        ['Slab Perimeter', `${num(det.forms.perimeterFt, 2)} lin ft`],
        ['Formwork Quantity', `${num(det.forms.perimeterFt, 2)} lin ft`],
        ['Rate Per Linear Foot', `${money(det.forms.rate)} / lin ft`],
        ['Total Cost', money(det.forms.cost)]
      ]);
    } else {
      kvTable(ctx, [['Formwork', 'Not Included']]);
    }

    subHeading(ctx, 'Vapor Barrier');
    if (det.vaporBarrier.enabled) {
      kvTable(ctx, [
        ['Coverage Area', `${num(det.vaporBarrier.areaSqFt, 0)} sq ft`],
        ['Rate Per Sq Ft', `${money(det.vaporBarrier.rate)} / sq ft`],
        ['Total Cost', money(det.vaporBarrier.cost)]
      ]);
    } else {
      kvTable(ctx, [['Vapor Barrier', 'Not Included']]);
    }

    subHeading(ctx, 'Reinforcement');
    // Only the actual calculation basis the calculator uses: an area-rate
    // (mesh/rebar), a flat cost override (custom), or none -- never an
    // invented rebar stick/mesh-sheet count the calculator doesn't compute.
    const rf = det.reinforcement;
    if (rf.type === 'none') {
      kvTable(ctx, [['Reinforcement', 'Not Included (plain concrete)']]);
    } else if (rf.basis && rf.basis.unit === 'sq ft') {
      kvTable(ctx, [
        ['Specification', rf.label],
        ['Calculation Basis', 'Area × Rate'],
        ['Area', `${num(rf.basis.qty, 0)} sq ft`],
        ['Rate', `${money(rf.basis.rate)} / sq ft`],
        ['Total Cost', money(rf.cost)]
      ]);
    } else {
      kvTable(ctx, [
        ['Specification', rf.label],
        ['Calculation Basis', 'Custom flat cost override entered by the user'],
        ['Total Cost', money(rf.cost)]
      ]);
    }

    subHeading(ctx, 'Finishing');
    kvTable(ctx, [
      ['Selected Finish', det.finishing.label],
      ['Area', `${num(det.finishing.areaSqFt, 0)} sq ft`],
      ['Rate', `${money(det.finishing.rate)} / sq ft`],
      ['Total Cost', money(det.finishing.cost)]
    ]);
  }

  // ---------------------------------------------------------------------
  // SECTION 5 -- LABOR CALCULATION
  // ---------------------------------------------------------------------
  function renderLabor(ctx) {
    const r = ctx.result;
    const lab = r.detail.labor;

    sectionHeading(ctx, '5. Labor Calculation');

    paragraph(ctx, `Labor Method: ${laborMethodDisplayName(lab.mode)}`, { bold: true, size: 10 });

    if (lab.mode === 'sqft') {
      calcStep(ctx, 'Per-Square-Foot Labor', `${num(lab.areaSqFt, 0)} sq ft × ${money(lab.ratePerSqFt)}/sq ft`, money(lab.cost));
    } else if (lab.mode === 'hourly') {
      calcStep(ctx, 'Crew Rate × Hours', `${money(lab.hourlyRate)}/hr × ${num(lab.hours, 1)} hrs`, money(lab.cost));
    } else if (lab.mode === 'flat') {
      calcStep(ctx, 'Flat Contractor Quote', 'Flat Quote = Labor Cost', money(lab.cost));
    } else if (lab.mode === 'diy') {
      paragraph(ctx, 'DIY / Self-Performed Labor selected -- no contractor labor cost is included in this estimate. Only your own time and any tool/equipment rentals (see Section 6) apply.', { color: COLOR.grayText });
    }
  }

  // ---------------------------------------------------------------------
  // SECTION 6 -- ADDITIONAL PROJECT COSTS TABLE
  // ---------------------------------------------------------------------
  function renderCostTable(ctx) {
    sectionHeading(ctx, '6. Additional Project Costs');
    const body = ctx.result.items.map(function (it) {
      return [
        sanitizeLabel(it.name),
        it.qty != null ? num(it.qty, it.unit === 'bags' || it.unit === 'hrs' ? 1 : 2) : '—',
        asciiUnit(it.unit) || '—',
        it.rate != null ? money(it.rate) : '—',
        money(it.cost)
      ];
    });
    dataTable(ctx,
      ['Cost Component', 'Quantity', 'Unit', 'Unit Rate', 'Calculated Cost'],
      body,
      {
        0: { cellWidth: CONTENT_WIDTH * 0.40 },
        1: { cellWidth: CONTENT_WIDTH * 0.15, halign: 'right' },
        2: { cellWidth: CONTENT_WIDTH * 0.13 },
        3: { cellWidth: CONTENT_WIDTH * 0.15, halign: 'right' },
        4: { cellWidth: CONTENT_WIDTH * 0.17, halign: 'right', fontStyle: 'bold' }
      }
    );
  }

  // ---------------------------------------------------------------------
  // SECTION 7 -- COST SUMMARY
  // ---------------------------------------------------------------------
  function renderCostSummary(ctx) {
    const r = ctx.result;
    const ct = r.categoryTotals;

    sectionHeading(ctx, '7. Cost Summary');

    const body = [
      ['Concrete Material Subtotal', money(ct.material)],
      ['Site Preparation & Base Subtotal', money(ct.base)],
      ['Reinforcement', money(ct.reinforce)],
      ['Formwork', money(ct.forms)],
      ['Labor', money(ct.labor)],
      ['Delivery, Equipment, Finishing & Other', money(ct.other)]
    ];
    dataTable(ctx, ['Category', 'Cost'], body,
      { 0: { cellWidth: CONTENT_WIDTH * 0.70 }, 1: { cellWidth: CONTENT_WIDTH * 0.30, halign: 'right', fontStyle: 'bold' } },
      {
        foot: [
          ['Base Project Cost (Direct Cost Subtotal)', money(r.costs.directSubtotal)],
          [`Contractor Markup (${pct(r.costs.markupPercent)})`, money(r.costs.markupCost)],
          [`Contingency (${pct(r.costs.contingencyPercent)})`, money(r.costs.contingencyCost)],
          ['FINAL ESTIMATED PROJECT COST', money(r.costs.totalEstimatedCost)]
        ]
      }
    );
  }

  // ---------------------------------------------------------------------
  // SECTION 8 -- MARKUP AND CONTINGENCY
  // ---------------------------------------------------------------------
  function renderMarkupContingency(ctx) {
    const r = ctx.result;
    const c = r.costs;

    sectionHeading(ctx, '8. Markup and Contingency');

    paragraph(ctx,
      'Contingency in this calculator is applied AFTER markup -- i.e. to the marked-up subtotal, not to the base cost alone. The steps below reproduce the calculator\'s exact order of operations.',
      { color: COLOR.grayText, size: 8.5 }
    );

    calcStep(ctx, 'Base Project Cost', 'Sum of all direct line-item costs (Section 6)', money(c.directSubtotal));
    calcStep(ctx, `Contractor Markup (${pct(c.markupPercent)})`, `${money(c.directSubtotal)} × ${pct(c.markupPercent)}`, `${money(c.markupCost)} markup amount`);
    calcStep(ctx, 'Subtotal After Markup', `${money(c.directSubtotal)} + ${money(c.markupCost)}`, money(c.directSubtotal + c.markupCost));
    calcStep(ctx, `Contingency (${pct(c.contingencyPercent)})`, `(${money(c.directSubtotal)} + ${money(c.markupCost)}) × ${pct(c.contingencyPercent)}`, `${money(c.contingencyCost)} contingency amount`);
    calcStep(ctx, 'Final Estimated Project Cost', `${money(c.directSubtotal + c.markupCost)} + ${money(c.contingencyCost)}`, money(c.totalEstimatedCost));
  }

  // ---------------------------------------------------------------------
  // SECTION 9 -- UNIT COST ANALYSIS
  // ---------------------------------------------------------------------
  function renderUnitCostAnalysis(ctx) {
    const r = ctx.result;
    const d = r.dimensions;
    const c = r.costs;

    sectionHeading(ctx, '9. Unit Cost Analysis');

    kvTable(ctx, [
      ['Total Surface Area', `${num(d.areaSqFt, 0)} sq ft  (${num(d.areaSqM, 2)} sq m)`],
      ['Total Project Cost', money(c.totalEstimatedCost)]
    ]);

    calcStep(ctx, 'Cost Per Square Foot', `${money(c.totalEstimatedCost)} ÷ ${num(d.areaSqFt, 0)} sq ft`, `${money(r.unitCosts.costPerSqFt)} / sq ft`);
    calcStep(ctx, 'Cost Per Square Meter', `${money(c.totalEstimatedCost)} ÷ ${num(d.areaSqM, 2)} sq m`, `${money(r.unitCosts.costPerSqM)} / sq m`);
  }

  // ---------------------------------------------------------------------
  // SECTION 10 -- CONCRETE PROCUREMENT SUMMARY
  // ---------------------------------------------------------------------
  function renderProcurement(ctx) {
    const r = ctx.result;
    const c = r.detail.concrete;
    const d = r.dimensions;

    sectionHeading(ctx, '10. Concrete Procurement Summary');

    if (c.materialType === 'readymix') {
      kvTable(ctx, [
        ['Net Concrete Volume', r.isImperial ? `${num(d.netCubicYds, 4)} cu yd` : `${num(d.netCubicM, 4)} cu m`],
        ['Waste Percentage', pct(d.wastePercent)],
        ['Order Volume', r.isImperial ? `${num(d.orderCubicYds, 4)} cu yd` : `${num(d.orderCubicM, 4)} cu m`],
        ['Price', `${money(c.unitRate)} / ${asciiUnit(c.unit)}`],
        ['Estimated Concrete Material Cost', money(c.cost)]
      ]);
    } else {
      kvTable(ctx, [
        ['Bag Size', `${c.bagSize} lb`],
        ['Bag Yield', `${num(c.bagYieldCuFt, 3)} cu ft / bag`],
        ['Estimated Number of Bags', `${c.totalBags} bags`],
        ['Price Per Bag', money(c.bagPrice)],
        ['Estimated Bagged Material Cost', money(c.cost)]
      ]);
    }
  }

  // ---------------------------------------------------------------------
  // SECTION 11 -- CALCULATION FORMULAS & METHODOLOGY
  // ---------------------------------------------------------------------
  function renderFormulas(ctx) {
    const r = ctx.result;
    const lab = r.detail.labor;

    sectionHeading(ctx, '11. Calculation Formulas & Methodology');
    paragraph(ctx, 'This section documents every formula actually used to produce this report, so the estimate is auditable against the numbers above.', { color: COLOR.grayText, size: 8.5 });

    const formulas = [
      ['Area', 'Area = Length × Width × Quantity'],
      ['Perimeter', 'Perimeter = 2 × (Length + Width) × Quantity'],
      ['Thickness Conversion', r.isImperial ? 'Thickness (ft) = Thickness (in) ÷ 12' : 'Thickness (m) = Thickness (cm) ÷ 100'],
      ['Concrete Volume', 'Volume = Area × Thickness'],
      ['Cubic Yard Conversion', 'Cubic Yards = Cubic Feet ÷ 27'],
      ['Waste-Adjusted Quantity', 'Order Quantity = Net Quantity × (1 + Waste %)'],
      ['Component Cost', 'Component Cost = Quantity × Unit Rate  (or a flat/override amount where the calculator uses one)']
    ];

    if (r.detail.gravel.enabled) {
      formulas.push(['Gravel Sub-Base Volume', 'Gravel (cu yd) = [Area × (Depth ÷ 12) ÷ 27] × 1.15 compaction factor']);
    }
    if (lab.mode === 'sqft') formulas.push(['Labor (Per Sq Ft method)', 'Labor Cost = Area × Labor Rate / sq ft']);
    if (lab.mode === 'hourly') formulas.push(['Labor (Hourly Crew method)', 'Labor Cost = Crew Hourly Rate × Estimated Hours']);
    if (lab.mode === 'flat') formulas.push(['Labor (Flat Quote method)', 'Labor Cost = Flat Contractor Quote (entered directly)']);
    if (lab.mode === 'diy') formulas.push(['Labor (DIY)', 'Labor Cost = $0.00 (self-performed, no contractor cost)']);

    formulas.push(['Markup', 'Markup Amount = Base Project Cost × Markup %']);
    formulas.push(['Contingency', 'Contingency Amount = (Base Project Cost + Markup Amount) × Contingency %  (applied after markup)']);
    formulas.push(['Final Total', 'Final Cost = Base Project Cost + Markup Amount + Contingency Amount']);
    formulas.push(['Unit Cost', 'Cost per Sq Ft = Final Cost ÷ Total Area (sq ft); Cost per sq m = Final Cost ÷ Total Area (sq m)']);

    dataTable(ctx, ['Formula', 'Definition'], formulas,
      { 0: { cellWidth: CONTENT_WIDTH * 0.28, fontStyle: 'bold' }, 1: { cellWidth: CONTENT_WIDTH * 0.72, font: 'courier', fontSize: 8 } }
    );
  }

  // ---------------------------------------------------------------------
  // SECTION 12 -- ITEMIZED ESTIMATE TABLE
  // ---------------------------------------------------------------------
  function renderItemizedEstimate(ctx) {
    const r = ctx.result;
    sectionHeading(ctx, '12. Itemized Estimate');

    const body = r.items.map(function (it) {
      const cleanName = sanitizeLabel(it.name);
      // Only split off a "Description" when the ENTIRE name ends in a
      // single trailing "(...)" clause (e.g. "Ready-Mix Concrete (5.43 cu
      // yd @ $150.00/cu yd)"). A name with a parenthetical in the middle,
      // like the rebar spec's '#4 (1/2") Steel Rebar Grid...', would
      // otherwise be cut at the wrong "(" and leave a fragment like "#4" as
      // the Item -- so those are left whole in the Item column instead.
      const trailingParenMatch = cleanName.match(/^(.*?)\s*(\([^()]*\))$/);
      const item = trailingParenMatch ? trailingParenMatch[1].trim() : cleanName;
      const description = trailingParenMatch ? trailingParenMatch[2] : '';
      return [
        item,
        description,
        it.qty != null ? num(it.qty, it.unit === 'bags' || it.unit === 'hrs' ? 1 : 2) : '—',
        asciiUnit(it.unit) || '—',
        it.rate != null ? money(it.rate) : '—',
        money(it.cost)
      ];
    });

    dataTable(ctx,
      ['Item', 'Description', 'Quantity', 'Unit', 'Unit Rate', 'Extended Cost'],
      body,
      {
        0: { cellWidth: CONTENT_WIDTH * 0.16 },
        1: { cellWidth: CONTENT_WIDTH * 0.32, fontSize: 7.5 },
        2: { cellWidth: CONTENT_WIDTH * 0.13, halign: 'right' },
        3: { cellWidth: CONTENT_WIDTH * 0.11 },
        4: { cellWidth: CONTENT_WIDTH * 0.13, halign: 'right' },
        5: { cellWidth: CONTENT_WIDTH * 0.15, halign: 'right', fontStyle: 'bold' }
      },
      {
        foot: [
          ['', '', '', '', 'Direct Subtotal', money(r.costs.directSubtotal)],
          ['', '', '', '', `Markup (${pct(r.costs.markupPercent)})`, money(r.costs.markupCost)],
          ['', '', '', '', `Contingency (${pct(r.costs.contingencyPercent)})`, money(r.costs.contingencyCost)],
          ['', '', '', '', 'FINAL TOTAL', money(r.costs.totalEstimatedCost)]
        ]
      }
    );
  }

  // ---------------------------------------------------------------------
  // SECTION 13 -- COST DISTRIBUTION VISUALIZATION
  // ---------------------------------------------------------------------
  function renderCostDistribution(ctx) {
    const doc = ctx.doc;
    const r = ctx.result;
    const total = r.costs.totalEstimatedCost;

    sectionHeading(ctx, '13. Cost Distribution');
    paragraph(ctx, 'Every dollar of the Final Estimated Project Cost, including markup and contingency, is represented below so the percentages reconcile to 100%.', { color: COLOR.grayText, size: 8.5, gapAfter: 8 });

    // Direct-cost categories plus markup/contingency as their own bars --
    // together these always sum to exactly r.costs.totalEstimatedCost, so
    // the percentages shown reconcile to 100% of the FINAL cost rather than
    // only the pre-markup/contingency direct subtotal.
    const bars = ['material', 'labor', 'base', 'reinforce', 'forms', 'other']
      .map(function (k) { return { key: k, label: CATEGORY_LABELS[k], value: r.categoryTotals[k] }; })
      .concat([
        { key: 'markup', label: `Contractor Markup (${pct(r.costs.markupPercent)})`, value: r.costs.markupCost },
        { key: 'contingency', label: `Contingency (${pct(r.costs.contingencyPercent)})`, value: r.costs.contingencyCost }
      ])
      .filter(function (b) { return b.value > 0.004; });

    const barColors = {
      material: [37, 99, 235], labor: [16, 185, 129], base: [245, 158, 11],
      reinforce: [139, 92, 246], forms: [236, 72, 153], other: [100, 116, 139],
      markup: [234, 179, 8], contingency: [220, 38, 38]
    };

    const barHeight = 16;
    const rowGap = 10;
    const trackWidth = CONTENT_WIDTH - 150;

    ensureSpace(ctx, bars.length * (barHeight + rowGap) + 10);

    bars.forEach(function (b) {
      const pctOfTotal = total > 0 ? (b.value / total) * 100 : 0;
      const fillW = Math.max(2, (pctOfTotal / 100) * trackWidth);

      doc.setFont('helvetica', 'normal');
      doc.setFontSize(8.5);
      doc.setTextColor.apply(doc, COLOR.ink);
      doc.text(b.label, MARGIN.left, ctx.y + barHeight - 5);

      const trackX = MARGIN.left + 110;
      doc.setFillColor(237, 240, 245);
      doc.rect(trackX, ctx.y, trackWidth, barHeight, 'F');
      doc.setFillColor.apply(doc, barColors[b.key] || COLOR.grayText);
      doc.rect(trackX, ctx.y, fillW, barHeight, 'F');

      doc.setFont('helvetica', 'bold');
      doc.setFontSize(8);
      doc.setTextColor.apply(doc, COLOR.ink);
      doc.text(`${money(b.value)}  (${num(pctOfTotal, 1)}%)`, trackX + trackWidth + 6, ctx.y + barHeight - 5);

      ctx.y += barHeight + rowGap;
    });
  }

  // ---------------------------------------------------------------------
  // SECTION 14 -- FINAL PROJECT SUMMARY
  // ---------------------------------------------------------------------
  function renderFinalSummary(ctx) {
    const doc = ctx.doc;
    const r = ctx.result;
    const d = r.dimensions;
    const c = r.costs;

    sectionHeading(ctx, '14. Final Project Summary');

    ensureSpace(ctx, 60);
    doc.setFillColor.apply(doc, COLOR.heading);
    doc.roundedRect(MARGIN.left, ctx.y, CONTENT_WIDTH, 50, 6, 6, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(11);
    doc.text('ESTIMATED TOTAL PROJECT COST', MARGIN.left + 14, ctx.y + 20);
    doc.setFontSize(22);
    doc.text(money(c.totalEstimatedCost), MARGIN.left + 14, ctx.y + 41);
    ctx.y += 50 + 12;
    doc.setTextColor.apply(doc, COLOR.ink);

    kvTable(ctx, [
      ['Total Area', `${num(d.areaSqFt, 0)} sq ft  (${num(d.areaSqM, 2)} sq m)`],
      ['Total Concrete Volume (net)', r.isImperial ? `${num(d.netCubicYds, 2)} cu yd` : `${num(d.netCubicM, 2)} cu m`],
      ['Order Quantity (with waste)', r.isImperial ? `${num(d.orderCubicYds, 2)} cu yd` : `${num(d.orderCubicM, 2)} cu m`],
      ['Cost per Sq Ft', money(r.unitCosts.costPerSqFt)],
      ['Cost per sq m', money(r.unitCosts.costPerSqM)],
      ['Material Subtotal', money(r.categoryTotals.material)],
      ['Labor Subtotal', money(r.categoryTotals.labor)],
      ['Additional Costs (Site Prep, Reinforcement, Forms, Other)', money(r.categoryTotals.base + r.categoryTotals.reinforce + r.categoryTotals.forms + r.categoryTotals.other)],
      [`Markup (${pct(c.markupPercent)})`, money(c.markupCost)],
      [`Contingency (${pct(c.contingencyPercent)})`, money(c.contingencyCost)]
    ]);
  }

  // ---------------------------------------------------------------------
  // SECTION 15 -- NOTES & DISCLAIMER
  // ---------------------------------------------------------------------
  function renderDisclaimer(ctx) {
    sectionHeading(ctx, '15. Estimate Notes & Disclaimer');
    paragraph(ctx,
      'This estimate is based on the quantities, rates, options, and assumptions entered into the calculator at the time the report was generated. Actual material, labor, delivery, equipment, site-condition, and contractor pricing may vary. This report is an estimating document and is not a structural engineering design, stamped plan, building permit, or binding contractor quotation.',
      { color: COLOR.grayText, size: 8.5, lineHeight: 12 }
    );
  }

  // ---------------------------------------------------------------------
  // Footer (page numbers, report id, branding) -- applied in a final pass
  // ---------------------------------------------------------------------
  function applyFooters(doc, reportId) {
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
      doc.setPage(i);
      doc.setDrawColor.apply(doc, COLOR.grayLine);
      doc.setLineWidth(0.5);
      doc.line(MARGIN.left, PAGE.height - MARGIN.bottom + 14, PAGE.width - MARGIN.right, PAGE.height - MARGIN.bottom + 14);
      doc.setFont('helvetica', 'normal');
      doc.setFontSize(7.5);
      doc.setTextColor.apply(doc, COLOR.grayText);
      doc.text('Slab Cost Calculator — slabcostcalculator.com', MARGIN.left, PAGE.height - MARGIN.bottom + 26);
      doc.text(`Report ID: ${reportId}`, PAGE.width / 2, PAGE.height - MARGIN.bottom + 26, { align: 'center' });
      doc.text(`Page ${i} of ${pageCount}`, PAGE.width - MARGIN.right, PAGE.height - MARGIN.bottom + 26, { align: 'right' });
    }
  }

  // ---------------------------------------------------------------------
  // Entry point
  // ---------------------------------------------------------------------
  function generate(result) {
    if (!result || !window.jspdf || !window.jspdf.jsPDF) {
      console.error('SlabPDFReport: calculation result or jsPDF library unavailable.');
      return;
    }

    const doc = new window.jspdf.jsPDF({ unit: 'pt', format: 'letter', compress: true });
    doc.setProperties({
      title: 'Concrete Slab Cost Estimate & Calculation Report',
      subject: 'Concrete slab project cost estimate',
      creator: 'Slab Cost Calculator (slabcostcalculator.com)'
    });

    const reportId = makeReportId(result);
    const ctx = newCtx(doc, reportId, result);

    renderCover(ctx);
    doc.addPage(); ctx.y = MARGIN.top;
    renderInputs(ctx);
    doc.addPage(); ctx.y = MARGIN.top;
    renderDimensionCalcs(ctx);
    doc.addPage(); ctx.y = MARGIN.top;
    renderMaterialQuantities(ctx);
    doc.addPage(); ctx.y = MARGIN.top;
    renderLabor(ctx);
    renderCostTable(ctx);
    doc.addPage(); ctx.y = MARGIN.top;
    renderCostSummary(ctx);
    renderMarkupContingency(ctx);
    doc.addPage(); ctx.y = MARGIN.top;
    renderUnitCostAnalysis(ctx);
    renderProcurement(ctx);
    doc.addPage(); ctx.y = MARGIN.top;
    renderFormulas(ctx);
    doc.addPage(); ctx.y = MARGIN.top;
    renderItemizedEstimate(ctx);
    doc.addPage(); ctx.y = MARGIN.top;
    renderCostDistribution(ctx);
    renderFinalSummary(ctx);
    doc.addPage(); ctx.y = MARGIN.top;
    renderDisclaimer(ctx);

    applyFooters(doc, reportId);

    const filename = `Concrete-Slab-Estimate-${safeFileSlug(reportId)}.pdf`;
    doc.save(filename);
    return { reportId: reportId, filename: filename, pageCount: doc.internal.getNumberOfPages() };
  }

  // Injects the jsPDF scripts in order, once. Resolves when both have run.
  let libsPromise = null;
  function load() {
    if (window.jspdf && window.jspdf.jsPDF) return Promise.resolve();
    if (libsPromise) return libsPromise;
    const libs = (window.SlabPDFConfig && window.SlabPDFConfig.libs) || [];
    libsPromise = libs.reduce(function (chain, src) {
      return chain.then(function () {
        return new Promise(function (resolve, reject) {
          const s = document.createElement('script');
          s.src = src;
          s.onload = resolve;
          s.onerror = function () { reject(new Error('Failed to load ' + src)); };
          document.head.appendChild(s);
        });
      });
    }, Promise.resolve()).catch(function (err) {
      libsPromise = null; // allow a retry on the next click
      throw err;
    });
    return libsPromise;
  }

  window.SlabPDFReport = { load: load, generate: generate };

})();
