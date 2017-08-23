<?php

use GuzzleHttp\Psr7\Response as BaseResponse;
use Qasico\Rubricate\Rest\Response;

class RestResponseTest extends TestCase
{
    public function testInstances()
    {
        $r        = new BaseResponse(200, [], '{}');
        $response = new Response($r);

        $this->assertNotFalse($response->getResponse());
    }

    public function testResponseSuccess()
    {
        $data     = json_encode(['data' => ['id' => 1]]);
        $r        = new BaseResponse(200, [], $data);
        $response = new Response($r);

        $this->assertEquals(true, $response->isSuccess());
        $this->assertEquals(['id' => 1], $response->getData());
    }

    public function testResponseNotFound()
    {
        $r        = new BaseResponse(404, [], '{}');
        $response = new Response($r);

        $this->assertEquals(false, $response->isSuccess());
        $this->assertEquals('Not Found', $response->getMessage());
    }

    public function testResponseSuccessButEmpty()
    {
        $r        = new BaseResponse(200, [], '{}');
        $response = new Response($r);

        $this->assertEquals(true, $response->isEmpty());

        $r        = new BaseResponse(200, [], 'null');
        $response = new Response($r);

        $this->assertEquals(true, $response->isEmpty());

    }

    public function testHandleError()
    {
        $r        = new BaseResponse(200, [], '{"error":{"orm":"field `konektifa/models.AccountingCoa.Type` cannot be NULL"},"success":false}');
        $response = new Response($r);

        $this->assertFalse($response->isSuccess());
    }

    public function testResponseContent()
    {
        $r        = new BaseResponse(200, [], '{"id": 5, "success": true}');
        $response = new Response($r);

        $this->assertEquals(5, $response->getContent('id'));


    }
}