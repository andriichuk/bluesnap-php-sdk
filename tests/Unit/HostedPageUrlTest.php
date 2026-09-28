<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Tests\Unit;

use Andriichuk\BlueSnap\HostedPage\HostedPageUrl;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HostedPageUrlTest extends TestCase
{
    #[Test]
    public function it_builds_a_subscription_checkout_url_with_a_bare_plan_key(): void
    {
        self::assertSame(
            'https://sandbox.bluesnap.com/buynow/checkout?plan3173219&merchantid=1469228',
            HostedPageUrl::build('https://sandbox.bluesnap.com', '1469228', 3173219),
        );
    }

    #[Test]
    public function it_builds_the_quantity_variant_and_encodes_values(): void
    {
        self::assertSame(
            'https://checkout.bluesnap.com/buynow/checkout?plan3173219=3&merchantid=1469228&merchanttransactionid=order%20%231%2F2026&email=ada%2Bbilling%40example.com&enc=opaque%2Btoken%2F%3D',
            HostedPageUrl::build(
                'https://checkout.bluesnap.com/',
                '1469228',
                3173219,
                'order #1/2026',
                'ada+billing@example.com',
                'opaque+token/=',
                3,
            ),
        );
    }

    #[Test]
    public function it_rejects_merchant_transaction_ids_longer_than_fifty_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('between 1 and 50');

        HostedPageUrl::build('https://sandbox.bluesnap.com', '1469228', 3173219, str_repeat('x', 51));
    }
}
