<?php

namespace webignition\WebResource\Exception;

use GuzzleHttp\Message\ResponseInterface as HttpResponse;
use GuzzleHttp\Message\RequestInterface as HttpRequest;
use webignition\InternetMediaType\InternetMediaType;

class InvalidContentTypeException extends Exception {
    
    /**
     *
     * @var InternetMediaType
     */
    private $responseContentType;    
    

    /**
     * 
     * @param InternetMediaType $responseContentType
     * @param HttpResponse $response
     * @param HttpRequest $request
     */
    public function __construct(InternetMediaType $responseContentType, HttpResponse $response, HttpRequest $request) {
        $this->responseContentType = $responseContentType;
        
        parent::__construct($response, $request);
    }
    
    
    /**
     * 
     * @return InternetMediaType
     */
    public function getResponseContentType() {
        return $this->responseContentType;
    }
    
}