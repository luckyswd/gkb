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


$args = array(
    'menu' => 'header_menu_desktop',
    'container' => 'nav',
    'container_class' => 'menu-container',
    'menu_class' => 'header-menu',
);
$logo = get_field('logo', 'option');
?>

<body>
<header id="header">
    <div class="container">
        <div class="header__wrapper">
            <?php if (!empty($logo)) : ?>
                <div class="header__logo">
                    <img src="<?= $logo['url'] ?>" alt="<?= $logo['title'] ?>">
                </div>
            <?php endif; ?>
            <?= wp_nav_menu($args); ?>
            <div>
                <a href="#">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="32" height="32" viewBox="0 0 32 32">
                        <path d="M12 16h-10v-4h10v-4l6 6-6 6zM32 0v26l-12 6v-6h-12v-8h2v6h10v-18l8-4h-18v8h-2v-10z"/>
                    </svg>
                </a>

                <a href="#">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="32" height="32" viewBox="0 0 32 32">
                        <path d="M24 20v-4h-10v-4h10v-4l6 6zM22 18v8h-10v6l-12-6v-26h22v10h-2v-8h-16l8 4v18h8v-6z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</header>
<main id="main" class="main" data-page-id="<?= get_queried_object_id() ?>">