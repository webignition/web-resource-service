<?php

namespace webignition\Tests\WebResource\Service;

use webignition\WebResource\Service\ScalarStreamAdapter;

class ScalarStreamAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider createStreamForScalarResourceDataProvider
     *
     * @param int|bool|string|float $resource
     * @param string $expectedContents
     */
    public function testCreateStreamForScalarResource($resource, $expectedContents)
    {
        $stream = ScalarStreamAdapter::createStreamForScalarResource($resource);

        $this->assertEquals($expectedContents, $stream->getContents());
    }

    /**
     * @return array
     */
    public function createStreamForScalarResourceDataProvider()
    {
        return [
            'int' => [
                'resource' => 1,
                'expectedContents' => '1',
            ],
            'bool true' => [
                'resource' => true,
                'expectedContents' => '1',
            ],
            'bool false' => [
                'resource' => false,
                'expectedContents' => '',
            ],
            'float' => [
                'resource' => pi(),
                'expectedContents' => (string)pi(),
            ],
            'string' => [
                'resource' => 'foo',
                'expectedContents' => 'foo',
            ],
        ];
    }
}
