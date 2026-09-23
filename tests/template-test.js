/* node tests/template-test.js */
const vm = require('vm');
const fs = require('fs');
const path = require('path');
const assert = require('assert');
const context = {window: {}};
vm.createContext(context);
vm.runInContext(fs.readFileSync(path.join(__dirname, '../assets/template.js'), 'utf8'), context);
const render = context.window.UDNPCTemplate;
assert.strictEqual(render.html('<div title="{{title}}">{{title}}</div>', {title: '"><img src=x onerror=alert(1)>'}), '<div title="&quot;&gt;&lt;img src=x onerror=alert(1)&gt;">&quot;&gt;&lt;img src=x onerror=alert(1)&gt;</div>');
assert.strictEqual(render.html('{{title}} / {{opt.wc}}', {title: '$& $1', opts: {wc: 0}}), '$&amp; $1 / 0');
assert.strictEqual(render.css('background:{{opt.color}}', {opts: {color: 'rgba(0,0,0,0.15)'}}), 'background:rgba(0,0,0,0.15)');
assert(!render.css('{{opt.value}}', {opts: {value: '</style><script>x</script>'}}).includes('<'));
assert.strictEqual(render.html('{{title}}', {title: '{{opt.logo}}', opts: {logo: 'recursive replacement'}}), '{{opt.logo}}');
assert.strictEqual(render.html('{{opt.empty}}', {opts: {empty: ''}}), '');
console.log('PASS: HTML/attribute escaping, literal dollar signs, zero values, alpha colors, CSS boundaries, nonrecursive substitution, empty fields.');
const base = '.card[data-wc="8"] .headline{font-size:72px}';
assert.strictEqual(render.css(base, {opts: {title_font_size: ''}}), base);
const custom = render.css(base, {opts: {title_font_size: '48', date_font_size: '20', cta_font_size: '24', site_font_size: '18'}});
for (const [role, size] of [['title', 48], ['date', 20], ['cta', 24], ['site', 18]]) {
  assert(custom.includes('[data-udnpc-text="' + role + '"]{font-size:' + size + 'px!important}'));
}
for (const size of ['23', '121', '48.5', '48px', '48; color:red', '-1']) {
  assert.strictEqual(render.css(base, {opts: {title_font_size: size}}), base);
}
assert.strictEqual(render.css(base, {opts: {site_font_size: '61'}}), base);
assert(custom.includes('::after{font-size:1em!important}'));
console.log('PASS: Auto preserves original sizing, all four text roles override sizing, invalid sizes are rejected, and decorative icons scale with text.');
for (const quote of ['"', "'", '']) {
  const css = '.card{font-family:' + quote + 'SolaimanLipi' + quote + ',sans-serif;font-weight:800}';
  assert(render.css(css, {opts: {font_family: 'Shurjo'}}).includes('font-family:"Shurjo",sans-serif;font-weight:800'));
  assert(!render.css(css, {opts: {font_family: 'Shurjo; color:red'}}).includes('color:red'));
}
assert.equal(render.fontFamily({}), 'SolaimanLipi');
(async () => {
  const requested = [];
  await render.loadFonts({fonts: {load: async (font, text) => { requested.push([font, text]); return [{}]; }, ready: Promise.resolve()}}, {opts: {font_family: 'Shurjo'}});
  assert.deepEqual(requested.map(x => x[0]), ['400 46px "Shurjo"', '700 46px "Shurjo"']);
  assert(requested.every(x => x[1].includes('বাংলা')));
  await assert.rejects(() => render.loadFonts({fonts: {load: async () => []}}, {}), /unavailable/);
  console.log('PASS: Dynamic font selection, safe fallback, Bengali regular/bold loading, and missing font rejection.');
})().catch(error => { console.error(error); process.exitCode = 1; });
