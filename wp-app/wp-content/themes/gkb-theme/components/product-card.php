<?php

use helpers\Helpers;

$helper = new Helpers();

$link = get_post_permalink($product->ID);
$image = $helper->getProductSliderImage($product);
$dataFunctionality = $helper->getProductFunctionality($product);
$title = $helper->getProductTitle($product);
$subheadline = $helper->getProductDescriptionCatalog($product);

?>

<div class="gkb-product-card">
    <div class="gkb-product-card__pic">
        <?php if ($image) : ?>
            <img loading="lazy" src="<?= esc_url($image) ?>" alt="<?= esc_attr($title ?? '') ?>" width="240" height="320">
        <?php endif; ?>
    </div>

    <div class="gkb-product-card__body">
        <?php if (!empty($dataFunctionality)) : ?>
            <div class="gkb-ficons">
                <?php foreach ($dataFunctionality as $functionality) : ?>
                    <?php if (empty($functionality['icon'])) {
                        continue;
                    } ?>
                    <button type="button" class="gkb-ficon gkb-tip-host"
                            aria-label="<?= esc_attr($functionality['text'] ?? '') ?>">
                        <span class="gkb-ficon__sprite<?= $functionality['is_sprite'] ? '' : ' is-single' ?>">
                            <img class="gkb-ficon__base" src="<?= esc_url($functionality['icon']) ?>" alt="" aria-hidden="true">
                            <?php if ($functionality['is_sprite']) : ?>
                                <img class="gkb-ficon__hover" src="<?= esc_url($functionality['icon']) ?>" alt="" aria-hidden="true">
                            <?php endif; ?>
                        </span>
                        <?php if (!empty($functionality['text'])) : ?>
                            <span class="gkb-tip" role="tooltip"><?= esc_html($functionality['text']) ?></span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="gkb-product-card__text">
            <a href="<?= esc_url($link ?: home_url('/')) ?>" class="gkb-product-card__name"><?= esc_html($title ?? '') ?></a>
            <?php if (!empty($subheadline)) : ?>
                <p class="gkb-product-card__sub"><?= esc_html($subheadline) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
