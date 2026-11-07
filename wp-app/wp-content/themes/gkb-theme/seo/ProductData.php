<?php

namespace seo;

use WP_Post;

/**
 * Модификации товара (мощность, площадь, ГВС, цена) из поля «Модификации и
 * цены» и выведенная из них сводка. Одна точка расчёта для title,
 * description, JSON-LD, карточек, llms.txt и калькулятора.
 */
class ProductData
{
    /**
     * Цены на сайте публикуются только в прайс-листе (отдельная страница),
     * поэтому цены модификаций нигде не выводятся: ни в карточках, ни в
     * title, ни в разметке. Включить — true.
     */
    public const SHOW_PRICES = false;

    private const TRANSIENT = 'gkb_product_summary_v2_';

    public static function register(): void
    {
        add_action('acf/save_post', [self::class, 'flush'], 20);
        add_action('save_post_products', [self::class, 'flush'], 20);
    }

    public static function flush($postId): void
    {
        if (is_numeric($postId)) {
            delete_transient(self::TRANSIENT . (int) $postId);
        }
        if ($postId === 'options') {
            foreach (get_posts(['post_type' => 'products', 'fields' => 'ids', 'posts_per_page' => -1, 'post_status' => 'any']) as $id) {
                delete_transient(self::TRANSIENT . $id);
            }
        }
    }

    /** Строки модификаций, отсортированные по мощности. */
    public static function rows(int $postId): array
    {
        if (!function_exists('get_field')) {
            return [];
        }
        $rows = [];
        foreach ((array) get_field('modifications', $postId) as $row) {
            if (!is_array($row) || trim((string) ($row['code'] ?? '')) === '') {
                continue;
            }
            $rows[] = [
                'code' => trim((string) $row['code']),
                'power_kw' => self::num($row['power_kw'] ?? null),
                'area_m2' => self::num($row['area_m2'] ?? null),
                'dhw' => !empty($row['dhw']),
                'price_byn' => self::SHOW_PRICES ? self::num($row['price_byn'] ?? null) : null,
            ];
        }
        usort($rows, static fn ($a, $b) => ($a['power_kw'] ?? 0) <=> ($b['power_kw'] ?? 0));
        return $rows;
    }

    /**
     * Сводка: min_price, max_price, price_count, power_min, power_max,
     * count, rows. Цены и мощности — null, если не заданы.
     */
    public static function summary(int $postId): array
    {
        $cached = get_transient(self::TRANSIENT . $postId);
        if (is_array($cached)) {
            return $cached;
        }

        $rows = self::rows($postId);
        $prices = array_values(array_filter(array_column($rows, 'price_byn'), static fn ($v) => $v !== null && $v > 0));
        $powers = array_values(array_filter(array_column($rows, 'power_kw'), static fn ($v) => $v !== null && $v > 0));

        $summary = [
            'rows' => $rows,
            'count' => count($rows),
            'min_price' => $prices ? min($prices) : null,
            'max_price' => $prices ? max($prices) : null,
            'price_count' => count($prices),
            'power_min' => $powers ? min($powers) : null,
            'power_max' => $powers ? max($powers) : null,
        ];

        set_transient(self::TRANSIENT . $postId, $summary, DAY_IN_SECONDS);
        return $summary;
    }

    /** «1 338» */
    public static function money($value): string
    {
        if ($value === null) {
            return '';
        }
        $decimals = floor($value) == $value ? 0 : 2;
        return number_format((float) $value, $decimals, ',', "\u{00A0}");
    }

    /** «7–45 кВт» или «20 кВт», пустая строка без данных. */
    public static function powerRange(array $summary, bool $withUnit = true): string
    {
        if ($summary['power_min'] === null) {
            return '';
        }
        $min = self::fmt($summary['power_min']);
        $max = self::fmt($summary['power_max']);
        $range = $min === $max ? $min : $min . '–' . $max;
        return $withUnit ? $range . "\u{00A0}кВт" : $range;
    }

    /** Сводка для категории товаров: число моделей, мин. цена и диапазон мощностей. */
    public static function termSummary(\WP_Term $term): array
    {
        $products = get_posts([
            'post_type' => 'products',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => [['taxonomy' => 'product-category', 'field' => 'term_id', 'terms' => $term->term_id]],
        ]);
        $min = null;
        $pMin = null;
        $pMax = null;
        foreach ($products as $p) {
            $s = self::summary($p->ID);
            if ($s['min_price'] !== null) {
                $min = $min === null ? $s['min_price'] : min($min, $s['min_price']);
            }
            if ($s['power_min'] !== null) {
                $pMin = $pMin === null ? $s['power_min'] : min($pMin, $s['power_min']);
                $pMax = $pMax === null ? $s['power_max'] : max($pMax, $s['power_max']);
            }
        }
        return [
            'products' => $products,
            'count' => count($products),
            'min_price' => $min,
            'power_min' => $pMin,
            'power_max' => $pMax,
        ];
    }

    /**
     * Модели с модификацией не меньше требуемой мощности: для каждой — ближайшая
     * подходящая модификация. Отсортированы по превышению мощности.
     */
    public static function matchPower(float $kw, bool $dhw = false): array
    {
        $out = [];
        foreach (get_posts(['post_type' => 'products', 'post_status' => 'publish', 'posts_per_page' => -1]) as $p) {
            // калькулятор подбирает котлы: конвекторы и колонки не в счёт
            $cat = Context::productCategory($p);
            if ($cat && in_array($cat->slug, ['gas-convectors', 'geysers'], true)) {
                continue;
            }
            $best = null;
            foreach (self::summary($p->ID)['rows'] as $row) {
                if ($row['power_kw'] === null || $row['power_kw'] < $kw) {
                    continue;
                }
                if ($dhw && !$row['dhw']) {
                    continue;
                }
                if ($best === null || $row['power_kw'] < $best['power_kw']) {
                    $best = $row;
                }
            }
            if ($best) {
                $out[] = ['post' => $p, 'row' => $best, 'excess' => $best['power_kw'] - $kw];
            }
        }
        usort($out, static fn ($a, $b) => $a['excess'] <=> $b['excess']);
        return $out;
    }

    private static function fmt($v): string
    {
        return rtrim(rtrim(number_format((float) $v, 1, ',', ''), '0'), ',');
    }

    private static function num($v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        $v = str_replace([' ', "\u{00A0}", ','], ['', '', '.'], (string) $v);
        return is_numeric($v) ? (float) $v : null;
    }
}
