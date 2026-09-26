<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap;

use Andriichuk\BlueSnap\Exception\ApiException;
use Andriichuk\BlueSnap\Exception\TransportException;
use Andriichuk\BlueSnap\Http\Response;
use Andriichuk\BlueSnap\Resource\PaymentFieldsTokens;
use Andriichuk\BlueSnap\Resource\Plans;
use Andriichuk\BlueSnap\Resource\MerchantManagedSubscriptions;
use Andriichuk\BlueSnap\Resource\Subscriptions;
use Andriichuk\BlueSnap\Resource\Transactions;
use Andriichuk\BlueSnap\Resource\VaultedShoppers;
use Andriichuk\BlueSnap\Resource\WebhookConfigurations;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class BlueSnapClient
{
    public function __construct(
        public readonly Configuration $configuration,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

    public function plans(): Plans
    {
        return new Plans($this);
    }

    public function subscriptions(): Subscriptions
    {
        return new Subscriptions($this);
    }

    public function merchantManagedSubscriptions(): MerchantManagedSubscriptions
    {
        return new MerchantManagedSubscriptions($this);
    }

    public function transactions(): Transactions
    {
        return new Transactions($this);
    }

    public function vaultedShoppers(): VaultedShoppers
    {
        return new VaultedShoppers($this);
    }

    public function paymentFieldsTokens(): PaymentFieldsTokens
    {
        return new PaymentFieldsTokens($this);
    }

    public function webhookConfigurations(): WebhookConfigurations
    {
        return new WebhookConfigurations($this);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, scalar|null> $query
     * @param array<string, string> $headers
     *
     * @throws ApiException
     * @throws TransportException
     */
    public function request(
        string $method,
        string $path,
        array $payload = [],
        array $query = [],
        array $headers = [],
        ?string $idempotencyKey = null,
    ): Response {
        $method = strtoupper($method);

        if ($idempotencyKey !== null) {
            if ($method !== 'POST') {
                throw new InvalidArgumentException('BlueSnap idempotency keys may only be used with POST requests.');
            }

            if ($idempotencyKey === '' || strlen($idempotencyKey) > 64) {
                throw new InvalidArgumentException('A BlueSnap idempotency key must contain between 1 and 64 characters.');
            }

            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        $uri = $this->buildUri($path, $query);
        $request = $this->requestFactory->createRequest($method, $uri);

        $defaultHeaders = [
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode($this->configuration->username.':'.$this->configuration->password),
            'BlueSnap-Version' => $this->configuration->apiVersion,
            'Content-Type' => 'application/json',
            'User-Agent' => $this->configuration->userAgent,
        ];

        foreach ([...$defaultHeaders, ...$headers] as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($payload !== []) {
            try {
                $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
            } catch (JsonException $exception) {
                throw new InvalidArgumentException('The request payload cannot be encoded as JSON.', previous: $exception);
            }

            $request = $request->withBody($this->streamFactory->createStream($json));
        }

        try {
            $psrResponse = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw new TransportException('The request to BlueSnap could not be completed.', previous: $exception);
        }

        $response = Response::fromPsrResponse($psrResponse);

        if ($response->statusCode < 200 || $response->statusCode >= 300) {
            throw ApiException::fromResponse($response);
        }

        return $response;
    }

    /**
     * @param array<string, scalar|null> $query
     */
    private function buildUri(string $path, array $query): string
    {
        $uri = $this->configuration->baseUri().'/'.ltrim($path, '/');
        $normalized = [];

        foreach ($query as $key => $value) {
            if ($value === null) {
                continue;
            }

            $normalized[$key] = is_bool($value) ? ($value ? 'true' : 'false') : $value;
        }

        if ($normalized !== []) {
            $uri .= '?'.http_build_query($normalized, '', '&', PHP_QUERY_RFC3986);
        }

        return $uri;
    }
}
