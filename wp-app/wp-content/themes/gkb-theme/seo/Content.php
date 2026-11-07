<?php

namespace seo;

use helpers\Helpers;
use WP_Post;
use WP_Term;

/**
 * Контентные блоки SEO-слоя, общие для шаблонов: FAQ, вводный текст
 * категории, таблица модификаций, похожие модели, блок «О компании»,
 * мощность в карточке.
 */
class Content
{
    /** Пары вопрос–ответ объекта. FAQ только на русском: на EN блок не выводится. */
    public static function faqRows($object): array
    {
        if (!function_exists('get_field') || Context::lang() === 'en') {
            return [];
        }
        $rows = $object instanceof WP_Term ? get_field('faq', $object) : get_field('faq', $object instanceof WP_Post ? $object->ID : $object);
        $out = [];
        foreach ((array) $rows as $row) {
            if (is_array($row) && trim((string) ($row['question'] ?? '')) !== '' && trim(wp_strip_all_tags((string) ($row['answer'] ?? ''))) !== '') {
                $out[] = $row;
            }
        }
        return $out;
    }

    public static function renderFaq(array $rows, string $title = 'Частые вопросы'): void
    {
        if (!$rows) {
            return;
        }
        ?>
        <section class="gkb-card gkb-faq" aria-labelledby="gkb-faq-title">
            <h2 class="gkb-faq__title" id="gkb-faq-title"><?= esc_html($title) ?></h2>
            <div class="gkb-faq__list">
                <?php foreach ($rows as $i => $row) : ?>
                    <details class="gkb-faq__item"<?= $i === 0 ? ' open' : '' ?>>
                        <summary class="gkb-faq__q"><?= esc_html(trim(wp_strip_all_tags((string) $row['question']))) ?></summary>
                        <div class="gkb-faq__a gkb-content"><?= wp_kses_post(wpautop((string) $row['answer'])) ?></div>
                    </details>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }

    /** Вводный текст и FAQ категории под сеткой товаров. */
    public static function renderTermBottom(?WP_Term $term): void
    {
        if (Context::lang() === 'en') {
            return;
        }
        if ($term) {
            $intro = function_exists('get_field') ? (string) get_field('intro_text', $term) : '';
            if (trim(wp_strip_all_tags($intro)) !== '') {
                echo '<section class="gkb-card gkb-seo-text gkb-content">' . wp_kses_post($intro) . '</section>';
            }
            self::renderFaq(self::faqRows($term));
            return;
        }

        // общий каталог: короткий текст о компании и ссылки на все категории
        $intro = function_exists('get_field') ? (string) get_field('catalog_intro_text', 'option') : '';
        $terms = get_terms(['taxonomy' => 'product-category', 'hide_empty' => true, 'orderby' => 'term_id']);
        echo '<section class="gkb-card gkb-seo-text gkb-content">';
        if (trim(wp_strip_all_tags($intro)) !== '') {
            echo wp_kses_post($intro);
        } else {
            echo '<p>ГазКотелБел — официальный представитель украинского производителя АТЕМ в Беларуси. В каталоге — газовые дымоходные и турбированные котлы, электро-газовые котлы с ТЭНом, твердотопливные и универсальные котлы, газовые конвекторы и проточные водонагреватели ВПГ. Цены — розничные, с НДС, купить технику можно у официальных дилеров во всех областях страны.</p>';
        }
        if (!is_wp_error($terms) && $terms) {
            echo '<ul class="gkb-seo-text__links">';
            foreach ($terms as $t) {
                echo '<li><a href="' . esc_url(get_term_link($t)) . '">' . esc_html($t->name) . ' Житомир</a></li>';
            }
            if (Context::calculatorUrl()) {
                echo '<li><a href="' . esc_url(Context::calculatorUrl()) . '">Калькулятор мощности котла</a></li>';
            }
            echo '</ul>';
        }
        echo '</section>';
    }

    /** Строка «от N BYN · 7–45 кВт» для карточки и героя. */
    public static function priceLine(WP_Post $product, string $class = 'gkb-price'): string
    {
        $s = ProductData::summary($product->ID);
        $parts = [];
        if ($s['min_price'] !== null) {
            $parts[] = '<span class="' . esc_attr($class) . '__value">' . (Context::lang() === 'en' ? 'from ' : 'от ') . esc_html(ProductData::money($s['min_price'])) . "\u{00A0}BYN</span>";
        }
        if ($s['power_min'] !== null) {
            $parts[] = '<span class="' . esc_attr($class) . '__power">' . esc_html(ProductData::powerRange($s)) . '</span>';
        }
        return $parts ? '<p class="' . esc_attr($class) . '">' . implode('', $parts) . '</p>' : '';
    }

