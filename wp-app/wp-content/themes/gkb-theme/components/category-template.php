<?php

use helpers\Helpers;

$helper = new Helpers();
$categories = get_terms([
    'taxonomy' => 'product-category',
    'orderby' => 'term_id',
    'order' => 'ASC',
    'hide_empty' => true,
]);

?>

<div class="page-catalog">
    <div class="container">
        <div class="catalog-wrapper">
            <?php if (!empty($categories)) : ?>
                <div class="catalog-left">
                    <?php foreach ($categories as $category): ?>
                        <?php
                        $lang = $helper->getLang();
                        if ($lang === 'ru') {
                            $categoryTitle = $category->name;
                        }

                        if ($lang === 'en') {
                            $categoryTitle = get_field('name_en', $category);
                        }

                        $categoryProducts = Helpers::getProductsByCategory($category);
                        $categoryLink = sprintf('%s/%s/%s', home_url(), $category->taxonomy, $category->slug);
                        ?>
                        <?php if (!empty($categoryProducts)) : ?>
                            <div class="catalog-categories">
                                <a href="<?= $categoryLink ?>"
                                   class="category-title <?= $currentCategory == $category ? 'active' : '' ?>">
                                    <?= $categoryTitle ?? '' ?>
                                </a>
                                <?php foreach ($categoryProducts as $categoryProduct) : ?>
                                    <?php
                                    $productLink = sprintf('%s/%s/%s', home_url(), $categoryProduct->post_type, $categoryProduct->post_name);
                                    ?>
                                    <a href="<?= $productLink ?>" class="product-title">
                                        <?= $helper->getProductTitle($categoryProduct) ?? '' ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div>
                <?php if (!empty($search)) : ?>
                    <div class="search-catalog">
                        <p><?= $helper->getLang() === 'ru' ? 'Поиск по каталогу: ' : 'Catalog search: ' ?> <?= $search ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($products)) : ?>
                    <div class="catalog-right">
                        <?php foreach ($products as $product) : ?>
                            <?php include 'product-card.php' ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="not-found"><?= $helper->getLang() === 'ru' ? 'Ничего не найдено' : 'Nothing found' ?>
                        😔</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>