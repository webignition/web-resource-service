<?php

namespace webignition\Tests\WebResource\Service\Get\InvalidContentTypeException;

use webignition\WebResource\Exception\InvalidContentTypeException;

class DisableAllowUnknownResourceTypesForUnknownContentTypeTest extends InvalidContentTypeExceptionTest {

    protected function getHttpFixtures() {
        return [
            $this->getHttpResponseFromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/example.com.txt.200.httpresponse'))
        ];
    }


    public function testDisableAllowUnknownResourceTypesThrowsExceptionForTextPlain() {
        $webResourceService = $this->getWebResourceServiceWithContentTypeMap();
        $webResourceService->getConfiguration()->disableAllowUnknownResourceTypes();

        try {
            $webResourceService->get($this->request);
            $this->fail('InvalidContentTypeException not thrown');
        } catch (InvalidContentTypeException $invalidContentTypeException) {
            $this->assertEquals('text/plain', (string)$invalidContentTypeException->getResponseContentType());
        }
    }
    
}