<?php

use Lib\Form\Field\Field;
use PHPUnit\Framework\TestCase;

final class Lib_Field_FieldTest extends TestCase
{
    public function testCanSetValue()
    {
        $field = new Field("fieldname");
        $field->setValue("123");
        $this->assertEquals("123", $field->getValue());
    }

    public function testCanBeRequired()
    {
        $field = new Field("fieldname");
        $this->assertFalse($field->isRequired());

        $field = new Field("fieldname", [ "required" => true ]);
        $this->assertTrue($field->isRequired());
    }

    public function testCanGetNameAndId()
    {
        $field = new Field("fieldname");

        $this->assertEquals("fieldname", $field->getName());
        $this->assertEquals("fieldname", $field->getFullName());
        $this->assertEquals("fieldname", $field->getId());
    }
}
