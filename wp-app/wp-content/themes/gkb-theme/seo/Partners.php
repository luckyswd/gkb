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
            'title' => 'Технологический партнёр',
            'items' => [
                [
                    'name' => 'ShefCode',
                    'label' => 'Разработка и сопровождение',
                    'url' => self::DEVELOPER_URL,
                    'host' => 'shefcode.tech',
                    'text' => 'Студия, которая создаёт сайты и бизнес-приложения и остаётся рядом после запуска: скорость, продвижение, аналитика, автоматизация и AI.',
                    'links' => [
                        ['Кейсы', 'https://shefcode.tech/cases/'],
                        ['ShefTools — аналитика сайта', 'https://tools.shefcode.tech/'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Полезные сервисы',
            'items' => [
                [
                    'name' => 'Расчётник',
                    'label' => 'Калькуляторы выплат',
                    'url' => 'https://raschetnik.by/',
                    'host' => 'raschetnik.by',
                    'text' => 'Зарплата на руки, отпускные, больничный и пенсия — бесплатные калькуляторы по правилам Беларуси с актуальными суммами. И производственный календарь на каждый год.',
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
                    'text' => 'Калькуляторы алиментов и стоимости развода и простые ответы на частые вопросы по законам Беларуси. Бесплатно и без регистрации.',
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

    /** Адрес страницы, если она опубликована, иначе null. */
    public static function url(): ?string
    {
        $page = get_page_by_path(self::SLUG);
        return ($page instanceof \WP_Post && $page->post_status === 'publish') ? trailingslashit(get_permalink($page)) : null;
    }
}
