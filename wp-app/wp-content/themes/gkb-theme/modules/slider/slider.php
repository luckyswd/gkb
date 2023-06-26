<?php

/*
Title: Слайдер модуль
Mode: preview
*/

use helpers\Helpers;

$fields = (new Helpers)->get_field_multi_lang();

?>

<?php if (!is_admin()) : ?>
    <?php if (!empty($fields['slider'])) : ?>
        <section class="slider">
            <div class="container">
                <div class="swiper gallery-swiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($fields['slider'] as $slide) : ?>
                            <?php
                            $product = $slide['product'];
                            if ($product) {
                                $link = sprintf('%s/%s', $product->post_type, $product->post_name);
                            } else {
                                $link = '';
                            }

                            ?>
                            <a href="/<?= $link ?>" class="swiper-slide">
                                <?= \helpers\Helpers::getPictureImage($slide['image'], 1400, 415); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </section>
    <?php endif; ?>
<?php else: ?>
    <h2 style="font-family: 'Mark', sans-serif;">Слайдер модуль</h2>
<?php endif; ?>