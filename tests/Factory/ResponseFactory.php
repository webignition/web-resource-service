<?php

namespace webignition\Tests\WebResource\Service\Factory;

use GuzzleHttp\Message\ResponseInterface as HttpResponse;
use GuzzleHttp\Message\RequestInterface as HttpRequest;
use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use webignition\WebResource\Exception\Exception as WebResourceException;
use webignition\WebResource\WebResource;

class ResponseFactory
{
    const CONTENT_TYPE_HTML = 'text/html';

    /**
     * @param string $fixtureName
     * @param string $contentType
     *
     * @return Mock|ResponseInterface
     */
    public static function createFromFixture($fixtureName, $contentType = self::CONTENT_TYPE_HTML)
    {
        return self::create(200, 'OK', FixtureLoader::load($fixtureName), $contentType);
    }

    /**
     * @param int $statusCode
     * @param string $reasonPhrase
     * @param string $content
     * @param string $contentType
     *
     * @return Mock|ResponseInterface
     */
    public static function create(
        $statusCode = 200,
        $reasonPhrase = 'OK',
        $content = '',
        $contentType = self::CONTENT_TYPE_HTML
    ) {
        /* @var ResponseInterface|Mock $response */
        $response = Mockery::mock(ResponseInterface::class);

        $response
            ->shouldReceive('getStatusCode')
            ->andReturn($statusCode);

        $response
            ->shouldReceive('getReasonPhrase')
            ->andReturn($reasonPhrase);

        $response
            ->shouldReceive('getHeader')
            ->with(WebResource::HEADER_CONTENT_TYPE)
            ->andReturn([
                $contentType,
            ]);

        /* @var StreamInterface|Mock $bodyStream */
        $bodyStream = Mockery::mock(StreamInterface::class);
        $bodyStream
            ->shouldReceive('__toString')
            ->andReturn($content);

        $response
            ->shouldReceive('getBody')
            ->andReturn($bodyStream);

        return $response;
    }
}
