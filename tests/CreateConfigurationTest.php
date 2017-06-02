<?php

namespace webignition\Tests\WebResource\Service;

use webignition\Tests\WebResource\Service\BaseTest;

class CreateConfigurationTest extends BaseTest {
    
    public function testCreateConfigurationReturnsSelf() {
        $service = $this->getDefaultWebResourceService();
        $this->assertEquals($service, $service->createConfiguration(array()));
    }    
    
    public function testSetAllowUnknownResourceTypesTrue() {
        $service = $this->getDefaultWebResourceService();
        $service->createConfiguration(array(
            'allow-uknown-resource-types' => true
        ));
        
        $this->assertTrue($service->getConfiguration()->getAllowUnknownResourceTypes());
    }
    
    public function testSetAllowUnknownResourceTypesFalse() {
        $service = $this->getDefaultWebResourceService();
        $service->createConfiguration(array(
            'allow-uknown-resource-types' => false
        ));
        
        $this->assertFalse($service->getConfiguration()->getAllowUnknownResourceTypes());
    }
    
    public function testSetRetryWithUrlEncodingDisabledTrue() {
        $service = $this->getDefaultWebResourceService();
        $service->createConfiguration(array(
            'retry-with-url-encoding-disabled' => true
        ));
        
        $this->assertTrue($service->getConfiguration()->getRetryWithUrlEncodingDisabled());        
    }    
    
    public function testSetRetryWithUrlEncodingDisabledFalse() {
        $service = $this->getDefaultWebResourceService();
        $service->createConfiguration(array(
            'retry-with-url-encoding-disabled' => false
        ));
        
        $this->assertFalse($service->getConfiguration()->getRetryWithUrlEncodingDisabled());        
    }
    
    public function testSetContentTypeWebResourceMap() {
        $contentTypeWebResourceMap = array(
            'foo' => 'bar'            
        );
        
        $service = $this->getDefaultWebResourceService();
        $service->createConfiguration(array(
            'content-type-web-resource-map' => $contentTypeWebResourceMap
        ));
        
        $this->assertEquals($contentTypeWebResourceMap, $service->getConfiguration()->getContentTypeWebResourceMap()); 
    }
    
    
}