<?php

namespace helpers;

use WP_REST_Request;

class Helpers
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';

    public static function getRequest(
        string $method,
        array  $params,
    ): WP_REST_Request
    {
        $request = new WP_REST_Request();
        $request->set_method($method);
        $request->set_body_params($params);

        return $request;
    }

    public function get_field_multi_lang(
        string      $lang,
        string      $field = null,
        bool|string $postId = false,
    ): array|object|null
    {
        if ($field) {
            $field = get_field($field, $postId);
            return match ($lang) {
                'ru' => $field['ru_language'] ?? null,
                'en' => $field['en_language'] ?? null,
            };
        }

        return match ($lang) {
            'ru' => get_field('ru_language', $postId ?? ''),
            'en' => get_field('en_language', $postId ?? ''),
        };
    }

    public function getLang()
    {
        return $_COOKIE['lang'];
    }

    public function getProductTitle(
        object $product
    )
    {
        $data = $this->getProductData($product);
        return match ((new Helpers)->getLang()) {
            'ru' => $data['ru_language_title'] ?? '',
            'en' => $data['en_language_title'] ?? '',
        };
    }

    private function getProductData(
        object $product
    )
    {
        $content = $product->post_content;

        $blocks = parse_blocks($content);
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'acf/product') {
                return $block['attrs']['data'] ?? '';
            }
        }
    }
}