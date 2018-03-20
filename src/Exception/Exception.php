<?php

namespace webignition\WebResource\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Exception extends \Exception
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     *
     * @var RequestInterface
     */
    private $request;

    /**
     * @param ResponseInterface $response
     * @param RequestInterface|null $request
     */
    public function __construct(ResponseInterface $response, RequestInterface $request = null)
    {
        $this->response = $response;
        $this->request = $request;

        parent::__construct($response->getReasonPhrase(), $response->getStatusCode());
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
