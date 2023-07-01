<?php
require_once sprintf('%s/vendor/autoload.php', __DIR__);
include 'hooks_filters/register_styles_scripts.php';
include 'hooks_filters/postTypes_taxonomies.php';
include 'hooks_filters/webp_upload.php';
include 'hooks_filters/after_setup_theme.php';
include 'hooks_filters/reset_default_css_js.php';
include 'hooks_filters/additional.php';

require_once 'helpers/Helpers.php';
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
    if (!isset($_COOKIE['lang'])) {
        $month = time() + (30 * 24 * 60 * 60);
        setcookie('lang', 'ru', $month, COOKIEPATH, COOKIE_DOMAIN);
    }
}
add_action('wp_loaded', 'set_custom_cookie');
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