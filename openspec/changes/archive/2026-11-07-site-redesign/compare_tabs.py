"""Сверка содержимого вкладок товара до и после редизайна (задача 11.8).

Сравнивается текст без разметки: обёртка таблиц и классы добавлены намеренно,
а вот тексты, числа, единицы измерения и адреса файлов меняться не должны.
"""
import html
import io
import os
import re
import sys
import time
import urllib.request

BEFORE = sys.argv[1]
BASE = 'https://gazkotelbel.com/products/%s/'

SLUGS = [
    'zhytomyr-3', 'zhytomyr-3-with-teno', 'zhytomyr-10', 'zhytomyr-turbo',
    'zhytomyr-m', 'zhytomyr-aotv-aktv', 'zhytomyr', 'zhytomyr-9', 'zhytomyr-5',
    'hsv-20', 'hsv-20tm', 'hsv-20m', 'vpg-20t',
]
PANES = {'features_and_benefits': 'benefits', 'specifications': 'specifications', 'equipment': 'equipment'}
TAG = re.compile(r'<(/?)div\b', re.I)


def fetch(slug, lang):
    url = '%s?compare=%s-%d' % (BASE % slug, lang, time.time() * 1000)
    req = urllib.request.Request(url, headers={
        'User-Agent': 'gkb-redesign-compare/1.0', 'Cookie': 'lang=%s' % lang})
    with urllib.request.urlopen(req, timeout=40) as r:
        return r.read().decode('utf-8', 'replace')


def extract(page, pane):
    m = re.search(r'<div[^>]*data-pane="%s"[^>]*>' % re.escape(pane), page, re.I)
    if not m:
        return None
    depth, pos = 1, m.end()
    for t in TAG.finditer(page, m.end()):
        depth += -1 if t.group(1) else 1
        if depth == 0:
            return page[pos:t.start()]
    return None


def text_of(markup):
    """Текст без разметки плюс адреса файлов и картинок — их тоже сверяем."""
    urls = re.findall(r'(?:href|src)="([^"]+)"', markup or '')
    body = re.sub(r'<(script|style)\b.*?</\1>', ' ', markup or '', flags=re.S | re.I)
    body = re.sub(r'<[^>]+>', ' ', body)
    body = html.unescape(body)
    body = re.sub(r'[\s\u00a0]+', ' ', body).strip()
    return body, sorted(set(u for u in urls if not u.startswith('#')))


def main():
    same = diff = missing = 0
    problems = []

    for slug in SLUGS:
        for lang in ('ru', 'en'):
            try:
                page = fetch(slug, lang)
            except Exception as e:                       # noqa: BLE001
                problems.append('%s %s — страница не открылась: %s' % (slug, lang, e))
                continue

            for field, pane in PANES.items():
                path = os.path.join(BEFORE, '%s-%s-%s.html' % (slug, field, lang))
                if not os.path.exists(path):
                    continue

                before_raw = re.sub(r'^<!--.*?-->\s*', '', io.open(path, encoding='utf-8').read(), flags=re.S)
                after_raw = extract(page, pane)

                if after_raw is None:
                    missing += 1
                    problems.append('%s %s %s — вкладки нет на странице' % (slug, lang, field))
                    continue

                bt, bu = text_of(before_raw)
                at, au = text_of(after_raw)

                if bt == at and bu == au:
                    same += 1
                    continue

                diff += 1
                if bt != at:
                    for i, (x, y) in enumerate(zip(bt, at)):
                        if x != y:
                            problems.append('%s %s %s — текст расходится с позиции %d:\n    было:  …%s…\n    стало: …%s…'
                                            % (slug, lang, field, i, bt[max(0, i - 60):i + 60], at[max(0, i - 60):i + 60]))
                            break
                    else:
                        problems.append('%s %s %s — текст отличается длиной: было %d, стало %d'
                                        % (slug, lang, field, len(bt), len(at)))
                if bu != au:
                    lost = [u for u in bu if u not in au]
                    added = [u for u in au if u not in bu]
                    problems.append('%s %s %s — ссылки: пропало %s, добавилось %s'
                                    % (slug, lang, field, lost or '—', added or '—'))
            time.sleep(0.3)

    print('совпало: %d, расходится: %d, не найдено: %d' % (same, diff, missing))
    if problems:
        print('\n'.join(problems[:40]))
    else:
        print('расхождений нет')
    return 1 if (diff or missing) else 0


if __name__ == '__main__':
    sys.exit(main())
