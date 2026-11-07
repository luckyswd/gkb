<?php

namespace seo;

use helpers\Helpers;

/**
 * Скорость: размеры изображений, preload LCP-изображения, Speculation Rules,
 * preconnect, токен Origin Trial для WebMCP, defer для скриптов темы.
 */
class Perf
{
    /** sizes для типовых мест вывода. */
    public const SIZES_BANNER = '(max-width: 1440px) calc(100vw - 32px), 1400px';
    public const SIZES_GALLERY = '(max-width: 900px) calc(100vw - 32px), 560px';
    public const SIZES_CARD = '(max-width: 600px) 42vw, 220px';

    public static function register(): void
    {
        add_action('after_setup_theme', static function () {
            add_image_size('gkb-card', 400, 0, false);
            add_image_size('gkb-card-2x', 800, 0, false);
            add_image_size('gkb-gallery', 720, 0, false);
            add_image_size('gkb-gallery-2x', 1200, 0, false);
            add_image_size('gkb-banner-sm', 768, 0, false);
            add_image_size('gkb-banner', 1280, 0, false);
            add_image_size('gkb-banner-2x', 1920, 0, false);
        });
        add_filter('max_srcset_image_width', static fn () => 2000);

        add_action('wp_head', [self::class, 'early'], 2);
        // Speculation Rules выводит ядро WordPress (6.8+): настраиваем его, а не дублируем
        add_filter('wp_speculation_rules_configuration', static function ($config) {
            return is_user_logged_in() ? null : ['mode' => 'prefetch', 'eagerness' => 'moderate'];
        });
        add_filter('wp_speculation_rules_href_exclude_paths', static function ($paths) {
            return array_merge((array) $paths, ['/login/*', '/registration/*', '/*.pdf']);
        });
        if (!function_exists('wp_get_speculation_rules_configuration')) {
            add_action('wp_head', [self::class, 'speculation'], 40); // старое ядро без встроенных правил
        }
        add_filter('wp_resource_hints', [self::class, 'hints'], 10, 2);

        /* WP-Optimize склеивает скрипты подвала в один файл и теряет у них strategy=defer:
           склейка (~170 КБ) блокирует разбор, и LCP-картинка ждёт отрисовки ~2 с
           (PageSpeed каталога 07.11.2026). Все исходные скрипты темы — defer,
           встроенный wpcf7 объявлен до склейки, так что defer ей безопасен. */
        add_filter('script_loader_tag', static function ($tag, $handle) {
            if (str_starts_with((string) $handle, 'wpo_min-footer') && !str_contains($tag, ' defer')) {
                $tag = str_replace(' src=', ' defer src=', $tag);
            }
            return $tag;
        }, 10, 2);

        // Критический CSS первого экрана — в <head>, основной CSS — без блокировки отрисовки
        add_action('wp_head', [self::class, 'criticalCss'], 1);
        add_filter('style_loader_tag', [self::class, 'asyncStyle'], 10, 4);
    }

    /** Тип страницы → файл assets/critical/{файл}.css (генерирует tools/critical/extract.mjs). */
    private const CRITICAL = [
        'front' => 'front',
        'catalog' => 'catalog',
        'search' => 'catalog',
        'category' => 'category',
        'product' => 'product',
        'page' => 'page',
        'region' => 'region',
    ];

    /** Путь к файлу критического CSS текущей страницы или null — тогда CSS грузится как обычно. */
    private static function criticalPath(): ?string
    {
        static $path = false;
        if ($path !== false) {
            return $path;
        }
        $path = null;
        if (is_admin() || is_user_logged_in() || is_customize_preview()) {
            return $path;
        }
        $key = self::CRITICAL[Context::type()] ?? null;
        $file = $key ? get_template_directory() . '/assets/critical/' . $key . '.css' : '';
        if ($file !== '' && is_readable($file) && filesize($file) > 0) {
            $path = $file;
        }
        return $path;
    }

    public static function criticalCss(): void
    {
        $file = self::criticalPath();
        if ($file === null) {
            return;
        }
        // Файл собран из CSS темы скриптом, а не из пользовательского ввода; </style> внутри не бывает
        $css = str_replace('</style', '<\/style', (string) file_get_contents($file));
        echo '<style id="gkb-critical-css">' . $css . "</style>\n";
    }

