<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Resource;

use Andriichuk\BlueSnap\BlueSnapClient;
use Andriichuk\BlueSnap\Http\Response;

final readonly class Subscriptions
{
    public function __construct(private BlueSnapClient $client) {}

    /**
     * @param  array<string, mixed>  $subscription
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
     * @param  array<string, scalar|null>  $query
     */
    public function all(array $query = []): Response
    {
        return $this->client->request('GET', 'recurring/subscriptions', query: $query);
    }

    /**
     * @param  array<string, mixed>  $changes
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

    public function renew(int|string $subscriptionId): Response
    {
        return $this->update($subscriptionId, ['autoRenew' => true]);
    }

    /**
     * @param  array<string, scalar|null>  $query
     */
    public function charges(int|string $subscriptionId, array $query = []): Response
    {
        return $this->client->request(
            'GET',
            'recurring/subscriptions/'.rawurlencode((string) $subscriptionId).'/charges',
            query: $query,
        );
    }

    public function chargeByTransactionId(int|string $transactionId): Response
    {
        return $this->client->request(
            'GET',
            'recurring/subscriptions/charges/resolve',
            query: ['transactionid' => $transactionId],
        );
    }

    /**
     * Preview the charge caused by changing a plan, quantity, or override amount.
     *
     * @param  array<string, scalar|null>  $changes
     */
    public function switchChargeAmount(int|string $subscriptionId, array $changes): Response
    {
        return $this->client->request(
            'GET',
            'recurring/subscriptions/'.rawurlencode((string) $subscriptionId).'/switch-charge-amount',
            query: $changes,
        );
    }

    /**
     * Trigger a renewal event in BlueSnap's sandbox subscription simulator.
     */
    public function simulate(int|string $subscriptionId): Response
    {
        return $this->client->request(
            'POST',
            'recurring/subscriptions/'.rawurlencode((string) $subscriptionId).'/run-specific',
        );
    }
}
