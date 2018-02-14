<?php

namespace Dlindberg\Pasteboard;

use \PHPUnit\Framework\TestCase;

class PasteboardTest extends TestCase
{
    private $current;
    private $string = "A text String with some UTF-8 Character: —’“’”¨ÓÔÒÚÂåß∑∂ƒ©¥ü";

    public function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->current = Pasteboard::get();
    }

    public function testSet()
    {
        $this->assertTrue(Pasteboard::set("Some Value"));
    }

    public function testGet()
    {
        Pasteboard::set("some value");
        $this->assertTrue(mb_strlen(Pasteboard::get()) > 0);
    }

    public function testEmptyGet()
    {
        Pasteboard::set(null);
        $this->assertFalse(Pasteboard::get());
    }

    public function testSetToGet()
    {
        Pasteboard::set($this->string);
        $this->assertEquals($this->string, Pasteboard::get());
    }

    public function testSetArrayBasic()
    {
        $test = array('foo', 'bar');
        Pasteboard::setArray($test);
        $this->assertEquals($test[1], Pasteboard::get());
    }

    public function testSetArrayWait()
    {
        $test = array('foo', 'baz', 'bar');
        Pasteboard::setArray($test, array('wait' => 0));
        $this->assertEquals($test[2], Pasteboard::get());
    }

    public function testSetArrayReset()
    {
        Pasteboard::set($this->string);
        $test = array('foo', 'baz', array('pumpkin', 'pie', array('bag', 'end')), 'bar');
        Pasteboard::setArray($test, array('wait' => 0, 'reset' => true, 'depth' => 1));
        $this->assertEquals($this->string, Pasteboard::get());
    }

    public function testSetArrayDepth()
    {
        $test = array('foo', 'baz', array('pumpkin', 'pie', array('bag', 'end')));
        Pasteboard::setArray($test, array('wait' => 0, 'depth' => 1));
        $this->assertEquals('pie', Pasteboard::get());
    }

    public function testSetArrayDeeper()
    {
        $test = array('foo', 'baz', array('pumpkin', 'pie', array('bag', 'end')));
        Pasteboard::setArray($test, array('wait' => 0, 'depth' => 2));
        $this->assertEquals('end', Pasteboard::get());
    }

    public function tearDown()/* The :void return type declaration that should be here would cause a BC issue */
    {
        Pasteboard::set($this->current);
    }
}
