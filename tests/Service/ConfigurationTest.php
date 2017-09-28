<?php

namespace webignition\Tests\WebResource\Service;

use webignition\WebResource\Service\Configuration;
use GuzzleHttp\Client as HttpClient;
use webignition\WebResource\WebResource;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider createConfigurationDataProvider
     *
     * @param array $configurationValues
     * @param HttpClient|null $expectedHttpClient
     * @param array $expectedContentTypeWebResourceMap
     * @param bool $expectedAllowUnknownResourceTypes
     * @param bool $expectedRetryWithUrlEncodingDisabled
     */
    public function testCreateConfiguration(
        array $configurationValues,
        $expectedHttpClient,
        array $expectedContentTypeWebResourceMap,
        $expectedAllowUnknownResourceTypes,
        $expectedRetryWithUrlEncodingDisabled
    ) {
        $configuration = new Configuration($configurationValues);

        $this->assertEquals($expectedContentTypeWebResourceMap, $configuration->getContentTypeWebResourceMap());
        $this->assertEquals($expectedAllowUnknownResourceTypes, $configuration->getAllowUnknownResourceTypes());
        $this->assertEquals($expectedRetryWithUrlEncodingDisabled, $configuration->getRetryWithUrlEncodingDisabled());

        $httpClient = $configuration->getHttpClient();
        $this->assertInstanceOf(HttpClient::class, $httpClient);

        if (!empty($expectedHttpClient)) {
            $this->assertEquals($expectedHttpClient, $httpClient);
        }
    }

    /**
     * @return array
     */
    public function createConfigurationDataProvider()
    {
        $httpClient = new HttpClient();

        return [
            'default' => [
                'configurationValues' => [],
                'expectedHttpClient' => null,
                'expectedContentTypeWebResourceMap' => [],
                'expectedAllowUnknownResourceTypes' => true,
                'expectedRetryWithUrlEncodingDisabled' => false,
            ],
            'set to non-default values' => [
                'configurationValues' => [
                    Configuration::CONFIG_KEY_HTTP_CLIENT => $httpClient,
                    Configuration::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => [
                        'foo/bar' => 'foobar',
                    ],
                    Configuration::CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES => false,
                    Configuration::CONFIG_RETRY_WITH_URL_ENCODING_DISABLED => true,
                ],
                'expectedHttpClient' => $httpClient,
                'expectedContentTypeWebResourceMap' => [
                    'foo/bar' => 'foobar',
                ],
                'expectedAllowUnknownResourceTypes' => false,
                'expectedRetryWithUrlEncodingDisabled' => true,
            ],
        ];
    }

    public function testEnableDisableRetryWithUrlEncodingDisabled()
    {
        $configuration = new Configuration();
        $this->assertFalse($configuration->getRetryWithUrlEncodingDisabled());

        $configuration->enableRetryWithUrlEncodingDisabled();
        $this->assertTrue($configuration->getRetryWithUrlEncodingDisabled());

        $configuration->disableRetryWithUrlEncodingDisabled();
        $this->assertFalse($configuration->getRetryWithUrlEncodingDisabled());
    }

    /**
     * @dataProvider hasMappedWebResourceClassNameDataProvider
     *
     * @param array $contentTypeWebResourceMap
     * @param string $contentType
     * @param bool $expectedHasMappedWebResourceClassName
     */
    public function testHasMappedWebResourceClassName(
        $contentTypeWebResourceMap,
        $contentType,
        $expectedHasMappedWebResourceClassName
    ) {
        $configuration = new Configuration([
            Configuration::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => $contentTypeWebResourceMap
        ]);

        $this->assertEquals(
            $expectedHasMappedWebResourceClassName,
            $configuration->hasMappedWebResourceClassName($contentType)
        );
    }

    /**
     * @return array
     */
    public function hasMappedWebResourceClassNameDataProvider()
    {
        return [
            'empty content type web resource map' => [
                'contentTypeWebResourceMap' => [],
                'contentType' => 'foo/bar',
                'expectedHasMappedWebResourceClassName' => false,
            ],
            'does not have' => [
                'contentTypeWebResourceMap' => [
                    'foo' => 'bar',
                ],
                'contentType' => 'foo/bar',
                'expectedHasMappedWebResourceClassName' => false,
            ],
            'does have' => [
                'contentTypeWebResourceMap' => [
                    'foo' => 'bar',
                    'foo/bar' => 'foobar',
                ],
                'contentType' => 'foo/bar',
                'expectedHasMappedWebResourceClassName' => true,
            ],
        ];
    }

    /**
     * @dataProvider getWebResourceClassNameDataProvider
     *
     * @param array $contentTypeWebResourceMap
     * @param string $contentType
     * @param string $expectedClassName
     */
    public function testGetWebResourceClassName(
        $contentTypeWebResourceMap,
        $contentType,
        $expectedClassName
    ) {
        $configuration = new Configuration([
            Configuration::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => $contentTypeWebResourceMap
        ]);

        $this->assertEquals(
            $expectedClassName,
            $configuration->getWebResourceClassName($contentType)
        );
    }

    /**
     * @return array
     */
    public function getWebResourceClassNameDataProvider()
    {
        return [
            'empty content type web resource map' => [
                'contentTypeWebResourceMap' => [],
                'contentType' => 'foo/bar',
                'expectedClassName' => WebResource::class,
            ],
            'does not have' => [
                'contentTypeWebResourceMap' => [
                    'foo' => 'bar',
                ],
                'contentType' => 'foo/bar',
                'expectedClassName' => WebResource::class,
            ],
            'does have' => [
                'contentTypeWebResourceMap' => [
                    'foo' => 'bar',
                    'foo/bar' => 'foobar',
                ],
                'contentType' => 'foo/bar',
                'expectedClassName' => 'foobar',
            ],
        ];
    }
}
