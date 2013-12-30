<?php

namespace webignition\Tests\WebResource\Service;

use webignition\Tests\WebResource\Service\BaseTest;

class HttpClientErrorTest extends BaseTest {
    
    public function test404ResponseThrowsWebResourceException() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            '404.httpresponse'
        ))));
        
        $this->setExpectedException('webignition\WebResource\Exception\Exception');
        
        $request = $this->getHttpClient()->get('http://example.com/');
        $this->getDefaultWebResourceService()->get($request);
    } 
    
    
    public function test404WebResourceExceptionContains404Response() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            '404.httpresponse'
        ))));
        
        $request = $this->getHttpClient()->get('http://example.com/');
        
        /* @var $webResourceException \webignition\WebResource\Exception\Exception */
        $webResourceException = null;        
        
        try {
            $this->getDefaultWebResourceService()->get($request);
        } catch (\webignition\WebResource\Exception\Exception $webResourceException) {
        }
        
        $this->assertInstanceOf('\webignition\WebResource\Exception\Exception', $webResourceException);        
        $this->assertEquals(404, $webResourceException->getResponse()->getStatusCode());
        
        
    }      
    
}