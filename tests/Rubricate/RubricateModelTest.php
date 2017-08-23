<?php

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Mockery as m;
use Qasico\Rubricate\Cache;
use Qasico\Rubricate\Model;

class RubricateModelTest extends TestCase
{
    public function tearDown()
    {
        m::close();
        Model::unsetRestClient();
        Model::unsetBuilder();
    }

    public function testModelShouldHasPropertyEndPoint()
    {
        $this->assertClassHasAttribute('table', 'FooModels');
    }
    
    public function testModelCanReadClassAsTable()
    {
        $m = new FooModels();

        $this->assertAttributeEquals(null, 'table', $m);
        $this->assertEquals('foo-models', $m->getTable());
    }

    public function testModelCanSetTable()
    {
        $m = new FooModels();

        $m->setTable('foo_test');
        $this->assertEquals('foo-tests', $m->getTable());

        $m->setTable('foo test');
        $this->assertEquals('foo-tests', $m->getTable());

        $m->setTable('foo tests');
        $this->assertEquals('foo-tests', $m->getTable());
    }

    public function testModelRestClientInstance()
    {
        $m = new FooModels();
        $c = m::mock(ClientInterface::class);

        $m::setRestClient($c);

        $this->assertAttributeInstanceOf(ClientInterface::class, 'client', $m);
        $this->assertInstanceOf(ClientInterface::class, $m::getRestClient());
    }

    public function testModelClientInstancesStatic()
    {
        $c = m::mock(ClientInterface::class);
        Model::setRestClient($c);

        $m = new FooModels();
        $this->assertInstanceOf(ClientInterface::class, $m::getRestClient());

        Model::unsetRestClient();
        $this->assertNull($m::getRestClient());
    }
    
    public function testModelStaticEvent()
    {
        $d = m::mock('Illuminate\Contracts\Events\Dispatcher');
        Model::setEventDispatcher($d);

        $m = new FooModels();
        $this->assertInstanceOf('Illuminate\Contracts\Events\Dispatcher', $m::getEventDispatcher());

        Model::unsetEventDispatcher();
        $this->assertNull($m::getEventDispatcher());
    }

    public function testModelAbilityShow()
    {
        $mock = new Response(200, [], json_encode(['data' => ['id' => 1, 'foo' => 'bar'], 'status' => 'success', 'total' => 0]));

        $c = m::mock(ClientInterface::class);
        $c->shouldReceive('request')
            ->with('GET', 'foo-models/1', ['body' => null, 'query' => null, 'headers' => null])
            ->once()
            ->andReturn($mock);

        Model::setRestClient($c);

        $m = new FooModels();
        $r = $m->show(1);

        $this->assertEquals(1, $r->id);
    }

    public function testModelAblilityGet()
    {
        $mock  = new Response(200, [], json_encode(['data' => [['id' => 1, 'foo' => 'bar'], ['id' => 2, 'bar' => 'foo']], 'status' => 'success', 'total' => 0]));
        $param = [
            "fields"  => "name,email",
            "groupby" => "id",
            "join"    => "bar,bazz",
            "limit"   => 1,
            "order"   => "asc",
            "sortby"  => "id",
            "query"   => "id:1",
        ];

        $c = m::mock(ClientInterface::class);
        $c->shouldReceive('request')
            ->with('GET', 'foo-models', ['body' => null, 'query' => $param, 'headers' => null])
            ->andReturn($mock);

        Model::setRestClient($c);

        $m = new FooModels();

        $m->select(['name', 'email']);
        $m->join('bar');
        $m->join('bazz');
        $m->groupBy('id');
        $m->order(['id' => 'asc']);
        $m->limit(1);
        $m->where('id', 1);

        $r = $m->get();

        $this->assertInstanceOf('Qasico\Rubricate\Collection', $r);
        $this->assertEquals(1, $r->first()->id);
        $this->assertEquals('mutated', $r->first()->foo);

        $mock = new Response(200, [], json_encode(['data' => ['id' => 1, 'foo' => 'bar'], 'status' => 'success', 'total' => 0]));
        $c->shouldReceive('request')
            ->with('GET', 'foo-models/5', ['body' => null, 'query' => null, 'headers' => null])
            ->andReturn($mock);

        Model::setRestClient($c);

        $r = $m->show(5);
        $this->assertEquals(1, $r->id);
    }
    
