<?php

namespace webignition\Tests\WebResource\Service;

use GuzzleHttp\Message\ResponseInterface as HttpResponse;
use GuzzleHttp\Message\RequestInterface as HttpRequest;
use Mockery\MockInterface;
use webignition\WebResource\Exception\Exception as WebResourceException;

abstract class AbstractExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return MockInterface|HttpResponse
     */
    protected function createResponse($reasonPhrase, $statusCode)
    {
        $response = \Mockery::mock(HttpResponse::class);
        $response
            ->shouldReceive('getReasonPhrase')
            ->andReturn($reasonPhrase);

        $response
            ->shouldReceive('getStatusCode')
            ->andReturn($statusCode);

        return $response;
    }
}
