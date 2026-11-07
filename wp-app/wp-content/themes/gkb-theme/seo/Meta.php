<?php

namespace seo;

use helpers\Helpers;
use WP_Post;

/**
 * Title, description, canonical, robots, Open Graph и Twitter Card.
 * Шаблоны по типу страницы, ручные значения из полей SEO важнее шаблонов.
 */
class Meta
{
    public const TITLE_MAX = 70;
    public const DESC_MIN = 120;
    public const DESC_MAX = 170;

    /** Шаблоны title и description для страниц по slug. */
    private const PAGES = [
        'catalog' => [
            'Каталог котлов Житомир и колонок ВПГ — официальные цены | ГазКотелБел',
            'Каталог продукции ГазКотелБел: газовые, твердотопливные и универсальные котлы Житомир, газовые конвекторы и колонки ВПГ. Официальные цены в BYN.',
        ],
        'where-to-buy' => [
            'Где купить котлы Житомир в Беларуси — официальные дилеры | ГазКотелБел',
            'Официальные дилеры котлов Житомир и колонок ВПГ в Минске и всех областях Беларуси: адреса, телефоны и часы работы магазинов, доставка и монтаж.',
        ],
        'price-list' => [
            'Прайс-лист на котлы Житомир и колонки ВПГ — цены в BYN | ГазКотелБел',
            'Актуальный прайс-лист ГазКотелБел: розничные цены с НДС в BYN на газовые котлы Житомир-3, Житомир-10, Житомир-Турбо, Житомир-М, конвекторы и колонки ВПГ.',
        ],
        'about' => [
            'О производителе котлов Житомир — АТЕМ | ГазКотелБел',
            'Котлы Житомир выпускает украинская компания АТЕМ с 1988 года. ГазКотелБел — её официальный представитель в Беларуси: поставки, гарантия и дилеры по всей стране.',
        ],
        'documentation' => [
            'Документация и паспорта котлов Житомир — скачать PDF | ГазКотелБел',
            'Паспорта, инструкции по монтажу и эксплуатации котлов Житомир, газовых конвекторов и колонок ВПГ в PDF. Официальная документация производителя.',
        ],
        'contact' => [
            'Контакты ГазКотелБел — котлы Житомир в Беларуси',
            'Контакты ООО «ГазКотелБел»: адрес в Мачулищах (Минский район), телефоны отдела продаж, email и часы работы. Консультации по подбору котла Житомир.',
        ],
        Context::CALCULATOR_SLUG => [
            'Калькулятор мощности газового котла по площади дома | ГазКотелБел',
            'Рассчитайте мощность газового котла для дома по площади, высоте потолков и утеплению. Подберите подходящий котёл Житомир с официальной ценой в BYN.',
        ],
    ];

    private const FILLERS = [
        ' Официальная гарантия.',
        ' Дилеры по всей Беларуси.',
        ' Доставка и монтаж через официальных дилеров.',
    ];

