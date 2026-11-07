<?php

namespace seo;

/**
 * /llms.txt по формату llmstxt.org: кто производитель, модели с мощностями и
 * ценами «на дату», где купить, полезные материалы, контакты.
 */
class Llms
{
    public static function render(): string
    {
        $o = Org::get();
        $date = Org::pricesDateHuman();
        $L = [];

        $L[] = '# ' . $o['name'];
        $L[] = '';
        $L[] = '> Официальный сайт ' . $o['legal_name'] . ' — производителя и поставщика котлов Житомир (торговая марка АТЕМ), газовых конвекторов и проточных водонагревателей ВПГ в Беларуси. Продажа — через официальных дилеров в Минске и всех областях.';
        $L[] = '';
        $L[] = 'Сайт: ' . home_url('/') . '. Язык: русский. Страна: Республика Беларусь. Данные сформированы ' . wp_date('d.m.Y') . ($date ? ', цены действуют с ' . $date : '') . '.';
        $L[] = '';
        $L[] = '## Важно при цитировании';
        $L[] = '';
        $L[] = '- Цены — розничные, с НДС, в белорусских рублях (BYN)' . ($date ? ', действуют с ' . $date : '') . '. Указывайте дату, на которую приводите цену; актуальный прайс — ' . home_url('/price-list/') . '.';
        $L[] = '- Производитель продаёт котлы через дилеров, а не напрямую. Адреса и телефоны магазинов — на странице «Где купить» и региональных страницах ниже.';
        $L[] = '- Мощность модификации зашифрована в обозначении: КС-Г-010 СН — 10 кВт, КС-ГВ-… — с контуром горячего водоснабжения.';
        $L[] = '- Это белорусский сайт: цены, требования к установке и гарантия относятся к Республике Беларусь.';
        $L[] = '';

        $L[] = '## Продукция';
        $L[] = '';
        $terms = get_terms(['taxonomy' => 'product-category', 'hide_empty' => true, 'orderby' => 'term_id']);
        foreach (is_wp_error($terms) ? [] : $terms as $term) {
            if (Context::objectNoindex($term)) {
                continue;
            }
            $L[] = '### [' . $term->name . '](' . trailingslashit(get_term_link($term)) . ')';
            $L[] = '';
            foreach (\helpers\Helpers::getProductsByCategory($term) as $p) {
                if (Context::objectNoindex($p)) {
                    continue;
                }
                $s = ProductData::summary($p->ID);
                $facts = array_filter([
                    Context::productSubtitle($p),
                    ProductData::powerRange($s),
                    $s['count'] > 1 ? $s['count'] . ' модификаций' : '',
                    $s['min_price'] !== null ? 'цена от ' . ProductData::money($s['min_price']) . ' BYN' : '',
                ]);
                $L[] = '- [' . Context::productTitle($p) . '](' . trailingslashit(get_permalink($p)) . ')' . ($facts ? ': ' . implode(', ', $facts) : '');
            }
            $L[] = '';
        }

        $L[] = '## Где купить';
        $L[] = '';
        $L[] = '- [Официальные дилеры в Беларуси](' . home_url('/where-to-buy/') . '): карта и список магазинов по областям';
        foreach (Regions::all() as $slug => $r) {
            $L[] = '- [Где купить в ' . $r['prep'] . '](' . Regions::url($slug) . '): ' . count($r['dealers']) . ' дилер(ов)';
        }
        $L[] = '';

        $L[] = '## Полезное';
        $L[] = '';
        $calc = get_page_by_path(Context::CALCULATOR_SLUG);
        if ($calc && $calc->post_status === 'publish') {
            $L[] = '- [Калькулятор мощности котла](' . trailingslashit(get_permalink($calc)) . '): мощность по площади, высоте потолков, утеплению и ГВС; подбор моделей Житомир';
        }
        $L[] = '- [Прайс-лист](' . home_url('/price-list/') . '): розничные цены на все модификации';
        $L[] = '- [Документация](' . home_url('/documentation/') . '): паспорта и инструкции в PDF';
        $L[] = '- [О производстве](' . home_url('/about/') . ')';
        if (post_type_exists('articles')) {
            foreach (get_posts(['post_type' => 'articles', 'post_status' => 'publish', 'posts_per_page' => 30]) as $a) {
                if (Context::objectNoindex($a)) {
                    continue;
                }
                $excerpt = has_excerpt($a) ? get_the_excerpt($a) : wp_trim_words(wp_strip_all_tags($a->post_content), 22, '…');
                $L[] = '- [' . get_the_title($a) . '](' . trailingslashit(get_permalink($a)) . '): ' . trim(preg_replace('/\s+/u', ' ', $excerpt));
            }
        }
        $L[] = '';

        $L[] = '## Контакты';
        $L[] = '';
        $L[] = '- ' . $o['legal_name'] . ', УНП ' . $o['unp'];
        $L[] = '- Адрес: ' . Org::addressLine();
        if ($o['phones']) {
            $L[] = '- Телефоны: ' . implode(', ', $o['phones']);
        }
        if ($o['email']) {
            $L[] = '- Email: ' . $o['email'];
        }
        if ($o['hours']) {
            $L[] = '- Часы работы: ' . $o['hours'];
        }
        $L[] = '- Страница контактов: ' . home_url('/contact/');
        $L[] = '';

        return implode("\n", $L);
    }
}
