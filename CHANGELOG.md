# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-01-01

### Added
- Initial release of TYPO3 Visual Diff extension
- Scheduler task for automated visual comparison
- Support for comparing two bases (A and B)
- Image rendering via wkhtmltoimage
- Image comparison using PHP Imagick or GD as fallback
- Storage management under var/visual-diff/job-*
- Issues-only reporting (shows only pages with differences)
- Configurable difference threshold
- Comprehensive documentation and examples
- Support for TYPO3 v13
- Support for PHP 8.1+

### Features
- **VisualDiffTask**: Scheduler task with configuration interface
- **VisualDiffService**: Core service for job orchestration
- **ImageRenderer**: Web page to PNG rendering using wkhtmltoimage
- **ImageComparator**: Pixel-based image comparison with diff generation
- **StorageUtility**: File and job management
- **Job Model**: Domain model for tracking comparisons

### Requirements
- TYPO3 v13.0 or later
- PHP 8.1 or later
- wkhtmltoimage binary
- PHP Imagick extension (recommended) or GD extension

[1.0.0]: https://github.com/priebera1/typo3-visual-diff-qa/releases/tag/v1.0.0
