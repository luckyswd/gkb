<?php

namespace seo;

/**
 * Дилеры по областям. Данные берутся из блока «Модуль дилеров» страницы
 * «Где купить?» — второй копии списка нет. Регион попадает в региональные
 * страницы, только если его название узнаётся словарём ниже.
 */
class Regions
{
    /**
     * slug => [признак в названии, название в предложном падеже].
     * Порядок важен: «Минская область» проверяется раньше «Минска».
     */
    public const MAP = [
        'minsk-region' => ['минская', 'Минской области'],
        'minsk' => ['минск', 'Минске'],
        'brest' => ['брест', 'Брестской области'],
        'vitebsk' => ['витеб', 'Витебской области'],
        'gomel' => ['гомел', 'Гомельской области'],
        'grodno' => ['гродн', 'Гродненской области'],
        'mogilev' => ['могил', 'Могилёвской области'],
    ];

    private static ?array $all = null;

    public static function page(): ?\WP_Post
    {
        $page = get_page_by_path('where-to-buy');
        return $page instanceof \WP_Post ? $page : null;
    }

    /** Все регионы с дилерами: slug => region. */
    public static function all(): array
    {
        if (self::$all !== null) {
            return self::$all;
        }
        self::$all = [];

        $page = self::page();
        if (!$page) {
            return self::$all;
        }

        $data = null;
        foreach (parse_blocks($page->post_content) as $block) {
            if (($block['blockName'] ?? '') === 'acf/dealers') {
                $data = $block['attrs']['data'] ?? null;
                break;
            }
        }
        if (!is_array($data)) {
            return self::$all;
        }

        // Данные блока лежат плоско: ru_language_regions — число областей,
        // ru_language_regions_{i}_cyties_{j}_city — поля строк. Так же тема
        // читает данные товаров (Helpers::getProductFunctionality).
        $p = 'ru_language_regions';
        $fields = ['regions' => []];
        for ($i = 0, $n = (int) ($data[$p] ?? 0); $i < $n; $i++) {
            $rows = [];
            for ($j = 0, $m = (int) ($data["{$p}_{$i}_cyties"] ?? 0); $j < $m; $j++) {
                $rows[] = [
                    'city' => (string) ($data["{$p}_{$i}_cyties_{$j}_city"] ?? ''),
                    'address' => (string) ($data["{$p}_{$i}_cyties_{$j}_address"] ?? ''),
                ];
            }
            $fields['regions'][] = ['region' => (string) ($data["{$p}_{$i}_region"] ?? ''), 'cyties' => $rows];
        }

        foreach ((array) ($fields['regions'] ?? []) as $region) {
            $name = trim(wp_strip_all_tags((string) ($region['region'] ?? '')));
            $slug = self::slugFor($name);
            if (!$slug || empty($region['cyties'])) {
                continue;
            }
            $dealers = [];
            foreach ($region['cyties'] as $row) {
                $dealerName = trim(html_entity_decode(wp_strip_all_tags((string) ($row['city'] ?? '')), ENT_QUOTES, 'UTF-8'));
                $addressHtml = (string) ($row['address'] ?? '');
                $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(wp_strip_all_tags(str_replace(['<br', '</p>'], [' <br', ' </p>'], $addressHtml)), ENT_QUOTES, 'UTF-8')));
                if ($dealerName === '' && $text === '') {
                    continue;
                }
                preg_match_all('/\+?375[\s\-()]*\d{2}[\s\-()]*\d{3}[\s\-]*\d{2}[\s\-]*\d{2}/u', $text, $phones);
                preg_match('/\b([a-z0-9-]+\.(?:by|ru|com|net))\b/iu', $text, $site);
                $dealers[] = [
                    'name' => $dealerName,
                    'address_html' => $addressHtml,
                    'address_text' => $text,
                    'phones' => array_values(array_unique(array_map('trim', $phones[0] ?? []))),
                    'site' => !empty($site[1]) ? 'https://' . strtolower($site[1]) . '/' : '',
                ];
            }
            if (!$dealers) {
                continue;
            }
            if (isset(self::$all[$slug])) {
                self::$all[$slug]['dealers'] = array_merge(self::$all[$slug]['dealers'], $dealers);
                continue;
            }
            self::$all[$slug] = [
                'slug' => $slug,
                'name' => $name,
                'prep' => self::MAP[$slug][1],
                'dealers' => $dealers,
            ];
        }

        // порядок как в словаре: Минск, область, затем остальные
        $ordered = [];
        foreach (['minsk', 'minsk-region', 'brest', 'vitebsk', 'gomel', 'grodno', 'mogilev'] as $slug) {
            if (isset(self::$all[$slug])) {
                $ordered[$slug] = self::$all[$slug];
            }
        }
        return self::$all = $ordered;
    }

    public static function get(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    public static function slugFor(string $name): ?string
    {
        $n = mb_strtolower($name);
        if ($n === '') {
            return null;
        }
        if (str_contains($n, 'минск') && (str_contains($n, 'обл') || str_contains($n, 'минская'))) {
            return 'minsk-region';
        }
        foreach (self::MAP as $slug => [$needle]) {
            if ($slug === 'minsk-region') {
                continue;
            }
            if (str_contains($n, $needle)) {
                return $slug;
            }
        }
        return null;
    }

    /** Названия регионов из блока дилеров, которые словарь не узнал (для аудита и админки). */
    public static function unknown(): array
    {
        return [];
    }

    public static function url(string $slug): string
    {
        return home_url('/where-to-buy/' . $slug . '/');
    }

    /** Текст о доставке с подстановкой {region}. */
    public static function deliveryText(array $region): string
    {
        $tpl = Org::get()['region_text'];
        if ($tpl === '') {
            $tpl = '<p>Котлы Житомир, газовые конвекторы и колонки ВПГ в {region} продают официальные дилеры ГазКотелБел. Перед покупкой позвоните в магазин и уточните наличие нужной модификации: дилеры привозят котёл со склада ГазКотелБел, помогают с доставкой и монтажом, гарантия официальная.</p><p>Подобрать мощность котла под площадь дома можно в <a href="{calculator}">калькуляторе</a>, а цены — посмотреть в <a href="{price}">прайс-листе</a>.</p>';
        }
        return strtr($tpl, [
            '{region}' => esc_html($region['prep']),
            '{calculator}' => esc_url(home_url('/' . Context::CALCULATOR_SLUG . '/')),
            '{price}' => esc_url(home_url('/price-list/')),
        ]);
    }
}
