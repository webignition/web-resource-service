<?php

namespace webignition\WebResource\Service;

use GuzzleHttp\Exception\BadResponseException;
use webignition\InternetMediaType\InternetMediaType;
use webignition\InternetMediaType\Parser\Parser as InternetMediaTypeParser;
use webignition\WebResource\Exception\Exception as WebResourceException;
use webignition\WebResource\Exception\Exception;
use webignition\WebResource\Exception\InvalidContentTypeException;
use GuzzleHttp\Message\ResponseInterface as HttpResponse;
use GuzzleHttp\Message\RequestInterface as HttpRequest;
use webignition\WebResource\WebResource;

class Service
{
    /**
     * @var Configuration
     */
    private $configuration = null;

    /**
     * @param Configuration $configuration
     */
    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        if (is_null($this->configuration)) {
            $this->configuration = new Configuration();
        }

        return $this->configuration;
    }

    /**
     * @param HttpRequest $request
     * @param bool|null $retryWithUrlEncodingDisabled
     *
     * @return WebResource
     * @throws Exception
     * @throws InvalidContentTypeException
     */
    public function get(HttpRequest $request, $retryWithUrlEncodingDisabled = null)
    {
        $configuration = $this->getConfiguration();

        try {
            $response = $configuration->getHttpClient()->send($request);
        } catch (BadResponseException $badResponseException) {
            if (is_null($retryWithUrlEncodingDisabled) && $configuration->getRetryWithUrlEncodingDisabled()) {
                $retryWithUrlEncodingDisabled = true;
            }

            if ($retryWithUrlEncodingDisabled) {
                return $this->get($this->deEncodeRequestUrl($request), false);
            }

            $response = $badResponseException->getResponse();
        }

        if ($this->isBadResponse($response)) {
            throw new WebResourceException($response, $request);
        }

        if ($this->isInformationalResponse($response)) {
            throw new WebResourceException($response, $request);
        }

        if ($this->isRedirectResponse($response)) {
            // Shouldn't happen, HTTP client should have the redirect handler
            // enabled, redirects should be followed
            throw new WebResourceException($response, $request);
        }

        $contentType = $this->getContentTypeFromResponse($response);

        $hasMappedWebResourceClassName = $configuration->hasMappedWebResourceClassName(
            $contentType->getTypeSubtypeString()
        );

        if (!$hasMappedWebResourceClassName && !$configuration->getAllowUnknownResourceTypes()) {
            throw new InvalidContentTypeException($contentType, $response, $request);
        }

        return $this->create($response);
    }

    /**
     * @param HttpResponse $response
     *
     * @return WebResource
     */
    public function create(HttpResponse $response)
    {
        $configuration = $this->getConfiguration();

        $webResourceClassName = $configuration->getWebResourceClassName(
            $this->getContentTypeFromResponse($response)->getTypeSubtypeString()
        );

        /* @var $resource WebResource */
        $resource = new $webResourceClassName;
        $resource->setHttpResponse($response);

        return $resource;
    }

    /**
     * @param HttpResponse $response
     *
     * @return InternetMediaType
     */
    private function getContentTypeFromResponse(HttpResponse $response)
    {
        $mediaTypeParser = new InternetMediaTypeParser();
        $mediaTypeParser->setAttemptToRecoverFromInvalidInternalCharacter(true);
        $mediaTypeParser->setIgnoreInvalidAttributes(true);

        return $mediaTypeParser->parse($response->getHeader('content-type'));
    }

    /**
     * @param HttpRequest $request
     *
     * @return HttpRequest
     */
    private function deEncodeRequestUrl(HttpRequest $request)
    {
        $request->getQuery()->setEncodingType(false);

        return $request;
    }

    /**
     * @param HttpResponse $response
     *
     * @return bool
     */
    private function isInformationalResponse(HttpResponse $response)
    {
        return $response->getStatusCode() < 200;
    }

    /**
     * @param HttpResponse $response
     *
     * @return bool
     */
    private function isRedirectResponse(HttpResponse $response)
    {
        return $response->getStatusCode() >= 300 && $response->getStatusCode() < 400;
    }

    /**
     * @param HttpResponse $response
     *
     * @return bool
     */
    private function isBadResponse(HttpResponse $response)
    {
        return $response->getStatusCode() >= 400;
    }
}
