<?php

declare(strict_types=1);

namespace MLL\PhpCsFixerRules;

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

const RULES = [
    '@Symfony' => true,

    'array_indentation' => true,
    'array_syntax' => [
        'syntax' => 'short',
    ],
    'binary_operator_spaces' => [
        'default' => 'single_space',
    ],
    'concat_space' => [
        'spacing' => 'one',
    ],
    'class_attributes_separation' => [
        'elements' => [
            'method',
            'property',
        ],
    ],
    'heredoc_indentation' => true,
    'method_argument_space' => [
        'on_multiline' => 'ensure_fully_multiline',
    ],
    'linebreak_after_opening_tag' => true,
    'new_with_braces' => true,
    'no_superfluous_phpdoc_tags' => true,
    'not_operator_with_successor_space' => true,
    'ordered_imports' => true,
    'operator_linebreak' => [
        'position' => 'beginning',
    ],
    'phpdoc_order' => true,
    'phpdoc_align' => [
        'align' => 'left',
        'tags' => [
            'param',
            'property',
            'property-read',
            'property-write',
            'return',
            'throws',
            'type',
            'var',
            'method',
        ],
    ],
    'phpdoc_no_alias_tag' => [
        'type' => 'var',
        'link' => 'see',
    ],
    'phpdoc_no_empty_return' => false,
    'single_line_throw' => false,

    // Risky rules
    'declare_strict_types' => true,
    'logical_operators' => true,
];

/**
 * Create a php-cs-fixer config that is enhanced with MLL rules.
 *
 * @param array<string, mixed> $ruleOverrides Custom rules that override the ones from this config
 */
function config(Finder $finder, array $ruleOverrides = []): Config
{
    return (new Config())
        ->setFinder($finder)
        ->setRiskyAllowed(true)
        ->setRules(array_merge(RULES, $ruleOverrides));
}
