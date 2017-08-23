<?php

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Qasico\Foundation\Validation\CustomValidator;
use Qasico\Foundation\Validation\ValidatorModel;
use Qasico\Rubricate\Model;

class ExtendedValidatorTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }
    
    public function testValidateForeignKey()
    {
        $trans = $this->getRealTranslator();
        $v     = new CustomValidator($trans, new ValidatorModel(), ['x' => ['id' => 1]], ['x' => 'ForeignKey']);
        $this->assertTrue($v->passes());
        
        $v = new CustomValidator($trans, new ValidatorModel(), ['x' => 1], ['x' => 'ForeignKey']);
        $this->assertFalse($v->passes());

        $v = new CustomValidator($trans, new ValidatorModel(), ['x' => ['ids' => 1]], ['x' => 'ForeignKey']);
        $this->assertFalse($v->passes());
    }
    
    public function testValidateApiUnique()
    {
        $trans = $this->getRealTranslator();
        $mR    = new Response(200, [], json_encode(['data' => [], 'status' => 'success', 'total' => 0]));

        $p = [
            "query" => "username:asls1-_3dlks",
            "limit" => 1,
            "join"  => "none"
        ];
        $this->getMockModel('users', $p, $mR);

        $v = new CustomValidator($trans, new ValidatorModel(), ['username' => 'asls1-_3dlks'], ['username' => 'api_unique:users']);
        $this->assertTrue($v->passes());

        $v = new CustomValidator($trans, new ValidatorModel(), ['x' => 'asls1-_3dlks'], ['x' => 'api_unique:users.username']);
        $this->assertTrue($v->passes());

        $p = [
            "join"  => "none",
            "limit" => 1,
            "query" => "username:sysadmin%2Cid:5"
        ];
        $this->getMockModel('users', $p, $mR);

        $v = new CustomValidator($trans, new ValidatorModel(), ['username' => 'sysadmin', 'id' => 5], ['username' => 'api_unique:users,id']);
        $this->assertTrue($v->passes());

        $v = new CustomValidator($trans, new ValidatorModel(), ['username' => 'sysadmin', 'x' => 5], ['username' => 'api_unique:users,id[x]']);
        $this->assertTrue($v->passes());

        $mR = new Response(200, [], json_encode(['data' => [['id' => 'foo']], 'status' => 'success', 'total' => 0]));
        $p  = [
            "query" => "username:asls1-_3dlks",
            "limit" => 1,
            "join"  => "none"
        ];
        $this->getMockModel('users', $p, $mR);

        $v = new CustomValidator($trans, new ValidatorModel(), ['x' => 'asls1-_3dlks'], ['x' => 'api_unique:users.username']);
        $this->assertFalse($v->passes());

    }

    public function testValidateApiExists()
    {
        $trans = $this->getRealTranslator();
        $mR    = new Response(200, [], json_encode(['data' => [['name' => 'foo']], 'status' => 'success', 'total' => 0]));
        $p     = [
            "join"  => "none",
            "limit" => 1,
            "query" => "username:sysadmin"
        ];
        $this->getMockModel('users', $p, $mR);
        $v = new CustomValidator($trans, new ValidatorModel(), ['x' => 'sysadmin'], ['x' => 'api_exists:users.username']);
        $this->assertTrue($v->passes());

        $mR = new Response(200, [], json_encode(['data' => [['name' => 'foo']], 'status' => 'success', 'total' => 0]));
        $this->getMockModel('users', $p, $mR);
        $v = new CustomValidator($trans, new ValidatorModel(), ['username' => 'sysadmin'], ['username' => 'api_exists:users']);
        $this->assertTrue($v->passes());

        $p = [
            "join"  => "none",
            "limit" => 1,
            "query" => "username:sysadmin%2Cid:1"
        ];

        $mR = new Response(200, [], json_encode(['data' => [['name' => 'foo']], 'status' => 'success', 'total' => 0]));
        $this->getMockModel('users', $p, $mR);
        $v = new CustomValidator($trans, new ValidatorModel(), ['username' => 'sysadmin', 'id' => 1], ['username' => 'api_exists:users,id']);
        $this->assertTrue($v->passes());

        $mR = new Response(200, [], json_encode(['data' => [['name' => 'foo']], 'status' => 'success', 'total' => 0]));
        $this->getMockModel('users', $p, $mR);
        $v = new CustomValidator($trans, new ValidatorModel(), ['username' => 'sysadmin', 'x' => 1], ['username' => 'api_exists:users,id[x]']);
        $this->assertTrue($v->passes());

    }
    
    protected function getMockModel($table, $param, $response)
    {
        Model::unsetRestClient();
        Model::unsetBuilder();
        Model::unsetEventDispatcher();

        $c = m::mock(ClientInterface::class);
        $c->shouldReceive('request')
            ->with('GET', $table, ['body' => null, 'query' => $param, 'headers' => null])
            ->andReturn($response);
        
        Model::setRestClient($c);
    }
    
    protected function getRealTranslator()
    {
        $trans = new Symfony\Component\Translation\Translator('en', new Symfony\Component\Translation\MessageSelector);
        $trans->addLoader('array', new Symfony\Component\Translation\Loader\ArrayLoader);
        
        return $trans;
    }
    
}
