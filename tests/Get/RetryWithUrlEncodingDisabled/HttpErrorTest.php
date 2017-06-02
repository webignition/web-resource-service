<?php

namespace webignition\Tests\WebResource\Service\Get\RetryWithUrlEncodingDisabled;

use webignition\WebResource\WebResource;

abstract class HttpErrorTest extends RetryWithUrlEncodingDisabledTest {

    /**
     * @var WebResource
     */
    protected  $resource;


    public function setUp() {
        parent::setUp();

        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
        $this->resource = $this->service->get($request);
    }

    abstract protected function getErrorStatusCode();


    protected function getHttpFixtures() {
        return [
            $this->getHttpResponseFromMessage("HTTP/1.1 " . $this->getErrorStatusCode()),
            $this->getHttpResponseFromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/example.com.html.200.httpresponse'))
        ];
    }


    public function testResourceIsRetrieved() {
        $this->assertEquals('webignition\WebResource\WebResource', get_class($this->resource));
    }


    public function testRetrievedResourceMatchesExpectedResource() {
        $expectedResponse = $this->getHttpResponseFromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/example.com.html.200.httpresponse'));
        $this->assertEquals((string)$expectedResponse, (string)$this->resource->getHttpResponse());
    }

}