<!doctype html>
<html lang="<?= (new helpers\Helpers)->getLang() === 'en' ? 'en' : 'ru' ?>">

<head>
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="yandex-verification" content="56c82ebe91ca39d9" />
    <title><?php wp_title('«', true, 'right'); ?><?php bloginfo('name'); ?></title>
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>"/>
    <?php wp_head(); ?>
    <?php if (home_url() == 'https://gazkotelbel.com' ) :?>
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function (m, e, t, r, i, k, a) {
            m[i] = m[i] || function () {
                (m[i].a = m[i].a || []).push(arguments)
            };
            m[i].l = 1 * new Date();
            for (var j = 0; j < document.scripts.length; j++) {
                if (document.scripts[j].src === r) {
                    return;
                }
            }
            k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
        })
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

        ym(94762651, "init", {
            clickmap: true,
            trackLinks: true,
            accurateTrackBounce: true,
            webvisor: true
        });
    </script>
    <noscript>
        <div><img src="https://mc.yandex.ru/watch/94762651" style="position:absolute; left:-9999px;" alt=""/></div>
    </noscript>
    <!-- /Yandex.Metrika counter -->
    <?php endif; ?>

</head>

<?php

use helpers\Helpers;

$helper = new Helpers();
$lang = $helper->getLang();
$logo = get_field('logo', 'option');
$socials = get_field('socials', 'option');
$fields = $helper->get_field_multi_lang('header_links', 'option');
$categories = get_terms([
    'taxonomy' => 'product-category',
    'orderby' => 'term_id',
    'order' => 'ASC',
    'hide_empty' => true,
]);

$catalogUrl = home_url('/catalog');
$allProductsLabel = $fields['all_products'] ?? ($lang === 'en' ? 'All products' : 'Вся продукция');

/** Путь текущей страницы и произвольного адреса — для подсветки активного пункта. */
$pathOf = static function (?string $url): string {
    return trim((string) parse_url((string) $url, PHP_URL_PATH), '/');
};
$currentPath = $pathOf($_SERVER['REQUEST_URI'] ?? '');

/** Категория, которую надо подсветить: своя на странице категории, родительская — на товаре. */
$activeTermId = 0;
if (is_tax('product-category')) {
    $activeTermId = (int) get_queried_object_id();
} elseif (is_singular('products')) {
    $productTerms = get_the_terms(get_queried_object_id(), 'product-category');
    if (!empty($productTerms)) {
        $activeTermId = (int) $productTerms[0]->term_id;
    }
}
$isCatalogPage = is_page_template('page-catalog.php') || $currentPath === 'catalog';

/** Название категории на текущем языке. */
$termName = static function ($term) use ($lang): string {
    if ($lang === 'en') {
        $en = get_field('name_en', $term);
        if (!empty($en)) {
            return $en;
        }
    }
    return $term->name;
};

/** Ссылка «Где купить?» берётся из меню, чтобы подпись оставалась за редактором. */
$whereLink = null;
foreach (($fields['links'] ?? []) as $menuItem) {
    if (!empty($menuItem['link']['url']) && str_contains($menuItem['link']['url'], 'where-to-buy')) {
        $whereLink = $menuItem['link'];
        break;
    }
}
$whereUrl = $whereLink['url'] ?? home_url('/where-to-buy/');
$whereTitle = $whereLink['title'] ?? ($lang === 'en' ? 'Where to buy?' : 'Где купить?');
$searchPlaceholder = $fields['placeholder_search'] ?? ($lang === 'en' ? 'Search' : 'Поиск');
$loginLabel = $fields['login'] ?? ($lang === 'en' ? 'Log in' : 'Войти');
$logoutLabel = $fields['logout'] ?? ($lang === 'en' ? 'Log out' : 'Выйти');
?>