    public function testModelAbilitySave()
    {
        $mock = new Response(200, [], json_encode(['data' => ['id' => 1, 'name' => 'Foo', 'email' => 'bar@foo.com'], 'status' => 'success', 'total' => 0]));
        $c    = m::mock(ClientInterface::class);
        $c->shouldReceive('request')
            ->with('POST', 'foo-models', ['body' => json_encode(['name' => 'Foo', 'email' => 'bar@foo.com']), 'query' => null, 'headers' => null])
            ->andReturn($mock);

        Model::setRestClient($c);

        $m = new FooModels();

        $m->name  = 'Foo';
        $m->email = 'bar@foo.com';

        $this->assertTrue($m->save());
        $this->assertEquals(1, $m->id);
        $this->assertEquals(['name' => 'Foo', 'email' => 'bar@foo.com'], $m->getDirty());

        Model::unsetBuilder();
        Model::unsetRestClient();

        $r = m::mock(ClientInterface::class);
        $r->shouldReceive('request')
            ->with('PUT', 'foo-models/1', ['body' => json_encode(['name' => 'Foo', 'email' => 'bar@foo.com', 'username' => 'Foobar']), 'query' => null, 'headers' => null])
            ->andReturn($mock);

        FooModels::setRestClient($r);
        $m->username = 'Foobar';
        $this->assertTrue($m->save());
    }

    public function testModelAbilityDelete()
    {
        $mock = new Response(200, [], json_encode(['data' => ['success'], 'status' => 'success']));
        $c    = m::mock(ClientInterface::class);
        $c->shouldReceive('request')
            ->with('DELETE', 'foo-models/1', ['body' => null, 'query' => null, 'headers' => null])
            ->andReturn($mock);

        Model::setRestClient($c);

        $m = new FooModels();

        $m->exists = true;
        $m->id     = 1;
        $m->name   = 'Foo';
        $m->email  = 'bar@foo.com';
        $this->assertTrue($m->destroy());
        $this->assertNull($m->id);
        $this->assertFalse($m->exists);
    }
    
    public function testModelQueryCreate()
    {
        $mock = new Response(200, [], json_encode(['data' => ['id' => 1, 'name' => 'Foo', 'email' => 'bar@foo.com'], 'status' => 'success', 'total' => 0]));
        $c    = m::mock(ClientInterface::class);
        $c->shouldReceive('request')
            ->with('POST', 'foo-models', ['body' => json_encode(['name' => 'Foo', 'email' => 'bar@foo.com']), 'query' => null, 'headers' => null])
            ->andReturn($mock);

        Model::setRestClient($c);

        $m = new FooModels();

        $r = $m->create(['name' => 'Foo', 'email' => 'bar@foo.com']);
        $this->assertInstanceOf('FooModels', $r);
    }

    public function testModelQueryUpdate()
    {
        $mock = new Response(200, [], json_encode(['data' => ['id' => 1, 'name' => 'Foo', 'email' => 'bar@foo.com'], 'status' => 'success', 'total' => 0]));
        $c    = m::mock(ClientInterface::class);
        $c->shouldReceive('request')
            ->with('PUT', 'foo-models/1', ['body' => json_encode(['name' => 'Foo', 'email' => 'bar@foo.com', 'username' => 'Foobar']), 'query' => null, 'headers' => null])
            ->andReturn($mock);

        Model::setRestClient($c);

        $m = new FooModels();

        $r = $m->update(1, ['name' => 'Foo', 'email' => 'bar@foo.com', 'username' => 'Foobar']);
        $this->assertTrue($r);
    }

    public function testModelQueryDelete()
    {
        $mock = new Response(200, [], json_encode(['data' => ['success'], 'status' => 'success']));
        $c    = m::mock(ClientInterface::class);
        $c->shouldReceive('request')
            ->with('DELETE', 'foo-models/1', ['body' => null, 'query' => null, 'headers' => null])
            ->andReturn($mock);

        Model::setRestClient($c);

        $m = new FooModels();

        $this->assertTrue($m->delete(1));
    }

    public function testModelNonOrm()
    {
        $mock  = new Response(200, [], json_encode(['data' => [['id' => 1, 'foo' => 'bar'], ['id' => 2, 'bar' => 'foo']], 'status' => 'success', 'total' => 0]));
        $param = [
            "fields"  => "name,email",
            "groupby" => "id",
            "join"    => "bar,bazz",
            "limit"   => 2,
            "order"   => "asc",
            "sortby"  => "id",
            "query"   => "id:1",
        ];

        $c = m::mock(ClientInterface::class);
        $c->shouldReceive('request')
            ->with('GET', 'foo-models', ['body' => null, 'query' => $param, 'headers' => null])
            ->once()
            ->andReturn($mock);

        Model::setRestClient($c);

        $m = new FooModels();

        $m->select(['name', 'email']);
        $m->join('bar');
        $m->join('bazz');
        $m->groupBy('id');
        $m->order(['id' => 'asc']);
        $m->limit(2);
        $m->where('id', 1);
        $m->raw();

        $r = $m->get();

        $this->assertInstanceOf('Qasico\Rubricate\Collection', $r);
        $this->assertEquals('65536', $r->first()->id_e);
    }

