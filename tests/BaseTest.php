<?php

namespace webignition\Tests\WebResource\Service;

use webignition\WebResource\Service\Service as WebResourceService;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Message\MessageFactory as HttpMessageFactory;
use GuzzleHttp\Message\ResponseInterface as HttpResponse;

abstract class BaseTest extends \PHPUnit_Framework_TestCase {

    const FIXTURES_BASE_PATH = '/fixtures';

    /**
     *
     * @var string
     */
    private $fixturePath = null;

    /**
     *
     * @var HttpClient
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
        $this->getHttpClient()->getEmitter()->attach(new \GuzzleHttp\Subscriber\Mock($fixtures));
    }


    protected function getCommonFixturesDataPath() {
        return __DIR__ . self::FIXTURES_BASE_PATH . '/Common';
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
     * @return HttpClient
     */
    protected function getHttpClient() {
        if (is_null($this->httpClient)) {
            $this->httpClient = new HttpClient();
        }

        return $this->httpClient;
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
        $service = new WebResourceService();
        $service->getConfiguration()->setHttpClient($this->getHttpClient());

        return $service;
    }


    /**
     * @param $message
     * @return HttpResponse
     */
    protected function getHttpResponseFromMessage($message) {
        $factory = new HttpMessageFactory();
        return $factory->fromMessage($message);
    }

}