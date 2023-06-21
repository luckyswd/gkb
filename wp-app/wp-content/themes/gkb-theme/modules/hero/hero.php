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
            <?php if (!empty($fields['image'])) : ?>
                <div class="hero__image">
                    <img src="<?= $fields['image']['url'] ?>" alt="<?= $fields['image']['title'] ?>">
                </div>
            <?php endif; ?>
            <?php if (!empty($fields['description'])) : ?>
                <div class="hero__description">
                    <?= $fields['description'] ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <h2 style="font-family: 'Mark', sans-serif;">Главный модуль</h2>
<?php endif; ?>