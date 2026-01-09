<?php declare(strict_types=1);

namespace MLL\PhpCsFixerConfig\Tests\Fixer;

use MLL\PhpCsFixerConfig\Fixer\PhpdocSimplifyArrayKeyFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PhpdocSimplifyArrayKeyFixerTest extends TestCase
{
    private PhpdocSimplifyArrayKeyFixer $fixer;

    protected function setUp(): void
    {
        $this->fixer = new PhpdocSimplifyArrayKeyFixer();
    }

    #[DataProvider('provideFixCases')]
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
        yield 'simplifies array-key to omit key type' => [
            '<?php
/**
 * @param array<string> $x
 */
',
            '<?php
/**
 * @param array<array-key, string> $x
 */
',
        ];

        yield 'simplifies int|string to omit key type' => [
            '<?php
/**
 * @return array<Foo>
 */
',
            '<?php
/**
 * @return array<int|string, Foo>
 */
',
        ];

        yield 'simplifies string|int to omit key type' => [
            '<?php
/**
 * @param array<Bar> $y
 */
',
            '<?php
/**
 * @param array<string|int, Bar> $y
 */
',
        ];

        yield 'handles multiple annotations in one docblock' => [
            '<?php
/**
 * @param array<string> $x
 * @param array<int> $y
 * @return array<Foo>
 */
',
            '<?php
/**
 * @param array<array-key, string> $x
 * @param array<array-key, int> $y
 * @return array<int|string, Foo>
 */
',
        ];

        yield 'handles nested array types' => [
            '<?php
/**
 * @param array<array<string>> $x
 */
',
            '<?php
/**
 * @param array<array-key, array<string>> $x
 */
',
        ];

        yield 'handles whitespace variations' => [
            '<?php
/**
 * @param array<string> $x
 */
',
            '<?php
/**
 * @param array<  array-key  ,  string> $x
 */
',
        ];

        yield 'leaves already simplified array unchanged' => [
            '<?php
/**
 * @param array<string> $x
 */
',
        ];

        yield 'leaves non-array types unchanged' => [
            '<?php
/**
 * @param string $x
 * @param int $y
 */
',
        ];

        yield 'leaves specific int key unchanged' => [
            '<?php
/**
 * @param array<int, string> $x
 */
',
        ];

        yield 'leaves specific string key unchanged' => [
            '<?php
/**
 * @param array<string, Foo> $x
 */
',
        ];

        yield 'leaves code without docblock unchanged' => [
            '<?php
function foo() {}
',
        ];

        yield 'handles @var annotation' => [
            '<?php
/** @var array<string> $x */
',
            '<?php
/** @var array<array-key, string> $x */
',
        ];

        yield 'handles @property annotation' => [
            '<?php
/**
 * @property array<Foo> $items
 */
',
            '<?php
/**
 * @property array<array-key, Foo> $items
 */
',
        ];

        // Note: AbstractPhpdocTypesFixer requires transformations to preserve line count.
        // Multiline types that would require removing a line cannot be transformed.
        // This is a known limitation of the php-cs-fixer framework.

        yield 'leaves multiline array-key unchanged (line count would change)' => [
            '<?php
/**
 * @param array<
 *     array-key,
 *     string
 * > $x
 */
',
        ];

        yield 'leaves multiline int|string unchanged (line count would change)' => [
            '<?php
/**
 * @param array<
 *     int|string,
 *     Foo
 * > $items
 */
',
        ];

        yield 'leaves multiline with specific key unchanged' => [
            '<?php
/**
 * @param array<
 *     int,
 *     string
 * > $x
 */
',
        ];

        yield 'simplifies single-line type within multiline docblock' => [
            '<?php
/**
 * A function with a long description
 * that spans multiple lines.
 *
 * @param array<string> $x The input array
 * @return void
 */
',
            '<?php
/**
 * A function with a long description
 * that spans multiple lines.
 *
 * @param array<array-key, string> $x The input array
 * @return void
 */
',
        ];
    }
}
