<?php

namespace webignition\Tests\WebResource\Service;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webignition\Tests\WebResource\Service\Factory\ResponseFactory;
use webignition\WebResource\Exception\Exception as WebResourceException;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider exceptionDataProvider
     *
     * @param ResponseInterface $response
     * @param RequestInterface $request
     * @param string $expectedMessage
     * @param int $expectedCode
     */
    public function testException(
        ResponseInterface $response,
        RequestInterface $request,
        $expectedMessage,
        $expectedCode
    ) {
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
                'response' => ResponseFactory::create($statusCode, $reasonPhrase),
                'request' => \Mockery::mock(RequestInterface::class),
                'expectedMessage' => $reasonPhrase,
                'expectedCode' => $statusCode,
            ];
        }

        return $testData;
    }
}
