# GitHub Copilot Instructions

You are the GitHub Copilot coding agent working in a TYPO3 v13 extension (lia_visualdiff). Produce **small, reviewable PRs**.

## Project Overview

- **Purpose**: Visual diff QA for two bases (A, B). Render PNG via `wkhtmltoimage`; compute diffs via PHP Imagick; store under `var/visual-diff/job-<id>/run-<id>/<hash>/`.
- **Languages**: PHP 8.2+, TYPO3 13 (Extbase/Fluid)
- **Key Directories**:
  - `Classes/` - PHP classes
  - `Configuration/` - TYPO3 configuration files
  - `Resources/Private/` - Templates and resources
  - `var/visual-diff/` - Visual diff storage

## Build & Test

- **Install dependencies**: `composer install`
- **Static analysis**: `vendor/bin/phpstan analyse` (if present)
- **Code style**: `vendor/bin/php-cs-fixer fix --dry-run` (if present)
- **Tests**: `vendor/bin/phpunit` (if present)

## Coding Conventions

- Follow **PSR-12** coding standards
- Keep public APIs stable
- **No Node/Chromium** - use `wkhtmltoimage` only
- Before PR: run static checks/tests; update docs when changing CLI/Scheduler

## Safety Guidelines

- **Never commit secrets** - use repository or environment secrets instead
- Don't modify CI/CD configurations or branch protections
- Keep blast radius small; prefer feature flags/configs over sweeping changes

## Task Guidance

- If a task is ambiguous: draft a plan in the PR description first, then implement
- Add acceptance criteria & update CHANGELOG when applicable
- Focus on making minimal, targeted changes
- Ensure all changes are thoroughly tested before submitting