    /** Таблица «Модификации и цены» на странице товара. */
    public static function renderModifications(WP_Post $product): void
    {
        $s = ProductData::summary($product->ID);
        if (!$s['rows']) {
            return;
        }
        $en = Context::lang() === 'en';
        $hasArea = (bool) array_filter(array_column($s['rows'], 'area_m2'));
        $hasDhw = (bool) array_filter(array_column($s['rows'], 'dhw'));
        $hasPrice = $s['min_price'] !== null;
        ?>
        <section class="gkb-card gkb-mods" id="modifications" aria-labelledby="gkb-mods-title">
            <h2 class="gkb-mods__title" id="gkb-mods-title"><?= esc_html($en ? 'Models' : 'Модификации') ?></h2>
            <div class="gkb-table-scroll">
                <table>
                    <thead>
                    <tr>
                        <th scope="col"><?= esc_html($en ? 'Model' : 'Модификация') ?></th>
                        <th scope="col"><?= esc_html($en ? 'Power, kW' : 'Мощность, кВт') ?></th>
                        <?php if ($hasArea) : ?><th scope="col"><?= esc_html($en ? 'Heated area, m²' : 'Площадь отопления, м²') ?></th><?php endif; ?>
                        <?php if ($hasDhw) : ?><th scope="col"><?= esc_html($en ? 'Hot water' : 'ГВС') ?></th><?php endif; ?>
                        <?php if ($hasPrice) : ?><th scope="col"><?= esc_html($en ? 'Retail price incl. VAT, BYN' : 'Цена с НДС, BYN') ?></th><?php endif; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($s['rows'] as $row) : ?>
                        <tr>
                            <th scope="row"><?= esc_html($row['code']) ?></th>
                            <td><?= $row['power_kw'] !== null ? esc_html(rtrim(rtrim(number_format($row['power_kw'], 1, ',', ''), '0'), ',')) : '—' ?></td>
                            <?php if ($hasArea) : ?><td><?= $row['area_m2'] !== null ? esc_html(($en ? 'up to ' : 'до ') . ProductData::money($row['area_m2'])) : '—' ?></td><?php endif; ?>
                            <?php if ($hasDhw) : ?><td><?= esc_html($row['dhw'] ? ($en ? 'yes' : 'есть') : '—') ?></td><?php endif; ?>
                            <?php if ($hasPrice) : ?><td><?= $row['price_byn'] !== null ? esc_html(ProductData::money($row['price_byn'])) : '—' ?></td><?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="gkb-mods__note">
                <?php if ($hasPrice && Org::pricesDateHuman() !== '') : ?>
                    <?= esc_html(($en ? 'Retail prices incl. VAT, valid from ' : 'Розничные цены с НДС, действительны с ') . Org::pricesDateHuman() . '.') ?>
                <?php endif; ?>
                <a href="<?= esc_url(home_url('/price-list/')) ?>"><?= esc_html($en ? 'Price list' : 'Цены — в прайс-листе') ?></a>
                ·
                <a href="<?= esc_url(home_url('/where-to-buy/')) ?>"><?= esc_html($en ? 'Where to buy' : 'Где купить') ?></a>
                <?php if (Context::calculatorUrl()) : ?>
                    ·
                    <a href="<?= esc_url(Context::calculatorUrl()) ?>"><?= esc_html($en ? 'Boiler power calculator' : 'Калькулятор мощности') ?></a>
                <?php endif; ?>
            </p>
        </section>
        <?php
    }

    /** 2–4 похожие модели: ручной список или та же категория + пересечение мощностей. */
    public static function similar(WP_Post $product, int $limit = 4): array
    {
        $manual = function_exists('get_field') ? (array) get_field('similar_products', $product->ID) : [];
        $manual = array_values(array_filter($manual, static fn ($p) => $p instanceof WP_Post && $p->ID !== $product->ID && $p->post_status === 'publish'));
        if ($manual) {
            return array_slice($manual, 0, $limit);
        }

        $cat = Context::productCategory($product);
        $all = get_posts(['post_type' => 'products', 'post_status' => 'publish', 'posts_per_page' => -1, 'exclude' => [$product->ID], 'orderby' => 'menu_order title', 'order' => 'ASC']);
        $mine = ProductData::summary($product->ID);
        $same = [];
        $other = [];
        foreach ($all as $p) {
            $pc = Context::productCategory($p);
            if ($cat && $pc && $pc->term_id === $cat->term_id) {
                $same[] = $p;
                continue;
            }
            $s = ProductData::summary($p->ID);
            if ($mine['power_min'] !== null && $s['power_min'] !== null
                && $s['power_min'] <= $mine['power_max'] && $s['power_max'] >= $mine['power_min']) {
                $other[] = $p;
            }
        }
        return array_slice(array_merge($same, $other), 0, $limit);
    }

