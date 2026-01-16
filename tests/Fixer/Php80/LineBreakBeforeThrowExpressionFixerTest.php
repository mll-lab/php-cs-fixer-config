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

        // Test idempotency
        $tokens = Tokens::fromCode($expected);
        $this->fixer->fix(new \SplFileInfo(__FILE__), $tokens);
        self::assertSame($expected, $tokens->generateCode());
    }

    /** @return iterable<string, array{0: string, 1?: string}> */
    public static function provideFixCases(): iterable
    {
        yield 'null coalesce throw on single line' => [
            '<?php
$result = $this->fetchNullable()
    ?? throw new \RuntimeException(\'message\');
',
            '<?php
$result = $this->fetchNullable() ?? throw new \RuntimeException(\'message\');
',
        ];

        yield 'elvis throw on single line' => [
            '<?php
$result = $this->fetchFalsy()
    ?: throw new \RuntimeException(\'message\');
',
            '<?php
$result = $this->fetchFalsy() ?: throw new \RuntimeException(\'message\');
',
        ];

        yield 'already multiline null coalesce throw - no change' => [
            '<?php
$result = $this->fetchNullable()
    ?? throw new \RuntimeException(\'message\');
',
        ];

        yield 'already multiline elvis throw - no change' => [
            '<?php
$result = $this->fetchFalsy()
    ?: throw new \RuntimeException(\'message\');
',
        ];

        yield 'regular null coalesce without throw - no change' => [
            '<?php
$result = $this->fetchNullable() ?? \'default\';
',
        ];

        yield 'regular elvis without throw - no change' => [
            '<?php
$result = $this->fetchFalsy() ?: \'default\';
',
        ];

        yield 'exception with arguments' => [
            '<?php
$user = $this->findUser($id)
    ?? throw new \InvalidArgumentException(sprintf(\'User %d not found\', $id));
',
            '<?php
$user = $this->findUser($id) ?? throw new \InvalidArgumentException(sprintf(\'User %d not found\', $id));
',
        ];

        yield 'method chain' => [
            '<?php
$result = $this->getRepository()->find($id)
    ?? throw new \RuntimeException(\'Not found\');
',
            '<?php
$result = $this->getRepository()->find($id) ?? throw new \RuntimeException(\'Not found\');
',
        ];

        yield 'return statement with null coalesce throw' => [
            '<?php
return $this->cache->get($key)
    ?? throw new \RuntimeException(\'Cache miss\');
',
            '<?php
return $this->cache->get($key) ?? throw new \RuntimeException(\'Cache miss\');
',
        ];

        yield 'nested in method' => [
            '<?php
class Foo
{
    public function bar(): string
    {
        $result = $this->fetchNullable()
            ?? throw new \RuntimeException(\'message\');

        return $result;
    }
}
',
            '<?php
class Foo
{
    public function bar(): string
    {
        $result = $this->fetchNullable() ?? throw new \RuntimeException(\'message\');

        return $result;
    }
}
',
        ];

        yield 'multiple statements' => [
            '<?php
$a = $foo
    ?? throw new \RuntimeException(\'a\');
$b = $bar
    ?: throw new \RuntimeException(\'b\');
',
            '<?php
$a = $foo ?? throw new \RuntimeException(\'a\');
$b = $bar ?: throw new \RuntimeException(\'b\');
',
        ];

        yield 'regular throw statement - no change' => [
            '<?php
if ($condition) {
    throw new \RuntimeException(\'message\');
}
',
        ];

        yield 'ternary operator - no change' => [
            '<?php
$result = $condition ? $a : $b;
',
        ];

        yield 'no whitespace before operator' => [
            '<?php
$result = $this->fetchNullable()
    ?? throw new \RuntimeException(\'message\');
',
            '<?php
$result = $this->fetchNullable()?? throw new \RuntimeException(\'message\');
',
        ];
    }
}
