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

        yield 'array access' => [
            <<<'PHP'
                <?php
                $result = $data['key']
                    ?? throw new \RuntimeException('message');
                PHP,
            <<<'PHP'
                <?php
                $result = $data['key'] ?? throw new \RuntimeException('message');
                PHP,
        ];

        yield 'static method exception factory' => [
            <<<'PHP'
                <?php
                $user = $this->find($id)
                    ?? throw UserNotFoundException::forId($id);
                PHP,
            <<<'PHP'
                <?php
                $user = $this->find($id) ?? throw UserNotFoundException::forId($id);
                PHP,
        ];

        yield 'chained null coalesce - only last gets line break' => [
            <<<'PHP'
                <?php
                $result = $a ?? $b ?? $c
                    ?? throw new \RuntimeException('message');
                PHP,
            <<<'PHP'
                <?php
                $result = $a ?? $b ?? $c ?? throw new \RuntimeException('message');
                PHP,
        ];

        yield 'nullsafe operator chain' => [
            <<<'PHP'
                <?php
                $result = $this->getUser()?->getProfile()?->getName()
                    ?? throw new \RuntimeException('message');
                PHP,
            <<<'PHP'
                <?php
                $result = $this->getUser()?->getProfile()?->getName() ?? throw new \RuntimeException('message');
                PHP,
        ];

        yield 'throw existing exception variable' => [
            <<<'PHP'
                <?php
                $result = $value
                    ?? throw $fallbackException;
                PHP,
            <<<'PHP'
                <?php
                $result = $value ?? throw $fallbackException;
                PHP,
        ];

        yield 'named arguments in exception constructor' => [
            <<<'PHP'
                <?php
                $result = $value
                    ?? throw new \RuntimeException(message: 'error', code: 500);
                PHP,
            <<<'PHP'
                <?php
                $result = $value ?? throw new \RuntimeException(message: 'error', code: 500);
                PHP,
        ];

        yield 'inline with square bracket - adds line break' => [
            <<<'PHP'
                <?php
                $foo = [
                    'a',
                    'b',
                ][$index]
                ?? throw new \RuntimeException('message');
                PHP,
            <<<'PHP'
                <?php
                $foo = [
                    'a',
                    'b',
                ][$index] ?? throw new \RuntimeException('message');
                PHP,
        ];

        yield 'inside match arm' => [
            <<<'PHP'
                <?php
                $result = match ($type) {
                    'user' => $this->findUser($id)
                        ?? throw new \RuntimeException('User not found'),
                    'order' => $this->findOrder($id)
                        ?? throw new \RuntimeException('Order not found'),
                };
                PHP,
            <<<'PHP'
                <?php
                $result = match ($type) {
                    'user' => $this->findUser($id) ?? throw new \RuntimeException('User not found'),
                    'order' => $this->findOrder($id) ?? throw new \RuntimeException('Order not found'),
                };
                PHP,
        ];

        yield 'arrow function with throw expression' => [
            <<<'PHP'
                <?php
                $fn = fn($x) => $x
                    ?? throw new \RuntimeException('message');
                PHP,
            <<<'PHP'
                <?php
                $fn = fn($x) => $x ?? throw new \RuntimeException('message');
                PHP,
        ];

        yield 'static property access' => [
            <<<'PHP'
                <?php
                $result = self::$instance
                    ?? throw new \RuntimeException('Not initialized');
                PHP,
            <<<'PHP'
                <?php
                $result = self::$instance ?? throw new \RuntimeException('Not initialized');
                PHP,
        ];

        yield 'spread operator in exception constructor' => [
            <<<'PHP'
                <?php
                $result = $value
                    ?? throw new \RuntimeException(...$args);
                PHP,
            <<<'PHP'
                <?php
                $result = $value ?? throw new \RuntimeException(...$args);
                PHP,
        ];

        yield 'multiline exception constructor - adds line break' => [
            <<<'PHP'
                <?php
                $result = $value
                    ?? throw new \RuntimeException(
                        'A very long error message that spans multiple lines',
                        500,
                    );
                PHP,
            <<<'PHP'
                <?php
                $result = $value ?? throw new \RuntimeException(
                    'A very long error message that spans multiple lines',
                    500,
                );
                PHP,
        ];

        yield 'comment between value and operator - preserves comment' => [
            <<<'PHP'
                <?php
                $result = $value /* fallback if null */
                    ?? throw new \RuntimeException('message');
                PHP,
            <<<'PHP'
                <?php
                $result = $value /* fallback if null */ ?? throw new \RuntimeException('message');
                PHP,
        ];

        yield 'inside closure' => [
            <<<'PHP'
                <?php
                $closure = function ($id) {
                    return $this->find($id)
                        ?? throw new \RuntimeException('Not found');
                };
                PHP,
            <<<'PHP'
                <?php
                $closure = function ($id) {
                    return $this->find($id) ?? throw new \RuntimeException('Not found');
                };
                PHP,
        ];

        yield 'property access with variable property name' => [
            <<<'PHP'
                <?php
                $result = $obj->$property
                    ?? throw new \RuntimeException('Property not found');
                PHP,
            <<<'PHP'
                <?php
                $result = $obj->$property ?? throw new \RuntimeException('Property not found');
                PHP,
        ];

        yield 'deeply nested class' => [
            <<<'PHP'
                <?php
                class Outer
                {
                    public function test(): void
                    {
                        $closure = function () {
                            $fn = fn($x) => $x
                                ?? throw new \RuntimeException('message');
                        };
                    }
                }
                PHP,
            <<<'PHP'
                <?php
                class Outer
                {
                    public function test(): void
                    {
                        $closure = function () {
                            $fn = fn($x) => $x ?? throw new \RuntimeException('message');
                        };
                    }
                }
                PHP,
        ];
    }
}
