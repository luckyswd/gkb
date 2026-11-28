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
        <p class="gkb-partners__lead">Кто сделал этот сайт и какие бесплатные онлайн-сервисы мы советуем жителям Беларуси.</p>

        <?php foreach (Partners::SECTIONS as $section) : ?>
            <section class="gkb-partners__section">
                <h2 class="gkb-partners__heading"><?= esc_html($section['title']) ?></h2>
                <div class="gkb-partners__grid">
                    <?php foreach ($section['items'] as $item) : ?>
                        <article class="gkb-card gkb-partners__card">
                            <span class="gkb-badge gkb-badge--green"><?= esc_html($item['label']) ?></span>
                            <h3 class="gkb-partners__name">
                                <a href="<?= esc_url($item['url']) ?>" target="_blank" rel="noopener"><?= esc_html($item['name']) ?></a>
                            </h3>
                            <p class="gkb-partners__text"><?= esc_html($item['text']) ?></p>
                            <ul class="gkb-partners__links">
                                <?php foreach ($item['links'] as [$title, $href]) : ?>
                                    <li><a href="<?= esc_url($href) ?>" target="_blank" rel="noopener"><?= esc_html($title) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                            <a class="gkb-btn gkb-btn--secondary gkb-btn--sm gkb-partners__go" href="<?= esc_url($item['url']) ?>" target="_blank" rel="noopener">
                                Перейти на <?= esc_html($item['host']) ?> ↗
                            </a>
                        </article>
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
