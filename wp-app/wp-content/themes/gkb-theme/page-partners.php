<?php
/**
 * Страница «Партнёры»: WordPress-страница со slug partners. Карточки берутся
 * из seo\Partners, текст из редактора (если есть) выводится под ними.
 */

use seo\Context;
use seo\Partners;

get_header();
the_post();

$crumbs = Context::crumbs();
$extra = trim((string) get_the_content());
?>
<section class="gkb-seo-page gkb-partners">
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

        <h1 class="gkb-seo-page__title"><?= esc_html(get_the_title()) ?></h1>
        <p class="gkb-partners__lead">Компании и сервисы, которым мы доверяем и которые советуем.</p>

        <?php foreach (Partners::SECTIONS as $section) : ?>
            <section class="gkb-partners__section">
                <h2 class="gkb-partners__heading"><?= esc_html($section['title']) ?></h2>
                <div class="gkb-partners__grid">
                    <?php foreach ($section['items'] as $item) : ?>
                        <a class="gkb-card gkb-partners__card" href="<?= esc_url($item['url']) ?>" target="_blank" rel="noopener">
                            <span class="gkb-partners__head">
                                <span class="gkb-partners__logo">
                                    <img src="<?= esc_url(Partners::logoUrl($item['logo'])) ?>" alt="" width="40" height="40" loading="lazy" decoding="async">
                                </span>
                                <span class="gkb-partners__who">
                                    <span class="gkb-partners__name"><?= esc_html($item['name']) ?></span>
                                    <span class="gkb-partners__host"><?= esc_html($item['host']) ?></span>
                                </span>
                                <span class="gkb-partners__arrow" aria-hidden="true">
                                    <svg viewBox="0 0 16 16" width="16" height="16"><path d="M5 11 11 5M6 5h5v5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </span>
                            <span class="gkb-partners__title"><?= esc_html($item['title']) ?></span>
                            <span class="gkb-partners__text"><?= esc_html($item['text']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <?php if ($extra !== '') : ?>
            <div class="gkb-content gkb-partners__extra"><?php the_content(); ?></div>
        <?php endif; ?>
    </div>
</section>
<?php
get_footer();
