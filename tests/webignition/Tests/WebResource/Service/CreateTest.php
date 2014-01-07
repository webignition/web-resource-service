<?php

namespace webignition\Tests\WebResource\Service;

use webignition\Tests\WebResource\Service\BaseTest;

class CreateTest extends BaseTest {
    
    public function testCreateForUnknownContentType() {
        $service = $this->getDefaultWebResourceService();
        $resource = $service->create('http://example.com/', 'Hello World!', 'text/plain');
        
        $this->assertEquals('webignition\WebResource\WebResource', get_class($resource));
    }
    
    
    public function testCreateForKnownContentType() {
        $service = $this->getWebResourceServiceWithContentTypeMap();
        $resource = $service->create('http://example.com/', '<!DOCTYPE html><html></html>', 'text/html');
        
        $this->assertEquals('webignition\WebResource\WebPage\WebPage', get_class($resource));
    }
    
}