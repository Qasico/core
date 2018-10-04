<?php

namespace Core\Rest;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class Request
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $jwt_token = "";

    /**
     * Init and setup http request with API options.
     *
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Send a GET request.
     *
     * @param string $path       Request path
     * @param array  $parameters GET Parameters
     * @param array  $headers    Reconfigure the request headers for this call only
     * @return Response The response content
     */
    public function get($path, array $parameters = [], array $headers = [])
    {
        return $this->request($path, 'GET', null, $parameters, $headers);
    }

    /**
     * Send a POST request.
     *
     * @param string $path    Request path
     * @param mixed  $body    Request body
     * @param array  $headers Reconfigure the request headers for this call only
     * @return Response The response content
     */
    public function post($path, $body = null, array $headers = [])
    {
        $body = array_filter($body, function ($var) {
            return ($var !== false);
        });

        $body = (count($body) == 0) ? null : json_encode($body);

        return $this->request($path, 'POST', $body, null, $headers);
    }

    /**
     * Send a PUT request.
     *
     * @param string $path    Request path
     * @param mixed  $body    Request body
     * @param array  $headers Reconfigure the request headers for this call only
     * @return Response The response content
     */
    public function put($path, $body, array $headers = [])
    {
        $body = array_filter($body, function ($var) {
            return ($var !== false);
        });

        $body = (count($body) == 0) ? null : json_encode($body);

        return $this->request($path, 'PUT', $body, null, $headers);
    }

    /**
     * Send a DELETE request.
     *
     * @param string $path    Request path
     * @param mixed  $body    Request body
     * @param array  $headers Reconfigure the request headers for this call only
     * @return Response The response content
     */
    public function delete($path, $body = null, array $headers = [])
    {
        return $this->request($path, 'DELETE', $body, null, $headers);
    }

    /**
     * Send a request to the server, receive a response,
     * decode the response and returns an associative array.
     *
     * @param string   $path       Request path
     * @param string   $httpMethod HTTP method to use
     * @param string   $body       Request body.
     * @param string[] $parameters Request GET parameters
     * @param array    $headers    Request headers
     * @throws \Exception
     * @return Response The response content
     */
    public function request($path, $httpMethod = 'GET', $body = null, array $parameters = null, array $headers = [])
    {
        $path = $this->client->getConfig("base_uri") . $path;
        try {
            $response = $this->client->request($httpMethod, $path, [
                'body'     => $body,
                'query'    => $parameters,
                'headers'  => $this->setHeader($headers),
                'on_stats' => function (TransferStats $stats) use (&$url) {
                    $url = $stats->getEffectiveUri();
                }
            ]);

        } catch (ConnectException $e) {
            throw new \Exception("System was offline.", 500);
        } catch (RequestException $e) {
            if ($e->getCode() != 422 && $e->getCode() != 400) {
                if (app()->environment() == "local") {
                    $message = $this->getErrorMessage($e->getResponse());
                    Log::error("API: Requested", ["url" => $path, "response" => $message]);
                    throw new \Exception($message, $e->getCode());
                } else {
                    abort(404);
                }
            }

            $response = $e->getResponse();
        }

        return new Response($response);
    }

    public function setHeader($headers)
    {
        $headers ["Content-Type"] = "application/json";

        return $headers;
    }

    public function getErrorMessage(ResponseInterface $response)
    {
        $error_message = json_decode($response->getBody()->getContents(), true);

        return array_get($error_message, 'message');
    }
}