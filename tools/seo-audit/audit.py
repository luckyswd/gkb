#!/usr/bin/env python3
"""SEO-аудит gazkotelbel.com по спекам изменения seo-promotion.

Обходит sitemap, проверяет мета-теги, JSON-LD, заголовки, alt, коды ответов,
robots.txt, llms.txt, noindex английской версии, сверку цен с прайс-листом и
граф внутренних ссылок. Только стандартная библиотека.

    python3 tools/seo-audit/audit.py https://gazkotelbel.com
    python3 tools/seo-audit/audit.py https://gazkotelbel.com --out openspec/changes/seo-promotion/baseline --lighthouse

Код выхода 1, если найдены нарушения уровня error.
"""

from __future__ import annotations

import argparse
import concurrent.futures as cf
import datetime as dt
import html
import json
import re
import statistics
import subprocess
import sys
import urllib.error
import urllib.parse
import urllib.request
import xml.etree.ElementTree as ET
from html.parser import HTMLParser
from pathlib import Path

UA = "Mozilla/5.0 (compatible; gkb-seo-audit/1.0)"
TIMEOUT = 30

LIGHTHOUSE_PATHS = [
    "/",
    "/catalog/",
    "/product-category/gas-boilers/",
    "/products/zhytomyr-3/",
    "/products/zhytomyr-10/",
    "/products/zhytomyr-turbo/",
    "/where-to-buy/",
]

FORBIDDEN_IN_SITEMAP = re.compile(r"/(login|registration|wp-admin|feed)/|\?lang=|%d0%bf%d1%80%d0%b8%d0%b2%d0%b5%d1%82", re.I)
TYPO_PATTERNS = [re.compile(p, re.I) for p in (r"Беларусии", r"диллер", r"gazkotelbelgazkotelbel")]


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):  # noqa: D401
        return None


_opener = urllib.request.build_opener()
_opener_noredir = urllib.request.build_opener(NoRedirect)


def fetch(url: str, *, follow: bool = True, headers: dict | None = None, method: str = "GET"):
    """Возвращает (status, headers, body, final_url)."""
    req = urllib.request.Request(url, method=method, headers={"User-Agent": UA, **(headers or {})})
    opener = _opener if follow else _opener_noredir
    try:
        with opener.open(req, timeout=TIMEOUT) as r:
            body = r.read().decode("utf-8", "replace") if method == "GET" else ""
            return r.status, dict(r.headers), body, r.geturl()
    except urllib.error.HTTPError as e:
        body = ""
        try:
            body = e.read().decode("utf-8", "replace")
        except Exception:
            pass
        return e.code, dict(e.headers or {}), body, url
    except Exception as e:  # сеть, таймаут
        return 0, {}, str(e), url


