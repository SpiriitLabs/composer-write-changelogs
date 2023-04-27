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
use Spiriit\ComposerWriteChangelogs\UrlGenerator\GithubUrlGenerator;
use Spiriit\ComposerWriteChangelogs\Version;

class GithubUrlGeneratorTest extends TestCase
{
    private GithubUrlGenerator $githubUrlGenerator;

    protected function setUp(): void
    {
        $this->githubUrlGenerator = new GithubUrlGenerator();
    }

    /**
     * @test
     */
    public function it_supports_github_urls(): void
    {
        $this->assertTrue($this->githubUrlGenerator->supports('https://github.com/phpunit/phpunit-mock-objects.git'));
        $this->assertTrue($this->githubUrlGenerator->supports('https://github.com/symfony/console'));
        $this->assertTrue($this->githubUrlGenerator->supports('git@github.com:private/repo.git'));
    }

    /**
     * @test
     */
    public function it_does_not_support_non_github_urls(): void
    {
        $this->assertFalse($this->githubUrlGenerator->supports('https://bitbucket.org/mailchimp/mandrill-api-php.git'));
        $this->assertFalse($this->githubUrlGenerator->supports('https://bitbucket.org/rogoOOS/rog'));
    }

    /**
     * @test
     */
    public function it_generates_compare_urls_with_or_without_git_extension_in_source_url(): void
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://github.com/spiriit/composer-write-changelogs-repository/compare/v1.0.0...v1.0.1',
            $this->githubUrlGenerator->generateCompareUrl(
                'https://github.com/spiriit/composer-write-changelogs-repository',
                $versionFrom,
                'https://github.com/spiriit/composer-write-changelogs-repository',
                $versionTo
            )
        );

        $this->assertSame(
            'https://github.com/spiriit/composer-write-changelogs-repository/compare/v1.0.0...v1.0.1',
            $this->githubUrlGenerator->generateCompareUrl(
                'https://github.com/spiriit/composer-write-changelogs-repository.git',
                $versionFrom,
                'https://github.com/spiriit/composer-write-changelogs-repository.git',
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
            'https://github.com/spiriit/composer-write-changelogs-repository/compare/1234abc...v1.0.1',
            $this->githubUrlGenerator->generateCompareUrl(
                'https://github.com/spiriit/composer-write-changelogs-repository.git',
                $versionFrom,
                'https://github.com/spiriit/composer-write-changelogs-repository.git',
                $versionTo
            )
        );

        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('9999999-dev', 'dev-master', 'dev-master 6789def');

        $this->assertSame(
            'https://github.com/spiriit/composer-write-changelogs-repository/compare/v1.0.0...6789def',
            $this->githubUrlGenerator->generateCompareUrl(
                'https://github.com/spiriit/composer-write-changelogs-repository.git',
                $versionFrom,
                'https://github.com/spiriit/composer-write-changelogs-repository.git',
                $versionTo
            )
        );

        $versionFrom = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');
        $versionTo = new Version('dev-fix/issue', 'dev-fix/issue', 'dev-fix/issue 1234abc');

        $this->assertSame(
            'https://github.com/spiriit/composer-write-changelogs-repository/compare/v1.0.1...1234abc',
            $this->githubUrlGenerator->generateCompareUrl(
                'https://github.com/spiriit/composer-write-changelogs-repository.git',
                $versionFrom,
                'https://github.com/spiriit/composer-write-changelogs-repository.git',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function it_generates_compare_urls_across_forks(): void
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://github.com/acme2/repo/compare/acme1:v1.0.0...acme2:v1.0.1',
            $this->githubUrlGenerator->generateCompareUrl(
                'https://github.com/acme1/repo',
                $versionFrom,
                'https://github.com/acme2/repo',
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
            $this->githubUrlGenerator->generateCompareUrl(
                '/home/toto/work/my-package',
                $versionFrom,
                'https://github.com/acme2/repo',
                $versionTo
            )
        );

        $this->assertNull(
            $this->githubUrlGenerator->generateCompareUrl(
                'https://github.com/acme1/repo',
                $versionFrom,
                '/home/toto/work/my-package',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function it_throws_exception_when_generating_compare_urls_across_forks_if_a_source_url_is_invalid(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unrecognized url format for github.com ("https://github.com/acme2")');

        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->githubUrlGenerator->generateCompareUrl(
            'https://github.com/acme1/repo',
            $versionFrom,
            'https://github.com/acme2',
            $versionTo
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
            'https://github.com/spiriit/composer-write-changelogs-repository/compare/v1.0.0...v1.0.1',
            $this->githubUrlGenerator->generateCompareUrl(
                'git@github.com:spiriit/composer-write-changelogs-repository.git',
                $versionFrom,
                'git@github.com:spiriit/composer-write-changelogs-repository.git',
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
            $this->githubUrlGenerator->generateReleaseUrl(
                'https://github.com/spiriit/composer-write-changelogs-repository',
                new Version('9999999-dev', 'dev-master', 'dev-master 1234abc')
            )
        );

        $this->assertNull(
            $this->githubUrlGenerator->generateReleaseUrl(
                'https://github.com/spiriit/composer-write-changelogs-repository',
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
            'https://github.com/spiriit/composer-write-changelogs-repository/releases/tag/v1.0.1',
            $this->githubUrlGenerator->generateReleaseUrl(
                'https://github.com/spiriit/composer-write-changelogs-repository',
                new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
            )
        );

        $this->assertSame(
            'https://github.com/spiriit/composer-write-changelogs-repository/releases/tag/v1.0.1',
            $this->githubUrlGenerator->generateReleaseUrl(
                'https://github.com/spiriit/composer-write-changelogs-repository.git',
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
            'https://github.com/spiriit/composer-write-changelogs-repository/releases/tag/v1.0.1',
            $this->githubUrlGenerator->generateReleaseUrl(
                'git@github.com:spiriit/composer-write-changelogs-repository.git',
                new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
            )
        );
    }
}
