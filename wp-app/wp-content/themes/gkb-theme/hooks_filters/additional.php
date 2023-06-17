<?php

function hide_menu_items() {
    remove_menu_page('edit.php');
    remove_menu_page('edit-comments.php');
}

add_action('admin_menu', 'hide_menu_items');

add_filter('manage_users_columns', 'custom_user_columns');
add_action('manage_users_custom_column', 'custom_user_column_values', 10, 3);
function custom_user_columns($columns) {
    $columns['email_confirmation_status'] = 'Статус подтверждения электронной почты';

    return $columns;
}
function custom_user_column_values($value, $column, $userId) {
    if ($column === 'email_confirmation_status') {
        $status = get_user_meta($userId, 'email_confirmation_status', true);

        if ($status === 'verified') {
            $value = 'Проверено';
        } elseif ($status === 'pending') {
            $value = 'В ожидании';
        } else {
            $value = 'Неизвестный';
        }
    }
    return $value;
}