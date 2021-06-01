<?php declare(strict_types=1);

namespace MLL\PhpCsFixerConfig;

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
        'elements' => [
            'method' => 'one',
            'property' => 'one',
        ],
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
    'phpdoc_no_alias_tag' => [
        'replacements' => [
            'type' => 'var',
            'link' => 'see',
        ],
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
];

const RISKY_RULES = [
    'declare_strict_types' => true,
    // Technically not strict rules, but they go hand-in-hand with declare_strict_types
    // to achieve a first line of: "<?php declare(strict_types=1);" with no extra newlines
    // see https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/4252
    'linebreak_after_opening_tag' => false,
    'blank_line_after_opening_tag' => false,

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
        ->setRules(array_merge(RULES, $ruleOverrides));
}

/**
 * Create a php-cs-fixer config with risky rules.
 *
 * @param array<string, mixed> $ruleOverrides Custom rules that override the ones from this config
 */
function risky(Finder $finder, array $ruleOverrides = []): Config
{
    return config($finder, array_merge(RISKY_RULES, $ruleOverrides))
        ->setRiskyAllowed(true);
}
