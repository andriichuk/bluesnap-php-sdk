<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Http;

use JsonException;
use Psr\Http\Message\ResponseInterface;

final readonly class Response
{
    /**
     * @param array<string, list<string>> $headers
     * @param array<mixed> $decoded
     */
    private function __construct(
        public int $statusCode,
        public array $headers,
        public string $body,
        private array $decoded,
    ) {
    }

    public static function fromPsrResponse(ResponseInterface $response): self
    {
        $body = (string) $response->getBody();
        $decoded = [];

        if ($body !== '') {
            try {
                $value = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                $decoded = is_array($value) ? $value : [];
            } catch (JsonException) {
                // Preserve non-JSON bodies for consumers via the body property.
            }
        }

        /** @var array<string, list<string>> $headers */
        $headers = $response->getHeaders();

        return new self($response->getStatusCode(), $headers, $body, $decoded);
    }

    /**
     * @return array<mixed>
     */
    public function json(): array
    {
        return $this->decoded;
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $header => $values) {
            if (strcasecmp($header, $name) === 0) {
                return $values[0] ?? null;
            }
        }

        return null;
    }

    public function location(): ?string
    {
        return $this->header('Location');
    }
}
