<?php

/*
Title: Модуль продуктов
Mode: preview
*/

use helpers\Helpers;

$fields = (new Helpers)->get_field_multi_lang();

?>

<?php if (!is_admin()) : ?>
    <section class="scramble">
        <div class="container">
            <div class="scramble_wrapper">
                <h2 class="headline">
                    <a href="/catalog">
                        <?= $fields['headline'] ?? '' ?>
                    </a>
                </h2>
                <p class="subheadline"><?= $fields['subheadline'] ?? '' ?></p>
                <div class="scramble__products">
                    <?php if ($fields['products']) : ?>
                        <?php foreach ($fields['products'] as $product) : ?>
                            <?php include get_template_directory() . '/components/product-card.php' ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php else: ?>
    <h2 style="font-family: 'Mark', sans-serif;">Модуль продуктов</h2>
<?php endif; ?>