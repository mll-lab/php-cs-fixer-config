<?php declare(strict_types=1);

use PhpCsFixer\Finder;
use function MLL\PhpCsFixerConfig\risky;

$finder = Finder::create()
    ->notPath('vendor')
    ->in(__DIR__)
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return risky($finder);
