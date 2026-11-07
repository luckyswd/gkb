<?php

namespace seo;

use helpers\Helpers;
use WP_Post;

/**
 * JSON-LD: один блок с @graph на каждой странице. Organization, WebSite,
 * WebPage, BreadcrumbList всегда; Product/AggregateOffer, ItemList, FAQPage,
 * Article и Store — по типу страницы. Значения совпадают с видимыми.
 */
class Schema
{
    /** FAQ, выведенные на странице шаблонами (для FAQPage). */
    private static array $faq = [];

    /** Списки товаров, выведенные на странице (для ItemList). */
    private static array $items = [];

    public static function register(): void
    {
        add_action('wp_head', [self::class, 'print'], 30);
        // Шаблон выводится после wp_head, поэтому FAQ и списки регистрируются
        // заранее теми же функциями, которые потом рисуют блоки.
    }

    public static function addFaq(array $rows): void
    {
        foreach ($rows as $row) {
            $q = trim(wp_strip_all_tags((string) ($row['question'] ?? '')));
            $a = trim((string) ($row['answer'] ?? ''));
            if ($q !== '' && $a !== '') {
                self::$faq[$q] = $a;
            }
        }
    }

    public static function addItems(array $posts): void
    {
        foreach ($posts as $p) {
            if ($p instanceof WP_Post) {
                self::$items[$p->ID] = $p;
            }
        }
    }

    private static function id(string $frag): string
    {
        return home_url('/') . '#' . $frag;
    }

