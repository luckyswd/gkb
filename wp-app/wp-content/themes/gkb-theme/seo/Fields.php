<?php

namespace seo;

/**
 * Поля ACF SEO-слоя. Регистрируются кодом (local field groups), чтобы
 * структура жила в git вместе с шаблонами, которые её читают.
 */
class Fields
{
    public static function register(): void
    {
        add_action('acf/init', [self::class, 'groups']);
    }

    private static function f(string $name, string $label, string $type = 'text', array $extra = []): array
    {
        return array_merge([
            'key' => 'field_gkb_' . $name,
            'label' => $label,
            'name' => $name,
            'type' => $type,
        ], $extra);
    }

    private static function tab(string $key, string $label): array
    {
        return ['key' => 'field_gkb_tab_' . $key, 'label' => $label, 'name' => '', 'type' => 'tab', 'placement' => 'top'];
    }

    private static function faqField(string $suffix): array
    {
        return self::f('faq', 'Частые вопросы', 'repeater', [
            'key' => 'field_gkb_faq_' . $suffix,
            'instructions' => '3–8 вопросов. Ответ должен быть понятен без остальной страницы. Выводится только в русской версии и размечается FAQPage.',
            'layout' => 'block',
            'button_label' => 'Добавить вопрос',
            'max' => 8,
            'sub_fields' => [
                self::f('question', 'Вопрос', 'text', ['key' => 'field_gkb_faq_q_' . $suffix, 'required' => 1]),
                self::f('answer', 'Ответ', 'wysiwyg', ['key' => 'field_gkb_faq_a_' . $suffix, 'required' => 1, 'toolbar' => 'basic', 'media_upload' => 0, 'tabs' => 'visual']),
            ],
        ]);
    }

