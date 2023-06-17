<?php

namespace ajax;

use helpers\Helpers;

class Ajax
{
    public function register(): void
    {
        add_action('wp_ajax_select_lang', [$this, 'selectLang']);
        add_action('wp_ajax_nopriv_select_lang', [$this, 'selectLang']);
    }

    public function selectLang(): never
    {
        $request = Helpers::getRequest(Helpers::METHOD_POST, $_POST);
        $lang = $request->get_param('lang');
        setcookie('lang', $lang, time() + 3600, COOKIEPATH, COOKIE_DOMAIN);

        wp_send_json([
            'status' => true,
        ]);

        wp_die();
    }
}