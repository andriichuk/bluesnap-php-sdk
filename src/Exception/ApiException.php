<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Exception;

use Andriichuk\BlueSnap\Http\Response;

class ApiException extends BlueSnapException
{
    /**
     * @param list<ApiError> $errors
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

        $message = $errors[0]->description ?? sprintf(
            'BlueSnap API request failed with HTTP status %d.',
            $response->statusCode,
        );

        return match ($response->statusCode) {
            401, 403 => new AuthenticationException($message, $response->statusCode, $errors, $response),
            404 => new NotFoundException($message, $response->statusCode, $errors, $response),
            409 => new ConflictException($message, $response->statusCode, $errors, $response),
            422 => new ValidationException($message, $response->statusCode, $errors, $response),
            429 => new RateLimitException($message, $response->statusCode, $errors, $response),
            default => new self($message, $response->statusCode, $errors, $response),
        };
    }
}
