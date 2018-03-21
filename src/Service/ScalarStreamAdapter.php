<?php

namespace webignition\WebResource\Service;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

class ScalarStreamAdapter
{
    /**
     * @param int|bool|string|float $resource
     * @param array $options
     *
     * @return StreamInterface
     */
    public static function createStreamForScalarResource($resource, array $options = [])
    {
        $stream = fopen('php://temp', 'r+');
        if ($resource !== '') {
            fwrite($stream, $resource);
            fseek($stream, 0);
        }

        return new Stream($stream, $options);
    }
}
