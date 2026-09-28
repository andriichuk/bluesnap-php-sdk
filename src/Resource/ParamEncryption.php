<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Resource;

use Andriichuk\BlueSnap\BlueSnapClient;
use Andriichuk\BlueSnap\Http\Response;
use InvalidArgumentException;

final readonly class ParamEncryption
{
    public function __construct(private BlueSnapClient $client) {}

    /**
     * @param  non-empty-array<string, scalar>  $parameters
     */
    public function encrypt(array $parameters): Response
    {
        if ($parameters === []) {
            throw new InvalidArgumentException('At least one BlueSnap parameter must be provided for encryption.');
        }

        $xml = '<param-encryption xmlns="http://ws.plimus.com"><parameters>';

        foreach ($parameters as $key => $value) {
            if (trim($key) === '') {
                throw new InvalidArgumentException('BlueSnap parameter encryption keys must not be empty.');
            }

            $xml .= '<parameter><param-key>'.self::escape($key).'</param-key><param-value>'
                .self::escape(self::stringify($value)).'</param-value></parameter>';
        }

        $xml .= '</parameters></param-encryption>';

        return $this->client->requestXml('POST', 'tools/param-encryption', $xml);
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private static function stringify(bool|float|int|string $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
