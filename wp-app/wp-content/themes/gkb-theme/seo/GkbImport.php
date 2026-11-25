<?php

namespace seo;

use WP_Post;

/**
 * Разовый импорт товаров GKB (openspec/changes/add-gkb-products).
 * Инструменты → «Товары GKB».
 *
 * «Проверить» — ничего не пишет: slug-и и термы свободны, файлы на месте,
 * шаблон блока найден, у каждого поля блока есть ключ ACF.
 * «Применить» — термы, вложения и посты-черновики; созданные ID — в опцию.
 * «Опубликовать» — черновики → «Опубликовано», бренд категории «Газовые колонки» → «ВПГ».
 * «Откатить» — удалить всё созданное по ID из опции и вернуть бренд колонок.
 * После приёмки файл и папка gkb-import/ удаляются из темы.
 */
class GkbImport
{
    private const STATE = 'gkb_import_gkb_products';
    private const SLUG = 'gkb-import-products';
    private const TEMPLATE_SLUG = 'hsv-20'; // АТЕМ ВПГ-20 — образец структуры блока acf/product
    private const GEYSERS = 'geysers';

    public static function register(): void
    {
        add_action('admin_menu', static function () {
            add_management_page('Товары GKB', 'Товары GKB', 'manage_options', self::SLUG, [self::class, 'page']);
        });
    }

    private static function dir(): string
    {
        return __DIR__ . '/gkb-import/';
    }

    private static function data(): array
    {
        require_once self::dir() . 'data.php';
        return ['terms' => GkbImportData\terms(), 'products' => GkbImportData\products()];
    }

    /* ------------------------------------------------------------------ */
    /* Блок acf/product                                                    */
    /* ------------------------------------------------------------------ */

    /** Шаблон: блоки поста-образца и ключи полей (_name → field_…), собранные со всех товаров. */
    private static function template(): array
    {
        $tpl = get_posts(['name' => self::TEMPLATE_SLUG, 'post_type' => 'products', 'post_status' => 'any', 'numberposts' => 1])[0] ?? null;
        $refs = [];
        $tplBlock = null;
        foreach (get_posts(['post_type' => 'products', 'post_status' => 'any', 'posts_per_page' => -1]) as $p) {
            foreach (parse_blocks($p->post_content) as $b) {
                if (($b['blockName'] ?? '') !== 'acf/product') {
                    continue;
                }
                if ($tpl && $p->ID === $tpl->ID) {
                    $tplBlock = $b;
                }
                foreach ((array) ($b['attrs']['data'] ?? []) as $k => $v) {
                    if (is_string($k) && $k !== '' && $k[0] === '_' && is_string($v)) {
                        $refs[self::pattern(substr($k, 1))] ??= $v;
                    }
                }
            }
        }
        return ['post' => $tpl, 'block' => $tplBlock, 'refs' => $refs];
    }

    /** ru_language_image_slider_3_image → ru_language_image_slider_N_image */
    private static function pattern(string $name): string
    {
        return preg_replace('/_\d+_/', '_N_', $name);
    }

    /**
     * Данные блока для товара. $media — имя файла → ID вложения (при проверке 0).
     * Возвращает [data, missingRefs].
     */
    private static function blockData(array $prod, array $tpl, array $media): array
    {
        $tplData = (array) ($tpl['block']['attrs']['data'] ?? []);
        $data = [];
        $missing = [];
        $put = static function (string $name, $value) use (&$data, &$missing, $tpl) {
            $data[$name] = $value;
            $ref = $tpl['refs'][self::pattern($name)] ?? null;
            if ($ref === null) {
                $missing[] = $name;
            } else {
                $data['_' . $name] = $ref;
            }
        };
        foreach (['ru', 'en'] as $lang) {
            $pre = $lang . '_language_';
            $f = $prod[$lang];
            foreach (['title', 'subtitle', 'description', 'description_catalog'] as $k) {
                $put($pre . $k, (string) ($f[$k] ?? ''));
            }
            // подпись кнопки — как у образца
            $put($pre . 'button_name', (string) ($tplData[$pre . 'button_name'] ?? ($lang === 'en' ? 'Where to buy?' : 'Где купить?')));
            $images = array_values($prod['images']);
            $put($pre . 'image_slider', count($images));
            foreach ($images as $i => $file) {
                $put($pre . 'image_slider_' . $i . '_image', (int) ($media[$file] ?? 0));
            }
            $put($pre . 'functionality_icons', 0);
            foreach (['features_and_benefits', 'specifications', 'equipment'] as $k) {
                $put($pre . $k, (string) ($f[$k] ?? ''));
            }
            if (!empty($prod['documentation'])) {
                $put($pre . 'documentation', 1);
                $put($pre . 'documentation_0_name_file', $prod['documentation'][$lang]);
                $put($pre . 'documentation_0_file', (int) ($media[$prod['documentation']['file']] ?? 0));
            } else {
                $put($pre . 'documentation', 0);
            }
            // группы ru_language / en_language
            $group = $lang . '_language';
            if (isset($tplData['_' . $group])) {
                $data[$group] = '';
                $data['_' . $group] = $tplData['_' . $group];
            }
        }
        return [$data, $missing];
    }

