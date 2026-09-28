<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Exception;

final readonly class ApiError
{
    /**
     * @param  array<array-key, mixed>|string|null  $invalidProperty
     */
    public function __construct(
        public ?string $name,
        public int|string|null $code,
        public ?string $description,
        public array|string|null $invalidProperty = null,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $code = $payload['code'] ?? null;

        return new self(
            name: is_string($payload['errorName'] ?? null) ? $payload['errorName'] : null,
            code: is_int($code) || is_string($code) ? $code : null,
            description: is_string($payload['description'] ?? null) ? $payload['description'] : null,
            invalidProperty: is_array($payload['invalidProperty'] ?? null) || is_string($payload['invalidProperty'] ?? null)
                ? $payload['invalidProperty']
                : null,
        );
    }
}
