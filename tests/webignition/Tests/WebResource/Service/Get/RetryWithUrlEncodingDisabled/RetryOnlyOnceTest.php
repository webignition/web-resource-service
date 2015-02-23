<?php

namespace webignition\Tests\WebResource\Service\Get\RetryWithUrlEncodingDisabled;

use webignition\WebResource\Exception\Exception as WebResourceException;

class RetryOnlyOnceTest extends RetryWithUrlEncodingDisabledTest {

    /**
     * @var WebResourceException
     */
    private $webResourceException = null;

    public function setUp() {
        parent::setUp();

        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');


        try {
            $this->service->get($request);
        } catch (\webignition\WebResource\Exception\Exception $webResourceException) {
            $this->webResourceException = $webResourceException;
        }
    }

    protected function getHttpFixtures() {
        return [
            $this->getHttpResponseFromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/500.httpresponse')),
            $this->getHttpResponseFromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/500.httpresponse')),
        ];
    }

    public function testExceptionIsRaised() {
        $this->assertInstanceOf('\webignition\WebResource\Exception\Exception', $this->webResourceException);
    }


    public function testExceptionStatusCodeMatchesLastResponseStatusCode() {
        $this->assertEquals(500, $this->webResourceException->getResponse()->getStatusCode());
    }
}