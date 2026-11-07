<?php

use helpers\Helpers;

$helper = new Helpers();
$lang = $helper->getLang();

/* На /catalog текущей категории нет, на странице категории её задаёт
   taxonomy-product-category.php. */
$currentCategory = $currentCategory ?? null;
$search = $search ?? '';

$headerFields = $helper->get_field_multi_lang('header_links', 'option');
$allProductsLabel = $headerFields['all_products'] ?? ($lang === 'en' ? 'All products' : 'Вся продукция');

$categories = get_terms([
    'taxonomy' => 'product-category',
    'orderby' => 'term_id',
    'order' => 'ASC',
    'hide_empty' => true,
]);

$termName = static function ($term) use ($lang): string {
    if ($lang === 'en') {
        $en = get_field('name_en', $term);
        if (!empty($en)) {
            return $en;
        }
    }
    return $term->name;
};

$catalogUrl = home_url('/catalog');
$title = $currentCategory ? $termName($currentCategory) : $allProductsLabel;

/* Счётчики считаются тем же запросом, что и листинг, — иначе снятый
   с публикации товар попадёт в счётчик, хотя в сетке его нет. */
$totalCount = count(Helpers::getProductsByCategory());

?>

<section class="gkb-catalog">
    <div class="container">

        <div class="gkb-catalog__head">
            <nav class="gkb-crumbs" aria-label="<?= esc_attr($lang === 'en' ? 'Breadcrumbs' : 'Хлебные крошки') ?>">
                <a href="<?= esc_url(home_url('/')) ?>"><?= esc_html($lang === 'en' ? 'Home' : 'Главная') ?></a>
                <span>/</span>
                <?php if ($currentCategory) : ?>
                    <a href="<?= esc_url($catalogUrl) ?>"><?= esc_html($allProductsLabel) ?></a>
                    <span>/</span>
                    <span class="gkb-crumbs__current"><?= esc_html($title) ?></span>
                <?php else : ?>
                    <span class="gkb-crumbs__current"><?= esc_html($allProductsLabel) ?></span>
                <?php endif; ?>
            </nav>
            <h1><?= esc_html($title) ?></h1>
        </div>

        <?php if (!empty($categories)) : ?>
            <div class="gkb-catalog__tabs">
                <div class="gkb-segments gkb-segments--chips">
                    <div class="gkb-segments__track">
                        <a href="<?= esc_url($catalogUrl) ?>"
                           class="gkb-segment<?= $currentCategory ? '' : ' is-active' ?>">
                            <?= esc_html($allProductsLabel) ?>
                            <span class="gkb-segment__count"><?= (int) $totalCount ?></span>
                        </a>
                        <?php foreach ($categories as $category) : ?>
                            <?php $categoryCount = count(Helpers::getProductsByCategory($category)); ?>
                            <?php if ($categoryCount === 0) {
                                continue;
                            } ?>
                            <a href="<?= esc_url(get_term_link($category)) ?>"
                               class="gkb-segment<?= $currentCategory && (int) $currentCategory->term_id === (int) $category->term_id ? ' is-active' : '' ?>">
                                <?= esc_html($termName($category)) ?>
                                <span class="gkb-segment__count"><?= (int) $categoryCount ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($search)) : ?>
            <p class="gkb-catalog__search">
                <?= esc_html($lang === 'en' ? 'Catalog search: ' : 'Поиск по каталогу: ') ?>
                <strong><?= esc_html($search) ?></strong>
            </p>
        <?php endif; ?>

        <?php if (!empty($products)) : ?>
            <div class="gkb-catalog__grid">
                <?php foreach ($products as $product) : ?>
                    <?php include 'product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="gkb-catalog__empty gkb-card">
                <p><?= esc_html($lang === 'en' ? 'Nothing found' : 'Ничего не найдено') ?> 😔</p>
                <a href="<?= esc_url($catalogUrl) ?>" class="gkb-btn gkb-btn--secondary gkb-btn--sm">
                    <?= esc_html($allProductsLabel) ?>
                </a>
            </div>
        <?php endif; ?>

        <?php if (empty($search)) {
            \seo\Content::renderTermBottom($currentCategory);
        } ?>

    </div>
</section>
