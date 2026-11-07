<?php

/*
Title: Модуль выбор продуктов
Mode: preview
*/

use helpers\Helpers;

$helper = new Helpers();
$fields = $helper->get_field_multi_lang();
$headerFields = $helper->get_field_multi_lang('header_links', 'option');
$allProductsLabel = $headerFields['all_products']
    ?? ($helper->getLang() === 'en' ? 'All products' : 'Вся продукция');

?>

<?php if (!is_admin()) : ?>
    <section class="scramble">
        <div class="container">
            <div class="scramble_wrapper">
                <div class="scramble__head">
                    <div class="scramble__intro">
                        <h1 class="headline"><?= esc_html(wp_strip_all_tags((string) ($fields['headline'] ?? ''))) ?></h1>
                        <p class="subheadline"><?= $fields['subheadline'] ?? '' ?></p>
                    </div>
                    <a href="<?= esc_url(home_url('/catalog')) ?>" class="gkb-btn gkb-btn--sm scramble__all">
                        <?= esc_html($allProductsLabel) ?>
                    </a>
                </div>
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
    <h2 style="font-family: 'Mark', sans-serif;">Модуль выбор продуктов</h2>
<?php endif; ?>