# WETU Importer PHPUnit Configuration

This directory contains PHPUnit tests for the WETU Importer plugin.

## Running Tests

```bash
# Run all PHP unit tests
composer test

# Run tests with coverage
composer test:coverage
```

## Writing Tests

Follow WordPress plugin testing best practices:
- Test classes should extend `WP_UnitTestCase`
- Use meaningful test method names prefixed with `test_`
- Mock external API calls to WETU services
- Test both success and failure scenarios

## Coverage Reports

Code coverage reports are generated using PHPUnit with XDebug.
Reports are saved to `/coverage/` directory.
