<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Services\Config;

use Rocketeer\TestCases\BaseTestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationDefinitionTest extends BaseTestCase
{
    /**
     * @var Processor
     */
    protected $processor;

    /**
     * Setup the tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->processor = new Processor();
    }

    public function testCanUnifyFlagServerDeclaration()
    {
        $processed = $this->processConfiguration([
            'config' => [
                'application_name' => 'foobar',
                'connections' => [
                    'production' => [
                        'host' => 'foo.com',
                    ],
                ],
            ],
        ]);

        $connection = $processed['config']['connections']['production'];

        $this->assertArrayHasKey('servers', $connection);
        $this->assertEquals('foo.com', $connection['servers'][0]['host']);
    }

    public function testCanUnifyUnkeyedServerDeclaration()
    {
        $processed = $this->processConfiguration([
            'config' => [
                'application_name' => 'foobar',
                'connections' => [
                    'production' => [
                        [
                            'host' => 'foo.com',
                        ],
                        [
                            'host' => 'bar.com',
                        ],
                    ],
                ],
            ],
        ]);

        $connection = $processed['config']['connections']['production'];

        $this->assertArrayHasKey('servers', $connection);
        $this->assertEquals('foo.com', $connection['servers'][0]['host']);
        $this->assertEquals('bar.com', $connection['servers'][1]['host']);
    }

    public function testCanUnifyFullServerDeclaration()
    {
        $processed = $this->processConfiguration([
            'config' => [
                'application_name' => 'foobar',
                'connections' => [
                    'production' => [
                        'servers' => [
                            [
                                'host' => 'foo.com',
                            ],
                            [
                                'host' => 'bar.com',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $connection = $processed['config']['connections']['production'];

        $this->assertArrayHasKey('servers', $connection);
        $this->assertEquals('foo.com', $connection['servers'][0]['host']);
        $this->assertEquals('bar.com', $connection['servers'][1]['host']);
    }

    public function testCanProperlyMergePaths()
    {
        $processed = $this->processConfiguration(
            [
                'paths' => [
                    'php' => '/foo/php',
                    'composer' => '/foo/composer',
                    'foo' => '/bar',
                ],
            ],
            [
                'paths' => [
                    'php' => '/bar/php',
                    'bar' => '/bar/baz',
                ],
            ]
        );

        $this->assertEquals([
            'php' => '/bar/php',
            'composer' => '/foo/composer',
            'foo' => '/bar',
            'bar' => '/bar/baz',
        ], $processed['paths']);
    }

    public function testCanConfigureEventInHooks()
    {
        $configuration = $this->processConfiguration([
            'hooks' => [
                'events' => [
                    'before' => [
                        'deploy' => ['echo "foobar"'],
                        'foo' => ['bar'],
                        'baz' => [
                            function ($task) {
                                $task->run('qux');
                            },
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertArrayHasKey('deploy', $configuration['hooks']['events']['before']);
        $this->assertEquals(['echo "foobar"'], $configuration['hooks']['events']['before']['deploy']);
    }

    /**
     * @param array[] ...$config
     *
     * @return array
     */
    protected function processConfiguration(...$config)
    {
        array_unshift($config, [
            'hooks' => [
                'events' => [
                    'before' => [],
                ],
            ],
        ]);

        $processed = $this->processor->processConfiguration(new ConfigurationDefinition(), $config);

        return $processed;
    }
}
