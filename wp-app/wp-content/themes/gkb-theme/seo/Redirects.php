<?php

namespace seo;

/**
 * 301 со снятых с витрины и мусорных адресов: старые карты сайта, тестовая
 * запись, пустая рубрика, вложения, архивы авторов и дат, поиск WordPress.
 * Список снятых товаров и категорий — removed_urls_map() в hooks_filters/removed_urls.php.
 */
class Redirects
{
    public const LEGACY_SITEMAPS = [
        'wp-sitemap.xml', 'sitemap_index.xml', 'post-sitemap.xml', 'page-sitemap.xml', 'products-sitemap.xml',
        'product-category-sitemap.xml', 'category-sitemap.xml', 'sitemap.rss', 'default-sitemap.xsl',
    ];

    public static function register(): void
    {
        add_action('parse_request', [self::class, 'early'], 0);
        add_action('template_redirect', [self::class, 'late'], 1);
    }

    private static function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        return is_string($path) ? trim(strtolower(rawurldecode($path)), '/') : '';
    }

    private static function go(string $to): void
    {
        wp_safe_redirect($to, 301, 'gkb-seo');
        exit;
    }

    /** До разбора запроса: старые карты сайта и снятые адреса. */
    public static function early(): void
    {
        $path = self::path();

        if (in_array($path, self::LEGACY_SITEMAPS, true) || preg_match('#^wp-sitemap-.+\.xml$#', $path)) {
            self::go(home_url('/sitemap.xml'));
        }

        if (function_exists('removed_urls_map')) {
            $map = removed_urls_map();
            if (isset($map[$path])) {
                self::go(home_url($map[$path]));
            }
        }

        // тестовая запись и рубрика по умолчанию
        if ($path === 'привет-мир' || $path === 'hello-world' || preg_match('#^category/(без-рубрики|uncategorized)$#u', $path)) {
            self::go(home_url('/'));
        }
    }

    /** После разбора запроса: вложения, авторы, даты, поиск ядра, рубрики. */
    public static function late(): void
    {
        if (is_admin()) {
            return;
        }
        if (is_attachment()) {
            $parent = (int) wp_get_post_parent_id(get_queried_object_id());
            self::go($parent && get_post_status($parent) === 'publish' ? get_permalink($parent) : home_url('/'));
        }
        if (is_author() || is_date() || is_category() || is_tag()) {
            self::go(home_url('/'));
        }
        if (is_singular('post')) {
            self::go(home_url('/'));
        }
        if (is_search() && !is_admin()) {
            $q = get_search_query(false);
            self::go(add_query_arg('search', rawurlencode($q), home_url('/catalog/')));
        }
    }
}
