<?php

namespace webignition\Tests\WebResource\Service\Get\RetryWithUrlEncodingDisabled;

use webignition\Tests\WebResource\Service\Get\GetTest;

abstract class RetryWithUrlEncodingDisabledTest extends GetTest {

    /**
     * @var \webignition\WebResource\Service\Service
     */
    protected $service;

    public function setUp() {
        parent::setUp();

        $this->service = $this->getDefaultWebResourceService();
        $this->service->getConfiguration()->enableRetryWithUrlEncodingDisabled();
    }

}