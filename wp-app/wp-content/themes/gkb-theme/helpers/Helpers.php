<?php

namespace helpers;

use WP_REST_Request;

class Helpers
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    private ?array $dataProduct = null;

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
        return $_COOKIE['lang'] ?? 'ru';
    }

    public function getProductTitle(
        object $product
    ) // Заголовок
    {
        return $this->getProductDataValue($product, 'language_title');
    }

    public function getProductSubtitle(
        object $product
    ) // Подзаголовок
    {
        return $this->getProductDataValue($product, 'language_subtitle');
    }

    public function getProductDescription(
        object $product
    ) // Описание
    {
        return $this->getProductDataValue($product, 'language_description');
    }

    public function getProductDescriptionCatalog(
        object $product
    ) // Описание для каталога
    {
        return $this->getProductDataValue($product, 'language_description_catalog');
    }

    public function getProductButtonName(
        object $product
    ) // Название кнопки
    {
        return $this->getProductDataValue($product, 'language_button_name');
    }

    public function getProductSliderImage(
        object $product
    ): false|string // Картинки для товара
    {
        return wp_get_attachment_image_url($this->getProductDataValue($product, 'language_image_slider_0_image'), 'full');
    }

    public function getProductIconFunctionality(
        object $product
    ): array //Иконки функциональности
    {
        $data = $this->getProductData($product);
        $count = $this->getProductDataValue($product, 'language_functionality_icons');
        $fields = [];

        for ($i = 0; $i < $count; $i++) {
            $fields[] = match ((new Helpers)->getLang()) {
                'ru' => wp_get_attachment_image_url($data['ru_language_image_slider_'. $i .'_image'], 'full') ?? '',
                'en' => wp_get_attachment_image_url($data['en_language_image_slider_'. $i .'_image'], 'full') ?? '',
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
        if ($this->dataProduct) {
            return $this->dataProduct;
        }

        $content = $product->post_content;
        $blocks = parse_blocks($content);
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'acf/product') {
                $this->dataProduct = $block['attrs']['data'] ?? '';

                return $this->dataProduct;
            }
        }
    }

    public static function getPictureImage(
        ?array $image = null,
        ?int   $width = null,
        ?int   $height = null,
    ): void
    {
        include get_template_directory() . '/components/picture.php';
    }
}