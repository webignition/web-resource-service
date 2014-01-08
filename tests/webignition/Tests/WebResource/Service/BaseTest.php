<?php

namespace webignition\Tests\WebResource\Service;

use webignition\WebResource\Service\Service as WebResourceService;
use Guzzle\Http\Client as HttpClient;

abstract class BaseTest extends \PHPUnit_Framework_TestCase {
    
    const FIXTURES_BASE_PATH = '/../../../../fixtures';
    
    /**
     *
     * @var string
     */
    private $fixturePath = null;    
    
    /**
     *
     * @var \Guzzle\Http\Client 
     */
    private $httpClient = null;

    /**
     * 
     * @param string $testClass
     * @param string $testMethod
     */
    protected function setTestFixturePath($testClass, $testMethod) {
        $this->fixturePath = __DIR__ . self::FIXTURES_BASE_PATH . '/' . $testClass . '/' . $testMethod;       
    }    
    
    
    /**
     * 
     * @return string
     */
    protected function getTestFixturePath() {
        return $this->fixturePath;     
    }
    
    
    /**
     * 
     * @param string $fixtureName
     * @return string
     */
    protected function getFixture($fixtureName) {        
        if (file_exists($this->getTestFixturePath() . '/' . $fixtureName)) {
            return file_get_contents($this->getTestFixturePath() . '/' . $fixtureName);
        }
        
        return file_get_contents(__DIR__ . self::FIXTURES_BASE_PATH . '/Common/' . $fixtureName);        
    }
    
    
    protected function setHttpFixtures($fixtures) {        
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        
        foreach ($fixtures as $fixture) {
            if ($fixture instanceof \Exception) {
                $plugin->addException($fixture);
            } else {
                $plugin->addResponse($fixture);
            }
        }
         
        $this->getHttpClient()->addSubscriber($plugin);              
    }
    
    
    protected function getHttpFixtures($path, $filter) {
        $items = array();

        $fixturesDirectory = new \DirectoryIterator($path);
        $fixturePaths = array();
        foreach ($fixturesDirectory as $directoryItem) {
            if ($directoryItem->isFile() && ((!is_array($filter)) || (is_array($filter) && in_array($directoryItem->getFilename(), $filter)))) {                
                $fixturePaths[] = $directoryItem->getPathname();
            }
        }
        
        sort($fixturePaths);        
        
        foreach ($fixturePaths as $fixturePath) {
            $items[] = file_get_contents($fixturePath);
        }
        
        return $this->buildHttpFixtureSet($items);
    }
    
    
    /**
     * 
     * @param array $items Collection of http messages and/or curl exceptions
     * @return array
     */
    protected function buildHttpFixtureSet($items) {
        $fixtures = array();
        
        foreach ($items as $item) {
            switch ($this->getHttpFixtureItemType($item)) {
                case 'httpMessage':
                    $fixtures[] = \Guzzle\Http\Message\Response::fromMessage($item);
                    break;
                
                case 'curlException':
                    $fixtures[] = $this->getCurlExceptionFromCurlMessage($item);                    
                    break;
                
                default:
                    throw new \LogicException();
            }
        }
        
        return $fixtures;
    }
    
    protected function getCommonFixturesDataPath() {
        return __DIR__ . self::FIXTURES_BASE_PATH . '/Common';
    }
    
    
    /**
     * 
     * @param string $item
     * @return string
     */
    private function getHttpFixtureItemType($item) {
        if (substr($item, 0, strlen('HTTP')) == 'HTTP') {
            return 'httpMessage';
        }
        
        return 'curlException';
    }
    
    
    /**
     *
     * @param string $testName
     * @return string
     */
    protected function getFixturesDataPath($testName) {
        return __DIR__ . self::FIXTURES_BASE_PATH . '/' . str_replace('\\', DIRECTORY_SEPARATOR, get_class($this)) . '/' . $testName;
    }    
    
    
    /**
     * 
     * @return \Guzzle\Http\Client
     */
    protected function getHttpClient() {
        if (is_null($this->httpClient)) {
            $this->httpClient = new HttpClient();
        }
        
        return $this->httpClient;
    }
    
    
    protected function enableBackoffPlugin() {
        $this->getHttpClient()->addSubscriber($this->getBackoffPlugin());
    }
    
    
    /**
     * 
     * @return \Guzzle\Plugin\Backoff\BackoffPlugin
     */
    protected function getBackoffPlugin() {
        return \Guzzle\Plugin\Backoff\BackoffPlugin::getExponentialBackoff(
            3,
            array(500, 503, 504)
        );
    }
    
    
    protected function getWebResourceServiceWithContentTypeMap() {
        $webResourceService = new WebResourceService();
        $webResourceService->getConfiguration()->setContentTypeWebResourceMap(array(
            'text/html' => 'webignition\WebResource\WebPage\WebPage',
            'application/xhtml+xml' =>'webignition\WebResource\WebPage\WebPage',
            'application/json' => 'webignition\WebResource\JsonDocument\JsonDocument'            
        ));
        
        return $webResourceService;
    }
    
    
    protected function getDefaultWebResourceService() {
        return new WebResourceService();        
    }
    
}