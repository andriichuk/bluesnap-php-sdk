<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Resource;

use Andriichuk\BlueSnap\BlueSnapClient;
use Andriichuk\BlueSnap\Http\Response;

final readonly class PaymentFieldsTokens
{
    public function __construct(private BlueSnapClient $client) {}

    /**
     * @param  array<string, scalar|null>  $options
     */
    public function create(array $options = []): Response
    {
        return $this->client->request('POST', 'payment-fields-tokens', query: $options);
    }

    /**
     * Create a 3-D Secure token prefilled with a saved card's details.
     *
     * @param  array<string, mixed>  $card
     * @param  array<string, scalar|null>  $options
     */
    public function prefill(array $card, array $options = []): Response
    {
        return $this->client->request('POST', 'payment-fields-tokens/prefill', $card, $options);
    }
}
