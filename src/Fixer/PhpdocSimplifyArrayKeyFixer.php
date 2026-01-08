<?php declare(strict_types=1);

namespace MLL\PhpCsFixerConfig\Fixer;

use PhpCsFixer\AbstractPhpdocTypesFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Simplifies array<array-key, T> to array<T>.
 *
 * Since array-key is equivalent to int|string and arrays naturally have int|string keys,
 * specifying this explicitly is redundant.
 */
final class PhpdocSimplifyArrayKeyFixer extends AbstractPhpdocTypesFixer
{
    public function getName(): string
    {
        return 'MLL/phpdoc_simplify_array_key';
    }

    public static function name(): string
    {
        return (new self())->getName();
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'PHPDoc `array<T>` should be used instead of `array<array-key, T>`.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        /**
                         * @param array<array-key, string> $x
                         * @return array<int|string, Foo>
                         */

                        PHP
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_DOC_COMMENT);
    }

    protected function normalize(string $type): string
    {
        // Match: array<array-key, T> or array<int|string, T> or array<string|int, T>
        return Preg::replace(
            '/\barray<\s*(?:array-key|int\s*\|\s*string|string\s*\|\s*int)\s*,\s*/',
            'array<',
            $type
        );
    }
}
