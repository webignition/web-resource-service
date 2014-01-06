<?php

namespace webignition\WebResource\Service;

use webignition\InternetMediaType\Parser\Parser as InternetMediaTypeParser;
use webignition\WebResource\Exception\Exception as WebResourceException;
use webignition\WebResource\Exception\InvalidContentTypeException;

class Service {
    
    const DEFAULT_WEB_RESOURCE_MODEL = 'webignition\WebResource\WebResource'; 
    
    
    /**
     * Maps content types to WebResource subclasses
     * 
     * @var array
     */
    private $contentTypeWebResourceMap = array();
    
    
    /**
     *
     * @var boolean
     */
    private $allowUnknownResourceTypes = true;

    
    /**
     *
     * @param \SimplyTestable\WorkerBundle\Services\HttpClientService $httpClientService
     * @param array $contentTypeWebResourceMap
     */
    public function __construct($contentTypeWebResourceMap = null) {    
        if (is_array($contentTypeWebResourceMap)) {
            $this->contentTypeWebResourceMap = $contentTypeWebResourceMap;        
        }
    }
    
    
    public function enableAllowUnknownResourceTypes() {
        $this->allowUnknownResourceTypes = true;
    }
    
    
    public function disableAllowUnknownResourceTypes() {
        $this->allowUnknownResourceTypes = false;
    }
    
    /**
     *
     * @param \Guzzle\Http\Message\Request $request
     * @return \webignition\WebResource\WebResource 
     */
    public function get(\Guzzle\Http\Message\Request $request) {
        
        try {
            $response = $request->send();
        } catch (\Guzzle\Http\Exception\ServerErrorResponseException $serverErrorResponseException) {
            $response = $serverErrorResponseException->getResponse();
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $clientErrorResponseException) {
            $response = $clientErrorResponseException->getResponse();
        }
        
        if ($response->isInformational()) {
            // Interesting to see what makes this happen
            throw new WebResourceException($response, $request);
        }
        
        if ($response->isRedirect()) {
            // Shouldn't happen, HTTP client should have the redirect handler
            // enabled, redirects should be followed            
            throw new WebResourceException($response, $request);
        }
        
        if ($response->isClientError() || $response->isServerError()) {
            throw new WebResourceException($response, $request); 
        }
        
        $mediaTypeParser = new InternetMediaTypeParser();
        $mediaTypeParser->setAttemptToRecoverFromInvalidInternalCharacter(true);
        $mediaTypeParser->setIgnoreInvalidAttributes(true);
        $contentType = $mediaTypeParser->parse($response->getContentType());               

        $webResourceClassName = $this->getWebResourceClassName($contentType->getTypeSubtypeString());        
        if ($webResourceClassName === self::DEFAULT_WEB_RESOURCE_MODEL && $this->allowUnknownResourceTypes === false) {
            throw new InvalidContentTypeException($contentType, $response, $request);
        }

        $resource = new $webResourceClassName;                
        $resource->setContent($response->getBody(true));                              
        $resource->setContentType((string)$contentType);        
        $resource->setUrl($response->getEffectiveUrl());          

        return $resource;
    }
    

    /**
     * Get the WebResource subclass name for a given content type
     * 
     * @param string $contentType
     * @return string
     */
    private function getWebResourceClassName($contentType) {
        return (isset($this->contentTypeWebResourceMap[$contentType])) ? $this->contentTypeWebResourceMap[$contentType] : self::DEFAULT_WEB_RESOURCE_MODEL;
    }
    
}