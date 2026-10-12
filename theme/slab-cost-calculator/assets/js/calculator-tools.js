/**
 * CALCULATOR PLANNING TOOLS
 * Regional price index, slab & rebar blueprint, contractor bid checker and
 * pour-day weather check. Reads every figure from the result object that
 * primary-calculator.js publishes (the `slabcalc:updated` event), so these
 * tools never disagree with the dashboard, the BOQ table or the PDF.
 */

(function () {
  'use strict';

  const FEET_TO_METERS = 0.3048;
  const YD3_PER_M3 = 1.30795;

  // Site-wide figures from inc/site-data.php (window.SLAB_DATA).
  const SD = window.SLAB_DATA || {};
  const sd = (key, fallback) => (SD[key] !== undefined && SD[key] !== null ? SD[key] : fallback);

  // National baseline rates. Same defaults as the calculator form.
  const BASE_RATES = {
    readyMixPerYard: sd('readyMixPerYard', 150.00),
    excavationPerSqFt: sd('excavation', 1.50),
    laborPerSqFt: sd('laborPerSqFt', 5.00),
    laborPerHour: sd('crewHour', 140.00) // whole crew, not one finisher
  };
  const OVERHEAD_PROFIT = sd('overheadProfit', 0.25);
  const SPLICE_ALLOWANCE = sd('spliceAllowance', 0.10);
  const EDGE_COVER_FT = sd('edgeCoverIn', 3) / 12;
  const WX = {
    cold: sd('coldF', 40),
    cool: sd('coolF', 50),
    hot: sd('hotF', 85),
    concreteLimit: sd('concreteTempLimitF', [90, 95]),
    evap: sd('evapLimitLb', 0.2)
  };
  const fToC = (f) => Math.round((f - 32) * 5 / 9);

  // Approximate construction cost location factors (U.S. national average
  // = 1.00), rounded from published city cost index data and weighted
  // toward each state's main metro areas. Local quotes still vary; these
  // set a sensible starting rate, not a guaranteed price.
  const METROS = [
    ['nyc', 'New York City metro, NY', 1.35],
    ['sf', 'San Francisco Bay Area, CA', 1.35],
    ['la', 'Los Angeles metro, CA', 1.20],
    ['bos', 'Boston metro, MA', 1.22],
    ['chi', 'Chicago metro, IL', 1.22],
    ['sea', 'Seattle metro, WA', 1.12],
    ['dc', 'Washington, DC metro', 1.05],
    ['den', 'Denver metro, CO', 0.97],
    ['hou', 'Houston metro, TX', 0.87],
    ['dal', 'Dallas-Fort Worth, TX', 0.88],
    ['atl', 'Atlanta metro, GA', 0.90],
    ['mia', 'Miami metro, FL', 0.92],
    ['phx', 'Phoenix metro, AZ', 0.90]
  ];

  const STATES = [
    ['AL', 'Alabama', 0.86], ['AK', 'Alaska', 1.22], ['AZ', 'Arizona', 0.90],
    ['AR', 'Arkansas', 0.85], ['CA', 'California', 1.25], ['CO', 'Colorado', 0.95],
    ['CT', 'Connecticut', 1.12], ['DE', 'Delaware', 1.03], ['DC', 'District of Columbia', 1.05],
    ['FL', 'Florida', 0.88], ['GA', 'Georgia', 0.88], ['HI', 'Hawaii', 1.25],
    ['ID', 'Idaho', 0.92], ['IL', 'Illinois', 1.10], ['IN', 'Indiana', 0.93],
    ['IA', 'Iowa', 0.94], ['KS', 'Kansas', 0.90], ['KY', 'Kentucky', 0.90],
    ['LA', 'Louisiana', 0.86], ['ME', 'Maine', 0.95], ['MD', 'Maryland', 0.98],
    ['MA', 'Massachusetts', 1.18], ['MI', 'Michigan', 0.98], ['MN', 'Minnesota', 1.05],
    ['MS', 'Mississippi', 0.84], ['MO', 'Missouri', 0.98], ['MT', 'Montana', 0.93],
    ['NE', 'Nebraska', 0.90], ['NV', 'Nevada', 1.02], ['NH', 'New Hampshire', 1.00],
    ['NJ', 'New Jersey', 1.15], ['NM', 'New Mexico', 0.88], ['NY', 'New York (statewide)', 1.20],
    ['NC', 'North Carolina', 0.88], ['ND', 'North Dakota', 0.92], ['OH', 'Ohio', 0.95],
    ['OK', 'Oklahoma', 0.86], ['OR', 'Oregon', 1.03], ['PA', 'Pennsylvania', 1.02],
    ['RI', 'Rhode Island', 1.10], ['SC', 'South Carolina', 0.87], ['SD', 'South Dakota', 0.88],
    ['TN', 'Tennessee', 0.88], ['TX', 'Texas', 0.87], ['UT', 'Utah', 0.92],
    ['VT', 'Vermont', 0.95], ['VA', 'Virginia', 0.92], ['WA', 'Washington', 1.08],
    ['WV', 'West Virginia', 0.95], ['WI', 'Wisconsin', 1.00], ['WY', 'Wyoming', 0.90]
  ];

  const REGIONS = { us: { name: 'U.S. National Average', index: 1.00 } };
  METROS.forEach(m => { REGIONS['m-' + m[0]] = { name: m[1], index: m[2] }; });
  STATES.forEach(st => { REGIONS['s-' + st[0]] = { name: st[1], index: st[2] }; });

  let root = null;
  let lastResult = null;
  let region = REGIONS.us;

  function $(sel) { return root.querySelector(sel); }

  function money(n, dec) {
    const d = dec === undefined ? 0 : dec;
    return '$' + n.toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
  }

  function esc(str) {
    return String(str).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  }

  /* ======================= 1. REGIONAL PRICE INDEX ======================= */

  function buildRegionSelect() {
    const sel = $('#calc-region');
    if (!sel) return;
    const metroGroup = document.createElement('optgroup');
    metroGroup.label = 'Major metro areas';
    METROS.forEach(m => metroGroup.appendChild(new Option(`${m[1]} (${m[2].toFixed(2)})`, 'm-' + m[0])));
    const stateGroup = document.createElement('optgroup');
    stateGroup.label = 'States';
    STATES.forEach(st => stateGroup.appendChild(new Option(`${st[1]} (${st[2].toFixed(2)})`, 's-' + st[0])));
    sel.appendChild(metroGroup);
    sel.appendChild(stateGroup);
    sel.addEventListener('change', () => applyRegion(sel.value));
  }

  function setInput(id, value) {
    const el = $(id);
    if (el) el.value = value;
    return el;
  }

  function applyRegion(key) {
    region = REGIONS[key] || REGIONS.us;
    window.SlabCalculatorRegion = { key: key, name: region.name, index: region.index };
    const idx = region.index;
    const metric = lastResult && !lastResult.isImperial;
    const readyMix = metric ? BASE_RATES.readyMixPerYard * YD3_PER_M3 * idx : BASE_RATES.readyMixPerYard * idx;
    setInput('#calc-readymix-price', readyMix.toFixed(2));
    setInput('#calc-excavation-rate', (BASE_RATES.excavationPerSqFt * idx).toFixed(2));
    setInput('#calc-labor-hourly-rate', (BASE_RATES.laborPerHour * idx).toFixed(2));
    const trigger = setInput('#calc-labor-sqft-rate', (BASE_RATES.laborPerSqFt * idx).toFixed(2));
    // One input event makes the engine re-read every field and recalculate.
    if (trigger) trigger.dispatchEvent(new Event('input', { bubbles: true }));
    updateRegionTag();
  }

  function updateRegionTag() {
    const tag = $('#out-region-tag');
    if (!tag) return;
    if (region.index === 1) {
      tag.textContent = 'Based on U.S. national average rates';
      tag.classList.remove('is-adjusted');
    } else {
      tag.textContent = `Adjusted to ${region.name} regional cost index (x${region.index.toFixed(2)})`;
      tag.classList.add('is-adjusted');
    }
  }

  /* ========================= 2. SLAB BLUEPRINT ========================== */

  // Control joints: 2.5 x thickness (in) in feet, capped at 15 ft. Matches
  // the joint spacing table in the control-joint guide on the homepage.
  function jointTargetFt(thicknessIn) {
    return Math.min(Math.max(thicknessIn, 3) * sd('jointRatio', 2.5), sd('jointCapFt', 15));
  }

  function slabGeometry(r) {
    const d = r.dimensions;
    const imp = r.isImperial;
    const lengthFt = imp ? d.length : d.length / FEET_TO_METERS;
    const widthFt = imp ? d.width : d.width / FEET_TO_METERS;
    const thicknessIn = imp ? d.thickness : d.thickness / 2.54;
    const type = r.detail.reinforcement.type;
    const spacingSel = $('#calc-rebar-spacing');
    const spacingIn = spacingSel ? parseFloat(spacingSel.value) || 18 : 18;
    const coverFt = EDGE_COVER_FT;

    const geo = { lengthFt, widthFt, thicknessIn, type, spacingIn, quantity: d.quantity, imp };
    geo.xBars = []; // bars running along the length (drawn horizontal), y positions
    geo.yBars = []; // bars running along the width (drawn vertical), x positions
    geo.chairs = [];

    if (type === 'rebar' && lengthFt > 2 * coverFt && widthFt > 2 * coverFt) {
      const sp = spacingIn / 12;
      const fill = (span) => {
        const usable = span - 2 * coverFt;
        const n = Math.floor(usable / sp) + 1;
        const step = n > 1 ? usable / (n - 1) : 0;
        const out = [];
        for (let i = 0; i < n; i++) out.push(coverFt + i * step);
        return out;
      };
      geo.xBars = fill(widthFt);
      geo.yBars = fill(lengthFt);
      geo.barLinFt = geo.xBars.length * (lengthFt - 2 * coverFt) + geo.yBars.length * (widthFt - 2 * coverFt);
      // Chairs at grid intersections, every k-th bar so they land 3-4 ft apart.
      const k = Math.max(1, Math.round(42 / spacingIn));
      geo.chairEveryFt = (k * spacingIn) / 12;
      const pick = (arr) => {
        const out = arr.filter((_, i) => i % k === 0);
        if (out[out.length - 1] !== arr[arr.length - 1] && arr[arr.length - 1] - out[out.length - 1] > geo.chairEveryFt / 2) {
          out.push(arr[arr.length - 1]);
        }
        return out;
      };
      pick(geo.yBars).forEach(x => pick(geo.xBars).forEach(y => geo.chairs.push([x, y])));
    }

    const target = jointTargetFt(thicknessIn);
    geo.jointTargetFt = target;
    // Keep panels close to square (no more than 1.5:1), which on narrow
    // walkways means joints every 1 to 1.5 times the width.
    const panels = (span, limit) => Math.max(1, Math.ceil(span / limit - 1e-9));
    geo.panelsW = panels(widthFt, target);
    geo.panelsL = panels(lengthFt, Math.min(target, 1.5 * widthFt / geo.panelsW));
    geo.panelsW = panels(widthFt, Math.min(target, 1.5 * lengthFt / geo.panelsL));
    geo.panelLFt = lengthFt / geo.panelsL;
    geo.panelWFt = widthFt / geo.panelsW;
    geo.jointLinFt = (geo.panelsL - 1) * widthFt + (geo.panelsW - 1) * lengthFt;
    return geo;
  }

  function fmtLen(ft, imp) {
    if (imp) return `${parseFloat(ft.toFixed(1))} ft`;
    return `${(ft * FEET_TO_METERS).toFixed(2)} m`;
  }

  function buildBlueprintSVG(r) {
    const g = slabGeometry(r);
    const W = 820;
    const padX = 70;
    const top = 92;
    const maxW = W - 2 * padX;
    const maxH = 440;
    const scale = Math.min(maxW / g.lengthFt, maxH / g.widthFt);
    const sw = g.lengthFt * scale;
    const sh = g.widthFt * scale;
    const x0 = (W - sw) / 2;
    const y0 = top;
    const legendY = y0 + sh + 64;
    const H = legendY + 70;
    const X = (ft) => (x0 + ft * scale).toFixed(1);
    const Y = (ft) => (y0 + ft * scale).toFixed(1);
    const font = 'font-family="Arial, Helvetica, sans-serif"';

    const thickLabel = g.imp ? `${parseFloat(g.thicknessIn.toFixed(1))} in` : `${(g.thicknessIn * 2.54).toFixed(1)} cm`;
    let reinfLabel = 'plain concrete (no reinforcement)';
    if (g.type === 'rebar') reinfLabel = `#4 rebar @ ${g.spacingIn} in o.c. each way`;
    else if (g.type === 'mesh') reinfLabel = '6x6 welded wire mesh';
    else if (g.type === 'custom') reinfLabel = 'custom reinforcement';

    const parts = [];
    parts.push(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H.toFixed(0)}" width="${W}" height="${H.toFixed(0)}" role="img" aria-label="Scaled slab plan">`);
    parts.push(`<rect x="0" y="0" width="${W}" height="${H.toFixed(0)}" fill="#ffffff"/>`);
    parts.push(`<text x="${padX - 40}" y="34" ${font} font-size="19" font-weight="700" fill="#0f172a">Slab Plan: ${esc(fmtLen(g.lengthFt, g.imp))} x ${esc(fmtLen(g.widthFt, g.imp))}, ${esc(thickLabel)} thick</text>`);
    parts.push(`<text x="${padX - 40}" y="56" ${font} font-size="13" fill="#475569">${esc(reinfLabel)}${g.quantity > 1 ? ` | drawing shows 1 of ${g.quantity} identical slabs` : ''}</text>`);

    // Slab body
    parts.push(`<rect x="${X(0)}" y="${Y(0)}" width="${sw.toFixed(1)}" height="${sh.toFixed(1)}" fill="#f1f5f9" stroke="#0f172a" stroke-width="2.5"/>`);

    // Mesh: light 6-inch hatch (drawn every 6 in, thinned out on big slabs)
    if (g.type === 'mesh') {
      const step = Math.max(0.5, Math.ceil(Math.max(g.lengthFt, g.widthFt) / 0.5 / 120) * 0.5);
      for (let x = step; x < g.lengthFt; x += step) parts.push(`<line x1="${X(x)}" y1="${Y(0)}" x2="${X(x)}" y2="${Y(g.widthFt)}" stroke="#a78bfa" stroke-width="0.6"/>`);
      for (let y = step; y < g.widthFt; y += step) parts.push(`<line x1="${X(0)}" y1="${Y(y)}" x2="${X(g.lengthFt)}" y2="${Y(y)}" stroke="#a78bfa" stroke-width="0.6"/>`);
    }

    // Rebar grid
    if (g.type === 'rebar') {
      const c = EDGE_COVER_FT;
      g.xBars.forEach(y => parts.push(`<line x1="${X(c)}" y1="${Y(y)}" x2="${X(g.lengthFt - c)}" y2="${Y(y)}" stroke="#7c3aed" stroke-width="1.3"/>`));
      g.yBars.forEach(x => parts.push(`<line x1="${X(x)}" y1="${Y(c)}" x2="${X(x)}" y2="${Y(g.widthFt - c)}" stroke="#7c3aed" stroke-width="1.3"/>`));
      g.chairs.forEach(p => parts.push(`<circle cx="${X(p[0])}" cy="${Y(p[1])}" r="3.6" fill="#f59e0b" stroke="#92400e" stroke-width="1"/>`));
    }

    // Control joints
    for (let i = 1; i < g.panelsL; i++) {
      const x = i * g.panelLFt;
      parts.push(`<line x1="${X(x)}" y1="${Y(0)}" x2="${X(x)}" y2="${Y(g.widthFt)}" stroke="#dc2626" stroke-width="2" stroke-dasharray="9 6"/>`);
    }
    for (let j = 1; j < g.panelsW; j++) {
      const y = j * g.panelWFt;
      parts.push(`<line x1="${X(0)}" y1="${Y(y)}" x2="${X(g.lengthFt)}" y2="${Y(y)}" stroke="#dc2626" stroke-width="2" stroke-dasharray="9 6"/>`);
    }

    // Dimension callouts: length above, width on the left
    const dimY = y0 - 16;
    parts.push(`<line x1="${X(0)}" y1="${dimY}" x2="${X(g.lengthFt)}" y2="${dimY}" stroke="#334155" stroke-width="1.2"/>`);
    parts.push(`<line x1="${X(0)}" y1="${dimY - 6}" x2="${X(0)}" y2="${dimY + 6}" stroke="#334155" stroke-width="1.2"/>`);
    parts.push(`<line x1="${X(g.lengthFt)}" y1="${dimY - 6}" x2="${X(g.lengthFt)}" y2="${dimY + 6}" stroke="#334155" stroke-width="1.2"/>`);
    parts.push(`<text x="${(x0 + sw / 2).toFixed(1)}" y="${dimY - 6}" ${font} font-size="13" font-weight="700" fill="#0f172a" text-anchor="middle" paint-order="stroke" stroke="#ffffff" stroke-width="4">${esc(fmtLen(g.lengthFt, g.imp))}</text>`);
    const dimX = x0 - 16;
    parts.push(`<line x1="${dimX}" y1="${Y(0)}" x2="${dimX}" y2="${Y(g.widthFt)}" stroke="#334155" stroke-width="1.2"/>`);
    parts.push(`<line x1="${dimX - 6}" y1="${Y(0)}" x2="${dimX + 6}" y2="${Y(0)}" stroke="#334155" stroke-width="1.2"/>`);
    parts.push(`<line x1="${dimX - 6}" y1="${Y(g.widthFt)}" x2="${dimX + 6}" y2="${Y(g.widthFt)}" stroke="#334155" stroke-width="1.2"/>`);
    const wy = (y0 + sh / 2).toFixed(1);
    parts.push(`<text x="${dimX - 8}" y="${wy}" ${font} font-size="13" font-weight="700" fill="#0f172a" text-anchor="middle" transform="rotate(-90 ${dimX - 8} ${wy})">${esc(fmtLen(g.widthFt, g.imp))}</text>`);

    // Panel size label below the slab
    const panelTxt = (g.panelsL * g.panelsW > 1)
      ? `Saw-cut joints make ${g.panelsL * g.panelsW} panels of ${fmtLen(g.panelLFt, g.imp)} x ${fmtLen(g.panelWFt, g.imp)}`
      : `No interior joints needed (both sides within ${fmtLen(g.jointTargetFt, g.imp)})`;
    parts.push(`<text x="${(x0 + sw / 2).toFixed(1)}" y="${(y0 + sh + 26).toFixed(1)}" ${font} font-size="13" fill="#334155" text-anchor="middle">${esc(panelTxt)}</text>`);

    // Legend
    let lx = padX - 40;
    const ly = legendY;
    parts.push(`<line x1="${lx}" y1="${ly}" x2="${lx + 30}" y2="${ly}" stroke="#dc2626" stroke-width="2" stroke-dasharray="9 6"/><text x="${lx + 38}" y="${ly + 4}" ${font} font-size="12" fill="#334155">Control joint (saw cut 1/4 of depth)</text>`);
    lx += 260;
    if (g.type === 'rebar') {
      parts.push(`<line x1="${lx}" y1="${ly}" x2="${lx + 30}" y2="${ly}" stroke="#7c3aed" stroke-width="1.6"/><text x="${lx + 38}" y="${ly + 4}" ${font} font-size="12" fill="#334155">#4 rebar, ${sd('edgeCoverIn', 3)} in edge cover</text>`);
      lx += 200;
      parts.push(`<circle cx="${lx + 8}" cy="${ly}" r="4" fill="#f59e0b" stroke="#92400e"/><text x="${lx + 20}" y="${ly + 4}" ${font} font-size="12" fill="#334155">Support chair (~${parseFloat(g.chairEveryFt.toFixed(1))} ft grid)</text>`);
    } else if (g.type === 'mesh') {
      parts.push(`<line x1="${lx}" y1="${ly}" x2="${lx + 30}" y2="${ly}" stroke="#a78bfa" stroke-width="1"/><text x="${lx + 38}" y="${ly + 4}" ${font} font-size="12" fill="#334155">Welded wire mesh (not to scale)</text>`);
    }
    parts.push(`<text x="${padX - 40}" y="${ly + 38}" ${font} font-size="11" fill="#64748b">slabcostcalculator.com | planning sketch only, not a stamped structural drawing</text>`);
    parts.push('</svg>');
    return { svg: parts.join(''), geo: g };
  }

  function renderBlueprint() {
    const wrap = $('#blueprint-wrap');
    if (!wrap || !lastResult) return;
    const d = lastResult.dimensions;
    if (!(d.length > 0 && d.width > 0 && d.thickness > 0)) {
      wrap.innerHTML = '<p class="calc-field-note">Enter a length, width and thickness to draw the slab.</p>';
      $('#blueprint-summary').innerHTML = '';
      return;
    }
    const out = buildBlueprintSVG(lastResult);
    wrap.innerHTML = out.svg;
    const g = out.geo;
    const cards = [];
    cards.push(['Control joints', `${fmtLen(g.jointLinFt * g.quantity, g.imp)} of saw cut`, `Target spacing ${fmtLen(g.jointTargetFt, g.imp)} for ${g.imp ? parseFloat(g.thicknessIn.toFixed(1)) + ' in' : (g.thicknessIn * 2.54).toFixed(1) + ' cm'} thickness`]);
    if (g.type === 'rebar') {
      const linFt = g.barLinFt * g.quantity;
      // +10% for lap splices (40 bar diameters = 20 in for #4) and offcuts.
      const sticks = Math.ceil(+((linFt * (1 + SPLICE_ALLOWANCE)) / 20).toFixed(6));
      cards.push(['Rebar', g.imp ? `${Math.round(linFt).toLocaleString('en-US')} lin ft of #4` : `${Math.round(linFt * FEET_TO_METERS).toLocaleString('en-US')} m of #4 (12 mm)`, `About ${sticks} x 20-ft bars incl. ~${Math.round(SPLICE_ALLOWANCE * 100)}% for laps (${sd('lapBarDiameters', 40)} bar diameters, ${sd('lapInNo4', 20)} in for #4) and waste`]);
      cards.push(['Support chairs', `${g.chairs.length * g.quantity} chairs`, 'Hold bars at mid-depth of the slab']);
    }
    $('#blueprint-summary').innerHTML = cards.map(c => `<div class="quantity-card"><div class="quantity-card-label">${esc(c[0])}</div><div class="quantity-card-value tool-card-value">${esc(c[1])}</div><div class="quantity-card-note">${esc(c[2])}</div></div>`).join('');
  }

  function downloadBlueprintPNG() {
    if (!lastResult) return;
    const out = buildBlueprintSVG(lastResult);
    const img = new Image();
    const scale = 2;
    img.onload = function () {
      const canvas = document.createElement('canvas');
      canvas.width = img.width * scale;
      canvas.height = img.height * scale;
      const ctx = canvas.getContext('2d');
      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
      canvas.toBlob(function (blob) {
        if (!blob) return;
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        const d = lastResult.dimensions;
        a.href = url;
        a.download = `slab-blueprint-${d.length}x${d.width}.png`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 1000);
      }, 'image/png');
    };
    img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(out.svg);
  }

  /* ========================= 3. BID CHECKER ============================= */

  const BID_TIERS = [
    { key: 'low', label: 'Below material cost: check the scope', cls: 'tier-low' },
    { key: 'great', label: 'Great deal / budget-friendly', cls: 'tier-great' },
    { key: 'fair', label: 'Fair market price', cls: 'tier-fair' },
    { key: 'premium', label: 'Premium / elevated cost', cls: 'tier-premium' },
    { key: 'over', label: 'Overpriced / high markup risk', cls: 'tier-over' }
  ];

  function bidBenchmark(r) {
    const ct = r.categoryTotals;
    const area = r.dimensions.areaSqFt;
    const labor = area * BASE_RATES.laborPerSqFt * region.index;
    const cats = {
      material: ct.material + ct.reinforce,
      labor: labor,
      base: ct.base + ct.forms,
      other: ct.other
    };
    const materials = cats.material + cats.base + cats.other;
    const fair = materials + labor; // direct cost, no overhead or profit
    return {
      cats: cats,
      materials: materials,
      fair: fair,
      high: fair * (1 + OVERHEAD_PROFIT), // typical 10-25% overhead & profit on top
      overLine: fair * (1 + OVERHEAD_PROFIT) * 1.30 // more than 30% above the high-end benchmark
    };
  }

  function renderBid() {
    const box = $('#bid-result');
    const scope = $('#bid-scope-line');
    if (!box || !lastResult) return;
    const r = lastResult;
    const d = r.dimensions;
    const b = bidBenchmark(r);
    const dimTxt = r.isImperial ? `${d.length} x ${d.width} ft, ${d.thickness} in thick` : `${d.length} x ${d.width} m, ${d.thickness} cm thick`;
    if (scope) {
      scope.innerHTML = `Benchmarking <strong>${esc(dimTxt)}</strong>${d.quantity > 1 ? ` (x${d.quantity})` : ''}, ${Math.round(d.areaSqFt).toLocaleString('en-US')} sq ft, ${esc(region.name)} rates.`;
    }

    const quote = parseFloat($('#bid-total').value);
    if (!r.valid || !(d.areaSqFt > 0)) {
      box.innerHTML = '<p class="calc-field-note">Enter a length, width and thickness above zero to get a benchmark for this slab.</p>';
      return;
    }
    if (!(quote > 0) || !(b.fair > 0)) {
      box.innerHTML = `<p class="calc-field-note">Benchmark for this slab: <strong>${money(b.fair)}</strong> before overhead and profit; a fair quote usually lands between <strong>${money(b.fair)}</strong> and <strong>${money(b.high)}</strong>.</p>`;
      return;
    }

    let tier;
    if (quote < b.materials) tier = BID_TIERS[0];
    else if (quote <= b.fair) tier = BID_TIERS[1];
    else if (quote <= b.high) tier = BID_TIERS[2];
    else if (quote <= b.overLine) tier = BID_TIERS[3];
    else tier = BID_TIERS[4];

    // Meter: five equal zones; the marker is interpolated inside its zone.
    const edges = [0, b.materials, b.fair, b.high, b.overLine, b.overLine * 1.3];
    const zi = BID_TIERS.indexOf(tier);
    const lo = edges[zi];
    const hi = edges[zi + 1];
    const frac = Math.min(1, Math.max(0, (quote - lo) / Math.max(1, hi - lo)));
    const pos = (zi + frac) / 5 * 100;
    const diffPct = (quote - b.fair) / b.fair * 100;
    const perSqFt = quote / d.areaSqFt;

    let html = `<div class="bid-grade ${tier.cls}"><span class="bid-grade-dot"></span>${esc(tier.label)}</div>`;
    html += `<div class="bid-meter" aria-hidden="true">${BID_TIERS.map(t => `<span class="bid-zone ${t.cls}"></span>`).join('')}<span class="bid-marker" style="left:${pos.toFixed(1)}%"></span></div>`;
    html += `<div class="bid-meter-labels"><span style="left:20%">${money(b.materials)}<br>materials</span><span style="left:40%">${money(b.fair)}<br>benchmark</span><span style="left:60%">${money(b.high)}<br>+25%</span><span style="left:80%">${money(b.overLine)}<br>+62.5%</span></div>`;
    html += `<p class="bid-verdict">Your quote of <strong>${money(quote)}</strong> (${money(perSqFt, 2)}/sq ft) is <strong>${Math.abs(diffPct).toFixed(0)}% ${diffPct >= 0 ? 'above' : 'below'}</strong> the ${money(b.fair)} benchmark. `;
    if (tier.key === 'low') html += 'That is less than the materials alone cost at your prices, so ask what is left out (base, rebar, finishing, cleanup) and whether the price is in writing.';
    else if (tier.key === 'great') html += 'It covers materials and standard labor with little or no margin. Confirm the scope and the crew\'s references.';
    else if (tier.key === 'fair') html += 'That fits a normal 10 to 25% contractor overhead and profit.';
    else if (tier.key === 'premium') html += 'Hard access, a pump, a rush date or a small-job minimum can explain this. Ask for an itemized bid.';
    else html += 'That is more than 30% above the high end of the normal range. Get at least two more quotes.';
    html += '</p>';

    // Where the money sits
    const rows = [
      ['material', 'Concrete & reinforcement', '#bid-item-material'],
      ['labor', 'Labor (place & finish)', '#bid-item-labor'],
      ['base', 'Site prep, base, forms & vapor barrier', '#bid-item-base'],
      ['other', 'Other (finish, delivery, pump, demo)', '#bid-item-other']
    ];
    const entered = rows.map(rw => parseFloat($(rw[2]).value));
    const anyItem = entered.some(v => v > 0);
    html += '<table class="boq-table bid-table"><thead><tr><th>Scope</th><th style="text-align:right;">Benchmark</th>';
    html += anyItem ? '<th style="text-align:right;">Bid</th><th style="text-align:right;">Difference</th>' : '<th style="text-align:right;">Share</th>';
    html += '</tr></thead><tbody>';
    let worst = null;
    rows.forEach((rw, i) => {
      const bench = b.cats[rw[0]];
      html += `<tr><td>${esc(rw[1])}</td><td style="text-align:right;">${money(bench)}</td>`;
      if (anyItem) {
        const v = entered[i];
        if (v > 0) {
          const diff = v - bench;
          const pct = bench > 0 ? diff / bench * 100 : null;
          const flag = pct !== null && pct > 30;
          if (flag && (!worst || diff > worst.diff)) worst = { name: rw[1], diff: diff, pct: pct };
          html += `<td style="text-align:right;">${money(v)}</td><td style="text-align:right;" class="${flag ? 'bid-flag' : ''}">${diff >= 0 ? '+' : '-'}${money(Math.abs(diff))}${pct !== null ? ` (${pct >= 0 ? '+' : ''}${pct.toFixed(0)}%)` : ''}</td>`;
        } else {
          html += '<td style="text-align:right;">-</td><td></td>';
        }
      } else {
        html += `<td style="text-align:right;">${(bench / b.fair * 100).toFixed(0)}%</td>`;
      }
      html += '</tr>';
    });
    html += `<tr class="total-row"><td><strong>Total</strong></td><td style="text-align:right;"><strong>${money(b.fair)}</strong></td>`;
    html += anyItem ? `<td style="text-align:right;"><strong>${money(quote)}</strong></td><td style="text-align:right;"><strong>${quote - b.fair >= 0 ? '+' : '-'}${money(Math.abs(quote - b.fair))}</strong></td>` : '<td style="text-align:right;">100%</td>';
    html += '</tr></tbody></table>';
    if (anyItem && worst) {
      html += `<p class="bid-verdict">Most of the markup sits in <strong>${esc(worst.name)}</strong>: ${money(worst.diff)} (${worst.pct.toFixed(0)}%) over the benchmark. Ask the contractor to explain that line.</p>`;
    } else if (!anyItem && quote > b.high) {
      html += `<p class="calc-field-note">The bid is ${money(quote - b.fair)} over the benchmark. Enter its line items above to see which part carries the markup.</p>`;
    }
    box.innerHTML = html;
  }

  /* ======================= 4. POUR-DAY WEATHER ========================== */

  // Surface evaporation rate, Uno (1998) form of the Menzel/ACI 305
  // nomograph: E [kg/m2/h] = 5((Tc+18)^2.5 - r(Ta+18)^2.5)(V+4) x 1e-6,
  // T in C, r = RH/100, V wind in km/h. Concrete temp taken equal to air.
  function evaporationRate(tempC, rh, windKmh) {
    const t = Math.pow(tempC + 18, 2.5);
    const e = 5 * (t - (rh / 100) * t) * (windKmh + 4) * 1e-6;
    return Math.max(0, e);
  }

  function renderWeather() {
    const box = $('#wx-result');
    if (!box) return;
    const imp = !lastResult || lastResult.isImperial;
    const tIn = parseFloat($('#wx-temp').value);
    const rh = Math.min(100, Math.max(0, parseFloat($('#wx-rh').value) || 0));
    const wind = Math.max(0, parseFloat($('#wx-wind').value) || 0);
    if (isNaN(tIn)) { box.innerHTML = ''; return; }
    const tF = imp ? tIn : tIn * 9 / 5 + 32;
    const tC = imp ? (tIn - 32) * 5 / 9 : tIn;
    const windKmh = imp ? wind * 1.609344 : wind;
    const eKg = evaporationRate(tC, rh, windKmh);
    const eLb = eKg * 0.2048;

    const notices = [];
    const lim = `${WX.concreteLimit[0]} to ${WX.concreteLimit[1]}°F`;
    if (tF <= WX.cold) {
      notices.push(['wx-cold', 'Cold-weather concreting (ACI 306R)',
        `At or below ${WX.cold}°F (${fToC(WX.cold)}°C) ACI 306R cold-weather practice applies. Cold weather slows cement hydration. Use Type III high-early cement or a non-chloride accelerator (calcium chloride corrodes rebar and mesh), never place on frozen or frosted ground, and cover with insulated curing blankets for 3 to 7 days. The concrete must not freeze before it reaches about 500 psi, which takes roughly 2 days at 50°F; ACI 306R calls for keeping thin slabs at 55°F or warmer through that period.`]);
    } else if (tF < WX.cool) {
      notices.push(['wx-cool', 'Cool-weather pouring notice',
        `Below ${WX.cool}°F (${fToC(WX.cool)}°C) cement hydration slows sharply, so set and saw-cut times stretch and early strength drops. Recommend Type III high-early cement or a non-chloride accelerator plus thermal curing blankets for 3 to 7 days to prevent freezing if nights drop toward ${WX.cold}°F (${fToC(WX.cold)}°C), where ACI 306R cold-weather rules begin.`]);
    } else if (tF > WX.hot) {
      notices.push(['wx-hot', 'Hot-weather concreting (ACI 305R)',
        'High heat causes rapid moisture evaporation and plastic shrinkage cracking, speeds up setting and lowers long-term strength. Recommend pouring at dawn, using a set retarder, wetting the base (no standing water), and applying curing compound right after finishing. ' + (tF > WX.concreteLimit[1] ? `Above ${WX.concreteLimit[1]}°F many specifications limit fresh concrete temperature to ${lim}; ask the plant about chilled water or ice in the mix.` : `Many specifications limit fresh concrete to ${lim} at placement.`)]);
    } else {
      notices.push(['wx-good', 'Good pouring temperature',
        `Between ${WX.cool}°F and ${WX.hot}°F (${fToC(WX.cool)} to ${fToC(WX.hot)}°C) concrete sets and gains strength normally. Keep the surface moist or use curing compound for at least 7 days.`]);
    }

    let evapCls = 'wx-good';
    let evapTxt = 'Low plastic shrinkage risk.';
    if (eLb >= WX.evap) {
      evapCls = 'wx-hot';
      evapTxt = 'Above the 0.2 lb/ft²/h (1.0 kg/m²/h) level where ACI 305R expects plastic shrinkage cracking. Use windbreaks or sunshades, fog-mist or spray an evaporation retarder between finishing passes, and start curing immediately.';
    } else if (eLb >= 0.1) {
      evapCls = 'wx-cool';
      evapTxt = 'Moderate. Above 0.1 lb/ft²/h, low water-cement mixes and fiber-free slabs can still crack; keep evaporation retarder and curing compound on hand.';
    }
    const evapVal = imp ? `${eLb.toFixed(2)} lb/ft²/h` : `${eKg.toFixed(2)} kg/m²/h`;
    notices.push([evapCls, `Evaporation rate: ${evapVal}`, evapTxt + ' (Estimate assumes concrete temperature equals air temperature; warmer concrete evaporates faster.)']);

    box.innerHTML = notices.map(n => `<div class="wx-notice ${n[0]}"><strong>${esc(n[1])}</strong><p>${esc(n[2])}</p></div>`).join('');
  }

  function bindWeather() {
    ['#wx-temp', '#wx-rh', '#wx-wind'].forEach(id => {
      const el = $(id);
      if (el) el.addEventListener('input', renderWeather);
    });
    root.querySelectorAll('[data-wx]').forEach(btn => {
      btn.addEventListener('click', () => {
        const imp = !lastResult || lastResult.isImperial;
        const presets = { cold: [38, 70, 8], mild: [70, 50, 8], hot: [95, 25, 15] };
        const p = presets[btn.getAttribute('data-wx')];
        $('#wx-temp').value = imp ? p[0] : Math.round((p[0] - 32) * 5 / 9);
        $('#wx-rh').value = p[1];
        $('#wx-wind').value = imp ? p[2] : Math.round(p[2] * 1.609344);
        renderWeather();
      });
    });
  }

  function syncWeatherUnits(imp) {
    const unitWas = root.getAttribute('data-wx-unit') || 'imperial';
    const unitNow = imp ? 'imperial' : 'metric';
    if (unitWas === unitNow) return;
    const t = $('#wx-temp');
    const w = $('#wx-wind');
    const tv = parseFloat(t.value);
    const wv = parseFloat(w.value);
    if (!isNaN(tv)) t.value = imp ? Math.round(tv * 9 / 5 + 32) : Math.round((tv - 32) * 5 / 9);
    if (!isNaN(wv)) w.value = imp ? Math.round(wv / 1.609344) : Math.round(wv * 1.609344);
    root.querySelectorAll('.wx-temp-unit').forEach(el => { el.textContent = imp ? '°F' : '°C'; });
    root.querySelectorAll('.wx-wind-unit').forEach(el => { el.textContent = imp ? 'mph' : 'km/h'; });
    root.setAttribute('data-wx-unit', unitNow);
  }

  /* ========================= LIVE ESTIMATE PANEL ========================= */
  // The results column is a sticky panel beside the inputs (desktop) and a
  // bottom sheet opened from a fixed estimate bar (phones). Everything here
  // only displays figures from the engine's result object.

  const SHEET_MAX = 900; // px; matches the single-column breakpoint in calculator.css
  let prevRows = null;
  let prevScope = null;
  let prevTotal = null;
  let calcInView = false;
  let panelInView = false;

  function isPhoneLayout() { return window.innerWidth <= SHEET_MAX; }

  function flash(el) {
    if (!el) return;
    el.classList.remove('is-flash');
    void el.offsetWidth; // restart the animation
    el.classList.add('is-flash');
  }

  function scopeCosts(r) {
    const d = r.detail;
    return {
      site: d.excavation.cost + d.gravel.cost,
      forms: d.forms.cost + d.vaporBarrier.cost,
      reinforce: d.reinforcement.cost,
      finish: d.finishing.cost,
      labor: d.labor.cost,
      delivery: d.delivery.cost + d.pump.cost + d.equipment.cost,
      demo: d.demolition.cost
    };
  }

  // Line items are matched by category + the label before its "(qty)" part,
  // so a row flashes when its own cost moves.
  function rowKey(it) { return it.category + '|' + String(it.name).split(' (')[0]; }

  function renderLivePanel() {
    const r = lastResult;
    if (!r) return;
    const total = r.costs.totalEstimatedCost;

    const costs = scopeCosts(r);
    root.querySelectorAll('[data-scope-cost]').forEach(el => {
      const key = el.getAttribute('data-scope-cost');
      const v = costs[key] || 0;
      el.textContent = v > 0 ? money(v) : 'Not included';
      el.classList.toggle('is-zero', !(v > 0));
      if (prevScope && Math.abs((prevScope[key] || 0) - v) > 0.005) flash(el);
    });

    const rows = {};
    r.items.forEach(it => { rows[rowKey(it)] = (rows[rowKey(it)] || 0) + it.cost; });
    const body = $('#boq-table-body');
    if (body && prevRows) {
      const trs = body.querySelectorAll('tr');
      r.items.forEach((it, i) => {
        const k = rowKey(it);
        if (!(k in prevRows) || Math.abs(prevRows[k] - rows[k]) > 0.005) flash(trs[i]);
      });
    }

    const bt = $('#bar-total');
    const by = $('#bar-yards');
    const bb = $('#bar-bags');
    if (bt) bt.textContent = r.valid ? money(total) : '$0';
    if (by) by.textContent = r.isImperial ? `${r.dimensions.orderCubicYds.toFixed(2)} yd³` : `${r.dimensions.orderCubicM.toFixed(2)} m³`;
    if (bb) bb.textContent = `${r.bags.totalBags} bags`;
    if (prevTotal !== null && Math.abs(prevTotal - total) > 0.005) {
      flash($('#out-total-cost'));
      flash(bt);
    }

    prevRows = rows;
    prevScope = costs;
    prevTotal = total;
  }

  function selectTab(key) {
    const tab = $('#tab-' + key);
    if (tab) tab.click();
  }

  function setBarVisible() {
    const bar = $('#live-bar');
    if (!bar) return;
    const show = isPhoneLayout() && calcInView && !panelInView && !root.classList.contains('sheet-open');
    bar.classList.toggle('is-visible', show);
    bar.setAttribute('aria-hidden', show ? 'false' : 'true');
    const btn = bar.querySelector('.live-bar-btn');
    if (btn) btn.tabIndex = show ? 0 : -1;
    document.body.classList.toggle('has-live-bar', show);
  }

  function openSheet(tabKey) {
    if (tabKey) selectTab(tabKey);
    if (!isPhoneLayout()) {
      const panel = $('#live-estimate');
      if (panel) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
      return;
    }
    root.classList.add('sheet-open');
    document.documentElement.classList.add('slab-sheet-lock');
    const bd = $('.live-sheet-backdrop');
    if (bd) bd.hidden = false;
    const btn = $('.live-bar-btn');
    if (btn) btn.setAttribute('aria-expanded', 'true');
    setBarVisible();
    const close = $('.live-sheet-close');
    if (close) close.focus({ preventScroll: true });
  }

  function closeSheet() {
    if (!root.classList.contains('sheet-open')) return;
    root.classList.remove('sheet-open');
    document.documentElement.classList.remove('slab-sheet-lock');
    const bd = $('.live-sheet-backdrop');
    if (bd) bd.hidden = true;
    const btn = $('.live-bar-btn');
    if (btn) btn.setAttribute('aria-expanded', 'false');
    setBarVisible();
  }

  // Opens one Live Estimate tab from links such as the homepage's "Got a
  // quote?" button and tools strip (data-open-tab="bid|blueprint|...") or a
  // #check-quote URL. Phones get the bottom sheet.
  function openTab(key) {
    if (isPhoneLayout()) {
      openSheet(key);
    } else {
      selectTab(key);
      const panel = $('#live-estimate');
      if (panel) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    if (key === 'bid') {
      const input = $('#bid-total');
      if (input) setTimeout(() => input.focus({ preventScroll: true }), 350);
    }
  }

  function bindLivePanel() {
    const barBtn = $('.live-bar-btn');
    if (barBtn) barBtn.addEventListener('click', () => openSheet());
    const close = $('.live-sheet-close');
    if (close) close.addEventListener('click', closeSheet);
    const bd = $('.live-sheet-backdrop');
    if (bd) bd.addEventListener('click', closeSheet);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeSheet(); });
    window.addEventListener('resize', () => {
      if (!isPhoneLayout()) closeSheet();
      setBarVisible();
    });

    document.querySelectorAll('[data-open-tab]').forEach(a => a.addEventListener('click', (ev) => {
      const key = a.getAttribute('data-open-tab');
      if (!$('#tab-' + key)) return;
      ev.preventDefault();
      openTab(key);
    }));
    if (window.location.hash === '#check-quote') setTimeout(() => openTab('bid'), 300);

    if ('IntersectionObserver' in window) {
      const panel = $('#live-estimate');
      new IntersectionObserver((entries) => {
        entries.forEach(en => { calcInView = en.isIntersecting; });
        setBarVisible();
      }).observe(root.querySelector('.calc-inputs-col') || root);
      if (panel) {
        new IntersectionObserver((entries) => {
          entries.forEach(en => { panelInView = en.isIntersecting && !root.classList.contains('sheet-open'); });
          setBarVisible();
        }, { threshold: 0.15 }).observe(panel.querySelector('.live-summary') || panel);
      }
    }
  }

  /* ============================== TABS ================================= */

  function bindTabs() {
    const tabs = Array.prototype.slice.call(root.querySelectorAll('.calc-tools-tab'));
    const select = (tab) => {
      tabs.forEach(t => {
        const on = t === tab;
        t.classList.toggle('active', on);
        t.setAttribute('aria-selected', on ? 'true' : 'false');
        t.tabIndex = on ? 0 : -1;
        const panel = document.getElementById(t.getAttribute('aria-controls'));
        if (panel) panel.hidden = !on;
      });
    };
    tabs.forEach((tab, i) => {
      tab.addEventListener('click', () => select(tab));
      tab.addEventListener('keydown', (e) => {
        if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
        const next = tabs[(i + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
        select(next);
        next.focus();
      });
    });
  }

  /* ============================== INIT ================================= */

  function onUpdate(e) {
    lastResult = e.detail;
    const spacing = $('#calc-rebar-spacing');
    if (spacing) spacing.disabled = lastResult.detail.reinforcement.type !== 'rebar';
    syncWeatherUnits(lastResult.isImperial);
    renderBlueprint();
    renderBid();
    renderWeather();
    renderLivePanel();
  }

  // Register before DOMContentLoaded so the engine's first result is caught.
  document.addEventListener('slabcalc:updated', function (e) {
    if (!root) return;
    onUpdate(e);
  });

  function init() {
    root = document.getElementById('concrete-slab-calculator');
    if (!root || !document.getElementById('calc-tools')) { root = null; return; }
    buildRegionSelect();
    bindTabs();
    bindWeather();
    bindLivePanel();
    ['#bid-total', '#bid-item-material', '#bid-item-labor', '#bid-item-base', '#bid-item-other'].forEach(id => {
      const el = $(id);
      if (el) el.addEventListener('input', renderBid);
    });
    const png = $('#btn-blueprint-png');
    if (png) png.addEventListener('click', downloadBlueprintPNG);
    const reset = $('#btn-calc-reset');
    if (reset) {
      reset.addEventListener('click', () => {
        const sel = $('#calc-region');
        if (sel) sel.value = 'us';
        applyRegion('us');
      });
    }
    window.SlabCalculatorRegion = { key: 'us', name: REGIONS.us.name, index: 1 };
    const engine = window.SlabCalculatorEngine;
    if (engine && engine.getLastResult && engine.getLastResult()) onUpdate({ detail: engine.getLastResult() });
  }

  // This file is enqueued after primary-calculator.js (both deferred), so
  // its DOMContentLoaded handler runs after the engine's first calculation.
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
