<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Tests\Unit;

use Andriichuk\BlueSnap\Configuration;
use Andriichuk\BlueSnap\Environment;
use Andriichuk\BlueSnap\HostedPage\HostedPageRequest;
use Andriichuk\BlueSnap\HostedPage\HostedPageUrl;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HostedPageUrlTest extends TestCase
{
    private function urlBuilder(?string $merchantId = '1469228', Environment $environment = Environment::Sandbox): HostedPageUrl
    {
        return new HostedPageUrl(new Configuration(
            username: 'user',
            password: 'secret',
            environment: $environment,
            merchantId: $merchantId,
        ));
    }

    #[Test]
    public function it_builds_a_subscription_checkout_url_with_a_bare_plan_key(): void
    {
        self::assertSame(
            'https://sandbox.bluesnap.com/buynow/checkout?plan3173219&merchantid=1469228',
            $this->urlBuilder()->build(new HostedPageRequest(planId: 3173219)),
        );
    }

    #[Test]
    public function it_builds_the_quantity_variant_and_encodes_values(): void
    {
        self::assertSame(
            'https://checkout.bluesnap.com/buynow/checkout?plan3173219=3&merchantid=1469228&merchanttransactionid=order%20%231%2F2026&email=ada%2Bbilling%40example.com&enc=opaque%2Btoken%2F%3D',
            $this->urlBuilder(environment: Environment::Production)->build(new HostedPageRequest(
                planId: 3173219,
                merchantTransactionId: 'order #1/2026',
                email: 'ada+billing@example.com',
                enc: 'opaque+token/=',
                quantity: 3,
            )),
        );
    }

    #[Test]
    public function it_requires_a_merchant_id_on_the_configuration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('merchant ID is required to build a Hosted Payment Page URL');

        $this->urlBuilder(merchantId: null)->build(new HostedPageRequest(planId: 3173219));
    }

    #[Test]
    public function it_rejects_merchant_transaction_ids_longer_than_fifty_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('between 1 and 50');

        new HostedPageRequest(planId: 3173219, merchantTransactionId: str_repeat('x', 51));
    }

    #[Test]
    public function it_rejects_a_non_positive_plan_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('plan ID must be a positive integer');

        new HostedPageRequest(planId: 0);
    }

    #[Test]
    public function it_rejects_a_non_positive_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('quantity must be a positive integer');

        new HostedPageRequest(planId: 3173219, quantity: 0);
    }
}
