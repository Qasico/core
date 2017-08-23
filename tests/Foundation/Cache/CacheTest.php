<?php

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Mockery as m;
use Qasico\Foundation\Cache\Cache;

class CacheTest extends TestCase
{
    public function testCacheSetTag()
    {
        $cache  = $this->cacheInstances();
        $tagged = $cache->setTag('test');

        $this->assertInstanceOf('Illuminate\Cache\TaggedCache', $tagged);
    }

    public function testCacheSetRelatedTag()
    {
        $cache = $this->cacheInstances();
        $cache->setRelatedTags(['test']);

        $this->assertAttributeEquals(['test'], 'relatedTags', $cache);
    }

    public function testCacheMakeInstanceTagged()
    {
        $cache = $this->cacheInstances();
        $cache->makeCache('test');

        $this->assertAttributeInstanceOf('Illuminate\Cache\TaggedCache', 'tagged', $cache);
    }

    public function testCacheSave()
    {
        $c = $this->makeCache('test');

        $v = $c->save('foo', 'bar');
        $this->assertEquals('bar', $v);

        $v = $c->save('bar', 'foo', 20);
        $this->assertEquals('foo', $v);
    }

    public function testCacheRead()
    {
        $c = $this->makeCache('test');
        $c->save('foo', 'bar');

        $this->assertEquals('bar', $c->read('foo'));
    }

    public function testCacheFlush()
    {
        $cx = $this->makeCache('test');
        $cx->save('foo', 'bar');
        $cx->flush();
        $this->assertFalse($cx->read('foo'));

        $c = $this->makeCache('test2');
        $c->setRelatedTags(['test']);
        $c->save('foox', 'barx');
        $c->flush();
        $this->assertFalse($c->read('foox'));
    }

    protected function cacheInstances()
    {
        $dispatcher = new Illuminate\Events\Dispatcher(m::mock('Illuminate\Container\Container'));
        $cache      = new Cache(new Repository(new ArrayStore()));
        $cache->setEventDispatcher($dispatcher);

        return $cache;
    }

    protected function makeCache($tag)
    {
        $dispatcher = new Illuminate\Events\Dispatcher(m::mock('Illuminate\Container\Container'));
        $cache      = new Cache(new Repository(new ArrayStore()));
        $cache->setEventDispatcher($dispatcher);

        return $cache->makeCache($tag);
    }
}