    public static function groups(): void
    {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        // SEO-поля страниц, товаров, статей и категорий
        $seoFields = static function (string $suffix): array {
            return [
                self::f('seo_title', 'SEO title', 'text', [
                    'key' => 'field_gkb_seo_title_' . $suffix,
                    'instructions' => 'До 70 символов. Пусто — по шаблону.',
                    'maxlength' => 90,
                ]),
                self::f('seo_description', 'SEO description', 'textarea', [
                    'key' => 'field_gkb_seo_description_' . $suffix,
                    'instructions' => '120–170 символов. Пусто — по шаблону.',
                    'rows' => 3,
                    'maxlength' => 200,
                ]),
                self::f('seo_image', 'Изображение для соцсетей', 'image', [
                    'key' => 'field_gkb_seo_image_' . $suffix,
                    'instructions' => '1200×630. Пусто — фото товара или изображение по умолчанию.',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                ]),
                self::f('seo_noindex', 'Скрыть из поиска (noindex)', 'true_false', [
                    'key' => 'field_gkb_seo_noindex_' . $suffix,
                    'instructions' => 'Страница получит noindex и пропадёт из sitemap и llms.txt.',
                    'ui' => 1,
                ]),
            ];
        };

        acf_add_local_field_group([
            'key' => 'group_gkb_seo_posts',
            'title' => 'SEO',
            'fields' => $seoFields('post'),
            'location' => [
                [['param' => 'post_type', 'operator' => '==', 'value' => 'page']],
                [['param' => 'post_type', 'operator' => '==', 'value' => 'products']],
            ],
            'position' => 'side',
            'menu_order' => 50,
        ]);

        acf_add_local_field_group([
            'key' => 'group_gkb_seo_terms',
            'title' => 'SEO и текст категории',
            'fields' => array_merge(
                [self::f('intro_text', 'Текст категории', 'wysiwyg', [
                    'instructions' => '600–1500 знаков под сеткой товаров: назначение, отличия моделей, мощности и цены, ссылки на статьи. Только русская версия.',
                    'media_upload' => 0,
                ])],
                [self::faqField('term')],
                $seoFields('term')
            ),
            'location' => [[['param' => 'taxonomy', 'operator' => '==', 'value' => 'product-category']]],
            'menu_order' => 50,
        ]);

        acf_add_local_field_group([
            'key' => 'group_gkb_product_mods',
            'title' => 'Модификации',
            'fields' => [
                self::f('modifications', 'Модификации', 'repeater', [
                    'instructions' => 'Мощность и отапливаемая площадь — из таблицы характеристик. Выводятся таблицей на странице товара, в карточках и llms.txt. Цены здесь не указываются: они только в прайс-листе.',
                    'layout' => 'table',
                    'button_label' => 'Добавить модификацию',
                    'sub_fields' => [
                        self::f('code', 'Обозначение', 'text', ['key' => 'field_gkb_mod_code', 'required' => 1, 'placeholder' => 'КС-Г-010 СН']),
                        self::f('power_kw', 'Мощность, кВт', 'number', ['key' => 'field_gkb_mod_power', 'step' => '0.1', 'min' => 0]),
                        self::f('area_m2', 'Площадь, м²', 'number', ['key' => 'field_gkb_mod_area', 'step' => 1, 'min' => 0]),
                        self::f('dhw', 'ГВС', 'true_false', ['key' => 'field_gkb_mod_dhw', 'ui' => 1]),
                    ],
                ]),
                self::f('similar_products', 'Похожие модели (вручную)', 'relationship', [
                    'instructions' => 'Пусто — подбор автоматически по категории и мощности.',
                    'post_type' => ['products'],
                    'filters' => ['search'],
                    'return_format' => 'object',
                    'max' => 4,
                ]),
                self::faqField('product'),
            ],
            'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'products']]],
            'menu_order' => 20,
        ]);

        acf_add_local_field_group([
            'key' => 'group_gkb_page_faq',
            'title' => 'Частые вопросы',
            'fields' => [self::faqField('page')],
            'location' => [
                [['param' => 'post_type', 'operator' => '==', 'value' => 'page']],
            ],
            'menu_order' => 40,
        ]);

        acf_add_local_field_group([
            'key' => 'group_gkb_org',
            'title' => 'Организация и SEO',
            'fields' => [
                self::tab('org', 'Организация'),
                self::f('org_name', 'Название (бренд)', 'text', ['placeholder' => 'ГазКотелБел']),
                self::f('org_legal_name', 'Юридическое название', 'text', ['placeholder' => 'ООО «ГазКотелБел»']),
                self::f('org_postcode', 'Индекс', 'text', ['placeholder' => '223012', 'wrapper' => ['width' => 25]]),
                self::f('org_region', 'Область, район', 'text', ['placeholder' => 'Минская область, Минский район', 'wrapper' => ['width' => 75]]),
                self::f('org_locality', 'Населённый пункт', 'text', ['placeholder' => 'г. п. Мачулищи', 'wrapper' => ['width' => 50]]),
                self::f('org_street', 'Улица, дом', 'text', ['placeholder' => 'ул. Центральная, 1А-2', 'wrapper' => ['width' => 50]]),
                self::f('org_phones', 'Телефоны (по одному в строке)', 'textarea', ['rows' => 3, 'placeholder' => "+375 29 151 68 90\n+375 33 684 83 95"]),
                self::f('org_email', 'Email', 'email'),
                self::f('org_unp', 'УНП', 'text', ['placeholder' => '693156545']),
                self::f('org_hours', 'Часы работы (для людей)', 'text', ['placeholder' => 'пн–пт 9:00–18:00']),
                self::f('org_logo', 'Логотип для разметки', 'image', ['return_format' => 'array', 'instructions' => 'Пусто — логотип из шапки.']),
                self::f('og_default_image', 'Изображение для соцсетей по умолчанию', 'image', ['return_format' => 'array', 'instructions' => '1200×630, для страниц без своего изображения.']),

                self::tab('texts', 'Тексты'),
                self::f('about_title', 'Главная: заголовок блока «О компании»', 'text'),
                self::f('about_text', 'Главная: текст блока «О компании»', 'wysiwyg', ['media_upload' => 0]),
                self::f('about_points', 'Главная: преимущества', 'repeater', [
                    'layout' => 'table',
                    'max' => 5,
                    'sub_fields' => [
                        self::f('title', 'Заголовок', 'text', ['key' => 'field_gkb_about_point_title']),
                        self::f('text', 'Текст', 'text', ['key' => 'field_gkb_about_point_text']),
                    ],
                ]),
                self::f('catalog_intro_text', 'Каталог: вводный текст', 'wysiwyg', ['media_upload' => 0]),
                self::f('region_delivery_text', 'Региональные страницы: текст о доставке', 'wysiwyg', [
                    'media_upload' => 0,
                    'instructions' => 'Подстановки: {region} — «Брестской области», {calculator} и {price} — адреса калькулятора и прайс-листа.',
                ]),

                self::tab('services', 'Сервисы'),
                self::f('yandex_verification', 'Яндекс Вебмастер: код подтверждения', 'text'),
                self::f('google_verification', 'Google Search Console: код подтверждения', 'text'),
                self::f('origin_trial_token', 'Chrome Origin Trial (WebMCP): токен', 'textarea', ['rows' => 3, 'instructions' => 'Выводится только на боевом домене.']),
            ],
            'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'theme-general-settings']]],
            'menu_order' => 90,
        ]);
    }
}
