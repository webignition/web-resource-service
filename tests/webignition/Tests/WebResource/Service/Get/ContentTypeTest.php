<?php

namespace webignition\Tests\WebResource\Service\Get;

use webignition\Tests\WebResource\Service\BaseTest;

class ContentTypeTest extends BaseTest {
    
    public function testTypeSubTypeOnlyInResponseHasNoCharset() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            'example.com.html.200.httpresponse'
        ))));
        
        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');

        $resource = $this->getDefaultWebResourceService()->get($request);
        
        $this->assertFalse($resource->getContentType()->hasParameter('charset'));
    }  
    
    public function testCharsetInResponseHasCharset() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            'example.com.html.with-charset-in-content-type.200.httpresponse'
        ))));
        
        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
        $resource = $this->getDefaultWebResourceService()->get($request);
        
        $this->assertTrue($resource->getContentType()->hasParameter('charset'));
    }  
    
    
    public function testCharsetInResponseIsCorrect() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            'example.com.html.with-charset-in-content-type.200.httpresponse'
        ))));
        
        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
        $resource = $this->getDefaultWebResourceService()->get($request);
        
        $this->assertEquals('UTF-8', $resource->getContentType()->getParameter('charset')->getValue());
    }    
        
    
}