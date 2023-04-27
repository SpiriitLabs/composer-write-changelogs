<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\OperationHandler\Install;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\Package\Package;
use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\OperationHandler\Install\InstallHandler;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeOperation;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeUrlGenerator;

class InstallHandlerTest extends TestCase
{
    private InstallHandler $installHandler;

    protected function setUp(): void
    {
        $this->installHandler = new InstallHandler();
    }

    /**
     * @test
     */
    public function it_supports_install_operation(): void
    {
        $operation = new InstallOperation(
            new Package('spiriit/composer-write-changelogs', 'v1.0.0.0', 'v1.0.0')
        );

        $this->assertTrue($this->installHandler->supports($operation));
    }

    /**
     * @test
     */
    public function it_does_not_support_non_install_operation(): void
    {
        $this->assertFalse($this->installHandler->supports(new FakeOperation('')));
    }

    /**
     * @test
     */
    public function it_extracts_source_url(): void
    {
        $package = new Package('spiriit/composer-write-changelogs', 'v1.0.0.0', 'v1.0.0');
        $package->setSourceUrl('https://example.com/spiriit/composer-write-changelogs.git');

        $operation = new InstallOperation($package);

        $this->assertSame(
            'https://example.com/spiriit/composer-write-changelogs.git',
            $this->installHandler->extractSourceUrl($operation)
        );
    }

    /**
     * @test
     */
    public function it_throws_exception_when_extracting_source_url_from_non_install_operation(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Operation should be an instance of InstallOperation');

        $this->installHandler->extractSourceUrl(new FakeOperation(''));
    }

    /**
     * @test
     */
    public function it_gets_output_without_url_generator(): void
    {
        $package = new Package('spiriit/composer-write-changelogs', 'v1.0.0.0', 'v1.0.0');
        $package->setSourceUrl('https://example.com/spiriit/composer-write-changelogs.git');

        $operation = new InstallOperation($package);

        $expectedOutput = [
            ' - <fg=green>spiriit/composer-write-changelogs</fg=green> installed in version <fg=yellow>v1.0.0</fg=yellow>',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->installHandler->getOutput($operation, null)
        );
    }

    /**
     * @test
     */
    public function it_gets_output_with_url_generator_no_supporting_compare_url(): void
    {
        $operation = new InstallOperation(
            new Package('spiriit/composer-write-changelogs', 'v1.0.0.0', 'v1.0.0')
        );

        $urlGenerator = new FakeUrlGenerator(
            true,
            null,
            'https://example.com/spiriit/composer-write-changelogs/release/v1.0.1'
        );

        $expectedOutput = [
            ' - <fg=green>spiriit/composer-write-changelogs</fg=green> installed in version <fg=yellow>v1.0.0</fg=yellow>',
            '   Release notes: https://example.com/spiriit/composer-write-changelogs/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->installHandler->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function it_gets_output_with_url_generator_no_supporting_release_url(): void
    {
        $operation = new InstallOperation(
            new Package('spiriit/composer-write-changelogs', 'v1.0.0.0', 'v1.0.0')
        );

        $urlGenerator = new FakeUrlGenerator(
            true,
            'https://example.com/spiriit/composer-write-changelogs/compare/v1.0.0/v1.0.1',
            'https://example.com/spiriit/composer-write-changelogs/release/v1.0.1'
        );

        $expectedOutput = [
            ' - <fg=green>spiriit/composer-write-changelogs</fg=green> installed in version <fg=yellow>v1.0.0</fg=yellow>',
            '   Release notes: https://example.com/spiriit/composer-write-changelogs/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->installHandler->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function it_gets_output_with_url_generator_supporting_all_urls(): void
    {
        $operation = new InstallOperation(
            new Package('spiriit/composer-write-changelogs', 'v1.0.0.0', 'v1.0.0')
        );

        $urlGenerator = new FakeUrlGenerator(
            true,
            'https://example.com/spiriit/composer-write-changelogs/compare/v1.0.0/v1.0.1',
            'https://example.com/spiriit/composer-write-changelogs/release/v1.0.1'
        );

        $expectedOutput = [
            ' - <fg=green>spiriit/composer-write-changelogs</fg=green> installed in version <fg=yellow>v1.0.0</fg=yellow>',
            '   Release notes: https://example.com/spiriit/composer-write-changelogs/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->installHandler->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function it_throws_exception_when_getting_output_from_non_install_operation(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Operation should be an instance of InstallOperation');

        $this->installHandler->getOutput(new FakeOperation(''));
    }
}
