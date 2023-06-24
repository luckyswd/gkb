<?php

namespace helpers;

use WP_REST_Request;
use WP_Term;

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
        string      $field = null,
        bool|string $postId = false,
    ): array|object|null
    {
        if ($field) {
            $field = get_field($field, $postId);
            return match ($this->getLang()) {
                'ru' => $field['ru_language'] ?? null,
                'en' => $field['en_language'] ?? null,
            };
        }
        return match ($this->getLang()) {
            'ru' => get_field('ru_language', $postId ?? ''),
            'en' => get_field('en_language', $postId ?? ''),
        };
    }

    public function getLang()
    {
        return $_COOKIE['lang'] ?? 'ru';
    }

    // Заголовок
    public function getProductTitle(
        object $product
    )
    {
        return $this->getProductDataValue($product, 'language_title');
    }

    // Подзаголовок
    public function getProductSubtitle(
        object $product
    )
    {
        return $this->getProductDataValue($product, 'language_subtitle');
    }

    // Описание
    public function getProductDescription(
        object $product
    )
    {
        return $this->getProductDataValue($product, 'language_description');
    }

    // Описание для каталога
    public function getProductDescriptionCatalog(
        object $product
    )
    {
        return $this->getProductDataValue($product, 'language_description_catalog');
    }

    // Название кнопки
    public function getProductButtonName(
        object $product
    )
    {
        return $this->getProductDataValue($product, 'language_button_name');
    }

    // Картинки для товара
    public function getProductSliderImage(
        object $product
    ): false|string
    {
        return wp_get_attachment_image_url($this->getProductDataValue($product, 'language_image_slider_0_image'), 'full');
    }

    // функциональности
    public function getProductFunctionality(
        object $product
    ): array
    {
        $data = $this->getProductData($product);
        $count = $this->getProductDataValue($product, 'language_functionality_icons');
        $fields = [];

        for ($i = 0; $i < $count; $i++) {
            $fields[] = match ((new Helpers)->getLang()) {
                'ru' => [
                    'icon' => wp_get_attachment_image_url($data['ru_language_functionality_icons_'. $i .'_icon'], 'full') ?? '',
                    'text' => $data['ru_language_functionality_icons_'. $i .'_caption'],
                ],
                'en' => [
                    'icon' => wp_get_attachment_image_url($data['en_language_functionality_icons_'. $i .'_icon'], 'full') ?? '',
                    'text' => $data['en_language_functionality_icons_'. $i .'_caption'],
                ],
            };
        }

        return $fields;
    }

    public function getProductDocumentation(
        object $product
    ): array
    {
        $data = $this->getProductData($product);
        $count = $this->getProductDataValue($product, 'language_documentation');
        $fields = [];

        for ($i = 0; $i < $count; $i++) {
            $fields[] = match ((new Helpers)->getLang()) {
                'ru' => [
                    'name_file' => $data['ru_language_documentation_'. $i .'_name_file'] ?? '',
                    'file' => wp_get_attachment_url($data['ru_language_documentation_'. $i .'_file']),
                ],
                'en' => [
                    'name_file' => $data['en_language_documentation_'. $i .'_name_file'] ?? '',
                    'file' => wp_get_attachment_url($data['en_language_documentation_'. $i .'_file']),
                ],
            };
        }
        return $fields;
    }

    private function getProductDataValue(
        object $product,
        string $fieldName,
    )
    {
        $data = $this->getProductData($product);

        return match ((new Helpers)->getLang()) {
            'ru' => $data['ru_' . $fieldName] ?? '',
            'en' => $data['en_' . $fieldName] ?? '',
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

        return '';
    }

    public static function getPictureImage(
        ?array $image = null,
        ?int   $width = null,
        ?int   $height = null,
    ): void
    {
        include get_template_directory() . '/components/picture.php';
    }

    public static function getProductsByCategory(
        ?WP_Term $term = null,
    ): array {

        if ($term) {
            $taxQuery = [
                'tax_query' => [
                    [
                        'taxonomy' => 'product-category',
                        'field' => 'term_id',
                        'terms' => $term->term_id,
                    ]
                ]
            ];
        }

        $args = [
            'post_type' => 'products',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'order' => 'ASC',
        ];

        return get_posts(array_merge($args, $taxQuery ?? []));
    }
}