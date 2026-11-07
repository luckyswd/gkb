<?php

namespace seo;

/**
 * Раздел статей /stati/: отдельный тип записи, чтобы не трогать общую
 * структуру постоянных ссылок и скрытые стандартные «Записи».
 */
class Articles
{
    public static function register(): void
    {
        add_action('init', [self::class, 'postType']);
        add_filter('the_content', [self::class, 'anchors'], 20);
    }

    public static function postType(): void
    {
        register_post_type('articles', [
            'labels' => [
                'name' => 'Статьи',
                'singular_name' => 'Статья',
                'add_new' => 'Добавить статью',
                'add_new_item' => 'Новая статья',
                'edit_item' => 'Редактировать статью',
                'view_item' => 'Открыть статью',
                'all_items' => 'Все статьи',
                'search_items' => 'Найти статью',
                'not_found' => 'Статей нет',
                'menu_name' => 'Статьи',
            ],
            'public' => true,
            'has_archive' => 'stati',
            'rewrite' => ['slug' => 'stati', 'with_front' => false],
            'menu_icon' => 'dashicons-media-document',
            'menu_position' => 6,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
            'show_in_rest' => true,
        ]);
    }

    /** Якоря у h2 статьи для оглавления. */
    public static function anchors(string $content): string
    {
        if (!is_singular('articles') || !in_the_loop() || !is_main_query()) {
            return $content;
        }
        $used = [];
        return preg_replace_callback('#<h2(\s[^>]*)?>(.*?)</h2>#is', static function ($m) use (&$used) {
            $attrs = $m[1] ?? '';
            if (stripos($attrs, ' id=') !== false) {
                return $m[0];
            }
            $id = self::slug(wp_strip_all_tags($m[2]));
            $base = $id;
            $i = 2;
            while (isset($used[$id])) {
                $id = $base . '-' . $i++;
            }
            $used[$id] = true;
            return '<h2' . $attrs . ' id="' . esc_attr($id) . '">' . $m[2] . '</h2>';
        }, $content) ?? $content;
    }

    /** Оглавление по h2: [[id, text], ...]. */
    public static function toc(string $html): array
    {
        preg_match_all('#<h2[^>]*\sid="([^"]+)"[^>]*>(.*?)</h2>#is', $html, $m, PREG_SET_ORDER);
        return array_map(static fn ($x) => [$x[1], trim(wp_strip_all_tags($x[2]))], $m);
    }

    private static function slug(string $text): string
    {
        $slug = sanitize_title(function_exists('transliterator_transliterate') ? transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text) : $text);
        return $slug !== '' ? $slug : 'section';
    }

    /** Модели для блока «Подходящие модели»: ручной список из поля или первые газовые котлы. */
    public static function relatedProducts(\WP_Post $article, int $limit = 3): array
    {
        $manual = function_exists('get_field') ? (array) get_field('similar_products', $article->ID) : [];
        $manual = array_values(array_filter($manual, static fn ($p) => $p instanceof \WP_Post && $p->post_status === 'publish'));
        if ($manual) {
            return array_slice($manual, 0, $limit);
        }
        $term = get_term_by('slug', 'gas-boilers', 'product-category');
        return array_slice(\helpers\Helpers::getProductsByCategory($term ?: null), 0, $limit);
    }
}
