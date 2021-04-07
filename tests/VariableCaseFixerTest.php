<?php declare(strict_types=1);

namespace MLL\PhpCsFixerConfig\Tests;

use MLL\PhpCsFixerConfig\VariableCaseFixer;
use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

final class VariableCaseFixerTest extends AbstractFixerTestCase
{
    protected function createFixer(): VariableCaseFixer
    {
        return new VariableCaseFixer();
    }

    /**
     * @dataProvider provideCamelCaseFixCases
     */
    public function testCamelCaseFix(string $expected, string $input = null): void
    {
        $this->doTest($expected, $input);
    }

    /**
     * @return array<int, array{0: string, 0?: string}>
     */
    public function provideCamelCaseFixCases(): array
    {
        return [
            [
                '<?php $testVariable = 2;',
                '<?php $test_variable = 2;',
            ],
            [
                '<?php $_ = 2;',
            ],
            [
                '<?php var_dump($_ENV);',
            ],
            [
                '<?php $testVariable = 2; echo "hi $testVariable!";',
                '<?php $test_variable = 2; echo "hi $test_variable!";',
            ],
            [
                '<?php $testVariable = 2; echo "hi ${testVariable}!";',
                '<?php $test_variable = 2; echo "hi ${test_variable}!";',
            ],
            [
                '<?php $testVariable = 2; echo "hi {$testVariable}!";',
                '<?php $test_variable = 2; echo "hi {$test_variable}!";',
            ],
            [
                '<?php function foo_bar() { $testVariable = 2;}',
                '<?php function foo_bar() { $test__variable = 2;}',
            ],
            [
                '<?php echo $testModel->this_field;',
                '<?php echo $test_model->this_field;',
            ],
            [
                '<?php function f($barBaz, $file) { require $file;}',
                '<?php function f($bar_baz, $file) { require $file;}',
            ],
            [
                '<?php class F { public $fooBar = 1; public function test() { $this->fooBar = 0; } } ?>',
                '<?php class F { public $foo_bar = 1; public function test() { $this->foo_bar = 0; } } ?>',
            ],
        ];
    }

    /**
     * @dataProvider provideSnakeCaseFixCases
     */
    public function testSnakeCaseFix(string $expected, string $input = null): void
    {
        $this->fixer->configure(['case' => VariableCaseFixer::SNAKE_CASE]);
        $this->doTest($expected, $input);
    }

    /**
     * @return array<int, array{0: string, 0?: string}>
     */
    public function provideSnakeCaseFixCases(): array
    {
        return [
            [
                '<?php $test_variable = 2;',
                '<?php $testVariable = 2;',
            ],
            [
                '<?php $abc_12_variable = 2;',
                '<?php $abc12Variable = 2;',
            ],
            [
                '<?php $abc_123_a_variable = 2;',
                '<?php $abc123aVariable = 2;',
            ],
            [
                '<?php function fooBar() { $test_variable = 2;}',
                '<?php function fooBar() { $testVariable = 2;}',
            ],
            [
                '<?php $test_variable = 2; echo "hi $test_variable!";',
                '<?php $testVariable = 2; echo "hi $testVariable!";',
            ],
            [
                '<?php $test_variable = 2; echo "hi ${test_variable}!";',
                '<?php $testVariable = 2; echo "hi ${testVariable}!";',
            ],
            [
                '<?php $test_variable = 2; echo "hi {$test_variable}!";',
                '<?php $testVariable = 2; echo "hi {$testVariable}!";',
            ],
            [
                '<?php echo $test_model->this_field;',
                '<?php echo $testModel->this_field;',
            ],
            [
                '<?php function f($bar_baz, $file) { require $file;}',
                '<?php function f($barBaz, $file) { require $file;}',
            ],
        ];
    }
}
