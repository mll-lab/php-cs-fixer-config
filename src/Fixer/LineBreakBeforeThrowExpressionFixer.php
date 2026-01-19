<?php declare(strict_types=1);

namespace MLL\PhpCsFixerConfig\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\CT;
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

            // Adjust following lines only when we're adding a new line break
            if (! $hasNewlineBeforeOperator) {
                $this->adjustFollowingIndentation($tokens, $operatorIndex);
            }
        } else {
            $tokens->insertAt($operatorIndex, new Token([T_WHITESPACE, $newWhitespace]));
            $this->adjustFollowingIndentation($tokens, $operatorIndex + 1);
        }
    }

    /**
     * Adjust indentation for lines following the operator when a line break is inserted.
     */
    private function adjustFollowingIndentation(Tokens $tokens, int $operatorIndex): void
    {
        $indent = $this->whitespacesConfig->getIndent();
        $statementEnd = $this->findStatementEnd($tokens, $operatorIndex);

        for ($i = $operatorIndex + 1; $i < $statementEnd; ++$i) {
            $token = $tokens[$i];
            if (! $token->isWhitespace()) {
                continue;
            }

            $content = $token->getContent();
            if (strpos($content, "\n") === false) {
                continue;
            }

            // Add one indent level after each newline
            $newContent = Preg::replace('/(\R)(\h*)/', '$1' . $indent . '$2', $content);
            if ($newContent !== $content) {
                $tokens[$i] = new Token([T_WHITESPACE, $newContent]);
            }
        }
    }

    /**
     * Find the end of the current statement (semicolon or closing match arm).
     */
    private function findStatementEnd(Tokens $tokens, int $index): int
    {
        $nestingLevel = 0;

        for ($i = $index; $i < $tokens->count(); ++$i) {
            $token = $tokens[$i];

            if ($token->equals('(') || $token->equals('[') || $token->equals('{')) {
                ++$nestingLevel;
            } elseif ($token->equals(')') || $token->equals(']') || $token->equals('}')) {
                if ($nestingLevel === 0) {
                    // We've reached the end of a block without finding a semicolon
                    return $i;
                }
                --$nestingLevel;
            } elseif ($nestingLevel === 0 && ($token->equals(';') || $token->equals(','))) {
                return $i;
            }
        }

        return $tokens->count();
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
        $nestingLevel = 0;

        for ($i = $index - 1; $i >= 0; --$i) {
            $token = $tokens[$i];

            // Track nesting to ignore commas/arrows inside parentheses, brackets, or braces
            // Note: CT::T_ARRAY_SQUARE_BRACE_* are custom tokens for array literal brackets
            if ($token->equals(')') || $token->equals(']')
                || $token->isGivenKind(CT::T_ARRAY_SQUARE_BRACE_CLOSE)) {
                ++$nestingLevel;
            } elseif ($token->equals('(') || $token->equals('[')
                || $token->isGivenKind(CT::T_ARRAY_SQUARE_BRACE_OPEN)) {
                --$nestingLevel;
            } elseif ($token->equals('}')) {
                // Closing brace at top level is a statement boundary
                if ($nestingLevel === 0) {
                    return $tokens->getNextMeaningfulToken($i) ?? $index;
                }
                ++$nestingLevel;
            } elseif ($token->equals('{')) {
                // Opening brace at any level is a statement boundary
                return $tokens->getNextMeaningfulToken($i) ?? $index;
            } elseif ($nestingLevel === 0) {
                if ($token->equals(';') || $token->isGivenKind(T_OPEN_TAG)) {
                    return $tokens->getNextMeaningfulToken($i) ?? $index;
                }
                // Comma and double arrow are statement boundaries only at top level (for match arms)
                if ($token->equals(',') || $token->isGivenKind(T_DOUBLE_ARROW)) {
                    return $tokens->getNextMeaningfulToken($i) ?? $index;
                }
            }
        }

        return 0;
    }
}
