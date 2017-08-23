<?php

use Mockery as m;
use Qasico\Foundation\Validation\CustomValidator;
use Qasico\Foundation\Validation\ValidatorModel;

class NotNullValidatorTest extends TestCase
{
    public function testValidateNotNullInteger()
    {
        $trans = $this->getRealTranslator();

        $v = new CustomValidator($trans, new ValidatorModel(), ['x' => 0], ['x' => 'not_null']);
        $this->assertFalse($v->passes());
    }

    public function testValidateNotNullBool()
    {
        $trans = $this->getRealTranslator();

        $v = new CustomValidator($trans, new ValidatorModel(), ['x' => false], ['x' => 'not_null']);
        $this->assertFalse($v->passes());
    }

    protected function getRealTranslator()
    {
        $trans = new Symfony\Component\Translation\Translator('en', new Symfony\Component\Translation\MessageSelector);
        $trans->addLoader('array', new Symfony\Component\Translation\Loader\ArrayLoader);

        return $trans;
    }

}
