<?php

namespace webignition\Tests\WebResource\Service\Create;

class UnknownContentTypeTest extends CreateTest {

    protected function createService() {
        return $this->getDefaultWebResourceService();
    }

    protected function getHttpFixture() {
        return "HTTP/1.0 200 OK\nContent-Type:text/plain\n\nHello World!";
    }

    protected function getExpectedResourceType() {
        return 'webignition\WebResource\WebResource';
    }
}