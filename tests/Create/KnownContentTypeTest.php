<?php

namespace webignition\Tests\WebResource\Service\Create;

class KnownContentTypeTest extends CreateTest {

    protected function createService() {
        return $this->getWebResourceServiceWithContentTypeMap();
    }

    protected function getHttpFixture() {
        return "HTTP/1.0 200 OK\nContent-Type:text/html\n\n<!DOCTYPE html><html></html>";
    }

    protected function getExpectedResourceType() {
        return 'webignition\WebResource\WebPage\WebPage';
    }
}