    public static function organization(): array
    {
        $o = Org::get();
        $node = [
            '@type' => 'Organization',
            '@id' => self::id('organization'),
            'name' => $o['name'],
            'legalName' => $o['legal_name'],
            'alternateName' => ['GazKotelBel', 'Газкотелбел', 'gazkotelbel'],
            'url' => home_url('/'),
            'description' => 'Официальный представитель украинского производителя АТЕМ в Беларуси: котлы Житомир, газовые конвекторы и колонки ВПГ.',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $o['street'],
                'addressLocality' => $o['locality'],
                'addressRegion' => $o['region'],
                'postalCode' => $o['postcode'],
                'addressCountry' => 'BY',
            ],
            'areaServed' => ['@type' => 'Country', 'name' => 'Беларусь'],
            'taxID' => $o['unp'],
            'brand' => ['@type' => 'Brand', 'name' => 'Житомир'],
        ];
        if ($o['logo']) {
            $node['logo'] = ['@type' => 'ImageObject', '@id' => self::id('logo'), 'url' => $o['logo']];
            $node['image'] = ['@id' => self::id('logo')];
        }
        if ($o['phones']) {
            $node['telephone'] = $o['phones'][0];
            $node['contactPoint'] = array_map(static fn ($phone) => [
                '@type' => 'ContactPoint',
                'telephone' => $phone,
                'contactType' => 'sales',
                'areaServed' => 'BY',
                'availableLanguage' => ['ru', 'be'],
            ], $o['phones']);
        }
        if ($o['email']) {
            $node['email'] = $o['email'];
        }
        if ($o['socials']) {
            $node['sameAs'] = array_values($o['socials']);
        }
        return $node;
    }

    public static function graph(): array
    {
        $ctx = Context::get();
        $en = Context::lang() === 'en';
        $lang = $en ? 'en' : 'ru-BY';
        $url = Context::canonical() ?: home_url(add_query_arg([]));
        $title = wp_get_document_title();
        $desc = Meta::description();

        $graph = [self::organization()];

        $graph[] = [
            '@type' => 'WebSite',
            '@id' => self::id('website'),
            'url' => home_url('/'),
            'name' => Org::NAME,
            'alternateName' => 'GazKotelBel',
            'inLanguage' => 'ru-BY',
            'publisher' => ['@id' => self::id('organization')],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => ['@type' => 'EntryPoint', 'urlTemplate' => home_url('/catalog/') . '?search={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ];

        $pageType = 'WebPage';
        if (in_array($ctx['type'], ['catalog', 'category', 'articles', 'region'], true)) {
            $pageType = 'CollectionPage';
        } elseif ($ctx['slug'] === 'about' && $ctx['type'] === 'page') {
            $pageType = 'AboutPage';
        } elseif ($ctx['slug'] === 'contact' && $ctx['type'] === 'page') {
            $pageType = 'ContactPage';
        } elseif ($ctx['type'] === 'product') {
            $pageType = 'ItemPage';
        }

        $page = [
            '@type' => $pageType,
            '@id' => $url . '#webpage',
            'url' => $url,
            'name' => $title,
            'isPartOf' => ['@id' => self::id('website')],
            'about' => ['@id' => self::id('organization')],
            'inLanguage' => $lang,
        ];
        if ($desc !== '') {
            $page['description'] = $desc;
        }
        if ($ctx['object'] instanceof WP_Post) {
            $page['datePublished'] = get_post_time('c', true, $ctx['object']);
            $page['dateModified'] = get_post_modified_time('c', true, $ctx['object']);
        }
        $img = Meta::image();
        if ($img) {
            $page['primaryImageOfPage'] = ['@type' => 'ImageObject', 'url' => $img[0]];
        }

        $crumbs = Context::crumbs();
        if ($crumbs) {
            $list = [];
            foreach ($crumbs as $i => [$name, $link]) {
                $item = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => wp_strip_all_tags((string) $name)];
                if ($i < count($crumbs) - 1 && $link && !is_wp_error($link)) {
                    $item['item'] = $link;
                }
                $list[] = $item;
            }
            $graph[] = ['@type' => 'BreadcrumbList', '@id' => $url . '#breadcrumb', 'itemListElement' => $list];
            $page['breadcrumb'] = ['@id' => $url . '#breadcrumb'];
        }

        switch ($ctx['type']) {
            case 'product':
                $product = self::product($ctx['object'], $url);
                $graph[] = $product;
                $page['mainEntity'] = ['@id' => $product['@id']];
                break;

            case 'category':
            case 'catalog':
                $posts = self::$items ?: (array) ($GLOBALS['products'] ?? []);
                if ($posts) {
                    $page['mainEntity'] = self::itemList($posts, $url);
                }
                break;

            case 'region':
                $region = Context::region();
                if ($region) {
                    $page['mainEntity'] = self::stores($region, $url);
                }
                break;

            case 'article':
                $p = $ctx['object'];
                $article = [
                    '@type' => 'Article',
                    '@id' => $url . '#article',
                    'headline' => get_the_title($p),
                    'description' => $desc,
                    'datePublished' => get_post_time('c', true, $p),
                    'dateModified' => get_post_modified_time('c', true, $p),
                    'author' => ['@id' => self::id('organization')],
                    'publisher' => ['@id' => self::id('organization')],
                    'mainEntityOfPage' => ['@id' => $url . '#webpage'],
                    'inLanguage' => 'ru-BY',
                ];
                if ($img) {
                    $article['image'] = $img[0];
                }
                $graph[] = $article;
                break;
        }

        $graph[] = $page;

        if (self::$faq) {
            $faq = [];
            foreach (self::$faq as $q => $a) {
                $faq[] = [
                    '@type' => 'Question',
                    'name' => $q,
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => trim(wp_kses($a, ['p' => [], 'br' => [], 'ul' => [], 'ol' => [], 'li' => [], 'a' => ['href' => []], 'strong' => [], 'b' => [], 'em' => []]))],
                ];
            }
            $graph[] = ['@type' => 'FAQPage', '@id' => $url . '#faq', 'mainEntity' => $faq, 'isPartOf' => ['@id' => $url . '#webpage']];
        }

        return $graph;
    }

    public static function product(WP_Post $p, string $url): array
    {
        $helper = new Helpers();
        $images = [];
        foreach ($helper->getProductImageIds($p) as $id) {
            $src = wp_get_attachment_image_url($id, 'full');
            if ($src) {
                $images[] = $src;
            }
        }
        $cat = Context::productCategory($p);
        $description = trim(wp_strip_all_tags((string) ($helper->getProductDescription($p) ?: $helper->getProductDescriptionCatalog($p))));

        $node = [
            '@type' => 'Product',
            '@id' => $url . '#product',
            'name' => Context::productTitle($p),
            'url' => $url,
            'brand' => ['@type' => 'Brand', 'name' => 'АТЕМ'],
            'manufacturer' => ['@type' => 'Organization', 'name' => 'АТЕМ', 'address' => ['@type' => 'PostalAddress', 'addressCountry' => 'UA']],
        ];
        if ($description !== '') {
            $node['description'] = mb_substr(preg_replace('/\s+/u', ' ', $description), 0, 1000);
        }
        if ($images) {
            $node['image'] = $images;
        }
        if ($cat) {
            $node['category'] = Context::termName($cat);
        }

        $s = ProductData::summary($p->ID);
        if ($s['min_price'] !== null) {
            $node['offers'] = [
                '@type' => 'AggregateOffer',
                'priceCurrency' => 'BYN',
                'lowPrice' => $s['min_price'],
                'highPrice' => $s['max_price'],
                'offerCount' => $s['price_count'],
                'availability' => 'https://schema.org/InStock',
                'priceValidUntil' => Org::priceValidUntil(),
                'seller' => ['@id' => self::id('organization')],
                'url' => $url,
            ];
        }
        if ($s['power_min'] !== null) {
            $node['additionalProperty'] = [[
                '@type' => 'PropertyValue',
                'name' => 'Номинальная мощность',
                'minValue' => $s['power_min'],
                'maxValue' => $s['power_max'],
                'unitCode' => 'KWT',
            ]];
        }
        return $node;
    }

    public static function itemList(array $posts, string $url): array
    {
        $items = [];
        $i = 0;
        foreach ($posts as $p) {
            if (!$p instanceof WP_Post) {
                continue;
            }
            $items[] = [
                '@type' => 'ListItem',
                'position' => ++$i,
                'url' => get_permalink($p),
                'name' => Context::productTitle($p),
            ];
        }
        return ['@type' => 'ItemList', '@id' => $url . '#itemlist', 'numberOfItems' => count($items), 'itemListElement' => $items];
    }

    public static function stores(array $region, string $url): array
    {
        $items = [];
        foreach ($region['dealers'] as $i => $d) {
            $store = [
                '@type' => 'Store',
                'name' => $d['name'],
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $d['address_text'],
                    'addressRegion' => $region['name'],
                    'addressCountry' => 'BY',
                ],
            ];
            if (!empty($d['phones'])) {
                $store['telephone'] = $d['phones'][0];
            }
            if (!empty($d['site'])) {
                $store['url'] = $d['site'];
            }
            $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'item' => $store];
        }
        return ['@type' => 'ItemList', '@id' => $url . '#dealers', 'numberOfItems' => count($items), 'itemListElement' => $items];
    }

    public static function print(): void
    {
        if (is_admin()) {
            return;
        }
        self::prepare();
        $json = wp_json_encode(['@context' => 'https://schema.org', '@graph' => self::graph()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
        echo '<script type="application/ld+json">' . $json . "</script>\n";
    }

    /**
     * wp_head выполняется до шаблона, поэтому FAQ и списки товаров текущей
     * страницы собираются здесь из тех же источников, что используют шаблоны.
     */
    private static function prepare(): void
    {
        $ctx = Context::get();
        switch ($ctx['type']) {
            case 'category':
                self::addItems(\helpers\Helpers::getProductsByCategory($ctx['object']));
                self::addFaq(Content::faqRows($ctx['object']));
                break;
            case 'catalog':
                self::addItems(\helpers\Helpers::getProductsByCategory());
                break;
            case 'product':
            case 'page':
            case 'calculator':
            case 'article':
            case 'front':
                if ($ctx['object'] instanceof WP_Post) {
                    self::addFaq(Content::faqRows($ctx['object']));
                }
                break;
        }
    }
}
