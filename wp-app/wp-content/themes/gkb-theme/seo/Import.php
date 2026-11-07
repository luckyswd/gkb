<?php

namespace seo;

/**
 * Одноразовый перенос модификаций из прайс-листа (цены с 09.06.2025) в поле
 * «Модификации и цены» товаров. Мощность и отапливаемая площадь — из таблиц
 * характеристик товаров (паспортные значения); у конвекторов и колонок
 * площадь в паспорте не указана и не заполняется.
 *
 * Пишет только в пустые поля: заполненные вручную модификации не трогает.
 * Инструменты → «Импорт модификаций».
 */
class Import
{
    public static function data(): array
    {
        return [
            'zhytomyr-3' => [
                ['code' => 'КС-Г-007 СН', 'power_kw' => 7, 'area_m2' => 70, 'dhw' => false, 'price_byn' => 1338],
                ['code' => 'КС-Г-010 СН', 'power_kw' => 10, 'area_m2' => 100, 'dhw' => false, 'price_byn' => 1608],
                ['code' => 'КС-Г-012 СН', 'power_kw' => 12.5, 'area_m2' => 125, 'dhw' => false, 'price_byn' => 1668],
                ['code' => 'КС-Г-015 СН', 'power_kw' => 16, 'area_m2' => 160, 'dhw' => false, 'price_byn' => 1818],
                ['code' => 'КС-Г-020 СН', 'power_kw' => 22.5, 'area_m2' => 220, 'dhw' => false, 'price_byn' => 2026],
                ['code' => 'КС-Г-025 СН', 'power_kw' => 26, 'area_m2' => 260, 'dhw' => false, 'price_byn' => 2370],
                ['code' => 'КС-Г-030 СН', 'power_kw' => 31.5, 'area_m2' => 320, 'dhw' => false, 'price_byn' => 2712],
                ['code' => 'КС-Г-045 СН', 'power_kw' => 40, 'area_m2' => 400, 'dhw' => false, 'price_byn' => 4614],
                ['code' => 'КС-ГВ-007 СН', 'power_kw' => 7, 'area_m2' => 70, 'dhw' => true, 'price_byn' => 1512],
                ['code' => 'КС-ГВ-010 СН', 'power_kw' => 10, 'area_m2' => 100, 'dhw' => true, 'price_byn' => 1722],
                ['code' => 'КС-ГВ-012 СН', 'power_kw' => 12.5, 'area_m2' => 125, 'dhw' => true, 'price_byn' => 1824],
                ['code' => 'КС-ГВ-015 СН', 'power_kw' => 16, 'area_m2' => 160, 'dhw' => true, 'price_byn' => 1986],
                ['code' => 'КС-ГВ-020 СН', 'power_kw' => 22.5, 'area_m2' => 220, 'dhw' => true, 'price_byn' => 2130],
                ['code' => 'КС-ГВ-025 СН', 'power_kw' => 26, 'area_m2' => 260, 'dhw' => true, 'price_byn' => 2652],
                ['code' => 'КС-ГВ-030 СН', 'power_kw' => 31.5, 'area_m2' => 320, 'dhw' => true, 'price_byn' => 2874],
            ],
            'zhytomyr-10' => [
                ['code' => 'КС-Г-007 СН', 'power_kw' => 7, 'area_m2' => 70, 'dhw' => true, 'price_byn' => 2232],
                ['code' => 'КС-Г-010 СН', 'power_kw' => 10, 'area_m2' => 100, 'dhw' => true, 'price_byn' => 2394],
                ['code' => 'КС-Г-012 СН', 'power_kw' => 12.5, 'area_m2' => 125, 'dhw' => true, 'price_byn' => 2472],
                ['code' => 'КС-Г-015 СН', 'power_kw' => 16, 'area_m2' => 160, 'dhw' => true, 'price_byn' => 2592],
                ['code' => 'КС-Г-020 СН', 'power_kw' => 22.5, 'area_m2' => 220, 'dhw' => true, 'price_byn' => 3138],
                ['code' => 'КС-Г-025 СН', 'power_kw' => 26, 'area_m2' => 260, 'dhw' => true, 'price_byn' => 3708],
                ['code' => 'КС-Г-030 СН', 'power_kw' => 31.5, 'area_m2' => 320, 'dhw' => true, 'price_byn' => 4410],
            ],
            'zhytomyr-m' => [
                ['code' => 'АОГВ-7 СН', 'power_kw' => 7, 'area_m2' => 70, 'dhw' => false, 'price_byn' => 1374],
                ['code' => 'АДГВ-7 СН', 'power_kw' => 7, 'area_m2' => 70, 'dhw' => true, 'price_byn' => 1656],
                ['code' => 'АОГВ-10 СН', 'power_kw' => 10, 'area_m2' => 100, 'dhw' => false, 'price_byn' => 1590],
                ['code' => 'АДГВ-10 СН', 'power_kw' => 10, 'area_m2' => 100, 'dhw' => true, 'price_byn' => 1914],
                ['code' => 'АОГВ-12 СН', 'power_kw' => 12.5, 'area_m2' => 125, 'dhw' => false, 'price_byn' => 1698],
                ['code' => 'АДГВ-12 СН', 'power_kw' => 12.5, 'area_m2' => 125, 'dhw' => true, 'price_byn' => 1932],
                ['code' => 'АОГВ-15 СН', 'power_kw' => 15, 'area_m2' => 150, 'dhw' => false, 'price_byn' => 1824],
                ['code' => 'АДГВ-15 СН', 'power_kw' => 15, 'area_m2' => 150, 'dhw' => true, 'price_byn' => 2040],
            ],
            'zhytomyr-9' => [
                ['code' => 'КС-Г-010СН/АОТВ-10', 'power_kw' => 10, 'area_m2' => 100, 'dhw' => false, 'price_byn' => 3732],
                ['code' => 'КС-Г-012СН/АОТВ-12', 'power_kw' => 12, 'area_m2' => 120, 'dhw' => false, 'price_byn' => 3816],
                ['code' => 'КС-Г-016СН/АОТВ-12', 'power_kw' => 16, 'area_m2' => 160, 'dhw' => false, 'price_byn' => 3990],
                ['code' => 'КС-Г-020СН/АОТВ-15', 'power_kw' => 20, 'area_m2' => 200, 'dhw' => false, 'price_byn' => 4516],
            ],
            'zhytomyr-5' => [
                ['code' => 'КНС-2', 'power_kw' => 2.5, 'area_m2' => null, 'dhw' => false, 'price_byn' => 972],
                ['code' => 'КНС-3', 'power_kw' => 3, 'area_m2' => null, 'dhw' => false, 'price_byn' => 996],
                ['code' => 'КНС-4', 'power_kw' => 4, 'area_m2' => null, 'dhw' => false, 'price_byn' => 1032],
                ['code' => 'КНС-6', 'power_kw' => 6, 'area_m2' => null, 'dhw' => false, 'price_byn' => 1056],
                ['code' => 'КНС-8', 'power_kw' => 8, 'area_m2' => null, 'dhw' => false, 'price_byn' => 1254],
                ['code' => 'КНС-10', 'power_kw' => 10, 'area_m2' => null, 'dhw' => false, 'price_byn' => 1266],
            ],
            'hsv-20' => [
                ['code' => 'ВПГ-20', 'power_kw' => 20, 'area_m2' => null, 'dhw' => true, 'price_byn' => 620],
            ],
            'hsv-20m' => [
                ['code' => 'ВПГ-20М', 'power_kw' => 20, 'area_m2' => null, 'dhw' => true, 'price_byn' => 620],
            ],
            'hsv-20tm' => [
                ['code' => 'ВПГ-20ТМ', 'power_kw' => 20, 'area_m2' => null, 'dhw' => true, 'price_byn' => 860],
            ],
            'vpg-20t' => [
                ['code' => 'ВПГ-20Т', 'power_kw' => 20, 'area_m2' => null, 'dhw' => true, 'price_byn' => 880],
            ],
            'zhytomyr-turbo' => [
                ['code' => 'КС-Г-10СН', 'power_kw' => 10, 'area_m2' => 100, 'dhw' => false, 'price_byn' => 1524],
                ['code' => 'КС-Г-12СН', 'power_kw' => 12, 'area_m2' => 120, 'dhw' => false, 'price_byn' => 1572],
                ['code' => 'КС-Г-16СН', 'power_kw' => 16, 'area_m2' => 160, 'dhw' => false, 'price_byn' => 1716],
                ['code' => 'КС-Г-20СН', 'power_kw' => 20, 'area_m2' => 200, 'dhw' => false, 'price_byn' => 1890],
                ['code' => 'КС-Г-25СН', 'power_kw' => 25, 'area_m2' => 250, 'dhw' => false, 'price_byn' => 2184],
                ['code' => 'КС-Г-30СН', 'power_kw' => 30, 'area_m2' => 300, 'dhw' => false, 'price_byn' => 2574],
            ],
        ];
    }

