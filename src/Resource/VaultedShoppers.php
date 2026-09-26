<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Resource;

use Andriichuk\BlueSnap\BlueSnapClient;
use Andriichuk\BlueSnap\Http\Response;

final readonly class VaultedShoppers
{
    public function __construct(private BlueSnapClient $client)
    {
    }

    /**
     * @param array<string, mixed> $shopper
     */
    public function create(array $shopper, ?string $idempotencyKey = null): Response
    {
        return $this->client->request('POST', 'vaulted-shoppers', $shopper, idempotencyKey: $idempotencyKey);
    }

    public function retrieve(int|string $shopperId): Response
    {
        return $this->client->request('GET', 'vaulted-shoppers/'.rawurlencode((string) $shopperId));
    }

    /**
     * @param array<string, mixed> $changes
     */
    public function update(int|string $shopperId, array $changes): Response
    {
        return $this->client->request(
            'PUT',
            'vaulted-shoppers/'.rawurlencode((string) $shopperId),
            $changes,
        );
    }

    public function delete(int|string $shopperId): Response
    {
        return $this->client->request('DELETE', 'vaulted-shoppers/'.rawurlencode((string) $shopperId));
    }
}
