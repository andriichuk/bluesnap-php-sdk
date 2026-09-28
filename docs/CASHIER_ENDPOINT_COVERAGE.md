# Cashier endpoint coverage

This document maps the BlueSnap operations required by a Laravel Cashier-style package to the SDK. It defines the intended billing scope; it is not a claim that the SDK wraps every BlueSnap product.

## Required billing lifecycle

| Cashier capability | SDK method | BlueSnap operation |
| --- | --- | --- |
| Create checkout token | `paymentFieldsTokens()->create()` | `POST /payment-fields-tokens` |
| Encrypt Hosted Payment Page parameters | `paramEncryption()->encrypt()` | `POST /tools/param-encryption` (XML) |
| Build Hosted Payment Page checkout URL | `HostedPageUrl::build()` | Local URL construction for `/buynow/checkout` |
| Prefill saved card for 3-D Secure | `paymentFieldsTokens()->prefill()` | `POST /payment-fields-tokens/prefill` |
| Create customer | `vaultedShoppers()->create()` | `POST /vaulted-shoppers` |
| Retrieve customer/payment methods | `vaultedShoppers()->retrieve()` | `GET /vaulted-shoppers/{id}` |
| Update customer/payment methods | `vaultedShoppers()->update()` | `PUT /vaulted-shoppers/{id}` |
| Delete customer | `vaultedShoppers()->delete()` | `DELETE /vaulted-shoppers/{id}` |
| Create billing plan | `plans()->create()` | `POST /recurring/plans` |
| Retrieve/list plans | `plans()->retrieve()`, `plans()->all()` | `GET /recurring/plans[/{id}]` |
| Update/activate/deactivate plan | `plans()->update()`, `activate()`, `deactivate()` | `PUT /recurring/plans/{id}` |
| Create subscription | `subscriptions()->create()` | `POST /recurring/subscriptions` |
| Retrieve/list/reconcile subscriptions | `subscriptions()->retrieve()`, `all()` | `GET /recurring/subscriptions[/{id}]` |
| Swap plan, quantity, price, date, or payment source | `subscriptions()->update()` | `PUT /recurring/subscriptions/{id}` |
| Preview plan-switch charge | `subscriptions()->switchChargeAmount()` | `GET /recurring/subscriptions/{id}/switch-charge-amount` |
| Cancel immediately | `subscriptions()->cancel()` | `PUT /recurring/subscriptions/{id}` with `CANCELED` |
| Cancel at period end | `subscriptions()->cancelAtPeriodEnd()` | `PUT /recurring/subscriptions/{id}` with `autoRenew=false` |
| Reactivate/renew | `subscriptions()->activate()`, `renew()` | `PUT /recurring/subscriptions/{id}` |
| List subscription payments | `subscriptions()->charges()` | `GET /recurring/subscriptions/{id}/charges` |
| Resolve a specific subscription charge | `subscriptions()->chargeByTransactionId()` | `GET /recurring/subscriptions/charges/resolve` |
| Simulate a renewal in sandbox | `subscriptions()->simulate()` | `POST /recurring/subscriptions/{id}/run-specific` |
| Create merchant-managed subscription | `merchantManagedSubscriptions()->create()` | `POST /recurring/ondemand` |
| Charge merchant-managed subscription | `merchantManagedSubscriptions()->charge()` | `POST /recurring/ondemand/{id}` |
| One-time charge | `transactions()->charge()` | `POST /transactions` with `AUTH_CAPTURE` |
| Authorize payment | `transactions()->authorize()` | `POST /transactions` with `AUTH_ONLY` |
| Capture authorization | `transactions()->capture()` | `PUT /transactions` with `CAPTURE` |
| Void authorization | `transactions()->reverseAuthorization()` | `PUT /transactions` with `AUTH_REVERSAL` |
| Retrieve transaction | `transactions()->retrieve()` | `GET /transactions/{id}` |
| Retrieve using application order ID | `transactions()->retrieveByMerchantTransactionId()` | `GET /transactions/{merchantTransactionId},{merchantId}` |
| Full or partial refund | `transactions()->refund()` | `POST /transactions/refund/{id}` |
| Refund using application order ID | `transactions()->refundByMerchantTransactionId()` | `POST /transactions/refund/merchant/{id}` |
| Cancel pending refund | `transactions()->cancelPendingRefund()` | `DELETE /transactions/pending-refund/{id}` |
| Read/update/delete webhook configuration | `webhookConfigurations()` | `/notification-config` |

## Responsibilities of the Laravel package

Some required behavior is application integration rather than an outbound BlueSnap endpoint. The future Cashier package must still provide:

- Models and migrations for customers, subscriptions, and transactions.
- An HTTPS webhook controller that consumes BlueSnap's URL-encoded payloads.
- Webhook signature and timestamp verification using the merchant's BlueSnap security-header key.
- Idempotent webhook storage and queued event handling.
- Mapping for recurring charge, charge failure, cancellation, cancel-on-renewal, refund, decline, chargeback, payment-method update, and account-updater events.
- Periodic reconciliation using the subscription and charge list endpoints.
- Hosted Payment Fields JavaScript, Hosted Payment Page redirects, and 3-D Secure orchestration.
- A local billing portal because BlueSnap does not provide a Cashier-compatible customer portal API.
- Local invoice/receipt rendering from charges and transactions when an application needs Cashier-style PDFs.

BlueSnap does not expose a Paddle-equivalent arbitrary pause operation. The package must model supported behavior explicitly with `autoRenew`, `nextChargeDate`, cancellation, and BlueSnap-generated hold/suspension states rather than presenting false API parity.

## Deliberately outside the initial Cashier scope

The first Cashier package can be complete for card-based subscription billing without wrapping unrelated BlueSnap products. Marketplace vendor onboarding and payouts, reporting exports, chargeback representment, surcharges, and every alternative payment rail should remain optional SDK modules unless product requirements call for them.
