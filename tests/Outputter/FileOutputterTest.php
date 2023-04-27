<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\Outputter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\Package;
use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\OperationHandler\Install\InstallOutputFileHandler;
use Spiriit\ComposerWriteChangelogs\OperationHandler\Uninstall\UninstallOutputFileHandler;
use Spiriit\ComposerWriteChangelogs\OperationHandler\Update\UpdateOutputFileHandler;
use Spiriit\ComposerWriteChangelogs\Outputter\FileOutputter;
use Spiriit\ComposerWriteChangelogs\tests\resources\FakeUrlGenerator;

class FileOutputterTest extends TestCase
{
    private FileOutputter $fileOutputterText;

    private FileOutputter $fileOutputterJSon;

    private array $operationHandlers;

    private array $urlGenerators;

    protected function setUp(): void
    {
        $this->operationHandlers = [
            new InstallOutputFileHandler(FileOutputter::TEXT_FORMAT),
            new UninstallOutputFileHandler(FileOutputter::TEXT_FORMAT),
            new UpdateOutputFileHandler(FileOutputter::TEXT_FORMAT),
        ];

        $this->urlGenerators = [
            new FakeUrlGenerator(false, '/compare-url1', '/release-url1'),
            new FakeUrlGenerator(true, '/compare-url2', '/release-url2'),
            new FakeUrlGenerator(true, '/compare-url3', '/release-url3'),
        ];

        $handlers = [
            new InstallOutputFileHandler(FileOutputter::JSON_FORMAT),
            new UninstallOutputFileHandler(FileOutputter::JSON_FORMAT),
            new UpdateOutputFileHandler(FileOutputter::JSON_FORMAT),
        ];

        $this->fileOutputterText = new FileOutputter($this->operationHandlers, $this->urlGenerators);
        $this->fileOutputterJSon = new FileOutputter($handlers, $this->urlGenerators);
    }

    /**
     * @test
     */
    public function it_adds_operation(): void
    {
        $operation = new InstallOperation(
            new Package('spiriit/composer-write-changelogs', 'v1.0.0.0', 'v1.0.0')
        );

        $operation2 = new UpdateOperation(
            new Package('spiriit/composer-write-changelogs1', 'v1.0.0.0', 'v1.0.0'),
            new Package('spiriit/composer-write-changelogs2', 'v1.0.1.0', 'v1.0.1')
        );

        $this->fileOutputterText->addOperation($operation);
        $this->fileOutputterText->addOperation($operation2);

        $expectedOutput = <<<TEXT
Changelogs summary:

 - spiriit/composer-write-changelogs installed in version v1.0.0
   Release notes: /release-url2

 - spiriit/composer-write-changelogs1 updated from v1.0.0 to v1.0.1 patch
   See changes: /compare-url2
   Release notes: /release-url2

TEXT;

        $this->assertFalse($this->fileOutputterText->isEmpty());
        $this->assertSame($expectedOutput, $this->fileOutputterText->getOutput(FileOutputter::TEXT_FORMAT));
    }

    /**
     * @test
     */
    public function it_outputs_with_no_supported_url_generator(): void
    {
        $this->fileOutputterText = new FileOutputter($this->operationHandlers, []);

        $operation = new InstallOperation(
            new Package('spiriit/composer-write-changelogs', 'v1.0.0.0', 'v1.0.0')
        );

        $operation2 = new UpdateOperation(
            new Package('spiriit/composer-write-changelogs1', 'v1.0.0.0', 'v1.0.0'),
            new Package('spiriit/composer-write-changelogs2', 'v1.0.1.0', 'v1.0.1')
        );

        $this->fileOutputterText->addOperation($operation);
        $this->fileOutputterText->addOperation($operation2);

        $expectedOutput = <<<TEXT
Changelogs summary:

 - spiriit/composer-write-changelogs installed in version v1.0.0

 - spiriit/composer-write-changelogs1 updated from v1.0.0 to v1.0.1 patch

TEXT;

        $this->assertFalse($this->fileOutputterText->isEmpty());
        $this->assertSame($expectedOutput, $this->fileOutputterText->getOutput(FileOutputter::TEXT_FORMAT));
    }

