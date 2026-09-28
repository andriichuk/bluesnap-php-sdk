<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Tests\Unit;

use Andriichuk\BlueSnap\Configuration;
use Andriichuk\BlueSnap\Environment;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    #[Test]
    public function it_exposes_environment_specific_checkout_hosts(): void
    {
        self::assertSame(
            'https://sandbox.bluesnap.com',
            new Configuration('user', 'password', merchantId: '1469228')->checkoutHost(),
        );
        self::assertSame(
            'https://checkout.bluesnap.com',
            new Configuration('user', 'password', Environment::Production, merchantId: '1469228')->checkoutHost(),
        );
    }

    #[Test]
    public function merchant_id_is_optional_for_api_only_clients_without_changing_positional_arguments(): void
    {
        $configuration = new Configuration('user', 'password', Environment::Production, '3.0', 'custom-agent');

        self::assertNull($configuration->merchantId);
        self::assertSame('https://ws.bluesnap.com/services/2', $configuration->baseUri());
        self::assertSame('custom-agent', $configuration->userAgent);
    }

    #[Test]
    public function it_accepts_an_explicit_checkout_host(): void
    {
        $configuration = new Configuration(
            'user',
            'password',
            merchantId: '1469228',
            checkoutHost: 'https://payments.example.com/',
        );

        self::assertSame('https://payments.example.com', $configuration->checkoutHost());
    }

    #[Test]
    #[DataProvider('invalidMerchantIds')]
    public function it_rejects_invalid_merchant_ids(string $merchantId): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Configuration('user', 'password', merchantId: $merchantId);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidMerchantIds(): iterable
    {
        yield 'empty' => [''];
        yield 'zero' => ['0'];
        yield 'negative' => ['-1'];
        yield 'not numeric' => ['merchant'];
    }
}
