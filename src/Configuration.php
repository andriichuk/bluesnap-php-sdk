<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap;

use InvalidArgumentException;

final readonly class Configuration
{
    public function __construct(
        public string $username,
        #[\SensitiveParameter]
        public string $password,
        public Environment $environment = Environment::Sandbox,
        public string $apiVersion = '3.0',
        public string $userAgent = 'andriichuk/bluesnap-php-sdk',
    ) {
        if (trim($this->username) === '') {
            throw new InvalidArgumentException('The BlueSnap username must not be empty.');
        }

        if ($this->password === '') {
            throw new InvalidArgumentException('The BlueSnap password must not be empty.');
        }

        if (! preg_match('/^\d+\.\d+$/', $this->apiVersion)) {
            throw new InvalidArgumentException('The BlueSnap API version must use the major.minor format.');
        }
    }

    public function baseUri(): string
    {
        return $this->environment->value.'/services/2';
    }
}
