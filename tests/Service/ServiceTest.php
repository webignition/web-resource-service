<?php

namespace webignition\Tests\WebResource\Service;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
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
     * @param array $configurationValues
     * @param array $httpFixtures
     * @param string $expectedExceptionMessage
     * @param int $expectedExceptionCode
     */
    public function testThrowsWebResourceException(
        array $configurationValues,
        array $httpFixtures,
        $expectedExceptionMessage,
        $expectedExceptionCode
    ) {
        $mockHandler = new MockHandler($httpFixtures);
        $httpClient = new HttpClient([
            'handler' => HandlerStack::create($mockHandler),
        ]);

        $request = new Request('GET', 'http://example.com');

        $service = new Service();
        $service->setConfiguration(new Configuration(array_merge(
            [
                Configuration::CONFIG_KEY_HTTP_CLIENT => $httpClient,
            ],
            $configurationValues
        )));

        try {
            $service->get($request);
            $this->fail(WebResourceException::class . ' not thrown');
        } catch (WebResourceException $webResourceException) {
            $this->assertEquals($expectedExceptionMessage, $webResourceException->getMessage());
            $this->assertEquals($expectedExceptionCode, $webResourceException->getCode());
        }

        $this->assertEquals(0, $mockHandler->count());
    }

    /**
     * @return array
     */
    public function throwsWebResourceExceptionDataProvider()
    {
        return [
            'http 404' => [
                'configurationValues' => [],
                'httpFixtures' => [
                    new Response(404),
                ],
                'expectedExceptionMessage' => 'Not Found',
                'expectedExceptionCode' => 404,
            ],
            'http 404 retry with url encoding disabled' => [
                'configurationValues' => [
                    Configuration::CONFIG_RETRY_WITH_URL_ENCODING_DISABLED => true,
                ],
                'httpFixtures' => [
                    new Response(404),
                    new Response(404),
                ],
                'expectedExceptionMessage' => 'Not Found',
                'expectedExceptionCode' => 404,
            ],
            'http 404 with content-type pre-verification' => [
                'configurationValues' => [
                    Configuration::CONFIG_RETRY_WITH_URL_ENCODING_DISABLED => true,
                    Configuration::CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES => false,
                ],
                'httpFixtures' => [
                    new Response(404),
                    new Response(404),
                    new Response(404),
                    new Response(404),
                    new Response(404),
                    new Response(404),
                ],
                'expectedExceptionMessage' => 'Not Found',
                'expectedExceptionCode' => 404,
            ],
            'http 500' => [
                'configurationValues' => [],
                'httpFixtures' => [
                    new Response(500),
                ],
                'expectedExceptionMessage' => 'Internal Server Error',
                'expectedExceptionCode' => 500,
            ],
            'http 100' => [
                'configurationValues' => [],
                'httpFixtures' => [
                    new Response(100)
                ],
                'expectedExceptionMessage' => 'Continue',
                'expectedExceptionCode' => 100,
            ],
            'http 301' => [
                'configurationValues' => [],
                'httpFixtures' => [
                    new Response(301),
                ],
                'expectedExceptionMessage' => 'Moved Permanently',
                'expectedExceptionCode' => 301,
            ],
        ];
    }

    /**
     * @dataProvider getInvalidContentTypeDataProvider
     *
     * @param array $configurationValues
     * @param array $httpFixtures
     * @param string $expectedExceptionMessage
     * @param int $expectedExceptionCode
     * @param string $expectedExceptionResponseContentType
     *
     * @throws WebResourceException
     */
    public function testGetInvalidContentType(
        array $configurationValues,
        array $httpFixtures,
        $expectedExceptionMessage,
        $expectedExceptionCode,
        $expectedExceptionResponseContentType
    ) {
        $mockHandler = new MockHandler($httpFixtures);
        $httpClient = new HttpClient([
            'handler' => HandlerStack::create($mockHandler),
        ]);

        $request = new Request('GET', 'http://example.com');

        $service = new Service();
        $service->setConfiguration(new Configuration(array_merge(
            [
                Configuration::CONFIG_KEY_HTTP_CLIENT => $httpClient,
            ],
            $configurationValues
        )));

        try {
            $service->get($request);
            $this->fail(InvalidContentTypeException::class . ' not thrown');
        } catch (InvalidContentTypeException $invalidContentTypeException) {
            $this->assertEquals($expectedExceptionMessage, $invalidContentTypeException->getMessage());
            $this->assertEquals($expectedExceptionCode, $invalidContentTypeException->getCode());

            $this->assertEquals(
                $expectedExceptionResponseContentType,
                (string)$invalidContentTypeException->getResponseContentType()
            );
        }

        $this->assertEquals(0, $mockHandler->count());
    }

    /**
     * @return array
     */
    public function getInvalidContentTypeDataProvider()
    {
        return [
            'no allowed content types; fails pre-verification' => [
                'configurationValues' => [
                    Configuration::CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES => false,
                    Configuration::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => [],
                ],
                'httpFixtures' => [
                    new Response(),
                ],
                'expectedExceptionMessage' => 'OK',
                'expectedExceptionCode' => 200,
                'expectedExceptionResponseContentType' => '',
            ],
            'disallowed content type; fails pre-verification' => [
                'configurationValues' => [
                    Configuration::CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES => false,
                    Configuration::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => [
                        'text/html' => WebPage::class,
                    ],
                ],
                'httpFixtures' => [
                    new Response(200, [
                        'Content-Type' => 'text/plain',
                    ]),
                ],
                'expectedExceptionMessage' => 'OK',
                'expectedExceptionCode' => 200,
                'expectedExceptionResponseContentType' => 'text/plain',
            ],
            'disallowed content type; retry with url encoding disabled; fails pre-verification' => [
                'configurationValues' => [
                    Configuration::CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES => false,
                    Configuration::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => [
                        'text/html' => WebPage::class,
                    ],
                    Configuration::CONFIG_RETRY_WITH_URL_ENCODING_DISABLED => true,
                ],
                'httpFixtures' => [
                    new Response(500),
                    new Response(200, ['Content-Type' => 'text/plain']),
                ],
                'expectedExceptionMessage' => 'OK',
                'expectedExceptionCode' => 200,
                'expectedExceptionResponseContentType' => 'text/plain',
            ],
            'no allowed content types; fails post-verification' => [
                'configurationValues' => [
                    Configuration::CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES => false,
                    Configuration::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => [],
                ],
                'httpFixtures' => [
                    new Response(404),
                    new Response(200),
                ],
                'expectedExceptionMessage' => 'OK',
                'expectedExceptionCode' => 200,
                'expectedExceptionResponseContentType' => '',
            ],
        ];
    }

    /**
     * @dataProvider getSuccessDataProvider
     *
     * @param array $configurationValues
     * @param array $httpFixtures
     * @param string $expectedResourceClassName
     * @param string $expectedResourceContent
     *
     * @throws InvalidContentTypeException
     * @throws WebResourceException
     */
    public function testGetSuccess(
        array $configurationValues,
        array $httpFixtures,
        $expectedResourceClassName,
        $expectedResourceContent
    ) {
        $mockHandler = new MockHandler($httpFixtures);
        $httpClient = new HttpClient([
            'handler' => HandlerStack::create($mockHandler),
        ]);

        $request = new Request('GET', 'http://example.com');

        $service = new Service();
        $service->setConfiguration(new Configuration(array_merge(
            [
                Configuration::CONFIG_KEY_HTTP_CLIENT => $httpClient,
            ],
            $configurationValues
        )));

        $resource = $service->get($request);

        $this->assertInstanceOf($expectedResourceClassName, $resource);
        $this->assertEquals($expectedResourceContent, $resource->getContent());

        $this->assertEquals(0, $mockHandler->count());
    }

    /**
     * @return array
     */
    public function getSuccessDataProvider()
    {
        return [
            'text/plain no mapped resource type' => [
                'configurationValues' => [],
                'httpFixtures' => [
                    new Response(200, ['Content-Type' => 'text/plain'], 'Foo'),
                ],
                'expectedResourceClassName' => WebResource::class,
                'expectedResourceContent' => 'Foo',
            ],
            'text/html no mapped resource type' => [
                'configurationValues' => [],
                'httpFixtures' => [
                    new Response(200, ['Content-Type' => 'text/html'], '<!doctype><html>'),
                ],
                'expectedResourceClassName' => WebResource::class,
                'expectedResourceContent' => '<!doctype><html>',
            ],
            'text/html with mapped resource type' => [
                'configurationValues' => [
                    Configuration::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => [
                        'text/html' => WebPage::class,
                    ],
                ],
                'httpFixtures' => [
                    new Response(200, ['Content-Type' => 'text/html'], '<!doctype><html>'),
                ],
                'expectedResourceClassName' => WebPage::class,
                'expectedResourceContent' => '<!doctype><html>',
            ],
            'text/html with mapped resource type and content-type pre-verification' => [
                'configurationValues' => [
                    Configuration::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => [
                        'text/html' => WebPage::class,
                    ],
                    Configuration::CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES => false,
                ],
                'httpFixtures' => [
                    new Response(200, ['Content-Type' => 'text/html']),
                    new Response(200, ['Content-Type' => 'text/html'], '<!doctype><html>'),
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
}
