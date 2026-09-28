<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap;

use InvalidArgumentException;

final readonly class Configuration
{
    private string $checkoutHost;

    public function __construct(
        public string $username,
        #[\SensitiveParameter]
        public string $password,
        public string $merchantId,
        public Environment $environment = Environment::Sandbox,
        public string $apiVersion = '3.0',
        public string $userAgent = 'andriichuk/bluesnap-php-sdk',
        ?string $checkoutHost = null,
    ) {
        if (trim($this->username) === '') {
            throw new InvalidArgumentException('The BlueSnap username must not be empty.');
        }

        if ($this->password === '') {
            throw new InvalidArgumentException('The BlueSnap password must not be empty.');
        }

        if (preg_match('/^[1-9]\d*$/', $this->merchantId) !== 1) {
            throw new InvalidArgumentException('The BlueSnap merchant ID must be a positive integer.');
        }

        if (! preg_match('/^\d+\.\d+$/', $this->apiVersion)) {
            throw new InvalidArgumentException('The BlueSnap API version must use the major.minor format.');
        }

        $this->checkoutHost = rtrim($checkoutHost ?? $this->environment->checkoutHost(), '/');

        if (! self::isValidHost($this->checkoutHost)) {
            throw new InvalidArgumentException('The BlueSnap checkout host must be an HTTPS origin without a path, query, or fragment.');
        }
    }

    public function baseUri(): string
    {
        return $this->environment->value.'/services/2';
    }

    public function checkoutHost(): string
    {
        return $this->checkoutHost;
    }

    private static function isValidHost(string $host): bool
    {
        $parts = parse_url($host);

        return is_array($parts)
            && ($parts['scheme'] ?? null) === 'https'
            && is_string($parts['host'] ?? null)
            && ($parts['host'] ?? '') !== ''
            && ! isset($parts['path'])
            && ! isset($parts['query'])
            && ! isset($parts['fragment'])
            && ! isset($parts['user'])
            && ! isset($parts['pass']);
    }
}
