<?php

namespace webignition\Tests\WebResource\Service;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Subscriber\Mock as MockSubscriber;
use Mockery\MockInterface;
use webignition\WebResource\Exception\InvalidContentTypeException;
use webignition\WebResource\Service\Configuration;
use webignition\WebResource\Service\Service;
use webignition\WebResource\Exception\Exception as WebResourceException;
use webignition\WebResource\WebPage\WebPage;
use webignition\WebResource\WebResource;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider throwsWebResourceExceptionDataProvider
     *
     * @param Configuration $configuration
     * @param array $createRequestArgs
     * @param string[] $responseFixtures
     * @param string $expectedExceptionMessage
     * @param int $expectedExceptionCode
     *
     * @throws InvalidContentTypeException
     * @throws WebResourceException
     */
    public function testThrowsWebResourceException(
        Configuration $configuration,
        $createRequestArgs,
        $responseFixtures,
        $expectedExceptionMessage,
        $expectedExceptionCode
    ) {
        $this->expectException(WebResourceException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->expectExceptionCode($expectedExceptionCode);

        $request = $this->createRequest(
            $createRequestArgs['method'],
            $createRequestArgs['url'],
            $createRequestArgs['options'],
            $responseFixtures
        );

        $service = new Service();
        $service->setConfiguration($configuration);
        $service->get($request);
    }

    /**
     * @return array
     */
    public function throwsWebResourceExceptionDataProvider()
    {
        return [
            'http 404' => [
                'configuration' => new Configuration(),
                'createRequestArgs' => [
                    'method' => 'GET',
                    'url' => 'http://example.com/',
                    'options' => [],
                ],
                'responseFixtures' => [
                    'HTTP/1.1 404 Not Found',
                ],
                'expectedExceptionMessage' => 'Not Found',
                'expectedExceptionCode' => 404,
            ],
            'http 404 retry with url encoding disabled' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_RETRY_WITH_URL_ENCODING_DISABLED => true,
                ]),
                'createRequestArgs' => [
                    'method' => 'GET',
                    'url' => 'http://example.com/',
                    'options' => [],
                ],
                'responseFixtures' => [
                    'HTTP/1.1 404 Not Found',
                    'HTTP/1.1 404 Not Found',
                ],
                'expectedExceptionMessage' => 'Not Found',
                'expectedExceptionCode' => 404,
            ],
            'http 404 with content-type pre-verification' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_RETRY_WITH_URL_ENCODING_DISABLED => true,
                    Configuration::CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES => false,
                ]),
                'createRequestArgs' => [
                    'method' => 'GET',
                    'url' => 'http://example.com/',
                    'options' => [],
                ],
                'responseFixtures' => [
                    'HTTP/1.1 404 Not Found',
                    'HTTP/1.1 404 Not Found',
                    'HTTP/1.1 404 Not Found',
                    'HTTP/1.1 404 Not Found',
                    'HTTP/1.1 404 Not Found',
                    'HTTP/1.1 404 Not Found',
                ],
                'expectedExceptionMessage' => 'Not Found',
                'expectedExceptionCode' => 404,
            ],
            'http 500' => [
                'configuration' => new Configuration(),
                'createRequestArgs' => [
                    'method' => 'GET',
                    'url' => 'http://example.com/',
                    'options' => [],
                ],
                'responseFixtures' => [
                    'HTTP/1.1 500 Internal Server Error',
                ],
                'expectedExceptionMessage' => 'Internal Server Error',
                'expectedExceptionCode' => 500,
            ],
            'http 100' => [
                'configuration' => new Configuration(),
                'createRequestArgs' => [
                    'method' => 'GET',
                    'url' => 'http://example.com/',
                    'options' => [],
                ],
                'responseFixtures' => [
                    'HTTP/1.1 100 Continue',
                ],
                'expectedExceptionMessage' => 'Continue',
                'expectedExceptionCode' => 100,
            ],
            'http 301' => [
                'configuration' => new Configuration(),
                'createRequestArgs' => [
                    'method' => 'GET',
                    'url' => 'http://example.com/',
                    'options' => [],
                ],
                'responseFixtures' => [
                    "HTTP/1.1 301 Moved Permanently",
                ],
                'expectedExceptionMessage' => 'Moved Permanently',
                'expectedExceptionCode' => 301,
            ],
        ];
    }

    /**
     * @dataProvider getInvalidContentTypeDataProvider
     *
     * @param Configuration $configuration
     * @param array $createRequestArgs
     * @param string[] $responseFixtures
     * @param string $expectedExceptionMessage
     * @param int $expectedExceptionCode
     * @param string $expectedExceptionResponseContentType
     *
     * @throws WebResourceException
     */
    public function testGetInvalidContentType(
        Configuration $configuration,
        $createRequestArgs,
        $responseFixtures,
        $expectedExceptionMessage,
        $expectedExceptionCode,
        $expectedExceptionResponseContentType
    ) {
        $request = $this->createRequest(
            $createRequestArgs['method'],
            $createRequestArgs['url'],
            $createRequestArgs['options'],
            $responseFixtures
        );

        $service = new Service();
        $service->setConfiguration($configuration);

        try {
            $service->get($request);
            $this->fail('InvalidContentTypeException not thrown');
        } catch (InvalidContentTypeException $invalidContentTypeException) {
            $this->assertEquals($expectedExceptionMessage, $invalidContentTypeException->getMessage());
            $this->assertEquals($expectedExceptionCode, $invalidContentTypeException->getCode());

            $this->assertEquals(
                $expectedExceptionResponseContentType,
                (string)$invalidContentTypeException->getResponseContentType()
            );
        }
    }

    /**
     * @return array
     */
    public function getInvalidContentTypeDataProvider()
    {
        return [
            'no allowed content types; fails pre-verification' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES => false,
                    Configuration::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => [],
                ]),
                'createRequestArgs' => [
                    'method' => 'GET',
                    'url' => 'http://example.com/',
                    'options' => [],
                ],
                'responseFixtures' => [
                    'HTTP/1.1 200 OK',
                ],
                'expectedExceptionMessage' => 'OK',
                'expectedExceptionCode' => 200,
                'expectedExceptionResponseContentType' => '',
            ],
            'disallowed content type; fails pre-verification' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES => false,
                    Configuration::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => [
                        'text/html' => WebPage::class,
                    ],
                ]),
                'createRequestArgs' => [
                    'method' => 'GET',
                    'url' => 'http://example.com/',
                    'options' => [],
                ],
                'responseFixtures' => [
                    "HTTP/1.1 200 OK\nContent-type: text/plain",
                ],
                'expectedExceptionMessage' => 'OK',
                'expectedExceptionCode' => 200,
                'expectedExceptionResponseContentType' => 'text/plain',
            ],
            'disallowed content type; retry with url encoding disabled; fails pre-verification' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES => false,
                    Configuration::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => [
                        'text/html' => WebPage::class,
                    ],
                    Configuration::CONFIG_RETRY_WITH_URL_ENCODING_DISABLED => true,
                ]),
                'createRequestArgs' => [
                    'method' => 'GET',
                    'url' => 'http://example.com/',
                    'options' => [],
                ],
                'responseFixtures' => [
                    "HTTP/1.1 500",
                    "HTTP/1.1 200 OK\nContent-type: text/plain",
                ],
                'expectedExceptionMessage' => 'OK',
                'expectedExceptionCode' => 200,
                'expectedExceptionResponseContentType' => 'text/plain',
            ],
            'no allowed content types; fails post-verification' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES => false,
                    Configuration::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => [],
                ]),
                'createRequestArgs' => [
                    'method' => 'GET',
                    'url' => 'http://example.com/',
                    'options' => [],
                ],
                'responseFixtures' => [
                    'HTTP/1.1 404 Not Found',
                    'HTTP/1.1 200 OK',
                ],
                'expectedExceptionMessage' => 'OK',
                'expectedExceptionCode' => 200,
                'expectedExceptionResponseContentType' => '',
            ],
        ];
    }

    /**
     * @dataProvider getDataProvider
     *
     * @param Configuration $configuration
     * @param array $createRequestArgs
     * @param string[] $responseFixtures
     * @param string $expectedResourceClassName
     * @param string $expectedResourceContent
     *
     * @throws InvalidContentTypeException
     * @throws WebResourceException
     */
    public function testGet(
        Configuration $configuration,
        $createRequestArgs,
        $responseFixtures,
        $expectedResourceClassName,
        $expectedResourceContent
    ) {
        $request = $this->createRequest(
            $createRequestArgs['method'],
            $createRequestArgs['url'],
            $createRequestArgs['options'],
            $responseFixtures
        );

        $service = new Service();
        $service->setConfiguration($configuration);
        $resource = $service->get($request);

        $this->assertInstanceOf($expectedResourceClassName, $resource);
        $this->assertEquals($expectedResourceContent, $resource->getContent());
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            'text/plain no mapped resource type' => [
                'configuration' => new Configuration([]),
                'createRequestArgs' => [
                    'method' => 'GET',
                    'url' => 'http://example.com/',
                    'options' => [],
                ],
                'responseFixtures' => [
                    "HTTP/1.1 200 OK\nContent-type:text/plain\n\nFoo",
                ],
                'expectedResourceClassName' => WebResource::class,
                'expectedResourceContent' => 'Foo',
            ],
            'text/html no mapped resource type' => [
                'configuration' => new Configuration([]),
                'createRequestArgs' => [
                    'method' => 'GET',
                    'url' => 'http://example.com/',
                    'options' => [],
                ],
                'responseFixtures' => [
                    "HTTP/1.1 200 OK\nContent-type:text/html\n\n<!doctype><html>",
                ],
                'expectedResourceClassName' => WebResource::class,
                'expectedResourceContent' => '<!doctype><html>',
            ],
            'text/html with mapped resource type' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => [
                        'text/html' => WebPage::class,
                    ],
                ]),
                'createRequestArgs' => [
                    'method' => 'GET',
                    'url' => 'http://example.com/',
                    'options' => [],
                ],
                'responseFixtures' => [
                    "HTTP/1.1 200 OK\nContent-type:text/html\n\n<!doctype><html>",
                ],
                'expectedResourceClassName' => WebPage::class,
                'expectedResourceContent' => '<!doctype><html>',
            ],
            'text/html with mapped resource type and content-type pre-verification' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => [
                        'text/html' => WebPage::class,
                    ],
                    Configuration::CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES => false,
                ]),
                'createRequestArgs' => [
                    'method' => 'GET',
                    'url' => 'http://example.com/',
                    'options' => [],
                ],
                'responseFixtures' => [
                    "HTTP/1.1 200 OK\nContent-type:text/html",
                    "HTTP/1.1 200 OK\nContent-type:text/html\n\n<!doctype><html>",
                ],
                'expectedResourceClassName' => WebPage::class,
                'expectedResourceContent' => '<!doctype><html>',
            ],
        ];
    }

    public function testGetDefaultConfiguration()
    {
        $service = new Service();
        $configuration = $service->getConfiguration();

        $this->assertEquals(true, $configuration->getAllowUnknownResourceTypes());
        $this->assertEquals([], $configuration->getContentTypeWebResourceMap());
        $this->assertEquals(false, $configuration->getRetryWithUrlEncodingDisabled());
    }

    /**
     * @dataProvider createDataProvider
     *
     * @param Configuration $configuration
     * @param ResponseInterface $response
     * @param string $expectedWebResourceClassName
     */
    public function testCreate(
        Configuration $configuration,
        ResponseInterface $response,
        $expectedWebResourceClassName
    ) {
        $service = new Service();
        $service->setConfiguration($configuration);
        $response = $service->create($response);

        $this->assertInstanceOf($expectedWebResourceClassName, $response);
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [
            'unknown content type' => [
                'configuration' => new Configuration(),
                'response' => $this->createResponse(
                    'text/plain'
                ),
                'expectedWebResourceClassName' => WebResource::class,
            ],
            'known content type' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => [
                        'text/html' => WebPage::class,
                    ],
                ]),
                'response' => $this->createResponse(
                    'text/html'
                ),
                'expectedWebResourceClassName' => WebPage::class,
            ],
        ];
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @param string[] $responseFixtures
     *
     * @return RequestInterface
     */
    private function createRequest($method, $url, $options, $responseFixtures)
    {
        $httpClient = new HttpClient();
        $httpClient->getEmitter()->attach(new MockSubscriber($responseFixtures));

        return $httpClient->createRequest(
            $method,
            $url,
            $options
        );
    }

    /**
     * @param string $contentType
     *
     * @return ResponseInterface|MockInterface
     */
    private function createResponse($contentType)
    {
        $response = \Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getHeader')
            ->with('content-type')
            ->andReturn($contentType);

        return $response;
    }
}
