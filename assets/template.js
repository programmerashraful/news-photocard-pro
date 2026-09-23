(function (window) {
  'use strict';
  function escapeHTML(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (char) {
      return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[char];
    });
  }
  function valueFor(key, data) {
    return key.indexOf('opt.') === 0 ? (data.opts || {})[key.slice(4)] : data[key];
  }
  function fill(source, data, css) {
    return String(source || '').replace(/\{\{\s*(title|date|image|site|opt\.[a-zA-Z0-9_-]+)\s*\}\}/g, function (_, key) {
      const value = valueFor(key, data);
      if (!css) return escapeHTML(value);
      // CSS escapes keep a value inside its declaration/url without changing its appearance.
      return String(value == null ? '' : value).replace(/[<>"'(){};\\\s]/g, function (char) {
        // Parentheses and commas are required for sanitized rgba()/rgb() color values.
        return '\\' + char.charCodeAt(0).toString(16) + ' ';
      });
    });
  }
  function css(source, data) {
    const styles = String(source || '').replace(/\{\{\s*(title|date|image|site|opt\.[a-zA-Z0-9_-]+)\s*\}\}/g, function (_, key) {
      const value = String(valueFor(key, data) == null ? '' : valueFor(key, data));
      if (/^#[a-f\d]{3}(?:[a-f\d]{3})?$/i.test(value) || /^rgba?\([\d\s.,]+\)$/.test(value)) return value;
      return fill('{{' + key + '}}', data, true);
    });
    const overrides = ['title', 'date', 'cta', 'site'].map(function (role) {
      const raw = String((data.opts || {})[role + '_font_size'] || '');
      const size = Number(raw);
      const min = role === 'title' ? 24 : 12;
      const max = role === 'title' ? 120 : 60;
      if (!/^\d+$/.test(raw) || size < min || size > max) return '';
      const selector = '[data-udnpc-text="' + role + '"]';
      return selector + '{font-size:' + size + 'px!important}' +
        selector + '::before,' + selector + '::after{font-size:1em!important}';
    }).join('');
    // All bundled designs declare their family on the card; preserve their weights and sizes.
    return styles.replace(/font-family\s*:\s*["']?SolaimanLipi["']?\s*,\s*sans-serif/gi,
      'font-family:"' + fontFamily(data) + '",sans-serif') + overrides;
  }
  function fontFamily(data) {
    return (data.opts || {}).font_family === 'Shurjo' ? 'Shurjo' : 'SolaimanLipi';
  }
  async function loadFonts(doc, data) {
    if (!doc.fonts) return;
    const family = fontFamily(data);
    // Include Bengali so loading is not limited to Latin glyphs. Load both real Shurjo faces.
    const faces = await Promise.all([400, 700].map(function (weight) {
      return doc.fonts.load(weight + ' 46px "' + family + '"', 'বাংলা সংবাদ Aa ০১২৩ 123');
    }));
    if (faces.some(function (loaded) { return !loaded.length; })) throw new Error('Photo card font unavailable');
    await doc.fonts.ready;
  }
  window.UDNPCTemplate = {html: function (source, data) {return fill(source, data, false);}, css: css, escapeHTML: escapeHTML, fontFamily: fontFamily, loadFonts: loadFonts};
})(window);
