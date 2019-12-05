<?php

use App\MutableArray;
use PHPUnit\Framework\TestCase;

final class App_MutableArrayTest extends TestCase
{
    public function testCanInitialize()
    {
        $marray = new MutableArray([ "foo" => 1, "bar" => 2 ]);
        $this->assertEquals(1, $marray["foo"]);
        $this->assertEquals(2, $marray["bar"]);
    }

    public function testCanSetValue()
    {
        $marray = new MutableArray([]);
        $marray["foo"] = 3;
        $marray["bar"] = 4;
        $this->assertEquals(3, $marray["foo"]);
        $this->assertEquals(4, $marray["bar"]);
    }

    public function testCanUnsetValue()
    {
        $marray = new MutableArray([ "foo" => 1, "bar" => 2 ]);
        unset($marray["foo"]);
        $this->assertFalse(isset($marray["foo"]));
        $this->assertTrue(isset($marray["bar"]));
    }
}
