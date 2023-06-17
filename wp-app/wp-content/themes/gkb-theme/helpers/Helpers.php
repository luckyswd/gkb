<?php

namespace helpers;

use WP_REST_Request;

class Helpers
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';

    public static function getRequest(
        string $method,
        array $params,
    ): WP_REST_Request {
        $request = new WP_REST_Request();
        $request->set_method($method);
        $request->set_body_params($params);

        return $request;
    }
}