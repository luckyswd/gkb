<?php

namespace seo;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Одноразовые правки контента по скриншотам заказчика (ноябрь 2026,
 * openspec/changes/site-edits-page-3). Инструменты → «Правки 11.2026».
 *
 * «Проверить» — пробный прогон без записи: каждая замена сверяет старое
 * значение и число совпадений. «Применить» доступна, только если все
 * проверки прошли; перед записью исходники сохраняются в опцию, кнопка
 * «Откатить» возвращает их. После приёмки файл удаляется из темы.
 */
class SiteEdits2611
{
    private const BACKUP = 'gkb_site_edits_2611_backup';
    private const SLUG = 'gkb-site-edits-2611';

    /* ------------------------------------------------------------------ */
    /* План правок                                                         */
    /* ------------------------------------------------------------------ */

    /**
     * Операции над полями блока acf/product (RU):
     *   ['r', from, to, count = 1]           — замена текста (пробелы = любые пробелы/&nbsp;)
     *   ['t', tableSelector, [tableOps...]]  — правки таблицы, где встречается selector
     * Операции таблицы:
     *   ['cell', rowLabel, col, old, new]    — значение ячейки (col от 1, без столбца подписи)
     *   ['text', rowLabel, col, from, to]    — замена внутри ячейки (col 0 — подпись строки)
     *   ['insertcol', afterCol]              — копия столбца afterCol справа от него, colspan общих строк +1
     *   ['delrow', rowLabel]                 — удалить строку
     * rowLabel — строка или массив подстрок, которые все должны быть в подписи.
     */
    public static function plan(): array
    {
        $mass = 'Масса, не более, кг, нетто';
        $gross = 'Масса, не более, кг, брутто';
        $maxGas = 'Максимальный (номинальный) расход';
        $prod = 'Продуктивность второго контура';

        return [
            'zhytomyr-3' => [
                'block' => [
                    'description' => [['r', 'еффективнисть', 'эффективность']],
                    'button_name' => [['r', 'Гду купить ?', 'Где купить?']],
                    'features_and_benefits' => [
                        ['r', 'привлекательной цене.', 'привлекательной ценой.'],
                        ['r', 'конструктивным решением', 'конструктивным решениям'],
                        ['r', 'уже долгое время является', 'уже долгое время являются'],
                        ['r', 'навесных турбо котлов', 'навесных котлов'],
                        ['r', 'с этой задачей напрямую', 'с этой задачей, напрямую'],
                        ['r', 'достигает 94%', 'достигает 97%'],
                    ],
                    'specifications' => [['t', 'КС-Г 030', [
                        ['cell', 'Отапливаемая площадь', 5, '220', '225'], // руководство 2026
                        ['cell', 'Отапливаемая площадь', 7, '320', '315'], // руководство 2026
                        ['cell', 'Средний расход газа', 7, '1,15', '1,09'],
                        ['cell', $maxGas, 1, '0,74', '0,76'],
                        ['cell', $maxGas, 3, '1,33', '1,34'],
                        ['cell', $maxGas, 4, '1,71', '1,72'],
                        ['cell', $maxGas, 5, '2,4', '2,44'],
                        ['cell', $maxGas, 6, '2,77', '2,79'],
                        ['cell', $maxGas, 7, '3,36', '3,39'],
                        ['text', $prod, 0, 'кг/час', 'л/мин'],
                        ['cell', $prod, 1, '175', '2,9'],
                        ['cell', $prod, 2, '230', '3,8'],
                        ['cell', $prod, 3, '280', '4,6'],
                        ['cell', $prod, 4, '350', '5,8'],
                        ['cell', $prod, 5, '500', '8,3'],
                        ['cell', $prod, 6, '600', '10'],
                        ['cell', $prod, 7, '700', '11,7'],
                        ['cell', $prod, 8, '900', '15'],
                        ['cell', $prod, 9, '1400', '23,3'],
                        ['cell', $prod, 10, '1800', '30'],
                        ['cell', 'Объем воды в котле', 7, '35 / 33', '27 / 25'],
                        ['cell', 'Объем воды в котле', 8, '60 / 55', '35 / 33'],
                        ['cell', 'Объем воды в котле', 9, '60 / 55', '53 / 51'], // 060, руководство 2026
                        ['cell', $mass, 1, '35 / 37', '32 / 34'],
                        ['cell', $mass, 2, '44 / 47', '44 / 46'],
                        ['cell', $mass, 3, '49 / 52', '48 / 50'],
                        ['cell', $mass, 4, '60 / 63', '60 / 62'],
                        ['cell', $mass, 5, '*', '71 / 73'], // сейчас битое «6/8 / 71»
                        ['cell', $mass, 6, '77 / 82', '73 / 75'],
                        ['cell', $mass, 7, '101 / 106', '81 / 83'],
                        ['cell', $gross, 1, '36 / 38', '35 / 37'],
                        ['cell', $gross, 2, '46 / 49', '46 / 48'],
                        ['cell', $gross, 3, '51 / 54', '50 / 52'],
                        ['cell', $gross, 4, '62 / 65', '62 / 64'],
                        ['cell', $gross, 5, '70 / 73', '74 / 76'],
                        ['cell', $gross, 6, '79 / 84', '76 / 78'],
                        ['cell', $gross, 7, '103 / 108', '84 / 86'],
                        // 080 и 100 нет в руководстве 2026: прежние числа раскладываем по новым строкам
                        // (строка 1 — КС-Г нетто/брутто, строка 2 — КС-ГВ нетто/брутто)
                        ['cell', $mass, 10, '224 / 230', '224 / 227'],
                        ['cell', $gross, 10, '227 / 233', '230 / 233'],
                        ['cell', $mass, 11, '244', '244 / 250'],
                        ['cell', $gross, 11, '250', '—'],
                        // новый столбец КС-Г 035 СН — копия 030 с новыми значениями
                        ['insertcol', 7],
                        ['text', 'Модель', 8, '030', '035', 2],
                        ['cell', 'Газовый клапан', 8, '710 MINISIT', '—'], // ждём ответа заказчика
                        ['cell', 'Номинальная тепловая мощность', 8, '31,5', '37'],
                        ['cell', 'Отапливаемая площадь', 8, '315', '370'],
                        ['cell', 'Средний расход газа', 8, '1,09', '1,37'],
                        ['cell', $maxGas, 8, '3,39', '3,96'],
                        ['cell', $prod, 8, '11,7', '14'],
                        ['cell', 'Объем воды в котле', 8, '27 / 25', '31 / 29'], // в руководстве опечатка «31/39»
                        ['cell', 'Объем воздуха', 8, '90', '104'],
                        ['cell', $mass, 8, '81 / 83', '101 / 103'],
                        ['cell', $gross, 8, '84 / 86', '106 / 108'],
                        // В руководстве 2026 массы даны как «нетто/брутто»: строка 1 — КС-Г, строка 2 — КС-ГВ
                        ['cell', $mass, 0, '*', 'Масса, не более, кг, нетто / брутто, ± 10% (КС-Г)'],
                        ['cell', $gross, 0, '*', 'Масса, не более, кг, нетто / брутто, ± 10% (КС-ГВ)'],
                    ]]],
                    'equipment' => [['t', 'Ножки', [['delrow', 'Ножки регулировочные']]]],
                ],
                'faq' => [
                    ['r', ' Готовая версия с установленным ТЭНом — Житомир-3 с ТЭНом.', ''],
                    ['r', 'у модели на 40 кВт', 'у модели на 45 кВт'],
                ],
                'mods_add' => [
                    ['code' => 'КС-Г-035 СН', 'power_kw' => 37, 'area_m2' => 370, 'dhw' => 0],
                    ['code' => 'КС-ГВ-035 СН', 'power_kw' => 37, 'area_m2' => 370, 'dhw' => 1],
                ],
                'mods_area' => [
                    'КС-Г-020 СН' => [220, 225], 'КС-ГВ-020 СН' => [220, 225],
                    'КС-Г-030 СН' => [320, 315], 'КС-ГВ-030 СН' => [320, 315],
                ],
                'similar' => ['zhytomyr-10', 'zhytomyr-m', 'zhytomyr-turbo'],
            ],

            'zhytomyr-5' => [
                'block' => [
                    'specifications' => [['t', 'КНС-10', [
                        ['cell', 'глубина', 1, '270', '280'],
                        ['cell', 'высота', 1, '555', '560'],
                        ['cell', 'высота', 2, '555', '560'],
                        ['cell', 'высота', 3, '590', '595'],
                        ['cell', 'высота', 4, '660', '665'],
                        ['cell', 'высота', 5, '660', '665'],
                        ['cell', 'высота', 6, '660', '665'],
                        ['cell', 'Масса, нетто', 1, '18/19', '21/22'],
                        ['cell', 'Масса, нетто', 2, '18/19', '21/22'],
                        ['cell', 'Масса, нетто', 3, '25/26', '29/30'],
                        ['cell', 'Масса, нетто', 4, '27/28', '33/34'],
                        ['cell', 'Масса, нетто', 5, '34/35', '40/41'],
                        ['cell', 'Масса, нетто', 6, '34/35', '40/41'],
                        ['cell', 'Масса с дымовоздушным', 1, '23/24', '27/28'],
                        ['cell', 'Масса с дымовоздушным', 2, '23/24', '27/28'],
                        ['cell', 'Масса с дымовоздушным', 3, '30/31', '35/36'],
                        ['cell', 'Масса с дымовоздушным', 4, '32/33', '39/40'],
                        ['cell', 'Масса с дымовоздушным', 5, '41/42', '48/49'],
                        ['cell', 'Масса с дымовоздушным', 6, '41/42', '48/49'],
                        // КНС-5 — копия КНС-4 с новыми значениями
                        ['insertcol', 3],
                        ['text', 'Модель', 4, 'КНС-4', 'КНС-5'],
                        ['cell', 'Номинальная тепловая мощность', 4, '4', '5'],
                        ['cell', 'Объем отапливаемого', 4, '100', '125'],
                        ['cell', ['Расход газа', 'природного'], 4, '0,42', '0,52'],
                        ['cell', ['Расход газа', 'сжиженного'], 4, '0,16', '0,20'],
                        ['cell', 'высота', 4, '595', '665'],
                        ['cell', 'Масса, нетто', 4, '29/30', '33/34'],
                        ['cell', 'Масса с дымовоздушным', 4, '35/36', '39/40'],
                    ]]],
                    'equipment' => [['r', 'для работы на сжиженном газе', 'для работы на природном газе']],
                ],
                'faq' => [['r', 'поставляется под сжиженный газ, комплект для перехода на природный газ', 'работает на природном газе, комплект для переоборудования на сжиженный (баллонный) газ']],
                'mods_add' => [['code' => 'КНС-5', 'power_kw' => 5, 'area_m2' => '', 'dhw' => 0]],
            ],

            'zhytomyr-9' => [
                'block' => [
                    'description' => [
                        ['r', 'КПД до 97%', 'КПД до 94%'],
                        ['r', 'от 10 до 15 кВт', 'от 10 до 20 кВт'],
                    ],
                    'features_and_benefits' => [
                        ['r', 'КПД до 97%', 'КПД до 94%'],
                        ['r', 'от 10 до 15 кВт', 'от 10 до 20 кВт'],
                    ],
                    'specifications' => [['t', 'АОТВ-10', [
                        ['text', 'Модель', 3, 'АОТВ-12', 'АОТВ-16', 2],
                        ['text', 'Модель', 4, 'АОТВ-15', 'АОТВ-20', 2],
                        ['cell', ['(КПД)', '(газ)'], 1, '97', '94'],
                        ['cell', ['(КПД)', '(газ)'], 2, '97', '94'],
                        ['cell', ['(КПД)', '(газ)'], 3, '97', '94'],
                        ['cell', ['(КПД)', '(газ)'], 4, '97', '94'],
                        ['cell', 'Номинальная тепловая мощность', 3, '16/12', '16/16'],
                        ['cell', 'Номинальная тепловая мощность', 4, '20/15', '20/20'],
                        ['cell', 'Отапливаемая площадь', 3, '160/120', '160/160'],
                        ['cell', 'Отапливаемая площадь', 4, '200/150', '200/200'],
                    ]]],
                    'equipment' => [['t', 'Колосники', [
                        ['delrow', 'Колосники чугунные'],
                        ['delrow', 'Ящик зольника'],
                    ]]],
                ],
                'faq' => [['r', 'До 97% на газе', 'До 94% на газе']],
                'mods_code' => [
                    'КС-Г-016СН/АОТВ-12' => 'КС-Г-016СН/АОТВ-16',
                    'КС-Г-020СН/АОТВ-15' => 'КС-Г-020СН/АОТВ-20',
                ],
            ],

            'zhytomyr-10' => [
                'block' => [
                    'features_and_benefits' => [
                        ['r', 'от 10 до 31,5 кВт', 'от 7 до 31,5 кВт'],
                        ['r', 'ВПГ-16', 'ВПГ-20'],
                        ['r', 'один компактное', 'одно компактное'],
                        ['r', 'достигает 94%', 'достигает 97%'],
                    ],
                    'specifications' => [['t', 'КС-Г 030', array_merge(
                        array_map(static fn ($c) => ['cell', ['(КПД)', 'отопление'], $c, '94', '97'], range(1, 7)),
                        [
                            ['cell', 'Отапливаемая площадь', 5, '220', '225'],
                            ['cell', 'Отапливаемая площадь', 7, '320', '315'],
                            ['cell', [$maxGas, 'отопление'], 1, '0,74', '0,76'],
                            ['cell', [$maxGas, 'отопление'], 3, '1,33', '1,34'],
                            ['cell', [$maxGas, 'отопление'], 4, '1,71', '1,72'],
                            ['cell', [$maxGas, 'отопление'], 5, '2,4', '2,44'],
                            ['cell', [$maxGas, 'отопление'], 6, '2,77', '2,79'],
                            ['cell', [$maxGas, 'отопление'], 7, '3,36', '3,39'],
                            ['cell', 'Максимальное давление теплоносителя', 1, '3', '2'],
                            ['cell', 'Максимальное давление в контуре', 1, '6', '3'],
                            ['cell', 'Разряжение за котлом', 6, '4 / 40', '4 / 25'],
                            ['cell', 'Разряжение за котлом', 7, '4 / 40', '4 / 25'],
                            ['cell', 'Объем воды в котле', 7, '35', '27'],
                            // массы — по руководству 2026 (на скриншоте заказчика эти строки не попали в кадр)
                            ['cell', 'Масса, кг, нетто', 1, '46', '50'],
                            ['cell', 'Масса, кг, нетто', 2, '56', '59'],
                            ['cell', 'Масса, кг, нетто', 3, '60', '64'],
                            ['cell', 'Масса, кг, нетто', 4, '73', '75'],
                            ['cell', 'Масса, кг, нетто', 7, '116', '100'],
                            ['cell', 'Масса, кг, брутто', 1, '49', '52'],
                            ['cell', 'Масса, кг, брутто', 2, '58', '61'],
                            ['cell', 'Масса, кг, брутто', 3, '62', '66'],
                            ['cell', 'Масса, кг, брутто', 4, '75', '77'],
                            ['cell', 'Масса, кг, брутто', 7, '118', '102'],
                        ]
                    )]],
                ],
                'faq' => [['r', 'до 320 м²', 'до 315 м²']],
                'mods_area' => ['КС-Г-020 СН' => [220, 225], 'КС-Г-030 СН' => [320, 315]],
            ],

            'hsv-20' => [
                'block' => [
                    'description' => [
                        ['r', 'до 9,3 литров', 'до 10 литров'],
                        ['r', '21,3 кВт', '22,4 кВт'],
                    ],
                    'features_and_benefits' => [
                        ['r', '18.8', '20'],
                        ['r', '21,3', '22,4'],
                        ['r', '9,3', '10'],
                        ['r', '6,8', '7,2'],
                        ['r', '2,12', '2,24'],
                    ],
                    'specifications' => [['t', 'Номинальный расход газа', [
                        ['cell', 'теплопродуктивность', 1, '18,8', '20'],
                        ['cell', 'Тепловая мощность горелки', 1, '21,3', '22,4'],
                        ['cell', 'Номинальный расход газа', 1, '2,12', '2,24'],
                        ['cell', 'Продуктивность при нагреве', 1, '9,3', '10'],
                        ['cell', 'Габаритные размеры', 1, '650х350х215', '550х330х188'],
                    ]]],
                ],
                'faq' => [
                    ['r', 'До 9,3 л/мин', 'До 10 л/мин'],
                    ['r', 'до 6,8 л/мин', 'до 7,2 л/мин'],
                ],
            ],

            'hsv-20m' => [
                'block' => [
                    'specifications' => [['t', 'Габаритные размеры', [
                        ['cell', 'Габаритные размеры', 1, '650х350х215', '550х330х188'],
                    ]]],
                ],
            ],

            'vpg-20t' => [
                'block' => [
                    'description' => [
                        ['r', '9,4 литров', '10 литров'],
                        ['r', '18,8 кВт', '22,4 кВт'],
                    ],
                    'specifications' => [
                        ['t', 'Тип камеры', [['cell', 'Тип камеры сгорания', 1, 'Герметичная', 'Герметичная (закрытая)']]],
                        ['t', 'Номинальный расход газа', [
                            ['cell', 'теплопродуктивность', 1, '16,8', '20'],
                            ['cell', 'Тепловая мощность горелки', 1, '18,8', '22,4'],
                            ['cell', 'Номинальный расход газа', 1, '1,93', '2,06'],
                            ['cell', 'Продуктивность при нагреве', 1, '9,4', '10'],
                        ]],
                    ],
                    'equipment' => [['r', 'Коаксиальный короб', 'Коаксиальный дымоход']],
                ],
                'faq' => [['r', '9,4 л/мин', '10 л/мин']],
            ],

            'hsv-20tm' => [
                'status' => 'draft',
            ],
        ];
    }

