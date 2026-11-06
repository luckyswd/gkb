<?php

/*
Title: Модуль продуктов
Mode: preview
*/

use helpers\Helpers;

$helpers = new Helpers();
$lang = $helpers->getLang();
$fields = $helpers->get_field_multi_lang();
$nameTabs = $helpers->get_field_multi_lang('name_tabs', 'option');

$productId = get_queried_object_id();
$functionality = $productId ? $helpers->getProductFunctionality(get_post($productId)) : [];

$headerFields = $helpers->get_field_multi_lang('header_links', 'option');
$allProductsLabel = $headerFields['all_products'] ?? ($lang === 'en' ? 'All products' : 'Вся продукция');
$writeToUsLabel = $lang === 'en' ? 'Write to us' : 'Написать нам';

$slides = $fields['image_slider'] ?? [];

$productCategory = null;
if ($productId) {
    $productTerms = get_the_terms($productId, 'product-category');
    if (!empty($productTerms)) {
        $productCategory = $productTerms[0];
    }
}
$categoryName = '';
if ($productCategory) {
    $categoryName = $lang === 'en'
        ? (get_field('name_en', $productCategory) ?: $productCategory->name)
        : $productCategory->name;
}

$tabs = [
    ['id' => 'benefits', 'name' => $nameTabs['tab_1'] ?? '', 'content' => $fields['features_and_benefits'] ?? ''],
    ['id' => 'specifications', 'name' => $nameTabs['tab_2'] ?? '', 'content' => $fields['specifications'] ?? ''],
    ['id' => 'equipment', 'name' => $nameTabs['tab_3'] ?? '', 'content' => $fields['equipment'] ?? ''],
    ['id' => 'documentation', 'name' => $nameTabs['tab_4'] ?? '', 'content' => $fields['documentation'] ?? ''],
];
$tabs = array_values(array_filter($tabs, static fn ($tab) => !empty($tab['name']) && !empty($tab['content'])));
$firstTab = $tabs[0]['id'] ?? '';
?>

