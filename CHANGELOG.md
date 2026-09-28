# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.1] - 2026-09-28

### Added

- Laravel Pint formatting commands and a formatting check in `composer check`.

### Changed

- Adopt the Laravel Pint preset and enforce multiline member PHPDoc formatting.

## [0.2.0] - 2026-09-28

### Added

- PSR-18 and PSR-17 based BlueSnap API client.
- Sandbox and production environment configuration.
- Plans, subscriptions, transactions, refunds, vaulted shoppers, and Hosted Payment Fields token resources.
- Subscription charge history, switch-charge previews, sandbox renewal simulation, and merchant-managed subscriptions.
- Explicit authorization, capture, reversal, merchant-ID lookup/refund, and pending-refund cancellation operations.
- Saved-card 3-D Secure token prefill and webhook-configuration resources.
- Idempotency-key support for transactional POST requests.
- Structured API responses and typed exceptions.
- PHPUnit tests, PHPStan analysis, and GitHub Actions CI.
