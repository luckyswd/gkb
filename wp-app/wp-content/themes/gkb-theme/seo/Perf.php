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
