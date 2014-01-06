<?php

namespace webignition\Tests\WebResource\Service;

use webignition\Tests\WebResource\Service\BaseTest;

class InvalidContentTypeExceptionTest extends BaseTest {
    
    public function testEnableAllowUnknownResourceTypesDoesNotExceptionForTextPlain() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            'example.com.txt.200.httpresponse'
        ))));
        
        $request = $this->getHttpClient()->get('http://example.com/');
        
        $webResourceService = $this->getDefaultWebResourceService();        
        $webResourceService->enableAllowUnknownResourceTypes();
        $webResourceService->get($request);
    }    
    
    public function testDisableAllowUnknownResourceTypesThrowsInvalidContentTypeExceptionForTextPlain() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            'example.com.txt.200.httpresponse'
        ))));
        
        $this->setExpectedException('webignition\WebResource\Exception\InvalidContentTypeException');
        
        $request = $this->getHttpClient()->get('http://example.com/');
        
        $webResourceService = $this->getWebResourceServiceWithContentTypeMap();      
        $webResourceService->disableAllowUnknownResourceTypes();
        $webResourceService->get($request);
    }
    
    
    public function testDisableAllowUnknownResourceTypesDoesNotThrowExceptionForKnownContentType() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            'example.com.html.200.httpresponse'
        ))));
        
        $request = $this->getHttpClient()->get('http://example.com/');
        
        $webResourceService = $this->getWebResourceServiceWithContentTypeMap();        
        $webResourceService->disableAllowUnknownResourceTypes();
        $webResourceService->get($request);
    }
    
}