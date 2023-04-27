<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\UrlGenerator;

use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\UrlGenerator\GitlabUrlGenerator;
use Spiriit\ComposerWriteChangelogs\Version;

class GitlabUrlGeneratorTest extends TestCase
{
    private GitlabUrlGenerator $gitlabUrlGenerator;

    protected function setUp(): void
    {
        $this->gitlabUrlGenerator = new GitlabUrlGenerator('gitlab.company.org');
    }

    /**
     * @test
     */
    public function it_supports_gitlab_urls(): void
    {
        $this->assertTrue($this->gitlabUrlGenerator->supports('https://gitlab.company.org/phpunit/phpunit-mock-objects.git'));
        $this->assertTrue($this->gitlabUrlGenerator->supports('https://gitlab.company.org/symfony/console'));
        $this->assertTrue($this->gitlabUrlGenerator->supports('git@gitlab.company.org:private/repo.git'));
    }

    /**
     * @test
     */
    public function it_does_not_support_non_gitlab_urls(): void
    {
        $this->assertFalse($this->gitlabUrlGenerator->supports('https://company.org/about-us'));
        $this->assertFalse($this->gitlabUrlGenerator->supports('https://bitbucket.org/rogoOOS/rog'));
    }

    /**
     * @test
     */
    public function it_generates_compare_urls_with_or_without_git_extension_in_source_url(): void
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://gitlab.company.org/spiriit/composer-write-changelogs-repository/compare/v1.0.0...v1.0.1',
            $this->gitlabUrlGenerator->generateCompareUrl(
                'https://gitlab.company.org/spiriit/composer-write-changelogs-repository',
                $versionFrom,
                'https://gitlab.company.org/spiriit/composer-write-changelogs-repository',
                $versionTo
            )
        );

        $this->assertSame(
            'https://gitlab.company.org/spiriit/composer-write-changelogs-repository/compare/v1.0.0...v1.0.1',
            $this->gitlabUrlGenerator->generateCompareUrl(
                'https://gitlab.company.org/spiriit/composer-write-changelogs-repository.git',
                $versionFrom,
                'https://gitlab.company.org/spiriit/composer-write-changelogs-repository.git',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function it_generates_compare_urls_with_dev_versions(): void
    {
        $versionFrom = new Version('v.1.0.9999999.9999999-dev', 'dev-master', 'dev-master 1234abc');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://gitlab.company.org/spiriit/composer-write-changelogs-repository/compare/1234abc...v1.0.1',
            $this->gitlabUrlGenerator->generateCompareUrl(
                'https://gitlab.company.org/spiriit/composer-write-changelogs-repository.git',
                $versionFrom,
                'https://gitlab.company.org/spiriit/composer-write-changelogs-repository.git',
                $versionTo
            )
        );

        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('9999999-dev', 'dev-master', 'dev-master 6789def');

        $this->assertSame(
            'https://gitlab.company.org/spiriit/composer-write-changelogs-repository/compare/v1.0.0...6789def',
            $this->gitlabUrlGenerator->generateCompareUrl(
                'https://gitlab.company.org/spiriit/composer-write-changelogs-repository.git',
                $versionFrom,
                'https://gitlab.company.org/spiriit/composer-write-changelogs-repository.git',
                $versionTo
            )
        );

        $versionFrom = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');
        $versionTo = new Version('dev-fix/issue', 'dev-fix/issue', 'dev-fix/issue 1234abc');

        $this->assertSame(
            'https://gitlab.company.org/spiriit/composer-write-changelogs-repository/compare/v1.0.1...1234abc',
            $this->gitlabUrlGenerator->generateCompareUrl(
                'https://gitlab.company.org/spiriit/composer-write-changelogs-repository.git',
                $versionFrom,
                'https://gitlab.company.org/spiriit/composer-write-changelogs-repository.git',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function it_does_not_generate_compare_urls_across_forks(): void
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertNull(
            $this->gitlabUrlGenerator->generateCompareUrl(
                'https://gitlab.company.org/acme1/repo',
                $versionFrom,
                'https://gitlab.company.org/acme2/repo',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function it_does_not_generate_compare_urls_for_unsupported_url(): void
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertNull(
            $this->gitlabUrlGenerator->generateCompareUrl(
                '/home/toto/work/my-package',
                $versionFrom,
                'https://gitlab.company.org/acme2/repo',
                $versionTo
            )
        );

        $this->assertNull(
            $this->gitlabUrlGenerator->generateCompareUrl(
                'https://gitlab.company.org/acme1/repo',
                $versionFrom,
                '/home/toto/work/my-package',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function it_generates_compare_urls_with_ssh_source_url(): void
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://gitlab.company.org/spiriit/composer-write-changelogs-repository/compare/v1.0.0...v1.0.1',
            $this->gitlabUrlGenerator->generateCompareUrl(
                'git@gitlab.company.org:spiriit/composer-write-changelogs-repository.git',
                $versionFrom,
                'git@gitlab.company.org:spiriit/composer-write-changelogs-repository.git',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function it_does_not_generate_release_urls_for_dev_version(): void
    {
        $this->assertNull(
            $this->gitlabUrlGenerator->generateReleaseUrl(
                'https://gitlab.company.org/spiriit/composer-write-changelogs-repository',
                new Version('9999999-dev', 'dev-master', 'dev-master 1234abc')
            )
        );

        $this->assertNull(
            $this->gitlabUrlGenerator->generateReleaseUrl(
                'https://gitlab.company.org/spiriit/composer-write-changelogs-repository',
                new Version('dev-fix/issue', 'dev-fix/issue', 'dev-fix/issue 1234abc')
            )
        );
    }

    /**
     * @test
     */
    public function it_generates_release_urls(): void
    {
        $this->assertSame(
            'https://gitlab.company.org/spiriit/composer-write-changelogs-repository/tags/v1.0.1',
            $this->gitlabUrlGenerator->generateReleaseUrl(
                'https://gitlab.company.org/spiriit/composer-write-changelogs-repository',
                new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
            )
        );

        $this->assertSame(
            'https://gitlab.company.org/spiriit/composer-write-changelogs-repository/tags/v1.0.1',
            $this->gitlabUrlGenerator->generateReleaseUrl(
                'https://gitlab.company.org/spiriit/composer-write-changelogs-repository.git',
                new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
            )
        );
    }

    /**
     * @test
     */
    public function it_generates_release_url_with_ssh_source_url(): void
    {
        $this->assertSame(
            'https://gitlab.company.org/spiriit/composer-write-changelogs-repository/tags/v1.0.1',
            $this->gitlabUrlGenerator->generateReleaseUrl(
                'git@gitlab.company.org:spiriit/composer-write-changelogs-repository.git',
                new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
            )
        );
    }
}
