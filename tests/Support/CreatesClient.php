<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Tests\Support;

use Andriichuk\BlueSnap\BlueSnapClient;
use Andriichuk\BlueSnap\Configuration;
use Andriichuk\BlueSnap\Environment;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;

trait CreatesClient
{
    /**
     * @return array{BlueSnapClient, RecordingClient}
     */
    private function createClient(ResponseInterface ...$responses): array
    {
        $httpClient = new RecordingClient(...$responses);
        $factory = new Psr17Factory;
        $client = new BlueSnapClient(
            new Configuration('api-user', 'api-password', Environment::Sandbox, merchantId: '1469228'),
            $httpClient,
            $factory,
            $factory,
        );

        return [$client, $httpClient];
    }
}