    public static function register(): void
    {
        add_action('admin_menu', static function () {
            add_management_page('Импорт модификаций', 'Импорт модификаций', 'manage_options', 'gkb-import-mods', [self::class, 'page']);
        });
    }

    public static function page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $done = [];
        if (isset($_POST['gkb_import']) && check_admin_referer('gkb_import_mods')) {
            $done = self::run();
        }
        echo '<div class="wrap"><h1>Импорт модификаций из прайс-листа</h1>';
        if ($done) {
            echo '<div class="notice notice-success"><p>' . esc_html(implode('; ', $done)) . '</p></div>';
        }
        echo '<p>Заполняет поле «Модификации и цены» у товаров, где оно пустое. Уже заполненные товары пропускаются.</p>';
        echo '<table class="widefat striped"><thead><tr><th>Товар</th><th>Строк в прайсе</th><th>Сейчас в поле</th></tr></thead><tbody>';
        foreach (self::data() as $slug => $rows) {
            $post = get_page_by_path($slug, OBJECT, 'products');
            $current = $post ? count(array_filter((array) get_field('modifications', $post->ID))) : 0;
            echo '<tr><td>' . esc_html($post ? get_the_title($post) . ' (' . $slug . ')' : $slug . ' — не найден') . '</td><td>' . count($rows) . '</td><td>' . (int) $current . '</td></tr>';
        }
        echo '</tbody></table><form method="post" style="margin-top:16px">';
        wp_nonce_field('gkb_import_mods');
        echo '<button class="button button-primary" name="gkb_import" value="1">Заполнить пустые</button></form></div>';
    }

    public static function run(): array
    {
        $log = [];
        foreach (self::data() as $slug => $rows) {
            $post = get_page_by_path($slug, OBJECT, 'products');
            if (!$post) {
                $log[] = "$slug: товар не найден";
                continue;
            }
            if (count(array_filter((array) get_field('modifications', $post->ID))) > 0) {
                $log[] = "$slug: уже заполнено, пропущено";
                continue;
            }
            $rows = array_map(static fn ($r) => array_merge($r, ['dhw' => $r['dhw'] ? 1 : 0]), $rows);
            update_field('modifications', $rows, $post->ID);
            ProductData::flush($post->ID);
            $log[] = "$slug: " . count($rows) . ' строк';
        }
        return $log;
    }
}
