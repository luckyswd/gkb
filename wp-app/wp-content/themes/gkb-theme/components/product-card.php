<?php

use helpers\Helpers;

$helper = new Helpers();

$link = get_post_permalink($product->ID);
$image = $helper->getProductSliderImage($product);
$dataFunctionality = $helper->getProductFunctionality($product);
$title = $helper->getProductTitle($product);
$subheadline = $helper->getProductDescriptionCatalog($product);

?>

<div class="product-card">
    <div class="product-left">
        <a href="<?= $link ?? '/' ?>">
            <picture>
                <img loading="lazy" src="<?= $image ?>" alt="product-image" width="140" height="190">
            </picture>
        </a>
    </div>
    <div class="product-right">
        <?php if (!empty($dataFunctionality)) : ?>
            <div class="product_icons">
                <?php foreach ($dataFunctionality as $functionality): ?>
                    <?php if ($functionality['icon']): ?>
                        <div class="product_icon_wrapper">
                            <div class="product_icon">
                                <picture>
                                    <img src="<?= $functionality['icon'] ?>" alt="product-icon" width="32" height="32">
                                </picture>
                            </div>
                            <div class="product_text"><?= $functionality['text'] ?? '' ?></div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <a href="<?= $link ?? '/' ?>" class="product-headline"><?= $title ?? '' ?></a>
        <p class="product-subheadline"><?= $subheadline ?? '' ?></p>
    </div>
</div>