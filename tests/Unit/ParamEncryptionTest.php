<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Tests\Unit;

use Andriichuk\BlueSnap\Exception\ApiException;
use Andriichuk\BlueSnap\Tests\Support\CreatesClient;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ParamEncryptionTest extends TestCase
{
    use CreatesClient;

    #[Test]
    public function it_sends_parameter_encryption_as_xml(): void
    {
        [$client, $http] = $this->createClient(new Response(200, ['Content-Type' => 'application/xml'], <<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <param-encryption xmlns="http://ws.plimus.com"><encrypted-token>opaque%2Btoken</encrypted-token></param-encryption>
            XML));

        $response = $client->paramEncryption()->encrypt([
            'thankyou.backtosellerurl' => 'https://app.example.com/return?token=a&b=2',
        ]);
        $request = $http->lastRequest();

        self::assertSame('/services/2/tools/param-encryption', $request->getUri()->getPath());
        self::assertSame('application/xml', $request->getHeaderLine('Content-Type'));
        self::assertStringContainsString('xmlns="http://ws.plimus.com"', (string) $request->getBody());
        self::assertStringContainsString('<param-key>thankyou.backtosellerurl</param-key>', (string) $request->getBody());
        self::assertStringContainsString('https://app.example.com/return?token=a&amp;b=2', (string) $request->getBody());
        self::assertStringContainsString('<encrypted-token>opaque%2Btoken</encrypted-token>', $response->body);
    }

    #[Test]
    public function an_http_415_explains_the_xml_requirement(): void
    {
        [$client] = $this->createClient(new Response(415));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('requires XML with Content-Type application/xml');

        $client->paramEncryption()->encrypt(['thankyou.backtosellerurl' => 'https://app.example.com/return']);
    }
}
