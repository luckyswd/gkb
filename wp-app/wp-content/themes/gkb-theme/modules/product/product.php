<?php

/*
Title: Product module
Mode: preview
*/

use helpers\Helpers;

$fields = (new Helpers)->get_field_multi_lang('en');
?>

<?php if (!is_admin()) : ?>
    <section class="product">
        <div class="container">

        </div>
    </section>
<?php else: ?>
    <h2 style="font-family: 'Mark', sans-serif;">Product module</h2>
<?php endif; ?>