    /** post_content нового товара: блоки образца, в которых acf/product заменён новым. */
    private static function content(array $tpl, array $data): string
    {
        $out = [];
        foreach (parse_blocks($tpl['post']->post_content) as $b) {
            if (($b['blockName'] ?? '') === 'acf/product') {
                $attrs = $b['attrs'];
                unset($attrs['id']);
                $attrs['data'] = $data;
                $b = ['blockName' => 'acf/product', 'attrs' => $attrs, 'innerBlocks' => [], 'innerHTML' => '', 'innerContent' => []];
            }
            if ($b['blockName'] === null && trim((string) $b['innerHTML']) === '') {
                continue;
            }
            $out[] = $b;
        }
        return serialize_blocks($out);
    }

    /* ------------------------------------------------------------------ */
    /* Прогон                                                              */
    /* ------------------------------------------------------------------ */

    private static function files(array $d): array
    {
        $files = [];
        foreach ($d['products'] as $prod) {
            foreach ($prod['images'] as $f) {
                $files[$f] = $prod['ru']['title'];
            }
            if (!empty($prod['documentation'])) {
                $files[$prod['documentation']['file']] = '';
            }
        }
        return $files;
    }

    /** $write = false — только проверка. Возвращает [log, ok, written]. */
    public static function run(bool $write): array
    {
        $log = [];
        $d = self::data();
        $tpl = self::template();

        if (get_option(self::STATE)) {
            return [[[false, 'Импорт уже выполнен (есть опция ' . self::STATE . '). Сначала «Откатить».']], false, false];
        }

        $log[] = [(bool) $tpl['post'], 'Образец блока: товар «' . self::TEMPLATE_SLUG . '»' . ($tpl['post'] ? ' найден (ID ' . $tpl['post']->ID . ')' : ' не найден')];
        $log[] = [(bool) $tpl['block'], 'Образец содержит блок acf/product'];
        if ($tpl['block']) {
            $others = array_filter(array_map(static fn ($b) => $b['blockName'], parse_blocks($tpl['post']->post_content)), static fn ($n) => $n && $n !== 'acf/product');
            $log[] = [true, 'Другие блоки образца (переносятся как есть): ' . ($others ? implode(', ', array_unique($others)) : 'нет')];
        }

        foreach ($d['terms'] as $slug => $t) {
            $exists = term_exists($slug, 'product-category');
            $log[] = [!$exists, "Категория {$slug} («{$t['name']}») ещё не существует"];
            $log[] = [count($t['faq']) >= 3, "Категория {$slug}: FAQ — " . count($t['faq']) . ' вопр.'];
        }
        $geysers = get_term_by('slug', self::GEYSERS, 'product-category');
        $log[] = [(bool) $geysers, 'Категория ' . self::GEYSERS . ' найдена'];

        foreach (self::files($d) as $file => $_) {
            $log[] = [is_readable(self::dir() . 'media/' . $file), "Файл media/{$file}"];
        }

        foreach ($d['products'] as $slug => $prod) {
            $taken = get_posts(['name' => $slug, 'post_type' => 'products', 'post_status' => 'any', 'numberposts' => 1]);
            $log[] = [!$taken, "Товар {$slug}: адрес свободен"];
            $termOk = $prod['term'] === self::GEYSERS ? (bool) $geysers : isset($d['terms'][$prod['term']]);
            $log[] = [$termOk, "Товар {$slug}: категория {$prod['term']}"];
            $len = mb_strlen($prod['seo_description']);
            $log[] = [$len >= 120 && $len <= 170, "Товар {$slug}: SEO description {$len} симв."];
            if (!empty($prod['modifications'])) {
                $cols = substr_count(explode('</tr>', $prod['ru']['specifications'])[0], '<td') - 1;
                $log[] = [$cols === count($prod['modifications']), "Товар {$slug}: столбцов моделей в таблице {$cols}, модификаций " . count($prod['modifications'])];
            }
            foreach (['ru', 'en'] as $lang) {
                foreach (['title', 'subtitle', 'description', 'description_catalog', 'features_and_benefits', 'specifications'] as $k) {
                    if (trim((string) ($prod[$lang][$k] ?? '')) === '') {
                        $log[] = [false, "Товар {$slug}: пустое поле {$lang}.{$k}"];
                    }
                }
            }
            $enText = implode(' ', array_map('strval', $prod['en']));
            $log[] = [!preg_match('/[А-Яа-яЁё]/u', wp_strip_all_tags($enText)), "Товар {$slug}: в EN-полях нет кириллицы"];
            if ($tpl['block']) {
                [, $missing] = self::blockData($prod, $tpl, []);
                $log[] = [!$missing, "Товар {$slug}: ключи ACF для всех полей блока" . ($missing ? ' — нет для: ' . implode(', ', array_slice($missing, 0, 6)) : '')];
            }
        }

        $ok = !in_array(false, array_column($log, 0), true);
        if (!$write || !$ok) {
            return [$log, $ok, false];
        }

        // ---- запись ----
        $state = ['posts' => [], 'terms' => [], 'attachments' => [], 'geysers_brand' => null];
        update_option(self::STATE, $state, false); // блокирует повторный запуск даже при сбое посередине
        $save = static function () use (&$state) {
            update_option(self::STATE, $state, false);
        };

        $termIds = [self::GEYSERS => (int) $geysers->term_id];
        foreach ($d['terms'] as $slug => $t) {
            $r = wp_insert_term($t['name'], 'product-category', ['slug' => $slug]);
            if (is_wp_error($r)) {
                $log[] = [false, "Категория {$slug}: " . $r->get_error_message()];
                $save();
                return [$log, false, true];
            }
            $tid = (int) $r['term_id'];
            $termIds[$slug] = $tid;
            $state['terms'][] = $tid;
            $save();
            $key = 'product-category_' . $tid;
            update_field('name_en', $t['name_en'], $key);
            update_field('seo_brand', $t['seo_brand'], $key);
            update_field('seo_description', $t['seo_description'], $key);
            update_field('intro_text', $t['intro_text'], $key);
            update_field('faq', array_map(static fn ($q) => ['question' => $q[0], 'answer' => $q[1]], $t['faq']), $key);
            $log[] = [true, "Категория {$slug} создана (ID {$tid})"];
        }

        $media = [];
        foreach (self::files($d) as $file => $alt) {
            $id = self::sideload($file, $alt);
            if (!$id) {
                $log[] = [false, "Файл {$file}: загрузка не удалась"];
                $save();
                return [$log, false, true];
            }
            $media[$file] = $id;
            $state['attachments'][] = $id;
            $save();
        }
        $log[] = [true, 'Загружено вложений: ' . count($media)];

        foreach ($d['products'] as $slug => $prod) {
            [$data] = self::blockData($prod, $tpl, $media);
            $id = wp_insert_post(wp_slash([
                'post_type' => 'products',
                'post_status' => 'draft',
                'post_title' => $prod['post_title'],
                'post_name' => $slug,
                'post_content' => self::content($tpl, $data),
            ]), true);
            if (is_wp_error($id)) {
                $log[] = [false, "Товар {$slug}: " . $id->get_error_message()];
                $save();
                return [$log, false, true];
            }
            $state['posts'][] = (int) $id;
            $save();
            wp_set_object_terms($id, [$termIds[$prod['term']]], 'product-category');
            update_field('brand', 'gkb', $id);
            update_field('seo_description', $prod['seo_description'], $id);
            if (!empty($prod['modifications'])) {
                update_field('modifications', array_map(static fn ($m) => ['code' => $m['code'], 'power_kw' => $m['power_kw'], 'area_m2' => '', 'dhw' => 0], $prod['modifications']), $id);
            }
            if (!empty($prod['faq'])) {
                update_field('faq', array_map(static fn ($q) => ['question' => $q[0], 'answer' => $q[1]], $prod['faq']), $id);
            }
            ProductData::flush($id);
            $log[] = [true, "Товар {$slug}: черновик создан (ID {$id}) — " . get_preview_post_link($id)];
        }

        self::flushCaches();
        return [$log, true, true];
    }

