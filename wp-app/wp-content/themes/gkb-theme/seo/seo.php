<?php
/**
 * SEO-слой темы: мета-теги, JSON-LD, sitemap, robots.txt, llms.txt,
 * IndexNow, редиректы, скорость, статьи, калькулятор, региональные страницы.
 * Спеки — openspec/changes/seo-promotion (после архивации — openspec/specs).
 */

require_once __DIR__ . '/Context.php';
require_once __DIR__ . '/Org.php';
require_once __DIR__ . '/ProductData.php';
require_once __DIR__ . '/Regions.php';
require_once __DIR__ . '/Content.php';
require_once __DIR__ . '/Meta.php';
require_once __DIR__ . '/Schema.php';
require_once __DIR__ . '/Endpoints.php';
require_once __DIR__ . '/Llms.php';
require_once __DIR__ . '/IndexNow.php';
require_once __DIR__ . '/Redirects.php';
require_once __DIR__ . '/Perf.php';
require_once __DIR__ . '/Fields.php';
require_once __DIR__ . '/Calculator.php';
require_once __DIR__ . '/Agents.php';

seo\Fields::register();
seo\ProductData::register();
seo\Meta::register();
seo\Schema::register();
seo\Endpoints::register();
seo\IndexNow::register();
seo\Redirects::register();
seo\Perf::register();
seo\Agents::register();

/*
 * All in One SEO на время перехода: пока плагин активен, его вывод в head,
 * разметка, sitemap и robots.txt выключаются — источник один, тема.
 */
add_filter('aioseo_disable', '__return_true');
add_filter('aioseo_schema_disable', '__return_true');
add_filter('aioseo_disable_title_rewrites', '__return_true');
add_filter('aioseo_meta_views', '__return_empty_array');
add_filter('aioseo_robots_meta', '__return_empty_array');
add_filter('aioseo_canonical_url', '__return_empty_string');
add_filter('aioseo_sitemap_enabled', '__return_false');
add_filter('aioseo_robots_txt_enabled', '__return_false');
