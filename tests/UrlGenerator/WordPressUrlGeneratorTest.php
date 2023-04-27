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
use Spiriit\ComposerWriteChangelogs\UrlGenerator\WordPressUrlGenerator;
use Spiriit\ComposerWriteChangelogs\Version;

class WordPressUrlGeneratorTest extends TestCase
{
    private WordPressUrlGenerator $wordPressUrlGenerator;

    protected function setUp(): void
    {
        $this->wordPressUrlGenerator = new WordPressUrlGenerator();
    }

    /**
     * @test
     */
    public function it_supports_wordpress_urls(): void
    {
        $this->assertTrue($this->wordPressUrlGenerator->supports('http://plugins.svn.wordpress.org/social-networks-auto-poster-facebook-twitter-g/'));
        $this->assertTrue($this->wordPressUrlGenerator->supports('http://plugins.svn.wordpress.org/askimet/'));
        $this->assertTrue($this->wordPressUrlGenerator->supports('http://themes.svn.wordpress.org/minimize/'));
    }

    /**
     * @test
     */
    public function it_does_not_support_non_wordpress_urls(): void
    {
        $this->assertFalse($this->wordPressUrlGenerator->supports('https://github.com/phpunit/phpunit-mock-objects.git'));
        $this->assertFalse($this->wordPressUrlGenerator->supports('https://github.com/symfony/console'));
        $this->assertFalse($this->wordPressUrlGenerator->supports('https://bitbucket.org/mailchimp/mandrill-api-php.git'));
        $this->assertFalse($this->wordPressUrlGenerator->supports('https://bitbucket.org/rogoOOS/rog'));
    }

    /**
     * @test
     */
    public function it_generates_compare_urls(): void
    {
        $versionFrom = new Version('v1.0.0.0', 'v1.0.0', 'v1.0.0');
        $versionTo = new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1');

        $this->assertSame(
            'https://wordpress.org/plugins/askimet/changelog/',
            $this->wordPressUrlGenerator->generateCompareUrl(
                'http://plugins.svn.wordpress.org/askimet/',
                $versionFrom,
                'http://plugins.svn.wordpress.org/askimet/',
                $versionTo
            )
        );

        $this->assertSame(
            'https://themes.trac.wordpress.org/log/minimize/',
            $this->wordPressUrlGenerator->generateCompareUrl(
                'http://themes.svn.wordpress.org/minimize/',
                $versionFrom,
                'http://themes.svn.wordpress.org/minimize/',
                $versionTo
            )
        );
    }

    /**
     * @test
     */
    public function it_generates_release_urls(): void
    {
        $this->assertNull($this->wordPressUrlGenerator->generateReleaseUrl(
            'http://themes.svn.wordpress.org/minimize/',
            new Version('v1.0.1.0', 'v1.0.1', 'v1.0.1')
        ));
    }
}
