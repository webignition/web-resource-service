<?php

namespace webignition\Tests\WebResource\Service\Get\RetryWithUrlEncodingDisabled;

use webignition\WebResource\WebResource;

class RetriesAcrossSubsequentRequestsTest extends RetryWithUrlEncodingDisabledTest {

    /**
     * @var WebResource
     */
    private $resource1;

    /**
     * @var WebResource
     */
    private $resource2;

    public function setUp() {
        parent::setUp();

        $baseRequest = $this->getHttpClient()->createRequest('GET', '');

        $request1 = clone $baseRequest;
        $request1->setUrl('http://example.com/foo');

        $this->resource1 = $this->service->get($request1);

        $request2 = clone $baseRequest;
        $request2->setUrl('http://example.com/bar');

        $this->resource2 = $this->service->get($request2);
    }


    protected function getHttpFixtures() {
        return [
            $this->getHttpResponseFromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/400.httpresponse')),
            $this->getHttpResponseFromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/200.httpresponse')),
            $this->getHttpResponseFromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/400.httpresponse')),
            $this->getHttpResponseFromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/200.httpresponse')),
        ];
    }


    public function testRequest1RetrievesResource() {
        $this->assertEquals('webignition\WebResource\WebResource', get_class($this->resource1));
    }


    public function testRequest2RetrievesResource() {
        $this->assertEquals('webignition\WebResource\WebResource', get_class($this->resource2));
    }
}