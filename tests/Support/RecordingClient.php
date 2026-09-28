<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Tests\Support;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class RecordingClient implements ClientInterface
{
    /**
     * @var list<RequestInterface>
     */
    public array $requests = [];

    /**
     * @var list<ResponseInterface>
     */
    private array $responses;

    public function __construct(ResponseInterface ...$responses)
    {
        $this->responses = array_values($responses);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        return array_shift($this->responses)
            ?? throw new RuntimeException('No fake response is queued.');
    }

    public function lastRequest(): RequestInterface
    {
        $request = $this->requests[count($this->requests) - 1] ?? null;

        return $request ?? throw new RuntimeException('No request has been recorded.');
    }
}
