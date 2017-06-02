<?php

namespace webignition\WebResource\Exception;

use GuzzleHttp\Message\ResponseInterface as HttpResponse;
use GuzzleHttp\Message\RequestInterface as HttpRequest;
use \Exception as BaseException;

class Exception extends BaseException {    
    
    /**
     *
     * @var HttpResponse
     */
    private $response;
    
    
    /**
     *
     * @var HttpRequest
     */
    private $request;
    
    
    /**
     * 
     * @param HttpResponse $response
     * @param HttpRequest $request
     */
    public function __construct(HttpResponse $response, HttpRequest $request = null) {
        $this->response = $response;
        $this->request = $request;
        
        parent::__construct($response->getReasonPhrase(), $response->getStatusCode());
    }
    
    
    /**
     * 
     * @return HttpResponse
     */
    public function getResponse() {
        return $this->response;
    }
    
    /**
     * 
     * @return HttpRequest
     */
    public function getRequest() {
        return $this->request;
    }
    
}