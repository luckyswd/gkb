<?php

/*
 * ACF-блоки в редакторе — только в режиме preview.
 *
 * С WordPress 7 холст редактора всегда рендерится в iframe. В режиме edit ACF 6.1.6
 * кладёт форму с полями в этот iframe, а визуальные редакторы ищет в основном документе:
 * не находит, падает на buildQuicktags и оставляет поля без TinyMCE и панели кнопок.
 * В режиме preview форма живёт в боковой панели — там редакторы поднимаются нормально.
 *
 * Сохранённые блоки записаны с "mode":"edit", поэтому при отдаче записи в редактор
 * режим подменяется на preview. Кавычки в значениях атрибутов WordPress экранирует
 * как ", так что строка "mode":"edit" может быть только атрибутом блока,
 * а не текстом внутри поля.
 */
function acf_blocks_force_preview_mode(WP_REST_Response $response, WP_Post $post, WP_REST_Request $request): WP_REST_Response
{
    if ($request['context'] !== 'edit' || empty($response->data['content']['raw'])) {
        return $response;
    }

    $content = preg_replace(
        '/(<!-- wp:acf\/[a-z0-9-]+ \{.*?)"mode":"edit"/',
        '$1"mode":"preview"',
        $response->data['content']['raw']
    );

    // preg_replace отдаёт null при ошибке PCRE — тогда оставляем контент как есть.
    if (is_string($content)) {
        $response->data['content']['raw'] = $content;
    }

    return $response;
}

function acf_blocks_register_preview_mode_filter(): void
{
    foreach (get_post_types(['show_in_rest' => true]) as $post_type) {
        add_filter("rest_prepare_{$post_type}", 'acf_blocks_force_preview_mode', 10, 3);
    }
}

add_action('rest_api_init', 'acf_blocks_register_preview_mode_filter');
