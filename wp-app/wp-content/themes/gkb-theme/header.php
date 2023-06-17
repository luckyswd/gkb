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

$logo = get_field('logo', 'option');
$socials = get_field('socials', 'option');
$fields = (new Helpers)->get_field_multi_lang((new Helpers)->getLang(), 'header_links', 'option');
?>

<body>
<div class="select-lang">
    <?php if ((new Helpers)->getLang() == 'ru') : ?>
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
    <?php else: ?>
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
    <?php endif; ?>
</div>
<header id="header">
    <div class="container">
        <div class="header__wrapper">
            <?php if (!empty($logo)) : ?>
                <a href="<?= home_url() ?>" class="header__logo">
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
            <?php endif; ?>
            <?php if (!empty($socials)) : ?>
                <div class="header__socials">
                    <?php foreach ($socials as $social) : ?>
                        <?php if ($social['url'] && !empty($social['icon'])) : ?>
                            <div class="header__social">
                                <a href="<?= $social['url'] ?>">
                                    <img src="<?= $social['icon']['url'] ?>" alt="<?= $social['icon']['title'] ?>">
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="header__login">
                <?php if (!is_user_logged_in()) : ?>
                    <a href="/login">
                        log in
                        <!--                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="32" height="32" viewBox="0 0 32 32">-->
                        <!--                        <path d="M12 16h-10v-4h10v-4l6 6-6 6zM32 0v26l-12 6v-6h-12v-8h2v6h10v-18l8-4h-18v8h-2v-10z"/>-->
                        <!--                    </svg>-->
                    </a>
                <?php else: ?>
                    <button class="user-logout">
                        log out
                        <!--                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="32" height="32" viewBox="0 0 32 32">-->
                        <!--                        <path d="M24 20v-4h-10v-4h10v-4l6 6zM22 18v8h-10v6l-12-6v-26h22v10h-2v-8h-16l8 4v18h8v-6z"/>-->
                        <!--                    </svg>-->
                    </button>
                <?php endif; ?>

            </div>
        </div>
    </div>
</header>
<main id="main" class="main" data-page-id="<?= get_queried_object_id() ?>">