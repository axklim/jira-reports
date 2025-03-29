# CLAUDE.md - Guide for Agentic Coding Assistants

## Project Overview
A PHP utility for generating Jira reports using the Jira REST API.

## Commands
- Run script: `php run.php`
- Install dependencies: `composer install`
- Update dependencies: `composer update`
- PHP linting: `php -l file.php`
- PHP code sniffer (if installed): `./vendor/bin/phpcs src/`

## Code Style Guidelines
- Follow PSR-4 autoloading standard
- Use 4-space indentation, no tabs
- Use camelCase for variables and methods
- Use PascalCase for class names
- Namespace all classes under `JiraReport\`
- Error handling: Use try/catch blocks for external API calls
- Include proper API error response handling
- Complete PHP docblocks for all classes and methods
- Explicit type declarations for function parameters and returns
- Secure API credentials with environment variables rather than hardcoding