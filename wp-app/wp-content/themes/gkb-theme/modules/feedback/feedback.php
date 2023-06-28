<?php

/*
Title: Обратная связь модуль
Mode: preview
*/

use helpers\Helpers;
$fields = (new Helpers)->get_field_multi_lang();
?>

<?php if (!is_admin()) : ?>
    <section class="feedback">
        <div class="container">
            <div class="feedback-wrapper">
                <h2 class="feedback-headline"><?= $fields['headline'] ?? '' ?></h2>
                <p class="feedback-subheadline"><?= $fields['subheadline'] ?? '' ?></p>
                <a class="btn feedback-btn" href="javascript:;" data-fancybox="" data-src="#feedback-form">
                    <?= $fields['btn_name'] ?>
                </a>
            </div>
        </div>
    </section>
<?php else: ?>
    <h2 style="font-family: 'Mark', sans-serif;">Обратная связь модуль</h2>
<?php endif; ?>