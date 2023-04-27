<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$header = <<<EOF
This file is part of the composer-write-changelogs project.

(c) Dev Spiriit <dev@spiriit.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        'header_comment' => ['header' => $header],
        'align_multiline_comment' => [
            'comment_type' => 'phpdocs_like',
        ],
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => false,
        ],
        'php_unit_method_casing' => [
            'case' => 'snake_case',
        ],
        'php_unit_test_annotation' => [
            'style' => 'annotation',
        ],
        'php_unit_test_case_static_method_calls' => [
            'call_type' => 'this',
        ],
        'heredoc_to_nowdoc' => false,
        'void_return' => true,
        'php_unit_test_class_requires_covers' => false,
        'php_unit_internal_class' => ['types' => ['final']],
    ])
    ->setFinder($finder)
;
