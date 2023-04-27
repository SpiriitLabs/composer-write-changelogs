<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Spiriit\ComposerWriteChangelogs\Config\Config;
use Spiriit\ComposerWriteChangelogs\Config\ConfigBuilder;
use Spiriit\ComposerWriteChangelogs\Config\ConfigLocator;
use Spiriit\ComposerWriteChangelogs\Outputter\FileOutputter;
use Spiriit\ComposerWriteChangelogs\Outputter\Outputter;
use Spiriit\ComposerWriteChangelogs\Util\WebhookCaller;

class ChangelogsPlugin implements PluginInterface, EventSubscriberInterface
{
    public const EXTRA_KEY = 'composer-write-changelogs';

    private Composer $composer;

    private IOInterface $io;

    private Outputter $outputter;

    private FileOutputter $fileOutputter;

    private ConfigLocator $configLocator;

    private Config $config;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->configLocator = new ConfigLocator($this->composer);

        $this->setupConfig();
        $this->autoloadNeededClasses();

        $this->outputter = Factory::createOutputter($this->config->getGitlabHosts());
        $this->fileOutputter = Factory::createFileOutputter($this->config->getOutputFileFormat(), $this->config->getGitlabHosts());
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PackageEvents::POST_PACKAGE_UPDATE => [
                ['postPackageOperation'],
            ],
            PackageEvents::POST_PACKAGE_INSTALL => [
                ['postPackageOperation'],
            ],
            PackageEvents::POST_PACKAGE_UNINSTALL => [
                ['postPackageOperation'],
            ],
            ScriptEvents::POST_UPDATE_CMD => [
                ['postUpdate'],
            ],
        ];
    }

    /**
     * After action on package.
     */
    public function postPackageOperation(PackageEvent $event): void
    {
        $operation = $event->getOperation();

        $this->outputter->addOperation($operation);
        $this->fileOutputter->addOperation($operation);
    }

    /**
     * After the composer update.
     */
    public function postUpdate(): void
    {
        $this->io->write($this->outputter->getOutput());

        $this->handleWriteSummaryFile();
    }

    /**
     * This method ensures all the classes required to make the plugin work
     * are loaded.
     *
     * It's required to avoid composer looking for classes which no longer exist
     * (for example after the plugin is updated).
     *
     * Lot of classes (like operation handlers, url generators, Outputter, etc.)
     * do not need this because they are already autoloaded at the activation
     * of the plugin.
     */
    private function autoloadNeededClasses(): void
    {
        /** @var string $file */
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ('.php' === substr($file, 0, -4)) {
                class_exists(__NAMESPACE__.str_replace('/', '\\', substr($file, \strlen(__DIR__), -4)));
            }
        }
    }

    private function setupConfig(): void
    {
        $builder = new ConfigBuilder();

        $this->config = $builder->build(
            $this->configLocator->getConfig(self::EXTRA_KEY)
        );

        if (\count($builder->getWarnings()) > 0) {
            $this->io->writeError('<error>Invalid config for composer-changelogs plugin:</error>');
            foreach ($builder->getWarnings() as $warning) {
                $this->io->write('    '.$warning);
            }
        }
    }

    /**
     * Check the validity of the output file and if the summary is enabled in the configuration.
     * Call the author of the summary and the webhookCaller.
     */
    private function handleWriteSummaryFile(): void
    {
        if ($this->fileOutputter->isEmpty()) {
            return;
        }

        if (!$this->config->isWriteSummaryFile()) {
            return;
        }

        $this->doWriteSummaryFile();
        $this->handleWebhookCall();
    }

    /**
     * Call the webhook url passed in the configuration.
     */
    private function handleWebhookCall(): void
    {
        if (null !== $this->config->getWebhookURL()) {
            $output = $this->fileOutputter->getOutput('json');

            if (0 === strcmp('No changelogs summary', $output)) {
                return;
            }

            $caller = new WebhookCaller($output, $this->config->getWebhookURL());

            $caller->callWebhook();
        }
    }

    /**
     * Write the summary in the file.
     */
    private function doWriteSummaryFile(): void
    {
        $projectRootPath = \dirname(\Composer\Factory::getComposerFile());

        // default changelogs dir path from current composer.json location creating directory named 'changelogs'
        $changelogsDirPath = $projectRootPath.'/'.Config::CHANGELOGS_DIR;

        if ($this->config->getChangelogsDirPath()) {
            $changelogsDirPath = $projectRootPath.'/'.$this->config->getChangelogsDirPath();
        }

        if (!is_dir($changelogsDirPath) && !file_exists($changelogsDirPath) && !mkdir($changelogsDirPath)) {
            $this->io->error('The directory '.$changelogsDirPath.' was not created. Maybe you specified wrong dir path to extra changelogs-dir-path configuration.');
        }

        $filename = $changelogsDirPath.'/changelogs-'.date('Y-m-d').'-'.date('H:i').$this->getFileExtension();

        if (!file_put_contents($filename, $this->fileOutputter->getOutput($this->config->getOutputFileFormat()))) {
            $this->io->error('The file '.$filename.' was not created. Maybe you specified wrong dir path to extra changelogs-dir-path configuration or you do not have right access to specified directory.');
        }
    }

    /**
     * Return the file extension chosen in the configuration.
     * By default, the file extension is .txt.
     */
    private function getFileExtension(): string
    {
        if (FileOutputter::JSON_FORMAT === $this->config->getOutputFileFormat()) {
            return '.json';
        }

        return '.txt';
    }
}
