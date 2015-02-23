<?php

namespace webignition\Tests\WebResource\Service\Get\HttpError;

class ServerErrorTest extends HttpErrorTest {

    protected function getExpectedStatusCode() {
        return 500;
    }
}