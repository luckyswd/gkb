<?php

/*
Title: Прайс лист модуль
Mode: preview
*/

use helpers\Helpers;
$fields = (new Helpers)->get_field_multi_lang();
?>

<?php if (!is_admin()) : ?>
    <section class="price-list">
        <div class="container">
            <h1 class="price-list-headline"><?= $fields['headline'] ?? '' ?></h1>
            <p class="price-list-subheadline"><?= $fields['subheadline'] ?></p>
            <p class="price-list-text"><?= $fields['text'] ?></p>
        </div>
    </section>
<?php else: ?>
    <h2 style="font-family: 'Mark', sans-serif;">Прайс лист модуль</h2>
<?php endif; ?>