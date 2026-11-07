<?php

namespace seo;

use helpers\Helpers;
use WP_Post;
use WP_Term;

/**
 * Что за страница сейчас отдаётся: тип, язык, канонический адрес, хлебные
 * крошки. Один источник для мета-тегов, JSON-LD и noindex.
 */
class Context
{
    /** Служебные страницы, которые не индексируются. */
    public const SERVICE_SLUGS = ['login', 'registration', 'password-recovery', 'forgot-password', 'reset-password', 'lost-password'];

    public const CALCULATOR_SLUG = 'kalkulyator-moshchnosti-kotla';

    private static ?array $cache = null;

    /** Адрес калькулятора мощности, если такая страница опубликована, иначе null. */
    public static function calculatorUrl(): ?string
    {
        $page = get_page_by_path(self::CALCULATOR_SLUG);
        return ($page instanceof \WP_Post && $page->post_status === 'publish') ? trailingslashit(get_permalink($page)) : null;
    }

    public static function lang(): string
    {
        return (new Helpers())->getLang();
    }

    public static function isProduction(): bool
    {
        return untrailingslashit(home_url()) === 'https://gazkotelbel.com';
    }

    /** Тип текущей страницы. */
    public static function type(): string
    {
        return self::get()['type'];
    }

    public static function get(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $type = 'other';
        $object = get_queried_object();

        if (get_query_var('gkb_region')) {
            $type = 'region';
        } elseif (is_front_page()) {
            $type = 'front';
        } elseif (is_singular('products')) {
            $type = 'product';
        } elseif (is_tax('product-category')) {
            $type = 'category';
        } elseif (is_singular('articles')) {
            $type = 'article';
        } elseif (is_post_type_archive('articles')) {
            $type = 'articles';
        } elseif (is_page()) {
            $slug = $object instanceof WP_Post ? $object->post_name : '';
            if ($slug === 'catalog') {
                $type = trim((string) ($_GET['search'] ?? '')) !== '' ? 'search' : 'catalog';
            } elseif ($slug === self::CALCULATOR_SLUG) {
                $type = 'calculator';
            } else {
                $type = 'page';
            }
        } elseif (is_search()) {
            $type = 'search';
        } elseif (is_404()) {
            $type = '404';
        } elseif (is_attachment()) {
            $type = 'attachment';
        }

        return self::$cache = [
            'type' => $type,
            'object' => $object,
            'slug' => $object instanceof WP_Post ? $object->post_name : ($object instanceof WP_Term ? $object->slug : ''),
        ];
    }

    /** Сбрасывает кеш контекста (нужен, если запрос подменили после старта). */
    public static function reset(): void
    {
        self::$cache = null;
    }

    public static function region(): ?array
    {
        $slug = (string) get_query_var('gkb_region');
        return $slug !== '' ? Regions::get($slug) : null;
    }

    /** Абсолютный канонический адрес со слешем и без параметров, или null. */
    public static function canonical(): ?string
    {
        $ctx = self::get();
        $url = null;

        switch ($ctx['type']) {
            case 'front':
                $url = home_url('/');
                break;
            case 'region':
                $url = home_url('/where-to-buy/' . get_query_var('gkb_region') . '/');
                break;
            case 'category':
                $link = get_term_link($ctx['object']);
                $url = is_wp_error($link) ? null : $link;
                break;
            case 'articles':
                $url = get_post_type_archive_link('articles');
                $paged = (int) get_query_var('paged');
                if ($url && $paged > 1) {
                    $url = trailingslashit($url) . 'page/' . $paged . '/';
                }
                break;
            case 'search':
                $url = home_url('/catalog/');
                break;
            case 'product':
            case 'article':
            case 'page':
            case 'catalog':
            case 'calculator':
                $url = get_permalink($ctx['object']);
                break;
        }

        if (!$url) {
            return null;
        }

        $url = strtok($url, '?#');
        return trailingslashit($url);
    }

    /** Название текущей категории товаров на текущем языке. */
    public static function termName(WP_Term $term): string
    {
        if (self::lang() === 'en') {
            $en = get_field('name_en', $term);
            if (!empty($en)) {
                return (string) $en;
            }
        }
        return $term->name;
    }

    public static function productTitle(WP_Post $product): string
    {
        $title = (new Helpers())->getProductTitle($product);
        return trim(wp_strip_all_tags((string) ($title ?: $product->post_title)));
    }

