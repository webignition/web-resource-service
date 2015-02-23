<?php

namespace webignition\Tests\WebResource\Service\Get\ContentType;

use webignition\Tests\WebResource\Service\Get\GetTest;
use webignition\WebResource\WebResource;

abstract class ContentTypeTest extends GetTest {

    /**
     * @var WebResource
     */
    protected $resource;

    public function setUp() {
        parent::setUp();

        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
        $this->resource = $this->getDefaultWebResourceService()->get($request);
    }
    
}