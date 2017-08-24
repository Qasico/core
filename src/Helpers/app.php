<?php

if (!function_exists('current_url')) {
    /**
     * Get Current Url
     *
     * @return string
     */
    function current_url()
    {
        $req = app('request');

        return '/' . $req->path();
    }
}

if (!function_exists('to_object')) {
    /**
     * Convert array to object
     *
     * @param array $data
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    function to_object(Array $data)
    {
        return json_decode(json_encode($data), false);
    }
}

if (!function_exists('make_route')) {
    /**
     * Routing generator
     *
     * @param \Illuminate\Routing\Router $router
     * @param array|string               $method
     * @param string                     $controller
     * @param array                      $path
     * @param string                     $alias
     * @param array                      $rule
     * @return \Illuminate\Routing\Route
     */
    function make_route(\Illuminate\Routing\Router $router, $method, $controller, array $path, $alias, array $rule = array())
    {
        foreach ($path as $uri) {
            $slug = preg_replace("/{[^}]+}/", "", $uri);
            $slug = preg_replace("/[^a-zA-Z0-9]+/", "", strtolower($slug));

            $action = array();
            if (preg_match_all("/{([^}]*)}/", $uri, $matches)) {
                $action ['where'] = $rule;
            }

            $action ['as'] = $alias . '.' . $slug;

            if ($uri == 'index') {
                $action ['as'] = $alias;
                $uri           = "/";
            }

            if (!is_array($method)) {
                $method = ($method == 'both') ? ['get', 'post'] : [$method];
            }

            foreach ($method as $m) {
                $action ['uses'] = $controller . '@' . strtolower($m) . ucfirst($slug);
                $router->$m($uri, $action);
            }
        }

        return $router;
    }
}