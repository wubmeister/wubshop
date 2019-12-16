<?php

use Lib\Form\Form;
use Lib\Form\Field\Field;
use PHPUnit\Framework\TestCase;

final class Lib_FormTest extends TestCase
{
    public function testCanContainFields()
    {
        $form = new Form("form");
        $form->addField(new Field("fieldname"));

        $field = $form->getField("fieldname");
        $this->assertInstanceOf(Field::class, $field);
        $this->assertEquals("fieldname", $field->getName());
    }

    public function testCanSetValues()
    {
        $form = new Form("form");
        $form->addField(new Field("fieldname"));
        $form->addField(new Field("otherfield"));
        $form->setValues([
            "fieldname" => "one",
            "otherfield" => "two"
        ]);

        $field = $form->getField("fieldname");
        $this->assertEquals("one", $field->getValue());
        $field = $form->getField("otherfield");
        $this->assertEquals("two", $field->getValue());

        $value = $form->getValues();
        $this->assertIsArray($value);
        $this->assertEquals("one", $value["fieldname"]);
        $this->assertEquals("two", $value["otherfield"]);
    }
}
