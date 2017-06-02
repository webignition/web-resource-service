<?php

namespace webignition\Tests\WebResource\Service\Get\InvalidContentTypeException;

class EnableAllowUnknownResourceTypesTest extends InvalidContentTypeExceptionTest {

    protected function getHttpFixtures() {
        return [
            $this->getHttpResponseFromMessage(file_get_contents($this->getCommonFixturesDataPath() . '/example.com.txt.200.httpresponse'))
        ];
    }
    
    public function testEnableAllowUnknownResourceTypesDoesNotExceptionForTextPlain() {
        $webResourceService = $this->getDefaultWebResourceService();
        $webResourceService->getConfiguration()->enableAllowUnknownResourceTypes();
        $webResourceService->get($this->request);
    }
    
}