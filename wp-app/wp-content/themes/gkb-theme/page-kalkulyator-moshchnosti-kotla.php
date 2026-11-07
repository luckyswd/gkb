<?php
/**
 * Калькулятор мощности котла: страница WordPress со slug
 * kalkulyator-moshchnosti-kotla. Текст страницы из редактора выводится под
 * калькулятором, FAQ — из поля «Частые вопросы».
 */

use seo\Calculator;
use seo\Content;
use seo\Context;
use seo\ProductData;

get_header();
the_post();

[$values, $errors] = Calculator::input(wp_unslash($_GET));
$result = ($values && !$errors) ? Calculator::result($values) : null;
$v = $values ?: ['area' => '', 'height' => Calculator::BASE_HEIGHT, 'insulation' => 'medium', 'dhw' => false];
$crumbs = Context::crumbs();
?>
<section class="gkb-seo-page gkb-calc">
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

        <div class="gkb-calc__layout">
            <form class="gkb-card gkb-calc__form" method="get" action="<?= esc_url(get_permalink()) ?>#raschet" novalidate
                  toolname="calculate_boiler_power" toolautosubmit
                  tooldescription="Рассчитывает мощность газового котла для частного дома в Беларуси по площади, высоте потолков, утеплению и потребности в горячей воде. Возвращает рекомендуемую мощность в кВт и подходящие модели котлов Житомир с ценой от производителя и ссылками. Ничего не отправляет владельцу сайта.">
                <div class="gkb-calc__field<?= isset($errors['area']) ? ' gkb-field--error' : '' ?>">
                    <label class="gkb-label" for="calc-area">Отапливаемая площадь, м²</label>
                    <input class="gkb-field" id="calc-area" name="area" type="number" inputmode="decimal" min="10" max="1000" step="1" required
                           value="<?= esc_attr((string) $v['area']) ?>" placeholder="например, 150"
                           toolparamdescription="Отапливаемая площадь дома в квадратных метрах, от 10 до 1000.">
                    <?php if (isset($errors['area'])) : ?><p class="gkb-field-error"><?= esc_html($errors['area']) ?></p><?php endif; ?>
                </div>
                <div class="gkb-calc__field<?= isset($errors['height']) ? ' gkb-field--error' : '' ?>">
                    <label class="gkb-label" for="calc-height">Высота потолков, м</label>
                    <input class="gkb-field" id="calc-height" name="height" type="number" inputmode="decimal" min="2.2" max="4" step="0.1" required
                           value="<?= esc_attr((string) $v['height']) ?>"
                           toolparamdescription="Высота потолков в метрах, от 2.2 до 4. Если неизвестна — 2.7.">
                    <?php if (isset($errors['height'])) : ?><p class="gkb-field-error"><?= esc_html($errors['height']) ?></p><?php endif; ?>
                </div>
                <fieldset class="gkb-calc__field">
                    <legend class="gkb-label">Утеплённость дома</legend>
                    <?php foreach (Calculator::INSULATION as $key => [$q, $label]) : ?>
                        <label class="gkb-calc__radio">
                            <input type="radio" name="insulation" value="<?= esc_attr($key) ?>" <?= checked($v['insulation'], $key, false) ?>
                                   toolparamdescription="Утеплённость дома: good — хорошая (утеплённые стены, стеклопакеты), medium — средняя, poor — слабая (старый дом без утепления).">
                            <span><?= esc_html(mb_strtoupper(mb_substr($label, 0, 1)) . mb_substr($label, 1)) ?></span>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <label class="gkb-calc__check">
                    <input type="checkbox" name="dhw" value="1" <?= checked($v['dhw'], true, false) ?>
                           toolparamdescription="1 — котёл должен ещё и греть воду (двухконтурный, ГВС); пусто — только отопление.">
                    <span>Нужна горячая вода (ГВС)</span>
                </label>
                <button class="gkb-btn gkb-btn--primary gkb-btn--block" type="submit">Рассчитать</button>
            </form>

            <div class="gkb-card gkb-calc__result" id="raschet" aria-live="polite" data-calc-result>
                <?php if ($result) : ?>
                    <p class="gkb-calc__kw">
                        Рекомендуемая мощность котла: <strong data-calc-kw><?= esc_html(Calculator::fmt($result['kw'])) ?>&nbsp;кВт</strong>
                    </p>
                    <p class="gkb-calc__explain">
                        Для дома <?= esc_html(Calculator::fmt($values['area'])) ?>&nbsp;м² с потолками <?= esc_html(Calculator::fmt($values['height'])) ?>&nbsp;м,
                        утеплённость — <?= esc_html(Calculator::INSULATION[$values['insulation']][1]) ?><?= $values['dhw'] ? ', с горячим водоснабжением' : '' ?>.
                        Запас 15&nbsp;% уже учтён.
                    </p>
                    <?php if ($result['models']) : ?>
                        <h2 class="gkb-calc__models-title">Подходящие котлы Житомир</h2>
                        <ul class="gkb-calc__models">
                            <?php foreach ($result['models'] as $m) : ?>
                                <li>
                                    <a href="<?= esc_url(get_permalink($m['post'])) ?>"><?= esc_html(Context::productTitle($m['post'])) ?></a>
                                    — <?= esc_html($m['row']['code']) ?>, <?= esc_html(Calculator::fmt($m['row']['power_kw'])) ?>&nbsp;кВт<?php if ($m['row']['price_byn'] !== null) : ?>, <?= esc_html(ProductData::money($m['row']['price_byn'])) ?>&nbsp;BYN<?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <a class="gkb-btn gkb-btn--secondary gkb-btn--sm" href="<?= esc_url(home_url('/where-to-buy/')) ?>">Где купить</a>
                    <?php else : ?>
                        <p>Подходящей модели в каталоге нет — <a href="#feedback-form">напишите нам</a>, подберём решение.</p>
                    <?php endif; ?>
                <?php else : ?>
                    <p class="gkb-calc__placeholder">Введите площадь дома и нажмите «Рассчитать» — покажем мощность и подходящие модели.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="gkb-card gkb-seo-text gkb-content">
            <h2>Как считается мощность</h2>
            <p>Мощность котла&nbsp;= площадь × (высота потолков ÷ 2,7) × удельная мощность × 1,15.</p>
            <ul>
                <li>Удельная мощность: 0,08&nbsp;кВт на м² для хорошо утеплённого дома, 0,10&nbsp;— для среднего, 0,13&nbsp;— для дома без утепления.</li>
                <li>1,15&nbsp;— запас на сильные морозы и остывший дом.</li>
                <li>Если котёл греет ещё и воду, добавляется 20&nbsp;%, но не меньше 3&nbsp;кВт.</li>
            </ul>
            <p>Это оценка для выбора модели. Точный теплотехнический расчёт для проекта газификации делает проектная организация.</p>
            <?php the_content(); ?>
        </div>

        <?php Content::renderFaq(Content::faqRows(get_post())); ?>
    </div>
</section>
<?php get_footer();
