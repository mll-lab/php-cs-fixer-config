<?php declare(strict_types=1);

namespace MLL\PhpCsFixerConfig\Tests\Fixer;

use MLL\PhpCsFixerConfig\Fixer\BlankLineAfterTraitImportsFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

final class BlankLineAfterTraitImportsFixerTest extends TestCase
{
    /** @var BlankLineAfterTraitImportsFixer */
    private $fixer;

    protected function setUp(): void
    {
        $this->fixer = new BlankLineAfterTraitImportsFixer();
    }

    /** @dataProvider provideFixCases */
    public function testFix(string $expected, ?string $input = null): void
    {
        $input ??= $expected;

        $tokens = Tokens::fromCode($input);
        $this->fixer->fix(new \SplFileInfo(__FILE__), $tokens);
        self::assertSame($expected, $tokens->generateCode());

        $tokens = Tokens::fromCode($expected);
        $this->fixer->fix(new \SplFileInfo(__FILE__), $tokens);
        self::assertSame($expected, $tokens->generateCode());
    }

    /** @return iterable<string, array{0: string, 1?: string}> */
    public static function provideFixCases(): iterable
    {
        yield 'constant after trait imports' => [
            <<<'PHP'
                <?php
                final class Foo
                {
                    use Bar;
                    use Baz;

                    public const QUX = 1;
                }
                PHP,
            <<<'PHP'
                <?php
                final class Foo
                {
                    use Bar;
                    use Baz;
                    public const QUX = 1;
                }
                PHP,
        ];

        yield 'docblock after trait import' => [
            <<<'PHP'
                <?php
                final class Foo
                {
                    use Bar;

                    /** @var int */
                    private $qux;
                }
                PHP,
            <<<'PHP'
                <?php
                final class Foo
                {
                    use Bar;
                    /** @var int */
                    private $qux;
                }
                PHP,
        ];

        yield 'conflict resolution block' => [
            <<<'PHP'
                <?php
                final class Foo
                {
                    use Bar, Baz {
                        Bar::qux insteadof Baz;
                    }

                    public function quux(): void {}
                }
                PHP,
            <<<'PHP'
                <?php
                final class Foo
                {
                    use Bar, Baz {
                        Bar::qux insteadof Baz;
                    }
                    public function quux(): void {}
                }
                PHP,
        ];

        yield 'trait importing a trait' => [
            <<<'PHP'
                <?php
                trait Foo
                {
                    use Bar;

                    public $qux;
                }
                PHP,
            <<<'PHP'
                <?php
                trait Foo
                {
                    use Bar;
                    public $qux;
                }
                PHP,
        ];

        yield 'anonymous class' => [
            <<<'PHP'
                <?php
                $foo = new class {
                    use Bar;

                    public $qux;
                };
                PHP,
            <<<'PHP'
                <?php
                $foo = new class {
                    use Bar;
                    public $qux;
                };
                PHP,
        ];

        yield 'class closes after trait imports' => [
            <<<'PHP'
                <?php
                final class Foo
                {
                    use Bar;
                    use Baz;
                }
                PHP,
        ];

        yield 'blank line present' => [
            <<<'PHP'
                <?php
                final class Foo
                {
                    use Bar;

                    public const QUX = 1;
                }
                PHP,
        ];

        yield 'comment on the same line' => [
            <<<'PHP'
                <?php
                final class Foo
                {
                    use Bar; // Provides qux
                    public const QUX = 1;
                }
                PHP,
        ];

        yield 'namespace imports and closure use' => [
            <<<'PHP'
                <?php
                namespace Foo;
                use Bar;
                use Baz;
                $qux = function () use ($bar) {
                    return $bar;
                };
                PHP,
        ];
    }

    /** @requires PHP 8.1 */
    public function testFixesEnumCaseAfterTraitImport(): void
    {
        $tokens = Tokens::fromCode(<<<'PHP'
            <?php
            enum Foo
            {
                use Bar;
                case Baz;
            }
            PHP);
        $this->fixer->fix(new \SplFileInfo(__FILE__), $tokens);

        self::assertSame(<<<'PHP'
            <?php
            enum Foo
            {
                use Bar;

                case Baz;
            }
            PHP, $tokens->generateCode());
    }
}
