<?php

namespace webignition\Tests\WebResource\Service\Get;

use webignition\Tests\WebResource\Service\BaseTest;

class WebPageTest extends BaseTest {
    
    public function testGetWebPageAsWebResource() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            'example.com.html.200.httpresponse'
        ))));
        
        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
        $resource = $this->getDefaultWebResourceService()->get($request);
        
        $this->assertEquals('webignition\WebResource\WebResource', get_class($resource));
    }
    
    
    public function testGetWebPageAsWebPageModel() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            'example.com.html.200.httpresponse'
        ))));
        
        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
        $resource = $this->getWebResourceServiceWithContentTypeMap()->get($request);
        
        $this->assertInstanceOf('webignition\WebResource\WebPage\WebPage', $resource);
    } 
    
    
    public function testGetWebPageWithContentTypeAttributeAsWebPage() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            'example.com.html.with-charset-in-content-type.200.httpresponse'
        ))));
        
        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
        $resource = $this->getWebResourceServiceWithContentTypeMap()->get($request);
        
        $this->assertInstanceOf('webignition\WebResource\WebPage\WebPage', $resource);
    }    
    
}