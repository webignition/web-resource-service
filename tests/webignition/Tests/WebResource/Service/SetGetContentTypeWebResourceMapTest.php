<?php

namespace webignition\Tests\WebResource\Service;

use webignition\Tests\WebResource\Service\BaseTest;

class SetGetContentTypeWebResourceMapTest extends BaseTest {
    
    public function testDefaultIsEmptyArray() {
        $this->assertEquals(array(), $this->getDefaultWebResourceService()->getContentTypeWebResourceMap());
    }
    
    public function testGetReturnsThatSet() {
        $map = array(
            'foo' => 'FooModel',
            'bar' => 'BarModel'
        );
        
        $service = new \webignition\WebResource\Service\Service($map);
        $this->assertEquals($map, $service->getContentTypeWebResourceMap());
    }   
    
}