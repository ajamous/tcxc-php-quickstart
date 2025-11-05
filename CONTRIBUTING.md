# Contributing to TCXC PHP SDK

Thank you for considering contributing to the TCXC PHP SDK! We welcome contributions from the community.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and collaborative environment.

## How to Contribute

### Reporting Bugs

1. Check if the bug has already been reported in [Issues](https://github.com/yourusername/tcxc-php-quickstart/issues)
2. If not, create a new issue with:
   - Clear title and description
   - Steps to reproduce
   - Expected vs actual behavior
   - PHP version and environment details
   - Code samples if applicable

### Suggesting Enhancements

1. Open an issue with the `enhancement` label
2. Provide a clear description of the feature
3. Explain the use case and benefits
4. Include code examples if applicable

### Pull Requests

1. **Fork the repository** and create your branch from `main`
2. **Make your changes** following our coding standards
3. **Add tests** for any new functionality
4. **Update documentation** if needed
5. **Run quality checks**:
   ```bash
   composer quality
   ```
6. **Commit your changes** with clear, descriptive messages
7. **Push to your fork** and submit a pull request

## Development Setup

### Prerequisites

- PHP 7.4 or higher
- Composer
- Docker (optional)

### Installation

```bash
# Clone your fork
git clone https://github.com/your-username/tcxc-php-quickstart.git
cd tcxc-php-quickstart

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Edit .env with your test credentials
```

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Run specific test file
vendor/bin/phpunit tests/Unit/Model/CallLegTest.php
```

### Code Quality

```bash
# Run static analysis
composer analyse

# Check code style
composer cs:check

# Fix code style
composer cs:fix

# Run all quality checks
composer quality
```

## Coding Standards

### PHP Standards

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style
- Use strict types: `declare(strict_types=1);`
- Add type hints to all parameters and return types
- Use meaningful variable and method names

### Documentation

- Add PHPDoc blocks to all classes, methods, and properties
- Include `@param`, `@return`, and `@throws` tags
- Provide clear descriptions and examples
- Update README.md for new features

### Testing

- Write unit tests for all new code
- Aim for 80%+ code coverage
- Use descriptive test method names
- Follow the Arrange-Act-Assert pattern
- Mock external dependencies

### Example Test

```php
public function testCanCreateCallLeg(): void
{
    // Arrange
    $destination = '19542405555';
    $callerId = '18009999999';
    $connectionId = 220;

    // Act
    $leg = new CallLeg($destination, $callerId, $connectionId);

    // Assert
    $this->assertSame($destination, $leg->getDestination());
    $this->assertSame($callerId, $leg->getCallerId());
    $this->assertSame($connectionId, $leg->getConnectionId());
}
```

### Git Commit Messages

- Use present tense ("Add feature" not "Added feature")
- Use imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit first line to 72 characters
- Reference issues and PRs when applicable

Example:
```
Add validation for phone number format

- Implement E.164 phone number validation
- Add unit tests for validation logic
- Update documentation with phone format requirements

Fixes #123
```

## Architecture Guidelines

### SOLID Principles

- **Single Responsibility**: Each class should have one reason to change
- **Open/Closed**: Open for extension, closed for modification
- **Liskov Substitution**: Subtypes must be substitutable for base types
- **Interface Segregation**: Many specific interfaces over one general
- **Dependency Inversion**: Depend on abstractions, not concretions

### Design Patterns

We use these design patterns throughout the codebase:

- **Factory Pattern**: For creating complex objects
- **Builder Pattern**: For fluent interfaces
- **Repository Pattern**: For data access
- **Dependency Injection**: For loose coupling
- **Exception Hierarchy**: For error handling

### Adding New Features

When adding new features:

1. **Create an interface** if the feature involves external dependencies
2. **Implement the interface** with concrete classes
3. **Add configuration** if needed
4. **Write comprehensive tests**
5. **Document the feature** in README.md
6. **Add usage examples** in the `public/` directory

## Directory Structure

```
tcxc-php-quickstart/
├── src/
│   ├── Client/         # API client implementations
│   ├── Config/         # Configuration classes
│   ├── Exception/      # Custom exceptions
│   └── Model/          # Data models
├── tests/
│   ├── Unit/           # Unit tests
│   └── Integration/    # Integration tests
├── public/             # Example files
└── config/             # Configuration files
```

## Pull Request Process

1. **Update the README.md** with details of changes if applicable
2. **Update the CHANGELOG.md** with your changes
3. **Ensure all tests pass** and code quality checks succeed
4. **Request review** from maintainers
5. **Address review comments** promptly
6. **Squash commits** if requested

## Release Process

Maintainers will handle releases:

1. Update version in `composer.json`
2. Update CHANGELOG.md
3. Create a git tag
4. Publish to Packagist

## Questions?

Feel free to open an issue with the `question` label or contact the maintainers.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to make TCXC PHP SDK better!
