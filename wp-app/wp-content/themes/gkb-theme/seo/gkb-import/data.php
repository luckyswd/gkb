<?php
/**
 * Данные импорта товаров GKB (openspec/changes/add-gkb-products).
 * Источники: руководство колонок GKB 2026 (табл. 1, 2, разделы 5 и 10) и
 * презентация «Презентация GKB колонки 2» (слайды 22–29).
 * Удаляется из темы вместе с GkbImport.php после приёмки.
 */

namespace seo\GkbImportData;

/** HTML-таблица. Строка: [подпись, v1, v2, ...] или [подпись, ['all' => значение]] — одно значение на все столбцы. */
function table(?array $head, array $rows): string
{
    $n = $head ? count($head) - 1 : 1;
    $h = '<table><tbody>';
    if ($head) {
        $h .= '<tr>';
        foreach ($head as $i => $c) {
            $h .= $i === 0 ? '<td><strong>' . $c . '</strong></td>' : '<td><strong>' . $c . '</strong></td>';
        }
        $h .= '</tr>';
    }
    foreach ($rows as $r) {
        $h .= '<tr><td>' . $r[0] . '</td>';
        if (is_array($r[1] ?? null)) {
            $h .= '<td' . ($n > 1 ? ' colspan="' . $n . '"' : '') . '>' . $r[1]['all'] . '</td>';
        } else {
            foreach (array_slice($r, 1) as $v) {
                $h .= '<td>' . $v . '</td>';
            }
        }
        $h .= '</tr>';
    }
    return $h . '</tbody></table>';
}

function ul(array $items): string
{
    return '<ul><li>' . implode('</li><li>', $items) . '</li></ul>';
}

function p(string ...$ps): string
{
    return '<p>' . implode('</p><p>', $ps) . '</p>';
}

/* ---------------------------------------------------------------------- */
/* Колонки: таблица 1 руководства 2026                                     */
/* ---------------------------------------------------------------------- */

/** Значения по моделям (числа — с запятой, как на сайте). */
function vpgModels(): array
{
    return [
        'ВПГ-20' => ['en' => 'VPG-20', 'kw' => 20, 'gas' => '2,07', 'type' => 'B11BS', 'ign' => 'std', 'wp' => '0,025–0,8', 'w25' => '10', 'w30' => '8,3', 'noz' => '1,02', 'flue' => '110', 'dim' => '55×33×18,8', 'kg' => '8 ± 5 %'],
        'ВПГ-24' => ['en' => 'VPG-24', 'kw' => 24, 'gas' => '2,42', 'type' => 'B11BS', 'ign' => 'std', 'wp' => '0,025–0,8', 'w25' => '12', 'w30' => '10', 'noz' => '1,12', 'flue' => '110', 'dim' => '61×35×18,8', 'kg' => '9,3 ± 5 %'],
        'ВПГ-26' => ['en' => 'VPG-26', 'kw' => 26, 'gas' => '2,55', 'type' => 'B11BS', 'ign' => 'std', 'wp' => '0,025–0,8', 'w25' => '13', 'w30' => '10,8', 'noz' => '1,22', 'flue' => '130', 'dim' => '65×40×18,8', 'kg' => '13,2 ± 5 %'],
        'ВПГ-20М' => ['en' => 'VPG-20M', 'kw' => 20, 'gas' => '2,1', 'type' => 'B11BS', 'ign' => 'std', 'wp' => '0,01–0,8', 'w25' => '10', 'w30' => '8,3', 'noz' => '1,05', 'flue' => '110', 'dim' => '61×35×18,8', 'kg' => '9,3 ± 5 %'],
        'ВПГ-24М' => ['en' => 'VPG-24M', 'kw' => 24, 'gas' => '2,42', 'type' => 'B11BS', 'ign' => 'std', 'wp' => '0,01–0,8', 'w25' => '12', 'w30' => '10', 'noz' => '1,12', 'flue' => '110', 'dim' => '61×35×18,8', 'kg' => '9,6 ± 5 %'],
        'ВПГ-22Т' => ['en' => 'VPG-22T', 'kw' => 22, 'gas' => '2,2', 'type' => 'C12', 'ign' => 'ac', 'wp' => '0,01–0,8', 'w25' => '11', 'w30' => '9,2', 'noz' => '1,7', 'flue' => '60/100', 'dim' => '55×35×14', 'kg' => '10,2 ± 5 %'],
        'ВПГ-26Т' => ['en' => 'VPG-26T', 'kw' => 26, 'gas' => '2,51', 'type' => 'C12', 'ign' => 'ac', 'wp' => '0,01–0,8', 'w25' => '13', 'w30' => '10,8', 'noz' => '1,75', 'flue' => '60/100', 'dim' => '55×35×14', 'kg' => '10,4 ± 5 %'],
    ];
}

/** Числа в EN — с точкой. */
function en(string $v): string
{
    return str_replace(',', '.', $v);
}

/** Одинаково у всех моделей серии → одно значение на все столбцы. */
function cells(array $models, string $key, bool $isEn = false, ?callable $fmt = null): array
{
    $vals = [];
    foreach ($models as $m) {
        $v = (string) $m[$key];
        $v = $fmt ? $fmt($v) : $v;
        $vals[] = $isEn ? en($v) : $v;
    }
    return count(array_unique($vals)) === 1 ? [['all' => $vals[0]]] : $vals;
}

