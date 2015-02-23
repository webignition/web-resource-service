<?php

namespace webignition\Tests\WebResource\Service\Get\WebPage;

use webignition\Tests\WebResource\Service\Get\GetTest;
use webignition\WebResource\WebResource;

class AsWebResourceTest extends WebPageTest {

    /**
     * @var WebResource
     */
    private $resource;

    public function setUp() {
        parent::setUp();

        $request = $this->getHttpClient()->createRequest('GET', 'http://example.com/');
        $this->resource = $this->getDefaultWebResourceService()->get($request);
    }

    protected function getHttpFixtures() {
        return [
            $this->getHttpResponseFromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/example.com.html.200.httpresponse'))
        ];
    }

    public function testResourceIsOfCorrectType() {
        $this->assertEquals('webignition\WebResource\WebResource', get_class($this->resource));
    }
    
}