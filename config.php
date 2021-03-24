<?php

declare(strict_types=1);

namespace MLL\PhpCsFixerRules;

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

const RULES = [
    '@Symfony' => true,

    'array_indentation' => true,
    'combine_consecutive_unsets' => true,
    'concat_space' => [
        'spacing' => 'one',
    ],
    'class_attributes_separation' => [
        'method' => 'one',
        'property' => 'one',
    ],
    'heredoc_indentation' => true,
    'method_argument_space' => [
        'on_multiline' => 'ensure_fully_multiline',
    ],
    'new_with_braces' => true,
    'no_superfluous_elseif' => true,
    'no_superfluous_phpdoc_tags' => true,
    'no_useless_else' => true,
    'not_operator_with_successor_space' => true,
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
