<?php

use Qasico\Foundation\Validation\CustomValidator;
use Qasico\Foundation\Validation\ValidatorModel;

class StringValidatorTest extends TestCase
{
    public function testValidateAlphaSpaces()
    {
        $trans = $this->getRealTranslator();

        $v = new CustomValidator($trans, new ValidatorModel(), ['x' => 'abc123@!#'], ['x' => 'alpha_spaces']);
        $this->assertFalse($v->passes());

        $v = new CustomValidator($trans, new ValidatorModel(), ['x' => 'abc def'], ['x' => 'alpha_spaces']);
        $this->assertTrue($v->passes());
    }

    public function testValidateAlphaNumSpaces()
    {
        $trans = $this->getRealTranslator();

        $v = new CustomValidator($trans, new ValidatorModel(), ['x' => 'abc123@!# asd'], ['x' => 'alpha_num_spaces']);
        $this->assertFalse($v->passes());

        $v = new CustomValidator($trans, new ValidatorModel(), ['x' => 'abc def 123'], ['x' => 'alpha_num_spaces']);
        $this->assertTrue($v->passes());
    }

    protected function getRealTranslator()
    {
        $trans = new Symfony\Component\Translation\Translator('en', new Symfony\Component\Translation\MessageSelector);
        $trans->addLoader('array', new Symfony\Component\Translation\Loader\ArrayLoader);

        return $trans;
    }
}
