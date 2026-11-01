<?php

/*
 * Редиректы со снятых с витрины URL.
 * Ключ — путь без ведущего и замыкающего слеша, значение — куда уводим.
 * Следующие снятия товаров и категорий дописываются сюда.
 */
function removed_urls_map(): array
{
    return [
        'products/arbat' => '/catalog',
        'product-category/gorelochnoe-ustroistvo' => '/catalog',
    ];
}

function redirect_removed_urls(): void
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

    if (!is_string($path)) {
        return;
    }

    $path = trim(strtolower(rawurldecode($path)), '/');
    $map = removed_urls_map();

    if (!isset($map[$path])) {
        return;
    }

    wp_redirect(home_url($map[$path]), 301);
    exit;
}

add_action('template_redirect', 'redirect_removed_urls');
