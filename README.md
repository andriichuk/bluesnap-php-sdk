# BlueSnap PHP SDK

A framework-agnostic PHP 8.5 SDK for the [BlueSnap Payment API](https://developers.bluesnap.com/v8976-JSON/reference/bluesnap-payment-api-json).

> This project is under active development. Its public API may change before the first stable release.

## Requirements

- PHP 8.5 or newer
- A [PSR-18 HTTP client](https://www.php-fig.org/psr/psr-18/)
- PSR-17 request and stream factories
- BlueSnap sandbox or production API credentials

The SDK deliberately does not depend on Laravel, Symfony, Guzzle, or any other framework. Applications provide their preferred PSR implementations.

## Installation

```bash
composer require andriichuk/bluesnap-php-sdk
```

Install a PSR-18 implementation if your project does not already contain one. For example:

```bash
composer require guzzlehttp/guzzle
```

## Creating a client

```php
use Andriichuk\BlueSnap\BlueSnapClient;
use Andriichuk\BlueSnap\Configuration;
use Andriichuk\BlueSnap\Environment;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

$httpClient = new Client();
$httpFactory = new HttpFactory();

$blueSnap = new BlueSnapClient(
    new Configuration(
        username: $_ENV['BLUESNAP_USERNAME'],
        password: $_ENV['BLUESNAP_PASSWORD'],
        merchantId: $_ENV['BLUESNAP_MERCHANT_ID'],
        environment: Environment::Sandbox,
    ),
    httpClient: $httpClient,
    requestFactory: $httpFactory,
    streamFactory: $httpFactory,
);
```

Never expose BlueSnap API credentials in client-side code or commit them to source control.

`merchantId` is required when building Hosted Payment Page URLs. API-only clients may omit it.

## Plans

```php
$response = $blueSnap->plans()->create([
    'name' => 'Pro Monthly',
    'currency' => 'USD',
    'chargeFrequency' => 'MONTHLY',
    'recurringChargeAmount' => '29.99',
    'trialPeriodDays' => 14,
], idempotencyKey: '5b68d40b-24ad-4fdd-99e9-a0386a35c5c0');

$plan = $blueSnap->plans()->retrieve($response->json()['planId'])->json();

$activePlans = $blueSnap->plans()->all([
    'status' => 'ACTIVE',
    'pagesize' => 50,
    'gettotal' => true,
])->json();
```

## Subscriptions

The payload shape follows BlueSnap's API so new BlueSnap capabilities remain available without waiting for an SDK release.

```php
$response = $blueSnap->subscriptions()->create([
    'planId' => 2283845,
    'vaultedShopperId' => 21188039,
    'quantity' => 1,
], idempotencyKey: 'e341e759-51fd-4629-8881-b0f560e01311');

$subscriptionId = $response->json()['subscriptionId'];

$blueSnap->subscriptions()->update($subscriptionId, [
    'planId' => 2283849,
]);

// Cancel immediately.
$blueSnap->subscriptions()->cancel($subscriptionId);

// Or allow the current billing period to finish.
$blueSnap->subscriptions()->cancelAtPeriodEnd($subscriptionId);
```

## Hosted Payment Fields tokens

```php
$response = $blueSnap->paymentFieldsTokens()->create([
    'authenticationrequired3ds' => true,
]);

$tokenUrl = $response->location();
```

BlueSnap returns the token in the response's `Location` header. Extract the final path segment only when the Hosted Payment Fields JavaScript integration requires the token value.

For saved-card 3-D Secure flows, use `paymentFieldsTokens()->prefill()`.

## Hosted Payment Pages

Hosted Payment Page URLs use the checkout host rather than the API base URI. The SDK defaults to
`https://sandbox.bluesnap.com` in sandbox and `https://checkout.bluesnap.com` in production; pass
`checkoutHost` to `Configuration` to override that origin.

Use `paramEncryption()->encrypt()` to encrypt protected parameters such as
`thankyou.backtosellerurl`, then build the redirect URL with `HostedPageUrl`. Parameter encryption
requires a Data Protection Key configured in the BlueSnap merchant console.

## Cashier readiness

The SDK includes the outbound API operations required for a card-based Laravel Cashier package: customers and payment methods, plans, BlueSnap-managed and merchant-managed subscriptions, charge history, plan-switch previews, one-time payments, authorization/capture/reversal, refunds, 3-D Secure token prefill, webhook configuration, and reconciliation queries.

See the [Cashier endpoint coverage matrix](docs/CASHIER_ENDPOINT_COVERAGE.md) for the exact mapping and the integration responsibilities that belong in the future Laravel package.

## Errors

Non-successful responses throw typed exceptions:

```php
use Andriichuk\BlueSnap\Exception\RateLimitException;
use Andriichuk\BlueSnap\Exception\ValidationException;

try {
    $blueSnap->plans()->create($payload, $idempotencyKey);
} catch (ValidationException $exception) {
    foreach ($exception->errors as $error) {
        // $error->code, $error->name, $error->description
    }
} catch (RateLimitException $exception) {
    // Retry later using the same idempotency key.
}
```

HTTP 401/403, 404, 409, 422, and 429 responses have dedicated exception classes. Other unsuccessful responses throw `ApiException`. PSR transport failures throw `TransportException`.

## Idempotency

BlueSnap accepts idempotency keys on transactional POST endpoints and retains them for up to 24 hours. Generate the key in your application and reuse the same value only when retrying the same logical request. Keys are limited to 64 characters.

## Development

```bash
composer install
composer check
```

The test suite does not make live BlueSnap requests or require API credentials.

## Security

Please do not report security vulnerabilities through public GitHub issues. Until a dedicated security policy is published, contact `andriichuk29@gmail.com`.

## License

BlueSnap PHP SDK is open-source software licensed under the [MIT license](LICENSE.md).
