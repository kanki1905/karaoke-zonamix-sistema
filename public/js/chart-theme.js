/* KZM — tema de gráficos (Chart.js) a partir de los tokens CSS. Sin colores literales.
   · kzmChartPalette()      paleta del tema actual (cacheada por tema)
   · kzmChartColor(key)     color "scriptable" para datasets (se reevalúa en update())
   · kzmChartColors(keys)   ídem, uno por elemento (pie/doughnut)
   · kzmApplyChartTheme()   ajusta Chart.defaults y refresca las gráficas existentes
   · kzmCaptureCharts(ids)  imágenes PNG de los canvas con paleta de papel (para PDF/Excel)
   · kzmPaperColors()       colores de papel (tema claro) en rgb[] y argb para PDF/Excel
   Reaplica el tema con un MutationObserver sobre html[data-theme]. */
(function () {
  'use strict';
  var cache = null, cacheKey = null, paperScope = null, forcePaper = false;

  function lightScope() {
    if (!paperScope) {
      paperScope = document.createElement('div');
      paperScope.setAttribute('data-theme', 'light');   /* isla de tema claro (ver style.css) */
      paperScope.hidden = true;
      paperScope.setAttribute('aria-hidden', 'true');
      document.body.appendChild(paperScope);
    }
    return paperScope;
  }
  function resolve(name, scope) {
    var host = scope || document.body, el = document.createElement('span');
    el.style.color = 'var(' + name + ')';
    host.appendChild(el);
    var c = getComputedStyle(el).color;
    host.removeChild(el);
    return c;
  }
  function build(scope) {
    var t = function (n) { return resolve(n, scope); }, series = [];
    for (var i = 1; i <= 8; i++) series.push(t('--chart-' + i));
    return {
      text: t('--chart-text'), grid: t('--chart-grid'), surface: t('--surface'), border: t('--border'),
      primary: t('--primary'), series: series,
      success: t('--chart-success'), warning: t('--chart-warning'), danger: t('--chart-danger'), info: t('--chart-info'),
      neutral: t('--chart-8'), accent: t('--accent')
    };
  }
  function key() { return forcePaper ? 'paper' : document.documentElement.getAttribute('data-theme') || 'light'; }
  function palette(scope) {
    if (scope) return build(scope);
    var k = key();
    if (!cache || cacheKey !== k) { cache = build(forcePaper ? lightScope() : null); cache = forcePaper ? sober(cache) : ink(cache); cacheKey = k; }
    return cache;
  }
  /* Pantalla: letras SIEMPRE negras y legibles sobre fondo blanco (también en modo nocturno). */
  function ink(P) {
    P.text = 'rgb(0,0,0)'; P.grid = 'rgb(200,200,200)'; P.border = 'rgb(120,120,120)'; P.surface = 'rgb(255,255,255)';
    return P;
  }
  /* Exportación (PDF/Excel): papel sobrio, texto negro y barras en grises oscuros, siempre legible. */
  function sober(P) {
    var g = ['rgb(17,24,39)', 'rgb(75,85,99)', 'rgb(156,163,175)', 'rgb(209,213,219)', 'rgb(55,65,81)', 'rgb(107,114,128)', 'rgb(229,231,235)', 'rgb(31,41,55)'];
    P.text = 'rgb(0,0,0)'; P.grid = 'rgb(190,190,190)'; P.border = 'rgb(120,120,120)'; P.surface = 'rgb(255,255,255)';
    P.primary = g[0]; P.series = g.slice(); P.accent = g[1];
    P.success = g[0]; P.info = g[1]; P.warning = g[2]; P.danger = g[3]; P.neutral = g[5];
    return P;
  }
  function pick(P, name) {
    if (/^series[1-8]$/.test(name)) return P.series[+name.slice(6) - 1];
    return P[name] || P.primary;
  }

  window.kzmChartPalette = palette;
  window.kzmChartColor = function (name) { return function () { return pick(palette(), name); }; };
  window.kzmChartColors = function (names) { return function (ctx) { return pick(palette(), names[ctx.dataIndex % names.length]); }; };

  var bgPlugin = {
    id: 'kzmCanvasBg',                      /* fondo opaco: el PNG exportado nunca queda transparente */
    beforeDraw: function (chart) {
      var ctx = chart.ctx; ctx.save();
      ctx.globalCompositeOperation = 'destination-over';
      ctx.fillStyle = palette().surface; ctx.fillRect(0, 0, chart.width, chart.height); ctx.restore();
    }
  };

  /* Etiquetas de datos en TODAS las gráficas: cantidad al final de cada barra y, en tortas/donas,
     cantidad + porcentaje dentro de cada porción (sobre una cajita blanca legible con cualquier color). */
  function fmtN(v) { return Number(v).toLocaleString('es-BO', { maximumFractionDigits: 2 }); }
  var labelsPlugin = {
    id: 'kzmValueLabels',
    afterDatasetsDraw: function (chart) {
      var type = chart.config.type, ctx = chart.ctx, P = palette();
      if (type !== 'bar' && type !== 'pie' && type !== 'doughnut') return;
      chart.data.datasets.forEach(function (ds, di) {
        var meta = chart.getDatasetMeta(di); if (!chart.isDatasetVisible(di)) return;
        var total = 0; if (type !== 'bar') ds.data.forEach(function (x) { total += Number(x) || 0; });
        meta.data.forEach(function (el, i) {
          var v = Number(ds.data[i]); if (!isFinite(v) || (type !== 'bar' && v <= 0) || el.hidden) return;
          ctx.save(); ctx.textBaseline = 'middle';
          if (type === 'bar') {
            ctx.font = 'bold 12px sans-serif'; ctx.fillStyle = P.text;
            if (chart.options.indexAxis === 'y') { ctx.textAlign = 'left'; ctx.fillText(fmtN(v), el.x + 6, el.y); }
            else { ctx.textAlign = 'center'; ctx.textBaseline = 'bottom'; ctx.fillText(fmtN(v), el.x, el.y - 4); }
          } else {
            var pct = total ? v * 100 / total : 0; if (pct < 3) { ctx.restore(); return; }
            var a = (el.startAngle + el.endAngle) / 2, r = (el.innerRadius + el.outerRadius) / 2;
            var x = el.x + Math.cos(a) * r, y = el.y + Math.sin(a) * r;
            var txt = fmtN(v) + ' (' + Math.round(pct) + '%)';
            ctx.font = 'bold 12px sans-serif'; var w = ctx.measureText(txt).width + 10, h = 18;
            ctx.fillStyle = 'rgba(255,255,255,.94)'; ctx.strokeStyle = 'rgba(0,0,0,.55)'; ctx.lineWidth = 1;
            ctx.beginPath(); ctx.rect(x - w / 2, y - h / 2, w, h); ctx.fill(); ctx.stroke();
            ctx.fillStyle = '#000'; ctx.textAlign = 'center'; ctx.fillText(txt, x, y + 1);
          }
          ctx.restore();
        });
      });
    }
  };

  /* Leyenda de tortas/donas con nombre + cantidad + porcentaje. */
  window.kzmPieLegend = function (chart) {
    var items = Chart.defaults.plugins.legend.labels.generateLabels(chart), ds = chart.data.datasets[0], total = 0;
    ds.data.forEach(function (x) { total += Number(x) || 0; });
    items.forEach(function (l) {
      var v = Number(ds.data[l.index]) || 0;
      l.text = l.text + ': ' + fmtN(v) + ' (' + (total ? Math.round(v * 100 / total) : 0) + '%)';
    });
    return items;
  };

  window.kzmApplyChartTheme = function () {
    if (!window.Chart) return;
    var P = palette(), d = Chart.defaults;
    d.font.size = 12; d.font.weight = 'bold';
    if (!Chart.registry.plugins.get('kzmValueLabels')) Chart.register(labelsPlugin);
    d.color = P.text; d.borderColor = P.grid;
    d.plugins.legend.labels.color = P.text;
    d.plugins.tooltip.backgroundColor = P.surface; d.plugins.tooltip.titleColor = P.text;
    d.plugins.tooltip.bodyColor = P.text; d.plugins.tooltip.borderColor = P.border; d.plugins.tooltip.borderWidth = 1;
    if (!Chart.registry.plugins.get('kzmCanvasBg')) Chart.register(bgPlugin);
    Object.keys(Chart.instances).forEach(function (id) { Chart.instances[id].update('none'); });
  };

  /* Imágenes con colores de papel, sin alterar lo que ve la persona. */
  window.kzmCaptureCharts = function (ids) {
    var out = {};
    if (!window.Chart) return out;
    forcePaper = true; kzmApplyChartTheme();
    try {
      ids.forEach(function (id) {
        var c = document.getElementById(id);
        try { if (c) out[id] = c.toDataURL('image/png', 1); } catch (e) { /* canvas no exportable */ }
      });
    } finally { forcePaper = false; kzmApplyChartTheme(); }
    return out;
  };

  function parse(rgb) { var n = rgb.match(/[\d.]+/g).map(Number); return [Math.round(n[0]), Math.round(n[1]), Math.round(n[2])]; }
  window.kzmPaperColors = function () {
    var S = lightScope(), c = function (n) { return parse(resolve(n, S)); };
    void c; /* el papel de exportación es sobrio: texto negro sobre blanco y gris claro */
    var rgb = {
      brand: [0, 0, 0], ink: [0, 0, 0], muted: [0, 0, 0], headFill: [226, 228, 232],
      headText: [0, 0, 0], line: [110, 110, 110], altRow: [244, 244, 245], onBrand: [0, 0, 0]
    }, argb = {};
    Object.keys(rgb).forEach(function (k) {
      argb[k] = 'FF' + rgb[k].map(function (v) { return ('0' + v.toString(16)).slice(-2); }).join('').toUpperCase();
    });
    return { rgb: rgb, argb: argb };
  };

  new MutationObserver(function () { window.kzmApplyChartTheme(); })
    .observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
})();