<body>
<header id="header" class="gkb-header">
    <div class="container">

        <div class="gkb-header__top">
            <?php if (!empty($logo)) : ?>
                <a href="<?= esc_url(home_url('/')) ?>" class="gkb-header__logo">
                    <img src="<?= esc_url($logo['url']) ?>" alt="<?= esc_attr($logo['title'] ?? '') ?>" width="180" height="44">
                </a>
            <?php endif; ?>

            <?php if (!empty($fields['links'])) : ?>
                <nav class="gkb-header__nav" aria-label="<?= esc_attr($lang === 'en' ? 'Site sections' : 'Разделы сайта') ?>">
                    <?php foreach ($fields['links'] as $menuItem) : ?>
                        <?php if (empty($menuItem['link']['url']) || empty($menuItem['link']['title'])) {
                            continue;
                        } ?>
                        <?php $isActive = $pathOf($menuItem['link']['url']) === $currentPath && $currentPath !== ''; ?>
                        <a href="<?= esc_url($menuItem['link']['url']) ?>"
                           class="gkb-header__nav-link<?= $isActive ? ' is-active' : '' ?>"
                           <?= $isActive ? 'aria-current="page"' : '' ?>
                           target="<?= esc_attr($menuItem['link']['target'] ?? '') ?>">
                            <?= esc_html($menuItem['link']['title']) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>

            <?php if (!empty($socials)) : ?>
                <div class="gkb-header__socials">
                    <?php foreach ($socials as $social) : ?>
                        <?php if (empty($social['url']) || empty($social['icon'])) {
                            continue;
                        } ?>
                        <a href="<?= esc_url($social['url']) ?>" class="gkb-btn gkb-btn--icon"
                           title="<?= esc_attr($social['icon']['title'] ?? '') ?>" rel="noopener" target="_blank">
                            <img src="<?= esc_url($social['icon']['url']) ?>" alt="<?= esc_attr($social['icon']['title'] ?? '') ?>" width="20" height="20">
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="gkb-lang" role="group" aria-label="<?= esc_attr($lang === 'en' ? 'Site language' : 'Язык сайта') ?>">
                <button type="button" class="gkb-lang__btn<?= $lang === 'ru' ? ' is-active' : '' ?>" data-lang="ru"
                        aria-pressed="<?= $lang === 'ru' ? 'true' : 'false' ?>">RU</button>
                <button type="button" class="gkb-lang__btn<?= $lang === 'en' ? ' is-active' : '' ?>" data-lang="en"
                        aria-pressed="<?= $lang === 'en' ? 'true' : 'false' ?>">EN</button>
            </div>

            <div class="gkb-header__account">
                <?php if (!is_user_logged_in()) : ?>
                    <a href="<?= esc_url(home_url('/login')) ?>" class="gkb-btn gkb-btn--dark gkb-btn--sm"><?= esc_html($loginLabel) ?></a>
                <?php else : ?>
                    <button type="button" class="gkb-btn gkb-btn--dark gkb-btn--sm user-logout"><?= esc_html($logoutLabel) ?></button>
                <?php endif; ?>
            </div>

            <button type="button" class="gkb-btn gkb-btn--secondary gkb-header__burger"
                    aria-expanded="false" aria-controls="gkb-mobile-menu"
                    data-label-open="<?= esc_attr($lang === 'en' ? 'Menu' : 'Меню') ?>"
                    data-label-close="<?= esc_attr($lang === 'en' ? 'Close' : 'Закрыть') ?>">
                <?= esc_html($lang === 'en' ? 'Menu' : 'Меню') ?>
            </button>
        </div>

        <div class="gkb-header__bar">
            <?php if (!empty($categories)) : ?>
                <div class="gkb-segments gkb-segments--wrap gkb-header__cats">
                    <div class="gkb-segments__track">
                        <a href="<?= esc_url($catalogUrl) ?>" class="gkb-segment<?= $isCatalogPage ? ' is-active' : '' ?>">
                            <?= esc_html($allProductsLabel) ?>
                        </a>
                        <?php foreach ($categories as $category) : ?>
                            <?php $models = Helpers::getProductsByCategory($category); ?>
                            <?php if (empty($models)) {
                                continue;
                            } ?>
                            <div class="gkb-header__cat">
                                <a href="<?= esc_url(get_term_link($category)) ?>"
                                   class="gkb-segment<?= $activeTermId === (int) $category->term_id ? ' is-active' : '' ?>"
                                   data-cat-trigger>
                                    <?= esc_html($termName($category)) ?>
                                </a>
                                <div class="gkb-header__models" data-cat-panel>
                                    <?php foreach ($models as $model) : ?>
                                        <a href="<?= esc_url(get_permalink($model)) ?>" class="gkb-header__model">
                                            <span class="gkb-header__model-pic">
                                                <?php $modelImage = $helper->getProductSliderImage($model); ?>
                                                <?php if ($modelImage) : ?>
                                                    <img src="<?= esc_url($modelImage) ?>" alt="" loading="lazy" width="48" height="64">
                                                <?php endif; ?>
                                            </span>
                                            <span class="gkb-header__model-text">
                                                <span class="gkb-header__model-name"><?= esc_html($helper->getProductTitle($model) ?? '') ?></span>
                                                <span class="gkb-header__model-sub"><?= esc_html($helper->getProductDescriptionCatalog($model) ?? '') ?></span>
                                            </span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <form class="gkb-header__search" role="search" method="get" action="<?= esc_url($catalogUrl) ?>">
                <input type="search" name="search" class="gkb-field gkb-header__search-input"
                       value="<?= esc_attr($_GET['search'] ?? '') ?>"
                       placeholder="<?= esc_attr($searchPlaceholder) ?>"
                       aria-label="<?= esc_attr($searchPlaceholder) ?>">
            </form>

            <a href="<?= esc_url($whereUrl) ?>" class="gkb-btn gkb-btn--primary gkb-btn--sm gkb-header__cta">
                <?= esc_html($whereTitle) ?>
            </a>
        </div>

        <div class="gkb-header__quick">
            <a href="<?= esc_url($catalogUrl) ?>" class="gkb-btn gkb-btn--secondary gkb-btn--block"><?= esc_html($allProductsLabel) ?></a>
            <a href="<?= esc_url($whereUrl) ?>" class="gkb-btn gkb-btn--primary gkb-btn--block"><?= esc_html($whereTitle) ?></a>
        </div>

        <div class="gkb-header__mobile" id="gkb-mobile-menu" hidden>
            <form class="gkb-header__mobile-search" role="search" method="get" action="<?= esc_url($catalogUrl) ?>">
                <input type="search" name="search" class="gkb-field"
                       value="<?= esc_attr($_GET['search'] ?? '') ?>"
                       placeholder="<?= esc_attr($searchPlaceholder) ?>"
                       aria-label="<?= esc_attr($searchPlaceholder) ?>">
            </form>

            <?php if (!empty($fields['links'])) : ?>
                <nav class="gkb-header__mobile-nav">
                    <?php foreach ($fields['links'] as $menuItem) : ?>
                        <?php if (empty($menuItem['link']['url']) || empty($menuItem['link']['title'])) {
                            continue;
                        } ?>
                        <a href="<?= esc_url($menuItem['link']['url']) ?>" target="<?= esc_attr($menuItem['link']['target'] ?? '') ?>">
                            <?= esc_html($menuItem['link']['title']) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>

            <?php if (!empty($categories)) : ?>
                <div class="gkb-header__mobile-cats">
                    <a href="<?= esc_url($catalogUrl) ?>" class="gkb-header__mobile-cat is-all"><?= esc_html($allProductsLabel) ?></a>
                    <?php foreach ($categories as $category) : ?>
                        <a href="<?= esc_url(get_term_link($category)) ?>" class="gkb-header__mobile-cat">
                            <?= esc_html($termName($category)) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="gkb-header__mobile-foot">
                <?php if (!empty($socials)) : ?>
                    <div class="gkb-header__socials">
                        <?php foreach ($socials as $social) : ?>
                            <?php if (empty($social['url']) || empty($social['icon'])) {
                                continue;
                            } ?>
                            <a href="<?= esc_url($social['url']) ?>" class="gkb-btn gkb-btn--icon"
                               title="<?= esc_attr($social['icon']['title'] ?? '') ?>" rel="noopener" target="_blank">
                                <img src="<?= esc_url($social['icon']['url']) ?>" alt="<?= esc_attr($social['icon']['title'] ?? '') ?>" width="20" height="20">
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="gkb-lang" role="group" aria-label="<?= esc_attr($lang === 'en' ? 'Site language' : 'Язык сайта') ?>">
                    <button type="button" class="gkb-lang__btn<?= $lang === 'ru' ? ' is-active' : '' ?>" data-lang="ru"
                            aria-pressed="<?= $lang === 'ru' ? 'true' : 'false' ?>">RU</button>
                    <button type="button" class="gkb-lang__btn<?= $lang === 'en' ? ' is-active' : '' ?>" data-lang="en"
                            aria-pressed="<?= $lang === 'en' ? 'true' : 'false' ?>">EN</button>
                </div>

                <?php if (!is_user_logged_in()) : ?>
                    <a href="<?= esc_url(home_url('/login')) ?>" class="gkb-btn gkb-btn--dark gkb-btn--block"><?= esc_html($loginLabel) ?></a>
                <?php else : ?>
                    <button type="button" class="gkb-btn gkb-btn--dark gkb-btn--block user-logout"><?= esc_html($logoutLabel) ?></button>
                <?php endif; ?>
            </div>
        </div>

    </div>
</header>
<main id="main" class="main" data-page-id="<?= get_queried_object_id() ?>">
