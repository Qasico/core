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

    public function testFkFormat()
    {
        $fk = fk_format(1);
        $this->assertArrayHasKey('id', $fk);

        $fk = fk_format(0);
        $this->assertFalse($fk);

        $fk = fk_format(encryption(1));
        $this->assertEquals(1, $fk['id']);
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