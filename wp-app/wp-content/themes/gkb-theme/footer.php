</main>
<?php

use helpers\Helpers;

$helper = new Helpers();
$lang = $helper->getLang();
$fields = $helper->get_field_multi_lang('footer_section', 'option');
$fieldsHeader = $helper->get_field_multi_lang('header_links', 'option');
$form = $helper->getFeedbackForm();
$logo = get_field('logo', 'option');
$socials = get_field('socials', 'option');
$categories = get_terms([
    'taxonomy' => 'product-category',
    'object_ids' => get_posts([
        'post_type' => 'products',
        'post_status' => 'publish',
        'fields' => 'ids',
        'posts_per_page' => -1,
    ]),
]);

$allProductsLabel = $fieldsHeader['all_products'] ?? ($lang === 'en' ? 'All products' : 'Вся продукция');
$writeToUsLabel = $lang === 'en' ? 'Write to us' : 'Написать нам';
$pagesLabel = $lang === 'en' ? 'Our pages' : 'Наши страницы';
$categoriesLabel = $lang === 'en' ? 'Our categories' : 'Наши категории';

$termName = static function ($term) use ($lang): string {
    if ($lang === 'en') {
        $en = get_field('name_en', $term);
        if (!empty($en)) {
            return $en;
        }
    }
    return $term->name;
};
?>
<footer id="footer" class="gkb-footer">
    <div class="container">
        <div class="gkb-footer__grid">

            <section class="gkb-card gkb-footer__form" id="feedback-form">
                <h2 class="gkb-footer__title"><?= esc_html($writeToUsLabel) ?></h2>
                <?= $form ?>
            </section>

            <div class="gkb-card gkb-footer__links">
                <?php if (!empty($fieldsHeader['links'])) : ?>
                    <div class="gkb-footer__col">
                        <span class="gkb-footer__col-title"><?= esc_html($pagesLabel) ?></span>
                        <?php foreach ($fieldsHeader['links'] as $menuItem) : ?>
                            <?php if (empty($menuItem['link']['url']) || empty($menuItem['link']['title'])) {
                                continue;
                            } ?>
                            <a href="<?= esc_url($menuItem['link']['url']) ?>" target="<?= esc_attr($menuItem['link']['target'] ?? '') ?>">
                                <?= esc_html($menuItem['link']['title']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($categories)) : ?>
                    <div class="gkb-footer__col">
                        <span class="gkb-footer__col-title"><?= esc_html($categoriesLabel) ?></span>
                        <a href="<?= esc_url(home_url('/catalog')) ?>"><?= esc_html($allProductsLabel) ?></a>
                        <?php foreach ($categories as $category) : ?>
                            <a href="<?= esc_url(get_term_link($category)) ?>"><?= esc_html($termName($category)) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="gkb-footer__col gkb-footer__col--contacts">
                    <?php if (!empty($fields['info'])) : ?>
                        <div class="gkb-footer__info"><?= $fields['info'] ?></div>
                    <?php endif; ?>

                    <?php if (!empty($socials)) : ?>
                        <div class="gkb-footer__socials">
                            <?php foreach ($socials as $social) : ?>
                                <?php if (empty($social['url']) || empty($social['icon'])) {
                                    continue;
                                } ?>
                                <a href="<?= esc_url($social['url']) ?>" class="gkb-btn gkb-btn--icon"
                                   title="<?= esc_attr($social['icon']['title'] ?? '') ?>" rel="noopener" target="_blank">
                                    <img src="<?= esc_url($social['icon']['url']) ?>" alt="<?= esc_attr($social['icon']['title'] ?? '') ?>" width="22" height="22">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="gkb-footer__bottom">
            <?php if (!empty($logo)) : ?>
                <img src="<?= esc_url($logo['url']) ?>" alt="<?= esc_attr($logo['title'] ?? '') ?>" width="140" height="32">
            <?php endif; ?>
            <?php if (!empty($fields['copyright'])) : ?>
                <span class="gkb-footer__copyright"><?= esc_html('© 1988-' . date('Y') . ' ' . $fields['copyright']) ?></span>
            <?php endif; ?>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