function vpgSpecs(array $codes, bool $isEn): string
{
    $all = vpgModels();
    $models = array_map(static fn ($c) => $all[$c], $codes);
    $head = array_merge([$isEn ? 'Model' : 'Модель'], $isEn ? array_column($models, 'en') : $codes);
    $ign = static fn ($v) => $v === 'ac'
        ? ($isEn ? 'AC 220 V / 50 Hz' : 'AC 220 В / 50 Гц')
        : ($isEn ? 'DC 3 V (2×R20) / AC 220 V / 50 Hz via Type-C' : 'DC 3 В (2×R20) / AC 220 В / 50 Гц через Type-C');
    $row = static fn (string $label, array $c) => array_merge([$label], $c);

    $rows = $isEn ? [
        $row('Heat output, kW', cells($models, 'kw', true)),
        ['Gas type', ['all' => 'Natural gas']],
        ['Nominal gas pressure, Pa', ['all' => '2000']],
        $row('Appliance type', cells($models, 'type', true)),
        ['Appliance category', ['all' => 'I2H']],
        $row('Nominal natural gas consumption, m³/h', cells($models, 'gas', true)),
        $row('Ignition / power supply', cells($models, 'ign', true, $ign)),
        $row('Water pressure range, MPa', cells($models, 'wp', true)),
        $row('Hot water flow at ΔT = 25 °C, l/min', cells($models, 'w25', true)),
        $row('Hot water flow at ΔT = 30 °C, l/min', cells($models, 'w30', true)),
        ['Minimum water pressure, kPa', ['all' => '15']],
        ['Required flue draught, Pa (mm w.c.)', ['all' => 'not less than 2.0 (0.2), not more than 30.0 (3.0)']],
        ['Average flue gas temperature, °C', ['all' => 'not less than 110']],
        ['Gas inlet', ['all' => 'G ½″']],
        ['Water inlet / outlet', ['all' => 'G ½″']],
        $row('Burner nozzle diameter (natural gas, 2.0 kPa), mm', cells($models, 'noz', true)),
        $row('Flue diameter, mm', cells($models, 'flue', true)),
        $row('Dimensions, cm', cells($models, 'dim', true)),
        $row('Net weight, kg', cells($models, 'kg', true)),
    ] : [
        $row('Тепловая мощность, кВт', cells($models, 'kw')),
        ['Тип газа', ['all' => 'Природный газ']],
        ['Номинальное давление газа, Па', ['all' => '2000']],
        $row('Тип устройства', cells($models, 'type')),
        ['Категория бытовой техники', ['all' => 'I2H']],
        $row('Номинальный расход природного газа, м³/час', cells($models, 'gas')),
        $row('Розжиг / питание', cells($models, 'ign', false, $ign)),
        $row('Диапазон давления воды, МПа', cells($models, 'wp')),
        $row('Расход горячей воды при ΔT = 25 °C, л/мин', cells($models, 'w25')),
        $row('Расход горячей воды при ΔT = 30 °C, л/мин', cells($models, 'w30')),
        ['Минимальное давление воды, кПа', ['all' => '15']],
        ['Требуемое разрежение в дымоходе, Па (мм вод. ст.)', ['all' => 'не менее 2,0 (0,2), не более 30,0 (3,0)']],
        ['Средняя температура продуктов сгорания, °C', ['all' => 'не менее 110']],
        ['Размер газопроводного входа', ['all' => 'G ½″']],
        ['Размер трубы для подачи / слива воды', ['all' => 'G ½″']],
        $row('Диаметр сопел горелки (природный газ, 2,0 кПа), мм', cells($models, 'noz')),
        $row('Диаметр дымохода, мм', cells($models, 'flue')),
        $row('Габаритные размеры, см', cells($models, 'dim')),
        $row('Масса нетто, кг', cells($models, 'kg')),
    ];
    // строки вида [label, ['all'=>v]] после array_merge остаются в этом же формате
    return table($head, $rows);
}

function vpgEquipment(bool $turbo, bool $isEn): string
{
    $rows = $isEn ? [
        ['Instantaneous gas water heater', '1 pc.'],
        ['Manual', '1 pc.'],
        ['Mounting kit', '1 pc.'],
    ] : [
        ['Проточный газовый водонагреватель', '1 шт.'],
        ['Руководство по эксплуатации', '1 шт.'],
        ['Крепёжный комплект', '1 шт.'],
    ];
    if ($turbo) {
        $rows[] = $isEn ? ['Coaxial flue pipe', '1 pc.'] : ['Труба дымоходная', '1 шт.'];
    }
    $html = table([$isEn ? 'Item' : 'Наименование', $isEn ? 'Qty' : 'Кол-во'], $rows);
    if (!$turbo) {
        $html .= $isEn ? p('Batteries are not included.') : p('Элементы питания в комплект поставки не входят.');
    }
    return $html;
}

function vpgCommonFeatures(bool $isEn): array
{
    return $isEn ? [
        '<strong>Automatic ignition.</strong> The heater lights up by itself when a hot water tap is opened; the flame is monitored by an ionisation sensor.',
        '<strong>Flame failure protection.</strong> If the burner flame goes out, the ionisation sensor shuts off the gas supply.',
        '<strong>Dry firing protection.</strong> Without water flow the gas supply is shut off.',
        '<strong>High water pressure protection.</strong> The safety valve relieves pressure above 10 bar.',
        '<strong>Overheat protection.</strong> When the water temperature exceeds 75 °C, the gas supply to the burner stops.',
        '<strong>40-minute timer.</strong> After 40 minutes of continuous operation the heater switches off; restart it to continue.',
        '<strong>Flue gas monitoring.</strong> If the draught is insufficient and combustion products may enter the room, the gas supply is shut off immediately.',
    ] : [
        '<strong>Автоматический розжиг.</strong> Колонка включается сама, когда открывают кран горячей воды; пламя контролирует ионизационный датчик.',
        '<strong>Защита от погасания пламени.</strong> Если пламя горелки погасло, датчик ионизации перекрывает подачу газа.',
        '<strong>Защита от сухого горения.</strong> Без протока воды подача газа перекрывается.',
        '<strong>Защита от высокого давления воды.</strong> Предохранительный клапан сбрасывает давление выше 10 бар.',
        '<strong>Защита от перегрева.</strong> Когда температура воды превышает 75 °C, подача газа к горелке прекращается.',
        '<strong>Таймер 40 минут.</strong> После 40 минут непрерывной работы колонка отключается; чтобы продолжить, её нужно перезапустить.',
        '<strong>Контроль отвода продуктов сгорания.</strong> Если тяги недостаточно и продукты сгорания могут попасть в помещение, подача газа сразу перекрывается.',
    ];
}

function brandNote(bool $isEn): string
{
    return $isEn
        ? p('GKB water heaters are made under the GKB trademark of GazKotelBel LLC: manufactured in China with quality control and to the company’s technical specifications. Warranty — 2 years from the date of sale; warranty service is provided by GazKotelBel LLC and its authorised representatives.')
        : p('Колонки выпускаются под собственной торговой маркой GKB компании ООО «ГазКотелБел»: производство в Китае с контролем качества и по техническим условиям компании. Гарантия — 2 года со дня продажи, гарантийный ремонт выполняет ООО «ГазКотелБел» или его официальные представители.');
}

/* ---------------------------------------------------------------------- */
/* Стабилизаторы: слайды 22–26                                             */
/* ---------------------------------------------------------------------- */

