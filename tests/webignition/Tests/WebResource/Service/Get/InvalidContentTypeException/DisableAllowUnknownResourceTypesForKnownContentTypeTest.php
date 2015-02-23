<?php

namespace webignition\Tests\WebResource\Service\Get\InvalidContentTypeException;

class DisableAllowUnknownResourceTypesForKnownContentTypeTest extends InvalidContentTypeExceptionTest {

    protected function getHttpFixtures() {
        return [
            $this->getHttpResponseFromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/example.com.html.200.httpresponse'))
        ];
    }

    public function testDisableAllowUnknownResourceTypesDoesNotThrowExceptionForKnownContentType() {
        $webResourceService = $this->getWebResourceServiceWithContentTypeMap();
        $webResourceService->getConfiguration()->disableAllowUnknownResourceTypes();
        $webResourceService->get($this->request);
    }
    
}