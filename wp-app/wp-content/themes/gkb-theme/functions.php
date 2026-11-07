<?php
require_once sprintf('%s/vendor/autoload.php', __DIR__);
include 'hooks_filters/register_styles_scripts.php';
include 'hooks_filters/postTypes_taxonomies.php';
include 'hooks_filters/webp_upload.php';
include 'hooks_filters/after_setup_theme.php';
include 'hooks_filters/reset_default_css_js.php';
include 'hooks_filters/additional.php';
include 'hooks_filters/removed_urls.php';
include 'hooks_filters/acf_blocks_editor.php';
include 'hooks_filters/cache_purge.php';

require_once 'helpers/Helpers.php';
require_once 'seo/seo.php';
require_once 'authorization/Authorization.php';
require_once 'ajax/Ajax.php';
require_once 'googleApi/GoogleClient.php';
require_once 'admin/LeftoversTab.php';
$authorization = new \authorization\Authorization();
$authorization->register();
$ajax = new \ajax\Ajax();
$ajax->register();
$leftoversTab = new \admin\LeftoversTab();
$leftoversTab->register();

add_filter('show_admin_bar', '__return_false');
add_filter('wpcf7_autop_or_not', '__return_false');
add_action('acf/init', 'my_register_blocks');
function my_register_blocks()
{
    if (function_exists('acf_register_block_type')) {
        $path = get_template_directory();
        $filesPhp = globSearch($path . "/modules/*.php");

        foreach ($filesPhp as $file) {
            $filePath = str_replace($path, '', $file);
            $fileName = explode('/', $filePath);
            $fileName = end($fileName);
            $fileName = str_replace('.php', '', $fileName);
            $file_headers = get_file_data(__DIR__ . $filePath, [
                'title' => 'Title',
                'description' => 'Description',
                'category' => 'Category',
                'icon' => 'Icon',
                'keywords' => 'Keywords',
                'mode' => 'Mode',
                'align' => 'Align',
                'post_types' => 'PostTypes',
                'supports_align' => 'SupportsAlign',
                'supports_anchor' => 'SupportsAnchor',
                'supports_mode' => 'SupportsMode',
                'supports_jsx' => 'SupportsInnerBlocks',
                'supports_align_text' => 'SupportsAlignText',
                'supports_align_content' => 'SupportsAlignContent',
                'supports_multiple' => 'SupportsMultiple',
                'enqueue_style'     => 'EnqueueStyle',
                'enqueue_script'    => 'EnqueueScript',
                'enqueue_assets'    => 'EnqueueAssets',
            ]);

            acf_register_block_type(array(
                'name' => $fileName,
                'title' => __($file_headers['title']),
                'mode' => __($file_headers['mode']),
                'render_callback' => 'my_acf_block_render_callback',
                'category' => 'formatting',
                // Без переключателя в edit: в этом режиме поля не работают, см. acf_blocks_editor.php
                'supports' => ['mode' => false],
            ));
        }
    }
}

function my_acf_block_render_callback($block)
{
    $slug = str_replace('acf/', '', $block['name']);
    if (file_exists(get_theme_file_path("modules/" . $slug . '/' . $slug . ".php"))) {
        include(get_theme_file_path("modules/" . $slug . '/' . $slug . ".php"));
    }
}
function set_custom_cookie() {
    $month = time() + (30 * 24 * 60 * 60);
    $fromUrl = $_GET['lang'] ?? null;

    if ($fromUrl === 'ru' || $fromUrl === 'en') {
        if (($_COOKIE['lang'] ?? '') !== $fromUrl) {
            setcookie('lang', $fromUrl, $month, COOKIEPATH, COOKIE_DOMAIN);
            $_COOKIE['lang'] = $fromUrl;
        }
        return;
    }

    if (!isset($_COOKIE['lang'])) {
        setcookie('lang', 'ru', $month, COOKIEPATH, COOKIE_DOMAIN);
    }
}
add_action('wp_loaded', 'set_custom_cookie');

/* Кеш страниц WP-Optimize по умолчанию не знает про куку языка и отдаёт
   английскому посетителю русскую копию страницы. Этот фильтр делает отдельную
   копию кеша на каждое значение куки lang. Настройка попадает в конфиг кеша
   при сохранении настроек WP-Optimize, поэтому после выкладки их нужно один
   раз пересохранить. */
add_filter('wpo_cache_cookies', static function ($cookies) {
    $cookies = is_array($cookies) ? $cookies : [];
    $cookies[] = 'lang';
    return array_values(array_unique($cookies));
});
function removeMenu(): void
{
    remove_menu_page('all-fields');
}
add_action('admin_init', 'removeMenu');

function slug_report_type_template(): void
{
    $page_type_object_podcasts = get_post_type_object('products');
    $page_type_object_podcasts->template = [
        ['acf/product'],
    ];
}
add_action('init', 'slug_report_type_template');

add_filter( 'allow_dev_auto_core_updates', '__return_false' );