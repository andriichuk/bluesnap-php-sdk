<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Exception;

use Andriichuk\BlueSnap\Http\Response;

class ApiException extends BlueSnapException
{
    /**
     * @param  list<ApiError>  $errors
     */
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly array $errors,
        public readonly Response $response,
    ) {
        parent::__construct($message, $statusCode);
    }

    public static function fromResponse(Response $response): self
    {
        $errors = [];
        $messages = $response->json()['message'] ?? [];

        if (is_array($messages)) {
            foreach ($messages as $message) {
                if (is_array($message)) {
                    /** @var array<string, mixed> $message */
                    $errors[] = ApiError::fromArray($message);
                }
            }
        }

        $description = $errors[0]->description ?? null;
        $message = match ($response->statusCode) {
            401 => self::withDescription('BlueSnap API authentication failed (HTTP 401); verify the API credentials.', $description),
            403 => self::withDescription('BlueSnap API request was forbidden (HTTP 403); verify that the calling IP is allowlisted.', $description),
            415 => self::withDescription('BlueSnap API rejected the media type (HTTP 415); parameter encryption requires XML with Content-Type application/xml.', $description),
            default => $description ?? sprintf('BlueSnap API request failed with HTTP status %d.', $response->statusCode),
        };

        return match ($response->statusCode) {
            401, 403 => new AuthenticationException($message, $response->statusCode, $errors, $response),
            404 => new NotFoundException($message, $response->statusCode, $errors, $response),
            409 => new ConflictException($message, $response->statusCode, $errors, $response),
            422 => new ValidationException($message, $response->statusCode, $errors, $response),
            429 => new RateLimitException($message, $response->statusCode, $errors, $response),
            default => new self($message, $response->statusCode, $errors, $response),
        };
    }

    private static function withDescription(string $message, ?string $description): string
    {
        return $description === null ? $message : $message.' '.$description;
    }
}
