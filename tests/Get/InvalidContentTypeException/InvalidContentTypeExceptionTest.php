<?php

namespace webignition\Tests\WebResource\Service\Get\InvalidContentTypeException;

use GuzzleHttp\Message\RequestInterface as HttpRequest;
use webignition\Tests\WebResource\Service\Get\GetTest;

abstract class InvalidContentTypeExceptionTest extends GetTest {

    /**
     * @var HttpRequest
     */
    protected $request;

    public function setUp() {
        parent::setUp();

        $this->request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
    }
    
}