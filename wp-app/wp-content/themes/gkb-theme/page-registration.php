<?php
//$helpers = new \helpers\Helpers();
//$helpers->isLoginUser();
get_header();
?>

<div class="registration-page">
    <?= do_shortcode("[wppb-register]"); ?>
</div>

<?php
get_footer();
?>
