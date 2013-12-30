<?php

namespace webignition\Tests\WebResource\Service;

use webignition\Tests\WebResource\Service\BaseTest;

class ServiceTest extends BaseTest {
    
    public function testGetWebPageAsWebResource() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            'example.com.html.200.httpresponse'
        ))));
        
        $request = $this->getHttpClient()->get('http://example.com/');
        $resource = $this->getDefaultWebResourceService()->get($request);
        
        $this->assertEquals('webignition\WebResource\WebResource', get_class($resource));
    }
    
    
    public function testGetWebPageAsWebPageModel() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            'example.com.html.200.httpresponse'
        ))));
        
        $request = $this->getHttpClient()->get('http://example.com/');
        $resource = $this->getWebResourceServiceWithContentTypeMap()->get($request);
        
        $this->assertInstanceOf('webignition\WebResource\WebPage\WebPage', $resource);
    }    
    
}