    public static function register(): void
    {
        add_action('after_setup_theme', static function () {
            add_theme_support('title-tag');
        });

        add_filter('pre_get_document_title', [self::class, 'title'], PHP_INT_MAX);
        add_filter('wp_robots', [self::class, 'robots'], 999);
        add_action('wp_head', [self::class, 'head'], 1);
        add_action('template_redirect', [self::class, 'headers'], 0);

        // Ядро и плагины не должны выводить свои canonical, shortlink и прочее
        remove_action('wp_head', 'rel_canonical');
        remove_action('wp_head', 'wp_shortlink_wp_head', 10);
        remove_action('template_redirect', 'wp_shortlink_header', 11);
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'feed_links_extra', 3);
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
    }

    public static function title(): string
    {
        $manual = trim((string) Context::seoField('seo_title'));
        if ($manual !== '') {
            return $manual;
        }
        return self::fitTitle(self::titleCandidates());
    }

    /** Кандидаты title от полного к короткому; берётся первый, что влезает. */
    public static function titleCandidates(): array
    {
        $ctx = Context::get();
        $en = Context::lang() === 'en';
        $brand = $en ? 'GazKotelBel' : Org::NAME;

        switch ($ctx['type']) {
            case 'front':
                return $en
                    ? ['Zhytomyr gas boilers in Belarus — official ATEM representative']
                    : ['Газовые котлы Житомир — официальный представитель АТЕМ в Беларуси'];

            case 'product':
                $name = Context::productTitle($ctx['object']);
                $sub = Context::productSubtitle($ctx['object']);
                $s = ProductData::summary($ctx['object']->ID);
                $price = $s['min_price'] !== null && !$en ? 'цена от ' . ProductData::money($s['min_price']) . "\u{00A0}BYN" : '';
                $c = [];
                if ($sub !== '' && $price !== '') {
                    $c[] = "$name — $sub, $price | $brand";
                    $c[] = "$name — $sub, $price";
                }
                if ($price !== '') {
                    $c[] = "$name — купить в Беларуси, $price | $brand";
                    $c[] = "$name, $price | $brand";
                    $c[] = "$name, $price";
                }
                if ($sub !== '') {
                    $c[] = "$name — $sub | $brand";
                    $c[] = "$name — $sub";
                }
                $c[] = "$name | $brand";
                return $c;

            case 'category':
                $name = Context::termName($ctx['object']);
                return $en
                    ? ["$name Zhytomyr — buy in Belarus | $brand", "$name | $brand"]
                    : [
                        "$name Житомир — купить в Беларуси, официальные цены | $brand",
                        "$name Житомир — купить в Беларуси, официальные цены",
                        "$name Житомир — официальные цены | $brand",
                    ];

            case 'search':
                $q = sanitize_text_field(wp_unslash($_GET['search'] ?? $_GET['s'] ?? ''));
                return [($en ? 'Search: ' : 'Поиск: ') . $q . " | $brand"];

            case 'articles':
                return $en ? ["Articles about heating | $brand"] : ["Статьи о газовых котлах и отоплении дома | $brand"];

            case 'region':
                $r = Context::region();
                return [
                    'Где купить котлы Житомир в ' . ($r['prep'] ?? '') . " — дилеры | $brand",
                    'Где купить котлы Житомир в ' . ($r['prep'] ?? ''),
                ];

            case '404':
                return [($en ? 'Page not found' : 'Страница не найдена') . " | $brand"];
        }

        if ($ctx['object'] instanceof WP_Post) {
            if (!$en && isset(self::PAGES[$ctx['slug']])) {
                $t = self::PAGES[$ctx['slug']][0];
                return [$t, preg_replace('/\s*\|\s*' . preg_quote($brand, '/') . '$/u', '', $t)];
            }
            $t = trim(wp_strip_all_tags(get_the_title($ctx['object'])));
            return ["$t | $brand", $t];
        }

        return [$brand];
    }

    public static function fitTitle(array $candidates): string
    {
        $candidates = array_values(array_filter(array_map(static fn ($t) => trim(preg_replace('/\s+/u', ' ', (string) $t)), $candidates)));
        foreach ($candidates as $t) {
            if (mb_strlen($t) <= self::TITLE_MAX) {
                return $t;
            }
        }
        return end($candidates) ?: Org::NAME;
    }

    public static function description(): string
    {
        $manual = trim((string) Context::seoField('seo_description'));
        if ($manual !== '') {
            return $manual;
        }

        $ctx = Context::get();
        $en = Context::lang() === 'en';
        $d = '';

        switch ($ctx['type']) {
            case 'front':
                $d = $en
                    ? 'GazKotelBel — official representative of ATEM: Zhytomyr gas, solid fuel and universal boilers, gas convectors and VPG water heaters in Belarus. Official warranty and dealers nationwide.'
                    : 'Газовые котлы Житомир от официального представителя АТЕМ в Беларуси: дымоходные и турбированные, твердотопливные и универсальные котлы, конвекторы и колонки ВПГ.';
                break;

            case 'product':
                $p = $ctx['object'];
                $name = Context::productTitle($p);
                $sub = Context::productSubtitle($p);
                $s = ProductData::summary($p->ID);
                if ($en) {
                    $d = "$name — $sub. Official warranty, dealers across Belarus.";
                    break;
                }
                $parts = [$name . ($sub !== '' ? ' — ' . mb_strtolower(mb_substr($sub, 0, 1)) . mb_substr($sub, 1) : '') . '.'];
                if ($s['power_min'] !== null) {
                    $parts[] = 'Мощность ' . ProductData::powerRange($s) . ($s['count'] > 1 ? ', ' . $s['count'] . ' модификац' . self::plural($s['count'], 'ия', 'ии', 'ий') : '') . '.';
                }
                if ($s['min_price'] !== null) {
                    $parts[] = 'Цена от ' . ProductData::money($s['min_price']) . "\u{00A0}BYN.";
                }
                $parts[] = 'Официальная гарантия, дилеры по всей Беларуси.';
                $d = implode(' ', $parts);
                break;

            case 'category':
                $term = $ctx['object'];
                $name = Context::termName($term);
                if ($en) {
                    $d = "$name Zhytomyr from GazKotelBel, official ATEM representative: models, specifications and documentation. Dealers across Belarus.";
                    break;
                }
                $ts = ProductData::termSummary($term);
                $parts = ["$name Житомир от официального представителя АТЕМ: " . $ts['count'] . ' модел' . self::plural($ts['count'], 'ь', 'и', 'ей')];
                if ($ts['power_min'] !== null) {
                    $parts[0] .= ', мощность ' . ProductData::powerRange(['power_min' => $ts['power_min'], 'power_max' => $ts['power_max']]);
                }
                if ($ts['min_price'] !== null) {
                    $parts[0] .= ', цены от ' . ProductData::money($ts['min_price']) . "\u{00A0}BYN";
                }
                $parts[0] .= '.';
                $parts[] = 'Официальная гарантия, дилеры по всей Беларуси.';
                $d = implode(' ', $parts);
                break;

            case 'region':
                $r = Context::region();
                $n = count($r['dealers'] ?? []);
                $d = 'Где купить котлы Житомир и колонки ВПГ в ' . ($r['prep'] ?? '') . ': ' . $n . ' официальн' . self::plural($n, 'ый дилер', 'ых дилера', 'ых дилеров') . ' — адреса, телефоны, часы работы. Доставка и монтаж.';
                break;

            case 'article':
                $d = has_excerpt($ctx['object']) ? get_the_excerpt($ctx['object']) : wp_trim_words(wp_strip_all_tags(strip_shortcodes($ctx['object']->post_content)), 28, '…');
                break;

            case 'articles':
                $d = 'Статьи ГазКотелБел о выборе и расчёте газового котла, расходе газа, требованиях к котельной и дымоходу в Беларуси. Советы официального представителя котлов Житомир.';
                break;
        }

        if ($d === '' && $ctx['object'] instanceof WP_Post) {
            if (!$en && isset(self::PAGES[$ctx['slug']])) {
                $d = self::PAGES[$ctx['slug']][1];
            } else {
                $d = get_the_title($ctx['object']) . ' — ГазКотелБел, официальный представитель котлов Житомир в Беларуси.';
            }
        }

        return self::fitDescription($d, $en);
    }

    public static function fitDescription(string $d, bool $en = false): string
    {
        $d = trim(preg_replace('/\s+/u', ' ', html_entity_decode(wp_strip_all_tags($d), ENT_QUOTES, 'UTF-8')));
        if ($d === '') {
            return '';
        }
        if (!$en) {
            foreach (self::FILLERS as $filler) {
                if (mb_strlen($d) >= self::DESC_MIN) {
                    break;
                }
                if (mb_strpos($d, trim($filler)) === false && mb_strlen($d . $filler) <= self::DESC_MAX) {
                    $d .= $filler;
                }
            }
        }
        if (mb_strlen($d) > self::DESC_MAX) {
            $cut = mb_substr($d, 0, self::DESC_MAX - 1);
            $space = mb_strrpos($cut, ' ');
            $d = rtrim(mb_substr($cut, 0, $space ?: self::DESC_MAX - 1), " ,.;:—-") . '…';
        }
        return $d;
    }

    public static function robots(array $robots): array
    {
        if (is_admin()) {
            return $robots;
        }
        if (Context::isNoindex()) {
            return ['noindex' => true, 'follow' => true];
        }
        return [
            'index' => true,
            'follow' => true,
            'max-image-preview' => 'large',
            'max-snippet' => '-1',
            'max-video-preview' => '-1',
        ];
    }

    /** X-Robots-Tag для английской версии, служебных страниц и лент. */
    public static function headers(): void
    {
        if (is_admin() || headers_sent()) {
            return;
        }
        if (is_feed() || Context::isNoindex()) {
            header('X-Robots-Tag: noindex, follow', true);
        }
    }

    /** Изображение для соцсетей: [url, width, height, alt] или null. */
    public static function image(): ?array
    {
        $ctx = Context::get();
        $manual = Context::seoField('seo_image');
        if (is_array($manual) && !empty($manual['url'])) {
            return [$manual['url'], (int) ($manual['width'] ?? 0), (int) ($manual['height'] ?? 0), (string) ($manual['alt'] ?: $manual['title'] ?? '')];
        }

        $id = 0;
        $alt = '';
        if ($ctx['type'] === 'product') {
            $ids = (new Helpers())->getProductImageIds($ctx['object']);
            $id = $ids[0] ?? 0;
            $alt = Context::productTitle($ctx['object']);
        } elseif ($ctx['object'] instanceof WP_Post && has_post_thumbnail($ctx['object'])) {
            $id = (int) get_post_thumbnail_id($ctx['object']);
            $alt = get_the_title($ctx['object']);
        }

        if ($id) {
            $src = wp_get_attachment_image_src($id, 'full');
            if ($src) {
                return [$src[0], (int) $src[1], (int) $src[2], $alt];
            }
        }

        $og = Org::get()['og_image'];
        if (is_array($og) && !empty($og['url'])) {
            return [$og['url'], (int) ($og['width'] ?? 0), (int) ($og['height'] ?? 0), Org::NAME];
        }
        $logo = Org::get()['logo'];
        return $logo ? [$logo, 0, 0, Org::NAME] : null;
    }

    public static function head(): void
    {
        if (is_admin()) {
            return;
        }
        $ctx = Context::get();
        $en = Context::lang() === 'en';
        $title = wp_get_document_title();
        $desc = self::description();
        $canonical = Context::canonical();
        $org = Org::get();

        $out = [];
        if ($desc !== '') {
            $out[] = '<meta name="description" content="' . esc_attr($desc) . '">';
        }
        if ($canonical) {
            $out[] = '<link rel="canonical" href="' . esc_url($canonical) . '">';
        }

        if (Context::isProduction()) {
            if ($org['yandex_verification'] !== '') {
                $out[] = '<meta name="yandex-verification" content="' . esc_attr($org['yandex_verification']) . '">';
            }
            if ($org['google_verification'] !== '') {
                $out[] = '<meta name="google-site-verification" content="' . esc_attr($org['google_verification']) . '">';
            }
        }

        $ogType = ['product' => 'product', 'article' => 'article'][$ctx['type']] ?? 'website';
        $og = [
            'og:type' => $ogType,
            'og:site_name' => Org::NAME,
            'og:locale' => $en ? 'en_US' : 'ru_BY',
            'og:title' => $title,
            'og:description' => $desc,
            'og:url' => $canonical ?: home_url(add_query_arg([])),
        ];
        foreach ($og as $prop => $value) {
            if ($value !== '') {
                $out[] = '<meta property="' . $prop . '" content="' . esc_attr($value) . '">';
            }
        }

        $img = self::image();
        if ($img) {
            $out[] = '<meta property="og:image" content="' . esc_url($img[0]) . '">';
            if ($img[1] && $img[2]) {
                $out[] = '<meta property="og:image:width" content="' . (int) $img[1] . '">';
                $out[] = '<meta property="og:image:height" content="' . (int) $img[2] . '">';
            }
            $out[] = '<meta property="og:image:alt" content="' . esc_attr($img[3] ?: $title) . '">';
        }

        if ($ctx['type'] === 'article') {
            $out[] = '<meta property="article:published_time" content="' . esc_attr(get_the_date('c', $ctx['object'])) . '">';
            $out[] = '<meta property="article:modified_time" content="' . esc_attr(get_the_modified_date('c', $ctx['object'])) . '">';
        }

        $out[] = '<meta name="twitter:card" content="summary_large_image">';
        $out[] = '<meta name="twitter:title" content="' . esc_attr($title) . '">';
        if ($desc !== '') {
            $out[] = '<meta name="twitter:description" content="' . esc_attr($desc) . '">';
        }
        if ($img) {
            $out[] = '<meta name="twitter:image" content="' . esc_url($img[0]) . '">';
        }

        echo "\n<!-- gkb seo -->\n" . implode("\n", $out) . "\n";
    }

    private static function plural(int $n, string $one, string $few, string $many): string
    {
        $n = abs($n) % 100;
        $n1 = $n % 10;
        if ($n > 10 && $n < 20) {
            return $many;
        }
        if ($n1 > 1 && $n1 < 5) {
            return $few;
        }
        if ($n1 === 1) {
            return $one;
        }
        return $many;
    }
}
