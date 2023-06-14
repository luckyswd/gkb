<?php

use helpers\Helpers;

class Ajax
{
    public string $ajax_blocks_path;

    public function __construct()
    {
        $this->ajax_blocks_path = get_template_directory() . '/ajax-blocks/';
    }

    public function register(): void
    {
//        add_action('wp_ajax_example_kitchens', [$this, 'example_kitchens']);
//        add_action('wp_ajax_nopriv_example_kitchens', [$this, 'example_kitchens']);
    }

//    public function example_kitchens()
//    {
//        $format = $_POST['format'];
//        $entities = $this->get_example_kitchens($format);
//
//        include $this->ajax_blocks_path . 'example-kitchens-ajax.php';
//        wp_die();
//    }
}