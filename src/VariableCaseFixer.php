<?php declare(strict_types=1);

namespace MLL\PhpCsFixerConfig;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class VariableCaseFixer extends AbstractFixer implements ConfigurationDefinitionFixerInterface
{
    public function getName(): string
    {
        return 'MLL/variable_case';
    }

    const CAMEL_CASE = 'camel_case';

    const SNAKE_CASE = 'snake_case';

    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition(
            'Enforce camel (or snake) case for variable names, following configuration.',
            [
                new CodeSample("<?php \$my_variable = 2;\n"),
                new CodeSample("<?php \$myVariable = 2;\n", ['case' => self::SNAKE_CASE]),
            ],
            null,
            'Risky because it cannot detect a change that will have an impact in other files.'
        );
    }

    public function isRisky(): bool
    {
        return true;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([T_VARIABLE, T_STRING_VARNAME]);
    }

    protected function createConfigurationDefinition(): FixerConfigurationResolver
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('case', 'Apply `camel_case` or `snake_case` to variables.'))
                ->setAllowedValues([self::CAMEL_CASE, self::SNAKE_CASE])
                ->setDefault(self::CAMEL_CASE)
                ->getOption(),
        ]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if ((T_VARIABLE === $token->getId()) || (T_STRING_VARNAME === $token->getId())) {
                $tokens[$index] = new Token([$token->getId(), $this->updateVariableCasing($token->getContent())]);
            }
        }
    }

    private function updateVariableCasing(string $variableName): string
    {
        if (self::CAMEL_CASE === $this->configuration['case']) {
            return $this->camelCase($variableName);
        }

        return $this->snakeCase($variableName);
    }

    private function camelCase(string $string): string
    {
        // Preserve leading underscores
        $lead = [];
        Preg::match('/^\$?_*/', $string, $lead);

        $string = Preg::replace('/[$_]/i', ' ', $string);
        $string = trim($string);
        // uppercase the first character of each word
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);

        return $lead[0] . lcfirst($string);
    }

    private function snakeCase(string $string, string $separator = '_'): string
    {
        // insert separator between any letter and the beginning of a numeric chain
        $string = Preg::replace('/([a-z]+)([0-9]+)/i', '$1' . $separator . '$2', $string);
        // insert separator between any lower-to-upper-case letter chain
        $string = Preg::replace('/([a-z]+)([A-Z]+)/', '$1' . $separator . '$2', $string);
        // insert separator between the end of a numeric chain and the beginning of an alpha chain
        $string = Preg::replace('/([0-9]+)([a-z]+)/i', '$1' . $separator . '$2', $string);

        // Lowercase
        return strtolower($string);
    }
}
