<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\Config;

use Spiriit\ComposerWriteChangelogs\Outputter\FileOutputter;

class ConfigBuilder
{
    private static array $validOutputFormatValues = [
        FileOutputter::TEXT_FORMAT,
        FileOutputter::JSON_FORMAT,
    ];

    private array $warnings = [];

    public function build(array $extra): Config
    {
        $this->reset();

        $gitlabHosts = $extra['gitlab-hosts'] ?? [];
        $changelogsDirPath = $extra['changelogs-dir-path'] ?? null;
        $outputFileFormat =  $extra['output-file-format'] ?? FileOutputter::TEXT_FORMAT;
        $writeSummaryFile = $extra['write-summary-file'] ?? true;
        $webhookUrl = $extra['webhook-url'] ?? null;

        if(!is_array($gitlabHosts)){
            $this->warnings[] = '"gitlab-hosts" is specified but should be an array. Ignoring.';

            $gitlabHosts = [];
        }

        if($changelogsDirPath != null && 0 === strlen(trim($changelogsDirPath))){
            $this->warnings[] = '"changelogs-dir-path" is specified but empty. Ignoring and using default changelogs dir path.';
        }

        if (!in_array($outputFileFormat, self::$validOutputFormatValues, true)) {
            $this->warnings[] = self::createWarningFromInvalidValue(
                $extra,
                'output-file-format',
                $outputFileFormat,
                sprintf('Valid options are "%s".', implode('", "', self::$validOutputFormatValues))
            );

            $outputFileFormat = FileOutputter::TEXT_FORMAT;
        }

        if($writeSummaryFile != null && 0 != strlen($writeSummaryFile) && 0 == strcmp('false', $writeSummaryFile)){
            $writeSummaryFile = false;
        }else{
            $writeSummaryFile = true;
        }

        if($webhookUrl != null && 0 == strlen($webhookUrl)){
            $this->warnings[] = '"webhookUrl" is specified but empty. Ignoring webhook';
            $webhookUrl = null;
        }

        return new Config($gitlabHosts, $changelogsDirPath, $outputFileFormat, $writeSummaryFile, $webhookUrl);
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    private function reset(): void
    {
        $this->warnings = [];
    }

    private static function createWarningFromInvalidValue(array $extra, string $key, string $default, string $additionalMessage = ''): string
    {
        $warning = sprintf(
            'Invalid value "%s" for option "%s", defaulting to "%s".',
            $extra[$key],
            $key,
            $default
        );

        if ($additionalMessage) {
            $warning .= ' ' . $additionalMessage;
        }

        return $warning;
    }
}
