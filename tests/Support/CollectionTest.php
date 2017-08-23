<?php

use Qasico\Support\Collection;

class CollectionTest extends TestCase
{
    public function testCollectionShouldExtendLaravelCollection()
    {
        $c = new Collection();
        $this->assertInstanceOf('Illuminate\Support\Collection', $c);
    }

    public function testCollectionItemsShouldBeArray()
    {
        $c = new Collection(['foo', 'bar']);

        $this->assertAttributeInternalType('array', 'items', $c);
        $this->assertAttributeEquals(2, 'total', $c);
    }

    public function testCollectionCanSetTotal()
    {
        $c = new Collection(['foo', 'bar'], 200);

        $this->assertEquals(200, $c->getTotal());
        $this->assertAttributeEquals(200, 'total', $c);
    }

    public function testCollectionShouldCalculateTotal()
    {
        $c = new Collection(['foo', 'bar']);

        $this->assertEquals(2, $c->getTotal());
        $this->assertAttributeEquals(2, 'total', $c);
    }

    public function testCollectionToObject()
    {
        $c = new Collection(['foo', 'bar']);
        $this->assertInternalType('object', $c->toObject());

        $c = new Collection();
        $this->assertFalse($c->toObject());
    }

    public function testCollectionEmpty()
    {
        $c = new Collection();

        $this->assertTrue($c->isEmpty());
    }

    public function testCollectionRemoveValues()
    {
        $c = new Collection(['foo' => 'baxx', 'bar' => 'bazz']);
        $c->remove('baxx');
        $c->remove('barr');

        $this->assertArrayNotHasKey('foo', $c->all());
    }

    public function testCollectionHasKey()
    {
        $c = new Collection(['foo' => 'bar', 'bar' => 'foo']);

        $this->assertTrue($c->has('foo'));
    }

    public function testCollectionGetKey()
    {
        $c = new Collection(['foo' => 'bar', 'bar' => 'foo']);

        $this->assertEquals('bar', $c->get('foo'));
    }

    public function testCollectionRetriveItems()
    {
        $c = new Collection(['foo' => 'bar', 'bar' => 'foo']);

        $this->assertEquals('foo', $c->bar);
        $this->assertFalse($c->wow);
    }
}