<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\resources;

use Spiriit\ComposerWriteChangelogs\UrlGenerator\UrlGenerator;
use Spiriit\ComposerWriteChangelogs\Version;

class FakeUrlGenerator implements UrlGenerator
{
    /** @var bool */
    private $supports;

    /** @var string|false */
    private $compareUrl;

    /** @var string|false */
    private $releaseUrl;

    /**
     * @param bool         $supports
     * @param string|false $compareUrl
     * @param string       $releaseUrl [false $releaseUrl
     */
    public function __construct($supports, $compareUrl, $releaseUrl)
    {
        $this->supports = $supports;
        $this->compareUrl = $compareUrl;
        $this->releaseUrl = $releaseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($sourceUrl)
    {
        return $this->supports;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCompareUrl($sourceUrlFrom, Version $versionFrom, $sourceUrlTo, Version $versionTo)
    {
        return $this->compareUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function generateReleaseUrl($sourceUrl, Version $version)
    {
        return $this->releaseUrl;
    }
}
