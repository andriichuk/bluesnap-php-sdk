<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Resource;

use Andriichuk\BlueSnap\BlueSnapClient;
use Andriichuk\BlueSnap\Http\Response;

final readonly class Plans
{
    public function __construct(private BlueSnapClient $client) {}

    /**
     * @param  array<string, mixed>  $plan
     */
    public function create(array $plan, ?string $idempotencyKey = null): Response
    {
        return $this->client->request('POST', 'recurring/plans', $plan, idempotencyKey: $idempotencyKey);
    }

    public function retrieve(int|string $planId): Response
    {
        return $this->client->request('GET', 'recurring/plans/'.rawurlencode((string) $planId));
    }

    /**
     * @param  array<string, scalar|null>  $query
     */
    public function all(array $query = []): Response
    {
        return $this->client->request('GET', 'recurring/plans', query: $query);
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    public function update(int|string $planId, array $changes): Response
    {
        return $this->client->request('PUT', 'recurring/plans/'.rawurlencode((string) $planId), $changes);
    }

    public function activate(int|string $planId): Response
    {
        return $this->update($planId, ['status' => 'ACTIVE']);
    }

    public function deactivate(int|string $planId): Response
    {
        return $this->update($planId, ['status' => 'INACTIVE']);
    }
}
