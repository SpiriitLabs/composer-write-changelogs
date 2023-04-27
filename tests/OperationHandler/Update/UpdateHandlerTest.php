<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\OperationHandler\Update;

use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\Package;
use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\OperationHandler\Update\UpdateHandler;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeOperation;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeUrlGenerator;

class UpdateHandlerTest extends TestCase
{
    private UpdateHandler $updateHandler;

    protected function setUp(): void
    {
        $this->updateHandler = new UpdateHandler();
    }

    /**
     * @test
     */
    public function it_supports_update_operation(): void
    {
        $operation = new UpdateOperation(
            new Package('spiriit/composer-write-changelogs', 'v1.0.0.0', 'v1.0.0'),
            new Package('spiriit/composer-write-changelogs', 'v1.0.1.0', 'v1.0.1')
        );

        $this->assertTrue($this->updateHandler->supports($operation));
    }

    /**
     * @test
     */
    public function it_does_not_support_non_update_operation(): void
    {
        $this->assertFalse($this->updateHandler->supports(new FakeOperation('')));
    }

    /**
     * @test
     */
    public function it_extracts_source_url(): void
    {
        $package1 = new Package('spiriit/composer-write-changelogs1', 'v1.0.0.0', 'v1.0.0');
        $package1->setSourceUrl('https://example.com/spiriit/composer-write-changelogs1.git');

        $package2 = new Package('spiriit/composer-write-changelogs2', 'v1.0.1.0', 'v1.0.1');
        $package2->setSourceUrl('https://example.com/spiriit/composer-write-changelogs2.git');

        $operation = new UpdateOperation($package1, $package2);

        $this->assertSame(
            'https://example.com/spiriit/composer-write-changelogs2.git',
            $this->updateHandler->extractSourceUrl($operation)
        );
    }

    /**
     * @test
     */
    public function it_throws_exception_when_extracting_source_url_from_non_update_operation(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Operation should be an instance of UpdateOperation');

        $this->updateHandler->extractSourceUrl(new FakeOperation(''));
    }

    /**
     * @test
     */
    public function it_gets_output_without_url_generator(): void
    {
        $package1 = new Package('spiriit/composer-write-changelogs1', 'v1.0.0.0', 'v1.0.0');
        $package1->setSourceUrl('https://example.com/spiriit/composer-write-changelogs1.git');

        $package2 = new Package('spiriit/composer-write-changelogs2', 'v1.1.1.0', 'v1.1.1');
        $package2->setSourceUrl('https://example.com/spiriit/composer-write-changelogs2.git');

        $operation = new UpdateOperation($package1, $package2);

        $expectedOutput = [
            ' - <fg=green>spiriit/composer-write-changelogs1</fg=green> updated from <fg=yellow>v1.0.0</fg=yellow> to <fg=yellow>v1.1.1</fg=yellow> <fg=magenta>minor</>',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateHandler->getOutput($operation, null)
        );
    }

