<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\HostedPage;

use InvalidArgumentException;

final readonly class HostedPageRequest
{
    private const int MERCHANT_TRANSACTION_ID_MAX_LENGTH = 50;

    public function __construct(
        public int $planId,
        public ?string $merchantTransactionId = null,
        public ?string $email = null,
        public ?string $enc = null,
        public ?int $quantity = null,
    ) {
        if ($this->planId < 1) {
            throw new InvalidArgumentException('The BlueSnap plan ID must be a positive integer.');
        }

        if ($this->quantity !== null && $this->quantity < 1) {
            throw new InvalidArgumentException('The BlueSnap Hosted Payment Page quantity must be a positive integer.');
        }

        if ($this->merchantTransactionId !== null
            && ($this->merchantTransactionId === '' || strlen($this->merchantTransactionId) > self::MERCHANT_TRANSACTION_ID_MAX_LENGTH)) {
            throw new InvalidArgumentException('The BlueSnap merchant transaction ID must contain between 1 and 50 characters.');
        }
    }
}
