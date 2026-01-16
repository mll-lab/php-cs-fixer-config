<?php declare(strict_types=1);

namespace MLL\PhpCsFixerConfig\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Forces line breaks before `?? throw` and `?: throw` patterns.
 */
final class LineBreakBeforeThrowExpressionFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    public const NAME = 'MLL/line_break_before_throw_expression';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Coalesce or elvis throw expressions (`?? throw`, `?: throw`) must be on their own line.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        $result = $this->fetchNullable() ?? throw new \RuntimeException('message');

                        PHP
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_THROW);
    }

    public function getPriority(): int
    {
        // Run before operator_linebreak (priority 0)
        return 1;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            if (! $tokens[$index]->isGivenKind(T_THROW)) {
                continue;
            }

            $operatorIndex = $this->findPrecedingThrowOperator($tokens, $index);
            if ($operatorIndex === null) {
                continue;
            }

            $this->ensureLineBreakBeforeOperator($tokens, $operatorIndex);
        }
    }

    /**
     * Find `??` or `?:` operator immediately before the throw token.
     *
     * @return int|null The index of the operator (T_COALESCE or `?` for elvis), or null
     */
    private function findPrecedingThrowOperator(Tokens $tokens, int $throwIndex): ?int
    {
        $prevIndex = $tokens->getPrevMeaningfulToken($throwIndex);
        if ($prevIndex === null) {
            return null;
        }

        // Check for ?? (T_COALESCE)
        if ($tokens[$prevIndex]->isGivenKind(T_COALESCE)) {
            return $prevIndex;
        }

        // Check for ?: (elvis operator - two separate tokens)
        if ($tokens[$prevIndex]->equals(':')) {
            $questionIndex = $tokens->getPrevMeaningfulToken($prevIndex);
            if ($questionIndex !== null && $tokens[$questionIndex]->equals('?')) {
                return $questionIndex;
            }
        }

        return null;
    }

    private function ensureLineBreakBeforeOperator(Tokens $tokens, int $operatorIndex): void
    {
        $prevMeaningfulIndex = $tokens->getPrevMeaningfulToken($operatorIndex);
        if ($prevMeaningfulIndex === null) {
            return;
        }

        $hasNewlineBeforeOperator = $this->hasNewlineBetween($tokens, $prevMeaningfulIndex, $operatorIndex);

        // If the preceding expression is a multi-line block, don't add another line break
        if (! $hasNewlineBeforeOperator
            && $tokens[$prevMeaningfulIndex]->equals(')')
        ) {
            $openIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $prevMeaningfulIndex);
            if ($this->hasNewlineBetween($tokens, $openIndex, $prevMeaningfulIndex)) {
                return;
            }
        }

        if (! $hasNewlineBeforeOperator
            && $tokens[$prevMeaningfulIndex]->equals('}')
        ) {
            $openIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_CURLY_BRACE, $prevMeaningfulIndex);
            if ($this->hasNewlineBetween($tokens, $openIndex, $prevMeaningfulIndex)) {
                return;
            }
        }

        $statementStart = $this->findStatementStart($tokens, $operatorIndex);
        $expressionAlreadyMultiline = $this->hasNewlineBetween($tokens, $statementStart, $prevMeaningfulIndex);

        if ($expressionAlreadyMultiline) {
            // Use the existing indentation level from the multiline expression
            $indentation = $this->getIndentAt($tokens, $operatorIndex);
        } else {
            // Add one indent level from the statement start
            $indentation = $this->getIndentAt($tokens, $statementStart) . $this->whitespacesConfig->getIndent();
        }

        $newWhitespace = $this->whitespacesConfig->getLineEnding() . $indentation;

        $whitespaceIndex = $operatorIndex - 1;
        if ($tokens[$whitespaceIndex]->isWhitespace()) {
            if ($tokens[$whitespaceIndex]->getContent() === $newWhitespace) {
                return;
            }

            $tokens[$whitespaceIndex] = new Token([T_WHITESPACE, $newWhitespace]);
        } else {
            $tokens->insertAt($operatorIndex, new Token([T_WHITESPACE, $newWhitespace]));
        }
    }

    private function hasNewlineBetween(Tokens $tokens, int $startIndex, int $endIndex): bool
    {
        for ($i = $startIndex + 1; $i < $endIndex; ++$i) {
            $token = $tokens[$i];
            if ($token->isWhitespace() && strpos($token->getContent(), "\n") !== false) {
                return true;
            }
        }

        return false;
    }

    // TODO: Replace with IndentationTrait once we require php-cs-fixer ^3.87.0
    private function getIndentAt(Tokens $tokens, int $index): string
    {
        for ($i = $index - 1; $i >= 0; --$i) {
            $token = $tokens[$i];
            $content = $token->getContent();

            if (Preg::match('/\R(\h*)$/', $content, $matches)) {
                return $matches[1];
            }
        }

        return '';
    }

    private function findStatementStart(Tokens $tokens, int $index): int
    {
        for ($i = $index - 1; $i >= 0; --$i) {
            $token = $tokens[$i];

            if ($token->equals(';') || $token->equals('{') || $token->equals('}') || $token->isGivenKind(T_OPEN_TAG)) {
                return $tokens->getNextMeaningfulToken($i) ?? $index;
            }
        }

        return 0;
    }
}
