<?php

namespace webignition\Tests\WebResource\Service;

use webignition\Tests\WebResource\Service\BaseTest;

class RetryWithUrlEncodingDisabledTest extends BaseTest {
    
    /**
     * Test that retrying without url encoding occurs for http server errors
     */
    public function testWithHttpServerError() {        
        $successResponseName = 'example.com.html.200.httpresponse';
        
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            '500.httpresponse',
            $successResponseName
        ))));        
        
        $request = $this->getHttpClient()->get('http://example.com/');       
        $service = $this->getDefaultWebResourceService();
        $service->getConfiguration()->enableRetryWithUrlEncodingDisabled();
        $resource = $service->get($request);
        
        $expectedResponse = \Guzzle\Http\Message\Response::fromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/' . $successResponseName));        
        
        $this->assertEquals('webignition\WebResource\WebResource', get_class($resource));        
        $this->assertEquals($expectedResponse->getBody(), $resource->getContent());
    }
   

    /**
     * Test that retrying without url encoding occurs for http client errors
     */    
    public function testWithHttpClientError() {        
        $successResponseName = 'example.com.html.200.httpresponse';
        
        $this->setHttpFixtures($this->buildHttpFixtureSet($this->getHttpFixtures($this->getCommonFixturesDataPath(), array(
            '500.httpresponse',
            $successResponseName
        ))));        
        
        $request = $this->getHttpClient()->get('http://example.com/');
        $service = $this->getDefaultWebResourceService();
        $service->getConfiguration()->enableRetryWithUrlEncodingDisabled();
        $resource = $service->get($request);
        
        $expectedResponse = \Guzzle\Http\Message\Response::fromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/' . $successResponseName));        
        
        $this->assertEquals('webignition\WebResource\WebResource', get_class($resource));        
        $this->assertEquals($expectedResponse->getBody(), $resource->getContent());
    } 
    
    
    /**
     * Test that retrying without url encoding occurs just once
     */
    public function testRetriesOnlyOnce() {  
        $this->setHttpFixtures($this->buildHttpFixtureSet(array(
            file_get_contents($this->getCommonFixturesDataPath() . '/500.httpresponse'),
            file_get_contents($this->getCommonFixturesDataPath() . '/500.httpresponse')
        )));
        
        $request = $this->getHttpClient()->get('http://example.com/');
        
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