(function ($) {
  'use strict';
  $(function () {
    const config = window.UDNPCAdmin;
    if (!config || !window.UDNPCTemplate) return;
    const strings = config.i18n;
    const options = JSON.parse(document.getElementById('udnpc-settings-data').textContent);
    const templates = config.templates;
    const $select = $('#udnpc-active-template');
    const $form = $('#udnpc-settings-form');
    const fontsOnly = $form.attr('data-fonts-only') === 'true';
    let active = $select.val();
    let dirty = false;
    let rendering = false;
    let timer;
    const viewport = document.getElementById('udnpc-preview-viewport');
    const frame = document.getElementById('udnpc-preview');
    function markDirty() {
      if (rendering) return;
      dirty = true;
      $('#udnpc-save-status').text(strings.unsaved).addClass('is-dirty');
    }
    function values() {
      if (fontsOnly) return Object.assign({}, options[active]);
      const data = {};
      $('#udnpc-dynamic-fields input[name]').each(function () { data[this.name.slice(4)] = this.value; });
      return data;
    }
    function scalePreview() {
      const width = viewport.clientWidth;
      frame.style.transform = 'scale(' + width / 1080 + ')';
      viewport.style.height = width + 'px';
    }
    function renderPreview() {
      const template = templates[active];
      if (!template) return;
      const opts = Object.assign({}, values());
      opts.font_family = $('#udnpc-font-family').val();
      opts.domain = config.domain;
      const title = $('#udnpc-preview-title').val().trim();
      opts.wc = title ? title.split(/\s+/u).length : 0;
      if (!opts.logo) opts.logo = config.assets + 'preview-logo.svg';
      const data = {title: title, date: config.date, image: config.assets + 'preview-landscape.svg', site: config.site, opts: opts};
      const escape = UDNPCTemplate.escapeHTML;
      $('#udnpc-font-status').text(strings.fontLoading);
      frame.onload = async function () {
        const doc = frame.contentDocument;
        try {
          await UDNPCTemplate.loadFonts(doc, data);
          if (frame.contentDocument === doc) $('#udnpc-font-status').text('');
        } catch (error) {
          if (frame.contentDocument === doc) $('#udnpc-font-status').text(strings.fontError);
        }
      };
      frame.srcdoc = '<!doctype html><html><head><meta charset="utf-8"><meta http-equiv="Content-Security-Policy" content="default-src &apos;none&apos;; img-src http: https: data:; style-src &apos;unsafe-inline&apos; http: https:; font-src http: https:;"><link rel="stylesheet" href="' + escape(config.fontsUrl) + '"><style>html,body{margin:0;padding:0;width:1080px;height:1080px;overflow:hidden}*{box-sizing:border-box}img{max-width:100%}' + UDNPCTemplate.css(template.css, data) + '</style></head><body>' + UDNPCTemplate.html(template.html, data) + '</body></html>';
      $('#udnpc-preview-name').text(template.name.replace(/\s*\(.*/, ''));
      scalePreview();
    }
    function queuePreview() { clearTimeout(timer); timer = setTimeout(renderPreview, 180); }
    function renderFields() {
      rendering = true;
      const $wrap = $('#udnpc-dynamic-fields').empty();
      const groups = {};
      [['font_size', strings.typography], ['image', strings.images], ['color', strings.colors], ['text', strings.text]].forEach(function (group) {
        const $fieldset = $('<fieldset class="udnpc-field-group"/>').append($('<legend/>').text(group[1]));
        groups[group[0]] = $('<div class="udnpc-fields-grid"/>').appendTo($fieldset);
        if (group[0] === 'font_size') $fieldset.addClass('udnpc-typography-group').append($('<p class="udnpc-help"/>').text(strings.sizeHelp));
        $wrap.append($fieldset);
      });
      const schema = templates[active].fields;
      Object.keys(schema).forEach(function (key) {
        const field = schema[key];
        const id = 'tpl_' + key;
        const saved = options[active] || {};
        const value = Object.prototype.hasOwnProperty.call(saved, key) ? saved[key] : (field.default || '');
        const $row = $('<div class="udnpc-field-row"/>');
        const $input = $('<input/>').attr({type: field.type === 'image' ? 'url' : 'text', id: id, name: id}).val(value);
        $row.append($('<label/>').attr('for', id).text(field.label || key));
        if (field.type === 'font_size') {
          const automatic = value === '' || value == null;
          $row.addClass('udnpc-size-row');
          $input.attr({type: 'number', min: field.min, max: field.max, step: 1, placeholder: strings.auto})
            .prop('disabled', automatic).prop('required', !automatic);
          const $range = $('<input type="range"/>').attr({min: field.min, max: field.max, step: 1, 'aria-label': field.label + ' (px)'})
            .val(automatic ? field.suggested : value).prop('disabled', automatic);
          const $auto = $('<input type="checkbox"/>').prop('checked', automatic).attr('aria-label', strings.auto + ': ' + field.label);
          const $autoLabel = $('<label class="udnpc-size-auto"/>').append($auto, $('<span/>').text(strings.auto));
          const $heading = $('<div class="udnpc-size-heading"/>').append($row.children('label'), $autoLabel);
          const $number = $('<div class="udnpc-size-number"/>').append($input, $('<span aria-hidden="true"/>').text('px'));
          $row.append($heading, $('<div class="udnpc-size-controls"/>').append($range, $number));
          $auto.on('change', function () {
            $input.prop('disabled', this.checked).prop('required', !this.checked).val(this.checked ? '' : $range.val());
            $range.prop('disabled', this.checked);
          });
          $range.on('input change', function () { $input.val(this.value); });
          $input.on('input change', function () { if (this.value !== '' && this.validity.valid) $range.val(this.value); });
        } else if (field.type === 'image') {
          $input.attr('placeholder', 'https://…');
          const $controls = $('<div class="udnpc-image-field"/>').append($input);
          const $pick = $('<button type="button" class="button udnpc-pick"/>').text(strings.selectImage);
          const $clear = $('<button type="button" class="udnpc-clear"/>').text(strings.remove).attr('aria-label', strings.remove + ': ' + field.label);
          $pick.on('click', function () {
            const media = wp.media({title: strings.selectImage, library: {type: 'image'}, button: {text: strings.useImage}, multiple: false});
            media.on('select', function () { $input.val(media.state().get('selection').first().toJSON().url).trigger('input'); });
            media.open();
          });
          $clear.on('click', function () { $input.val('').trigger('input'); });
          $controls.append($pick, $clear);
          $row.append($controls);
        } else {
          $row.append($input);
          // WordPress's built-in picker does not preserve alpha. Keep rgba fields as editable text.
          if (field.type === 'color' && !/^rgba?\(/i.test(value || field.default || '')) {
            $input.addClass('udnpc-color');
          }
        }
        (groups[field.type] || groups.text).append($row);
      });
      $wrap.find('fieldset').each(function () { if (!$(this).find('input').length) $(this).remove(); });
      $wrap.find('.udnpc-color').wpColorPicker({
        change: function (event, ui) { $(event.target).val(ui.color.toString()); markDirty(); queuePreview(); },
        clear: function () { markDirty(); queuePreview(); }
      });
      rendering = false;
      renderPreview();
    }
    function selectTemplate() {
      options[active] = values();
      active = $select.val();
      $('.udnpc-template-tile').each(function () { $(this).attr('aria-pressed', String($(this).data('template') === active)); });
      renderFields();
      markDirty();
    }
    $('.udnpc-template-tile').on('click', function () { $select.val($(this).data('template')).trigger('change'); });
    $select.on('change', selectTemplate);
    $form.on('input change', 'input, select', function () { markDirty(); queuePreview(); });
    $('#udnpc-preview-title').on('input', queuePreview);
    $form.on('submit', function (event) {
      // A color picker can temporarily contain incomplete text while typing.
      let invalid = null;
      Object.keys(templates[active].fields).forEach(function (key) {
        if (fontsOnly) return;
        const input = document.getElementById('tpl_' + key);
        const field = templates[active].fields[key];
        if (field.type === 'color' && input.value && !/^#[a-f\d]{3}(?:[a-f\d]{3})?$/i.test(input.value) && !/^rgba?\([\d\s.,]+\)$/.test(input.value)) invalid = input;
      });
      if (invalid) { event.preventDefault(); invalid.focus(); invalid.setCustomValidity('Enter a hex, rgb, or rgba color.'); invalid.reportValidity(); $(invalid).one('input', function () { this.setCustomValidity(''); }); return; }
      dirty = false;
    });
    $(window).on('beforeunload', function (event) { if (dirty) { event.preventDefault(); event.originalEvent.returnValue = ''; } });
    $('#udnpc-copy').on('click', async function () {
      const code = document.getElementById('udnpc-shortcode');
      try { await navigator.clipboard.writeText(code.textContent); $('#udnpc-copy-status').text(strings.copied); }
      catch (error) { const range = document.createRange(); range.selectNodeContents(code); const selection = window.getSelection(); selection.removeAllRanges(); selection.addRange(range); $('#udnpc-copy-status').text(strings.copyFailed); }
    });
    if (window.ResizeObserver) new ResizeObserver(scalePreview).observe(viewport);
    else $(window).on('resize', scalePreview);
    if (fontsOnly) renderPreview();
    else renderFields();
    if ($('.udnpc-studio .notice-error').length) markDirty();
    $('.udnpc-save').prop('disabled', false);
  });
})(jQuery);
