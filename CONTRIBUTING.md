# Contributing

Contributions are welcome. Please open an issue before starting a substantial change so the proposed API can be discussed first.

## Development setup

The project requires PHP 8.5 and Composer 2.

```bash
composer install
composer format
composer check
```

Pull requests should include tests for new behavior and must pass Laravel Pint, PHPUnit, and PHPStan at the maximum level.

Do not include BlueSnap credentials, card data, Hosted Payment Fields tokens, or production API responses in issues, fixtures, logs, or commits.
