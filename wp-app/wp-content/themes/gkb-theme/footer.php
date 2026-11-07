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
$writeToUsText = $lang === 'en'
    ? 'Ask about boilers, choosing a model or where to buy — we will reply by e-mail.'
    : 'Спросите о котлах, подборе модели или где купить — ответим на почту.';
$pagesLabel = $lang === 'en' ? 'Our pages' : 'Наши страницы';
$categoriesLabel = $lang === 'en' ? 'Our categories' : 'Наши категории';

/* В поле «Общая информация» редактор держит часы работы и заодно ссылку-иконку
   на Instagram. В блоке с часами иконке не место — переносим её к остальным
   соцсетям, ссылка и картинка при этом остаются теми же. */
$infoHtml = $fields['info'] ?? '';
$infoIcons = [];
if ($infoHtml !== '') {
    $pull = static function (string $pattern) use (&$infoHtml, &$infoIcons): void {
        $infoHtml = preg_replace_callback($pattern, static function ($m) use (&$infoIcons) {
            $infoIcons[] = $m[1];
            return '';
        }, $infoHtml) ?? $infoHtml;
    };
    $pull('#<p>\s*(<a\b[^>]*>\s*<img\b[^>]*>\s*</a>)\s*</p>#i');
    $pull('#(<a\b[^>]*>\s*<img\b[^>]*>\s*</a>)#i');
}

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

<section class="gkb-cta" id="feedback-form">
    <div class="container">
        <div class="gkb-card gkb-cta__card">
            <div class="gkb-cta__intro">
                <h2 class="gkb-cta__title"><?= esc_html($writeToUsLabel) ?></h2>
                <p class="gkb-cta__text"><?= esc_html($writeToUsText) ?></p>
            </div>
            <div class="gkb-cta__form"><?= $form ?></div>
        </div>
    </div>
</section>

<footer id="footer" class="gkb-footer">
    <div class="container gkb-footer__inner">
        <div class="gkb-footer__brand">
            <?php if (!empty($logo)) : ?>
                <a href="<?= esc_url(home_url('/')) ?>" class="gkb-footer__logo">
                    <img src="<?= esc_url($logo['url']) ?>" alt="<?= esc_attr($logo['title'] ?? '') ?>" width="140" height="40">
                </a>
            <?php endif; ?>

            <?php if (trim(strip_tags($infoHtml)) !== '') : ?>
                <div class="gkb-footer__info"><?= $infoHtml ?></div>
            <?php endif; ?>

            <?php if (!empty($socials) || !empty($infoIcons)) : ?>
                <div class="gkb-footer__socials">
                    <?php foreach (($socials ?: []) as $social) : ?>
                        <?php if (empty($social['url']) || empty($social['icon'])) {
                            continue;
                        } ?>
                        <a href="<?= esc_url($social['url']) ?>" title="<?= esc_attr($social['icon']['title'] ?? '') ?>" rel="noopener" target="_blank">
                            <img src="<?= esc_url($social['icon']['url']) ?>" alt="<?= esc_attr($social['icon']['title'] ?? '') ?>" width="22" height="22">
                        </a>
                    <?php endforeach; ?>
                    <?php foreach ($infoIcons as $icon) : ?>
                        <?= $icon ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($fieldsHeader['links'])) : ?>
            <nav class="gkb-footer__col" aria-label="<?= esc_attr($pagesLabel) ?>">
                <span class="gkb-footer__col-title"><?= esc_html($pagesLabel) ?></span>
                <?php foreach ($fieldsHeader['links'] as $menuItem) : ?>
                    <?php if (empty($menuItem['link']['url']) || empty($menuItem['link']['title'])) {
                        continue;
                    } ?>
                    <a href="<?= esc_url($menuItem['link']['url']) ?>" target="<?= esc_attr($menuItem['link']['target'] ?? '') ?>">
                        <?= esc_html($menuItem['link']['title']) ?>
                    </a>
                <?php endforeach; ?>
                <?php if ($lang !== 'en') : ?>
                    <?php $calcPage = get_page_by_path(\seo\Context::CALCULATOR_SLUG); ?>
                    <?php if ($calcPage && $calcPage->post_status === 'publish') : ?>
                        <a href="<?= esc_url(get_permalink($calcPage)) ?>">Калькулятор мощности</a>
                    <?php endif; ?>
                    <?php if (wp_count_posts('articles')->publish ?? 0) : ?>
                        <a href="<?= esc_url(get_post_type_archive_link('articles')) ?>">Статьи</a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

        <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
            <nav class="gkb-footer__col" aria-label="<?= esc_attr($categoriesLabel) ?>">
                <span class="gkb-footer__col-title"><?= esc_html($categoriesLabel) ?></span>
                <a href="<?= esc_url(home_url('/catalog')) ?>"><?= esc_html($allProductsLabel) ?></a>
                <?php foreach ($categories as $category) : ?>
                    <a href="<?= esc_url(get_term_link($category)) ?>"><?= esc_html($termName($category)) ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    </div>

    <div class="gkb-footer__bottom">
        <div class="container gkb-footer__bottom-inner">
            <?php if (!empty($fields['copyright'])) : ?>
                <span class="gkb-footer__copyright"><?= esc_html('© 1988-' . date('Y') . ' ' . $fields['copyright']) ?></span>
            <?php endif; ?>
            <a href="#header" class="gkb-footer__up"><?= esc_html($lang === 'en' ? 'Back to top ↑' : 'Наверх ↑') ?></a>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
<?php if (home_url() == 'https://gazkotelbel.com' ) :?>
<!-- ShefTools: аналитика и cookie-баннер (uptime работает на стороне ShefTools) -->
<script defer src="https://tools.shefcode.tech/all.js"
        data-site="gazkotelbel"
        data-modules="analytics,cookie"></script>
<?php endif; ?>
</body>
</html>