    public static function publish(): string
    {
        $state = get_option(self::STATE);
        if (!is_array($state) || empty($state['posts'])) {
            return 'Нечего публиковать: импорт не выполнен.';
        }
        foreach ($state['posts'] as $id) {
            wp_update_post(['ID' => $id, 'post_status' => 'publish']);
            ProductData::flush($id);
        }
        $geysers = get_term_by('slug', self::GEYSERS, 'product-category');
        if ($geysers) {
            $key = 'product-category_' . $geysers->term_id;
            if ($state['geysers_brand'] === null) {
                $state['geysers_brand'] = (string) get_field('seo_brand', $key);
            }
            update_field('seo_brand', 'ВПГ', $key);
        }
        $state['published'] = true;
        update_option(self::STATE, $state, false);
        self::flushCaches();
        return 'Опубликовано товаров: ' . count($state['posts']) . '. Бренд категории «Газовые колонки» — «ВПГ». Кеш сброшен.';
    }

    public static function rollback(): string
    {
        $state = get_option(self::STATE);
        if (!is_array($state)) {
            return 'Откатывать нечего.';
        }
        foreach ($state['posts'] as $id) {
            wp_delete_post($id, true);
        }
        foreach ($state['attachments'] as $id) {
            wp_delete_attachment($id, true);
        }
        foreach ($state['terms'] as $id) {
            wp_delete_term($id, 'product-category');
        }
        if ($state['geysers_brand'] !== null) {
            $geysers = get_term_by('slug', self::GEYSERS, 'product-category');
            if ($geysers) {
                update_field('seo_brand', $state['geysers_brand'], 'product-category_' . $geysers->term_id);
            }
        }
        delete_option(self::STATE);
        self::flushCaches();
        return 'Удалено: товаров ' . count($state['posts']) . ', вложений ' . count($state['attachments']) . ', категорий ' . count($state['terms']) . '.';
    }

