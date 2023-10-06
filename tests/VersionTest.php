<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests;

use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\Version;

class VersionTest extends TestCase
{
    private Version $version;

    public static function provideIt_keep_version_formatsCases(): iterable
    {
        yield 'all sames' => ['V1.0.0.0', 'V1.0.0', 'V1.0.0'];

        yield 'normal uses' => ['v.1.0.9999999.9999999-dev', 'dev-master', 'dev-master 1234abc'];
    }

    /**
     * @dataProvider provideIt_keep_version_formatsCases
     *
     * @test
     */
    public function it_keep_version_formats(string $name, string $pretty, string $fullPretty): void
    {
        $this->version = new Version($name, $pretty, $fullPretty);
        $this->assertSame($name, $this->version->getName());
        $this->assertSame($pretty, $this->version->getPretty());
        $this->assertSame($fullPretty, $this->version->getFullPretty());
    }

    /**
     * Data provider of 'test_it_detects_dev_version' test.
     */
    public static function provideIt_detects_dev_versionCases(): iterable
    {
        yield 'all sames' => ['v1.0.0.0', 'v1.0.0.0', 'v1.0.0.0', false];

        yield 'abused' => ['v.1.0.9999999.9999999-dev', 'dev-master', 'dev-master 1234abc', true];

        yield 'normal uses' => ['dev-fix/issue', 'dev-fix/issue', 'dev-fix/issue 1234abc', true];
    }

    /**
     * @dataProvider provideIt_detects_dev_versionCases
     *
     * @test
     */
    public function it_detects_dev_version(string $name, string $pretty, string $fullPretty, bool $isDev): void
    {
        $this->version = new Version($name, $pretty, $fullPretty);
        $this->assertSame($isDev, $this->version->isDev());
    }
}
