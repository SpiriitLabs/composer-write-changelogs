<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\OperationHandler;

use Composer\DependencyResolver\Operation\OperationInterface;
use Spiriit\ComposerWriteChangelogs\UrlGenerator\UrlGenerator;

interface OperationHandler
{
    /**
     * Return whether the handler supports the given operation.
     */
    public function supports(OperationInterface $operation): bool;

    /**
     * Extract the source url for the package related to the given operation.
     */
    public function extractSourceUrl(OperationInterface $operation): ?string;

    /**
     * Generate output for the given operation, with some links generated by
     * the url generator.
     */
    public function getOutput(OperationInterface $operation, UrlGenerator $urlGenerator = null): ?array;
}
