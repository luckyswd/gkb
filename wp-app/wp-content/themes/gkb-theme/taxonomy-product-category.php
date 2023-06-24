<?php

get_header();
$currentCategory = get_queried_object();
$products = \helpers\Helpers::getProductsByCategory($currentCategory);

include 'components/category-template.php';

get_footer();