    /**
     * @test
     */
    public function it_outputs_with_no_supported_operation_handler(): void
    {
        $this->fileOutputterText = new FileOutputter([], []);

        $operation = new InstallOperation(
            new Package('spiriit/composer-write-changelogs', 'v1.0.0.0', 'v1.0.0')
        );

        $operation2 = new UpdateOperation(
            new Package('spiriit/composer-write-changelogs1', 'v1.0.0.0', 'v1.0.0'),
            new Package('spiriit/composer-write-changelogs2', 'v1.0.1.0', 'v1.0.1')
        );

        $this->fileOutputterText->addOperation($operation);
        $this->fileOutputterText->addOperation($operation2);

        $expectedOutput = <<<TEXT
Changelogs summary:

TEXT;

        $this->assertFalse($this->fileOutputterText->isEmpty());
        $this->assertSame($expectedOutput, $this->fileOutputterText->getOutput(FileOutputter::TEXT_FORMAT));
    }

    /**
     * @test
     */
    public function it_outputs_right_text(): void
    {
        $operation = new InstallOperation(
            new Package('spiriit/composer-write-changelogs', 'v1.0.0.0', 'v1.0.0')
        );

        $operation2 = new UpdateOperation(
            new Package('spiriit/composer-write-changelogs1', 'v1.0.0.0', 'v1.0.0'),
            new Package('spiriit/composer-write-changelogs2', 'v1.0.1.0', 'v1.0.1')
        );

        $this->fileOutputterText->addOperation($operation);
        $this->fileOutputterText->addOperation($operation2);

        $expectedOutput = <<<TEXT
Changelogs summary:

 - spiriit/composer-write-changelogs installed in version v1.0.0
   Release notes: /release-url2

 - spiriit/composer-write-changelogs1 updated from v1.0.0 to v1.0.1 patch
   See changes: /compare-url2
   Release notes: /release-url2

TEXT;

        $this->assertFalse($this->fileOutputterText->isEmpty());
        $this->assertSame($expectedOutput, $this->fileOutputterText->getOutput(FileOutputter::TEXT_FORMAT));
    }

    /**
     * @test
     */
    public function it_outputs_right_json(): void
    {
        $operation = new InstallOperation(
            new Package('spiriit/composer-write-changelogs', 'v1.0.0.0', 'v1.0.0')
        );

        $operation2 = new UpdateOperation(
            new Package('spiriit/composer-write-changelogs1', 'v1.0.0.0', 'v1.0.0'),
            new Package('spiriit/composer-write-changelogs2', 'v1.0.1.0', 'v1.0.1')
        );

        $this->fileOutputterJSon->addOperation($operation);
        $this->fileOutputterJSon->addOperation($operation2);

        $expectedOutput = '[{"operation":"install","package":"spiriit/composer-write-changelogs","phrasing":"installed in version","version":"v1.0.0","releaseUrl":"/release-url2"},{"operation":"update","package":"spiriit/composer-write-changelogs1","action":"updated","phrasing":"updated from","versionFrom":"v1.0.0","versionTo":"v1.0.1","semver":"patch","changesUrl":"/compare-url2","releaseUrl":"/release-url2"}]';

        $this->assertFalse($this->fileOutputterJSon->isEmpty());
        $this->assertSame($expectedOutput, $this->fileOutputterJSon->getOutput(FileOutputter::JSON_FORMAT));
    }

    /**
     * @test
     */
    public function it_outputs_nothing_without_operation(): void
    {
        $expectedOutput = <<<TEXT
No changelogs summary
TEXT;

        $this->assertTrue($this->fileOutputterText->isEmpty());
        $this->assertSame($expectedOutput, $this->fileOutputterText->getOutput(FileOutputter::TEXT_FORMAT));
    }
}
