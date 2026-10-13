<?php
/**
 * EMBEDDABLE CALCULATOR — /embed/concrete-calculator/ and /embed-concrete-calculator/
 *
 * /embed/concrete-calculator/ is a small stand-alone page (no theme header,
 * footer or tracking) that other sites show in an iframe: slab size and
 * state in, concrete, bags and an installed and DIY price range out. It is
 * the only URL on the site that may be framed by other sites, and it is
 * kept out of search results (noindex, canonical to the info page).
 *
 * /embed-concrete-calculator/ explains the widget and gives the code to
 * copy: the iframe plus a plain credit link to the site, which is what
 * carries the attribution on the host page.
 *
 * The widget's maths is Slab_Data::size_costs() in JavaScript, with the
 * same figures handed over from Slab_Data, so it always matches the
 * calculator, the size pages and the state pages.
 *
 * @package SlabCostCalculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Slab_Embed_Widget {

    public static function init() {
        add_action('template_redirect', [__CLASS__, 'render_frame'], 0);
    }

    public static function info_path() {
        return '/embed-concrete-calculator/';
    }

    public static function frame_path() {
        return '/embed/concrete-calculator/';
    }

    /** True while serving the iframe page (usable from send_headers, before the main query). */
    public static function is_frame_request() {
        global $wp;
        return isset($wp->query_vars['slab_route']) && $wp->query_vars['slab_route'] === 'embed_frame';
    }

    /** The figures the widget needs, all from Slab_Data. */
    public static function config() {
        $d = Slab_Data::all();
        return [
            'readyMix' => $d['concrete']['ready_mix_default'],
            'deliveryFee' => $d['delivery']['fee_default'],
            'wastePct' => $d['concrete']['waste_pct'],
            'bagYields' => $d['concrete']['bag_yields'],
            'bagPrice' => $d['concrete']['bag_price_default'],
            'forms' => $d['site']['forms_default'],
            'vapor' => $d['site']['vapor_default'],
            'rebar' => $d['reinforcement']['rebar_default'],
            'broom' => $d['finish']['broom']['default'],
            'laborLow' => $d['labor']['sqft_range'][0],
            'labor' => $d['labor']['sqft_default'],
            'overheadProfit' => $d['overhead_profit'],
        ];
    }

    /** The code site owners copy. */
    public static function snippet($state = '') {
        $src = home_url(self::frame_path()) . ($state ? '?state=' . strtolower($state) : '');
        return '<iframe src="' . esc_url($src) . '" title="Concrete slab calculator" width="100%" height="560" style="border:0;max-width:640px;" loading="lazy"></iframe>' . "\n"
            . '<p style="font-size:13px;margin:4px 0 0;">Concrete calculator by <a href="' . esc_url(home_url('/')) . '">SlabCostCalculator.com</a></p>';
    }

    public static function faqs() {
        return [
            ['q' => 'Is the concrete calculator widget free?', 'a' => 'Yes. Copy the code into any page; there is no sign-up, no fee and no tracking in the widget. Keep the credit link under it so readers can find the full calculator.'],
            ['q' => 'Will the prices in the widget stay up to date?', 'a' => 'Yes. The widget loads from our site, so when we update our prices it updates on your page too, with the same figures as our calculator and price report.'],
            ['q' => 'Can I set the widget to my state?', 'a' => 'Yes. Pick a state above and the code changes to open on that state. Visitors can still pick another state in the widget.'],
            ['q' => 'Does the widget work on WordPress, Wix and Squarespace?', 'a' => 'Yes, anywhere you can add an HTML or embed block. In WordPress, paste the code into a Custom HTML block.'],
        ];
    }

    /** /embed/concrete-calculator/: the stand-alone widget page. */
    public static function render_frame() {
        if (get_query_var('slab_route') !== 'embed_frame') {
            return;
        }
        $states = Slab_Data::states();
        $metros = Slab_Data::metros();
        $start = isset($_GET['state']) ? strtoupper(sanitize_key(wp_unslash($_GET['state']))) : '';
        if (!isset($states[$start])) {
            $start = '';
        }
        $home = home_url('/');
        status_header(200);
        header('Content-Type: text/html; charset=utf-8');
        header('X-Robots-Tag: noindex, follow');
        ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, follow">
<link rel="canonical" href="<?php echo esc_url(home_url(self::info_path())); ?>">
<title>Concrete Slab Calculator | SlabCostCalculator.com</title>
<style>
*{box-sizing:border-box}
body{margin:0;font:15px/1.45 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:#1f2937;background:#fff}
.w{border:1px solid #d1d5db;border-radius:10px;padding:16px;max-width:640px}
h1{font-size:18px;margin:0 0 12px;color:#111827}
.g{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
label{display:block;font-size:12px;font-weight:600;color:#4b5563;margin-bottom:4px}
input,select{width:100%;padding:8px 10px;border:1px solid #9ca3af;border-radius:6px;font:inherit;color:#111827;background:#fff}
input:focus,select:focus{outline:2px solid #2563eb;outline-offset:1px}
.loc{margin-top:10px}
.r{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:14px}
.c{background:#f3f4f6;border-radius:8px;padding:10px 12px}
.c.hl{background:#eff6ff;border:1px solid #bfdbfe}
.k{font-size:12px;color:#4b5563}
.v{font-size:20px;font-weight:700;color:#111827}
.c.hl .v{color:#1d4ed8}
.n{font-size:12px;color:#4b5563}
.f{display:flex;justify-content:space-between;flex-wrap:wrap;gap:6px;margin-top:12px;font-size:12px;color:#4b5563}
a{color:#1d4ed8}
@media (max-width:480px){.w{padding:12px}.g,.r{gap:8px}label{font-size:11px}input,select{padding:7px 8px}.c{padding:8px 10px}.v{font-size:16px}.n{font-size:11px}}
</style>
</head>
<body>
<div class="w" id="w">
  <h1>Concrete Slab Calculator</h1>
  <div class="g">
    <div><label for="l">Length (ft)</label><input id="l" type="number" min="1" max="500" step="0.5" value="20" inputmode="decimal"></div>
    <div><label for="wd">Width (ft)</label><input id="wd" type="number" min="1" max="500" step="0.5" value="20" inputmode="decimal"></div>
    <div><label for="t">Thickness (in)</label><input id="t" type="number" min="2" max="12" step="0.5" value="4" inputmode="decimal"></div>
  </div>
  <div class="loc"><label for="s">Location</label>
    <select id="s">
      <option value="1">U.S. average</option>
      <optgroup label="States">
        <?php foreach ($states as $code => $st) : ?>
        <option value="<?php echo esc_attr($st['index']); ?>" data-hash="state-<?php echo esc_attr(strtolower($code)); ?>"<?php selected($start, $code); ?>><?php echo esc_html($st['name']); ?></option>
        <?php endforeach; ?>
      </optgroup>
      <optgroup label="Metro areas">
        <?php foreach ($metros as $m) : ?>
        <option value="<?php echo esc_attr($m['index']); ?>" data-hash="metro-<?php echo esc_attr($m['key']); ?>"><?php echo esc_html($m['name']); ?></option>
        <?php endforeach; ?>
      </optgroup>
    </select>
  </div>
  <div class="r" aria-live="polite">
    <div class="c"><div class="k">Concrete</div><div class="v" id="o-yd">–</div><div class="n" id="o-net"></div></div>
    <div class="c"><div class="k">Or bags of mix</div><div class="v" id="o-bags">–</div><div class="n" id="o-bags60"></div></div>
    <div class="c hl"><div class="k">Installed cost</div><div class="v" id="o-inst">–</div><div class="n" id="o-psf"></div></div>
    <div class="c"><div class="k">DIY materials</div><div class="v" id="o-diy">–</div><div class="n">concrete and forms, up to rebar</div></div>
  </div>
  <div class="f">
    <a id="full" href="<?php echo esc_url($home); ?>" target="_blank" rel="noopener">Full estimate with rebar, gravel &amp; labor &rarr;</a>
    <span>Powered by <a href="<?php echo esc_url($home); ?>" target="_blank" rel="noopener">SlabCostCalculator.com</a></span>
  </div>
</div>
<script>
(function () {
  var C = <?php echo wp_json_encode(self::config()); ?>;
  var HOME = <?php echo wp_json_encode($home); ?>;
  function $(id) { return document.getElementById(id); }
  function num(id, lo, hi) { var v = parseFloat($(id).value); return isFinite(v) && v >= lo && v <= hi ? v : null; }
  function r10(n) { return Math.round(n / 10) * 10; }
  function bags(cuft, size) { return Math.ceil(Math.round(cuft / C.bagYields[size] * 1e6) / 1e6); }
  function money(n) { return '$' + Math.round(n).toLocaleString('en-US'); }
  function range(a, b) { return money(a) + ' – ' + money(b); }
  // Same line items as Slab_Data::size_costs() in inc/site-data.php.
  function calc(l, w, t, idx) {
    var area = l * w, perim = 2 * (l + w);
    var orderCuft = area * t / 12 * (1 + C.wastePct / 100), orderYd = orderCuft / 27;
    var b80 = bags(orderCuft, '80');
    var readyMix = orderYd * C.readyMix * idx + C.deliveryFee;
    var forms = perim * C.forms, vapor = area * C.vapor, rebar = area * C.rebar, broom = area * C.broom;
    var basic = readyMix + forms + broom + area * C.laborLow * idx;
    var full = readyMix + forms + broom + area * C.labor * idx + vapor + rebar;
    var inst = [basic, full * (1 + C.overheadProfit)];
    var diyLo = Math.min(readyMix, b80 * C.bagPrice) + forms;
    return {
      area: area, netYd: area * t / 12 / 27, orderYd: orderYd, bags80: b80, bags60: bags(orderCuft, '60'),
      installed: [r10(inst[0]), r10(inst[1])], psf: [inst[0] / area, inst[1] / area],
      diy: [r10(diyLo), r10(diyLo + vapor + rebar)]
    };
  }
  function update() {
    var l = num('l', 1, 500), w = num('wd', 1, 500), t = num('t', 2, 12);
    var sel = $('s'), idx = parseFloat(sel.value) || 1;
    var hash = sel.options[sel.selectedIndex].getAttribute('data-hash');
    $('full').href = HOME + '#' + (hash || 'concrete-slab-calculator');
    if (l === null || w === null || t === null) {
      ['o-yd', 'o-bags', 'o-inst', 'o-diy'].forEach(function (id) { $(id).textContent = '–'; });
      $('o-net').textContent = 'Enter a size'; $('o-bags60').textContent = ''; $('o-psf').textContent = '';
      return;
    }
    var r = calc(l, w, t, idx);
    $('o-yd').textContent = r.orderYd.toFixed(2) + ' yd³';
    $('o-net').textContent = r.netYd.toFixed(2) + ' yd³ net + ' + C.wastePct + '% waste';
    $('o-bags').textContent = r.bags80.toLocaleString('en-US') + ' × 80 lb';
    $('o-bags60').textContent = 'or ' + r.bags60.toLocaleString('en-US') + ' × 60 lb';
    $('o-inst').textContent = range(r.installed[0], r.installed[1]);
    $('o-psf').textContent = '$' + r.psf[0].toFixed(2) + ' – $' + r.psf[1].toFixed(2) + ' per sq ft';
    $('o-diy').textContent = range(r.diy[0], r.diy[1]);
  }
  ['l', 'wd', 't', 's'].forEach(function (id) { $(id).addEventListener('input', update); $(id).addEventListener('change', update); });
  update();
})();
</script>
</body>
</html>
<?php
        exit;
    }
}

Slab_Embed_Widget::init();
