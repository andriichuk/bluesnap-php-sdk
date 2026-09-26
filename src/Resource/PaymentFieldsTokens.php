<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Resource;

use Andriichuk\BlueSnap\BlueSnapClient;
use Andriichuk\BlueSnap\Http\Response;

final readonly class PaymentFieldsTokens
{
    public function __construct(private BlueSnapClient $client)
    {
    }

    /**
     * @param array<string, scalar|null> $options
     */
    public function create(array $options = []): Response
    {
        return $this->client->request('POST', 'payment-fields-tokens', query: $options);
    }
}
