<?php

namespace seo;

use helpers\Helpers;

/**
 * Одноразовая пересборка уменьшенных копий фото товаров и баннеров главной:
 * появляются размеры gkb-card (400/800), gkb-gallery (720/1200), gkb-banner
 * (768/1280/1920), и srcset начинает отдавать телефону картинку по размеру.
 * Оригиналы не меняются. Партиями по 5, чтобы не упереться в таймаут.
 * Инструменты → «Миниатюры товаров». После запуска файл удаляется из темы.
 */
class ThumbsRegen
{
    private const BATCH = 5;
    private const OPTION = 'gkb_thumbs_regen_done';

    public static function register(): void
    {
        add_action('admin_menu', static function () {
            add_management_page('Миниатюры товаров', 'Миниатюры товаров', 'manage_options', 'gkb-thumbs', [self::class, 'page']);
        });
    }

    /** ID вложений: галереи всех товаров на обоих языках и слайды баннера главной. */
    private static function ids(): array
    {
        $ids = [];
        foreach (get_posts(['post_type' => 'products', 'post_status' => 'publish', 'posts_per_page' => -1]) as $p) {
            foreach (parse_blocks($p->post_content) as $block) {
                if (($block['blockName'] ?? '') !== 'acf/product') {
                    continue;
                }
                foreach ((array) ($block['attrs']['data'] ?? []) as $key => $value) {
                    if (preg_match('/^(ru|en)_language_image_slider_\d+_image$/', (string) $key) && (int) $value > 0) {
                        $ids[] = (int) $value;
                    }
                }
            }
        }
        $front = get_post((int) get_option('page_on_front'));
        if ($front) {
            foreach (parse_blocks($front->post_content) as $block) {
                foreach ((array) ($block['attrs']['data'] ?? []) as $key => $value) {
                    if (preg_match('/_slider_\d+_image$/', (string) $key) && (int) $value > 0) {
                        $ids[] = (int) $value;
                    }
                }
            }
        }
        $ids = array_values(array_unique(array_filter($ids, static fn ($id) => wp_attachment_is_image($id))));
        sort($ids);
        return $ids;
    }

    public static function page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $all = self::ids();
        $done = array_map('intval', (array) get_option(self::OPTION, []));
        $log = [];

        if (isset($_POST['gkb_regen']) && check_admin_referer('gkb_thumbs')) {
            @set_time_limit(120);
            $todo = array_slice(array_values(array_diff($all, $done)), 0, self::BATCH);
            foreach ($todo as $id) {
                $file = get_attached_file($id);
                if (!$file || !file_exists($file)) {
                    $log[] = "#$id: файла нет, пропущено";
                    $done[] = $id;
                    continue;
                }
                $meta = wp_generate_attachment_metadata($id, $file);
                if (is_wp_error($meta) || empty($meta)) {
                    $log[] = "#$id: ошибка";
                    $done[] = $id;
                    continue;
                }
                wp_update_attachment_metadata($id, $meta);
                $log[] = '#' . $id . ' ' . basename($file) . ': ' . count($meta['sizes'] ?? []) . ' размеров';
                $done[] = $id;
            }
            update_option(self::OPTION, array_values(array_unique($done)), false);
        }

        $left = count(array_diff($all, $done));
        echo '<div class="wrap"><h1>Миниатюры товаров и баннеров</h1>';
        if ($log) {
            echo '<div class="notice notice-success"><p>' . esc_html(implode('; ', $log)) . '</p></div>';
        }
        echo '<p>Всего изображений: ' . count($all) . '. Осталось обработать: <strong>' . $left . '</strong>. За один раз — ' . self::BATCH . '.</p>';
        if ($left > 0) {
            echo '<form method="post">';
            wp_nonce_field('gkb_thumbs');
            echo '<button class="button button-primary" name="gkb_regen" value="1">Обработать следующие ' . min(self::BATCH, $left) . '</button></form>';
        } else {
            echo '<p><strong>Готово.</strong></p>';
        }
        echo '</div>';
    }
}
