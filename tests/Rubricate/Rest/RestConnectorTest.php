<?php

use GuzzleHttp\Psr7\Response;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Mockery as m;
use Qasico\Rubricate\Cache;
use Qasico\Rubricate\Rest\Connector;

class RestConnectorTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testInstances()
    {
        $q = $this->makeQuery();
        $c = new Connector($q);

        $this->assertAttributeInstanceOf('Qasico\Rubricate\Interfaces\QueryInterface', 'query', $c);
    }

    public function testMakeClientInstance()
    {
        $q = $this->makeQuery();
        $c = new Connector($q);
        $g = $this->makeClient();

        $c->setClient($g);

        $this->assertInstanceOf('Qasico\Rubricate\Rest\Request', $c->getRequest());
    }

    public function testConnectorRead()
    {
        $response = new Response(200, [], json_encode(['data' => ['name' => 'foo', 'email' => 'bar'], 'status' => 'success', 'total' => 1]));
        $param    = ['fields' => 'name,email'];

        $mQ = m::mock('Qasico\Rubricate\Interfaces\QueryInterface');
        $mQ->shouldReceive('compileBinding')
            ->andReturn($param);

        $c = new Connector($mQ);

        $mC = m::mock('GuzzleHttp\ClientInterface');
        $mC->shouldReceive('request')
            ->with('GET', 'foo', ['body' => null, 'query' => $param, 'headers' => null])
            ->once()
            ->andReturn($response);

        $c->setClient($mC);
        $response = $c->read('foo');

        $this->assertInstanceOf('Qasico\Rubricate\Rest\Response', $response);
        $this->assertEquals(['name' => 'foo', 'email' => 'bar'], $response->getData());
    }

    public function testConnectorCreate()
    {
        $data     = ['foo' => 'bar'];
        $response = new Response(200, [], json_encode(['data' => ['id' => 1, 'foo' => 'bar'], 'status' => 'success', 'total' => 1]));

        $mQ = m::mock('Qasico\Rubricate\Interfaces\QueryInterface');
        $c  = new Connector($mQ);

        $mC = m::mock('GuzzleHttp\ClientInterface');
        $mC->shouldReceive('request')
            ->with('POST', 'foo', ['body' => json_encode($data), 'query' => null, 'headers' => null])
            ->once()
            ->andReturn($response);

        $c->setClient($mC);

        $this->assertEquals(['id' => 1, 'foo' => 'bar'], $c->create('foo', $data));
    }

    public function testConnectorUpdate()
    {
        $data     = ['foo' => 'bar'];
        $response = new Response(200, [], json_encode(['status' => 'success']));

        $mQ = m::mock('Qasico\Rubricate\Interfaces\QueryInterface');
        $c  = new Connector($mQ);

        $mC = m::mock('GuzzleHttp\ClientInterface');
        $mC->shouldReceive('request')
            ->with('PUT', 'foo/1', ['body' => json_encode($data), 'query' => null, 'headers' => null])
            ->once()
            ->andReturn($response);

        $c->setClient($mC);

        $this->assertTrue($c->update('foo', 1, $data));
    }

    public function testConnectorDestroy()
    {
        $response = new Response(200, [], json_encode(['status' => 'success']));

        $mQ = m::mock('Qasico\Rubricate\Interfaces\QueryInterface');
        $c  = new Connector($mQ);

        $mC = m::mock('GuzzleHttp\ClientInterface');
        $mC->shouldReceive('request')
            ->with('DELETE', 'foo/1', ['body' => null, 'query' => null, 'headers' => null])
            ->once()
            ->andReturn($response);

        $c->setClient($mC);

        $this->assertTrue($c->destroy('foo', 1));
    }

    protected function makeQuery()
    {
        return m::mock('Qasico\Rubricate\Interfaces\QueryInterface');
    }

    protected function makeClient()
    {
        return m::mock('GuzzleHttp\ClientInterface');
    }

    protected function makeCache()
    {
        $dispatcher = new Illuminate\Events\Dispatcher(m::mock('Illuminate\Container\Container'));
        $cache      = new Cache(new Repository(new ArrayStore()));
        $cache->setEventDispatcher($dispatcher);

        return $cache;
    }
}