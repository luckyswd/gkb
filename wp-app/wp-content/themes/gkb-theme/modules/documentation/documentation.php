<?php

/*
Title: Модуль документации
Mode: preview
*/

use helpers\Helpers;

$fields = (new Helpers)->get_field_multi_lang();
$products = get_posts(
    [
        'post_type' => 'products',
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => -1,
    ]
);
$iconPDF = get_field('icon_pdf', 'option');

?>
<?php if (!is_admin()) : ?>
    <section class="documentation">
        <div class="container">
            <?php if (!empty($fields['title'])) : ?>
                <h1 class="documentation__title">
                    <?= $fields['title'] ?>
                </h1>
            <?php endif; ?>
            <?php if (!empty($products)) : ?>
                <div class="documentation__products">
                    <?php foreach ($products as $product) : ?>
                        <?php
                        $files = (new Helpers)->getProductDocumentation($product);
                        ?>
                        <?php if (!empty($files)) : ?>
                            <div class="documentation__product">
                                <h3 class="documentation__product-title">
                                    <?= (new Helpers)->getProductTitle($product) ?>
                                </h3>
                                <div class="documentation__product-files">
                                    <?php foreach ($files as $file) : ?>
                                        <div class="documentation__product-file">
                                            <a href="<?= $file['file'] ?>"
                                               class="product__body-content-documentation">
                                                <img src="<?= $iconPDF['url'] ?>" alt="<?= $iconPDF['title'] ?>">
                                                <?= ($file['name_file']) ?? '' ?>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <h2 style="font-family: 'Mark', sans-serif;">Модуль документации</h2>
<?php endif; ?>