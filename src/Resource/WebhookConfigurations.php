<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Resource;

use Andriichuk\BlueSnap\BlueSnapClient;
use Andriichuk\BlueSnap\Http\Response;

final readonly class WebhookConfigurations
{
    private const array HEADERS = ['BlueSnap-Version' => '2.0'];

    public function __construct(private BlueSnapClient $client)
    {
    }

    public function retrieve(): Response
    {
        return $this->client->request('GET', 'notification-config', headers: self::HEADERS);
    }

    /**
     * BlueSnap treats omitted webhook flags as disabled. Send the complete desired configuration.
     *
     * @param array<string, mixed> $configuration
     */
    public function update(array $configuration): Response
    {
        return $this->client->request(
            'POST',
            'notification-config',
            $configuration,
            headers: self::HEADERS,
        );
    }

    public function delete(): Response
    {
        return $this->client->request('DELETE', 'notification-config', headers: self::HEADERS);
    }
}