function snSpecs(string $va, string $w, bool $isEn): string
{
    $rows = $isEn ? [
        ['Output power', "$va VA, $w W max"],
        ['Input AC voltage range', '100–270 V~'],
        ['Input frequency', '50 Hz'],
        ['Output AC voltage', '220 V'],
        ['Output frequency', 'synchronised with input frequency'],
        ['Output accuracy', '±8 %'],
        ['Distortion', '< 3 %'],
        ['Efficiency', '> 90 %'],
        ['Delay time', '6 / 180 s, selectable'],
        ['Protection', 'undervoltage, overvoltage, overheating'],
        ['Noise', '< 65 dB at 1 m'],
        ['Ingress protection', 'IP20'],
        ['Operating temperature', '−10…+40 °C'],
        ['Storage temperature', '−20…+45 °C'],
        ['Operating humidity', 'up to 95 %, non-condensing'],
        ['Safety', 'CE (EMC + LVD)'],
        ['Warranty', '1 year'],
    ] : [
        ['Выходная мощность', "$va ВА, $w Вт макс."],
        ['Диапазон входного переменного напряжения', '100–270 В~'],
        ['Входная частота', '50 Гц'],
        ['Выходное напряжение переменного тока', '220 В'],
        ['Выходная частота', 'синхронизирована с входной'],
        ['Точность выходного напряжения', '±8 %'],
        ['Искажения', '< 3 %'],
        ['КПД', '> 90 %'],
        ['Время задержки', '6 / 180 с по выбору'],
        ['Защита', 'от пониженного и повышенного напряжения, от перегрева'],
        ['Шум', '< 65 дБ на расстоянии 1 м'],
        ['Степень защиты оболочки', 'IP20'],
        ['Рабочая температура', '−10…+40 °C'],
        ['Температура хранения', '−20…+45 °C'],
        ['Рабочая влажность', 'до 95 %, без конденсации'],
        ['Безопасность', 'CE (EMC + LVD)'],
        ['Гарантия', '1 год'],
    ];
    return table(null, $rows);
}

function snFeatures(bool $isEn): string
{
    return $isEn
        ? p('GKB SN relay voltage stabilizers keep the supply voltage of household appliances at 220 V, 50 Hz in single-phase networks. The series is designed to protect boiler equipment and other devices from input voltage fluctuations.')
          . p('<strong>Applications:</strong> boiler equipment, security systems, lighting, laboratory and test installations, water supply systems, medical equipment, office equipment.')
          . p('<strong>Front panel indicators:</strong>') . ul(['green — “Power on”;', 'yellow — “Delay”;', 'red — “Protection”.'])
          . p('The output socket has a self-resetting fuse. The delay button selects a 6 or 180 second pause before voltage is supplied to the load.')
          . p('<strong>Installation:</strong> the stabilizer must be earthed and mounted on a vertical surface, at least 30 cm from walls, away from direct sunlight, in a non-explosive environment without aggressive vapours and conductive dust. After transport in frost, keep it at room temperature for at least 2 hours before switching on. Connection should be made by a qualified specialist.')
          . p('GKB is the own trademark of GazKotelBel LLC: manufactured in China with quality control and to the company’s technical specifications.')
        : p('Релейные стабилизаторы напряжения GKB SN поддерживают напряжение 220 В, 50 Гц для бытовых электроприборов в однофазных сетях. Серия разработана для защиты котельного оборудования и других устройств при перепадах входного напряжения.')
          . p('<strong>Сфера применения:</strong> котельное оборудование, охранные системы, системы освещения, лаборатории и испытательные установки, системы водоснабжения, медицинское оборудование, оргтехника.')
          . p('<strong>Индикаторы на передней панели:</strong>') . ul(['зелёный — «Питание включено»;', 'жёлтый — «Задержка»;', 'красный — «Защита».'])
          . p('Выходной разъём — с самовосстанавливающимся предохранителем. Кнопкой задержки выбирается пауза 6 или 180 секунд перед подачей напряжения на нагрузку.')
          . p('<strong>Установка:</strong> стабилизатор должен быть заземлён и закреплён на вертикальной поверхности, не ближе 30 см от стен, без прямых солнечных лучей, в невзрывоопасной среде без агрессивных паров и токопроводящей пыли. После перевозки на морозе выдержите его при комнатной температуре не менее 2 часов. Подключение выполняет квалифицированный специалист.')
          . p('GKB — собственная торговая марка ООО «ГазКотелБел»: производство в Китае с контролем качества и по техническим условиям компании.');
}

/* ---------------------------------------------------------------------- */
/* Датчики: слайды 27–29                                                   */
/* ---------------------------------------------------------------------- */

function coWarranty(bool $isEn): array
{
    return $isEn ? [['Warranty', '1 year'], ['Service life', '10 years']] : [['Гарантия', '1 год'], ['Срок службы', '10 лет']];
}

function coBrand(bool $isEn): string
{
    return $isEn
        ? p('GKB is the own trademark of GazKotelBel LLC: manufactured in China with quality control and to the company’s technical specifications.')
        : p('GKB — собственная торговая марка ООО «ГазКотелБел»: производство в Китае с контролем качества и по техническим условиям компании.');
}

/* ---------------------------------------------------------------------- */
/* Итоговые данные                                                         */
/* ---------------------------------------------------------------------- */

