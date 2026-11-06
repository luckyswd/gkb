<?php
/**
 * Сброс кеша страниц после каждой выкладки.
 *
 * Сайт закрыт кешем WP-Optimize (и W3 Total Cache). После выкладки посетители
 * продолжали получать копии страниц, сохранённые до неё, — со старой
 * разметкой и ссылкой на старые скрипты. Имя файла стилей в dist/ при каждой
 * сборке новое, поэтому по нему видно, что вышла новая версия темы: при первом
 * запросе после этого кеш сбрасывается, а конфиг кеша WP-Optimize
 * перезаписывается, чтобы подхватить фильтр wpo_cache_cookies (отдельный кеш на
 * каждый язык).
 *
 * Все вызовы плагинов защищены проверками: если плагина нет или его API
 * изменился, тема просто ничего не делает.
 */

function gkb_current_build_id(): string
{
    $files = glob(get_template_directory() . '/dist/css/*.css') ?: [];
    sort($files);

    return $files ? basename(end($files)) : '';
}

function gkb_purge_caches_after_deploy(): void
{
    $build = gkb_current_build_id();

    if ($build === '' || get_option('gkb_build_id') === $build) {
        return;
    }

    // запоминаем сразу, чтобы параллельные запросы не запускали сброс повторно
    update_option('gkb_build_id', $build, true);

    try {
        // WP-Optimize: перезаписать конфиг кеша (подхватит wpo_cache_cookies)
        if (class_exists('WPO_Cache_Config')
            && method_exists('WPO_Cache_Config', 'instance')) {
            $config = WPO_Cache_Config::instance();
            if (method_exists($config, 'get_config') && method_exists($config, 'update')) {
                $current = $config->get_config();
                if (is_array($current)) {
                    $config->update($current);
                }
            }
        }

        // WP-Optimize: сбросить кеш страниц и склейку скриптов/стилей
        if (function_exists('WP_Optimize')) {
            $wpo = WP_Optimize();
            if (is_object($wpo) && method_exists($wpo, 'get_page_cache')) {
                $pageCache = $wpo->get_page_cache();
                if (is_object($pageCache) && method_exists($pageCache, 'purge')) {
                    $pageCache->purge();
                }
            }
        }
        if (function_exists('wpo_cache_flush')) {
            wpo_cache_flush();
        }
        if (class_exists('WP_Optimize_Minify_Cache_Functions')
            && method_exists('WP_Optimize_Minify_Cache_Functions', 'reset')) {
            WP_Optimize_Minify_Cache_Functions::reset();
        }

        // W3 Total Cache
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }
    } catch (\Throwable $e) {
        error_log('gkb: сброс кеша после выкладки не удался: ' . $e->getMessage());
    }
}

add_action('init', 'gkb_purge_caches_after_deploy', 1);
