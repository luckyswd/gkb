<?php

namespace seo;

/**
 * Страница «Партнёры» (/partners/): разработчик сайта и полезные сервисы.
 * Список один на шаблон, подвал и llms.txt. Страница только на русском.
 * Ссылки без UTM и без nofollow: переходы и так видны в аналитике
 * по рефереру, а меткам не место в адресах, которые учитывают поисковики.
 */
class Partners
{
    public const SLUG = 'partners';

    public const DEVELOPER_URL = 'https://shefcode.tech/';

    /** Разделы страницы: заголовок и карточки. */
    public const SECTIONS = [
        [
            'title' => 'Разработка и поддержка сайта',
            'items' => [
                [
                    'name' => 'ShefCode',
                    'label' => 'Разработчик сайта',
                    'url' => self::DEVELOPER_URL,
                    'host' => 'shefcode.tech',
                    'text' => 'Команда ShefCode разработала gazkotelbel.com и поддерживает его: каталог котлов и колонок, прайс-лист, карта дилеров, калькулятор мощности, поисковая оптимизация и скорость загрузки. Студия создаёт и развивает бизнес-приложения и внутренние системы, автоматизирует процессы и внедряет AI. Работает по договору.',
                    'links' => [
                        ['Кейсы ShefCode', 'https://shefcode.tech/cases/'],
                        ['ShefTools — бесплатная аналитика сайта', 'https://tools.shefcode.tech/'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Полезные сервисы для жителей Беларуси',
            'items' => [
                [
                    'name' => 'Расчётник',
                    'label' => 'Калькуляторы выплат',
                    'url' => 'https://raschetnik.by/',
                    'host' => 'raschetnik.by',
                    'text' => 'Бесплатные калькуляторы выплат по правилам Беларуси: зарплата на руки, отпускные, больничный, пенсия и страховой стаж, детские пособия. Без регистрации и с суммами, которые действуют сегодня. Там же — производственный календарь Беларуси с праздниками и нормой рабочих часов.',
                    'links' => [
                        ['Производственный календарь на 2027 год', 'https://raschetnik.by/proizvodstvennyy-kalendar/2027'],
                        ['Калькулятор зарплаты на руки', 'https://raschetnik.by/zarplata-na-ruki'],
                        ['Калькулятор отпускных', 'https://raschetnik.by/otpusknye'],
                        ['Калькулятор больничного', 'https://raschetnik.by/bolnichnyy'],
                        ['Пенсионный возраст по годам рождения', 'https://raschetnik.by/pensionnyy-vozrast'],
                    ],
                ],
                [
                    'name' => 'alimenty.by',
                    'label' => 'Алименты и развод',
                    'url' => 'https://alimenty.by/',
                    'host' => 'alimenty.by',
                    'text' => 'Калькуляторы алиментов и стоимости развода и понятные гиды по законодательству Республики Беларусь: как подать на алименты в браке и без него, что будет при неуплате, как считаются задолженность и неустойка. Бесплатно и без регистрации.',
                    'links' => [
                        ['Калькулятор алиментов', 'https://alimenty.by/kalkulyator-alimentov'],
                        ['Минимальный размер алиментов', 'https://alimenty.by/minimalnye-alimenty'],
                        ['Калькулятор неустойки по алиментам', 'https://alimenty.by/kalkulyator-neustoyki-po-alimentam'],
                        ['Калькулятор стоимости развода', 'https://alimenty.by/kalkulyator-stoimosti-razvoda'],
                    ],
                ],
            ],
        ],
    ];

    /** Разовое создание страницы: wp-admin недоступен, страница заводится кодом. */
    public static function register(): void
    {
        add_action('init', [self::class, 'ensurePage'], 20);
    }

    public static function ensurePage(): void
    {
        // add_option вставляет строку с уникальным именем: из параллельных
        // запросов страницу создаст только один, остальные выйдут здесь.
        if (!add_option('gkb_partners_page_created', (string) time(), '', false)) {
            return;
        }
        if (get_page_by_path(self::SLUG)) {
            return; // страницу уже завели руками — не трогаем
        }
        $admins = get_users(['role' => 'administrator', 'number' => 1, 'fields' => 'ID']);
        $id = wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => 'Партнёры',
            'post_name' => self::SLUG,
            'post_content' => '',
            'post_author' => (int) ($admins[0] ?? 1),
            'comment_status' => 'closed',
        ], true);
        update_option('gkb_partners_page_created', is_wp_error($id) ? 'error: ' . $id->get_error_message() : 'page ' . $id, false);
        // ссылка «Партнёры» в подвале есть на каждой странице — сбрасываем кеш целиком
        if (!is_wp_error($id) && function_exists('wpo_cache_flush')) {
            wpo_cache_flush();
        }
    }

    /** Адрес страницы, если она опубликована, иначе null. */
    public static function url(): ?string
    {
        $page = get_page_by_path(self::SLUG);
        return ($page instanceof \WP_Post && $page->post_status === 'publish') ? trailingslashit(get_permalink($page)) : null;
    }
}
