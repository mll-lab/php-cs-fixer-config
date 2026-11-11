<?php declare(strict_types=1);

namespace MLL\PhpCsFixerConfig\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @see https://www.php-fig.org/per/coding-style/#42-using-traits
 */
final class BlankLineAfterTraitImportsFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    public const NAME = 'MLL/blank_line_after_trait_imports';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Trait imports must be followed by a blank line, unless the class closes right after them.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        final class Foo
                        {
                            use Bar;
                            use Baz;
                            public const QUX = 1;
                        }

                        PHP
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(CT::T_USE_TRAIT);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            if (! $tokens[$index]->isGivenKind(CT::T_USE_TRAIT)) {
                continue;
            }

            $endIndex = $this->findImportEnd($tokens, $index);
            $nextMeaningfulIndex = $tokens->getNextMeaningfulToken($endIndex);
            if ($nextMeaningfulIndex === null) {
                continue;
            }

            $nextMeaningfulToken = $tokens[$nextMeaningfulIndex];
            if ($nextMeaningfulToken->isGivenKind(CT::T_USE_TRAIT) || $nextMeaningfulToken->equals('}')) {
                continue;
            }

            $this->ensureBlankLineAfter($tokens, $endIndex);
        }
    }

    private function findImportEnd(Tokens $tokens, int $useIndex): int
    {
        $endIndex = $tokens->getNextTokenOfKind($useIndex, [';', '{']);
        assert($endIndex !== null, 'A trait import always ends with ; or a conflict resolution block.');

        if ($tokens[$endIndex]->equals('{')) {
            return $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $endIndex);
        }

        return $endIndex;
    }

    private function ensureBlankLineAfter(Tokens $tokens, int $endIndex): void
    {
        $whitespaceIndex = $endIndex + 1;
        $whitespace = $tokens[$whitespaceIndex];
        if (! $whitespace->isWhitespace()) {
            return;
        }

        $content = $whitespace->getContent();
        $lineBreakCount = substr_count($content, "\n");
        if ($lineBreakCount === 0 || $lineBreakCount >= 2) {
            return;
        }

        $indentation = substr($content, strrpos($content, "\n") + 1);
        $lineEnding = $this->whitespacesConfig->getLineEnding();
        $tokens[$whitespaceIndex] = new Token([T_WHITESPACE, $lineEnding . $lineEnding . $indentation]);
    }
}
