/**
 * Критический CSS первого экрана для темы gkb-theme (задача 4.6 openspec seo-promotion).
 *
 * Открывает контрольные страницы боевого сайта на телефоне (412×915) и компьютере (1350×940),
 * берёт из основного CSS (склейка WP-Optimize или dist/css темы) только правила для элементов
 * первого экрана, сохраняя порядок и @media, и пишет <тип>.css в assets/critical темы.
 * Тема встраивает файл в <head>, а основной CSS грузит без блокировки отрисовки (seo/Perf.php).
 *
 * Запускать после заметных правок стилей первого экрана:
 *   npm i --prefix /tmp/gkb-critical puppeteer-core@23
 *   NODE_PATH=/tmp/gkb-critical/node_modules node tools/critical/extract.mjs
 * Chrome: переменная CHROME (по умолчанию — Google Chrome в /Applications).
 */
import {createRequire} from 'node:module';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
const require = createRequire(path.join(process.env.NODE_PATH || '.', 'x.js'));
const puppeteer = require('puppeteer-core');
const BASE = process.env.BASE || 'https://gazkotelbel.com';
const out = process.argv[2] || path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../../wp-app/wp-content/themes/gkb-theme/assets/critical');
const ud = fs.mkdtempSync(path.join(os.tmpdir(), 'gkb-crit-'));
const CHROME = process.env.CHROME || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const pages = {
  front: BASE + '/',
  catalog: BASE + '/catalog/',
  category: BASE + '/product-category/gas-boilers/',
  product: BASE + '/products/zhytomyr-3/',
  page: BASE + '/where-to-buy/',
  region: BASE + '/where-to-buy/minsk/',
};
const b = await puppeteer.launch({executablePath: CHROME, headless: true, userDataDir: ud, args: ['--no-first-run']});
for (const [k, url] of Object.entries(pages)) {
  const keep = new Map(); // ключ правила -> текст, порядок по индексу
  for (const [w, h, mobile] of [[412, 915, true], [1350, 940, false]]) {
    const p = await b.newPage();
    await p.setViewport({width: w, height: h, isMobile: mobile, hasTouch: mobile, deviceScaleFactor: 1});
    await p.goto(url + '?crit=1', {waitUntil: 'load'});
    // баннер cookie и прочее, добавленное скриптами позже, не учитываем — берём DOM как отдала разметка
    const res = await p.evaluate((H) => {
      const sheet = [...document.styleSheets].find(s => s.href && /wpo-minify-header|gkb-theme\/dist\/css/.test(s.href));
      if (!sheet) return {err: 'no sheet'};
      const visible = el => { const r = el.getBoundingClientRect(); return r.top < H; };
      const strip = sel => sel.replace(/::?(before|after|placeholder|selection|marker|-webkit-[\w-]+|-moz-[\w-]+)/g, '')
                              .replace(/:(hover|focus|focus-visible|focus-within|active|visited|checked|disabled|target)/g, '')
                              .trim() || '*';
      const matches = sel => sel.split(',').some(s => {
        try { const els = document.querySelectorAll(strip(s)); for (const el of els) if (visible(el)) return true; } catch (e) { return true; }
        return false;
      });
      const outRules = [];
      const walk = (rules, path) => {
        [...rules].forEach((r, i) => {
          const id = path.concat(i).join('.');
          if (r instanceof CSSStyleRule) { if (matches(r.selectorText)) outRules.push([id, null, r.cssText]); }
          else if (r instanceof CSSMediaRule || r instanceof CSSSupportsRule) {
            const save = outRules.length;
            walk(r.cssRules, path.concat(i));
            const inner = outRules.splice(save);
            if (inner.length) outRules.push([id, r instanceof CSSMediaRule ? '@media ' + r.conditionText : '@supports ' + r.conditionText, inner]);
          }
          else if (r instanceof CSSFontFaceRule) outRules.push([id, null, r.cssText]);
          else if (r.cssText.startsWith(':root') || r.type === 1) outRules.push([id, null, r.cssText]);
        });
      };
      walk(sheet.cssRules, []);
      return {rules: outRules, total: sheet.cssRules.length};
    }, h);
    await p.close();
    if (res.err) { console.log('ERR', k, res.err); continue; }
    for (const [id, cond, body] of res.rules) {
      if (cond) {
        const prev = keep.get(id) || {cond, inner: new Map()};
        for (const [iid, , t] of body) prev.inner.set(iid, t);
        keep.set(id, prev);
      } else keep.set(id, body);
    }
  }
  const cmp = (a, b) => { const x = a.split('.').map(Number), y = b.split('.').map(Number); for (let i = 0; i < Math.max(x.length, y.length); i++) { if ((x[i] ?? -1) !== (y[i] ?? -1)) return (x[i] ?? -1) - (y[i] ?? -1); } return 0; };
  let css = '';
  for (const id of [...keep.keys()].sort(cmp)) {
    const v = keep.get(id);
    if (typeof v === 'string') css += v + '\n';
    else css += v.cond + '{' + [...v.inner.keys()].sort(cmp).map(i => v.inner.get(i)).join('') + '}\n';
  }
  css = css.split(BASE + '/').join('/')                                   // корневые адреса шрифтов
           .replace(/@font-face \{ font-family: swiper-icons;[^}]*\}\n/g, ''); // иконки слайдера не нужны первому экрану
  fs.mkdirSync(out, {recursive: true});
  fs.writeFileSync(`${out}/${k}.css`, css);
  console.log('OK', k, css.length);
}
await b.close();
