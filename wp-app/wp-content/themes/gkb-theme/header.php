<!doctype html>
<html lang="ru">

<head>
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title><?php wp_title('«', true, 'right'); ?><?php bloginfo('name'); ?></title>
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>"/>
    <?php wp_head(); ?>
</head>

<?php

use helpers\Helpers;

$lang = (new Helpers)->getLang();
$short_logo = get_field('short_logo', 'option');
$logo = get_field('logo', 'option');
$socials = get_field('socials', 'option');
$fields = (new Helpers)->get_field_multi_lang('header_links', 'option');
$categories = get_terms([
    'taxonomy' => 'product-category',
    'orderby' => 'term_id',
    'order' => 'ASC',
    'hide_empty' => true,
]);
?>

<body>
<?php if (!empty($socials)) : ?>
    <div class="section-socials">
        <?php foreach ($socials as $social) : ?>
            <?php if ($social['url'] && !empty($social['icon'])) : ?>
                <div class="section-social">
                    <a href="<?= $social['url'] ?>">
                        <img src="<?= $social['icon']['url'] ?>" alt="<?= $social['icon']['title'] ?>">
                    </a>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<header id="header">
    <div class="container">
        <div class="header__main">
            <?php if (!empty($short_logo)) : ?>
                <a href="<?= home_url() ?>" class="header__short-logo">
                    <img src="<?= $short_logo['url'] ?>" alt="<?= $short_logo['title'] ?>">
                </a>
            <?php endif; ?>
            <div class="header__burger-close">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="32" height="32"
                     viewBox="0 0 32 32">
                    <path d="M31.708 25.708c-0-0-0-0-0-0l-9.708-9.708 9.708-9.708c0-0 0-0 0-0 0.105-0.105 0.18-0.227 0.229-0.357 0.133-0.356 0.057-0.771-0.229-1.057l-4.586-4.586c-0.286-0.286-0.702-0.361-1.057-0.229-0.13 0.048-0.252 0.124-0.357 0.228 0 0-0 0-0 0l-9.708 9.708-9.708-9.708c-0-0-0-0-0-0-0.105-0.104-0.227-0.18-0.357-0.228-0.356-0.133-0.771-0.057-1.057 0.229l-4.586 4.586c-0.286 0.286-0.361 0.702-0.229 1.057 0.049 0.13 0.124 0.252 0.229 0.357 0 0 0 0 0 0l9.708 9.708-9.708 9.708c-0 0-0 0-0 0-0.104 0.105-0.18 0.227-0.229 0.357-0.133 0.355-0.057 0.771 0.229 1.057l4.586 4.586c0.286 0.286 0.702 0.361 1.057 0.229 0.13-0.049 0.252-0.124 0.357-0.229 0-0 0-0 0-0l9.708-9.708 9.708 9.708c0 0 0 0 0 0 0.105 0.105 0.227 0.18 0.357 0.229 0.356 0.133 0.771 0.057 1.057-0.229l4.586-4.586c0.286-0.286 0.362-0.702 0.229-1.057-0.049-0.13-0.124-0.252-0.229-0.357z"/>
                </svg>
            </div>
            <div class="header__burger-icon">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000"
                     height="800px" width="800px" version="1.1" id="Layer_1" viewBox="0 0 150 150" xml:space="preserve">
                <g id="XMLID_240_">
                    <path id="XMLID_241_"
                          d="M15,30h120c8.284,0,15-6.716,15-15s-6.716-15-15-15H15C6.716,0,0,6.716,0,15S6.716,30,15,30z"/>
                    <path id="XMLID_242_"
                          d="M135,60H15C6.716,60,0,66.716,0,75s6.716,15,15,15h120c8.284,0,15-6.716,15-15S143.284,60,135,60z"/>
                    <path id="XMLID_243_"
                          d="M135,120H15c-8.284,0-15,6.716-15,15s6.716,15,15,15h120c8.284,0,15-6.716,15-15S143.284,120,135,120z"/>
                </g>
                </svg>
            </div>
            <?php if (!empty($logo)) : ?>
                <a href="<?= home_url() ?>" class="header__logo-mobile">
                    <img src="<?= $logo['url'] ?>" alt="<?= $logo['title'] ?>">
                </a>
            <?php endif; ?>
            <?php if (!empty($fields['links'])) : ?>
                <div class="header__menu">
                    <?php foreach ($fields['links'] as $link) : ?>
                        <?php if ($link['link']['url'] && $link['link']['title']) : ?>
                            <a href="<?= $link['link']['url'] ?>" target="<?= $link['link']['target'] ?>">
                                <?= $link['link']['title'] ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="header__search">
                    <input class="header__search-input" type="text" placeholder="<?= $fields['placeholder_search'] ?>">
                    <div class="header__search-close">
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="32" height="32"
                             viewBox="0 0 32 32">
                            <path d="M31.708 25.708c-0-0-0-0-0-0l-9.708-9.708 9.708-9.708c0-0 0-0 0-0 0.105-0.105 0.18-0.227 0.229-0.357 0.133-0.356 0.057-0.771-0.229-1.057l-4.586-4.586c-0.286-0.286-0.702-0.361-1.057-0.229-0.13 0.048-0.252 0.124-0.357 0.228 0 0-0 0-0 0l-9.708 9.708-9.708-9.708c-0-0-0-0-0-0-0.105-0.104-0.227-0.18-0.357-0.228-0.356-0.133-0.771-0.057-1.057 0.229l-4.586 4.586c-0.286 0.286-0.361 0.702-0.229 1.057 0.049 0.13 0.124 0.252 0.229 0.357 0 0 0 0 0 0l9.708 9.708-9.708 9.708c-0 0-0 0-0 0-0.104 0.105-0.18 0.227-0.229 0.357-0.133 0.355-0.057 0.771 0.229 1.057l4.586 4.586c0.286 0.286 0.702 0.361 1.057 0.229 0.13-0.049 0.252-0.124 0.357-0.229 0-0 0-0 0-0l9.708-9.708 9.708 9.708c0 0 0 0 0 0 0.105 0.105 0.227 0.18 0.357 0.229 0.356 0.133 0.771 0.057 1.057-0.229l4.586-4.586c0.286-0.286 0.362-0.702 0.229-1.057-0.049-0.13-0.124-0.252-0.229-0.357z"/>
                        </svg>
                    </div>
                </div>
            <?php endif; ?>
            <div class="header__right">
                <div class="header__search-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="32" height="32" viewBox="0 0 32 32">
                        <path d="M31.008 27.231l-7.58-6.447c-0.784-0.705-1.622-1.029-2.299-0.998 1.789-2.096 2.87-4.815 2.87-7.787 0-6.627-5.373-12-12-12s-12 5.373-12 12 5.373 12 12 12c2.972 0 5.691-1.081 7.787-2.87-0.031 0.677 0.293 1.515 0.998 2.299l6.447 7.58c1.104 1.226 2.907 1.33 4.007 0.23s0.997-2.903-0.23-4.007zM12 20c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z"/>
                    </svg>
                </div>
                <div class="header__login">
                    <?php if (!is_user_logged_in()) : ?>
                        <a href="/login">
                            <?= $fields['login'] ?? '' ?>
                        </a>
                    <?php else: ?>
                        <div class="user-logout">
                            <?= $fields['logout'] ?? '' ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>


        </div>
    </div>
    <div class="header__category-container">
        <div class="container">
            <?php if (!empty($categories)) : ?>
                <div class="header__categories header__menu">
                    <div class="header__category">
                        <a href="/catalog">
                            <?= $fields['all_products'] ?? '' ?>
                        </a>
                    </div>
                    <?php foreach ($categories as $category) : ?>
                        <?php
                        $productsByCategory = get_posts([
                            'post_type' => 'products',
                            'posts_per_page' => -1,
                            'tax_query' => [
                                [
                                    'taxonomy' => 'product-category',
                                    'field' => 'term_id',
                                    'terms' => $category->term_id,
                                ]
                            ]
                        ]);
                        ?>
                        <div class="header__category">
                            <a href="<?= get_term_link($category) ?>">
                                <?php if ($lang == 'ru') : ?>
                                    <?= $category->name ?>
                                <?php elseif ($lang == 'en') : ?>
                                    <?= get_field('name_en', $category) ?>
                                <?php endif; ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="800px" height="800px"
                                     viewBox="0 0 24 24">
                                    <rect x="0" fill="none" width="24" height="24"/>
                                    <g>
                                        <path d="M7 10l5 5 5-5"/>
                                    </g>
                                </svg>
                            </a>
                            <?php if (!empty($productsByCategory)) : ?>
                                <div class="header__category-products">
                                    <?php foreach ($productsByCategory as $product) : ?>
                                        <a href="<?= get_permalink($product) ?>" class="header__category-product">
                                            <?= (new Helpers())->getProductTitle($product) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <div class="header__mobile">
        <h3>
            <?= $fields['name_menu_page'] ?? '' ?>
        </h3>
        <?php if (!empty($fields['links'])) : ?>
            <div class="header__mobile-links">
                <?php foreach ($fields['links'] as $link) : ?>
                    <?php if ($link['link']['url'] && $link['link']['title']) : ?>
                        <a class="btn" href="<?= $link['link']['url'] ?>" target="<?= $link['link']['target'] ?>">
                            <?= $link['link']['title'] ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <h3>
            <?= $fields['name_menu_category'] ?? '' ?>
        </h3>
        <?php if (!empty($categories)) : ?>
            <div class="header__mobile-categories">
                <div class="header__mobile-category">
                    <a class="btn" href="">
                        <?= $fields['all_products'] ?? '' ?>
                    </a>
                </div>
                <?php foreach ($categories as $category) : ?>
                    <div class="header__mobile-category">
                        <a class="btn" href="<?= get_term_link($category) ?>">
                            <?php if ($lang == 'ru') : ?>
                                <?= $category->name ?>
                            <?php elseif ($lang == 'en') : ?>
                                <?= get_field('name_en', $category) ?>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="header__mobile-login">
            <?php if (!is_user_logged_in()) : ?>
                <a class="btn " href="/login">
                    <?= $fields['login'] ?? '' ?>
                </a>
            <?php else: ?>
                <div class=" btn user-logout">
                    <?= $fields['logout'] ?? '' ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>
