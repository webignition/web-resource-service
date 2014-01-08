<?php

namespace webignition\WebResource\Service;

class Configuration {
    
    
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
     * @var boolean
     */
    private $retryWithUrlEncodingDisabled = true;
    

    /**
     * 
     * @param array $contentTypeWebResourceMap
     * @return \webignition\WebResource\Service\Configuration
     */
    public function setContentTypeWebResourceMap($contentTypeWebResourceMap) {
        $this->contentTypeWebResourceMap = $contentTypeWebResourceMap;
        return $this;
    }
    
    
    /**
     * 
     * @return array
     */
    public function getContentTypeWebResourceMap() {
        return $this->contentTypeWebResourceMap;
    }
    
    
    public function enableAllowUnknownResourceTypes() {
        $this->allowUnknownResourceTypes = true;
    }
    
    
    public function disableAllowUnknownResourceTypes() {
        $this->allowUnknownResourceTypes = false;
    }
    
    
    /**
     * 
     * @return boolean
     */
    public function getAllowUnknownResourceTypes() {
        return $this->allowUnknownResourceTypes;
    }
    
    
    public function enableRetryWithUrlEncodingDisabled() {
        $this->retryWithUrlEncodingDisabled = true;
    }
    
    public function disableRetryWithUrlEncodingDisabled() {
        $this->retryWithUrlEncodingDisabled = false;
    } 
    
    
    /**
     * 
     * @return boolean
     */
    public function getRetryWithUrlEncodingDisabled() {
        return $this->retryWithUrlEncodingDisabled;
    }
    
    
    /**
     * 
     * @param string $contentType
     * @return boolean
     */
    public function hasMappedWebResourceClassName($contentType) {        
        return isset($this->contentTypeWebResourceMap[$contentType]);
    }    
    
    
    /**
     * Get the WebResource subclass name for a given content type
     * 
     * @param string $contentType
     * @return string
     */
    public function getWebResourceClassName($contentType) {        
        return ($this->hasMappedWebResourceClassName($contentType)) ? $this->contentTypeWebResourceMap[(string)$contentType] : self::DEFAULT_WEB_RESOURCE_MODEL;
    }    
    
    
}