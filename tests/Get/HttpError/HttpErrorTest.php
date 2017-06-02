<?php

namespace webignition\Tests\WebResource\Service\Get\HttpError;

use webignition\Tests\WebResource\Service\Get\GetTest;

abstract class HttpErrorTest extends GetTest {

    abstract protected function getExpectedStatusCode();

    protected function getHttpFixtures() {
        return [
            $this->getHttpResponseFromMessage("HTTP/1.1 " . $this->getExpectedStatusCode())
        ];
    }
    
    public function testThrowsWebResourceException() {
        $this->setExpectedException('\webignition\WebResource\Exception\Exception');
        
        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
        $this->getDefaultWebResourceService()->get($request);
    } 
    
    
    public function testWebResourceExceptionContainsCorrectResponse() {
        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');

        /* @var $webResourceException \webignition\WebResource\Exception\Exception */
        $webResourceException = null;

        try {
            $this->getDefaultWebResourceService()->get($request);
        } catch (\webignition\WebResource\Exception\Exception $webResourceException) {
        }

        $this->assertInstanceOf('\webignition\WebResource\Exception\Exception', $webResourceException);
        $this->assertEquals($this->getExpectedStatusCode(), $webResourceException->getResponse()->getStatusCode());


    }
}