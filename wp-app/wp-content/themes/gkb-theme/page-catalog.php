<?php

get_header();

$search = $_GET['search'] ?? '';
$products = \helpers\Helpers::getProductsByCategory(null, $search);

include 'components/category-template.php';

get_footer();
