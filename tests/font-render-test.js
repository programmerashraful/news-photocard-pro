/* Browser regression: NODE_PATH must resolve playwright; local WordPress + PHP required. */
const {chromium} = require('playwright');
const {execFileSync} = require('child_process');
const fs = require('fs');
const path = require('path');
const assert = require('assert');
const root = path.resolve(__dirname, '..');
const base = process.env.UDNPC_TEST_URL || 'http://localhost/dialsylhet24/wp-content/plugins/news-photocard-pro/';
const fixtures = JSON.parse(execFileSync('php', [path.join(__dirname, 'fonts-admin-fixture.php')], {encoding: 'utf8'}));
(async () => {
  const browser = await chromium.launch({channel: process.env.UDNPC_BROWSER_CHANNEL || 'msedge', headless: true});
  try {
    const page = await browser.newPage({viewport: {width: 1440, height: 1100}});
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.route(base + 'font-test-page', route => route.fulfill({contentType: 'text/html', body: '<!doctype html><html><body></body></html>'}));
    await page.goto(base + 'font-test-page');
    for (const screen of ['fonts', 'studio']) {
      await page.setContent('<!doctype html><html><head><link rel="stylesheet" href="' + base + 'admin/admin.css"></head><body>' + fixtures[screen] + '</body></html>');
      await page.addScriptTag({url: new URL('../../../wp-includes/js/jquery/jquery.min.js', base).href});
      await page.addScriptTag({content: fixtures.config});
      // Picker behavior is outside this regression; allow the real editor fields to render.
      await page.addScriptTag({content: 'jQuery.fn.wpColorPicker = function () { return this; };'});
      await page.addScriptTag({path: path.join(root, 'assets/template.js')});
      await page.addScriptTag({path: path.join(root, 'admin/admin.js')});
      for (const font of ['SolaimanLipi', 'Shurjo', 'SolaimanLipi', 'Shurjo']) {
        await page.selectOption('#udnpc-font-family', font);
        await page.waitForFunction(font => {
          const frame = document.getElementById('udnpc-preview');
          const title = frame.contentDocument?.querySelector('[data-udnpc-text="title"]');
          return title && frame.contentWindow.getComputedStyle(title).fontFamily.startsWith(font) && !document.getElementById('udnpc-font-status').textContent;
        }, font);
      }
      assert.equal(await page.locator('#udnpc-save-status').getAttribute('class'), 'is-dirty');
      if (screen === 'studio') {
        await page.selectOption('#udnpc-active-template', 'midnight-focus');
        await page.waitForFunction(() => document.getElementById('udnpc-preview').contentDocument?.querySelector('.nmf-title'));
        assert.equal(await page.locator('#udnpc-font-family').inputValue(), 'Shurjo');
      }
      if (process.env.UDNPC_TEST_OUTPUT) {
        fs.mkdirSync(process.env.UDNPC_TEST_OUTPUT, {recursive: true});
        await page.screenshot({path: path.join(process.env.UDNPC_TEST_OUTPUT, screen + '-fonts.png'), fullPage: true});
      }
      console.log('PASS: ' + screen + ' switches fonts immediately in the real preview iframe and marks changes unsaved');
      // Clear dirty-state listeners before replacing this document.
      await page.evaluate(() => jQuery(window).off('beforeunload'));
    }
    const config = await page.evaluate(() => UDNPCAdmin);
    await page.setContent('<!doctype html><html><head><link rel="stylesheet" href="' + base + 'assets/fonts.css"><link rel="stylesheet" href="' + base + 'assets/elementor-button.css"></head><body><div class="udnpc-wrap"><button class="udnpc-generate" data-post-id="123">Download</button></div></body></html>');
    await page.addScriptTag({url: new URL('../../../wp-includes/js/jquery/jquery.min.js', base).href});
    await page.addScriptTag({path: path.join(root, 'assets/template.js')});
    await page.addScriptTag({path: path.join(root, 'assets/html2canvas.min.js')});
    await page.evaluate(base => {
      window.UDNPCF = {ajax: base + 'font-test-ajax', nonce: 'test', i18n: {preparing: 'Preparing', detail: 'Loading', error: 'Export failed', network: 'Network error'}};
      window.fontChecks = [];
      const original = html2canvas;
      window.html2canvas = async (card, opts) => {
        const family = getComputedStyle(card.querySelector('[data-udnpc-text="title"]')).fontFamily;
        const originalClone = opts.onclone;
        opts.onclone = async doc => {
          await originalClone(doc);
          window.fontChecks.push({family, cloned: doc.fonts.check('700 46px ' + family.split(',')[0], 'বাংলা')});
        };
        return original(card, opts);
      };
    }, base);
    await page.addScriptTag({path: path.join(root, 'assets/front.js')});
    let payload;
    await page.route(base + 'font-test-ajax', route => route.fulfill({json: {success: true, data: payload}}));
    for (const [key, tpl] of Object.entries(config.templates)) {
      const images = [];
      for (const font of ['SolaimanLipi', 'Shurjo']) {
        const opts = Object.fromEntries(Object.entries(tpl.fields).map(([key, field]) => [key, field.default]));
        Object.assign(opts, {font_family: font, logo: base + 'assets/preview-logo.svg', domain: 'dialsylhet24.com', wc: 7});
        payload = {tpl_def: tpl, title: 'সিলেটের সবুজ প্রকৃতিতে নতুন দিনের গল্প', date: '১১ সেপ্টেম্বর ২০২৬', site: 'ডায়াল সিলেট ২৪', image: base + 'assets/preview-landscape.svg', opts};
        const downloadPromise = page.waitForEvent('download');
        await page.click('.udnpc-generate');
        const download = await downloadPromise;
        const png = fs.readFileSync(await download.path());
        assert.equal(png.readUInt32BE(16), 1080);
        assert.equal(png.readUInt32BE(20), 1080);
        images.push(png);
        const check = await page.evaluate(() => window.fontChecks.at(-1));
        assert(check.family.startsWith(font) && check.cloned, key + ': selected font loaded in export clone');
        assert.equal(await page.locator('.udnpc-render-surface').count(), 0);
      }
      assert(!images[0].equals(images[1]), key + ': font selection changes actual downloaded PNG pixels');
      console.log('PASS: ' + key + ' downloads distinct 1080px PNGs in both fonts through the real button/export path');
    }
    assert.deepEqual(errors, []);
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
