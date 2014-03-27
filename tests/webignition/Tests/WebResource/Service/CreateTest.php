<?php

namespace webignition\Tests\WebResource\Service;

use webignition\Tests\WebResource\Service\BaseTest;

class CreateTest extends BaseTest {
    
    public function testCreateForUnknownContentType() {
        $service = $this->getDefaultWebResourceService();
        
        $response = \Guzzle\Http\Message\Response::fromMessage("HTTP/1.0 200 OK\nContent-Type:text/plain\n\nHello World!");
        $response->setEffectiveUrl('http://example.com/');
        
        $resource = $service->create($response);
        
        $this->assertEquals('webignition\WebResource\WebResource', get_class($resource));
    }
    
    
    public function testCreateForKnownContentType() {
        $service = $this->getWebResourceServiceWithContentTypeMap();
        
        $response = \Guzzle\Http\Message\Response::fromMessage("HTTP/1.0 200 OK\nContent-Type:text/html\n\n<!DOCTYPE html><html></html>");
        $response->setEffectiveUrl('http://example.com/');
        
        $resource = $service->create($response);        
        
        $this->assertEquals('webignition\WebResource\WebPage\WebPage', get_class($resource));
    }
    
}