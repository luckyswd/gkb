"""Снимает HTML вкладок товаров с боевого сайта (задача 1.1 изменения site-redesign).

Сохраняет содержимое .product__body-content-{benefits,specifications,equipment}
для каждого товара в обеих языковых версиях. Содержимое страниц — данные, скрипт
их только сохраняет.
"""
import os
import re
import sys
import time
import urllib.request

BASE = "https://gazkotelbel.com/products/%s/"
OUT = sys.argv[1]
DATE = time.strftime("%Y-%m-%d")

SLUGS = [
    "zhytomyr-3", "zhytomyr-3-with-teno", "zhytomyr-10", "zhytomyr-turbo",
    "zhytomyr-m", "zhytomyr-aotv-aktv", "zhytomyr", "zhytomyr-9", "zhytomyr-5",
    "hsv-20", "hsv-20tm", "hsv-20m", "vpg-20t",
]

FIELDS = {
    "benefits": "features_and_benefits",
    "specifications": "specifications",
    "equipment": "equipment",
}

TAG = re.compile(r"<(/?)div\b", re.I)


def fetch(url, lang):
    # Чистые URL товаров отдаются из кеша, который игнорирует куку lang,
    # поэтому к адресу добавляется уникальный параметр.
    url = "%s?snapshot=%s-%d" % (url, lang, time.time() * 1000)
    req = urllib.request.Request(url, headers={
        "User-Agent": "gkb-redesign-snapshot/1.0",
        "Cookie": "lang=%s" % lang,
        "Accept-Language": "ru,en",
    })
    with urllib.request.urlopen(req, timeout=40) as r:
        return r.read().decode("utf-8", "replace")


def extract(html, cls):
    """Вернуть внутренний HTML первого div с указанным классом."""
    m = re.search(r'<div[^>]*class="[^"]*\b%s\b[^"]*"[^>]*>' % re.escape(cls), html, re.I)
    if not m:
        return None
    depth, pos = 1, m.end()
    for t in TAG.finditer(html, m.end()):
        depth += -1 if t.group(1) else 1
        if depth == 0:
            return html[pos:t.start()]
    return None


def main():
    os.makedirs(OUT, exist_ok=True)
    report = []
    for slug in SLUGS:
        for lang in ("ru", "en"):
            try:
                html = fetch(BASE % slug, lang)
            except Exception as e:                     # noqa: BLE001
                report.append("%-22s %s FETCH-FAIL %s" % (slug, lang, e))
                continue
            for suffix, field in FIELDS.items():
                body = extract(html, "product__body-content-" + suffix)
                if body is None or not body.strip():
                    report.append("%-22s %s %-15s empty" % (slug, lang, field))
                    continue
                name = "%s-%s-%s.html" % (slug, field, lang)
                head = (
                    "<!-- %s / %s / поле %s — состояние до правки разметки (задача 1.1).\n"
                    "     Снято с отрендеренной страницы %s (кука lang=%s, кеш обойдён параметром) %s. -->\n"
                    % (slug, lang, field, BASE % slug, lang, DATE)
                )
                with open(os.path.join(OUT, name), "w", encoding="utf-8") as f:
                    f.write(head + body.strip() + "\n")
                report.append("%-22s %s %-15s %7d B" % (slug, lang, field, len(body)))
            time.sleep(0.4)
    print("\n".join(report))


if __name__ == "__main__":
    main()
