<?php

namespace webignition\Tests\WebResource\Service;

use GuzzleHttp\Message\ResponseInterface as HttpResponse;
use GuzzleHttp\Message\RequestInterface as HttpRequest;
use webignition\InternetMediaType\InternetMediaType;
use webignition\InternetMediaType\Parser\Parser as InternetMediaTypeParser;
use webignition\WebResource\Exception\InvalidContentTypeException;

class InvalidContentTypeExceptionTest extends AbstractExceptionTest
{
    /**
     * @dataProvider exceptionDataProvider
     *
     * @param InternetMediaType $responseContentType
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @param string $expectedMessage
     * @param int $expectedCode
     * @param InternetMediaType $expectedResponseContentType
     */
    public function testException(
        InternetMediaType $responseContentType,
        HttpResponse $response,
        HttpRequest $request,
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
                'response' => $this->createResponse('OK', 200),
                'request' => \Mockery::mock(HttpRequest::class),
                'expectedMessage' => 'OK',
                'expectedCode' => 200,
                'expectedResponseContentType' => $mediaType,
            ];
        }

        return $testData;
    }
}
