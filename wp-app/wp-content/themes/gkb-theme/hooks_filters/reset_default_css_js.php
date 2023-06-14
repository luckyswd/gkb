<?php
function dequeueStyleAndScripts() {
    if (!is_admin()) {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('classic-theme-styles');

        wp_deregister_style('wp-block-library');
        wp_deregister_style('classic-theme-styles');
    }
}

add_action( 'wp_head', 'dequeueStyleAndScripts', 9999 );
add_action( 'wp_enqueue_scripts', 'dequeueStyleAndScripts', 9999 );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );

