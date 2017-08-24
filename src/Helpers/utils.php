<?php

if (!function_exists('object_to_array')) {
    /**
     * Convert object to array
     *
     * @param $object
     * @return array
     */
    function object_to_array($object)
    {
        if (is_object($object)) {
            $object = get_object_vars($object);
        }

        return is_array($object) ? array_map(__FUNCTION__, $object) : $object;
    }
}

if (!function_exists('array_to_object')) {
    /**
     * Convert array to object
     *
     * @param $array
     * @return object
     */
    function array_to_object($array)
    {
        return is_array($array) ? (object) array_map(__FUNCTION__, $array) : $array;
    }
}

if (!function_exists('current_time')) {
    /**
     *  Get current time RFC3339
     *
     * @param string $format
     * @return string
     */
    function current_time($format = 'rfc')
    {
        $now = new \DateTime('now', new DateTimeZone('Asia/Jakarta'));

        if ($format == 'rfc') {
            return $now->format(\DateTime::RFC3339);
        } else {
            return $now->format($format);
        }
    }
}

if (!function_exists('format_timestamp')) {
    /**
     * Formating timestamp
     *
     * @param string $timestamp
     * @param string $format d/M/Y H:i, Y/m/d
     * @return bool|string
     */
    function format_timestamp($timestamp, $format = 'd/m/Y')
    {
        if ($timestamp == "" || strtoupper($timestamp) == '0001-01-01T00:00:00Z') {
            return null;
        }

        try {
            $datetime = new \DateTime($timestamp);

            return $datetime->format($format);
        } catch (Exception $e) {
            return false;
        }
    }
}
if (!function_exists('date_to_timestamp')) {
    /**
     * Reformat date into timestamp format
     *
     * @param string $date
     * @return string
     */
    function date_to_timestamp($date)
    {
        $with_time = explode(' ', $date);
        $date      = explode('/', $with_time[0]);

        if (count($date) == 3) {
            $hour    = null;
            $minutes = null;

            if (isset($with_time[1])) {
                $time = explode(':', $with_time[1]);

                $hour    = $time[0];
                $minutes = $time[1];
            }

            if ($timestamp = \Carbon\Carbon::create($date[2], $date[1], $date [0], $hour, $minutes)) {
                return $timestamp->format(\DateTime::RFC3339);
            }
        }
    }
}

if (!function_exists('num_format')) {
    /**
     * Formating number decimal
     *
     * @param float  $number
     * @param string $separator
     * @param int    $decimal
     * @return string
     */
    function num_format($number, $separator = '.', $decimal = 0)
    {
        return number_format($number, $decimal, '', $separator);
    }
}
if (!function_exists('datetime_range')) {
    /**
     * Making datetime ranges.
     *
     * @param string $start_at
     * @param string $end_at
     * @return array
     */
    function datetime_range($start_at, $end_at)
    {
        $start_date = date_to_timestamp($start_at);
        $start_date = substr($start_date, 0, 10);

        $timestamp = strtotime(date_to_timestamp($end_at));
        $d         = new \DateTime();
        $d->setTimestamp($timestamp);
        $d->add(new \DateInterval('P1D'));
        $end_date = $d->format(\DateTime::RFC3339);
        $end_date = substr($end_date, 0, 10);

        return ['start' => $start_date, 'end' => $end_date];
    }
}
if (!function_exists('upload')) {
    /**
     * Perform uploading files.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param null                                                $path
     * @param null                                                $filename
     * @param string                                              $storage
     * @return bool|null|string
     */
    function upload(\Symfony\Component\HttpFoundation\File\UploadedFile $file, $path = null, $filename = null, $storage = 's3')
    {
        $imageFileName = $filename ?: time();
        $path          = '/' . $path . '/' . $imageFileName . '.' . $file->getClientOriginalExtension();

        $storage = app('filesystem')->disk($storage);
        if ($storage->put($path, file_get_contents($file->getRealPath()))) {
            return $path;
        }

        return false;
    }
}

function filesystem_render($path)
{
    $server = config('filesystems.disks.s3.server');

    return $server . $path;
}


if (!function_exists('encryption')) {
    /**
     * Simple encryption for hidding ID
     *
     * @param  string $value
     * @return string
     */
    function encryption($value)
    {
        return (string) (((0x0000FFFF & $value) << 16) + ((0xFFFF0000 & $value) >> 16));
    }
}

if (!function_exists('decryption')) {
    /**
     * Decryption for simple encrypted string
     *
     * @param string $value
     * @param bool   $int
     * @return string
     */
    function decryption($value, $int = true)
    {
        return ($int) ? (int) encryption($value) : encryption($value);
    }
}
