<?php

namespace webignition\WebResource\Service;

use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webignition\InternetMediaType\InternetMediaType;
use webignition\InternetMediaType\Parser\Parser as InternetMediaTypeParser;
use webignition\WebResource\Exception\Exception as WebResourceException;
use webignition\WebResource\Exception\Exception;
use webignition\WebResource\Exception\InvalidContentTypeException;
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
     * @param RequestInterface $request
     * @param bool|null $retryWithUrlEncodingDisabled
     *
     * @return WebResource
     *
     * @throws Exception
     * @throws InvalidContentTypeException
     */
    public function get(RequestInterface $request, $retryWithUrlEncodingDisabled = null)
    {
        $configuration = $this->getConfiguration();

        if (!$configuration->getAllowUnknownResourceTypes()) {
            $headRequest = clone $request;
            $headRequest->withMethod('HEAD');

            $this->preVerifyContentType($headRequest);
        }

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
        $contentTypeSubtypeString = $contentType->getTypeSubtypeString();
        $hasMappedWebResourceClassName = $configuration->hasMappedWebResourceClassName($contentTypeSubtypeString);

        if (!$hasMappedWebResourceClassName && !$configuration->getAllowUnknownResourceTypes()) {
            throw new InvalidContentTypeException($contentType, $response, $request);
        }

        $webResourceClassName = $configuration->getWebResourceClassName($contentTypeSubtypeString);

        return new $webResourceClassName($response, $request->getUri());
    }

    /**
     * @param RequestInterface $request
     * @param bool|null $retryWithUrlEncodingDisabled
     *
     * @return bool
     * @throws Exception
     * @throws InvalidContentTypeException
     */
    private function preVerifyContentType(RequestInterface $request, $retryWithUrlEncodingDisabled = null)
    {
        $configuration = $this->getConfiguration();

        try {
            $response = $configuration->getHttpClient()->send($request);
        } catch (BadResponseException $badResponseException) {
            if (is_null($retryWithUrlEncodingDisabled) && $configuration->getRetryWithUrlEncodingDisabled()) {
                $retryWithUrlEncodingDisabled = true;
            }

            if ($retryWithUrlEncodingDisabled) {
                return $this->preVerifyContentType($this->deEncodeRequestUrl($request), false);
            }

            $response = $badResponseException->getResponse();
        }

        if (!$this->isSuccessResponse($response)) {
            return null;
        }

        $contentType = $this->getContentTypeFromResponse($response);

        $hasMappedWebResourceClassName = $configuration->hasMappedWebResourceClassName(
            $contentType->getTypeSubtypeString()
        );

        if (!$hasMappedWebResourceClassName && !$configuration->getAllowUnknownResourceTypes()) {
            throw new InvalidContentTypeException($contentType, $response, $request);
        }

        return true;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return InternetMediaType
     */
    private function getContentTypeFromResponse(ResponseInterface $response)
    {
        $mediaTypeParser = new InternetMediaTypeParser();
        $mediaTypeParser->setAttemptToRecoverFromInvalidInternalCharacter(true);
        $mediaTypeParser->setIgnoreInvalidAttributes(true);

        $contentTypeHeader = $response->getHeader('content-type');
        $contentTypeString = empty($contentTypeHeader)
            ? ''
            : $contentTypeHeader[0];

        return $mediaTypeParser->parse($contentTypeString);
    }

    /**
     * @param RequestInterface $request
     *
     * @return RequestInterface
     */
    private function deEncodeRequestUrl(RequestInterface $request)
    {
        $query = $request->getUri()->getQuery();
        $decodedQuery = rawurldecode($query);

        $request->getUri()->withQuery($decodedQuery);

        return $request;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function isInformationalResponse(ResponseInterface $response)
    {
        return $response->getStatusCode() < 200;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function isRedirectResponse(ResponseInterface $response)
    {
        return $response->getStatusCode() >= 300 && $response->getStatusCode() < 400;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function isBadResponse(ResponseInterface $response)
    {
        return $response->getStatusCode() >= 400;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function isSuccessResponse(ResponseInterface $response)
    {
        return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
    }
}
