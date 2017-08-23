<?php

use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Qasico\Rubricate\Rest\Request;

class RestRequestTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testInstancesRequest()
    {
        $c = $this->makeClient();
        $r = $this->makeRequest($c);

        $this->assertInstanceOf('Qasico\Rubricate\Rest\Request', $r);
    }

    public function testRequestGet()
    {
        $mock = new Response(200, [], json_encode(['data' => [], 'status' => 'success', 'total' => 0]));

        $c = $this->makeClient();
        $c->shouldReceive('request')
            ->with('GET', 'test', ['body' => null, 'query' => null, 'headers' => null])
            ->once()
            ->andReturn($mock);

        $r = $this->makeRequest($c);

        $response = $r->get('test');

        $this->assertInstanceOf('Qasico\Rubricate\Rest\Response', $response);
    }

    public function testRequestPost()
    {
        $data = ['foo' => 'bar'];
        $mock = new Response(200, [], json_encode(['data' => $data, 'status' => 'success', 'total' => 1]));

        $c = $this->makeClient();
        $c->shouldReceive('request')
            ->with('POST', 'test', ['body' => json_encode($data), 'query' => null, 'headers' => null])
            ->once()
            ->andReturn($mock);

        $r = $this->makeRequest($c);

        $response = $r->post('test', $data);

        $this->assertInstanceOf('Qasico\Rubricate\Rest\Response', $response);
    }

    public function testRequestPut()
    {
        $data = ['foo' => 'bar'];
        $mock = new Response(200, [], json_encode(['data' => $data, 'status' => 'success', 'total' => 1]));

        $c = $this->makeClient();
        $c->shouldReceive('request')
            ->with('PUT', 'test/1', ['body' => json_encode($data), 'query' => null, 'headers' => null])
            ->once()
            ->andReturn($mock);

        $r = $this->makeRequest($c);

        $response = $r->put('test/1', $data);

        $this->assertInstanceOf('Qasico\Rubricate\Rest\Response', $response);
    }

    public function testRequestDelete()
    {
        $mock = new Response(200, [], json_encode(['status' => 'success']));

        $c = $this->makeClient();
        $c->shouldReceive('request')
            ->with('DELETE', 'test/1', ['body' => null, 'query' => null, 'headers' => null])
            ->once()
            ->andReturn($mock);

        $r = $this->makeRequest($c);

        $response = $r->delete('test/1');

        $this->assertInstanceOf('Qasico\Rubricate\Rest\Response', $response);
    }

    public function testRequestException()
    {
        $mock = new Response(200, [], '{"message":{"error":"field `konektifa/models.AccountingCoa.Type` cannot be NULL"},"success":false}');

        $e = m::mock('GuzzleHttp\Exception\ClientException');
        $e->shouldReceive('getResponse')
            ->andReturn($mock);

        $c = $this->makeClient();
        $c->shouldReceive('request')
            ->with('GET', 'test', ['body' => null, 'query' => null, 'headers' => null])
            ->once()
            ->andThrow($e);

        $r = $this->makeRequest($c);

        $this->setExpectedException('\Exception');
        $r->get('test');
    }

    protected function makeClient()
    {
        return m::mock('GuzzleHttp\ClientInterface');
    }

    protected function makeRequest($client)
    {
        return new Request($client);
    }
}