<?php if (!is_admin()) : ?>
    <section class="gkb-product">
        <div class="container">

            <nav class="gkb-crumbs" aria-label="<?= esc_attr($lang === 'en' ? 'Breadcrumbs' : 'Хлебные крошки') ?>">
                <a href="<?= esc_url(home_url('/')) ?>"><?= esc_html($lang === 'en' ? 'Home' : 'Главная') ?></a>
                <span>/</span>
                <a href="<?= esc_url(home_url('/catalog')) ?>"><?= esc_html($allProductsLabel) ?></a>
                <?php if ($productCategory) : ?>
                    <span>/</span>
                    <a href="<?= esc_url(get_term_link($productCategory)) ?>"><?= esc_html($categoryName) ?></a>
                <?php endif; ?>
                <span>/</span>
                <span class="gkb-crumbs__current"><?= esc_html($fields['title'] ?? '') ?></span>
            </nav>

            <div class="gkb-card gkb-product__hero">
                <?php if (!empty($slides)) : ?>
                    <div class="gkb-gallery<?= count($slides) < 2 ? ' is-single' : '' ?>">
                        <?php if (count($slides) > 1) : ?>
                            <div class="gkb-gallery__thumbs">
                                <?php foreach ($slides as $index => $slide) : ?>
                                    <button type="button" class="gkb-gallery__thumb<?= $index === 0 ? ' is-active' : '' ?>"
                                            aria-label="<?= esc_attr(($lang === 'en' ? 'Photo ' : 'Фото ') . ($index + 1)) ?>">
                                        <img src="<?= esc_url($slide['image']['url']) ?>" alt="" loading="lazy" width="72" height="96">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="gkb-gallery__stage">
                            <div class="swiper gkb-gallery__main">
                                <div class="swiper-wrapper">
                                    <?php foreach ($slides as $slide) : ?>
                                        <div class="swiper-slide">
                                            <img src="<?= esc_url($slide['image']['url']) ?>"
                                                 alt="<?= esc_attr($fields['title'] ?? '') ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php if (count($slides) > 1) : ?>
                                <button type="button" class="gkb-gallery__nav gkb-gallery__nav--prev"
                                        aria-label="<?= esc_attr($lang === 'en' ? 'Previous photo' : 'Предыдущее фото') ?>">
                                    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
                                        <path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2"
                                              stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <button type="button" class="gkb-gallery__nav gkb-gallery__nav--next"
                                        aria-label="<?= esc_attr($lang === 'en' ? 'Next photo' : 'Следующее фото') ?>">
                                    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
                                        <path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2"
                                              stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <span class="gkb-gallery__counter"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="gkb-product__info">
                    <?php if (!empty($functionality)) : ?>
                        <div class="gkb-ficons">
                            <?php foreach ($functionality as $item) : ?>
                                <?php if (empty($item['icon'])) {
                                    continue;
                                } ?>
                                <button type="button" class="gkb-ficon gkb-ficon--lg gkb-tip-host"
                                        aria-label="<?= esc_attr($item['text'] ?? '') ?>">
                                    <span class="gkb-ficon__sprite<?= $item['is_sprite'] ? '' : ' is-single' ?>">
                                        <img src="<?= esc_url($item['icon']) ?>" alt="" aria-hidden="true">
                                    </span>
                                    <?php if (!empty($item['text'])) : ?>
                                        <span class="gkb-tip" role="tooltip"><?= esc_html($item['text']) ?></span>
                                    <?php endif; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="gkb-product__titles">
                        <?php if (!empty($fields['title'])) : ?>
                            <h1 class="gkb-product__title"><?= esc_html($fields['title']) ?></h1>
                        <?php endif; ?>
                        <?php if (!empty($fields['subtitle'])) : ?>
                            <p class="gkb-product__subtitle"><?= esc_html($fields['subtitle']) ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($fields['description'])) : ?>
                        <div class="gkb-product__description gkb-content"><?= Helpers::wrapTables($fields['description']) ?></div>
                    <?php endif; ?>

                    <div class="gkb-product__actions">
                        <?php if (!empty($fields['button_name'])) : ?>
                            <a href="<?= esc_url(home_url('/where-to-buy')) ?>" class="gkb-btn gkb-btn--primary">
                                <?= esc_html($fields['button_name']) ?>
                            </a>
                        <?php endif; ?>
                        <a class="gkb-btn gkb-btn--secondary" href="#feedback-form"><?= esc_html($writeToUsLabel) ?></a>
                    </div>
                </div>
            </div>

            <?php if (!empty($tabs)) : ?>
                <div class="gkb-card gkb-product__tabs">
                    <div class="gkb-segments">
                        <div class="gkb-segments__track">
                            <?php foreach ($tabs as $tab) : ?>
                                <button type="button" class="gkb-segment<?= $tab['id'] === $firstTab ? ' is-active' : '' ?>"
                                        data-tab="<?= esc_attr($tab['id']) ?>">
                                    <?= esc_html($tab['name']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php foreach ($tabs as $tab) : ?>
                        <div class="gkb-product__pane<?= $tab['id'] === $firstTab ? ' is-active' : '' ?>"
                             data-pane="<?= esc_attr($tab['id']) ?>">
                            <?php if ($tab['id'] === 'documentation') : ?>
                                <div class="gkb-product__docs">
                                    <?php foreach ($tab['content'] as $documentation) : ?>
                                        <?php if (empty($documentation['file']['url'])) {
                                            continue;
                                        } ?>
                                        <a href="<?= esc_url($documentation['file']['url']) ?>" class="gkb-doc" target="_blank" rel="noopener">
                                            <span class="gkb-doc__icon">PDF</span>
                                            <span class="gkb-doc__name"><?= esc_html($documentation['name_file'] ?? '') ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif ($tab['id'] === 'specifications') : ?>
                                <div class="gkb-content"><?= Helpers::wrapTables($tab['content']) ?></div>
                            <?php else : ?>
                                <div class="gkb-content"><?= Helpers::wrapTables($tab['content']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </section>
<?php else : ?>
    <h2 style="font-family: 'Mark', sans-serif;">Модуль продукта</h2>
<?php endif; ?>
