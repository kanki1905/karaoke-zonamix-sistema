/* KZM — switch de tema claro/oscuro (única implementación).
   Mecanismo: html[data-theme="light|dark"] + localStorage['kzm-theme'].
   El valor inicial lo fija el snippet de <head> (themeHeadTag) antes del primer
   paint; este archivo gestiona el botón .theme-toggle y avisa a los demás
   componentes (p. ej. gráficos) con el evento "kzm:themechange". */
(function () {
  'use strict';
  if (window.__kzmThemeInitialized) return;
  window.__kzmThemeInitialized = true;

  var KEY = 'kzm-theme';
  var root = document.documentElement;
  var valid = function (v) { return v === 'dark' || v === 'light'; };

  function systemTheme() {
    try { return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'; }
    catch (_) { return 'light'; }
  }
  function storedTheme() {
    try { var v = localStorage.getItem(KEY); return valid(v) ? v : null; }
    catch (_) { return null; }
  }
  function current() {
    var t = root.getAttribute('data-theme');
    return valid(t) ? t : (storedTheme() || systemTheme());
  }
  function refreshButtons() {
    var dark = current() === 'dark';
    document.querySelectorAll('.theme-toggle').forEach(function (b) {
      var icon = document.createElement('span'), label = document.createElement('span');
      icon.className = 'theme-toggle__icon'; icon.setAttribute('aria-hidden', 'true'); icon.textContent = dark ? '☀️' : '🌙';
      label.className = 'theme-toggle__label'; label.textContent = dark ? 'Modo claro' : 'Modo nocturno';
      b.textContent = ''; b.appendChild(icon); b.appendChild(label);
      b.setAttribute('aria-pressed', dark ? 'true' : 'false');
      b.setAttribute('title', dark ? 'Cambiar al modo claro' : 'Cambiar al modo nocturno');
    });
  }
  function apply(theme, persist) {
    root.setAttribute('data-theme', theme);
    if (persist) { try { localStorage.setItem(KEY, theme); } catch (_) {} }
    refreshButtons();
    document.dispatchEvent(new CustomEvent('kzm:themechange', { detail: { theme: theme } }));
  }

  /* Sin preferencia guardada, seguir al sistema mientras la persona no elija. */
  try {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
      if (!storedTheme()) apply(systemTheme(), false);
    });
  } catch (_) {}

  /* Sincroniza otras pestañas e iframes del mismo sitio (p. ej. el chat embebido del admin). */
  window.addEventListener('storage', function (e) {
    if (e.key === KEY && valid(e.newValue) && e.newValue !== current()) apply(e.newValue, false);
  });

  document.addEventListener('click', function (e) {
    if (!e.target.closest('.theme-toggle')) return;
    apply(current() === 'dark' ? 'light' : 'dark', true);
  });

  if (!valid(root.getAttribute('data-theme'))) root.setAttribute('data-theme', storedTheme() || systemTheme());
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', refreshButtons, { once: true });
  else refreshButtons();

  window.kzmTheme = { get: current, set: function (t) { if (valid(t)) apply(t, true); } };
})();
