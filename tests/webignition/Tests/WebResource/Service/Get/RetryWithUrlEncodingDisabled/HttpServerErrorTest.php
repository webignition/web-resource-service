<?php

namespace webignition\Tests\WebResource\Service\Get\RetryWithUrlEncodingDisabled;

class HttpServerErrorTest extends HttpErrorTest {

    protected function getErrorStatusCode() {
        return 500;
    }

}