function terms(): array
{
    return [
        'voltage-stabilizers' => [
            'name' => 'Стабилизаторы напряжения',
            'name_en' => 'Voltage stabilizers',
            'seo_brand' => 'GKB',
            'seo_description' => 'Стабилизаторы напряжения GKB SN-500 и SN-1000 для газовых котлов и бытовой техники: вход 100–270 В, выход 220 В ±8 %, защита от перегрева. Гарантия 1 год.',
            'intro_text' => p(
                'Стабилизаторы напряжения GKB SN — релейные стабилизаторы собственной торговой марки ООО «ГазКотелБел». Они поддерживают на выходе 220 В с точностью ±8 % при входном напряжении от 100 до 270 В и защищают котельное оборудование, насосы, автоматику и другие бытовые приборы от перепадов в сети.',
                'В линейке две модели. <a href="/products/gkb-sn-500/">GKB SN-500</a> рассчитан на 500 ВА (до 300 Вт нагрузки) — этого хватает для автоматики газового котла и циркуляционного насоса. <a href="/products/gkb-sn-1000/">GKB SN-1000</a> — 1000 ВА (до 600 Вт), для котла с несколькими насосами или нескольких приборов сразу.',
                'Обе модели отключают нагрузку при пониженном и повышенном напряжении и при перегреве, а после восстановления сети подают напряжение с задержкой 6 или 180 секунд — время выбирается кнопкой. Состояние показывают три индикатора: «Питание», «Задержка», «Защита». Стабилизатор вешается на стену, его нужно заземлить. Гарантия — 1 год.'
            ),
            'faq' => [
                ['Какой стабилизатор выбрать для котла?', p('Ориентируйтесь на потребляемую электрическую мощность прибора, а не на тепловую мощность котла в кВт. SN-500 рассчитан на нагрузку до 300 Вт, SN-1000 — до 600 Вт. Потребляемая мощность указана в руководстве котла и насоса.')],
                ['Чем SN-500 отличается от SN-1000?', p('Только выходной мощностью: 500 ВА / 300 Вт у SN-500 и 1000 ВА / 600 Вт у SN-1000. Диапазон входного напряжения (100–270 В), точность (±8 %), защиты и время задержки у моделей одинаковые.')],
                ['При каком напряжении в сети работает стабилизатор?', p('При входном напряжении от 100 до 270 В стабилизатор выдаёт 220 В ±8 %. При выходе за эти пределы срабатывает защита и загорается красный индикатор.')],
                ['Что такое время задержки 6 / 180 секунд?', p('Это пауза перед подачей напряжения на нагрузку после включения или восстановления сети. Её выбирают кнопкой задержки: 6 секунд — для большинства приборов, 180 секунд — для техники, которой вредны частые повторные пуски. Пока идёт отсчёт, горит жёлтый индикатор «Задержка».')],
            ],
        ],
        'co-detectors' => [
            'name' => 'Датчики угарного газа',
            'name_en' => 'Carbon monoxide detectors',
            'seo_brand' => 'GKB',
            'seo_description' => 'Датчики угарного газа GKB: CO999 (угарный газ), CO888 (природный и угарный газ, от сети) и CO777 (дым и угарный газ). Характеристики, гарантия 1 год.',
            'intro_text' => p(
                'Датчики (сигнализаторы) GKB подают громкий сигнал тревоги, когда в воздухе появляется угарный газ (CO) — продукт неполного сгорания от газовой колонки, котла, печи или камина. Угарный газ не имеет ни цвета, ни запаха, поэтому без датчика его не заметить. Датчики выпускаются под собственной торговой маркой ООО «ГазКотелБел».',
                'В линейке три модели. <a href="/products/gkb-co999/">GKB CO999</a> обнаруживает только угарный газ и работает от трёх батареек AA. <a href="/products/gkb-co888/">GKB CO888</a> реагирует и на угарный, и на природный газ (метан), включается в розетку. <a href="/products/gkb-co777/">GKB CO777</a> совмещает датчик угарного газа с датчиком дыма и тоже работает от батареек.',
                'Если в доме есть газовые приборы, CO888 предупредит ещё и об утечке природного газа. Если нужен прибор без проводов или защита от пожара — подойдут CO999 и CO777. Гарантия на все датчики — 1 год, срок службы — 10 лет.'
            ),
            'faq' => [
                ['Чем отличаются датчики GKB CO999, CO888 и CO777?', p('Тем, что они обнаруживают. CO999 — только угарный газ (CO). CO888 — угарный газ и природный газ (метан). CO777 — угарный газ и дым.')],
                ['Обнаружит ли CO999 утечку природного газа?', p('Нет. CO999 предназначен только для угарного газа и не реагирует на другие газы. Для контроля утечки природного газа нужен CO888: он срабатывает при концентрации метана 10 % от нижнего концентрационного предела распространения пламени.')],
                ['Какие датчики работают без розетки?', p('CO999 и CO777 — от трёх батареек AA по 1,5 В, батарейки идут в комплекте. CO888 питается от сети 100–250 В и включается прямо в розетку.')],
                ['Какая гарантия и срок службы?', p('Гарантия на датчики GKB — 1 год, срок службы — 10 лет.')],
            ],
        ],
    ];
}

