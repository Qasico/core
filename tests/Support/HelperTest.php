<?php

class HelperTest extends TestCase
{
    public function testEncryption()
    {
        $e = encryption(1);

        $this->assertEquals('65536', $e);
    }

    public function testDecryption()
    {
        $d = decryption('65536');

        $this->assertEquals(1, $d);
    }

    public function testArrayToObject()
    {
        $o = array_to_object(['foo' => 'bar']);

        $this->assertInternalType('object', $o);
    }

    public function testObjectToArray()
    {
        $a = object_to_array((object) ['foo' => 'bar']);

        $this->assertInternalType('array', $a);
    }

}