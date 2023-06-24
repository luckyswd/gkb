<?php

get_header();

$products = \helpers\Helpers::getProductsByCategory();

include 'components/category-template.php';

get_footer();
