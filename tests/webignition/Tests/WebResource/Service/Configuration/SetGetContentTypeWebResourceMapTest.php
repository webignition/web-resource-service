<?php

namespace webignition\Tests\WebResource\Service\Configuration;

use webignition\Tests\WebResource\Service\BaseTest;

class SetGetContentTypeWebResourceMapTest extends BaseTest {
    
    public function testDefaultIsEmptyArray() {
        $this->assertEquals(array(), $this->getDefaultWebResourceService()->getConfiguration()->getContentTypeWebResourceMap());
    }
    
    public function testGetReturnsThatSet() {
        $map = array(
            'foo' => 'FooModel',
            'bar' => 'BarModel'
        );
        
        $service = new \webignition\WebResource\Service\Service();
        $service->getConfiguration()->setContentTypeWebResourceMap($map);
        $this->assertEquals($map, $service->getConfiguration()->getContentTypeWebResourceMap());
    }   
    
}