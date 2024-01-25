# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

You can find and compare releases at the [GitHub release page](https://github.com/mll-lab/php-cs-fixer-config/releases).

## Unreleased

## v5.6.0

### Added

- Force `T?` over `T|null`

## v5.5.0

### Added

- Disable `fully_qualified_strict_types` fixer

## v5.4.0

### Added

- Enable `single_line_empty_body` fixer

## v5.3.0

### Changed

- Enforce non-Yoda style

## v5.2.0

### Changed

- Prefer single-line PHPDocs

## v5.1.0

### Added

- Add `ConstructorEmptyBracesFixer`

## v5.0.0

### Added

- Add `DeclareAfterOpeningTagFixer` from `kubawerlos/php-cs-fixer-custom-fixers`

### Changed

- Require PHP 7.4

## v4.5.0

### Added

- Add rule `explicit_string_variable`

## v4.4.1

### Fixed

- Disable `heredoc_indentation`

## v4.4.0

### Changed

- Always have single space around binary operators

## v4.3.0

### Changed

- Downgrade the required PHP version to `^7.2`

## v4.2.0

### Added

- Support PHP 8

## v4.1.0

### Changed

- No alignment in PHPDocs to preserve complex array shapes
- Allow intermediary PHPDocs to provide type assertions for PHPStan

## v4.0.0

### Changed

- Upgrade to `friendsofphp/php-cs-fixer:^3`

### Removed

- Remove custom fixer `MLL/variable_case`

## v3.0.1

### Changed

- Disable custom fixer `MLL/variable_case` by default

## v3.0.0

### Added

- Add custom fixer `MLL/variable_case`

### Changed

- Change namespace to `MLL\PhpCsFixerConfig`

## v2.1.0

### Changed

- Add declare in line with PHP opening tag

## v2.0.1

### Fixed

- Readd custom `phpdoc_no_alias_tag`

## v2.0.0

### Changed

- Make risky rules optional

## v1.2.1

### Changed

- Require `friendsofphp/php-cs-fixer`

## v1.2.0

### Added

- Enable rule `array_indentation`

## v1.1.0

### Added

- Enable rule `logical_operators`

## v1.0.0

### Added

- Add `MLL\PhpCsFixerRules\config()`
