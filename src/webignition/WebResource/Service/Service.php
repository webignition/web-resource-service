<?php

namespace webignition\WebResource\Service;

use webignition\InternetMediaType\Parser\Parser as InternetMediaTypeParser;
use webignition\WebResource\Exception\Exception as WebResourceException;
use webignition\WebResource\Exception\InvalidContentTypeException;
use webignition\WebResource\Service\Configuration;

class Service {
    
    /**
     *
     * @var \webignition\WebResource\Service\Configuration 
     */
    private $configuration = null;
    
    
    /**
     * 
     * @param \webignition\WebResource\Service\Configuration $configuration
     * @return \webignition\WebResource\Service\Service
     */
    public function setConfiguration(\webignition\WebResource\Service\Configuration $configuration) {
        $this->configuration = $configuration;
        return $this;
    }
    
    
    /**
     * 
     * @return \webignition\WebResource\Service\Configuration
     */
    public function getConfiguration() {
        if (is_null($this->configuration)) {
            $this->configuration = new Configuration();
        }
        
        return $this->configuration;
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
            if ($this->getConfiguration()->getRetryWithUrlEncodingDisabled() && !$this->getConfiguration()->getHasRetriedWithUrlEncodingDisabled()) {
                $this->getConfiguration()->setHasRetriedWithUrlEncodingDisabled(true);
                return $this->get($this->deEncodeRequestUrl($request));
            }
            
            $response = $serverErrorResponseException->getResponse();
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $clientErrorResponseException) {
            if ($this->getConfiguration()->getRetryWithUrlEncodingDisabled() && !$this->getConfiguration()->getHasRetriedWithUrlEncodingDisabled()) {
                $this->getConfiguration()->setHasRetriedWithUrlEncodingDisabled(true);
                return $this->get($this->deEncodeRequestUrl($request));
            }
            
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
        
        if (!$this->getConfiguration()->hasMappedWebResourceClassName($contentType->getTypeSubtypeString()) && $this->getConfiguration()->getAllowUnknownResourceTypes() === false) {
            throw new InvalidContentTypeException($contentType, $response, $request);
        }
        
        return $this->create($response->getEffectiveUrl(), $response->getBody(true), $contentType->getTypeSubtypeString());
    }
    
    
    /**
     * 
     * @param string $url
     * @param string $content
     * @param string $contentType
     * @return \webignition\WebResource\WebResource
     */
    public function create($url, $content, $contentType) {
        $webResourceClassName = $this->getConfiguration()->getWebResourceClassName($contentType);
        
        $resource = new $webResourceClassName;                
        $resource->setContent($content);                              
        $resource->setContentType($contentType);        
        $resource->setUrl($url);          

        return $resource;
    }
    
    
    /**
     * 
     * @param \Guzzle\Http\Message\Request $request
     * @return \Guzzle\Http\Message\Request
     */
    private function deEncodeRequestUrl(\Guzzle\Http\Message\Request $request) {
        // Intentionally not a one-liner to make the process easier to understand
        $requestUrl = $request->getUrl(true);
        $requestQuery = $requestUrl->getQuery(true);
        $requestQuery->useUrlEncoding(false);

        $requestUrl->setQuery($requestQuery);
        $request->setUrl($requestUrl);

        return $request;
      
    }
    
}