    public static function productSubtitle(WP_Post $product): string
    {
        $helper = new Helpers();
        $sub = $helper->getProductDescriptionCatalog($product) ?: $helper->getProductSubtitle($product);
        return trim(wp_strip_all_tags((string) $sub));
    }

    public static function productCategory(WP_Post $product): ?WP_Term
    {
        $terms = get_the_terms($product->ID, 'product-category');
        return (!empty($terms) && !is_wp_error($terms)) ? $terms[0] : null;
    }

    /**
     * Хлебные крошки текущей страницы: [[name, url], ...], последний — текущая
     * страница. Совпадают с видимыми крошками шаблонов.
     */
    public static function crumbs(): array
    {
        $ctx = self::get();
        $en = self::lang() === 'en';
        $home = [$en ? 'Home' : 'Главная', home_url('/')];
        $catalog = [self::allProductsLabel(), home_url('/catalog/')];
        $here = self::canonical();

        switch ($ctx['type']) {
            case 'front':
                return [];
            case 'product':
                $items = [$home, $catalog];
                $cat = self::productCategory($ctx['object']);
                if ($cat) {
                    $items[] = [self::termName($cat), get_term_link($cat)];
                }
                $items[] = [self::productTitle($ctx['object']), $here];
                return $items;
            case 'category':
                return [$home, $catalog, [self::termName($ctx['object']), $here]];
            case 'catalog':
            case 'search':
                return [$home, [self::allProductsLabel(), home_url('/catalog/')]];
            case 'article':
                return [$home, [$en ? 'Articles' : 'Статьи', get_post_type_archive_link('articles')], [get_the_title($ctx['object']), $here]];
            case 'articles':
                return [$home, [$en ? 'Articles' : 'Статьи', get_post_type_archive_link('articles')]];
            case 'region':
                $region = self::region();
                return [$home, [$en ? 'Where to buy' : 'Где купить', home_url('/where-to-buy/')], [$region['name'] ?? '', $here]];
            case 'page':
            case 'calculator':
                return [$home, [get_the_title($ctx['object']), $here]];
        }
        return [];
    }

    public static function allProductsLabel(): string
    {
        $fields = (new Helpers())->get_field_multi_lang('header_links', 'option');
        return (string) ($fields['all_products'] ?? (self::lang() === 'en' ? 'All products' : 'Вся продукция'));
    }

    /** Поле SEO текущего объекта (страница, товар, статья, категория). */
    public static function seoField(string $name)
    {
        $ctx = self::get();
        if (!function_exists('get_field')) {
            return null;
        }
        if ($ctx['object'] instanceof WP_Post && in_array($ctx['type'], ['product', 'article', 'page', 'catalog', 'calculator', 'front'], true)) {
            return get_field($name, $ctx['object']->ID);
        }
        if ($ctx['object'] instanceof WP_Term && $ctx['type'] === 'category') {
            return get_field($name, $ctx['object']);
        }
        return null;
    }

    /** Должна ли страница быть закрыта от индексации. */
    public static function isNoindex(): bool
    {
        $ctx = self::get();

        if (self::lang() === 'en') {
            return true;
        }
        if (in_array($ctx['type'], ['search', '404', 'attachment', 'other'], true)) {
            return true;
        }
        if ($ctx['type'] === 'page' && in_array($ctx['slug'], self::SERVICE_SLUGS, true)) {
            return true;
        }
        if ($ctx['type'] === 'calculator' && !empty($_GET['area'])) {
            return true;
        }
        if (is_feed() || is_author() || is_date() || is_paged() && $ctx['type'] !== 'articles') {
            return true;
        }
        if (!empty(self::seoField('seo_noindex'))) {
            return true;
        }
        return false;
    }

    /** Закрыт ли объект от индексации флагом SEO (для sitemap и llms.txt). */
    public static function objectNoindex($object): bool
    {
        if ($object instanceof WP_Post) {
            if ($object->post_type === 'page' && in_array($object->post_name, self::SERVICE_SLUGS, true)) {
                return true;
            }
            return function_exists('get_field') && !empty(get_field('seo_noindex', $object->ID));
        }
        if ($object instanceof WP_Term) {
            return function_exists('get_field') && !empty(get_field('seo_noindex', $object));
        }
        return false;
    }
}