    public function testModelWithCache()
    {
        $mock = new Response(200, [], json_encode(['data' => ['id' => 1, 'foo' => 'bar'], 'status' => 'success', 'total' => 0]));
        $c    = m::mock(ClientInterface::class);
        $c->shouldReceive('request')
            ->with('GET', 'foo-models/1', ['body' => null, 'query' => null, 'headers' => null])
            ->once()
            ->andReturn($mock);

        Model::setRestClient($c);
        Model::setCache($this->makeCache());

        $m = new FooModels();
        $m->show(1);
        $m->show(1);
    }

    public function testModelQueryInstances()
    {
        $c = m::mock(ClientInterface::class);
        Model::setRestClient($c);

        $foo_model = new FooModels();
        $bar_model = new BarModels();

        $foo_model->where('id', 1);

        $bar_query = $bar_model->getQuery();
        $foo_query = $foo_model->getQuery();

        $this->assertNull($bar_query->query);
        $this->assertEquals([['id' => 1]], $foo_query->query);
    }

    public function testModelResponseFormater()
    {
        $mock = new Response(200, [], json_encode(['data' => ['id' => 1, 'foo' => 'bar'], 'status' => 'success', 'total' => 0]));

        $c = m::mock(ClientInterface::class);
        $c->shouldReceive('request')
            ->with('GET', 'foo-models/1', ['body' => null, 'query' => null, 'headers' => null])
            ->andReturn($mock);

        Model::setRestClient($c);

        $m = new FooModels();
        $r = $m->show(1);

        $this->assertNull($r->getOriginal('id_e'));
        $this->assertEquals('65536', $r->id_e);
    }

    public function testModelAttributeSave()
    {
        $mock = new Response(200, [], json_encode(['data' => ['id' => 1, 'name' => 'Foo', 'email' => ''], 'status' => 'success', 'total' => 0]));

        $c = m::mock(ClientInterface::class);
        $c->shouldReceive('request')
            ->with('GET', 'foo-models/1', ['body' => null, 'query' => null, 'headers' => null])
            ->andReturn($mock);

        Model::setRestClient($c);

        $m = new FooModels();
        $m->setCached(false);
        $r = $m->show(1);

        $this->assertNull($r->getOriginal('id_e'));
        $this->assertEquals('65536', $r->id_e);

        Model::unsetBuilder();
        Model::unsetRestClient();

        $mock = new Response(200, [], json_encode(['data' => ['id' => 1, 'name' => 'Foo', 'email' => 'bar@foo.com'], 'status' => 'success', 'total' => 0]));
        $c    = m::mock(ClientInterface::class);
        $c->shouldReceive('request')
            ->with('PUT', 'foo-models/1', ['body' => json_encode(['name' => 'Bar', 'email' => 'bar@foo.com']), 'query' => null, 'headers' => null])
            ->andReturn($mock);

        Model::setRestClient($c);

        $r->email = "bar@foo.com";
        $r->name  = "Bar";
        $this->assertEquals("bar@foo.com", $r->email);

        $this->assertTrue($r->save());
    }

    protected function makeCache()
    {
        $dispatcher = new Illuminate\Events\Dispatcher(m::mock('Illuminate\Container\Container'));
        $cache      = new Cache(new Repository(new ArrayStore()));
        $cache->setEventDispatcher($dispatcher);

        return $cache;

    }

    protected function setMockModel()
    {
        $mock = new Response(200, [], json_encode(['data' => ['id' => 1, 'name' => 'Foo', 'email' => 'bar@foo.com'], 'status' => 'success', 'total' => 0]));

        $c = m::mock(ClientInterface::class);
        $c->shouldReceive('request')
            ->withAnyArgs()
            ->andReturn($mock);

        return $c;
    }
}

class FooModels extends Model
{
    protected $related_cache = ['BarModels'];

    public function getFooAttribute($value)
    {
        return 'mutated';
    }
}

class BarModels extends Model
{

}