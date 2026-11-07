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
    ];
}

// Редирект выполняет seo\Redirects::early() — вместе с остальными 301 SEO-слоя.
