<?php

namespace seo;

/**
 * Калькулятор мощности котла. Расчёт на сервере по GET-параметрам: работает
 * без JS, даёт ссылку на результат, ту же страницу запрашивает JS и агент.
 *
 * P = S × h / 2,7 × q × 1,15; q — 0,08 / 0,10 / 0,13 кВт/м²;
 * с ГВС +20 %, но не меньше +3 кВт.
 */
class Calculator
{
    public const INSULATION = [
        'good' => [0.08, 'хорошая (утеплённые стены, стеклопакеты)'],
        'medium' => [0.10, 'средняя'],
        'poor' => [0.13, 'слабая (старый дом без утепления)'],
    ];
    public const RESERVE = 1.15;
    public const BASE_HEIGHT = 2.7;

    /** Разбор и проверка ввода: [values, errors]. Пустой ввод — [null, []]. */
    public static function input(array $q): array
    {
        if (!isset($q['area']) || $q['area'] === '') {
            return [null, []];
        }
        $num = static fn ($v) => is_numeric(str_replace(',', '.', (string) $v)) ? (float) str_replace(',', '.', (string) $v) : null;
        $area = $num($q['area']);
        $height = $num($q['height'] ?? self::BASE_HEIGHT);
        $ins = (string) ($q['insulation'] ?? 'medium');
        $dhw = !empty($q['dhw']) && $q['dhw'] !== '0';

        $errors = [];
        if ($area === null || $area < 10 || $area > 1000) {
            $errors['area'] = 'Площадь — от 10 до 1000 м²';
        }
        if ($height === null || $height < 2.2 || $height > 4) {
            $errors['height'] = 'Высота потолков — от 2,2 до 4 м';
        }
        if (!isset(self::INSULATION[$ins])) {
            $errors['insulation'] = 'Выберите утеплённость дома';
        }
        return [['area' => $area, 'height' => $height, 'insulation' => $ins, 'dhw' => $dhw], $errors];
    }

    /** Рекомендуемая мощность, кВт (с запасом и ГВС), округлённая до 0,5. */
    public static function power(array $v): float
    {
        $q = self::INSULATION[$v['insulation']][0];
        $p = $v['area'] * $v['height'] / self::BASE_HEIGHT * $q * self::RESERVE;
        if ($v['dhw']) {
            $p += max($p * 0.2, 3);
        }
        return ceil($p * 2) / 2;
    }

    /** Результат: мощность и модели с подходящей модификацией. */
    public static function result(array $v): array
    {
        $kw = self::power($v);
        $matches = ProductData::matchPower($kw, $v['dhw']);
        if (!$matches && $v['dhw']) {
            $matches = ProductData::matchPower($kw, false);
        }
        return ['kw' => $kw, 'models' => array_slice($matches, 0, 6)];
    }

    public static function fmt(float $v): string
    {
        return rtrim(rtrim(number_format($v, 1, ',', ''), '0'), ',');
    }
}