function products(): array
{
    $std = ['ВПГ-20', 'ВПГ-24', 'ВПГ-26'];
    $mod = ['ВПГ-20М', 'ВПГ-24М'];
    $tur = ['ВПГ-22Т', 'ВПГ-26Т'];
    $doc = ['file' => 'gkb-vpg-manual-2026.pdf', 'ru' => 'Руководство по эксплуатации колонок GKB ВПГ 2026', 'en' => 'GKB VPG water heaters manual 2026'];
    $mods = static fn (array $codes) => array_map(static fn ($c) => ['code' => $c, 'power_kw' => vpgModels()[$c]['kw']], $codes);

    return [
        'gkb-vpg-20' => [
            'term' => 'geysers',
            'post_title' => 'GKB ВПГ-20 / 24 / 26',
            'modifications' => $mods($std),
            'images' => ['gkb-vpg-20-front.png', 'gkb-vpg-20-2.jpg', 'gkb-vpg-20-3.jpg', 'gkb-vpg-20-4.jpg', 'gkb-vpg-20-scheme.jpg'],
            'documentation' => $doc,
            'seo_description' => 'Газовая колонка GKB ВПГ-20 / 24 / 26: 20, 24 и 26 кВт, до 13 л/мин горячей воды, розжиг от батареек или через Type-C. Гарантия 2 года, характеристики и руководство.',
            'faq' => [
                ['Сколько горячей воды даёт колонка?', p('При нагреве на 25 °C — 10, 12 и 13 л/мин у ВПГ-20, ВПГ-24 и ВПГ-26. При нагреве на 30 °C — 8,3, 10 и 10,8 л/мин.')],
                ['От чего работает розжиг?', p('От двух батареек R20 по 1,5 В в батарейном отсеке. Можно подключить постоянное питание через разъём Type-C от зарядного устройства 5 В / 2 А. Батарейки в комплект поставки не входят.')],
                ['Какой нужен дымоход?', p('Колонка подключается к дымоходу с естественной тягой: диаметр патрубка 110 мм у ВПГ-20 и ВПГ-24, 130 мм у ВПГ-26. Разрежение в дымоходе — не менее 2 и не более 30 Па.')],
                ['Какая гарантия на колонку?', p('2 года со дня продажи. Гарантийный ремонт выполняет ООО «ГазКотелБел» или его официальные представители.')],
            ],
            'ru' => [
                'title' => 'GKB ВПГ-20 / 24 / 26',
                'subtitle' => 'Проточный газовый водонагреватель, 20–26 кВт',
                'description_catalog' => 'Колонка 20–26 кВт, розжиг от батареек или Type-C',
                'description' => p('Газовая колонка GKB ВПГ — стандартная серия собственной торговой марки ООО «ГазКотелБел». Три модели мощностью 20, 24 и 26 кВт дают до 10, 12 и 13 л/мин горячей воды при нагреве на 25 °C. Электронный розжиг включает колонку при открытии крана, питание — от двух батареек R20 или постоянно через разъём Type-C. Газ и воду регулируют две ручки, температуру показывает дисплей.'),
                'features_and_benefits' => p('Стандартная серия GKB ВПГ-20 / 24 / 26 работает на природном газе и подключается к дымоходу с естественной тягой.')
                    . ul(array_merge([
                        '<strong>Питание без розетки.</strong> Розжиг работает от двух батареек R20 (по 1,5 В) в батарейном отсеке. Для постоянного питания предусмотрен разъём Type-C: колонку можно подключить к сети 220 В через зарядное устройство 5 В / 2 А.',
                        '<strong>Простое управление.</strong> Две ручки — регулировка газа и воды, дисплей показывает температуру горячей воды.',
                    ], vpgCommonFeatures(false)))
                    . brandNote(false),
                'specifications' => vpgSpecs($std, false),
                'equipment' => vpgEquipment(false, false),
            ],
            'en' => [
                'title' => 'GKB VPG-20 / 24 / 26',
                'subtitle' => 'Instantaneous gas water heater, 20–26 kW',
                'description_catalog' => '20–26 kW water heater, battery or Type-C ignition',
                'description' => p('The GKB VPG gas water heater is the standard series of the GazKotelBel own trademark. Three models rated 20, 24 and 26 kW deliver up to 10, 12 and 13 l/min of hot water at a 25 °C rise. Electronic ignition starts the heater when a tap is opened; power comes from two R20 batteries or permanently via a Type-C port. Two knobs control gas and water, a display shows the temperature.'),
                'features_and_benefits' => p('The standard GKB VPG-20 / 24 / 26 series runs on natural gas and connects to a natural-draught flue.')
                    . ul(array_merge([
                        '<strong>No mains socket needed.</strong> Ignition is powered by two R20 batteries (1.5 V each) in the battery compartment. A Type-C port allows permanent power from 220 V via a 5 V / 2 A charger.',
                        '<strong>Simple controls.</strong> Two knobs adjust gas and water, the display shows the hot water temperature.',
                    ], vpgCommonFeatures(true)))
                    . brandNote(true),
                'specifications' => vpgSpecs($std, true),
                'equipment' => vpgEquipment(false, true),
            ],
        ],

        'gkb-vpg-20m' => [
            'term' => 'geysers',
            'post_title' => 'GKB ВПГ-20М / 24М',
            'modifications' => $mods($mod),
            'images' => ['gkb-vpg-20m-front.png', 'gkb-vpg-20m-2.jpg', 'gkb-vpg-20m-3.jpg', 'gkb-vpg-20m-4.jpg', 'gkb-vpg-20m-5.jpg', 'gkb-vpg-20m-scheme.jpg'],
            'documentation' => $doc,
            'seo_description' => 'Газовая колонка GKB ВПГ-20М / 24М с модуляцией пламени: 20 и 24 кВт, до 12 л/мин, работает при давлении воды от 0,015 МПа. Гарантия 2 года, руководство.',
            'faq' => [
                ['Чем серия М отличается от обычной колонки?', p('Модуляцией пламени: пропорциональный газовый клапан подстраивает мощность горелки, поэтому температура воды держится ровнее. Колонка работает при давлении воды всего 0,015 МПа — это удобно на верхних этажах и при слабом напоре.')],
                ['Сколько горячей воды даёт колонка?', p('При нагреве на 25 °C — 10 л/мин у ВПГ-20М и 12 л/мин у ВПГ-24М. При нагреве на 30 °C — 8,3 и 10 л/мин.')],
                ['От чего работает розжиг?', p('От двух батареек R20 по 1,5 В или постоянно через разъём Type-C от зарядного устройства 5 В / 2 А. Батарейки в комплект поставки не входят.')],
                ['Какая гарантия на колонку?', p('2 года со дня продажи. Гарантийный ремонт выполняет ООО «ГазКотелБел» или его официальные представители.')],
            ],
            'ru' => [
                'title' => 'GKB ВПГ-20М / 24М',
                'subtitle' => 'Проточный газовый водонагреватель с модуляцией пламени, 20–24 кВт',
                'description_catalog' => 'Колонка 20–24 кВт с модуляцией пламени',
                'description' => p('GKB ВПГ-20М / 24М — колонки с модуляцией пламени: мощность горелки подстраивается автоматически, и температура воды держится стабильнее. Две модели на 20 и 24 кВт дают до 10 и 12 л/мин горячей воды при нагреве на 25 °C и работают при давлении воды от 0,015 МПа. Розжиг — от батареек R20 или через Type-C, температуру показывает цифровой индикатор.'),
                'features_and_benefits' => p('Серия М работает на природном газе и подключается к дымоходу с естественной тягой (патрубок 110 мм).')
                    . ul(array_merge([
                        '<strong>Модуляция пламени.</strong> Пропорциональный газовый клапан плавно меняет мощность горелки, поэтому температура горячей воды держится ровнее.',
                        '<strong>Работа при низком давлении воды.</strong> Колонка работает при давлении всего 0,015 МПа — подходит для верхних этажей и слабого напора.',
                        '<strong>Питание без розетки.</strong> Две батарейки R20 (по 1,5 В) или постоянное питание через разъём Type-C от зарядного устройства 5 В / 2 А.',
                    ], vpgCommonFeatures(false)))
                    . brandNote(false),
                'specifications' => vpgSpecs($mod, false),
                'equipment' => vpgEquipment(false, false),
            ],
            'en' => [
                'title' => 'GKB VPG-20M / 24M',
                'subtitle' => 'Instantaneous gas water heater with flame modulation, 20–24 kW',
                'description_catalog' => '20–24 kW water heater with flame modulation',
                'description' => p('GKB VPG-20M / 24M heaters feature flame modulation: burner output adjusts automatically, keeping the water temperature more stable. Two models rated 20 and 24 kW deliver up to 10 and 12 l/min of hot water at a 25 °C rise and operate from a water pressure of 0.015 MPa. Ignition is powered by R20 batteries or via Type-C; a digital indicator shows the temperature.'),
                'features_and_benefits' => p('The M series runs on natural gas and connects to a natural-draught flue (110 mm spigot).')
                    . ul(array_merge([
                        '<strong>Flame modulation.</strong> A proportional gas valve smoothly varies burner output, so the hot water temperature stays steadier.',
                        '<strong>Low water pressure operation.</strong> Works at a water pressure of only 0.015 MPa — suitable for upper floors and weak supply.',
                        '<strong>No mains socket needed.</strong> Two R20 batteries (1.5 V each) or permanent power via a Type-C port from a 5 V / 2 A charger.',
                    ], vpgCommonFeatures(true)))
                    . brandNote(true),
                'specifications' => vpgSpecs($mod, true),
                'equipment' => vpgEquipment(false, true),
            ],
        ],

        'gkb-vpg-22t' => [
            'term' => 'geysers',
            'post_title' => 'GKB ВПГ-22Т / 26Т',
            'modifications' => $mods($tur),
            'images' => ['gkb-vpg-22t-front.png', 'gkb-vpg-22t-2.jpg', 'gkb-vpg-22t-3.jpg', 'gkb-vpg-22t-4.jpg', 'gkb-vpg-22t-5.jpg', 'gkb-vpg-22t-scheme.jpg'],
            'documentation' => $doc,
            'seo_description' => 'Турбированная газовая колонка GKB ВПГ-22Т / 26Т: 22 и 26 кВт, до 13 л/мин, коаксиальный дымоход 60/100, защита от замерзания. Гарантия 2 года.',
            'faq' => [
                ['Какой нужен дымоход?', p('Коаксиальный дымоход 60/100 мм: продукты сгорания выводит встроенный вентилятор. Дымоходная труба входит в комплект поставки.')],
                ['Нужна ли розетка?', p('Да. Серия Т питается от сети 220 В, 50 Гц: от сети работают розжиг, вентилятор и защита от замерзания. Батарейки этой серии не нужны.')],
                ['Сколько горячей воды даёт колонка?', p('При нагреве на 25 °C — 11 л/мин у ВПГ-22Т и 13 л/мин у ВПГ-26Т. При нагреве на 30 °C — 9,2 и 10,8 л/мин.')],
                ['Какая гарантия на колонку?', p('2 года со дня продажи. Гарантийный ремонт выполняет ООО «ГазКотелБел» или его официальные представители.')],
            ],
            'ru' => [
                'title' => 'GKB ВПГ-22Т / 26Т',
                'subtitle' => 'Турбированный проточный газовый водонагреватель, 22–26 кВт',
                'description_catalog' => 'Турбированная колонка 22–26 кВт, дымоход 60/100',
                'description' => p('GKB ВПГ-22Т / 26Т — турбированные колонки с принудительным отводом продуктов сгорания: вентилятор выводит их через коаксиальную трубу 60/100 мм, она есть в комплекте. Две модели на 22 и 26 кВт дают до 11 и 13 л/мин горячей воды при нагреве на 25 °C. Питание — от сети 220 В, есть защита от замерзания, управление — с сенсорной панели с круглым дисплеем.'),
                'features_and_benefits' => p('Серия Т работает на природном газе, тип устройства — C12 (закрытая камера сгорания, коаксиальный дымоход 60/100).')
                    . ul(array_merge([
                        '<strong>Принудительный отвод газов.</strong> Встроенный вентилятор выводит продукты сгорания через коаксиальную трубу 60/100 мм; труба входит в комплект.',
                        '<strong>Питание от сети 220 В.</strong> Батарейки не нужны.',
                        '<strong>Работа при низком давлении воды.</strong> Колонка работает при давлении всего 0,015 МПа.',
                        '<strong>Защита от замерзания.</strong> Когда температура вокруг опускается ниже +5 °C, включается подогрев, чтобы теплообменник не замёрз и не треснул.',
                        '<strong>Защита от протечек и защита заземления.</strong>',
                    ], vpgCommonFeatures(false)))
                    . brandNote(false),
                'specifications' => vpgSpecs($tur, false),
                'equipment' => vpgEquipment(true, false),
            ],
            'en' => [
                'title' => 'GKB VPG-22T / 26T',
                'subtitle' => 'Fan-assisted instantaneous gas water heater, 22–26 kW',
                'description_catalog' => 'Fan-assisted 22–26 kW water heater, 60/100 flue',
                'description' => p('GKB VPG-22T / 26T are fan-assisted heaters with forced flue gas extraction: a fan removes combustion products through a 60/100 mm coaxial pipe, supplied with the heater. Two models rated 22 and 26 kW deliver up to 11 and 13 l/min of hot water at a 25 °C rise. Powered from 220 V mains, with frost protection and a touch panel with a round display.'),
                'features_and_benefits' => p('The T series runs on natural gas; appliance type C12 (sealed combustion chamber, 60/100 coaxial flue).')
                    . ul(array_merge([
                        '<strong>Forced flue gas extraction.</strong> A built-in fan removes combustion products through a 60/100 mm coaxial pipe, included in the package.',
                        '<strong>220 V mains power.</strong> No batteries needed.',
                        '<strong>Low water pressure operation.</strong> Works at a water pressure of only 0.015 MPa.',
                        '<strong>Frost protection.</strong> When the ambient temperature drops below +5 °C, heating switches on so that the heat exchanger does not freeze and crack.',
                        '<strong>Leakage and earthing protection.</strong>',
                    ], vpgCommonFeatures(true)))
                    . brandNote(true),
                'specifications' => vpgSpecs($tur, true),
                'equipment' => vpgEquipment(true, true),
            ],
        ],

        'gkb-sn-500' => [
            'term' => 'voltage-stabilizers',
            'post_title' => 'GKB SN-500',
            'images' => ['gkb-sn-1.jpg', 'gkb-sn-scheme.jpg'],
            'seo_description' => 'Релейный стабилизатор напряжения GKB SN-500: 500 ВА / 300 Вт, вход 100–270 В, выход 220 В ±8 %. Защита котла и бытовых приборов от перепадов напряжения.',
            'ru' => [
                'title' => 'GKB SN-500',
                'subtitle' => 'Релейный стабилизатор напряжения, 500 ВА / 300 Вт',
                'description_catalog' => 'Стабилизатор 500 ВА / 300 Вт для котла',
                'description' => p('GKB SN-500 — релейный стабилизатор напряжения на 500 ВА (до 300 Вт нагрузки) для однофазной сети 220 В, 50 Гц. При входном напряжении от 100 до 270 В он выдаёт 220 В ±8 % и защищает автоматику газового котла, насос и другие приборы от перепадов, пониженного и повышенного напряжения и перегрева.'),
                'features_and_benefits' => snFeatures(false),
                'specifications' => snSpecs('500', '300', false),
            ],
            'en' => [
                'title' => 'GKB SN-500',
                'subtitle' => 'Relay voltage stabilizer, 500 VA / 300 W',
                'description_catalog' => '500 VA / 300 W stabilizer for boilers',
                'description' => p('GKB SN-500 is a 500 VA (up to 300 W load) relay voltage stabilizer for a single-phase 220 V, 50 Hz network. With an input voltage of 100 to 270 V it delivers 220 V ±8 % and protects boiler controls, pumps and other appliances from fluctuations, under- and overvoltage and overheating.'),
                'features_and_benefits' => snFeatures(true),
                'specifications' => snSpecs('500', '300', true),
            ],
        ],

        'gkb-sn-1000' => [
            'term' => 'voltage-stabilizers',
            'post_title' => 'GKB SN-1000',
            'images' => ['gkb-sn-1.jpg', 'gkb-sn-scheme.jpg'],
            'seo_description' => 'Релейный стабилизатор напряжения GKB SN-1000: 1000 ВА / 600 Вт, вход 100–270 В, выход 220 В ±8 %. Для котла с насосами и бытовой техники 220 В.',
            'ru' => [
                'title' => 'GKB SN-1000',
                'subtitle' => 'Релейный стабилизатор напряжения, 1000 ВА / 600 Вт',
                'description_catalog' => 'Стабилизатор 1000 ВА / 600 Вт для котла',
                'description' => p('GKB SN-1000 — релейный стабилизатор напряжения на 1000 ВА (до 600 Вт нагрузки) для однофазной сети 220 В, 50 Гц. При входном напряжении от 100 до 270 В он выдаёт 220 В ±8 % и защищает газовый котёл с насосами, автоматику и бытовые приборы от перепадов, пониженного и повышенного напряжения и перегрева.'),
                'features_and_benefits' => snFeatures(false),
                'specifications' => snSpecs('1000', '600', false),
            ],
            'en' => [
                'title' => 'GKB SN-1000',
                'subtitle' => 'Relay voltage stabilizer, 1000 VA / 600 W',
                'description_catalog' => '1000 VA / 600 W stabilizer for boilers',
                'description' => p('GKB SN-1000 is a 1000 VA (up to 600 W load) relay voltage stabilizer for a single-phase 220 V, 50 Hz network. With an input voltage of 100 to 270 V it delivers 220 V ±8 % and protects a gas boiler with pumps, controls and household appliances from fluctuations, under- and overvoltage and overheating.'),
                'features_and_benefits' => snFeatures(true),
                'specifications' => snSpecs('1000', '600', true),
            ],
        ],

        'gkb-co999' => [
            'term' => 'co-detectors',
            'post_title' => 'GKB CO999',
            'images' => ['gkb-co999-1.jpg'],
            'seo_description' => 'Датчик угарного газа GKB CO999 на батарейках 3×AA: замер раз в минуту, тревога при 50–300 ppm CO. Для домов и квартир с газовыми приборами. Гарантия 1 год.',
            'ru' => [
                'title' => 'GKB CO999',
                'subtitle' => 'Датчик угарного газа',
                'description_catalog' => 'Обнаруживает угарный газ, батарейки 3×AA',
                'description' => p('GKB CO999 — датчик (сигнализатор) угарного газа на батарейках. Он раз в минуту измеряет концентрацию CO от любого источника горения и подаёт сигнал тревоги: при 300 ppm — в течение 3 минут. Датчик обнаруживает <strong>только угарный газ</strong> и не предназначен для обнаружения других газов, в том числе природного.'),
                'features_and_benefits' => p('Угарный газ не имеет цвета и запаха, поэтому без датчика его не заметить. CO999 следит за воздухом рядом с газовой колонкой, котлом, печью или камином.')
                    . ul(['Питание — 3 батарейки AA по 1,5 В, батарейки идут в комплекте; провода и розетка не нужны.', 'Замер концентрации CO раз в минуту.', 'Время срабатывания зависит от концентрации: до 50 ppm — за 60–90 минут, до 100 ppm — за 10–40 минут, до 300 ppm — за 3 минуты.', 'Малое потребление в режиме ожидания — батарейки служат долго.'])
                    . coBrand(false),
                'specifications' => table(null, array_merge([
                    ['Что обнаруживает', 'только угарный газ (CO)'],
                    ['Питание', '3 батарейки AA, 1,5 В (в комплекте)'],
                    ['Частота замеров', '1 раз в минуту'],
                    ['Срабатывание при концентрации CO до 50 ppm', 'в течение 60–90 мин'],
                    ['Срабатывание при концентрации CO до 100 ppm', 'в течение 10–40 мин'],
                    ['Срабатывание при концентрации CO до 300 ppm', 'в течение 3 мин'],
                    ['Ток в режиме ожидания', '< 50 мкА (без ЖК-дисплея), < 200 мкА (с ЖК-дисплеем)'],
                    ['Ток в режиме тревоги', '< 75 мА'],
                    ['Температура окружающей среды', '+5…+40 °C'],
                    ['Относительная влажность', '20–90 %'],
                ], coWarranty(false))),
            ],
            'en' => [
                'title' => 'GKB CO999',
                'subtitle' => 'Carbon monoxide detector',
                'description_catalog' => 'Detects carbon monoxide, 3×AA batteries',
                'description' => p('GKB CO999 is a battery-powered carbon monoxide detector (alarm). It measures the CO concentration from any combustion source once a minute and sounds an alarm: at 300 ppm — within 3 minutes. The detector senses <strong>carbon monoxide only</strong> and is not designed to detect any other gas, including natural gas.'),
                'features_and_benefits' => p('Carbon monoxide is colourless and odourless, so it cannot be noticed without a detector. CO999 monitors the air near a gas water heater, boiler, stove or fireplace.')
                    . ul(['Powered by 3 AA batteries, 1.5 V, included; no wires or socket needed.', 'CO concentration measured once a minute.', 'Response time depends on concentration: up to 50 ppm — within 60–90 minutes, up to 100 ppm — within 10–40 minutes, up to 300 ppm — within 3 minutes.', 'Low standby consumption — batteries last a long time.'])
                    . coBrand(true),
                'specifications' => table(null, array_merge([
                    ['Detects', 'carbon monoxide (CO) only'],
                    ['Power supply', '3 AA batteries, 1.5 V (included)'],
                    ['Measurement interval', 'once a minute'],
                    ['Alarm at CO up to 50 ppm', 'within 60–90 min'],
                    ['Alarm at CO up to 100 ppm', 'within 10–40 min'],
                    ['Alarm at CO up to 300 ppm', 'within 3 min'],
                    ['Standby current', '< 50 µA (without LCD), < 200 µA (with LCD)'],
                    ['Alarm current', '< 75 mA'],
                    ['Ambient temperature', '+5…+40 °C'],
                    ['Relative humidity', '20–90 %'],
                ], coWarranty(true))),
            ],
        ],

        'gkb-co888' => [
            'term' => 'co-detectors',
            'post_title' => 'GKB CO888',
            'images' => ['gkb-co888-1.jpg', 'gkb-co888-2.jpg'],
            'seo_description' => 'Сигнализатор природного и угарного газа GKB CO888 от розетки: тревога при 10 % НКПР метана и 150 ppm CO, время отклика до 30 секунд. Гарантия 1 год.',
            'ru' => [
                'title' => 'GKB CO888',
                'subtitle' => 'Сигнализатор природного и угарного газа',
                'description_catalog' => 'Обнаруживает природный и угарный газ, от розетки',
                'description' => p('GKB CO888 — сигнализатор загазованности, который обнаруживает и <strong>природный газ (метан, CH₄)</strong>, и <strong>угарный газ (CO)</strong> от любого источника горения. Он включается прямо в розетку и срабатывает менее чем за 30 секунд: при утечке метана 10 % от нижнего концентрационного предела распространения пламени (НКПР) или при 150 ppm угарного газа.'),
                'features_and_benefits' => p('CO888 предупреждает сразу о двух опасностях в доме с газовыми приборами: об утечке природного газа и об угарном газе от неполного сгорания.')
                    . ul(['Два датчика в одном корпусе: метан и угарный газ.', 'Питание от сети AC 100–250 В, 50–60 Гц — включается в розетку, батарейки не нужны.', 'Порог тревоги: метан — 10 % НКПР, угарный газ — 150 ppm.', 'Время отклика — менее 30 секунд.', 'Работает при температуре от −10 до +55 °C.'])
                    . coBrand(false),
                'specifications' => table(null, array_merge([
                    ['Что обнаруживает', 'природный газ (метан, CH₄) и угарный газ (CO)'],
                    ['Источник питания', 'AC 100–250 В, 50–60 Гц (от розетки)'],
                    ['Диапазон обнаружения метана', '0–20 % НКПР'],
                    ['Диапазон обнаружения угарного газа', '0–500 ppm'],
                    ['Порог тревоги по метану', '10 % НКПР'],
                    ['Порог тревоги по угарному газу', '150 ppm'],
                    ['Время отклика', '< 30 с'],
                    ['Температура эксплуатации', '−10…+55 °C'],
                    ['Влажность', '≤ 95 %, без конденсации'],
                ], coWarranty(false))),
            ],
            'en' => [
                'title' => 'GKB CO888',
                'subtitle' => 'Natural gas and carbon monoxide alarm',
                'description_catalog' => 'Detects natural gas and CO, mains powered',
                'description' => p('GKB CO888 is a gas alarm that detects both <strong>natural gas (methane, CH₄)</strong> and <strong>carbon monoxide (CO)</strong> from any combustion source. It plugs straight into a socket and responds in less than 30 seconds: at a methane level of 10 % LEL or at 150 ppm of carbon monoxide.'),
                'features_and_benefits' => p('CO888 warns of two hazards in a home with gas appliances at once: a natural gas leak and carbon monoxide from incomplete combustion.')
                    . ul(['Two sensors in one housing: methane and carbon monoxide.', 'Mains powered, AC 100–250 V, 50–60 Hz — plugs into a socket, no batteries needed.', 'Alarm threshold: methane — 10 % LEL, carbon monoxide — 150 ppm.', 'Response time — less than 30 seconds.', 'Operates at −10 to +55 °C.'])
                    . coBrand(true),
                'specifications' => table(null, array_merge([
                    ['Detects', 'natural gas (methane, CH₄) and carbon monoxide (CO)'],
                    ['Power supply', 'AC 100–250 V, 50–60 Hz (mains socket)'],
                    ['Methane detection range', '0–20 % LEL'],
                    ['Carbon monoxide detection range', '0–500 ppm'],
                    ['Methane alarm threshold', '10 % LEL'],
                    ['Carbon monoxide alarm threshold', '150 ppm'],
                    ['Response time', '< 30 s'],
                    ['Operating temperature', '−10…+55 °C'],
                    ['Humidity', '≤ 95 %, non-condensing'],
                ], coWarranty(true))),
            ],
        ],

        'gkb-co777' => [
            'term' => 'co-detectors',
            'post_title' => 'GKB CO777',
            'images' => ['gkb-co777-1.jpg'],
            'seo_description' => 'Датчик дыма и угарного газа GKB CO777: фотоэлектрический датчик дыма и электрохимический датчик CO, сирена от 85 дБ, батарейки 3×AA в комплекте. Гарантия 1 год.',
            'ru' => [
                'title' => 'GKB CO777',
                'subtitle' => 'Датчик дыма и угарного газа',
                'description_catalog' => 'Обнаруживает дым и угарный газ, батарейки 3×AA',
                'description' => p('GKB CO777 — комбинированный датчик, который обнаруживает <strong>дым</strong> и <strong>угарный газ (CO)</strong> от любого источника горения. В нём два чувствительных элемента: фотоэлектрический датчик дыма и электрохимический датчик CO. Сирена громкостью не менее 85 дБ, питание — от трёх батареек AA, они идут в комплекте.'),
                'features_and_benefits' => p('CO777 защищает сразу от двух угроз: пожара и отравления угарным газом.')
                    . ul(['Фотоэлектрический датчик дыма и электрохимический датчик угарного газа в одном корпусе.', 'Звуковой сигнал тревоги — не менее 85 дБ.', 'Срабатывание по угарному газу: 70 ppm — за 60–180 минут, 150 ppm — за 10–50 минут, 400 ppm — за 4–15 минут, 700 ppm — за 2–6 минут.', 'Питание — 3 батарейки AA по 1,5 В, в комплекте.', 'Компактный круглый корпус Ø 107 × 38 мм.', 'Соответствует критериям UL217 и BS5446.'])
                    . coBrand(false),
                'specifications' => table(null, array_merge([
                    ['Что обнаруживает', 'дым и угарный газ (CO)'],
                    ['Тип датчика', 'фотоэлектрический датчик дыма и электрохимический датчик CO'],
                    ['Питание', '3 батарейки AA, 1,5 В (в комплекте)'],
                    ['Звуковой сигнал тревоги', '≥ 85 дБ'],
                    ['Срабатывание по CO при 70 ppm', 'в течение 60–180 мин'],
                    ['Срабатывание по CO при 150 ppm', 'в течение 10–50 мин'],
                    ['Срабатывание по CO при 400 ppm', 'в течение 4–15 мин'],
                    ['Срабатывание по CO при 700 ppm', 'в течение 2–6 мин'],
                    ['Ток в режиме ожидания', '≤ 98 мкА (без ЖК-дисплея)'],
                    ['Ток в режиме тревоги', '≤ 75 мА'],
                    ['Температура окружающей среды', '0…+49 °C'],
                    ['Относительная влажность', '≤ 90 %, без обледенения и конденсата'],
                    ['Размеры', 'Ø 107 × 38 мм'],
                    ['Исполнительные критерии', 'UL217, BS5446'],
                ], coWarranty(false))),
            ],
            'en' => [
                'title' => 'GKB CO777',
                'subtitle' => 'Smoke and carbon monoxide detector',
                'description_catalog' => 'Detects smoke and CO, 3×AA batteries',
                'description' => p('GKB CO777 is a combined detector that senses <strong>smoke</strong> and <strong>carbon monoxide (CO)</strong> from any combustion source. It has two sensing elements: a photoelectric smoke sensor and an electrochemical CO sensor. The siren is at least 85 dB; power comes from three AA batteries, included.'),
                'features_and_benefits' => p('CO777 protects against two threats at once: fire and carbon monoxide poisoning.')
                    . ul(['Photoelectric smoke sensor and electrochemical CO sensor in one housing.', 'Alarm sound — at least 85 dB.', 'CO response: 70 ppm — within 60–180 minutes, 150 ppm — within 10–50 minutes, 400 ppm — within 4–15 minutes, 700 ppm — within 2–6 minutes.', 'Powered by 3 AA batteries, 1.5 V, included.', 'Compact round housing, Ø 107 × 38 mm.', 'Meets UL217 and BS5446 criteria.'])
                    . coBrand(true),
                'specifications' => table(null, array_merge([
                    ['Detects', 'smoke and carbon monoxide (CO)'],
                    ['Sensor type', 'photoelectric smoke sensor and electrochemical CO sensor'],
                    ['Power supply', '3 AA batteries, 1.5 V (included)'],
                    ['Alarm sound level', '≥ 85 dB'],
                    ['CO alarm at 70 ppm', 'within 60–180 min'],
                    ['CO alarm at 150 ppm', 'within 10–50 min'],
                    ['CO alarm at 400 ppm', 'within 4–15 min'],
                    ['CO alarm at 700 ppm', 'within 2–6 min'],
                    ['Standby current', '≤ 98 µA (without LCD)'],
                    ['Alarm current', '≤ 75 mA'],
                    ['Ambient temperature', '0…+49 °C'],
                    ['Relative humidity', '≤ 90 %, no icing or condensation'],
                    ['Dimensions', 'Ø 107 × 38 mm'],
                    ['Performance criteria', 'UL217, BS5446'],
                ], coWarranty(true))),
            ],
        ],
    ];
}
