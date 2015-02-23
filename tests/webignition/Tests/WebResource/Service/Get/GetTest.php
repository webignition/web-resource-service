<?php

namespace webignition\Tests\WebResource\Service\Get;

use webignition\Tests\WebResource\Service\BaseTest;

abstract class GetTest extends BaseTest {

    public function setUp() {
        $this->setHttpFixtures($this->getHttpFixtures());
    }

    abstract protected function getHttpFixtures();
}