<?php

use Qasico\Support\Traits\Accessible;

class AccessibleTraitTest extends TestCase
{
    public function testGettingProperties()
    {
        $d = new DummyClassStub();

        $this->assertEquals('test', $d->value);
    }
}

class DummyClassStub
{
    use Accessible;

    protected $value = 'test';
}