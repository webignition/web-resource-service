<?php

namespace webignition\WebResource\Service;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use webignition\InternetMediaType\Parser\Parser as InternetMediaTypeParser;
use webignition\WebResource\Exception\Exception as WebResourceException;
use webignition\WebResource\Exception\InvalidContentTypeException;
use GuzzleHttp\Message\ResponseInterface as HttpResponse;
use GuzzleHttp\Message\RequestInterface as HttpRequest;

class Service {
    
    /**
     *
     * @var Configuration
     */
    private $configuration = null;
    
    
    /**
     * 
     * @param array $configurationValues
     * @return Service
     */
    public function createConfiguration($configurationValues) {
        $configuration = new Configuration();        
        
        if (isset($configurationValues['allow-uknown-resource-types'])) {
            if ($configurationValues['allow-uknown-resource-types']) {
                $configuration->enableAllowUnknownResourceTypes();
            } else {
                $configuration->disableAllowUnknownResourceTypes();
            }
        }
        
        if (isset($configurationValues['retry-with-url-encoding-disabled'])) {
            if ($configurationValues['retry-with-url-encoding-disabled']) {
                $configuration->enableRetryWithUrlEncodingDisabled();
            } else {
                $configuration->disableRetryWithUrlEncodingDisabled();
            }
        }
        
        if (isset($configurationValues['content-type-web-resource-map'])) {
            $configuration->setContentTypeWebResourceMap($configurationValues['content-type-web-resource-map']);
        }
        
        $this->setConfiguration($configuration);
        return $this;       
    }
    
    
    /**
     * 
     * @param Configuration $configuration
     * @return Service
     */
    public function setConfiguration(\webignition\WebResource\Service\Configuration $configuration) {
        $this->configuration = $configuration;
        return $this;
    }
    
    
    /**
     * 
     * @return Configuration
     */
    public function getConfiguration() {
        if (is_null($this->configuration)) {
            $this->configuration = new Configuration();
        }
        
        return $this->configuration;
    }


    /**
     * @param HttpRequest $request
     * @return \webignition\WebResource\WebResource
     * @throws \webignition\WebResource\Exception\InvalidContentTypeException
     * @throws \webignition\WebResource\Exception\Exception
     */
    public function get(HttpRequest $request) {
        try {
            $response = $this->getConfiguration()->getHttpClient()->send($request);
        } catch (ServerException $serverErrorResponseException) {
            if ($this->getConfiguration()->getRetryWithUrlEncodingDisabled() && !$this->getConfiguration()->getHasRetriedWithUrlEncodingDisabled()) {
                $this->getConfiguration()->setHasRetriedWithUrlEncodingDisabled(true);
                return $this->get($this->deEncodeRequestUrl($request));
            }
            
            $response = $serverErrorResponseException->getResponse();
        } catch (ClientException $clientErrorResponseException) {
            if ($this->getConfiguration()->getRetryWithUrlEncodingDisabled() && !$this->getConfiguration()->getHasRetriedWithUrlEncodingDisabled()) {
                $this->getConfiguration()->setHasRetriedWithUrlEncodingDisabled(true);
                return $this->get($this->deEncodeRequestUrl($request));
            }
            
            $response = $clientErrorResponseException->getResponse();
        }
        
        if ($this->getConfiguration()->getHasRetriedWithUrlEncodingDisabled()) {
            $this->getConfiguration()->setHasRetriedWithUrlEncodingDisabled(false);
        }


        // Informational?
        if ($this->isInformationalResponse($response)) {
            // Interesting to see what makes this happen
            throw new WebResourceException($response, $request);
        }

        // Redirect?
        if ($this->isRedirectResponse($response)) {
            // Shouldn't happen, HTTP client should have the redirect handler
            // enabled, redirects should be followed            
            throw new WebResourceException($response, $request);
        }
        
        if ($this->isErrorResponse($response)) {
            throw new WebResourceException($response, $request); 
        }
        
        $contentType = $this->getContentTypeFromResponse($response);
        
        if (!$this->getConfiguration()->hasMappedWebResourceClassName($contentType->getTypeSubtypeString()) && $this->getConfiguration()->getAllowUnknownResourceTypes() === false) {
            throw new InvalidContentTypeException($contentType, $response, $request);
        }
        
        return $this->create($response);
    }
    
    
    /**
     * 
     * @param HttpResponse $response
     * @return \webignition\WebResource\WebResource
     */
    public function create(HttpResponse $response) {
        $webResourceClassName = $this->getConfiguration()->getWebResourceClassName($this->getContentTypeFromResponse($response)->getTypeSubtypeString());
        
        $resource = new $webResourceClassName;                
        $resource->setHttpResponse($response);
        
        return $resource;
    }
    
    
    
    /**
     * 
     * @param HttpResponse $response
     * @return \webignition\InternetMediaType\InternetMediaType
     */
    private function getContentTypeFromResponse(HttpResponse $response) {
        $mediaTypeParser = new InternetMediaTypeParser();
        $mediaTypeParser->setAttemptToRecoverFromInvalidInternalCharacter(true);
        $mediaTypeParser->setIgnoreInvalidAttributes(true);
        return $mediaTypeParser->parse($response->getHeader('content-type'));
    }
    
    
    /**
     * 
     * @param HttpRequest $request
     * @return HttpRequest
     */
    private function deEncodeRequestUrl(HttpRequest $request) {
        $request->getQuery()->setEncodingType(false);
        return $request;
    }


    /**
     * @param HttpResponse $response
     * @return bool
     */
    private function isInformationalResponse(HttpResponse $response) {
        return $response->getStatusCode() < 200;
    }


    /**
     * @param HttpResponse $response
     * @return bool
     */
    private function isRedirectResponse(HttpResponse $response) {
        return $response->getStatusCode() >= 300 && $response->getStatusCode() < 400;
    }


    /**
     * @param HttpResponse $response
     * @return bool
     */
    private function isClientErrorResponse(HttpResponse $response) {
        return $response->getStatusCode() >= 400 && $response->getStatusCode() < 500;
    }


    /**
     * @param HttpResponse $response
     * @return bool
     */
    private function isServerErrorResponse(HttpResponse $response) {
        return $response->getStatusCode() >= 500 && $response->getStatusCode() < 600;
    }


    /**
     * @param HttpResponse $response
     * @return bool
     */
    private function isErrorResponse(HttpResponse $response) {
        return $this->isClientErrorResponse($response) || $this->isServerErrorResponse($response);
    }

    
}