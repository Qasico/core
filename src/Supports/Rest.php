<?php

namespace Core\Supports;

use Core\Rest\Request;
use Core\Rest\Response;
use GuzzleHttp\ClientInterface;

class Rest
{
    /**
     * @var ClientInterface
     */
    protected static $client;

    /**
     * @var Cache
     */
    protected static $cache;

    /**
     * @var RestRequest
     */
    protected $query;

    /**
     * @var bool
     */
    protected $cached = true;

    /**
     * @var string
     */
    protected $cache_tag = "";

    /**
     * @var bool
     */
    protected $authentication = true;

    /**
     * Jason web token
     *
     * @var string
     */
    protected $jwt_key = "";

    /**
     * Set cache instances with tag current models.
     *
     * @param Cache $cache
     * @return $this
     */
    public static function setCache(Cache $cache)
    {
        static::$cache = $cache;
    }

    /**
     * Set the rest client instance.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @return void
     */
    public static function setRestClient(ClientInterface $client)
    {
        static::$client = $client;
    }

    /**
     * Get the cache instance.
     *
     * @return Cache
     */
    protected function getCache()
    {
        if (!$this->cached || !static::$cache instanceof Cache) {
            return null;
        }

        return static::$cache->makeCache($this->cache_tag);
    }

    /**
     * Get the rest client instance.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    protected function getRestClient()
    {
        return static::$client;
    }

    protected function getHeader()
    {
        return [
            "Authorization" => "Bearer " . $this->getJwtToken()
        ];
    }

    protected function getJwtToken()
    {
        $token = $this->jwt_key;

        if ($this->authentication && $token == "") {
            abort(500, "API Token is not set");
        }

        return $token;
    }

    /**
     * Read request response, we will check if cached available.
     *
     * @param string $path
     * @param array  $query
     * @return Response|string
     */
    public function read($path, array $query = [])
    {
//        return ($this->getCache()) ? $this->fromCache($path, $this->requestParameter()) : $this->fromRest($path, $this->requestParameter());
        $query = array_merge($query, $this->requestParameter());
        
        return $this->fromRest($path, $query);
    }

    /**
     * Sending request insert new data.
     *
     * @param string $path
     * @param array  $data
     * @return Response
     */
    public function post($path, array $data = [])
    {
        $response = $this->getRequest($path)->post($path, $data, $this->getHeader());

        if ($response->isSuccess()) {
            $this->flushCache();
        }

        return $response;
    }

    /**
     * Sending request update existing data.
     *
     * @param string $path
     * @param array  $data
     * @return Response
     */
    public function put($path, array $data = array())
    {
        $response = $this->getRequest()->put($path, $data, $this->getHeader());

        if ($response->isSuccess()) {
            $this->flushCache();
        }

        return $response;
    }

    /**
     * Sending request delet existing data.
     *
     * @param string $path
     * @return bool
     */
    public function delete($path)
    {
        $response = $this->getRequest()->delete($path, null, $this->getHeader());

        if ($status = $response->isSuccess()) {
            $this->flushCache();
        }

        return $response;
    }

    /**
     * Set request as guest
     *
     * @return $this
     */
    public function guest()
    {
        $this->authentication = false;

        return $this;
    }

    /**
     * Perform request as user based on token
     *
     * @param string $token
     * @return $this
     */
    public function asUser(string $token)
    {
        $this->jwt_key = $token;

        return $this;
    }

    /**
     * Get all binding parameter from query.
     *
     * @return array
     */
    protected function requestParameter()
    {
        return $this->query ? $this->query->compileBinding() ?: [] : [];
    }

    /**
     * Get request instances.
     *
     * @return Request
     */
    protected function getRequest()
    {
        return new Request($this->getRestClient());
    }

    /**
     * Flush cache after fetch requests.
     *
     * @return void
     */
    protected function flushCache()
    {
        if ($this->getCache()) {
            $this->getCache()->flush();
        }
    }

    /**
     * Read response request from cache if available.
     *
     * @param $path
     * @param $parameter
     * @return mixed
     */
    protected function fromCache($path, array $parameter = [])
    {
        $queryString = $path . $this->query->toString('?');
        if (!$queryString || !$result = $this->getCache()->read($queryString)) {
            $result = $this->fromRest($path, $parameter);
            if ($result && !$result->isEmpty()) {
                $this->getCache()->save($queryString, $result);
            }
        }

        return $result;
    }

    /**
     * Sending request getting data.
     *
     * Sending along with the parameters if available,
     * You can set parameter query string by calling param () method.
     *
     * @param $path
     * @param $parameter
     * @return Response
     */
    protected function fromRest($path, array $parameter = [])
    {


        return $this->getRequest()->get($path, $parameter, $this->getHeader());
    }
}