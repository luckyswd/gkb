<?php

/*
Title: Модуль продуктов
Mode: preview
*/

use helpers\Helpers;

$helpers = new Helpers();
$fields = $helpers->get_field_multi_lang();
$nameTabs = $helpers->get_field_multi_lang('name_tabs', 'option');
$iconPDF = get_field('icon_pdf', 'option');
?>

<?php if (!is_admin()) : ?>
    <section class="product">
        <div class="container">
            <div class="product__header">
                <div class="product__slider-wrapper">
                    <?php if (!empty($fields['image_slider'])) : ?>
                        <div class="swiper product__slider-2">
                            <div class="swiper-wrapper">
                                <?php foreach ($fields['image_slider'] as $key => $slide) : ?>
                                    <div class="swiper-slide">
                                        <img src="<?= $slide['image']['url'] ?>" alt="<?= $slide['image']['title'] ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($fields['image_slider'])) : ?>
                        <div thumbsSlider="" class="swiper product__slider">
                            <div class="swiper-wrapper">
                                <?php foreach ($fields['image_slider'] as $key => $slide) : ?>
                                    <div class="swiper-slide">
                                        <img src="<?= $slide['image']['url'] ?>" alt="<?= $slide['image']['title'] ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="product__info">
                    <?php if (!empty($fields['title'])) : ?>
                        <h1 class="product__title">
                            <?= $fields['title'] ?>
                        </h1>
                    <?php endif; ?>
                    <?php if (!empty($fields['subtitle'])) : ?>
                        <h3 class="product__subtitle">
                            <?= $fields['subtitle'] ?>
                        </h3>
                    <?php endif; ?>
                    <?php if (!empty($fields['description'])) : ?>
                        <div class="product__description">
                            <?= $fields['description'] ?>
                        </div>
                    <?php endif; ?>
                    <div class="wrapper-buttons">
                        <?php if (!empty($fields['button_name'])) : ?>
                            <a href="/where-to-buy" class="product__button btn">
                                <?= $fields['button_name'] ?>
                            </a>
                        <?php endif; ?>
                        <a class="btn btn-form" href="javascript:;" data-fancybox="" data-src="#feedback-form">
                            <?= $helpers->getLang() === 'ru' ? 'Написать нам' : 'Write to us' ?>
                        </a>
                    </div>
                </div>
            </div>
            <div class="product__body">
                <div class="product__body-nav-tabs">
                    <?php if ($nameTabs['tab_1'] && $fields['features_and_benefits']) : ?>
                        <div class="product__body-nav-tab active">
                            <?= $nameTabs['tab_1'] ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($nameTabs['tab_2'] && $fields['specifications']) : ?>
                        <div class="product__body-nav-tab active">
                            <?= $nameTabs['tab_2'] ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($nameTabs['tab_3'] && $fields['equipment']) : ?>
                        <div class="product__body-nav-tab active">
                            <?= $nameTabs['tab_3'] ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($nameTabs['tab_4'] && $fields['documentation']) : ?>
                        <div class="product__body-nav-tab active">
                            <?= $nameTabs['tab_4'] ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="product__body-content">
                    <?php if ($fields['features_and_benefits']) : ?>
                        <div class="product__body-content-benefits active">
                            <?= $fields['features_and_benefits'] ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($fields['specifications']) : ?>
                        <div class="product__body-content-specifications">
                            <?= $fields['specifications'] ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($fields['equipment']) : ?>
                        <div class="product__body-content-equipment">
                            <?= $fields['equipment'] ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($fields['documentation'])) : ?>
                        <div class="product__body-content-documentations">
                            <?php foreach ($fields['documentation'] as $documentation) : ?>
                                <a href="<?= $documentation['file']['url'] ?>"
                                   class="product__body-content-documentation">
                                    <img src="<?= $iconPDF['url'] ?>" alt="<?= $iconPDF['title'] ?>">
                                    <?= ($documentation['name_file']) ?? '' ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php else: ?>
    <h2 style="font-family: 'Mark', sans-serif;">Модуль продукта</h2>
<?php endif; ?>