<?php

namespace webignition\Tests\WebResource\Service\Get\RetryWithUrlEncodingDisabled;

class HttpClientErrorTest extends HttpErrorTest {

    protected function getErrorStatusCode() {
        return 404;
    }

}