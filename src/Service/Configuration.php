<?php

namespace webignition\WebResource\Service;

use GuzzleHttp\Client as HttpClient;
use webignition\WebResource\WebResource;

class Configuration
{
    const DEFAULT_WEB_RESOURCE_MODEL = WebResource::class;

    const CONFIG_KEY_HTTP_CLIENT = 'http-client';
    const CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP = 'content-type-web-resource-map';
    const CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES = 'allow-unknown-resource-types';
    const CONFIG_RETRY_WITH_URL_ENCODING_DISABLED = 'retry-with-url-encoding-disabled';

    /**
     * Maps content types to WebResource subclasses
     *
     * @var array
     */
    private $contentTypeWebResourceMap = array();

    /**
     * @var boolean
     */
    private $allowUnknownResourceTypes = true;

    /**
     * @var boolean
     */
    private $retryWithUrlEncodingDisabled = false;

    /**
     * @var HttpClient
     */
    private $httpClient = null;

    /**
     * @param $configurationValues
     */
    public function __construct(array $configurationValues = [])
    {
        $this->httpClient = (isset($configurationValues[self::CONFIG_KEY_HTTP_CLIENT]))
            ? $configurationValues[self::CONFIG_KEY_HTTP_CLIENT]
            : new HttpClient();

        if (isset($configurationValues[self::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP])) {
            $this->contentTypeWebResourceMap = $configurationValues[self::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP];
        }

        if (isset($configurationValues[self::CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES])) {
            $this->allowUnknownResourceTypes = $configurationValues[self::CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES];
        }

        if (isset($configurationValues[self::CONFIG_RETRY_WITH_URL_ENCODING_DISABLED])) {
            $this->retryWithUrlEncodingDisabled = $configurationValues[self::CONFIG_RETRY_WITH_URL_ENCODING_DISABLED];
        }
    }

    /**
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @return array
     */
    public function getContentTypeWebResourceMap()
    {
        return $this->contentTypeWebResourceMap;
    }

    /**
     * @return boolean
     */
    public function getAllowUnknownResourceTypes()
    {
        return $this->allowUnknownResourceTypes;
    }

    /**
     * @return boolean
     */
    public function getRetryWithUrlEncodingDisabled()
    {
        return $this->retryWithUrlEncodingDisabled;
    }

    /**
     * @param string $contentType
     *
     * @return boolean
     */
    public function hasMappedWebResourceClassName($contentType)
    {
        return isset($this->contentTypeWebResourceMap[$contentType]);
    }

    /**
     * Get the WebResource subclass name for a given content type
     *
     * @param string $contentType
     *
     * @return string
     */
    public function getWebResourceClassName($contentType)
    {
        return ($this->hasMappedWebResourceClassName($contentType))
            ? $this->contentTypeWebResourceMap[(string)$contentType]
            : self::DEFAULT_WEB_RESOURCE_MODEL;
    }

    /**
     * @param array $configurationValues
     *
     * @return Configuration
     */
    public function createFromCurrent($configurationValues)
    {
        $currentConfigurationValues = [
            self::CONFIG_KEY_CONTENT_TYPE_WEB_RESOURCE_MAP => $this->getContentTypeWebResourceMap(),
            self::CONFIG_ALLOW_UNKNOWN_RESOURCE_TYPES => $this->getAllowUnknownResourceTypes(),
            self::CONFIG_RETRY_WITH_URL_ENCODING_DISABLED => $this->getRetryWithUrlEncodingDisabled(),
            self::CONFIG_KEY_HTTP_CLIENT => $this->getHttpClient(),
        ];

        return new Configuration(array_merge($currentConfigurationValues, $configurationValues));
    }
}
