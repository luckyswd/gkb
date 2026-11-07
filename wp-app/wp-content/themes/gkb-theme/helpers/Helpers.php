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
        if (!empty($params)) {
            foreach ($params as $key => $param) {
                $request->set_param($key, $param);
            }
        }

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
        // Параметр ?lang= в адресе важнее куки: после переключения языка
        // страница открывается с ним, чтобы кеш страниц не отдал старую версию.
        $fromUrl = $_GET['lang'] ?? null;
        if ($fromUrl === 'ru' || $fromUrl === 'en') {
            return $fromUrl;
        }

        // Любое другое значение куки считаем русским — иначе match() в
        // get_field_multi_lang упадёт с UnhandledMatchError.
        return ($_COOKIE['lang'] ?? 'ru') === 'en' ? 'en' : 'ru';
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
        object $product,
        string $size = 'full'
    ): false|string
    {
        return wp_get_attachment_image_url($this->getProductDataValue($product, 'language_image_slider_0_image'), $size);
    }

    // функциональности
    public function getProductFunctionality(
        object $product
    ): array
    {
        $data = $this->getProductData($product);
        $count = $this->getProductDataValue($product, 'language_functionality_icons');
        $fields = [];

        $prefix = $this->getLang() === 'en' ? 'en_language' : 'ru_language';

        for ($i = 0; $i < $count; $i++) {
            $key = $prefix . '_functionality_icons_' . $i;
            // при отсутствии вложения функция возвращает false — приводим к массиву,
            // чтобы не получить предупреждение об обращении к индексу у bool
            $image = wp_get_attachment_image_src($data[$key . '_icon'] ?? 0, 'full') ?: [];

            // Иконки загружены спрайтами 64×32: левая половина — обычное
            // состояние, правая показывается при наведении. Одинарную картинку
            // сдвигать нельзя, поэтому ширину и высоту отдаём в шаблон.
            $fields[] = [
                'icon' => $image[0] ?? '',
                'width' => $image[1] ?? 0,
                'height' => $image[2] ?? 0,
                'is_sprite' => !empty($image[1]) && !empty($image[2]) && $image[1] >= $image[2] * 1.8,
                'text' => $data[$key . '_caption'] ?? '',
            ];
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

    /**
     * ID вложений галереи товара в порядке вывода. Нужны SEO-слою:
     * og:image, image в JSON-LD и sitemap, preload LCP-изображения.
     */
    public function getProductImageIds(
        object $product
    ): array
    {
        $data = $this->getProductData($product);
        if (!is_array($data)) {
            return [];
        }
        $prefix = $this->getLang() === 'en' ? 'en_language' : 'ru_language';
        $count = (int) ($data[$prefix . '_image_slider'] ?? 0);
        $ids = [];
        for ($i = 0; $i < $count; $i++) {
            $id = (int) ($data[$prefix . '_image_slider_' . $i . '_image'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    /** Значение поля товара из блока acf/product на текущем языке. */
    public function getProductField(
        object $product,
        string $fieldName,
    )
    {
        return $this->getProductDataValue($product, 'language_' . $fieldName);
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

    /**
     * Оборачивает каждую таблицу из визуального редактора в прокручиваемый
     * контейнер. Редактор работает в визуальном режиме и обёртку руками
     * потерял бы при первой же правке, поэтому она ставится на выводе.
     * Вложенные таблицы не поддерживаются: при несовпадении числа открывающих
     * и закрывающих тегов разметка возвращается нетронутой.
     */
    public static function wrapTables(?string $html): string
    {
        if (empty($html) || stripos($html, '<table') === false) {
            return (string) $html;
        }

        $open = preg_match_all('#<table\b#i', $html);
        $close = preg_match_all('#</table>#i', $html);

        if ($open !== $close) {
            return $html;
        }

        // Оборачиваются только таблицы верхнего уровня: вставки из Excel
        // приходят с таблицами внутри ячеек, и вложенная обёртка рисовала бы
        // рамку вокруг пустой ячейки.
        $out = '';
        $depth = 0;
        $offset = 0;

        if (!preg_match_all('#<table\b[^>]*>|</table>#i', $html, $m, PREG_OFFSET_CAPTURE)) {
            return $html;
        }

        foreach ($m[0] as $tag) {
            [$text, $pos] = $tag;
            $out .= substr($html, $offset, $pos - $offset);
            $offset = $pos + strlen($text);

            if ($text[1] === '/') {
                $depth--;
                $out .= $depth === 0 ? $text . '</div>' : $text;
            } else {
                if ($depth === 0) {
                    $out .= '<div class="gkb-table-scroll' . self::tableVariant($html, $pos) . '">' . $text;
                } else {
                    $out .= $text;
                }
                $depth++;
            }
        }

        return $out . substr($html, $offset);
    }

    /**
     * Вид таблицы по числу колонок в её первой строке:
     *  - до двух колонок — список «параметр — значение»: без рамки и зебры;
     *  - три колонки (комплектация: что / сколько / примечание) — компактная
     *    таблица по ширине экрана, с переносом и без прокрутки;
     *  - четыре и больше (модели) — широкая таблица с прокруткой внутри рамки.
     */
    private static function tableVariant(string $html, int $tablePos): string
    {
        $cells = self::topRowCells($html, $tablePos);

        if ($cells > 0 && $cells <= 2) {
            return ' gkb-table-scroll--plain';
        }

        return $cells === 3 ? ' gkb-table-scroll--compact' : '';
    }

    /**
     * Число ячеек первой строки самой таблицы. Вложенные таблицы не
     * считаются: в прайсе из Excel внутри первой ячейки лежит ещё одна.
     */
    private static function topRowCells(string $html, int $tablePos): int
    {
        if (!preg_match_all('#<table\b|</table>|<t[dh]\b|</tr>#i', $html, $m, PREG_OFFSET_CAPTURE, $tablePos)) {
            return 0;
        }

        $depth = 0;
        $cells = 0;

        foreach ($m[0] as [$tag]) {
            $tag = strtolower($tag);

            if ($tag === '<table') {
                $depth++;
            } elseif ($tag === '</table>') {
                $depth--;
                if ($depth === 0) {
                    break;
                }
            } elseif ($depth === 1) {
                if ($tag === '</tr>') {
                    break;
                }
                $cells++;
            }
        }

        return $cells;
    }

    public static function getPictureImage(
        ?array  $image = null,
        ?int    $width = null,
        ?int    $height = null,
        string  $sizes = '100vw',
        bool    $priority = false,
        ?string $alt = null,
    ): void
    {
        include get_template_directory() . '/components/picture.php';
    }

    public static function getProductsByCategory(
        ?WP_Term $term = null,
        ?string $search = '',
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
            's' => $search,
        ];

        return get_posts(array_merge($args, $taxQuery ?? []));
    }

    public function getFeedbackForm(): string {
        if ($this->getLang() === 'ru') {
            return get_field('feedback_ru', 'option');
        }

        if ($this->getLang() === 'en') {
            return get_field('feedback_en', 'option');
        }

        return '';
    }
}