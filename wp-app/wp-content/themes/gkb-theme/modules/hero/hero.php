<?php

/*
Title: Главный модуль
Mode: preview
*/

use helpers\Helpers;
$fields = (new Helpers)->get_field_multi_lang();
?>
<?php if (!is_admin()) : ?>
    <section class="hero">
        <div class="container">
            <?php if (!is_front_page() && get_queried_object() instanceof WP_Post) : ?>
                <h1 class="hero__title gkb-seo-page__title"><?= esc_html(get_the_title(get_queried_object())) ?></h1>
            <?php endif; ?>
            <?php if (!empty($fields['image'])) : ?>
                <div class="hero__image">
                    <?php \helpers\Helpers::getPictureImage($fields['image'], null, null, '(max-width: 1440px) calc(100vw - 32px), 1400px', true); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($fields['description'])) : ?>
                <div class="hero__description gkb-content">
                    <?= Helpers::wrapTables($fields['description'] ?? '') ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <h2 style="font-family: 'Mark', sans-serif;">Главный модуль</h2>
<?php endif; ?>