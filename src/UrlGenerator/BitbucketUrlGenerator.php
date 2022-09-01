<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\UrlGenerator;

use Spiriit\ComposerWriteChangelogs\Version;

class BitbucketUrlGenerator extends AbstractUrlGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function getDomain()
    {
        return 'bitbucket.org';
    }

    /**
     * {@inheritdoc}
     */
    public function generateCompareUrl($sourceUrlFrom, Version $versionFrom, $sourceUrlTo, Version $versionTo)
    {
        // Check if both urls come from the supported domain
        // It avoids problems when one url is from another domain or is local
        if (!$this->supports($sourceUrlFrom) || !$this->supports($sourceUrlTo)) {
            return false;
        }

        $sourceUrlFrom = $this->generateBaseUrl($sourceUrlFrom);
        $sourceUrlTo = $this->generateBaseUrl($sourceUrlTo);

        // Check if comparison across forks is needed
        if ($sourceUrlFrom !== $sourceUrlTo) {
            $repositoryFrom = $this->extractRepositoryInformation($sourceUrlFrom);
            $repositoryTo = $this->extractRepositoryInformation($sourceUrlTo);

            return sprintf(
                '%s/branches/compare/%s/%s:%s%%0D%s/%s:%s',
                $sourceUrlTo,
                $repositoryTo['user'],
                $repositoryTo['repository'],
                $this->getCompareVersion($versionTo),
                $repositoryFrom['user'],
                $repositoryFrom['repository'],
                $this->getCompareVersion($versionFrom)
            );
        }

        return sprintf(
            '%s/branches/compare/%s%%0D%s',
            $sourceUrlTo,
            $this->getCompareVersion($versionTo),
            $this->getCompareVersion($versionFrom)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generateReleaseUrl($sourceUrl, Version $version)
    {
        // Releases are not supported on Bitbucket :'(
        return false;
    }
}
