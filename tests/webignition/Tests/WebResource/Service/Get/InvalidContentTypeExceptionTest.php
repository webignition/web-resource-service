<?php

namespace webignition\Tests\WebResource\Service\Get;

use webignition\Tests\WebResource\Service\BaseTest;

class InvalidContentTypeExceptionTest extends BaseTest {
    
    public function testEnableAllowUnknownResourceTypesDoesNotExceptionForTextPlain() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            'example.com.txt.200.httpresponse'
        ))));
        
        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
        
        $webResourceService = $this->getDefaultWebResourceService();        
        $webResourceService->getConfiguration()->enableAllowUnknownResourceTypes();
        $webResourceService->get($request);
    }    
    
    public function testDisableAllowUnknownResourceTypesThrowsInvalidContentTypeExceptionForTextPlain() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            'example.com.txt.200.httpresponse'
        ))));
        
        //$this->setExpectedException('webignition\WebResource\Exception\InvalidContentTypeException');
        
        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
        
        $webResourceService = $this->getWebResourceServiceWithContentTypeMap();
        $webResourceService->getConfiguration()->disableAllowUnknownResourceTypes();
        
        try {
            $webResourceService->get($request);
            $this->fail('InvalidContentTypeException not thrown');
        } catch (\webignition\WebResource\Exception\InvalidContentTypeException $invalidContentTypeException) {
            $this->assertEquals('text/plain', (string)$invalidContentTypeException->getResponseContentType());
        }
    }
    
    
    public function testDisableAllowUnknownResourceTypesDoesNotThrowExceptionForKnownContentType() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            'example.com.html.200.httpresponse'
        ))));
        
        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
        
        $webResourceService = $this->getWebResourceServiceWithContentTypeMap();        
        $webResourceService->getConfiguration()->disableAllowUnknownResourceTypes();
        $webResourceService->get($request);
    }
    
}