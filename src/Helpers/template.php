<?php

if (!function_exists('set_error')) {
    /**
     * Add has-error to form-group
     * {{ set_error( 'field_name', $errors ) }}
     *
     * @param string $key    name of input field being checked
     * @param object $errors just passing the global $errors variable to the function
     * @return string
     */
    function set_error($key, $errors)
    {
        $keys   = str_replace('.', '-', $key);
        $result = $errors->has($keys) ? 'has-error' : '';

        return 'field-' . $keys . ' ' . $result;
    }
}

if (!function_exists('get_error')) {
    /**
     * Get error message and add to a help-block
     * {!! get_error( 'field_name', $errors ) !!}
     *
     * @param string $key    name of input field being checked
     * @param object $errors just passing the global $errors variable to the function
     * @return string
     */
    function get_error($key, $errors)
    {
        return $errors->has($key) ? $errors->first($key, '<p class="help-block">:message</p>') : '';
    }
}

if (!function_exists('flash_messages')) {
    /**
     * Show alert bootstrap with messages
     * <div id="flash-message">
     *      {!! flash_messages( 'message', Session::get('status') ) !!}
     * </div>
     *
     * @param string $name
     * @param string $style
     * @return bool|string
     */
    function flash_messages($name, $style = 'info')
    {
        if ($message = Session::get($name)) {
            $output = '<div class="alert alert-page pa_page_alerts_dark alert-dark alert-' . $style . '">';
            $output .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
            $output .= preg_replace('/^(\S+(\s+\S+)?)/', '<strong>$1</strong>', $message);
            $output .= '</div>';

            return $output;
        }

        return false;
    }
}

if (!function_exists('page_title')) {
    /**
     * Arrange Page title with formated.
     *
     * @param string $value
     * @return string
     */
    function page_title($value)
    {
        $wordarray = explode('/', $value);
        if (count($wordarray) > 1) {
            $wordarray[count($wordarray) - 1] = '<span class="text-primary">' . ($wordarray[count($wordarray) - 1]) . '</span>';

            return implode(' / ', $wordarray);
        }

        return $value;
    }
}

if (!function_exists('ng_route')) {
    /**
     * Generate url with decode string for angular variable.
     *
     * @param string $name
     * @param string $parameters
     * @return string
     */
    function ng_route($name, $parameters)
    {
        $parameters = '<% ' . $parameters . ' %>';

        return urldecode(route($name, $parameters, true));
    }
}
