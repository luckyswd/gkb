<?php

namespace helpers;

class Helpers
{
    public function isLoginUser(): void
    {
        if (is_user_logged_in()) {
            wp_redirect(home_url());
            exit;
        }
    }
}