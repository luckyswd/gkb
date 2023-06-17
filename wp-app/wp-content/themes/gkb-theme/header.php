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
    <div class="header__wrapper">
        <?php if (!empty($logo)) :?>
            <div class="header__logo">
                <img src="<?= $logo['url'] ?>" alt="<?= $logo['title'] ?>">
            </div>
        <?php endif; ?>
        <?= wp_nav_menu($args); ?>
        <div>

        </div>
    </div>

    <button class="btn user-logout">logout</button>
</header>
<main id="main" class="main" data-page-id="<?= get_queried_object_id() ?>">