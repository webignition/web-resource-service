<?php

namespace webignition\WebResource\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webignition\InternetMediaType\InternetMediaType;

class InvalidContentTypeException extends Exception
{
    /**
     * @var InternetMediaType
     */
    private $responseContentType;

    /**
     * @param InternetMediaType $responseContentType
     * @param ResponseInterface $response
     * @param RequestInterface $request
     */
    public function __construct(
        InternetMediaType $responseContentType,
        ResponseInterface $response,
        RequestInterface $request
    ) {
        $this->responseContentType = $responseContentType;

        parent::__construct($response, $request);
    }

    /**
     * @return InternetMediaType
     */
    public function getResponseContentType()
    {
        return $this->responseContentType;
    }
}
