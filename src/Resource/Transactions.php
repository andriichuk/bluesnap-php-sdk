<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Resource;

use Andriichuk\BlueSnap\BlueSnapClient;
use Andriichuk\BlueSnap\Http\Response;

final readonly class Transactions
{
    public function __construct(private BlueSnapClient $client)
    {
    }

    /**
     * @param array<string, mixed> $transaction
     */
    public function create(array $transaction, ?string $idempotencyKey = null): Response
    {
        return $this->client->request('POST', 'transactions', $transaction, idempotencyKey: $idempotencyKey);
    }

    public function retrieve(int|string $transactionId): Response
    {
        return $this->client->request('GET', 'transactions/'.rawurlencode((string) $transactionId));
    }

    /**
     * @param array<string, mixed> $refund
     */
    public function refund(int|string $transactionId, array $refund = [], ?string $idempotencyKey = null): Response
    {
        return $this->client->request(
            'POST',
            'transactions/refund/'.rawurlencode((string) $transactionId),
            $refund,
            idempotencyKey: $idempotencyKey,
        );
    }
}
