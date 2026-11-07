<?php
/** Список статей /stati/. */

use seo\Context;

get_header();
$crumbs = Context::crumbs();
?>
<section class="gkb-seo-page gkb-articles">
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

        <h1 class="gkb-seo-page__title">Статьи о газовых котлах и отоплении дома</h1>

        <?php if (have_posts()) : ?>
            <div class="gkb-articles__grid">
                <?php while (have_posts()) : the_post(); ?>
                    <a class="gkb-card gkb-articles__item" href="<?= esc_url(get_permalink()) ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <?= \seo\Perf::img((int) get_post_thumbnail_id(), get_the_title(), '(max-width: 900px) 100vw, 420px') ?>
                        <?php endif; ?>
                        <span class="gkb-articles__title"><?= esc_html(get_the_title()) ?></span>
                        <span class="gkb-articles__excerpt"><?= esc_html(wp_trim_words(get_the_excerpt(), 26, '…')) ?></span>
                        <time class="gkb-articles__date" datetime="<?= esc_attr(get_the_modified_date('c')) ?>"><?= esc_html(get_the_modified_date('d.m.Y')) ?></time>
                    </a>
                <?php endwhile; ?>
            </div>
            <?php the_posts_pagination(['mid_size' => 1]); ?>
        <?php else : ?>
            <div class="gkb-card"><p>Статьи скоро появятся.</p></div>
        <?php endif; ?>
    </div>
</section>
<?php get_footer();
