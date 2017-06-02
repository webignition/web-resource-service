<?php

namespace webignition\Tests\WebResource\Service\Create;

use webignition\Tests\WebResource\Service\BaseTest;
use webignition\WebResource\Service\Service as WebResourceService;
use webignition\WebResource\WebResource;

abstract class CreateTest extends BaseTest {

    /**
     * @var WebResource
     */
    private $resource;

    public function setUp() {
        parent::setUp();

        $service = $this->createService();
        $response = $this->getHttpResponseFromMessage($this->getHttpFixture());
        $response->setEffectiveUrl('http://example.com/');

        $this->resource = $service->create($response);
    }

    /**
     * @return WebResourceService
     */
    abstract protected function createService();

    abstract protected function getHttpFixture();

    /**
     * @return string
     */
    abstract protected function getExpectedResourceType();


    public function testResourceIsOfExpectedType() {
        $this->assertEquals($this->getExpectedResourceType(), get_class($this->resource));
    }
    
//    public function testCreateForUnknownContentType() {
//        $service = $this->getDefaultWebResourceService();
//
//        $response = $this->getHttpResponseFromMessage("HTTP/1.0 200 OK\nContent-Type:text/plain\n\nHello World!");
//        $response->setEffectiveUrl('http://example.com/');
//
//        $resource = $service->create($response);
//
//        $this->assertEquals('webignition\WebResource\WebResource', get_class($resource));
//    }
//
//
//    public function testCreateForKnownContentType() {
//        $service = $this->getWebResourceServiceWithContentTypeMap();
//
//        $response = $this->getHttpResponseFromMessage("HTTP/1.0 200 OK\nContent-Type:text/html\n\n<!DOCTYPE html><html></html>");
//        $response->setEffectiveUrl('http://example.com/');
//
//        $resource = $service->create($response);
//
//        $this->assertEquals('webignition\WebResource\WebPage\WebPage', get_class($resource));
//    }
    
}