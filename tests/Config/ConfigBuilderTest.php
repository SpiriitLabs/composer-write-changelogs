<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\Config;

use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\Config\Config;
use Spiriit\ComposerWriteChangelogs\Config\ConfigBuilder;

class ConfigBuilderTest extends TestCase
{
    private ConfigBuilder $configBuilder;

    protected function setUp(): void
    {
        $this->configBuilder = new ConfigBuilder();
    }

    public static function configCasesProvider(): \Generator
    {
        yield 'default setup' => [
            [],
            [
                'getGitlabHosts' => [],
                'getOutputFileFormat' => 'text',
                'getChangelogsDirPath' => null,
                'isWriteSummaryFile' => true,
                'getWebhookURL' => null,
            ],
        ];

        yield 'warning : gitlab-hosts not an array' => [
            [
                'gitlab-hosts' => 'gitlab.company1.com',
            ],
            [
                'getGitlabHosts' => [],
            ],
            [
                '"gitlab-hosts" is specified but should be an array. Ignoring.',
            ],
        ];

        yield 'warning: changelogs-dir-path specified but empty' => [
            [
                'changelogs-dir-path' => ' ',
            ],
            [],
            [
                '"changelogs-dir-path" is specified but empty. Ignoring and using default changelogs dir path.',
            ],
        ];

        yield 'warning: outpt-file-format invalid' => [
            [
                'output-file-format' => 'foo',
            ],
            [],
            [
                'Invalid value "foo" for option "output-file-format", defaulting to "foo". Valid options are "text", "json".',
            ],
        ];

        yield 'true: write-summary-file specified empty' => [
            [
                'write-summary-file' => '',
            ],
            [
                'isWriteSummaryFile' => true,
            ],
        ];

        yield 'true: write-summary-file random string' => [
            [
                'write-summary-file' => 'abcdef',
            ],
            [
                'isWriteSummaryFile' => true,
            ],
        ];

        yield 'accept: setup valid' => [
            [
                'gitlab-hosts' => [
                    'gitlab.company1.com',
                    'gitlab.company2.com',
                ],
                'changelogs-dir-path' => 'my/custom/path',
                'output-file-format' => 'text',
                'write-summary-file' => 'false',
            ],
            [
                'getGitlabHosts' => ['gitlab.company1.com', 'gitlab.company2.com'],
                'getOutputFileFormat' => 'text',
                'getChangelogsDirPath' => 'my/custom/path',
                'isWriteSummaryFile' => false,
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider configCasesProvider
     */
    public function it_builds_config(array $inputConfig, array $expectedConfig, array $expectedWarnings = []): void
    {
        $config = $this->configBuilder->build($inputConfig);
        $this->assertInstanceOf(Config::class, $config);

        foreach ($expectedConfig as $configKey => $value) {
            $this->assertSame($expectedConfig[$configKey], $config->{$configKey}());
        }

        $this->assertSame($expectedWarnings, $this->configBuilder->getWarnings());
    }
}