    public static function renderSimilar(WP_Post $product): void
    {
        $items = self::similar($product);
        if (count($items) < 2) {
            return;
        }
        $en = Context::lang() === 'en';
        ?>
        <section class="gkb-similar" aria-labelledby="gkb-similar-title">
            <h2 class="gkb-similar__title" id="gkb-similar-title"><?= esc_html($en ? 'Similar models' : 'Похожие модели') ?></h2>
            <div class="gkb-catalog__grid">
                <?php foreach ($items as $product) : ?>
                    <?php include get_template_directory() . '/components/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }

    /** Блок «О компании» на главной. */
    public static function renderAbout(): void
    {
        if (Context::lang() === 'en') {
            return;
        }
        $f = static fn ($name, $default) => (function_exists('get_field') && ($v = get_field($name, 'option'))) ? $v : $default;
        $title = $f('about_title', 'ГазКотелБел — официальный представитель котлов Житомир в Беларуси');
        $text = $f('about_text', '<p>Котлы Житомир выпускает украинская компания АТЕМ: первые образцы отопительной техники вышли в 1988 году, с тех пор выпущено и работает более 2 млн единиц. Котлы АТЕМ — единственные украинские дымоходные котлы, которые наравне с Vaillant, Bosch и Immergas включены в программу энергосбережения IQenergy Европейского банка реконструкции и развития.</p><p>ГазКотелБел — официальный представитель АТЕМ в Беларуси. Мы поставляем газовые напольные дымоходные и турбированные котлы, двухконтурные и электро-газовые котлы, котлы парапетного типа, твердотопливные и универсальные котлы (газ / твёрдое топливо), газовые конвекторы и проточные водонагреватели ВПГ.</p>');
        $points = $f('about_points', [
            ['title' => 'Официальный представитель АТЕМ', 'text' => 'Оригинальная техника и официальная гарантия'],
            ['title' => 'Сервис и запчасти', 'text' => 'Автоматика, термопары и комплектующие в наличии'],
            ['title' => 'Дилеры по всей Беларуси', 'text' => 'Минск и все области: доставка и монтаж'],
            ['title' => 'С 1988 года', 'text' => 'Более 2 млн котлов и колонок АТЕМ в работе'],
        ]);
        ?>
        <section class="gkb-about">
            <div class="container">
                <div class="gkb-card gkb-about__card">
                    <h2 class="gkb-about__title"><?= esc_html($title) ?></h2>
                    <div class="gkb-about__text gkb-content"><?= wp_kses_post($text) ?></div>
                    <?php if (!empty($points)) : ?>
                        <ul class="gkb-about__points">
                            <?php foreach ($points as $point) : ?>
                                <li class="gkb-about__point">
                                    <strong><?= esc_html($point['title'] ?? '') ?></strong>
                                    <span><?= esc_html($point['text'] ?? '') ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <div class="gkb-about__links">
                        <?php if (Context::calculatorUrl()) : ?>
                            <a class="gkb-btn gkb-btn--secondary gkb-btn--sm" href="<?= esc_url(Context::calculatorUrl()) ?>">Рассчитать мощность котла</a>
                        <?php endif; ?>
                        <a class="gkb-btn gkb-btn--primary gkb-btn--sm" href="<?= esc_url(home_url('/catalog/')) ?>">Вся продукция</a>
                        <a class="gkb-btn gkb-btn--secondary gkb-btn--sm" href="<?= esc_url(home_url('/where-to-buy/')) ?>">Где купить</a>
                        <a class="gkb-btn gkb-btn--secondary gkb-btn--sm" href="<?= esc_url(home_url('/about/')) ?>">О производстве</a>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }

    /** Добавляет alt изображениям из визуального редактора, у которых его нет. */
    public static function fillAlt(string $html, string $alt): string
    {
        if ($alt === '' || stripos($html, '<img') === false) {
            return $html;
        }
        return preg_replace_callback('#<img\b(?![^>]*\balt=)([^>]*)>#i', static fn ($m) => '<img alt="' . esc_attr($alt) . '"' . $m[1] . '>', $html) ?? $html;
    }

    /** alt изображения: из медиатеки, иначе из запасного текста. */
    public static function alt(int $attachmentId, string $fallback): string
    {
        $alt = trim((string) get_post_meta($attachmentId, '_wp_attachment_image_alt', true));
        return $alt !== '' ? $alt : $fallback;
    }
}
