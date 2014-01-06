<?php

namespace webignition\WebResource\Exception;

class InvalidContentTypeException extends Exception {    
    
    /**
     *
     * @var \webignition\InternetMediaType\InternetMediaType
     */
    private $responseContentType;    
    

    /**
     * 
     * @param \webignition\InternetMediaType\InternetMediaType $responseContentType
     * @param \Guzzle\Http\Message\Response $response
     * @param \Guzzle\Http\Message\Request $request
     */
    public function __construct(\webignition\InternetMediaType\InternetMediaType $responseContentType, \Guzzle\Http\Message\Response $response, \Guzzle\Http\Message\Request $request) {
        $this->responseContentType = $responseContentType;
        
        parent::__construct($response, $request);
    }
    
}