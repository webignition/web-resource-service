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
     * @var bool
     */
    private $hasBadResponse = false;

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
     *
     * @throws InvalidContentTypeException
     * @throws Exception
     *
     * @return WebResource
     */
    public function get(HttpRequest $request)
    {
        $configuration = $this->getConfiguration();
        $this->hasBadResponse = false;

        try {
            $response = $configuration->getHttpClient()->send($request);
        } catch (BadResponseException $badResponseException) {
            $isRetryWithUrlEncodingDisabled = $configuration->getRetryWithUrlEncodingDisabled();
            $hasTriedWithUrlEncodingDisabled = $configuration->getHasRetriedWithUrlEncodingDisabled();

            if ($isRetryWithUrlEncodingDisabled && !$hasTriedWithUrlEncodingDisabled) {
                $configuration->setHasRetriedWithUrlEncodingDisabled(true);
                return $this->get($this->deEncodeRequestUrl($request));
            }

            $response = $badResponseException->getResponse();
            $this->hasBadResponse = true;
        }

        if ($configuration->getHasRetriedWithUrlEncodingDisabled()) {
            $configuration->setHasRetriedWithUrlEncodingDisabled(false);
        }

        if ($this->hasBadResponse) {
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
    private function create(HttpResponse $response)
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
}
