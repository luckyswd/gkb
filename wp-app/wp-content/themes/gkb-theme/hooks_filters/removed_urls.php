<?php

/*
 * Редиректы со снятых с витрины URL.
 * Ключ — путь без ведущего и замыкающего слеша, значение — куда уводим.
 * Следующие снятия товаров и категорий дописываются сюда.
 */
function removed_urls_map(): array
{
    return [
        'products/arbat' => '/catalog/',
        'product-category/gorelochnoe-ustroistvo' => '/catalog/',
        // ВПГ-20ТМ снят с производства (11.2026) — уводим к остальным колонкам.
        'products/hsv-20tm' => '/product-category/geysers/',
    ];
}

// Редирект выполняет seo\Redirects::early() — вместе с остальными 301 SEO-слоя.
