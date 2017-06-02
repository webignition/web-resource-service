<?php

namespace webignition\Tests\WebResource\Service\Get\ContentType;

class CharsetInResponseTest extends ContentTypeTest {

    protected function getHttpFixtures() {
        return [
            $this->getHttpResponseFromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/example.com.html.with-charset-in-content-type.200.httpresponse'))
        ];
    }
    
    public function testResourceContentTypeHasCharset() {
        $this->assertTrue($this->resource->getContentType()->hasParameter('charset'));
    }


    public function testResourceContentTypeCharsetIsCorrect() {
        $this->assertEquals('UTF-8', $this->resource->getContentType()->getParameter('charset')->getValue());
    }
    
}