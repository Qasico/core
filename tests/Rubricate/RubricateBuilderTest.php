<?php

use GuzzleHttp\ClientInterface;
use Mockery as m;
use Qasico\Rubricate\Builder;
use Qasico\Rubricate\Model;
use Qasico\Rubricate\RestQuery;

class RubricateBuilderTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testBuilderInstance()
    {
        $b = new Builder($this->makeQuery(), $this->makeClient());

        $this->assertAttributeInstanceOf('Qasico\Rubricate\Interfaces\QueryInterface', 'query', $b);
    }
    
    public function testBuilderModelInstances()
    {
        $b = new Builder($this->makeQuery(), $this->makeClient());

        $b->setModel(new ModelBuilder());
        $this->assertAttributeInstanceOf('Qasico\Rubricate\Model', 'model', $b);
    }

    public function testBuilderRestConnector()
    {
        $b = new Builder($this->makeQuery(), $this->makeClient());

        $this->assertInstanceOf('Qasico\Rubricate\Rest\Connector', $b->rest());
    }

    public function testBuilderCallingQuery()
    {
        $b = new Builder($this->makeQuery(), $this->makeClient());

        $b->where('id', 1);
        $b->limit(1);

        $this->assertEquals(['query' => 'id:1', 'limit' => 1], $b->rest()->requestParameter());
    }

    protected function makeQuery()
    {
        return new RestQuery();
    }

    protected function makeClient()
    {
        return m::mock(ClientInterface::class);
    }
}

class ModelBuilder extends Model
{

}