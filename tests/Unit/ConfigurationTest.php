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
            new Configuration('user', 'password', '1469228')->checkoutHost(),
        );
        self::assertSame(
            'https://checkout.bluesnap.com',
            new Configuration('user', 'password', '1469228', Environment::Production)->checkoutHost(),
        );
    }

    #[Test]
    public function it_accepts_an_explicit_checkout_host(): void
    {
        $configuration = new Configuration(
            'user',
            'password',
            '1469228',
            checkoutHost: 'https://payments.example.com/',
        );

        self::assertSame('https://payments.example.com', $configuration->checkoutHost());
    }

    #[Test]
    #[DataProvider('invalidMerchantIds')]
    public function it_rejects_invalid_merchant_ids(string $merchantId): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Configuration('user', 'password', $merchantId);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidMerchantIds(): iterable
    {
        yield 'empty' => [''];
        yield 'zero' => ['0'];
        yield 'negative' => ['-1'];
        yield 'not numeric' => ['merchant'];
    }
}
