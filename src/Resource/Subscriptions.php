<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Resource;

use Andriichuk\BlueSnap\BlueSnapClient;
use Andriichuk\BlueSnap\Http\Response;

final readonly class Subscriptions
{
    public function __construct(private BlueSnapClient $client)
    {
    }

    /**
     * @param array<string, mixed> $subscription
     */
    public function create(array $subscription, ?string $idempotencyKey = null): Response
    {
        return $this->client->request(
            'POST',
            'recurring/subscriptions',
            $subscription,
            idempotencyKey: $idempotencyKey,
        );
    }

    public function retrieve(int|string $subscriptionId): Response
    {
        return $this->client->request(
            'GET',
            'recurring/subscriptions/'.rawurlencode((string) $subscriptionId),
        );
    }

    /**
     * @param array<string, scalar|null> $query
     */
    public function all(array $query = []): Response
    {
        return $this->client->request('GET', 'recurring/subscriptions', query: $query);
    }

    /**
     * @param array<string, mixed> $changes
     */
    public function update(int|string $subscriptionId, array $changes): Response
    {
        return $this->client->request(
            'PUT',
            'recurring/subscriptions/'.rawurlencode((string) $subscriptionId),
            $changes,
        );
    }

    public function cancel(int|string $subscriptionId): Response
    {
        return $this->update($subscriptionId, ['status' => 'CANCELED']);
    }

    public function cancelAtPeriodEnd(int|string $subscriptionId): Response
    {
        return $this->update($subscriptionId, ['autoRenew' => false]);
    }

    public function activate(int|string $subscriptionId): Response
    {
        return $this->update($subscriptionId, ['status' => 'ACTIVE']);
    }
}
