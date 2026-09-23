/* Browser/export regression: requires playwright and a local WordPress server.
 * NODE_PATH may point to bundled dependencies. Optional: UDNPC_TEST_URL,
 * UDNPC_BROWSER_CHANNEL (defaults to msedge), UDNPC_TEST_OUTPUT for PNG artifacts.
 * Run: node tests/design-render-test.js
 */
const { chromium } = require('playwright');
const { execFileSync } = require('child_process');
const fs = require('fs');
const path = require('path');
const assert = require('assert');
const root = path.resolve(__dirname, '..');
const base = process.env.UDNPC_TEST_URL || 'http://localhost/dialsylhet24/wp-content/plugins/news-photocard-pro/';
const keys = ['editorial-cream', 'midnight-focus', 'red-bulletin', 'split-dispatch', 'emerald-frame'];
const definitions = JSON.parse(execFileSync('php', ['-r',
  '$out=[]; foreach(glob("templates/*.php") as $file){$t=include $file; $out[$t["key"]]=$t;} echo json_encode($out);'
], { cwd: root, encoding: 'utf8' }));
const headlines = [
  'সিলেটের সবুজ প্রকৃতিতে নতুন দিনের গল্প',
  'সিলেটের পর্যটনে নতুন সম্ভাবনা, সবুজ প্রকৃতি ও পাহাড়ের সৌন্দর্য উপভোগ করতে দেশের বিভিন্ন প্রান্ত থেকে ছুটে আসছেন ভ্রমণপ্রেমীরা',
  'A new chapter for Sylhet: local communities welcome plans to protect the region’s green hills and rivers',
];
(async () => {
  const browser = await chromium.launch({ channel: process.env.UDNPC_BROWSER_CHANNEL || 'msedge', headless: true });
  try {
    const page = await browser.newPage({ viewport: { width: 1080, height: 1080 }, deviceScaleFactor: 1 });
    await page.goto(new URL('../../..', base).href, { waitUntil: 'domcontentloaded' });
    for (const key of keys) {
      const tpl = definitions[key];
      assert(tpl, key + ': template discovered');
      for (let variant = 0; variant < 8; variant++) {
        const opts = Object.fromEntries(Object.entries(tpl.fields).map(([k, v]) => [k, v.default]));
        const title = headlines[(variant % 4) % headlines.length];
        opts.font_family = variant < 4 ? 'SolaimanLipi' : 'Shurjo';
        Object.assign(opts, { domain: 'dialsylhet24.com', wc: title.split(/\s+/u).length });
        // Check missing-logo fallback as well as branded cards and explicit text sizes.
        if (variant % 4 !== 1) opts.logo = base + 'assets/preview-logo.svg';
        if (variant % 4 === 3) Object.assign(opts, { title_font_size: '48', date_font_size: '24', cta_font_size: '24', site_font_size: '24' });
        const data = { title, date: '০৭ সেপ্টেম্বর ২০২৬', image: base + 'assets/preview-landscape.svg', site: 'ডায়াল সিলেট ২৪', opts };
        await page.setContent('<!doctype html><html><head><meta charset="utf-8"><link rel="stylesheet" href="' + base + 'assets/fonts.css"></head><body style="margin:0"></body></html>');
        await page.addScriptTag({ path: path.join(root, 'assets/template.js') });
        await page.addScriptTag({ path: path.join(root, 'assets/html2canvas.min.js') });
        const result = await page.evaluate(async ({ tpl, data }) => {
          const style = document.createElement('style');
          style.textContent = UDNPCTemplate.css(tpl.css, data);
          document.head.append(style);
          document.body.innerHTML = UDNPCTemplate.html(tpl.html, data);
          await UDNPCTemplate.loadFonts(document, data);
          await Promise.all([...document.images].map(img => img.getAttribute('src') ? img.decode() : Promise.resolve()));
          const background = new Image(); background.src = data.image; await background.decode();
          const card = document.body.firstElementChild;
          const title = card.querySelector('[data-udnpc-text="title"]');
          const story = title.parentElement;
          const t = title.getBoundingClientRect(), s = story.getBoundingClientRect();
          const footer = card.querySelector('[data-udnpc-text="cta"]').parentElement.getBoundingClientRect();
          const canvas = await html2canvas(card, { width: 1080, height: 1080, scale: 1, useCORS: true, logging: false });
          return {
            width: canvas.width, height: canvas.height,
            fits: t.top >= s.top && t.bottom <= s.bottom && t.left >= s.left && t.right <= s.right && t.bottom < footer.top,
            fontSize: getComputedStyle(title).fontSize, fontFamily: getComputedStyle(title).fontFamily,
            fallback: data.opts.logo ? true : [...card.querySelectorAll('span')].some(el => el.textContent === data.site && getComputedStyle(el).display !== 'none'),
            png: canvas.toDataURL('image/png'),
          };
        }, { tpl, data });
        assert.equal(result.width, 1080); assert.equal(result.height, 1080);
        assert(result.fits, key + ': headline fits its panel, variant ' + variant);
        assert(result.fallback, key + ': missing logo shows site name');
        assert(result.fontFamily.startsWith(opts.font_family));
        if (variant % 4 === 3) assert.equal(result.fontSize, '48px');
        if (process.env.UDNPC_TEST_OUTPUT && variant % 4 === 0) {
          fs.mkdirSync(process.env.UDNPC_TEST_OUTPUT, { recursive: true });
          fs.writeFileSync(path.join(process.env.UDNPC_TEST_OUTPUT, key + '-' + opts.font_family + '.png'), Buffer.from(result.png.split(',')[1], 'base64'));
        }
      }
      console.log('PASS: ' + key + ' — Bangla/English layout, logo fallback, custom sizing, and 1080px PNG export');
    }
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
