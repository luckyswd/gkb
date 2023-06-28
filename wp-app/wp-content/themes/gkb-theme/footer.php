</main>
<?php

use helpers\Helpers;

$fields = (new Helpers)->get_field_multi_lang('footer_section', 'option');
$fieldsHeader = (new Helpers)->get_field_multi_lang('header_links', 'option');
$form = (new Helpers)->getFeedbackForm();
$categories = get_terms([
    'taxonomy' => 'product-category',
    'object_ids' => get_posts([
        'post_type' => 'products',
        'post_status' => 'publish',
        'fields' => 'ids',
        'posts_per_page' => -1,
    ]),
]);
?>
<footer id="footer" class="footer">
    <div class="modal-form" id="feedback-form">
        <div class="form-container">
            <?= $form ?>
        </div>
    </div>
    <div class="container">
        <div class="footer__body">
            <?php if ($fields['info']) : ?>
                <div class="footer__column-info">
                    <?= $fields['info'] ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($categories)) : ?>
                <div class="footer__categories">
                    <div class="footer__category">
                        <a href="">
                            <?= $fieldsHeader['all_products'] ?? '' ?>
                        </a>
                    </div>
                    <?php foreach ($categories as $category) : ?>
                        <div class="footer__category">
                            <a href="<?= get_term_link($category) ?>">
                                <?php if ((new Helpers)->getLang() == 'ru') : ?>
                                    <?= $category->name ?>
                                <?php elseif ((new Helpers)->getLang() == 'en') : ?>
                                    <?= get_field('name_en', $category) ?>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($fieldsHeader['links'])) : ?>
                <div class="footer__menu">
                    <?php foreach ($fieldsHeader['links'] as $link) : ?>
                        <?php if ($link['link']['url'] && $link['link']['title']) : ?>
                            <a href="<?= $link['link']['url'] ?>" target="<?= $link['link']['target'] ?>">
                                <?= $link['link']['title'] ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($fields['copyright']) : ?>
        <div class="footer__copyright">
            <?= '© 1988-' . date('Y') . ' ' . $fields['copyright'] ?>
        </div>
    <?php endif; ?>
</footer>
<?php wp_footer(); ?>
</body>
</html>