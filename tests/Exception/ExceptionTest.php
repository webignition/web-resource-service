<?php

namespace webignition\Tests\WebResource\Service;

use GuzzleHttp\Message\ResponseInterface as HttpResponse;
use GuzzleHttp\Message\RequestInterface as HttpRequest;
use webignition\WebResource\Exception\Exception as WebResourceException;

class ExceptionTest extends AbstractExceptionTest
{
    /**
     * @dataProvider exceptionDataProvider
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @param string $expectedMessage
     * @param int $expectedCode
     */
    public function testException(HttpResponse $response, HttpRequest $request, $expectedMessage, $expectedCode)
    {
        $exception = new WebResourceException($response, $request);

        $this->assertEquals($response, $exception->getResponse());
        $this->assertEquals($request, $exception->getRequest());
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals($expectedCode, $exception->getCode());
    }

    /**
     * @return array
     */
    public function exceptionDataProvider()
    {
        $testDataValues = [
            404 => 'Not Found',
            500 => 'Internal Server Error',
            503 => 'Unavailable',
        ];

        $testData = [];

        foreach ($testDataValues as $statusCode => $reasonPhrase) {
            $testData[$statusCode] = [
                'response' => $this->createResponse($reasonPhrase, $statusCode),
                'request' => \Mockery::mock(HttpRequest::class),
                'expectedMessage' => $reasonPhrase,
                'expectedCode' => $statusCode,
            ];
        }

        return $testData;
    }
}