    /** FAQ терминов категорий. */
    public static function termPlan(): array
    {
        return [
            'universal-boilers' => [
                ['r', '10, 12, 12 и 15 кВт', '10, 12, 16 и 20 кВт'],
                ['r', 'работает и на газе,', 'работает и на газу,'],
                ['r', 'м² на газе.', 'м² на газу.'],
            ],
        ];
    }

    /** Замены в опциях подвала (RU и EN): часы работы. */
    public static function footerPlan(): array
    {
        return [
            ['r', '08:00', '09:00', -1],
            ['r', '17:00', '18:00', -1],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Чистые функции над HTML (тестируются без WordPress)                 */
    /* ------------------------------------------------------------------ */

    /** Регулярка из строки: любой пробел совпадает с пробелом, &nbsp; или U+00A0. */
    private static function pattern(string $from): string
    {
        $parts = preg_split('/\s+/u', $from);
        $parts = array_map(static fn ($p) => preg_quote($p, '/'), $parts);
        return '/' . implode('(?:\s|&nbsp;|\x{00A0})+', $parts) . '/u';
    }

    /**
     * Применяет список операций к HTML поля.
     * @return array{0:string,1:array} [новый HTML, журнал [ok(bool), текст]]
     */
    public static function applyOps(string $html, array $ops): array
    {
        $log = [];
        foreach ($ops as $op) {
            if ($op[0] === 'r') {
                [, $from, $to] = $op;
                $want = $op[3] ?? 1;
                $n = preg_match_all(self::pattern($from), $html);
                $ok = $want === -1 ? $n > 0 : $n === $want;
                if ($ok) {
                    $html = preg_replace(self::pattern($from), $to, $html);
                }
                $log[] = [$ok, "«{$from}» → «{$to}»: найдено {$n}" . ($want === -1 ? '' : ", нужно {$want}")];
            } elseif ($op[0] === 't') {
                [$html, $tlog] = self::applyTable($html, $op[1], $op[2]);
                $log = array_merge($log, $tlog);
            }
        }
        return [$html, $log];
    }

    private static function norm(string $s): string
    {
        $s = str_replace(["\u{00A0}", '&nbsp;'], ' ', $s);
        return trim(preg_replace('/\s+/u', ' ', $s));
    }

    /** Таблица с селектором правится через DOM, остальной HTML поля не трогается. */
    public static function applyTable(string $html, string $selector, array $ops): array
    {
        if (!preg_match_all('#<table\b.*?</table>#su', $html, $m, PREG_OFFSET_CAPTURE)) {
            return [$html, [[false, "таблиц нет (ищем «{$selector}»)"]]];
        }
        $found = array_values(array_filter($m[0], static fn ($t) => str_contains(self::norm(strip_tags($t[0])), self::norm($selector))));
        if (count($found) !== 1) {
            return [$html, [[false, 'таблиц с «' . $selector . '»: ' . count($found) . ', нужна 1']]];
        }
        [$tableHtml, $offset] = $found[0];

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><html><body>' . $tableHtml . '</body></html>', LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $table = $doc->getElementsByTagName('table')->item(0);
        $log = [];

        foreach ($ops as $op) {
            $log[] = self::tableOp($doc, $table, $op);
        }

        $newTable = $doc->saveHTML($table);
        $html = substr($html, 0, $offset) . $newTable . substr($html, $offset + strlen($tableHtml));
        return [$html, $log];
    }

    /** @return DOMElement[] */
    private static function rows(DOMElement $table): array
    {
        $out = [];
        foreach ((new DOMXPath($table->ownerDocument))->query('.//tr', $table) as $tr) {
            $out[] = $tr;
        }
        return $out;
    }

    /** @return DOMElement[] */
    private static function cells(DOMElement $tr): array
    {
        $out = [];
        foreach ($tr->childNodes as $n) {
            if ($n instanceof DOMElement && in_array(strtolower($n->tagName), ['td', 'th'], true)) {
                $out[] = $n;
            }
        }
        return $out;
    }

    private static function findRow(DOMElement $table, $label): array
    {
        $labels = (array) $label;
        $hits = [];
        foreach (self::rows($table) as $tr) {
            $cells = self::cells($tr);
            if (!$cells) {
                continue;
            }
            $text = self::norm($cells[0]->textContent);
            $all = true;
            foreach ($labels as $l) {
                if (!str_contains($text, self::norm($l))) {
                    $all = false;
                    break;
                }
            }
            if ($all) {
                $hits[] = $tr;
            }
        }
        return $hits;
    }

    private static function tableOp(DOMDocument $doc, DOMElement $table, array $op): array
    {
        $kind = $op[0];

        if ($kind === 'insertcol') {
            $after = $op[1];
            $rows = self::rows($table);
            $full = max(array_map(static fn ($tr) => count(self::cells($tr)), $rows));
            $cloned = 0;
            $spans = 0;
            foreach ($rows as $tr) {
                $cells = self::cells($tr);
                if (count($cells) === $full) {
                    $copy = $cells[$after]->cloneNode(true);
                    if ($cells[$after]->nextSibling) {
                        $tr->insertBefore($copy, $cells[$after]->nextSibling);
                    } else {
                        $tr->appendChild($copy);
                    }
                    $cloned++;
                    continue;
                }
                foreach ($cells as $c) {
                    if ((int) $c->getAttribute('colspan') > 1) {
                        $c->setAttribute('colspan', (string) ((int) $c->getAttribute('colspan') + 1));
                        $spans++;
                        break;
                    }
                }
            }
            return [$cloned > 0, "новый столбец после №{$after}: скопировано ячеек {$cloned}, colspan увеличен в {$spans} строках"];
        }

        $label = $op[1];
        $labelText = implode(' + ', (array) $label);
        $hits = self::findRow($table, $label);
        if (count($hits) !== 1) {
            return [false, "строка «{$labelText}»: найдено " . count($hits) . ', нужна 1'];
        }
        $tr = $hits[0];

        if ($kind === 'delrow') {
            $tr->parentNode->removeChild($tr);
            return [true, "строка «{$labelText}» удалена"];
        }

        $col = $op[2];
        $cells = self::cells($tr);
        if (!isset($cells[$col])) {
            return [false, "строка «{$labelText}»: нет столбца №{$col}"];
        }
        $cell = $cells[$col];

        if ($kind === 'text') {
            [, , , $from, $to] = $op;
            $want = $op[5] ?? 1;
            $n = 0;
            foreach ((new DOMXPath($doc))->query('.//text()', $cell) as $t) {
                $c = substr_count($t->nodeValue, $from);
                if ($c) {
                    $n += $c;
                    $t->nodeValue = str_replace($from, $to, $t->nodeValue);
                }
            }
            return [$n === $want, "«{$labelText}» №{$col}: «{$from}» → «{$to}», найдено {$n}, нужно {$want}"];
        }

        // cell
        [, , , $old, $new] = $op;
        $actual = self::norm($cell->textContent);
        if ($old !== '*' && $actual !== self::norm($old)) {
            return [false, "«{$labelText}» №{$col}: ожидали «{$old}», в ячейке «{$actual}»"];
        }
        $texts = [];
        foreach ((new DOMXPath($doc))->query('.//text()', $cell) as $t) {
            if (self::norm($t->nodeValue) !== '') {
                $texts[] = $t;
            }
        }
        if (!$texts) {
            $cell->appendChild($doc->createTextNode($new));
        } else {
            $texts[0]->nodeValue = $new;
            for ($i = 1; $i < count($texts); $i++) {
                $texts[$i]->nodeValue = '';
            }
            // старое значение было в несколько строк — убираем лишние переносы
            if (count($texts) > 1) {
                foreach (iterator_to_array($cell->getElementsByTagName('br')) as $br) {
                    $br->parentNode->removeChild($br);
                }
            }
        }
        return [true, "«{$labelText}» №{$col}: «{$actual}» → «{$new}»"];
    }

    /* ------------------------------------------------------------------ */
    /* WordPress                                                           */
    /* ------------------------------------------------------------------ */

    public static function register(): void
    {
        add_action('admin_menu', static function () {
            add_management_page('Правки 11.2026', 'Правки 11.2026', 'manage_options', self::SLUG, [self::class, 'page']);
        });
    }

    private static function product(string $slug): ?\WP_Post
    {
        $p = get_posts(['name' => $slug, 'post_type' => 'products', 'post_status' => 'any', 'numberposts' => 1]);
        return $p[0] ?? null;
    }

    /** Ключ поля RU в данных блока acf/product. */
    private static function blockKey(array $data, string $field): array
    {
        return array_values(array_filter(array_keys($data), static fn ($k) => $k[0] !== '_'
            && str_starts_with($k, 'ru_language') && str_ends_with($k, '_' . $field)));
    }

    private static function textRows(array $rows, array $ops, array &$log, string $what): array
    {
        $all = '';
        foreach ($rows as $r) {
            $all .= ($r['question'] ?? '') . "\n" . ($r['answer'] ?? '') . "\n";
        }
        foreach ($ops as [$k, $from, $to]) {
            $n = preg_match_all(self::pattern($from), $all);
            $log[] = [$n === 1, "{$what}: «{$from}» → «{$to}»: найдено {$n}, нужно 1"];
            foreach ($rows as &$r) {
                $r['answer'] = preg_replace(self::pattern($from), $to, (string) ($r['answer'] ?? ''));
                $r['question'] = preg_replace(self::pattern($from), $to, (string) ($r['question'] ?? ''));
            }
            unset($r);
        }
        return $rows;
    }

    private static function plainRepeater($rows): array
    {
        return array_values(array_filter((array) $rows, 'is_array'));
    }

    private static function metaSnapshot(int $id): array
    {
        $out = [];
        foreach (get_post_meta($id) as $k => $v) {
            if (preg_match('/^_?(faq|modifications|similar_products)(_|$)/', $k)) {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    /** Прогон плана. $write = false — только проверка. */
    public static function run(bool $write): array
    {
        $log = [];
        $backup = ['posts' => [], 'terms' => [], 'options' => []];
        $pending = [];

        foreach (self::plan() as $slug => $plan) {
            $post = self::product($slug);
            if (!$post) {
                $log[] = [false, "{$slug}: товар не найден"];
                continue;
            }
            $id = $post->ID;
            $backup['posts'][$id] = ['slug' => $slug, 'content' => $post->post_content, 'status' => $post->post_status, 'meta' => self::metaSnapshot($id)];
            $job = ['id' => $id];

            if (!empty($plan['block'])) {
                $blocks = parse_blocks($post->post_content);
                $bi = null;
                foreach ($blocks as $i => $b) {
                    if (($b['blockName'] ?? '') === 'acf/product') {
                        $bi = $i;
                        break;
                    }
                }
                if ($bi === null) {
                    $log[] = [false, "{$slug}: блок acf/product не найден"];
                } else {
                    $data = $blocks[$bi]['attrs']['data'] ?? [];
                    foreach ($plan['block'] as $field => $ops) {
                        $keys = self::blockKey($data, $field);
                        if (count($keys) !== 1) {
                            $log[] = [false, "{$slug}/{$field}: ключей в блоке " . count($keys) . ', нужен 1'];
                            continue;
                        }
                        [$data[$keys[0]], $flog] = self::applyOps((string) $data[$keys[0]], $ops);
                        foreach ($flog as $l) {
                            $log[] = [$l[0], "{$slug}/{$field}: {$l[1]}"];
                        }
                    }
                    $blocks[$bi]['attrs']['data'] = $data;
                    $job['content'] = serialize_blocks($blocks);
                }
            }

            if (!empty($plan['faq'])) {
                $job['faq'] = self::textRows(self::plainRepeater(get_field('faq', $id)), $plan['faq'], $log, "{$slug}/faq");
            }

            if (!empty($plan['mods_add']) || !empty($plan['mods_code']) || !empty($plan['mods_area'])) {
                $mods = self::plainRepeater(get_field('modifications', $id));
                $codes = array_column($mods, 'code');
                foreach ($plan['mods_add'] ?? [] as $row) {
                    $exists = in_array($row['code'], $codes, true);
                    $log[] = [!$exists, "{$slug}/модификации: добавить {$row['code']}" . ($exists ? ' — уже есть' : '')];
                    if (!$exists) {
                        $mods[] = $row;
                    }
                }
                foreach ($plan['mods_code'] ?? [] as $from => $to) {
                    $n = 0;
                    foreach ($mods as &$r) {
                        if (trim((string) $r['code']) === $from) {
                            $r['code'] = $to;
                            $n++;
                        }
                    }
                    unset($r);
                    $log[] = [$n === 1, "{$slug}/модификации: {$from} → {$to}, найдено {$n}"];
                }
                foreach ($plan['mods_area'] ?? [] as $code => [$old, $new]) {
                    $n = 0;
                    foreach ($mods as &$r) {
                        if (trim((string) $r['code']) === $code && (float) $r['area_m2'] === (float) $old) {
                            $r['area_m2'] = $new;
                            $n++;
                        }
                    }
                    unset($r);
                    $log[] = [$n === 1, "{$slug}/модификации: {$code} площадь {$old} → {$new}, найдено {$n}"];
                }
                $job['mods'] = $mods;
            }

            if (!empty($plan['similar'])) {
                $ids = [];
                foreach ($plan['similar'] as $s) {
                    $p = self::product($s);
                    $ok = $p && $p->post_status === 'publish';
                    $log[] = [$ok, "{$slug}/похожие: {$s}" . ($ok ? " (#{$p->ID})" : ' — не найден или не опубликован')];
                    if ($ok) {
                        $ids[] = $p->ID;
                    }
                }
                $job['similar'] = $ids;
            }

            if (!empty($plan['status'])) {
                $log[] = [true, "{$slug}: статус {$post->post_status} → {$plan['status']}"];
                $job['status'] = $plan['status'];
            }

            $pending[] = $job;
        }

        $termJobs = [];
        foreach (self::termPlan() as $slug => $ops) {
            $term = get_term_by('slug', $slug, 'product-category');
            if (!$term) {
                $log[] = [false, "категория {$slug} не найдена"];
                continue;
            }
            $rows = self::plainRepeater(get_field('faq', $term));
            $backup['terms'][$term->term_id] = get_term_meta($term->term_id);
            $termJobs[] = [$term, self::textRows($rows, $ops, $log, "категория {$slug}/faq")];
        }

        global $wpdb;
        $optionJobs = [];
        $names = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'options\\_footer\\_section\\_%' AND option_value LIKE '%17:00%'");
        $log[] = [count($names) > 0, 'подвал: опций с часами ' . count($names) . ' (' . implode(', ', $names) . ')'];
        foreach ($names as $name) {
            $value = (string) get_option($name);
            $backup['options'][$name] = $value;
            [$new, $flog] = self::applyOps($value, self::footerPlan());
            foreach ($flog as $l) {
                $log[] = [$l[0], "{$name}: {$l[1]}"];
            }
            $optionJobs[$name] = $new;
        }

        $allOk = !in_array(false, array_column($log, 0), true);
        if (!$write || !$allOk) {
            return [$log, $allOk, false];
        }

        if (!get_option(self::BACKUP)) {
            update_option(self::BACKUP, $backup, false);
        }
        foreach ($pending as $job) {
            $id = $job['id'];
            if (isset($job['content'])) {
                wp_update_post(wp_slash(['ID' => $id, 'post_content' => $job['content']]));
            }
            if (isset($job['faq'])) {
                update_field('faq', $job['faq'], $id);
            }
            if (isset($job['mods'])) {
                update_field('modifications', $job['mods'], $id);
            }
            if (isset($job['similar'])) {
                update_field('similar_products', $job['similar'], $id);
            }
            if (isset($job['status'])) {
                wp_update_post(['ID' => $id, 'post_status' => $job['status']]);
            }
            ProductData::flush($id);
        }
        foreach ($termJobs as [$term, $rows]) {
            update_field('faq', $rows, $term);
        }
        foreach ($optionJobs as $name => $value) {
            update_option($name, $value);
        }
        self::flushCaches();
        return [$log, true, true];
    }

    public static function rollback(): string
    {
        $b = get_option(self::BACKUP);
        if (!$b) {
            return 'Резервной копии нет.';
        }
        foreach ($b['posts'] as $id => $p) {
            wp_update_post(wp_slash(['ID' => $id, 'post_content' => $p['content'], 'post_status' => $p['status']]));
            foreach (array_keys(self::metaSnapshot($id)) as $k) {
                delete_post_meta($id, $k);
            }
            foreach ($p['meta'] as $k => $values) {
                foreach ($values as $v) {
                    add_post_meta($id, $k, maybe_unserialize($v));
                }
            }
            ProductData::flush($id);
        }
        foreach ($b['terms'] as $termId => $meta) {
            foreach (get_term_meta($termId) as $k => $v) {
                if (preg_match('/^_?faq(_|$)/', $k)) {
                    delete_term_meta($termId, $k);
                }
            }
            foreach ($meta as $k => $values) {
                if (preg_match('/^_?faq(_|$)/', $k)) {
                    foreach ($values as $v) {
                        add_term_meta($termId, $k, maybe_unserialize($v));
                    }
                }
            }
        }
        foreach ($b['options'] as $name => $value) {
            update_option($name, $value);
        }
        delete_option(self::BACKUP);
        self::flushCaches();
        return 'Откат выполнен, резервная копия удалена.';
    }

    /* ------------------------------------------------------------------ */
    /* Фото «Электрорезерв» — отдельный шаг, после согласования ретуши     */
    /* ------------------------------------------------------------------ */

    /* ------------------------------------------------------------------ */
    /* Доп. правки после основного применения (решения владельца)          */
    /* ------------------------------------------------------------------ */

    private const EXTRA_BACKUP = 'gkb_site_edits_2611_extra_backup';

    /** FAQ товаров: slug => [[from, to]]. */
    public static function extraPlan(): array
    {
        return [
            // FAQ Житомир-3: мощность верхней модели — как в руководстве 2026 (КС-Г-045 — 40 кВт)
            'zhytomyr-3' => [['r', 'у модели на 45 кВт', 'у модели на 40 кВт']],
        ];
    }

    public static function runExtra(bool $write): array
    {
        $log = [];
        $jobs = [];
        foreach (self::extraPlan() as $slug => $ops) {
            $post = self::product($slug);
            if (!$post) {
                $log[] = [false, "{$slug}: товар не найден"];
                continue;
            }
            $rows = self::plainRepeater(get_field('faq', $post->ID));
            $jobs[] = [$post->ID, $rows, self::textRows($rows, $ops, $log, "{$slug}/faq")];
        }
        $allOk = !in_array(false, array_column($log, 0), true);
        if (!$write || !$allOk) {
            return [$log, $allOk, false];
        }
        $backup = (array) get_option(self::EXTRA_BACKUP, []);
        foreach ($jobs as [$id, $before, $after]) {
            $backup[$id] = $backup[$id] ?? $before;
            update_field('faq', $after, $id);
        }
        update_option(self::EXTRA_BACKUP, $backup, false);
        self::flushCaches();
        $log[] = [true, 'записано'];
        return [$log, true, true];
    }

    private const PHOTO_BACKUP = 'gkb_site_edits_2611_photos_backup';

    /** Галерея: путь исходника в uploads → русская версия в seo/site-edits-2611/. */
    public static function photoPlan(): array
    {
        return [
            'gallery' => [
                '2023/06/60cb050916913.jpg' => '60cb050916913-ru.jpg',   // Житомир-3
                '2023/07/60cb062aa472e-1.jpg' => '60cb062aa472e-1-ru.jpg', // Житомир-10
                '2023/07/60cb09d3c28c2.jpg' => '60cb09d3c28c2-ru.jpg',   // Житомир-М
                '2023/07/6196020949d1c.jpg' => '6196020949d1c-ru.jpg',   // Житомир-9
            ],
            // картинка «Электрорезерв» в «Особенностях»: <img> с src, оканчивающимся на img-04.png
            'inline' => ['file' => 'img-04-ru.png', 'match' => 'img-04.png', 'products' => ['zhytomyr-3', 'zhytomyr-10']],
        ];
    }

    private static function photoDir(): string
    {
        return __DIR__ . '/site-edits-2611/';
    }

    private static function sideload(string $file, string $alt): int
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $tmp = wp_tempnam($file);
        copy(self::photoDir() . $file, $tmp);
        $id = media_handle_sideload(['name' => $file, 'tmp_name' => $tmp], 0);
        if (is_wp_error($id)) {
            @unlink($tmp);
            return 0;
        }
        if ($alt !== '') {
            update_post_meta($id, '_wp_attachment_image_alt', $alt);
        }
        return (int) $id;
    }

    public static function runPhotos(bool $write): array
    {
        $log = [];
        $plan = self::photoPlan();
        $products = get_posts(['post_type' => 'products', 'post_status' => 'any', 'numberposts' => -1]);

        // какие вложения меняем и где они стоят
        $swap = [];
        foreach ($plan['gallery'] as $path => $file) {
            $oldId = attachment_url_to_postid(content_url('uploads/' . $path));
            $exists = is_file(self::photoDir() . $file);
            $log[] = [$oldId > 0 && $exists, "{$path}: вложение #{$oldId}, новый файл " . ($exists ? 'есть' : 'НЕ НАЙДЕН')];
            if ($oldId) {
                $swap[$oldId] = $file;
            }
        }
        $jobs = [];
        foreach ($products as $post) {
            $blocks = parse_blocks($post->post_content);
            foreach ($blocks as $bi => $b) {
                if (($b['blockName'] ?? '') !== 'acf/product') {
                    continue;
                }
                $data = $b['attrs']['data'] ?? [];
                foreach ($data as $k => $v) {
                    if (preg_match('/^ru_language_image_slider_\d+_image$/', $k) && isset($swap[(int) $v])) {
                        $jobs[$post->ID]['slider'][] = [$bi, $k, (int) $v];
                        $log[] = [true, "{$post->post_name}: {$k} = #{$v} — заменить"];
                    }
                }
                if (in_array($post->post_name, $plan['inline']['products'], true)) {
                    $html = (string) ($data['ru_language_features_and_benefits'] ?? '');
                    $n = preg_match_all('#<img\b[^>]*\bsrc="[^"]*/' . preg_quote($plan['inline']['match'], '#') . '"[^>]*>#i', $html);
                    $log[] = [$n === 1, "{$post->post_name}/features_and_benefits: картинок «{$plan['inline']['match']}» {$n}, нужна 1"];
                    if ($n === 1) {
                        $jobs[$post->ID]['inline'] = $bi;
                    }
                }
            }
            $thumb = (int) get_post_thumbnail_id($post->ID);
            if ($thumb && isset($swap[$thumb])) {
                $jobs[$post->ID]['thumb'] = $thumb;
                $log[] = [true, "{$post->post_name}: миниатюра записи #{$thumb} — заменить"];
            }
        }
        $inlineOk = is_file(self::photoDir() . $plan['inline']['file']);
        $log[] = [$inlineOk, "{$plan['inline']['file']}: " . ($inlineOk ? 'есть' : 'НЕ НАЙДЕН')];

        $allOk = !in_array(false, array_column($log, 0), true);
        if (!$write || !$allOk) {
            return [$log, $allOk, false];
        }

        // загрузка новых вложений
        $newIds = [];
        foreach ($swap as $oldId => $file) {
            $newIds[$oldId] = self::sideload($file, (string) get_post_meta($oldId, '_wp_attachment_image_alt', true));
            $log[] = [$newIds[$oldId] > 0, "{$file}: загружен как #{$newIds[$oldId]}"];
        }
        $inlineId = self::sideload($plan['inline']['file'], 'Атем Электрорезерв — подготовка под установку электрических ТЭНов');
        $log[] = [$inlineId > 0, "{$plan['inline']['file']}: загружен как #{$inlineId}"];
        if (in_array(0, $newIds, true) || !$inlineId) {
            return [$log, false, false];
        }

        $backup = ['posts' => []];
        foreach ($jobs as $postId => $job) {
            $post = get_post($postId);
            $backup['posts'][$postId] = ['content' => $post->post_content, 'thumb' => (int) get_post_thumbnail_id($postId)];
            $blocks = parse_blocks($post->post_content);
            foreach ($job['slider'] ?? [] as [$bi, $k, $old]) {
                $blocks[$bi]['attrs']['data'][$k] = (string) $newIds[$old];
            }
            if (isset($job['inline'])) {
                $bi = $job['inline'];
                $url = wp_get_attachment_url($inlineId);
                $blocks[$bi]['attrs']['data']['ru_language_features_and_benefits'] = preg_replace_callback(
                    '#<img\b[^>]*\bsrc="[^"]*/' . preg_quote($plan['inline']['match'], '#') . '"[^>]*>#i',
                    static function ($m) use ($url, $inlineId) {
                        $tag = preg_replace('#\s(srcset|sizes)="[^"]*"#i', '', $m[0]);
                        // и src, и data-src (в старых описаниях встречается ленивая загрузка)
                        $tag = preg_replace('#\b((?:data-)?src)="[^"]*"#i', '$1="' . esc_url($url) . '"', $tag);
                        return preg_replace('#wp-image-\d+#', 'wp-image-' . $inlineId, $tag);
                    },
                    $blocks[$bi]['attrs']['data']['ru_language_features_and_benefits']
                );
            }
            wp_update_post(wp_slash(['ID' => $postId, 'post_content' => serialize_blocks($blocks)]));
            if (isset($job['thumb'])) {
                set_post_thumbnail($postId, $newIds[$job['thumb']]);
            }
            ProductData::flush($postId);
        }
        if (!get_option(self::PHOTO_BACKUP)) {
            update_option(self::PHOTO_BACKUP, $backup, false);
        }
        self::flushCaches();
        $log[] = [true, 'фото заменены, старые вложения остались в медиатеке'];
        return [$log, true, true];
    }

    public static function rollbackPhotos(): string
    {
        $b = get_option(self::PHOTO_BACKUP);
        if (!$b) {
            return 'Резервной копии фото нет.';
        }
        foreach ($b['posts'] as $id => $p) {
            wp_update_post(wp_slash(['ID' => $id, 'post_content' => $p['content']]));
            if ($p['thumb']) {
                set_post_thumbnail($id, $p['thumb']);
            }
            ProductData::flush($id);
        }
        delete_option(self::PHOTO_BACKUP);
        self::flushCaches();
        return 'Фото возвращены. Новые вложения остались в медиатеке — их можно удалить вручную.';
    }

    private static function flushCaches(): void
    {
        if (function_exists('wpo_cache_flush')) {
            wpo_cache_flush();
        }
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }

    public static function page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $notice = '';
        $log = null;
        $ok = false;
        $written = false;
        $action = $_POST['gkb_action'] ?? '';
        if ($action && check_admin_referer('gkb_site_edits_2611')) {
            if ($action === 'rollback') {
                $notice = self::rollback();
            } elseif (str_starts_with($action, 'extra')) {
                [$log, $ok, $written] = self::runExtra($action === 'extra-apply');
            } elseif ($action === 'photos-rollback') {
                $notice = self::rollbackPhotos();
            } elseif (str_starts_with($action, 'photos')) {
                [$log, $ok, $written] = self::runPhotos($action === 'photos-apply');
            } else {
                [$log, $ok, $written] = self::run($action === 'apply');
            }
        }
        $hasBackup = (bool) get_option(self::BACKUP);
        $hasPhotoBackup = (bool) get_option(self::PHOTO_BACKUP);

        echo '<div class="wrap"><h1>Правки 11.2026</h1>';
        echo '<p>Правки по скриншотам заказчика (openspec <code>site-edits-page-3</code>). «Проверить» ничего не записывает.</p>';
        if ($notice) {
            echo '<div class="notice notice-info"><p>' . esc_html($notice) . '</p></div>';
        }
        if ($log !== null) {
            $cls = $written ? 'notice-success' : ($ok ? 'notice-info' : 'notice-error');
            $msg = $written ? 'Правки записаны, кеш сброшен.' : ($ok ? 'Все проверки прошли — можно применять.' : 'Есть ошибки — запись невозможна.');
            echo '<div class="notice ' . $cls . '"><p>' . esc_html($msg) . '</p></div>';
            echo '<table class="widefat striped" id="gkb-edits-log"><tbody>';
            foreach ($log as [$good, $text]) {
                echo '<tr><td style="width:2em">' . ($good ? '✅' : '❌') . '</td><td>' . esc_html($text) . '</td></tr>';
            }
            echo '</tbody></table>';
        }
        echo '<form method="post" style="margin-top:16px;display:flex;gap:8px">';
        wp_nonce_field('gkb_site_edits_2611');
        echo '<button class="button" name="gkb_action" value="check">Проверить</button>';
        if (!$hasBackup) {
            echo '<button class="button button-primary" name="gkb_action" value="apply" onclick="return confirm(\'Записать правки на сайт?\')">Применить</button>';
        } else {
            echo '<button class="button" name="gkb_action" value="rollback" onclick="return confirm(\'Вернуть всё как было до правок?\')">Откатить</button>';
        }
        echo '</form>';
        if ($hasBackup) {
            echo '<p>Правки уже применены (есть резервная копия). Повторное применение заблокировано.</p>';
        }

        echo '<h2 style="margin-top:32px">Доп. правки</h2><p>FAQ Житомир-3: «на 45 кВт» → «на 40 кВт» (как в руководстве 2026).</p>';
        echo '<form method="post" style="display:flex;gap:8px">';
        wp_nonce_field('gkb_site_edits_2611');
        echo '<button class="button" name="gkb_action" value="extra-check">Проверить доп. правки</button>';
        echo '<button class="button button-primary" name="gkb_action" value="extra-apply">Применить доп. правки</button>';
        echo '</form>';

        echo '<h2 style="margin-top:32px">Фото «Электрорезерв»</h2>';
        echo '<p>Только после того, как заказчик одобрил ретушь. Новые файлы загружаются в медиатеку, старые остаются.</p>';
        echo '<form method="post" style="display:flex;gap:8px">';
        wp_nonce_field('gkb_site_edits_2611');
        echo '<button class="button" name="gkb_action" value="photos-check">Проверить фото</button>';
        if (!$hasPhotoBackup) {
            echo '<button class="button button-primary" name="gkb_action" value="photos-apply" onclick="return confirm(\'Заменить фото на сайте?\')">Заменить фото</button>';
        } else {
            echo '<button class="button" name="gkb_action" value="photos-rollback" onclick="return confirm(\'Вернуть прежние фото?\')">Вернуть прежние фото</button>';
        }
        echo '</form></div>';
    }
}
