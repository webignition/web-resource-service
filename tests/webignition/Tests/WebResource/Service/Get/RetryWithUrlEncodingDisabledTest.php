<?php

namespace webignition\Tests\WebResource\Service\Get;

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
        
        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
        $service = $this->getDefaultWebResourceService();
        $service->getConfiguration()->enableRetryWithUrlEncodingDisabled();
        $resource = $service->get($request);
        
        $expectedResponse = $this->getHttpResponseFromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/' . $successResponseName));
        
        $this->assertEquals('webignition\WebResource\WebResource', get_class($resource));        
        $this->assertEquals((string)$expectedResponse, (string)$resource->getHttpResponse());
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

        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
        $service = $this->getDefaultWebResourceService();
        $service->getConfiguration()->enableRetryWithUrlEncodingDisabled();
        $resource = $service->get($request);

        $expectedResponse = $this->getHttpResponseFromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/' . $successResponseName));

        $this->assertEquals('webignition\WebResource\WebResource', get_class($resource));
        $this->assertEquals((string)$expectedResponse, (string)$resource->getHttpResponse());
    }


    /**
     * Test that retrying without url encoding occurs just once
     */
    public function testRetriesOnlyOnce() {
        $this->setHttpFixtures($this->buildHttpFixtureSet(array(
            file_get_contents($this->getCommonFixturesDataPath() . '/500.httpresponse'),
            file_get_contents($this->getCommonFixturesDataPath() . '/500.httpresponse')
        )));

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


    /**
     * Test that retrying is applied to subsequent requests when re-using the
     * web resource service
     */
    public function testRetriesAcrossSubsequentRequests() {
        $this->setHttpFixtures($this->buildHttpFixtureSet(array(
            file_get_contents($this->getCommonFixturesDataPath() . '/400.httpresponse'),
            file_get_contents($this->getCommonFixturesDataPath() . '/200.httpresponse'),
            file_get_contents($this->getCommonFixturesDataPath() . '/400.httpresponse'),
            file_get_contents($this->getCommonFixturesDataPath() . '/200.httpresponse')
        )));

        $service = $this->getDefaultWebResourceService();
        $service->getConfiguration()->enableRetryWithUrlEncodingDisabled();

        $baseRequest = $this->getHttpClient()->createRequest('GET', '');

        $request1 = clone $baseRequest;
        $request1->setUrl('http://example.com/foo');

        $this->assertEquals('webignition\WebResource\WebResource', get_class($service->get($request1)));

        $request2 = clone $baseRequest;
        $request2->setUrl('http://example.com/bar');

        $this->assertEquals('webignition\WebResource\WebResource', get_class($service->get($request2)));
    }
}