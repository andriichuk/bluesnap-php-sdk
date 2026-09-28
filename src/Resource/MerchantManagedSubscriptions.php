<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Resource;

use Andriichuk\BlueSnap\BlueSnapClient;
use Andriichuk\BlueSnap\Http\Response;

final readonly class MerchantManagedSubscriptions
{
    public function __construct(private BlueSnapClient $client) {}

    /**
     * Create a merchant-managed subscription and its initial charge.
     *
     * @param  array<string, mixed>  $charge
     */
    public function create(array $charge, ?string $idempotencyKey = null): Response
    {
        return $this->client->request(
            'POST',
            'recurring/ondemand',
            $charge,
            idempotencyKey: $idempotencyKey,
        );
    }

    /**
     * Add a recurring charge to a merchant-managed subscription.
     *
     * @param  array<string, mixed>  $charge
     */
    public function charge(
        int|string $subscriptionId,
        array $charge,
        ?string $idempotencyKey = null,
    ): Response {
        return $this->client->request(
            'POST',
            'recurring/ondemand/'.rawurlencode((string) $subscriptionId),
            $charge,
            idempotencyKey: $idempotencyKey,
        );
    }
}
