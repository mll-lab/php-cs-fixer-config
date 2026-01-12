<?php declare(strict_types=1);

namespace MLL\PhpCsFixerConfig\Fixer;

use PhpCsFixer\AbstractPhpdocTypesFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Simplifies PHPDoc definitions to array<T>, omitting explicit array-key defaults.
 */
final class PhpdocSimplifyArrayKeyFixer extends AbstractPhpdocTypesFixer
{
    public const NAME = 'MLL/phpdoc_simplify_array_key';

    public function getName(): string
    {
        return self::NAME;
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
                         * @param array<string|int, Foo> $y
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
        // Uses [^\S\n]* (whitespace except newlines) to avoid matching multiline types,
        // which are not supported due to AbstractPhpdocTypesFixer requiring line count preservation.
        $ws = '[^\S\n]*'; // Whitespace except newlines

        return Preg::replace(
            "/\\barray<{$ws}(?:array-key|int{$ws}\\|{$ws}string|string{$ws}\\|{$ws}int){$ws},{$ws}/",
            'array<',
            $type
        );
    }
}
