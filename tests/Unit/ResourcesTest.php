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
}
