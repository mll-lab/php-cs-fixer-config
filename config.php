<?php declare(strict_types=1);

namespace MLL\PhpCsFixerConfig;

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixerCustomFixers\Fixer\ConstructorEmptyBracesFixer;
use PhpCsFixerCustomFixers\Fixer\DeclareAfterOpeningTagFixer;
use PhpCsFixerCustomFixers\Fixers;

/**
 * Create a php-cs-fixer config that is enhanced with MLL rules.
 *
 * @param array<string, mixed> $ruleOverrides Custom rules that override the ones from this config
 */
function config(Finder $finder, array $ruleOverrides = []): Config
{
    $safeRules = [
        '@Symfony' => true,

        'array_indentation' => true,
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => [],
        ],
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
        'explicit_string_variable' => true,
        'fully_qualified_strict_types' => false, // Messes up global config files where fully qualified class names are preferred
        'heredoc_indentation' => false,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line',
        ],
        'new_with_braces' => true,
        'no_superfluous_elseif' => true,
        'no_superfluous_phpdoc_tags' => true,
        'no_useless_else' => true,
        'not_operator_with_successor_space' => true,
        'nullable_type_declaration' => [
            'syntax' => 'question_mark',
        ],
        'operator_linebreak' => [
            'position' => 'beginning',
        ],
        'phpdoc_align' => false, // Messes with complex array shapes
        'phpdoc_line_span' => [
            'property' => 'single',
            'const' => 'single',
            'method' => 'single',
        ],
        'phpdoc_no_alias_tag' => [
            'replacements' => [
                'type' => 'var',
                'link' => 'see',
            ],
        ],
        'phpdoc_order' => true,
        'phpdoc_to_comment' => false, // Intermediary PHPDocs are sometimes useful to provide type assertions for PHPStan
        'single_line_empty_body' => true,
        'single_line_throw' => false,
        // TODO add trailing commas everywhere when dropping PHP 7.4
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => [
                'array_destructuring',
                'arrays',
            ],
        ],
        'yoda_style' => [ // Not necessary with static analysis, non-Yoda is more natural to write and read
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],

        ConstructorEmptyBracesFixer::name() => true,
        DeclareAfterOpeningTagFixer::name() => true, // Use native rule when added with https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/issues/2062
    ];

    return (new Config())
        ->registerCustomFixers(new Fixers())
        ->setFinder($finder)
        ->setRules(array_merge($safeRules, $ruleOverrides));
}

/**
 * Create a php-cs-fixer config with risky rules.
 *
 * @param array<string, mixed> $ruleOverrides Custom rules that override the ones from this config
 */
function risky(Finder $finder, array $ruleOverrides = []): Config
{
    $riskyRules = [
        'declare_strict_types' => true,
        // Technically not strict rules, but they go hand-in-hand with declare_strict_types
        // to achieve a first line of: "<?php declare(strict_types=1);" with no extra newlines
        // see https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/4252
        'linebreak_after_opening_tag' => false,
        'blank_line_after_opening_tag' => false,

        'logical_operators' => true,
        'modernize_types_casting' => true,
        'use_arrow_functions' => true,
    ];

    return config($finder, array_merge($riskyRules, $ruleOverrides))
        ->setRiskyAllowed(true);
}
