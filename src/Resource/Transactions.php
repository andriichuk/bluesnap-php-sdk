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

    /**
     * @param array<string, mixed> $transaction
     */
    public function charge(array $transaction, ?string $idempotencyKey = null): Response
    {
        return $this->create(
            [...$transaction, 'cardTransactionType' => 'AUTH_CAPTURE'],
            $idempotencyKey,
        );
    }

    /**
     * @param array<string, mixed> $transaction
     */
    public function authorize(array $transaction, ?string $idempotencyKey = null): Response
    {
        return $this->create(
            [...$transaction, 'cardTransactionType' => 'AUTH_ONLY'],
            $idempotencyKey,
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    public function capture(int|string $transactionId, array $options = []): Response
    {
        return $this->client->request('PUT', 'transactions', [
            ...$options,
            'cardTransactionType' => 'CAPTURE',
            'transactionId' => $transactionId,
        ]);
    }

    /**
     * Reverse an uncaptured authorization.
     */
    public function reverseAuthorization(int|string $transactionId): Response
    {
        return $this->client->request('PUT', 'transactions', [
            'cardTransactionType' => 'AUTH_REVERSAL',
            'transactionId' => $transactionId,
        ]);
    }

    public function retrieve(int|string $transactionId): Response
    {
        return $this->client->request('GET', 'transactions/'.rawurlencode((string) $transactionId));
    }

    public function retrieveByMerchantTransactionId(
        int|string $merchantTransactionId,
        int|string $merchantId,
    ): Response {
        $identifier = rawurlencode((string) $merchantTransactionId).','.rawurlencode((string) $merchantId);

        return $this->client->request('GET', 'transactions/'.$identifier);
    }

    /**
     * @param array<string, mixed> $refund
     * @param array<string, scalar|null> $query
     */
    public function refund(
        int|string $transactionId,
        array $refund = [],
        ?string $idempotencyKey = null,
        array $query = [],
    ): Response {
        return $this->client->request(
            'POST',
            'transactions/refund/'.rawurlencode((string) $transactionId),
            $refund,
            $query,
            idempotencyKey: $idempotencyKey,
        );
    }

    /**
     * @param array<string, mixed> $refund
     * @param array<string, scalar|null> $query
     */
    public function refundByMerchantTransactionId(
        int|string $merchantTransactionId,
        array $refund = [],
        ?string $idempotencyKey = null,
        array $query = [],
    ): Response {
        return $this->client->request(
            'POST',
            'transactions/refund/merchant/'.rawurlencode((string) $merchantTransactionId),
            $refund,
            $query,
            idempotencyKey: $idempotencyKey,
        );
    }

    public function cancelPendingRefund(int|string $refundTransactionId): Response
    {
        return $this->client->request(
            'DELETE',
            'transactions/pending-refund/'.rawurlencode((string) $refundTransactionId),
        );
    }
}
