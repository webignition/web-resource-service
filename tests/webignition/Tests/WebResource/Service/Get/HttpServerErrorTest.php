<?php

namespace webignition\Tests\WebResource\Service\Get;

use webignition\Tests\WebResource\Service\BaseTest;

class HttpServerErrorTest extends BaseTest {
    
    public function test500ResponseThrowsWebResourceException() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            '500.httpresponse'
        ))));
        
        $this->setExpectedException('webignition\WebResource\Exception\Exception');
        
        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
        $this->getDefaultWebResourceService()->get($request);
    } 
    
    
    public function test404WebResourceExceptionContains404Response() {
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            '500.httpresponse'
        ))));
        
        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
        
        /* @var $webResourceException \webignition\WebResource\Exception\Exception */
        $webResourceException = null;        
        
        try {
            $this->getDefaultWebResourceService()->get($request);
        } catch (\webignition\WebResource\Exception\Exception $webResourceException) {
        }
        
        $this->assertInstanceOf('\webignition\WebResource\Exception\Exception', $webResourceException);        
        $this->assertEquals(500, $webResourceException->getResponse()->getStatusCode());
        
        
    }      
    
}