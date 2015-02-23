<?php

namespace webignition\Tests\WebResource\Service\Get\HttpError;

class ClientErrorTest extends HttpErrorTest {

    protected function getExpectedStatusCode() {
        return 404;
    }
}