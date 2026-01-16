<?php declare(strict_types=1);

namespace MLL\PhpCsFixerConfig\Tests\Fixer\Php80;

use MLL\PhpCsFixerConfig\Fixer\LineBreakBeforeThrowExpressionFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

final class LineBreakBeforeThrowExpressionFixerTest extends TestCase
{
    private LineBreakBeforeThrowExpressionFixer $fixer;

    protected function setUp(): void
    {
        $this->fixer = new LineBreakBeforeThrowExpressionFixer();
    }

    /** @dataProvider provideFixCases */
    public function testFix(string $expected, ?string $input = null): void
    {
        $input ??= $expected;

        $tokens = Tokens::fromCode($input);
        $this->fixer->fix(new \SplFileInfo(__FILE__), $tokens);
        $actual = $tokens->generateCode();

        self::assertSame($expected, $actual);

        $tokens = Tokens::fromCode($expected);
        $this->fixer->fix(new \SplFileInfo(__FILE__), $tokens);
        self::assertSame($expected, $tokens->generateCode());
    }

    /** @return iterable<string, array{0: string, 1?: string}> */
    public static function provideFixCases(): iterable
    {
        yield 'null coalesce throw on single line' => [
            <<<'PHP'
                <?php
                $result = $this->fetchNullable()
                    ?? throw new \RuntimeException('message');
                PHP,
            <<<'PHP'
                <?php
                $result = $this->fetchNullable() ?? throw new \RuntimeException('message');
                PHP,
        ];

        yield 'elvis throw on single line' => [
            <<<'PHP'
                <?php
                $result = $this->fetchFalsy()
                    ?: throw new \RuntimeException('message');
                PHP,
            <<<'PHP'
                <?php
                $result = $this->fetchFalsy() ?: throw new \RuntimeException('message');
                PHP,
        ];

        yield 'null coalesce throw with missing indent' => [
            <<<'PHP'
                <?php
                $result = $this->fetchNullable()
                    ?? throw new \RuntimeException('message');
                PHP,
            <<<'PHP'
                <?php
                $result = $this->fetchNullable()
                ?? throw new \RuntimeException('message');
                PHP,
        ];

        yield 'elvis throw with missing indent' => [
            <<<'PHP'
                <?php
                $result = $this->fetchFalsy()
                    ?: throw new \RuntimeException('message');
                PHP,
            <<<'PHP'
                <?php
                $result = $this->fetchFalsy()
                ?: throw new \RuntimeException('message');
                PHP,
        ];

        yield 'null coalesce throw with extra indent' => [
            <<<'PHP'
                <?php
                $result = $this->fetchNullable()
                    ?? throw new \RuntimeException('message');
                PHP,
            <<<'PHP'
                <?php
                $result = $this->fetchNullable()
                        ?? throw new \RuntimeException('message');
                PHP,
        ];

        yield 'elvis throw with extra indent' => [
            <<<'PHP'
                <?php
                $result = $this->fetchFalsy()
                    ?: throw new \RuntimeException('message');
                PHP,
            <<<'PHP'
                <?php
                $result = $this->fetchFalsy()
                        ?: throw new \RuntimeException('message');
                PHP,
        ];

        yield 'already multiline null coalesce throw - no change' => [
            <<<'PHP'
                <?php
                $result = $this->fetchNullable()
                    ?? throw new \RuntimeException('message');
                PHP,
        ];

        yield 'already multiline elvis throw - no change' => [
            <<<'PHP'
                <?php
                $result = $this->fetchFalsy()
                    ?: throw new \RuntimeException('message');
                PHP,
        ];

        yield 'regular null coalesce without throw - no change' => [
            <<<'PHP'
                <?php
                $result = $this->fetchNullable() ?? 'default';
                PHP,
        ];

        yield 'regular elvis without throw - no change' => [
            <<<'PHP'
                <?php
                $result = $this->fetchFalsy() ?: 'default';
                PHP,
        ];

        yield 'exception with arguments' => [
            <<<'PHP'
                <?php
                $user = $this->findUser($id)
                    ?? throw new \InvalidArgumentException("User {$id} not found");
                PHP,
            <<<'PHP'
                <?php
                $user = $this->findUser($id) ?? throw new \InvalidArgumentException("User {$id} not found");
                PHP,
        ];

        yield 'method chain' => [
            <<<'PHP'
                <?php
                $result = $this->getRepository()->find($id)
                    ?? throw new \RuntimeException('Not found');
                PHP,
            <<<'PHP'
                <?php
                $result = $this->getRepository()->find($id) ?? throw new \RuntimeException('Not found');
                PHP,
        ];

        yield 'return statement with null coalesce throw' => [
            <<<'PHP'
                <?php
                return $this->cache->get($key)
                    ?? throw new \RuntimeException('Cache miss');
                PHP,
            <<<'PHP'
                <?php
                return $this->cache->get($key) ?? throw new \RuntimeException('Cache miss');
                PHP,
        ];

        yield 'nested in method' => [
            <<<'PHP'
                <?php
                class Foo
                {
                    public function bar(): string
                    {
                        $result = $this->fetchNullable()
                            ?? throw new \RuntimeException('message');

                        return $result;
                    }
                }
                PHP,
            <<<'PHP'
                <?php
                class Foo
                {
                    public function bar(): string
                    {
                        $result = $this->fetchNullable() ?? throw new \RuntimeException('message');

                        return $result;
                    }
                }
                PHP,
        ];

        yield 'multiple statements' => [
            <<<'PHP'
                <?php
                $a = $foo
                    ?? throw new \RuntimeException('a');
                $b = $bar
                    ?: throw new \RuntimeException('b');
                PHP,
            <<<'PHP'
                <?php
                $a = $foo ?? throw new \RuntimeException('a');
                $b = $bar ?: throw new \RuntimeException('b');
                PHP,
        ];

        yield 'after multiline statement' => [
            <<<'PHP'
                <?php
                $a = $foo->bar()
                    ->baz()
                    ->qux()
                    ?? throw new \RuntimeException('a');
                PHP,
            <<<'PHP'
                <?php
                $a = $foo->bar()
                    ->baz()
                    ->qux() ?? throw new \RuntimeException('a');
                PHP,
        ];

        yield 'regular throw statement - no change' => [
            <<<'PHP'
                <?php
                if ($condition) {
                    throw new \RuntimeException('message');
                }
                PHP,
        ];

        yield 'ternary operator - no change' => [
            <<<'PHP'
                <?php
                $result = $condition ? throw new \RuntimeException('a') : throw new \RuntimeException('b');
                PHP,
        ];

        yield 'inline with brace - no change' => [
            <<<'PHP'
                <?php
                $foo = Foo::find(
                    1,
                    'asdf',
                ) ?? throw new \RuntimeException('message');
                PHP,
        ];

        yield 'inline with curly brace - no change' => [
            <<<'PHP'
                <?php
                $foo = match ($value) {
                    1 => 'foo',
                    default => 'asdf',
                } ?? throw new \RuntimeException('message');
                PHP,
        ];

        yield 'nested throw expression - no change' => [
            <<<'PHP'
                <?php
                return $defaultIsolation?->setting->toMPSetting()
                    ?? throw new DefaultMPSettingMissingException(
                        $this->pnl454_name
                            ?? throw new \Exception("pnl454_name missing for Ngs454Panel {$this->id}."),
                    );
                PHP,
        ];

        yield 'no whitespace before operator' => [
            <<<'PHP'
                <?php
                $result = $this->fetchNullable()
                    ?? throw new \RuntimeException('message');
                PHP,
            <<<'PHP'
                <?php
                $result = $this->fetchNullable()?? throw new \RuntimeException('message');
                PHP,
        ];
    }
}
