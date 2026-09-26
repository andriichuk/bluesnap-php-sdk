<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Tests\Unit;

use Andriichuk\BlueSnap\Tests\Support\CreatesClient;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResourcesTest extends TestCase
{
    use CreatesClient;

    #[Test]
    public function it_can_cancel_a_subscription_immediately(): void
    {
        [$client, $httpClient] = $this->createClient(new Response(200, [], '{"status":"CANCELED"}'));

        $client->subscriptions()->cancel(8491543);
        $request = $httpClient->lastRequest();

        self::assertSame('PUT', $request->getMethod());
        self::assertSame('/services/2/recurring/subscriptions/8491543', $request->getUri()->getPath());
        self::assertJsonStringEqualsJsonString('{"status":"CANCELED"}', (string) $request->getBody());
    }

    #[Test]
    public function it_can_cancel_a_subscription_at_period_end(): void
    {
        [$client, $httpClient] = $this->createClient(new Response(200, [], '{"autoRenew":false}'));

        $client->subscriptions()->cancelAtPeriodEnd(8491543);

        self::assertJsonStringEqualsJsonString(
            '{"autoRenew":false}',
            (string) $httpClient->lastRequest()->getBody(),
        );
    }

    #[Test]
    public function it_can_create_a_refund(): void
    {
        [$client, $httpClient] = $this->createClient(new Response(200, [], '{"refundTransactionId":42}'));

        $client->transactions()->refund(1012463333, ['amount' => '5.00'], 'refund-order-100');
        $request = $httpClient->lastRequest();

        self::assertSame('/services/2/transactions/refund/1012463333', $request->getUri()->getPath());
        self::assertSame('refund-order-100', $request->getHeaderLine('Idempotency-Key'));
    }

    #[Test]
    public function it_can_manage_vaulted_shoppers(): void
    {
        [$client, $httpClient] = $this->createClient(
            new Response(200, [], '{"vaultedShopperId":123}'),
            new Response(204),
        );

        $client->vaultedShoppers()->retrieve(123);
        self::assertSame('/services/2/vaulted-shoppers/123', $httpClient->lastRequest()->getUri()->getPath());

        $client->vaultedShoppers()->delete(123);
        self::assertSame('DELETE', $httpClient->lastRequest()->getMethod());
    }

    #[Test]
    public function it_exposes_the_complete_card_transaction_lifecycle(): void
    {
        [$client, $httpClient] = $this->createClient(
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
        );

        $client->transactions()->charge(['amount' => '19.99', 'currency' => 'USD'], 'charge-1');
        self::assertJsonStringEqualsJsonString(
            '{"amount":"19.99","currency":"USD","cardTransactionType":"AUTH_CAPTURE"}',
            (string) $httpClient->lastRequest()->getBody(),
        );

        $client->transactions()->authorize(['amount' => '19.99', 'currency' => 'USD'], 'auth-1');
        self::assertStringContainsString('"cardTransactionType":"AUTH_ONLY"', (string) $httpClient->lastRequest()->getBody());

        $client->transactions()->capture(1001, ['amount' => '10.00']);
        self::assertJsonStringEqualsJsonString(
            '{"amount":"10.00","cardTransactionType":"CAPTURE","transactionId":1001}',
            (string) $httpClient->lastRequest()->getBody(),
        );

        $client->transactions()->reverseAuthorization(1002);
        self::assertJsonStringEqualsJsonString(
            '{"cardTransactionType":"AUTH_REVERSAL","transactionId":1002}',
            (string) $httpClient->lastRequest()->getBody(),
        );
    }

    #[Test]
    public function it_supports_merchant_transaction_ids_and_pending_refunds(): void
    {
        [$client, $httpClient] = $this->createClient(
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200),
        );

        $client->transactions()->retrieveByMerchantTransactionId('order-100', 456);
        self::assertSame('/services/2/transactions/order-100,456', $httpClient->lastRequest()->getUri()->getPath());

        $client->transactions()->refundByMerchantTransactionId(
            'order-100',
            ['amount' => '5.00'],
            'refund-100',
            ['simulatenofunds' => true],
        );
        $request = $httpClient->lastRequest();
        self::assertSame('/services/2/transactions/refund/merchant/order-100', $request->getUri()->getPath());
        self::assertSame('simulatenofunds=true', $request->getUri()->getQuery());

        $client->transactions()->cancelPendingRefund(9001);
        self::assertSame('DELETE', $httpClient->lastRequest()->getMethod());
        self::assertSame(
            '/services/2/transactions/pending-refund/9001',
            $httpClient->lastRequest()->getUri()->getPath(),
        );
    }

    #[Test]
    public function it_supports_subscription_charge_history_and_switch_previews(): void
    {
        [$client, $httpClient] = $this->createClient(
            new Response(200, [], '{"charges":[]}'),
            new Response(200, [], '{"chargeId":10}'),
            new Response(200, [], '{"currency":"USD","value":15}'),
            new Response(204),
        );

        $client->subscriptions()->charges(2001, ['pagesize' => 50, 'fulldescription' => true]);
        self::assertSame('/services/2/recurring/subscriptions/2001/charges', $httpClient->lastRequest()->getUri()->getPath());
        self::assertSame('pagesize=50&fulldescription=true', $httpClient->lastRequest()->getUri()->getQuery());

        $client->subscriptions()->chargeByTransactionId(3001);
        self::assertSame(
            'transactionid=3001',
            $httpClient->lastRequest()->getUri()->getQuery(),
        );

        $client->subscriptions()->switchChargeAmount(2001, [
            'newplanid' => 4001,
            'newquantity' => 2,
        ]);
        self::assertSame(
            '/services/2/recurring/subscriptions/2001/switch-charge-amount',
            $httpClient->lastRequest()->getUri()->getPath(),
        );

        $client->subscriptions()->simulate(2001);
        self::assertSame('POST', $httpClient->lastRequest()->getMethod());
        self::assertSame(
            '/services/2/recurring/subscriptions/2001/run-specific',
            $httpClient->lastRequest()->getUri()->getPath(),
        );
    }

    #[Test]
    public function it_supports_merchant_managed_subscriptions(): void
    {
        [$client, $httpClient] = $this->createClient(
            new Response(200, [], '{"subscriptionId":5001}'),
            new Response(200, [], '{"chargeId":6001}'),
        );

        $client->merchantManagedSubscriptions()->create([
            'amount' => '29.99',
            'currency' => 'USD',
        ], 'ondemand-1');
        self::assertSame('/services/2/recurring/ondemand', $httpClient->lastRequest()->getUri()->getPath());

        $client->merchantManagedSubscriptions()->charge(5001, [
            'amount' => '29.99',
            'currency' => 'USD',
        ], 'ondemand-charge-1');
        self::assertSame('/services/2/recurring/ondemand/5001', $httpClient->lastRequest()->getUri()->getPath());
        self::assertSame('ondemand-charge-1', $httpClient->lastRequest()->getHeaderLine('Idempotency-Key'));
    }

    #[Test]
    public function it_supports_saved_card_3ds_token_prefill(): void
    {
        [$client, $httpClient] = $this->createClient(new Response(201, ['Location' => '/token/123']));

        $client->paymentFieldsTokens()->prefill([
            'ccNumber' => '4111111111111111',
            'expDate' => '12/2030',
        ], ['authenticationrequired3ds' => true]);

        $request = $httpClient->lastRequest();
        self::assertSame('/services/2/payment-fields-tokens/prefill', $request->getUri()->getPath());
        self::assertSame('authenticationrequired3ds=true', $request->getUri()->getQuery());
    }

    #[Test]
    public function it_can_manage_webhook_configuration_with_api_version_two(): void
    {
        [$client, $httpClient] = $this->createClient(
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(204),
        );

        $client->webhookConfigurations()->retrieve();
        self::assertSame('2.0', $httpClient->lastRequest()->getHeaderLine('BlueSnap-Version'));

        $client->webhookConfigurations()->update([
            'ipnDestinations' => [['ipnUrl' => 'https://example.com/webhooks/bluesnap']],
            'onCharge' => true,
        ]);
        self::assertSame('POST', $httpClient->lastRequest()->getMethod());

        $client->webhookConfigurations()->delete();
        self::assertSame('DELETE', $httpClient->lastRequest()->getMethod());
        self::assertSame('/services/2/notification-config', $httpClient->lastRequest()->getUri()->getPath());
    }
}
