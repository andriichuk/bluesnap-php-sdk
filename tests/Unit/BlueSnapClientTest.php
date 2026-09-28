<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Tests\Unit;

use Andriichuk\BlueSnap\Exception\ValidationException;
use Andriichuk\BlueSnap\Exception\AuthenticationException;
use Andriichuk\BlueSnap\Tests\Support\CreatesClient;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BlueSnapClientTest extends TestCase
{
    use CreatesClient;

    #[Test]
    public function it_builds_an_authenticated_json_request(): void
    {
        [$client, $httpClient] = $this->createClient(new Response(200, [], '{"planId":123}'));

        $response = $client->plans()->retrieve(123);
        $request = $httpClient->lastRequest();

        self::assertSame('GET', $request->getMethod());
        self::assertSame(
            'https://sandbox.bluesnap.com/services/2/recurring/plans/123',
            (string) $request->getUri(),
        );
        self::assertSame('Basic '.base64_encode('api-user:api-password'), $request->getHeaderLine('Authorization'));
        self::assertSame('3.0', $request->getHeaderLine('BlueSnap-Version'));
        self::assertSame(123, $response->json()['planId']);
    }

    #[Test]
    public function it_normalizes_boolean_query_parameters(): void
    {
        [$client, $httpClient] = $this->createClient(new Response(200, [], '{"plans":[]}'));

        $client->plans()->all([
            'pagesize' => 50,
            'gettotal' => true,
            'fulldescription' => false,
            'after' => null,
        ]);

        self::assertSame(
            'pagesize=50&gettotal=true&fulldescription=false',
            $httpClient->lastRequest()->getUri()->getQuery(),
        );
    }

    #[Test]
    public function it_sends_json_and_an_idempotency_key(): void
    {
        [$client, $httpClient] = $this->createClient(new Response(200, [], '{"planId":456}'));

        $client->plans()->create([
            'name' => 'Monthly',
            'currency' => 'USD',
            'chargeFrequency' => 'MONTHLY',
            'recurringChargeAmount' => '19.99',
        ], 'f51903be-68f4-4c16-b55f-705ad4508f30');

        $request = $httpClient->lastRequest();

        self::assertSame('POST', $request->getMethod());
        self::assertSame('f51903be-68f4-4c16-b55f-705ad4508f30', $request->getHeaderLine('Idempotency-Key'));
        self::assertJsonStringEqualsJsonString(
            '{"name":"Monthly","currency":"USD","chargeFrequency":"MONTHLY","recurringChargeAmount":"19.99"}',
            (string) $request->getBody(),
        );
    }

    #[Test]
    public function it_maps_bluesnap_errors_to_typed_exceptions(): void
    {
        [$client] = $this->createClient(new Response(422, [], json_encode([
            'message' => [[
                'errorName' => 'VALIDATION_GENERAL_FAILURE',
                'code' => 10001,
                'description' => 'Invalid plan',
                'invalidProperty' => ['name' => 'currency'],
            ]],
        ], JSON_THROW_ON_ERROR)));

        try {
            $client->plans()->create(['currency' => 'INVALID']);
            self::fail('A ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame(422, $exception->statusCode);
            self::assertSame('Invalid plan', $exception->getMessage());
            self::assertSame(10001, $exception->errors[0]->code);
            self::assertSame(['name' => 'currency'], $exception->errors[0]->invalidProperty);
        }
    }

    #[Test]
    public function it_reads_location_headers_case_insensitively(): void
    {
        [$client] = $this->createClient(new Response(
            201,
            ['location' => 'https://sandbox.bluesnap.com/services/2/payment-fields-tokens/token-123'],
        ));

        $response = $client->paymentFieldsTokens()->create(['authenticationrequired3ds' => true]);

        self::assertSame(
            'https://sandbox.bluesnap.com/services/2/payment-fields-tokens/token-123',
            $response->location(),
        );
    }

    #[Test]
    public function authentication_and_ip_allowlist_failures_are_distinguishable(): void
    {
        [$unauthorized] = $this->createClient(new Response(401));

        try {
            $unauthorized->plans()->all();
            self::fail('Expected an authentication exception.');
        } catch (AuthenticationException $exception) {
            self::assertStringContainsString('credentials', $exception->getMessage());
            self::assertStringContainsString('HTTP 401', $exception->getMessage());
        }

        [$forbidden] = $this->createClient(new Response(403));

        try {
            $forbidden->plans()->all();
            self::fail('Expected an IP allowlist exception.');
        } catch (AuthenticationException $exception) {
            self::assertStringContainsString('IP is allowlisted', $exception->getMessage());
            self::assertStringContainsString('HTTP 403', $exception->getMessage());
        }
    }
}
