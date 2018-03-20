<?php

namespace webignition\Tests\WebResource\Service;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webignition\InternetMediaType\InternetMediaType;
use webignition\InternetMediaType\Parser\Parser as InternetMediaTypeParser;
use webignition\Tests\WebResource\Service\Factory\ResponseFactory;
use webignition\WebResource\Exception\InvalidContentTypeException;

class InvalidContentTypeExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider exceptionDataProvider
     *
     * @param InternetMediaType $responseContentType
     * @param ResponseInterface $response
     * @param RequestInterface $request
     * @param string $expectedMessage
     * @param int $expectedCode
     * @param InternetMediaType $expectedResponseContentType
     */
    public function testException(
        InternetMediaType $responseContentType,
        ResponseInterface $response,
        RequestInterface $request,
        $expectedMessage,
        $expectedCode,
        InternetMediaType $expectedResponseContentType
    ) {
        $exception = new InvalidContentTypeException(
            $responseContentType,
            $response,
            $request
        );

        $this->assertEquals($response, $exception->getResponse());
        $this->assertEquals($request, $exception->getRequest());
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals($expectedCode, $exception->getCode());
        $this->assertEquals($expectedResponseContentType, $exception->getResponseContentType());
    }

    /**
     * @return array
     */
    public function exceptionDataProvider()
    {
        $mediaTypeParser = new InternetMediaTypeParser();

        $testDataValues = [
            'text/plain',
            'image/png',
            'application/pdf'
        ];

        $testData = [];

        foreach ($testDataValues as $contentTypeString) {
            $mediaType = $mediaTypeParser->parse($contentTypeString);

            $testData[$contentTypeString] = [
                'responseContentType' => $mediaType,
                'response' => ResponseFactory::create(),
                'request' => \Mockery::mock(RequestInterface::class),
                'expectedMessage' => 'OK',
                'expectedCode' => 200,
                'expectedResponseContentType' => $mediaType,
            ];
        }

        return $testData;
    }
}
