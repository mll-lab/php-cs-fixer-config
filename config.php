<?php declare(strict_types=1);

namespace MLL\PhpCsFixerConfig;

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
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
        'heredoc_indentation' => false,
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
        'phpdoc_align' => false, // Messes with complex array shapes
        'phpdoc_no_alias_tag' => [
            'replacements' => [
                'type' => 'var',
                'link' => 'see',
            ],
        ],
        'phpdoc_order' => true,
        'phpdoc_to_comment' => false, // Intermediary PHPDocs are sometimes useful to provide type assertions for PHPStan
        'single_line_throw' => false,
    ];

    // Not available for PHP 7.2, see https://github.com/kubawerlos/php-cs-fixer-custom-fixers/tree/v2.5.0
    if (class_exists(DeclareAfterOpeningTagFixer::class)) {
        $safeRules[DeclareAfterOpeningTagFixer::name()] = true;
    }

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
    ];

    return config($finder, array_merge($riskyRules, $ruleOverrides))
        ->setRiskyAllowed(true);
}
