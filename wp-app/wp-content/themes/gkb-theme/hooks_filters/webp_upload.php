<?php
function webp_upload_mimes( $existing_mimes ) {
    $existing_mimes['webp'] = 'image/webp';

    return $existing_mimes;
}
add_filter( 'mime_types', 'webp_upload_mimes' );

function allow_svg_upload($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_upload');