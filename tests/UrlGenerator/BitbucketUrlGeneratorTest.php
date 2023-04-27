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
use Spiriit\ComposerWriteChangelogs\UrlGenerator\BitbucketUrlGenerator;
use Spiriit\ComposerWriteChangelogs\Version;

class BitbucketUrlGeneratorTest extends TestCase
{
    private BitbucketUrlGenerator $bitbucketUrlGenerator;

    protected function setUp(): void
    {
        $this->bitbucketUrlGenerator = new BitbucketUrlGenerator();
    }

    /**
     * @test
     */
    public function it_supports_bitbucket_urls(): void
    {
        $this->assertTrue($this->bitbucketUrlGenerator->supports('https://bitbucket.org/mailchimp/mandrill-api-php.git'));
        $this->assertTrue($this->bitbucketUrlGenerator->supports('https://bitbucket.org/rogoOOS/rog'));
        $this->assertTrue($this->bitbucketUrlGenerator->supports('git@bitbucket.org:private/repo.git'));
    }

    /**
     * @test
     */
    public function it_does_not_support_non_bitbucket_urls(): void
    {
        $this->assertFalse($this->bitbucketUrlGenerator->supports('https://github.com/phpunit/phpunit-mock-objects.git'));
        $this->assertFalse($this->bitbucketUrlGenerator->supports('https://github.com/symfony/console'));
    }

    /**
     * @test
     */
    public function it_generates_compare_urls_with_or_without_git_extension_in_source_url(): void
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://bitbucket.org/spiriit/composer-write-changelogs-repository/branches/compare/v1.0.1%0Dv1.0.0',
            $this->bitbucketUrlGenerator->generateCompareUrl(
                'https://bitbucket.org/spiriit/composer-write-changelogs-repository',
                $versionFrom,
                'https://bitbucket.org/spiriit/composer-write-changelogs-repository',
                $versionTo
            )
        );

        $this->assertSame(
            'https://bitbucket.org/spiriit/composer-write-changelogs-repository/branches/compare/v1.0.1%0Dv1.0.0',
            $this->bitbucketUrlGenerator->generateCompareUrl(
                'https://bitbucket.org/spiriit/composer-write-changelogs-repository.git',
                $versionFrom,
                'https://bitbucket.org/spiriit/composer-write-changelogs-repository.git',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function it_generates_compare_urls_with_dev_versions(): void
    {
        $versionFrom = new Version('v1.0.9999999.9999999-dev', 'dev-master', 'dev-master 1234abc');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://bitbucket.org/spiriit/composer-write-changelogs-repository/branches/compare/v1.0.1%0D1234abc',
            $this->bitbucketUrlGenerator->generateCompareUrl(
                'https://bitbucket.org/spiriit/composer-write-changelogs-repository.git',
                $versionFrom,
                'https://bitbucket.org/spiriit/composer-write-changelogs-repository.git',
                $versionTo
            )
        );

        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('9999999-dev', 'dev-master', 'dev-master 6789def');

        $this->assertSame(
            'https://bitbucket.org/spiriit/composer-write-changelogs-repository/branches/compare/6789def%0Dv1.0.0',
            $this->bitbucketUrlGenerator->generateCompareUrl(
                'https://bitbucket.org/spiriit/composer-write-changelogs-repository.git',
                $versionFrom,
                'https://bitbucket.org/spiriit/composer-write-changelogs-repository.git',
                $versionTo
            )
        );

        $versionFrom = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');
        $versionTo = new Version('dev-fix/issue', 'dev-fix/issue', 'dev-fix/issue 1234abc');

        $this->assertSame(
            'https://bitbucket.org/spiriit/composer-write-changelogs-repository/branches/compare/1234abc%0Dv1.0.1',
            $this->bitbucketUrlGenerator->generateCompareUrl(
                'https://bitbucket.org/spiriit/composer-write-changelogs-repository.git',
                $versionFrom,
                'https://bitbucket.org/spiriit/composer-write-changelogs-repository.git',
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
            'https://bitbucket.org/spiriit2/repo/branches/compare/spiriit2/repo:v1.0.1%0Dspiriit1/repo:v1.0.0',
            $this->bitbucketUrlGenerator->generateCompareUrl(
                'https://bitbucket.org/spiriit1/repo',
                $versionFrom,
                'https://bitbucket.org/spiriit2/repo',
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
            $this->bitbucketUrlGenerator->generateCompareUrl(
                '/home/toto/work/my-package',
                $versionFrom,
                'https://bitbucket.org/spiriit2/repo',
                $versionTo
            )
        );

        $this->assertNull(
            $this->bitbucketUrlGenerator->generateCompareUrl(
                'https://bitbucket.org/spiriit1/repo',
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
        $this->expectExceptionMessage('Unrecognized url format for bitbucket.org ("https://bitbucket.org/spiriit2")');

        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->bitbucketUrlGenerator->generateCompareUrl(
            'https://bitbucket.org/spiriit1/repo',
            $versionFrom,
            'https://bitbucket.org/spiriit2',
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
            'https://bitbucket.org/spiriit/composer-write-changelogs-repository/branches/compare/v1.0.1%0Dv1.0.0',
            $this->bitbucketUrlGenerator->generateCompareUrl(
                'git@bitbucket.org:spiriit/composer-write-changelogs-repository.git',
                $versionFrom,
                'git@bitbucket.org:spiriit/composer-write-changelogs-repository.git',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function it_does_not_generate_release_urls(): void
    {
        $this->assertNull(
            $this->bitbucketUrlGenerator->generateReleaseUrl(
                'https://bitbucket.org/spiriit/composer-write-changelogs-repository',
                new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
            )
        );

        $this->assertNull(
            $this->bitbucketUrlGenerator->generateReleaseUrl(
                'https://bitbucket.org/spiriit/composer-write-changelogs-repository.git',
                new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
            )
        );
    }
}