class PageParser(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.in_head = True
        self.titles: list[str] = []
        self._in_title = False
        self.metas: list[dict] = []
        self.links: list[dict] = []
        self.jsonld: list[str] = []
        self._in_jsonld = False
        self._buf: list[str] = []
        self.h1: list[dict] = []
        self._h1_depth = 0
        self._h1_has_link = False
        self._h1_text: list[str] = []
        self.imgs: list[dict] = []
        self.anchors: list[str] = []
        self.visible_text: list[str] = []
        self._skip = 0
        self.forms: list[dict] = []

    def handle_starttag(self, tag, attrs):
        a = {k: (v or "") for k, v in attrs}
        if tag == "body":
            self.in_head = False
        if tag == "title" and self.in_head:
            self._in_title = True
            self._buf = []
        elif tag == "meta":
            self.metas.append(a)
        elif tag == "link":
            self.links.append(a)
        elif tag == "script":
            if a.get("type") == "application/ld+json":
                self._in_jsonld = True
                self._buf = []
            else:
                self._skip += 1
        elif tag in ("style", "noscript", "template"):
            self._skip += 1
        elif tag == "h1":
            self._h1_depth += 1
            self._h1_has_link = False
            self._h1_text = []
        elif tag == "a":
            if self._h1_depth:
                self._h1_has_link = True
            if a.get("href"):
                self.anchors.append(a["href"])
        elif tag == "img":
            self.imgs.append(a)
        elif tag == "form":
            self.forms.append(a)

    def handle_endtag(self, tag):
        if tag == "title" and self._in_title:
            self._in_title = False
            self.titles.append("".join(self._buf))
        elif tag == "script":
            if self._in_jsonld:
                self._in_jsonld = False
                self.jsonld.append("".join(self._buf))
            elif self._skip:
                self._skip -= 1
        elif tag in ("style", "noscript", "template") and self._skip:
            self._skip -= 1
        elif tag == "h1" and self._h1_depth:
            self._h1_depth -= 1
            self.h1.append({"text": " ".join("".join(self._h1_text).split()), "link": self._h1_has_link})
        elif tag == "head":
            self.in_head = False

    def handle_data(self, data):
        if self._in_title or self._in_jsonld:
            self._buf.append(data)
            return
        if self._h1_depth:
            self._h1_text.append(data)
        if not self._skip and not self.in_head:
            self.visible_text.append(data)


def meta(p: PageParser, *, name=None, prop=None):
    out = []
    for m in p.metas:
        if name and m.get("name", "").lower() == name:
            out.append(m.get("content", ""))
        if prop and m.get("property", "").lower() == prop:
            out.append(m.get("content", ""))
    return out


def graph_types(blocks: list[str]):
    types, errors, nodes = [], [], []
    for raw in blocks:
        try:
            data = json.loads(raw)
        except Exception as e:
            errors.append(f"JSON-LD не разбирается: {e}")
            continue
        items = data.get("@graph", [data]) if isinstance(data, dict) else data
        for it in items:
            if not isinstance(it, dict):
                continue
            nodes.append(it)
            t = it.get("@type")
            types.extend(t if isinstance(t, list) else [t])
    return types, errors, nodes


class Report:
    def __init__(self):
        self.items: list[dict] = []

    def add(self, level: str, rule: str, url: str, msg: str):
        self.items.append({"level": level, "rule": rule, "url": url, "msg": msg})

    @property
    def errors(self):
        return [i for i in self.items if i["level"] == "error"]


def read_sitemap(base: str, rep: Report, index_url: str | None = None) -> list[str]:
    urls: list[str] = []
    seen = set()

    def walk(u: str, depth=0):
        if u in seen or depth > 3:
            return
        seen.add(u)
        st, _, body, _ = fetch(u)
        if st != 200:
            rep.add("error", "sitemap", u, f"карта отвечает {st}")
            return
        body = re.sub(r"<\?xml-stylesheet[^>]*\?>", "", body)
        try:
            root = ET.fromstring(body.encode("utf-8"))
        except ET.ParseError as e:
            rep.add("error", "sitemap", u, f"карта не разбирается: {e}")
            return
        ns = {"s": "http://www.sitemaps.org/schemas/sitemap/0.9"}
        for loc in root.findall("s:sitemap/s:loc", ns):
            walk(loc.text.strip(), depth + 1)
        for url in root.findall("s:url", ns):
            loc = url.find("s:loc", ns).text.strip()
            if url.find("s:lastmod", ns) is None:
                rep.add("warn", "sitemap", loc, "нет lastmod")
            urls.append(loc)

    walk(index_url or base + "/sitemap.xml")
    for u in urls:
        if FORBIDDEN_IN_SITEMAP.search(u):
            rep.add("error", "sitemap", u, "служебный или мусорный адрес в sitemap")
    return list(dict.fromkeys(urls))


def norm(u: str) -> str:
    p = urllib.parse.urlsplit(u)
    return urllib.parse.urlunsplit((p.scheme, p.netloc, p.path, "", ""))


def audit_page(url: str, base: str):
    st, hdrs, body, final = fetch(url)
    res = {"url": url, "status": st, "issues": [], "links": [], "title": None, "desc": None, "types": []}
    iss = res["issues"]
    if st != 200:
        iss.append(("error", "status", f"ответ {st}"))
        return res
    if norm(final) != norm(url):
        iss.append(("error", "status", f"редирект на {final}"))
    xr = {k.lower(): v for k, v in hdrs.items()}.get("x-robots-tag", "")
    if "noindex" in xr.lower():
        iss.append(("error", "robots", f"X-Robots-Tag: {xr} на русской странице из sitemap"))

    p = PageParser()
    p.feed(body)

    # title
    if len(p.titles) != 1:
        iss.append(("error", "title", f"title на странице: {len(p.titles)}"))
    title = " ".join((p.titles[0] if p.titles else "").split())
    res["title"] = title
    if not title:
        iss.append(("error", "title", "пустой title"))
    elif len(title) > 75:
        iss.append(("warn", "title", f"title длиннее 70 символов ({len(title)}): {title}"))
    if re.search(r"gazkotelbel\s*gazkotelbel|[-–|«]\s*$", title, re.I):
        iss.append(("error", "title", f"мусор в title: {title}"))

    # description
    descs = meta(p, name="description")
    if len(descs) != 1:
        iss.append(("error", "description", f"meta description: {len(descs)}"))
    d = html.unescape(descs[0]) if descs else ""
    res["desc"] = d
    if d and not (110 <= len(d) <= 180):
        iss.append(("warn", "description", f"длина description {len(d)}"))

    # canonical
    canon = [l.get("href", "") for l in p.links if "canonical" in l.get("rel", "").lower().split()]
    if len(canon) != 1:
        iss.append(("error", "canonical", f"canonical: {len(canon)}"))
    elif norm(canon[0]) != norm(url) or "?" in canon[0]:
        iss.append(("error", "canonical", f"canonical {canon[0]} не равен адресу"))

    # robots
    robots = meta(p, name="robots")
    if len(robots) != 1:
        iss.append(("error", "robots", f"meta robots: {len(robots)}"))
    elif "noindex" in robots[0]:
        iss.append(("error", "robots", "noindex на странице из sitemap"))

    # OG
    for prop in ("og:title", "og:description", "og:url", "og:image", "og:type", "og:locale", "og:site_name"):
        if not meta(p, prop=prop):
            iss.append(("error", "og", f"нет {prop}"))
    if not meta(p, name="twitter:card"):
        iss.append(("warn", "og", "нет twitter:card"))

    # JSON-LD
    types, errs, nodes = graph_types(p.jsonld)
    res["types"] = sorted({t for t in types if t})
    for e in errs:
        iss.append(("error", "jsonld", e))
    if len(p.jsonld) != 1:
        iss.append(("error", "jsonld", f"блоков JSON-LD: {len(p.jsonld)}"))
    for need in ("Organization", "WebSite"):
        if need not in types:
            iss.append(("error", "jsonld", f"нет {need}"))
    if not any(t in types for t in ("WebPage", "CollectionPage", "AboutPage", "ContactPage", "FAQPage", "ItemPage")):
        iss.append(("error", "jsonld", "нет WebPage"))
    if "Person" in types:
        iss.append(("error", "jsonld", "в графе есть Person"))
    if norm(url) != norm(base + "/") and "BreadcrumbList" not in types:
        iss.append(("error", "jsonld", "нет BreadcrumbList"))
    if "/products/" in url:
        prod = [n for n in nodes if n.get("@type") == "Product"]
        if not prod:
            iss.append(("error", "jsonld", "нет Product на странице товара"))
        else:
            res["offers"] = prod[0].get("offers")
    if "FAQPage" in types:
        text = html.unescape(" ".join(" ".join(p.visible_text).split()))
        for n in nodes:
            if n.get("@type") == "FAQPage":
                for q in n.get("mainEntity", []):
                    qn = " ".join(html.unescape(q.get("name", "")).split())
                    if qn and qn not in text:
                        iss.append(("error", "jsonld", f"вопрос FAQ не виден на странице: {qn[:60]}"))

    # h1
    if len(p.h1) != 1:
        iss.append(("error", "h1", f"h1 на странице: {len(p.h1)}"))
    for h in p.h1:
        if h["link"]:
            iss.append(("error", "h1", f"h1 содержит ссылку: {h['text'][:60]}"))

    # alt: содержательные картинки — не aria-hidden, не 1×1, не иконки в кнопках
    empty_alt = 0
    for img in p.imgs:
        if img.get("aria-hidden") == "true" or img.get("role") == "presentation":
            continue
        w = img.get("width", "")
        if w.isdigit() and int(w) <= 72:
            continue
        if "alt" not in img:
            empty_alt += 1
        elif not img["alt"].strip():
            empty_alt += 1
    if empty_alt:
        iss.append(("warn", "alt", f"содержательных изображений без alt: {empty_alt}"))

    text = " ".join(p.visible_text) + " " + title
    for pat in TYPO_PATTERNS:
        if pat.search(text):
            iss.append(("error", "typo", f"опечатка «{pat.pattern}»"))

    host = urllib.parse.urlsplit(base).netloc
    for href in p.anchors:
        u = urllib.parse.urljoin(url, href)
        s = urllib.parse.urlsplit(u)
        if s.netloc == host and s.scheme.startswith("http"):
            res["links"].append(norm(u))
    return res


def check_lang(url: str, rep: Report):
    sep = "&" if "?" in url else "?"
    st, hdrs, body, _ = fetch(url + sep + "lang=en")
    xr = {k.lower(): v for k, v in hdrs.items()}.get("x-robots-tag", "")
    p = PageParser()
    p.feed(body)
    rob = meta(p, name="robots")
    if "noindex" not in xr.lower():
        rep.add("error", "lang-en", url, "нет X-Robots-Tag noindex у ?lang=en")
    if not rob or "noindex" not in rob[0]:
        rep.add("error", "lang-en", url, "нет meta robots noindex у ?lang=en")
    # русская копия без куки сразу после английской
    for _ in range(3):
        st, hdrs, body, _ = fetch(url)
        xr = {k.lower(): v for k, v in hdrs.items()}.get("x-robots-tag", "")
        p = PageParser()
        p.feed(body)
        rob = meta(p, name="robots")
        if "noindex" in xr.lower() or (rob and "noindex" in rob[0]):
            rep.add("error", "lang-en", url, "русская копия отдаётся с noindex после запроса EN")
            break


def check_robots(base: str, rep: Report):
    st, hdrs, body, _ = fetch(base + "/robots.txt")
    if st != 200:
        rep.add("error", "robots.txt", base, f"robots.txt отвечает {st}")
        return
    for need in ("Sitemap: " + base + "/sitemap.xml", "Disallow: /wp-admin/", "Clean-param", "llms.txt",
                 "OAI-SearchBot", "PerplexityBot", "ClaudeBot", "YandexAdditional"):
        if need not in body:
            rep.add("error", "robots.txt", base, f"в robots.txt нет «{need}»")
    if re.search(r"Disallow:\s*/\*\?lang", body):
        rep.add("error", "robots.txt", base, "robots.txt запрещает ?lang= — робот не увидит noindex")
    for ua in ("PerplexityBot", "ClaudeBot", "OAI-SearchBot", "YandexAdditional"):
        st, _, _, _ = fetch(base + "/products/zhytomyr-3/", headers={"User-Agent": ua})
        if st != 200:
            rep.add("error", "ai-bots", base, f"{ua} получает {st}")


def check_llms(base: str, sitemap_urls: list[str], rep: Report):
    st, hdrs, body, _ = fetch(base + "/llms.txt")
    ct = {k.lower(): v for k, v in hdrs.items()}.get("content-type", "")
    if st != 200 or "text/plain" not in ct:
        rep.add("error", "llms.txt", base, f"llms.txt: {st} {ct}")
        return
    for sec in ("# ", "## Важно при цитировании", "## Продукция", "## Где купить", "## Контакты"):
        if sec not in body:
            rep.add("error", "llms.txt", base, f"нет раздела «{sec}»")
    if "lang=en" in body:
        rep.add("error", "llms.txt", base, "английские адреса в llms.txt")
    for u in re.findall(r"\]\((https?://[^)]+)\)", body):
        if norm(u) not in {norm(x) for x in sitemap_urls}:
            rep.add("warn", "llms.txt", u, "адрес из llms.txt отсутствует в sitemap")


def check_legacy(base: str, rep: Report):
    for path in ("/wp-sitemap.xml", "/sitemap_index.xml", "/post-sitemap.xml", "/page-sitemap.xml",
                 "/products-sitemap.xml", "/product-category-sitemap.xml", "/category-sitemap.xml", "/sitemap.rss"):
        st, hdrs, _, _ = fetch(base + path, follow=False)
        loc = {k.lower(): v for k, v in hdrs.items()}.get("location", "")
        if st != 301 or not loc.rstrip("/").endswith("/sitemap.xml"):
            rep.add("error", "legacy-sitemap", base + path, f"ожидался 301 на /sitemap.xml, получено {st} {loc}")
    for path in ("/login/", "/registration/"):
        st, _, body, _ = fetch(base + path)
        p = PageParser()
        p.feed(body)
        rob = meta(p, name="robots")
        if not rob or "noindex" not in rob[0]:
            rep.add("error", "robots", base + path, "служебная страница без noindex")
    st, _, _, _ = fetch(base + "/products/net-takogo-tovara/")
    if st != 404:
        rep.add("error", "404", base, f"несуществующий адрес отвечает {st}")


def parse_price_list(base: str) -> dict[str, int]:
    """Код модификации → цена из таблицы прайс-листа (первое вхождение по серии не различаем)."""
    st, _, body, _ = fetch(base + "/price-list/")
    if st != 200:
        return {}
    text = " ".join(re.sub(r"<[^>]+>", " ", html.unescape(body)).split())
    prices = {}
    for code, price in re.findall(r"(К[СС]-[А-ЯA-Z]+-?\s?0\d\d\s?[А-ЯA-Z]*)\s+(\d[\d\s]{2,6})", text):
        prices.setdefault(re.sub(r"\s", "", code), int(re.sub(r"\s", "", price)))
    return prices


def check_prices(results: list[dict], base: str, rep: Report):
    pl = parse_price_list(base)
    if not pl:
        rep.add("warn", "prices", base, "прайс-лист не разобран")
        return
    low = min(pl.values())
    for r in results:
        offers = r.get("offers")
        if offers and isinstance(offers, dict):
            lp = offers.get("lowPrice")
            try:
                lp = float(lp)
            except (TypeError, ValueError):
                rep.add("error", "prices", r["url"], "lowPrice не число")
                continue
            if lp < low:
                rep.add("error", "prices", r["url"], f"lowPrice {lp} меньше минимальной цены прайса {low}")


def lighthouse(base: str, outdir: Path):
    rows = []
    for path in LIGHTHOUSE_PATHS:
        for preset in ("mobile", "desktop"):
            runs = []
            for i in range(3):
                out = outdir / f"lh-{preset}-{re.sub(r'[^a-z0-9]+', '_', path.strip('/') or 'home')}-{i}.json"
                cmd = ["npx", "-y", "lighthouse@12.6.0", base + path, "--quiet", "--chrome-flags=--headless=new",
                       "--output=json", f"--output-path={out}",
                       "--only-categories=performance,seo,accessibility,best-practices"]
                if preset == "desktop":
                    cmd.insert(4, "--preset=desktop")
                subprocess.run(cmd, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, timeout=300)
                try:
                    lr = json.loads(out.read_text())
                    runs.append(lr)
                    out.unlink()
                except Exception:
                    pass
            if not runs:
                rows.append({"path": path, "preset": preset, "error": "lighthouse не отработал"})
                continue
            med = lambda f: statistics.median(f(r) for r in runs)  # noqa: E731
            rows.append({
                "path": path, "preset": preset,
                **{c: round(med(lambda r: (r["categories"][c]["score"] or 0) * 100))
                   for c in ("performance", "accessibility", "best-practices", "seo")},
                "lcp_s": round(med(lambda r: r["audits"]["largest-contentful-paint"]["numericValue"]) / 1000, 2),
                "cls": round(med(lambda r: r["audits"]["cumulative-layout-shift"]["numericValue"]), 3),
                "tbt_ms": round(med(lambda r: r["audits"]["total-blocking-time"]["numericValue"])),
            })
    return rows


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("base", nargs="?", default="https://gazkotelbel.com")
    ap.add_argument("--out", default=None, help="каталог для отчёта (md + json)")
    ap.add_argument("--lighthouse", action="store_true")
    ap.add_argument("--workers", type=int, default=6)
    ap.add_argument("--sitemap", default=None, help="адрес индекса карт, если не /sitemap.xml")
    ap.add_argument("--skip-legacy", action="store_true", help="не проверять старые адреса карт (локальный стенд)")
    a = ap.parse_args()
    base = a.base.rstrip("/")
    rep = Report()

    urls = read_sitemap(base, rep, a.sitemap)
    with cf.ThreadPoolExecutor(a.workers) as ex:
        results = list(ex.map(lambda u: audit_page(u, base), urls))
    for r in results:
        for lvl, rule, msg in r["issues"]:
            rep.add(lvl, rule, r["url"], msg)

    # уникальность description и title
    for key, rule in (("desc", "description"), ("title", "title")):
        seen: dict[str, str] = {}
        for r in results:
            v = r.get(key)
            if v and v in seen:
                rep.add("error", rule, r["url"], f"{key} совпадает с {seen[v]}")
            elif v:
                seen[v] = r["url"]

    # сироты
    inbound = {norm(u): 0 for u in urls}
    for r in results:
        for l in set(r["links"]):
            if l in inbound and l != norm(r["url"]):
                inbound[l] += 1
    for u, n in inbound.items():
        if n == 0 and u != norm(base + "/"):
            rep.add("warn", "orphan", u, "нет входящих внутренних ссылок")

    check_robots(base, rep)
    check_llms(base, urls, rep)
    if not a.skip_legacy:
        check_legacy(base, rep)
    for u in [x for x in urls if "/products/" in x][:3] + [base + "/"]:
        check_lang(u, rep)
    check_prices(results, base, rep)

    lh_dir = Path(a.out or ".").resolve()
    lh_dir.mkdir(parents=True, exist_ok=True)
    lh = lighthouse(base, lh_dir) if a.lighthouse else []
    if lh:
        for row in lh:
            if row.get("preset") == "mobile" and "performance" in row:
                if row["performance"] < 90:
                    rep.add("warn", "lighthouse", base + row["path"], f"мобильная производительность {row['performance']}")
                if row["accessibility"] < 95:
                    rep.add("warn", "lighthouse", base + row["path"], f"доступность {row['accessibility']}")
                if row["lcp_s"] > 2.5:
                    rep.add("warn", "lighthouse", base + row["path"], f"LCP {row['lcp_s']} с")

    now = dt.datetime.now().strftime("%Y-%m-%d %H:%M")
    lines = [f"# SEO-аудит {base} — {now}", "",
             f"Страниц в sitemap: {len(urls)}. Ошибок: {len(rep.errors)}, предупреждений: {len(rep.items) - len(rep.errors)}.", ""]
    if lh:
        lines += ["## Lighthouse (медиана трёх прогонов)", "",
                  "| Страница | Профиль | Perf | A11y | BP | SEO | LCP, с | CLS | TBT, мс |", "|---|---|---|---|---|---|---|---|---|"]
        for r in lh:
            if "error" in r:
                lines.append(f"| {r['path']} | {r['preset']} | {r['error']} |||||||")
            else:
                lines.append(f"| {r['path']} | {r['preset']} | {r['performance']} | {r['accessibility']} | {r['best-practices']} | {r['seo']} | {r['lcp_s']} | {r['cls']} | {r['tbt_ms']} |")
        lines.append("")
    by_rule: dict[str, list] = {}
    for i in rep.items:
        by_rule.setdefault(i["rule"], []).append(i)
    for rule, items in sorted(by_rule.items()):
        lines += [f"## {rule} ({len(items)})", ""]
        for i in items:
            lines.append(f"- **{i['level']}** {i['url']} — {i['msg']}")
        lines.append("")
    lines += ["## Страницы", "", "| URL | Title | Типы JSON-LD |", "|---|---|---|"]
    for r in results:
        lines.append(f"| {r['url']} | {(r['title'] or '').replace('|', '/')} | {', '.join(r['types'])} |")
    md = "\n".join(lines) + "\n"

    if a.out:
        out = Path(a.out)
        out.mkdir(parents=True, exist_ok=True)
        stamp = dt.datetime.now().strftime("%Y-%m-%d")
        (out / f"audit-{stamp}.md").write_text(md, encoding="utf-8")
        (out / f"audit-{stamp}.json").write_text(json.dumps(
            {"base": base, "date": now, "urls": urls, "issues": rep.items, "lighthouse": lh,
             "pages": [{k: r.get(k) for k in ("url", "status", "title", "desc", "types")} for r in results]},
            ensure_ascii=False, indent=1), encoding="utf-8")
        print(f"Отчёт: {out}/audit-{stamp}.md")
    else:
        print(md)
    print(f"Ошибок: {len(rep.errors)}, предупреждений: {len(rep.items) - len(rep.errors)}", file=sys.stderr)
    sys.exit(1 if rep.errors else 0)


if __name__ == "__main__":
    main()
