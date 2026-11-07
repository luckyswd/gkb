<?php
/**
 * Где купить в {области}: дилеры области из блока «Модуль дилеров»
 * страницы «Где купить?». Маршрут /where-to-buy/{slug}/ — seo\Endpoints.
 */

use seo\Context;
use seo\Regions;

$region = Context::region();
get_header();
$crumbs = Context::crumbs();
?>
<section class="gkb-seo-page gkb-region">
    <div class="container">
        <nav class="gkb-crumbs" aria-label="Хлебные крошки">
            <?php foreach ($crumbs as $i => [$name, $url]) : ?>
                <?php if ($i > 0) : ?><span>/</span><?php endif; ?>
                <?php if ($i < count($crumbs) - 1) : ?>
                    <a href="<?= esc_url($url) ?>"><?= esc_html($name) ?></a>
                <?php else : ?>
                    <span class="gkb-crumbs__current" aria-current="page"><?= esc_html($name) ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <h1 class="gkb-seo-page__title">Где купить котлы Житомир в <?= esc_html($region['prep']) ?></h1>

        <div class="gkb-region__grid">
            <?php foreach ($region['dealers'] as $dealer) : ?>
                <article class="gkb-card gkb-region__dealer">
                    <?php if ($dealer['name'] !== '') : ?>
                        <h2 class="gkb-region__name"><?= esc_html($dealer['name']) ?></h2>
                    <?php endif; ?>
                    <div class="gkb-region__address gkb-content"><?= wp_kses_post($dealer['address_html']) ?></div>
                    <?php if ($dealer['phones']) : ?>
                        <p class="gkb-region__phones">
                            <?php foreach ($dealer['phones'] as $phone) : ?>
                                <a href="tel:<?= esc_attr(preg_replace('/[^\d+]/', '', $phone)) ?>"><?= esc_html($phone) ?></a>
                            <?php endforeach; ?>
                        </p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="gkb-card gkb-seo-text gkb-content">
            <?= wp_kses_post(Regions::deliveryText($region)) ?>
        </div>

        <div class="gkb-card gkb-region__map">
            <?= do_shortcode('[mappress mapid="1"]') ?>
        </div>

        <?php $others = array_diff_key(Regions::all(), [$region['slug'] => true]); ?>
        <?php if ($others) : ?>
            <nav class="gkb-region__others" aria-label="Другие области">
                <span class="gkb-region__others-title">Дилеры в других областях:</span>
                <?php foreach ($others as $slug => $other) : ?>
                    <a class="gkb-btn gkb-btn--secondary gkb-btn--sm" href="<?= esc_url(Regions::url($slug)) ?>"><?= esc_html($other['name']) ?></a>
                <?php endforeach; ?>
                <a class="gkb-btn gkb-btn--sm" href="<?= esc_url(home_url('/where-to-buy/')) ?>">Все дилеры Беларуси</a>
            </nav>
        <?php endif; ?>
    </div>
</section>
<?php get_footer();
