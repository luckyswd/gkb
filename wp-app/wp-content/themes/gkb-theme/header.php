<!doctype html>
<html lang="<?= (new helpers\Helpers)->getLang() === 'en' ? 'en' : 'ru' ?>">

<head>
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <?php $fontsDir = get_template_directory_uri() . '/dist/fonts/Inter/'; ?>
    <?php foreach (['cyrillic-400', 'cyrillic-500', 'cyrillic-600'] as $fontFile) : ?>
        <link rel="preload" href="<?= esc_url($fontsDir . 'inter-' . $fontFile . '-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <?php endforeach; ?>
    <?php wp_head(); ?>
    <?php if (home_url() == 'https://gazkotelbel.com' ) :?>
    <!-- Yandex.Metrika counter: загружается после первого действия посетителя
         или когда браузер свободен (не позже ~3,5 с), чтобы не мешать отрисовке.
         Вызовы ym() до загрузки копятся в очереди и не теряются. -->
    <script>
        (function (m, e, t, r, i) {
            m[i] = m[i] || function () { (m[i].a = m[i].a || []).push(arguments) };
            m[i].l = 1 * new Date();
            var loaded = false;
            function load() {
                if (loaded) { return; }
                loaded = true;
                for (var j = 0; j < e.scripts.length; j++) {
                    if (e.scripts[j].src === r) { return; }
                }
                var k = e.createElement(t), a = e.getElementsByTagName(t)[0];
                k.async = 1; k.src = r; a.parentNode.insertBefore(k, a);
            }
            ['pointerdown', 'keydown', 'touchstart', 'scroll'].forEach(function (ev) {
                m.addEventListener(ev, load, {once: true, passive: true});
            });
            m.addEventListener('load', function () {
                (m.requestIdleCallback || function (cb) { return setTimeout(cb, 1500); })(load, {timeout: 3500});
            });
        })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym');

        ym(94762651, 'init', {
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

/* Данные для мгновенного поиска: заполняются ниже, пока выводится панель
   моделей категорий, чтобы не делать второй проход по товарам. */
$searchItems = [];
$searchPopular = [];
$searchIcon = '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="M20 20l-4-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
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
                <div class="gkb-segments gkb-segments--wrap gkb-segments--chips gkb-header__cats">
                    <div class="gkb-segments__track">
                        <a href="<?= esc_url($catalogUrl) ?>" class="gkb-segment<?= $isCatalogPage ? ' is-active' : '' ?>">
                            <?= esc_html($allProductsLabel) ?>
                        </a>
                        <?php foreach ($categories as $category) : ?>
                            <?php $models = Helpers::getProductsByCategory($category); ?>
                            <?php if (empty($models)) {
                                continue;
                            } ?>
                            <?php $searchPopular[] = $termName($category); ?>
                            <div class="gkb-header__cat">
                                <a href="<?= esc_url(get_term_link($category)) ?>"
                                   class="gkb-segment<?= $activeTermId === (int) $category->term_id ? ' is-active' : '' ?>"
                                   data-cat-trigger>
                                    <?= esc_html($termName($category)) ?>
                                </a>
                                <div class="gkb-header__models" data-cat-panel>
                                    <?php foreach ($models as $model) : ?>
                                        <?php
                                        $modelImage = $helper->getProductSliderImage($model, 'medium');
                                        $searchItems[] = [
                                            'name' => (string) ($helper->getProductTitle($model) ?? ''),
                                            'sub' => (string) ($helper->getProductDescriptionCatalog($model) ?? ''),
                                            'cat' => $termName($category),
                                            'img' => $modelImage ?: '',
                                            'url' => get_permalink($model),
                                        ];
                                        ?>
                                        <a href="<?= esc_url(get_permalink($model)) ?>" class="gkb-header__model">
                                            <span class="gkb-header__model-pic">
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

            <button type="button" class="gkb-header__search-trigger" data-search-open
                    aria-haspopup="dialog" aria-controls="gkb-search">
                <?= $searchIcon ?>
                <span><?= esc_html($searchPlaceholder) ?></span>
            </button>

            <a href="<?= esc_url($whereUrl) ?>" class="gkb-btn gkb-btn--primary gkb-btn--sm gkb-header__cta">
                <?= esc_html($whereTitle) ?>
            </a>
        </div>

        <div class="gkb-header__quick">
            <a href="<?= esc_url($catalogUrl) ?>" class="gkb-btn gkb-btn--secondary gkb-btn--block"><?= esc_html($allProductsLabel) ?></a>
            <a href="<?= esc_url($whereUrl) ?>" class="gkb-btn gkb-btn--primary gkb-btn--block"><?= esc_html($whereTitle) ?></a>
        </div>

        <div class="gkb-header__mobile" id="gkb-mobile-menu" hidden>
            <button type="button" class="gkb-header__search-trigger gkb-header__search-trigger--block" data-search-open
                    aria-haspopup="dialog" aria-controls="gkb-search">
                <?= $searchIcon ?>
                <span><?= esc_html($searchPlaceholder) ?></span>
            </button>

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

    <?php
    // популярные запросы: категории и несколько ходовых моделей
    foreach ($searchItems as $item) {
        if (count($searchPopular) >= 8) {
            break;
        }
        if (preg_match('/(турбо|turbo|житомир-3$|zhytomyr-3$|впг-20$)/iu', $item['name'])) {
            $searchPopular[] = $item['name'];
        }
    }
    $searchPopular = array_values(array_unique(array_filter($searchPopular)));
    ?>
    <div class="gkb-search" id="gkb-search" role="dialog" aria-modal="true"
         aria-label="<?= esc_attr($searchPlaceholder) ?>" hidden
         data-text-empty="<?= esc_attr($lang === 'en' ? 'Nothing found' : 'Ничего не найдено') ?>"
         data-text-all="<?= esc_attr($lang === 'en' ? 'All results in the catalog' : 'Все результаты в каталоге') ?>">
        <div class="container gkb-search__inner">
            <button type="button" class="gkb-search__close" data-search-close
                    aria-label="<?= esc_attr($lang === 'en' ? 'Close search' : 'Закрыть поиск') ?>">
                <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>

            <form class="gkb-search__form" role="search" method="get" action="<?= esc_url($catalogUrl) ?>"
                  toolname="search_catalog" toolautosubmit
                  tooldescription="Ищет модели в каталоге котлов Житомир и колонок ВПГ официального представителя АТЕМ в Беларуси (gazkotelbel.com) по названию или типу: «Житомир-3», «турбо», «колонка». Возвращает страницу каталога с найденными товарами.">
                <?= $searchIcon ?>
                <input type="search" name="search" class="gkb-search__input" autocomplete="off"
                       value="<?= esc_attr($_GET['search'] ?? '') ?>"
                       placeholder="<?= esc_attr($lang === 'en' ? 'search' : 'поиск') ?>"
                       toolparamdescription="Поисковый запрос на русском: название модели (например, «Житомир-10») или тип оборудования."
                       aria-label="<?= esc_attr($searchPlaceholder) ?>">
                <button type="button" class="gkb-search__clear" data-search-clear hidden
                        aria-label="<?= esc_attr($lang === 'en' ? 'Clear' : 'Очистить') ?>">
                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </form>

            <?php if (!empty($searchPopular)) : ?>
                <div class="gkb-search__popular">
                    <?php foreach ($searchPopular as $word) : ?>
                        <button type="button" class="gkb-search__chip" data-search-term="<?= esc_attr($word) ?>"><?= esc_html($word) ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="gkb-search__results" data-search-results aria-live="polite"></div>
        </div>
        <script type="application/json" id="gkb-search-data"><?= wp_json_encode($searchItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    </div>
</header>
<main id="main" class="main" data-page-id="<?= get_queried_object_id() ?>">
