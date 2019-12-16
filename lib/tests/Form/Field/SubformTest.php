<?php

use Lib\Form\Field\Subform;
use Lib\Form\Field\Field;
use PHPUnit\Framework\TestCase;

final class Lib_Field_SubformTest extends TestCase
{
    public function testCanContainFields()
    {
        $subform = new Subform("subform");
        $subform->addField(new Field("fieldname"));

        $field = $subform->getField("fieldname");
        $this->assertInstanceOf(Field::class, $field);
        $this->assertEquals("fieldname", $field->getName());
    }

    public function testCanCombineNames()
    {
        $subform = new Subform("subform");
        $subform->addField(new Field("fieldname"));

        $field = $subform->getField("fieldname");
        $this->assertEquals("subform[fieldname]", $field->getFullName());
    }

    public function testCanSetValues()
    {
        $subform = new Subform("subform");
        $subform->addField(new Field("fieldname"));
        $subform->addField(new Field("otherfield"));
        $subform->setValue([
            "fieldname" => "one",
            "otherfield" => "two"
        ]);

        $field = $subform->getField("fieldname");
        $this->assertEquals("one", $field->getValue());
        $field = $subform->getField("otherfield");
        $this->assertEquals("two", $field->getValue());

        $value = $subform->getValue();
        $this->assertIsArray($value);
        $this->assertEquals("one", $value["fieldname"]);
        $this->assertEquals("two", $value["otherfield"]);
    }
}
