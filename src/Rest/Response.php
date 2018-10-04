<?php

namespace Core\Rest;

use Core\Supports\Collection;
use Psr\Http\Message\ResponseInterface;

class Response
{
    /**
     * @var ResponseInterface
     */
    protected static $response;

    /**
     * Raw data from api.
     *
     * @var array
     */
    protected static $content;

    /**
     * Data resources.
     *
     * @var array
     */
    protected $data;

    /**
     * Data total.
     *
     * @var int
     */
    protected $total = 0;

    /**
     * @var bool|mixed
     */
    protected $file;

    /**
     * @var string
     */
    protected $message;

    /**
     * Response constructor.
     *
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        static::$response = $response;
        static::$content  = json_decode($response->getBody()->getContents());

        if ($this->isSuccess()) {
            $this->total = $this->getContent('total');

            if ($data = $this->getContent('data')) {
                $this->data = (array) $data;
            }

            if ($file = $this->getContent('file')) {
                $this->file = $file;
            }
        } else {
            $this->message = $this->getContent('errors');
        }
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return static::$response;
    }

    /**
     * Get all data resources.
     *
     * @return array
     */
    public function getData()
    {
        if ($this->isEmpty()) {
            return false;
        }

        return $this->data;
    }

    public function download()
    {
        if ($file = $this->file) {
            return response()->download(public_path($file));
        }

        return abort(404);
    }

    /**
     * Get file path
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * merge data resources.
     * use this method if necessary
     *
     * @param $data
     * @return array
     */
    public function mergeData($data)
    {
        if (!$this->isEmpty() && is_array($data)) {
            // merging data
            $data = array_merge($data, $this->data);
            $data = new Collection($data);

            // remove duplicate data filtered by its id
            $this->data = $data->unique('id')->values()->all();
        }

        return $this;
    }

    /**
     * Get collection from data responses.
     *
     * @return Collection
     */
    public function getCollection()
    {
        if ($this->isEmpty()) {
            return false;
        }

        return new Collection($this->data, $this->total);
    }

    /**
     * Get response total result.
     *
     * @return int
     */
    public function getTotal()
    {
        return (int) $this->total;
    }

    /**
     * Get raw content by key.
     *
     * @param $key
     * @return bool|mixed
     */
    public function getContent($key)
    {
        return isset(static::$content->$key) ? static::$content->$key : false;
    }

    /**
     * Get response http messages.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Check if http status code is accepted.
     *
     * @return bool
     */
    public function isSuccess()
    {
        if (isset(static::$content->error)) return false;
        $status_code = static::$response->getStatusCode();

        return ($status_code >= 200 && $status_code < 300);
    }

    /**
     * Check if data empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return ($this->data) ? empty($this->data) : true;
    }
}