<main id="main" class="main" data-page-id="<?= get_queried_object_id() ?>">
    <div class="select-lang">
        <div class="lang-wrapper">
            <div class="select-lang__item" data-lang="en">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"
                     id="Layer_1"
                     x="0px" y="0px" viewBox="0 0 55.2 38.4" style="enable-background:new 0 0 55.2 38.4"
                     xml:space="preserve"><style
                            type="text/css">.st0 {
                            fill: #FEFEFE;
                        }

                        .st1 {
                            fill: #C8102E;
                        }

                        .st2 {
                            fill: #012169;
                        }</style>
                    <g>
                        <path class="st0"
                              d="M2.87,38.4h49.46c1.59-0.09,2.87-1.42,2.87-3.03V3.03c0-1.66-1.35-3.02-3.01-3.03H3.01 C1.35,0.01,0,1.37,0,3.03v32.33C0,36.98,1.28,38.31,2.87,38.4L2.87,38.4z"/>
                        <polygon class="st1"
                                 points="23.74,23.03 23.74,38.4 31.42,38.4 31.42,23.03 55.2,23.03 55.2,15.35 31.42,15.35 31.42,0 23.74,0 23.74,15.35 0,15.35 0,23.03 23.74,23.03"/>
                        <path class="st2" d="M33.98,12.43V0h18.23c1.26,0.02,2.34,0.81,2.78,1.92L33.98,12.43L33.98,12.43z"/>
                        <path class="st2"
                              d="M33.98,25.97V38.4h18.35c1.21-0.07,2.23-0.85,2.66-1.92L33.98,25.97L33.98,25.97z"/>
                        <path class="st2"
                              d="M21.18,25.97V38.4H2.87c-1.21-0.07-2.24-0.85-2.66-1.94L21.18,25.97L21.18,25.97z"/>
                        <path class="st2" d="M21.18,12.43V0H2.99C1.73,0.02,0.64,0.82,0.21,1.94L21.18,12.43L21.18,12.43z"/>
                        <polygon class="st2" points="0,12.8 7.65,12.8 0,8.97 0,12.8"/>
                        <polygon class="st2" points="55.2,12.8 47.51,12.8 55.2,8.95 55.2,12.8"/>
                        <polygon class="st2" points="55.2,25.6 47.51,25.6 55.2,29.45 55.2,25.6"/>
                        <polygon class="st2" points="0,25.6 7.65,25.6 0,29.43 0,25.6"/>
                        <polygon class="st1" points="55.2,3.25 36.15,12.8 40.41,12.8 55.2,5.4 55.2,3.25"/>
                        <polygon class="st1" points="19.01,25.6 14.75,25.6 0,32.98 0,35.13 19.05,25.6 19.01,25.6"/>
                        <polygon class="st1" points="10.52,12.81 14.78,12.81 0,5.41 0,7.55 10.52,12.81"/>
                        <polygon class="st1" points="44.63,25.59 40.37,25.59 55.2,33.02 55.2,30.88 44.63,25.59"/>
                    </g></svg>
            </div>
            <div class="select-lang__item" data-lang="ru">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"
                     id="Layer_1"
                     x="0px" y="0px" viewBox="0 0 55.32 38.52" style="enable-background:new 0 0 55.32 38.52"
                     xml:space="preserve"><style type="text/css">.st0 {
                            fill: #FFFFFF;
                        }

                        .st1 {
                            fill: #D52B1E;
                        }

                        .st2 {
                            fill: #0039A6;
                        }

                        .st3 {
                            fill: none;
                            stroke: #CCCCCC;
                            stroke-width: 0.1199;
                            stroke-miterlimit: 2.6131;
                        }</style>
                    <g>
                        <path class="st0"
                              d="M3.09,0.06h49.13c1.67,0,3.03,1.36,3.03,3.03v16.17H0.06V3.09C0.06,1.42,1.42,0.06,3.09,0.06L3.09,0.06z"/>
                        <path class="st1"
                              d="M0.06,19.26h55.2v16.17c0,1.67-1.36,3.03-3.03,3.03H3.09c-1.67,0-3.03-1.37-3.03-3.03V19.26L0.06,19.26z"/>
                        <polygon class="st2" points="0.06,12.86 55.26,12.86 55.26,25.66 0.06,25.66 0.06,12.86"/>
                        <path class="st3"
                              d="M3.09,0.06h49.13c1.67,0,3.03,1.36,3.03,3.03v32.33c0,1.67-1.36,3.03-3.03,3.03H3.09 c-1.67,0-3.03-1.37-3.03-3.03V3.09C0.06,1.42,1.42,0.06,3.09,0.06L3.09,0.06z"/>
                    </g></svg>
            </div>
        </div>

        <div class="current-lang">
            <?php if ($lang === 'en'): ?>
               <div class="select-lang__item" data-lang="en">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"
                     id="Layer_1"
                     x="0px" y="0px" viewBox="0 0 55.2 38.4" style="enable-background:new 0 0 55.2 38.4"
                     xml:space="preserve"><style
                            type="text/css">.st0 {
                            fill: #FEFEFE;
                        }

                        .st1 {
                            fill: #C8102E;
                        }

                        .st2 {
                            fill: #012169;
                        }</style>
                    <g>
                        <path class="st0"
                              d="M2.87,38.4h49.46c1.59-0.09,2.87-1.42,2.87-3.03V3.03c0-1.66-1.35-3.02-3.01-3.03H3.01 C1.35,0.01,0,1.37,0,3.03v32.33C0,36.98,1.28,38.31,2.87,38.4L2.87,38.4z"/>
                        <polygon class="st1"
                                 points="23.74,23.03 23.74,38.4 31.42,38.4 31.42,23.03 55.2,23.03 55.2,15.35 31.42,15.35 31.42,0 23.74,0 23.74,15.35 0,15.35 0,23.03 23.74,23.03"/>
                        <path class="st2" d="M33.98,12.43V0h18.23c1.26,0.02,2.34,0.81,2.78,1.92L33.98,12.43L33.98,12.43z"/>
                        <path class="st2"
                              d="M33.98,25.97V38.4h18.35c1.21-0.07,2.23-0.85,2.66-1.92L33.98,25.97L33.98,25.97z"/>
                        <path class="st2"
                              d="M21.18,25.97V38.4H2.87c-1.21-0.07-2.24-0.85-2.66-1.94L21.18,25.97L21.18,25.97z"/>
                        <path class="st2" d="M21.18,12.43V0H2.99C1.73,0.02,0.64,0.82,0.21,1.94L21.18,12.43L21.18,12.43z"/>
                        <polygon class="st2" points="0,12.8 7.65,12.8 0,8.97 0,12.8"/>
                        <polygon class="st2" points="55.2,12.8 47.51,12.8 55.2,8.95 55.2,12.8"/>
                        <polygon class="st2" points="55.2,25.6 47.51,25.6 55.2,29.45 55.2,25.6"/>
                        <polygon class="st2" points="0,25.6 7.65,25.6 0,29.43 0,25.6"/>
                        <polygon class="st1" points="55.2,3.25 36.15,12.8 40.41,12.8 55.2,5.4 55.2,3.25"/>
                        <polygon class="st1" points="19.01,25.6 14.75,25.6 0,32.98 0,35.13 19.05,25.6 19.01,25.6"/>
                        <polygon class="st1" points="10.52,12.81 14.78,12.81 0,5.41 0,7.55 10.52,12.81"/>
                        <polygon class="st1" points="44.63,25.59 40.37,25.59 55.2,33.02 55.2,30.88 44.63,25.59"/>
                    </g></svg>
            </div>
            <?php elseif($lang === 'ru'): ?>
                <div class="select-lang__item" data-lang="ru">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"
                     id="Layer_1"
                     x="0px" y="0px" viewBox="0 0 55.32 38.52" style="enable-background:new 0 0 55.32 38.52"
                     xml:space="preserve"><style type="text/css">.st0 {
                            fill: #FFFFFF;
                        }

                        .st1 {
                            fill: #D52B1E;
                        }

                        .st2 {
                            fill: #0039A6;
                        }

                        .st3 {
                            fill: none;
                            stroke: #CCCCCC;
                            stroke-width: 0.1199;
                            stroke-miterlimit: 2.6131;
                        }</style>
                    <g>
                        <path class="st0"
                              d="M3.09,0.06h49.13c1.67,0,3.03,1.36,3.03,3.03v16.17H0.06V3.09C0.06,1.42,1.42,0.06,3.09,0.06L3.09,0.06z"/>
                        <path class="st1"
                              d="M0.06,19.26h55.2v16.17c0,1.67-1.36,3.03-3.03,3.03H3.09c-1.67,0-3.03-1.37-3.03-3.03V19.26L0.06,19.26z"/>
                        <polygon class="st2" points="0.06,12.86 55.26,12.86 55.26,25.66 0.06,25.66 0.06,12.86"/>
                        <path class="st3"
                              d="M3.09,0.06h49.13c1.67,0,3.03,1.36,3.03,3.03v32.33c0,1.67-1.36,3.03-3.03,3.03H3.09 c-1.67,0-3.03-1.37-3.03-3.03V3.09C0.06,1.42,1.42,0.06,3.09,0.06L3.09,0.06z"/>
                    </g></svg>
            </div>
            <?php endif;?>
            <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24">
                <rect x="0" fill="none" width="24" height="24"></rect>
                <g>
                    <path d="M7 10l5 5 5-5"></path>
                </g>
            </svg>
        </div>
    </div>