    /**
     * @test
     */
    public function it_gets_output_with_url_generator_no_supporting_compare_url(): void
    {
        $operation = new UpdateOperation(
            new Package('spiriit/composer-write-changelogs1', 'v1.0.0.0', 'v1.0.0'),
            new Package('spiriit/composer-write-changelogs2', 'v1.0.1.0', 'v1.0.1')
        );

        $urlGenerator = new FakeUrlGenerator(
            true,
            null,
            'https://example.com/spiriit/composer-write-changelogs/release/v1.0.1'
        );

        $expectedOutput = [
            ' - <fg=green>spiriit/composer-write-changelogs1</fg=green> updated from <fg=yellow>v1.0.0</fg=yellow> to <fg=yellow>v1.0.1</fg=yellow> <fg=cyan>patch</>',
            '   Release notes: https://example.com/spiriit/composer-write-changelogs/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateHandler->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function it_gets_output_with_url_generator_no_supporting_release_url(): void
    {
        $operation = new UpdateOperation(
            new Package('spiriit/composer-write-changelogs1', 'v1.0.0.0', 'v1.0.0'),
            new Package('spiriit/composer-write-changelogs2', 'v1.0.1.0', 'v1.0.1')
        );

        $urlGenerator = new FakeUrlGenerator(
            true,
            'https://example.com/spiriit/composer-write-changelogs/compare/v1.0.0/v1.0.1',
            'https://example.com/spiriit/composer-write-changelogs/release/v1.0.1'
        );

        $expectedOutput = [
            ' - <fg=green>spiriit/composer-write-changelogs1</fg=green> updated from <fg=yellow>v1.0.0</fg=yellow> to <fg=yellow>v1.0.1</fg=yellow> <fg=cyan>patch</>',
            '   See changes: https://example.com/spiriit/composer-write-changelogs/compare/v1.0.0/v1.0.1',
            '   Release notes: https://example.com/spiriit/composer-write-changelogs/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateHandler->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function it_gets_output_with_url_generator_supporting_all_urls(): void
    {
        $operation = new UpdateOperation(
            new Package('spiriit/composer-write-changelogs1', 'v1.0.0.0', 'v1.0.0'),
            new Package('spiriit/composer-write-changelogs2', 'v1.0.1.0', 'v1.0.1')
        );

        $urlGenerator = new FakeUrlGenerator(
            true,
            'https://example.com/spiriit/composer-write-changelogs/compare/v1.0.0/v1.0.1',
            'https://example.com/spiriit/composer-write-changelogs/release/v1.0.1'
        );

        $expectedOutput = [
            ' - <fg=green>spiriit/composer-write-changelogs1</fg=green> updated from <fg=yellow>v1.0.0</fg=yellow> to <fg=yellow>v1.0.1</fg=yellow> <fg=cyan>patch</>',
            '   See changes: https://example.com/spiriit/composer-write-changelogs/compare/v1.0.0/v1.0.1',
            '   Release notes: https://example.com/spiriit/composer-write-changelogs/release/v1.0.1',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateHandler->getOutput($operation, $urlGenerator)
        );
    }

    /**
     * @test
     */
    public function it_throws_exception_when_getting_output_from_non_update_operation(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Operation should be an instance of UpdateOperation');

        $this->updateHandler->getOutput(new FakeOperation(''));
    }

    /**
     * @test
     */
    public function it_uses_correct_action_name(): void
    {
        $package1 = new Package('spiriit/composer-write-changelogs1', 'v1.0.0.0', 'v1.0.0');
        $package2 = new Package('spiriit/composer-write-changelogs2', 'v1.0.1.0', 'v1.0.1');

        $operationUpdate = new UpdateOperation($package1, $package2);

        $expectedOutput = [
            ' - <fg=green>spiriit/composer-write-changelogs1</fg=green> updated from <fg=yellow>v1.0.0</fg=yellow> to <fg=yellow>v1.0.1</fg=yellow> <fg=cyan>patch</>',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateHandler->getOutput($operationUpdate, null)
        );

        $operationDowngrade = new UpdateOperation($package2, $package1);

        $expectedOutput = [
            ' - <fg=green>spiriit/composer-write-changelogs2</fg=green> downgraded from <fg=yellow>v1.0.1</fg=yellow> to <fg=yellow>v1.0.0</fg=yellow> <fg=cyan>patch</>',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateHandler->getOutput($operationDowngrade, null)
        );
    }

    /**
     * @test
     */
    public function it_outputs_the_correct_semver_colors(): void
    {
        $base = new Package('spiriit/composer-write-changelogs1', 'v1.0.0.0', 'v1.0.0');
        $patch = new Package('spiriit/composer-write-changelogs1', 'v1.0.1.0', 'v1.0.1');
        $minor = new Package('spiriit/composer-write-changelogs2', 'v1.1.0.0', 'v1.1.0');
        $major = new Package('spiriit/composer-write-changelogs2', 'v2.0.0.0', 'v2.0.0');

        $patchUpdate = new UpdateOperation($base, $patch);

        $expectedOutput = [
            ' - <fg=green>spiriit/composer-write-changelogs1</fg=green> updated from <fg=yellow>v1.0.0</fg=yellow> to <fg=yellow>v1.0.1</fg=yellow> <fg=cyan>patch</>',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateHandler->getOutput($patchUpdate, null)
        );

        $minorUpdate = new UpdateOperation($base, $minor);

        $expectedOutput = [
            ' - <fg=green>spiriit/composer-write-changelogs1</fg=green> updated from <fg=yellow>v1.0.0</fg=yellow> to <fg=yellow>v1.1.0</fg=yellow> <fg=magenta>minor</>',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateHandler->getOutput($minorUpdate, null)
        );

        $majorUpdate = new UpdateOperation($base, $major);

        $expectedOutput = [
            ' - <fg=green>spiriit/composer-write-changelogs1</fg=green> updated from <fg=yellow>v1.0.0</fg=yellow> to <fg=yellow>v2.0.0</fg=yellow> <fg=red>major</>',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateHandler->getOutput($majorUpdate, null)
        );
    }

    /**
     * @test
     */
    public function it_displays_vcs_revision_for_dev_package(): void
    {
        $package1 = new Package('spiriit/composer-write-changelogs1', 'dev-master', 'dev-master');
        $package1->setSourceType('git');
        $package1->setSourceReference('958a5dd');
        $package2 = new Package('spiriit/composer-write-changelogs2', 'dev-master', 'dev-master');
        $package2->setSourceType('git');
        $package2->setSourceReference('6d57476');

        $operationUpdate = new UpdateOperation($package1, $package2);

        $expectedOutput = [
            ' - <fg=green>spiriit/composer-write-changelogs1</fg=green> updated from <fg=yellow>dev-master@958a5dd</fg=yellow> to <fg=yellow>dev-master@6d57476</fg=yellow>',
        ];

        $this->assertSame(
            $expectedOutput,
            $this->updateHandler->getOutput($operationUpdate, null)
        );
    }
}
