<?php
/** Статья раздела /stati/: оглавление, даты, подходящие модели, ссылки на калькулятор и «Где купить». */

use seo\Articles;
use seo\Content;
use seo\Context;

get_header();
the_post();
$article = get_post();
$crumbs = Context::crumbs();

ob_start();
the_content();
$body = ob_get_clean();
$toc = Articles::toc($body);
$related = Articles::relatedProducts($article);
?>
<article class="gkb-seo-page gkb-article">
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

        <div class="gkb-card gkb-article__card">
            <h1 class="gkb-seo-page__title"><?= esc_html(get_the_title()) ?></h1>
            <p class="gkb-article__dates">
                Опубликовано <time datetime="<?= esc_attr(get_the_date('c')) ?>"><?= esc_html(get_the_date('d.m.Y')) ?></time>
                <?php if (get_the_modified_date('Y-m-d') !== get_the_date('Y-m-d')) : ?>
                    · обновлено <time datetime="<?= esc_attr(get_the_modified_date('c')) ?>"><?= esc_html(get_the_modified_date('d.m.Y')) ?></time>
                <?php endif; ?>
            </p>

            <?php if (count($toc) >= 3) : ?>
                <nav class="gkb-article__toc" aria-label="Содержание">
                    <p class="gkb-article__toc-title">Содержание</p>
                    <ol>
                        <?php foreach ($toc as [$id, $text]) : ?>
                            <li><a href="#<?= esc_attr($id) ?>"><?= esc_html($text) ?></a></li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>

            <div class="gkb-content gkb-article__body"><?= \helpers\Helpers::wrapTables($body) ?></div>
        </div>

        <?php Content::renderFaq(Content::faqRows($article)); ?>

        <?php if ($related) : ?>
            <section class="gkb-similar" aria-labelledby="gkb-related-title">
                <h2 class="gkb-similar__title" id="gkb-related-title">Подходящие модели</h2>
                <div class="gkb-catalog__grid">
                    <?php foreach ($related as $product) : ?>
                        <?php include get_template_directory() . '/components/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <div class="gkb-card gkb-article__next">
            <?php if (Context::calculatorUrl()) : ?>
                <a class="gkb-btn gkb-btn--secondary" href="<?= esc_url(Context::calculatorUrl()) ?>">Рассчитать мощность котла</a>
            <?php endif; ?>
            <a class="gkb-btn gkb-btn--primary" href="<?= esc_url(home_url('/catalog/')) ?>">Каталог котлов Житомир</a>
            <a class="gkb-btn gkb-btn--secondary" href="<?= esc_url(home_url('/where-to-buy/')) ?>">Где купить котёл Житомир</a>
            <a class="gkb-btn gkb-btn--secondary" href="<?= esc_url(get_post_type_archive_link('articles')) ?>">Все статьи</a>
        </div>
    </div>
</article>
<?php get_footer();
