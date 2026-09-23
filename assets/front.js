(function ($) {
  'use strict';
  let busy = false;
  function overlay() {
    const $overlay = $('<div id="udnpc-overlay" role="status" aria-live="polite"/>');
    const $panel = $('<div class="udnpc-progress-panel"/>');
    $panel.append('<span class="udnpc-spinner" aria-hidden="true"></span>');
    $panel.append($('<strong/>').text(UDNPCF.i18n.preparing));
    $panel.append($('<p/>').text(UDNPCF.i18n.detail));
    $overlay.append($panel).appendTo(document.body);
  }
  function waitForImages(root) {
    return Promise.all(Array.from(root.querySelectorAll('img')).map(function (img) {
      if (!img.getAttribute('src')) { img.remove(); return Promise.resolve(); }
      if (img.complete) return Promise.resolve();
      return new Promise(function (resolve) {
        const timer = setTimeout(resolve, 10000);
        img.onload = img.onerror = function () { clearTimeout(timer); resolve(); };
      });
    }));
  }
  async function build(data) {
    if (!data.tpl_def || !data.tpl_def.html) throw new Error('Missing template');
    const wrap = document.createElement('div');
    wrap.className = 'udnpc-render-surface';
    wrap.setAttribute('aria-hidden', 'true');
    const style = document.createElement('style');
    style.textContent = '.udnpc-render-surface *{box-sizing:border-box}.udnpc-render-surface img{max-width:100%}' + UDNPCTemplate.css(data.tpl_def.css, data);
    const holder = document.createElement('div');
    holder.innerHTML = UDNPCTemplate.html(data.tpl_def.html, data);
    const card = holder.firstElementChild;
    if (!card) throw new Error('Invalid template');
    card.style.width = '1080px';
    card.style.height = '1080px';
    wrap.append(style, holder);
    document.body.appendChild(wrap);
    try {
      await waitForImages(card);
      await UDNPCTemplate.loadFonts(document, data);
      return await html2canvas(card, {backgroundColor: null, useCORS: true, width: 1080, height: 1080, scale: 1, imageTimeout: 10000,
        onclone: function (doc) { return UDNPCTemplate.loadFonts(doc, data); }
      });
    } finally { wrap.remove(); }
  }
  $(document).on('click', '.udnpc-generate, #udnpc-generate', async function (event) {
    event.preventDefault();
    if (busy || !window.UDNPCF) return;
    const button = this;
    const postId = $(button).data('post-id') || $(button).closest('.udnpc-wrap').find('#udnpc-postid').val();
    busy = true;
    $(button).prop('disabled', true).attr('aria-busy', 'true');
    $(button).closest('.udnpc-wrap').find('.udnpc-download-error').remove();
    overlay();
    try {
      const response = await $.ajax({url: UDNPCF.ajax, type: 'POST', dataType: 'json', timeout: 20000, data: {action: 'udnpc_get_card', nonce: UDNPCF.nonce, post_id: postId}});
      if (!response || !response.success) throw new Error(response && response.data && response.data.message || UDNPCF.i18n.error);
      const canvas = await build(response.data);
      const blob = await new Promise(function (resolve) { canvas.toBlob(resolve, 'image/png'); });
      if (!blob) throw new Error(UDNPCF.i18n.error);
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.download = 'news-photocard-' + postId + '-' + Date.now() + '.png';
      link.href = url;
      document.body.appendChild(link);
      link.click();
      link.remove();
      setTimeout(function () { URL.revokeObjectURL(url); }, 30000);
    } catch (error) {
      const message = error && error.responseJSON && error.responseJSON.data && error.responseJSON.data.message || (error && error.statusText ? UDNPCF.i18n.network : UDNPCF.i18n.error);
      $('<p class="udnpc-download-error" role="alert"/>').text(message).appendTo($(button).closest('.udnpc-wrap'));
    } finally {
      $('#udnpc-overlay').remove();
      $(button).prop('disabled', false).removeAttr('aria-busy');
      busy = false;
    }
  });
})(jQuery);
