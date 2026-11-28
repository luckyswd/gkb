<?php

namespace seo;

/**
 * Страница «Партнёры» (/partners/): технологические партнёры и полезные
 * сервисы. Тексты взяты с сайтов партнёров. Список один на шаблон и
 * llms.txt. Страница только на русском.
 * Ссылки карточек без UTM и без nofollow: переходы видны в аналитике
 * по рефереру, а меткам не место в адресах, которые учитывают поисковики.
 */
class Partners
{
    public const SLUG = 'partners';

    public const DEVELOPER_URL = 'https://shefcode.tech/';

    /**
     * Разделы страницы: заголовок и карточки. logo — файл в
     * assets/img/partners/, title и text — с главной страницы партнёра.
     */
    public const SECTIONS = [
        [
            'title' => 'Технологические партнёры',
            'items' => [
                [
                    'name' => 'ShefCode',
                    'url' => self::DEVELOPER_URL,
                    'host' => 'shefcode.tech',
                    'logo' => 'shefcode.svg',
                    'title' => 'Разработка и развитие бизнес-приложений',
                    'text' => 'Помогаем компаниям автоматизировать процессы, создавать внутренние системы и развивать существующие цифровые продукты. AI встраиваем там, где он реально экономит время.',
                ],
                [
                    'name' => 'ShefTools',
                    'url' => 'https://tools.shefcode.tech/',
                    'host' => 'tools.shefcode.tech',
                    'logo' => 'sheftools.svg',
                    'title' => 'Аналитика, чат и мониторинг сайта — одной строкой кода',
                    'text' => 'Аналитика, онлайн-чат, cookie-баннер, записи сессий, мониторинг доступности и SEO-аудит. Отчёты и сообщения приходят в Telegram, а о статистике можно спросить AI-ассистента обычными словами.',
                ],
            ],
        ],
        [
            'title' => 'Полезные сервисы',
            'items' => [
                [
                    'name' => 'Расчётник',
                    'url' => 'https://raschetnik.by/',
                    'host' => 'raschetnik.by',
                    'logo' => 'raschetnik.svg',
                    'title' => 'Простые калькуляторы выплат в Беларуси',
                    'text' => 'Зарплата, отпускные, больничный, детские пособия и дата выхода на пенсию. Бесплатно, с актуальными суммами.',
                ],
                [
                    'name' => 'Алименты',
                    'url' => 'https://alimenty.by/',
                    'host' => 'alimenty.by',
                    'logo' => 'alimenty.svg',
                    'title' => 'Алименты и развод: посчитать, а не гадать',
                    'text' => 'Бесплатные инструменты по законодательству Республики Беларусь: сколько положено алиментов, сколько накопилось долга и неустойки, во что обойдётся развод.',
                ],
            ],
        ],
    ];

    /** Адрес логотипа партнёра в теме. */
    public static function logoUrl(string $file): string
    {
        return get_template_directory_uri() . '/assets/img/partners/' . $file;
    }

    /** Адрес страницы, если она опубликована, иначе null. */
    public static function url(): ?string
    {
        $page = get_page_by_path(self::SLUG);
        return ($page instanceof \WP_Post && $page->post_status === 'publish') ? trailingslashit(get_permalink($page)) : null;
    }
}
