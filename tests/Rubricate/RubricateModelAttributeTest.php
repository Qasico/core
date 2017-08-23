<?php

use GuzzleHttp\ClientInterface;
use Mockery as m;
use Qasico\Rubricate\Model;
use Qasico\Rubricate\Rest\Response;

class RubricateModelAttributeTest extends TestCase
{

    public function __construct()
    {
        parent::__construct();
        $c = m::mock(ClientInterface::class);
        Model::setRestClient($c);
    }

    public function testDateFormatAttribute()
    {
        $m = new RubricateModelStub();

        $m->created_at = '2015-02-01T17:00:00Z';
        $this->assertEquals('2015-02-01T17:00:00Z', $m->created_at);
    }

    public function testFillableAttribute()
    {
        $m = new RubricateModelStub();

        $m->fillable(['name']);

        $this->assertEquals(['name'], $m->getFillable());
        $this->assertEquals(true, $m->isFillable('name'));

        $m->fill(['name' => 'foo', 'email' => 'bar']);

        $this->assertNull($m->email);
    }

    public function testAttributeManipulation()
    {
        $m       = new RubricateModelStub;
        $m->name = 'foo';

        $a = $m->getAttributes();
        $this->assertEquals('foo', $a['name']);

        // test mutation
        $m->list_items = ['name' => 'foo'];
        $m->total      = 200;
        $m->discount   = 10;

        $this->assertEquals(20, $m->discount);
        $this->assertEquals(['name' => 'foo'], $m->list_items);
        $attributes = $m->getAttributes();
        $this->assertEquals(json_encode(['name' => 'foo']), $attributes['list_items']);
    }

    public function testCalculatedAttributes()
    {
        $model           = new RubricateModelStub;
        $model->password = 'secret';
        $attributes      = $model->getAttributes();

        // ensure password attribute was not set to null
        $this->assertArrayNotHasKey('password', $attributes);
        $this->assertEquals('******', $model->password);

        $hash = 'e5e9fa1ba31ecd1ae84f75caaa474f3a663f05f4';

        $this->assertEquals($hash, $attributes['password_hash']);
        $this->assertEquals($hash, $model->password_hash);
    }

    public function testGetterSetterKeynameAttribute()
    {
        $m = new RubricateModelStub();

        $m->setKeyName('id');
        $this->assertEquals('id', $m->getKeyName());

        $m->id = 1;
        $this->assertEquals(1, $m->getKey());
    }

    public function testAttributeFromRelationValue()
    {
        $m = new RubricateModelStub();

        $m->setRelation('foo', 'bar');

        $this->assertEquals('bar', $m->foo);
    }

    public function testNewInstanceReturnsNewInstanceWithAttributesSet()
    {
        $model    = new RubricateModelStub;
        $instance = $model->newInstance(['name' => 'foo']);
        $this->assertInstanceOf('RubricateModelStub', $instance);
        $this->assertEquals('foo', $instance->name);
    }

    public function testHydrateCreatesCollectionOfModels()
    {
        $data     = [['name' => 'Foo'], ['name' => 'Bar']];
        $response = new Response(new \GuzzleHttp\Psr7\Response(200, [], json_encode(['data' => $data, 'status' => 'success', 'total' => 2])));

        $collection = RubricateModelStub::writeResult($response);

        $this->assertInstanceOf('Qasico\Rubricate\Collection', $collection);
        $this->assertCount(2, $collection);
        $this->assertInstanceOf('RubricateModelStub', $collection[0]);
        $this->assertInstanceOf('RubricateModelStub', $collection[1]);
        $this->assertEquals($collection[0]->getAttributes(), $collection[0]->getOriginal());
        $this->assertEquals($collection[1]->getAttributes(), $collection[1]->getOriginal());
        $this->assertEquals('Foo', $collection[0]->name);
        $this->assertEquals('Bar', $collection[1]->name);
    }
}

class RubricateModelStub extends Model
{
    protected $endpoint = 'test';

    protected $dates = ['created_at', 'updated_at'];

    public function getListItemsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getDiscountAttribute($value)
    {
        return ($this->total * $value) / 100;
    }

    public function setListItemsAttribute($value)
    {
        $this->attributes['list_items'] = json_encode($value);
    }

    public function getPasswordAttribute()
    {
        return '******';
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password_hash'] = sha1($value);
    }

    public static function bootInstances()
    {
        return true;
    }
}