    private static function sideload(string $file, string $alt): int
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $tmp = wp_tempnam($file);
        copy(self::dir() . 'media/' . $file, $tmp);
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

    private static function flushCaches(): void
    {
        if (function_exists('wpo_cache_flush')) {
            wpo_cache_flush();
        }
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
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
        if ($action && check_admin_referer('gkb_import_products')) {
            if ($action === 'rollback') {
                $notice = self::rollback();
            } elseif ($action === 'publish') {
                $notice = self::publish();
            } else {
                [$log, $ok, $written] = self::run($action === 'apply');
            }
        }
        $state = get_option(self::STATE);

        echo '<div class="wrap"><h1>Товары GKB</h1>';
        echo '<p>Импорт колонок, стабилизаторов и датчиков GKB (openspec <code>add-gkb-products</code>). «Проверить» ничего не записывает.</p>';
        if ($notice) {
            echo '<div class="notice notice-info"><p>' . esc_html($notice) . '</p></div>';
        }
        if ($log !== null) {
            $cls = $written && $ok ? 'notice-success' : ($ok ? 'notice-info' : 'notice-error');
            $msg = $written && $ok ? 'Черновики созданы, кеш сброшен.' : ($ok ? 'Все проверки прошли — можно применять.' : ($written ? 'Запись прервана — нажмите «Откатить».' : 'Есть ошибки — запись невозможна.'));
            echo '<div class="notice ' . $cls . '"><p>' . esc_html($msg) . '</p></div>';
            echo '<table class="widefat striped"><tbody>';
            foreach ($log as [$good, $text]) {
                echo '<tr><td style="width:2em">' . ($good ? '✅' : '❌') . '</td><td>' . esc_html($text) . '</td></tr>';
            }
            echo '</tbody></table>';
        }
        echo '<form method="post" style="margin-top:16px;display:flex;gap:8px">';
        wp_nonce_field('gkb_import_products');
        echo '<button class="button" name="gkb_action" value="check">Проверить</button>';
        if (!$state) {
            echo '<button class="button button-primary" name="gkb_action" value="apply" onclick="return confirm(\'Создать категории, вложения и черновики товаров?\')">Применить</button>';
        } else {
            if (empty($state['published'])) {
                echo '<button class="button button-primary" name="gkb_action" value="publish" onclick="return confirm(\'Опубликовать товары GKB?\')">Опубликовать</button>';
            }
            echo '<button class="button" name="gkb_action" value="rollback" onclick="return confirm(\'Удалить все созданные товары, вложения и категории?\')">Откатить</button>';
        }
        echo '</form>';
        if (is_array($state)) {
            echo '<p>Создано: товаров ' . count($state['posts']) . ', вложений ' . count($state['attachments']) . ', категорий ' . count($state['terms']) . '. ' . (!empty($state['published']) ? 'Опубликовано.' : 'Черновики.') . '</p>';
            echo '<ul>';
            foreach ($state['posts'] as $id) {
                $p = get_post($id);
                if ($p instanceof WP_Post) {
                    echo '<li><a href="' . esc_url(get_preview_post_link($p)) . '" target="_blank">' . esc_html($p->post_title) . '</a> — ' . esc_html($p->post_status) . '</li>';
                }
            }
            echo '</ul>';
        }
        echo '</div>';
    }
}
