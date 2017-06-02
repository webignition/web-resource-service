<?php

namespace webignition\Tests\WebResource\Service\Get\ContentType;

class SubtypeTypeWithNoCharsetInResponseTest extends ContentTypeTest {

    protected function getHttpFixtures() {
        return [
            $this->getHttpResponseFromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/example.com.html.200.httpresponse'))
        ];
    }
    
    public function testResourceContentTypeHasNoCharset() {
        $this->assertFalse($this->resource->getContentType()->hasParameter('charset'));
    }
    
}