    /**
     * Основной CSS (склейка WP-Optimize или dist/css темы) при наличии критического CSS
     * подключается через preload и включается по onload; без JS — обычной ссылкой в <noscript>.
     */
    public static function asyncStyle($tag, $handle, $href, $media)
    {
        if (self::criticalPath() === null || !in_array($media, ['all', 'screen', ''], true)) {
            return $tag;
        }
        $handle = (string) $handle;
        $isMain = str_starts_with($handle, 'wpo_min-header')
            || preg_match('~/gkb-theme/dist/css/style\d*\.min\.css~', (string) $href);
        if (!$isMain || str_contains($tag, 'rel="preload"')) {
            return $tag;
        }
        $async = preg_replace(
            ['~rel=([\'"])stylesheet\1~', '~\smedia=([\'"])[^\'"]*\1~'],
            ['rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"', ''],
            $tag,
            1
        );
        return $async === null || $async === $tag ? $tag : $async . '<noscript>' . trim($tag) . "</noscript>\n";
    }

    /** ID LCP-изображения текущей страницы и его sizes, или null. */
    public static function lcp(): ?array
    {
        $ctx = Context::get();
        switch ($ctx['type']) {
            case 'front':
                $page = get_post((int) get_option('page_on_front'));
                if (!$page) {
                    return null;
                }
                foreach (parse_blocks($page->post_content) as $block) {
                    if (($block['blockName'] ?? '') === 'acf/slider') {
                        $prefix = Context::lang() === 'en' ? 'en_language' : 'ru_language';
                        $id = (int) ($block['attrs']['data'][$prefix . '_slider_0_image'] ?? 0);
                        return $id ? [$id, self::SIZES_BANNER] : null;
                    }
                }
                return null;

            case 'product':
                $ids = (new Helpers())->getProductImageIds($ctx['object']);
                return $ids ? [$ids[0], self::SIZES_GALLERY] : null;

            case 'category':
            case 'catalog':
                $products = Helpers::getProductsByCategory($ctx['type'] === 'category' ? $ctx['object'] : null);
                if (!$products) {
                    return null;
                }
                $ids = (new Helpers())->getProductImageIds($products[0]);
                return $ids ? [$ids[0], self::SIZES_CARD] : null;
        }
        return null;
    }

    public static function early(): void
    {
        if (is_admin()) {
            return;
        }
        $o = Org::get();
        if (Context::isProduction() && $o['origin_trial'] !== '') {
            foreach (preg_split('/\s+/', trim($o['origin_trial'])) as $token) {
                echo '<meta http-equiv="origin-trial" content="' . esc_attr($token) . '">' . "\n";
            }
        }

        $lcp = self::lcp();
        if ($lcp) {
            [$id, $sizes] = $lcp;
            $src = wp_get_attachment_image_url($id, 'full');
            $srcset = wp_get_attachment_image_srcset($id, 'full');
            if ($src) {
                echo '<link rel="preload" as="image" href="' . esc_url($src) . '"'
                    . ($srcset ? ' imagesrcset="' . esc_attr($srcset) . '" imagesizes="' . esc_attr($sizes) . '"' : '')
                    . ' fetchpriority="high">' . "\n";
            }
        }
    }

    public static function speculation(): void
    {
        if (is_admin() || is_user_logged_in()) {
            return;
        }
        $rules = [
            'prefetch' => [[
                'where' => ['and' => [
                    ['href_matches' => '/*'],
                    ['not' => ['href_matches' => ['/wp-admin/*', '/wp-login.php*', '/login/*', '/registration/*', '/*\\?*lang=*', '/*.pdf']]],
                    ['not' => ['selector_matches' => '[rel~=nofollow], [target=_blank], .user-logout']],
                ]],
                'eagerness' => 'moderate',
            ]],
        ];
        echo '<script type="speculationrules">' . wp_json_encode($rules, JSON_UNESCAPED_SLASHES) . "</script>\n";
    }

    public static function hints(array $urls, string $relation): array
    {
        if ($relation === 'preconnect' && Context::isProduction()) {
            $urls[] = 'https://mc.yandex.ru';
            $urls[] = 'https://tools.shefcode.tech';
        }
        if ($relation === 'dns-prefetch') {
            $urls[] = '//mc.yandex.ru';
        }
        return $urls;
    }

    /**
     * Атрибуты изображения по ID вложения: srcset/sizes, размеры, lazy или
     * fetchpriority=high для LCP.
     */
    public static function img(int $id, string $alt, string $sizes, bool $priority = false, array $attr = [], string $size = 'full'): string
    {
        if (!$id) {
            return '';
        }
        $attr = array_merge([
            'alt' => $alt,
            'sizes' => $sizes,
            'decoding' => 'async',
        ], $attr);
        if ($priority) {
            $attr['loading'] = 'eager';
            $attr['fetchpriority'] = 'high';
        } else {
            $attr['loading'] = 'lazy';
        }
        return (string) wp_get_attachment_image($id, $size, false, $attr);
    }
}
