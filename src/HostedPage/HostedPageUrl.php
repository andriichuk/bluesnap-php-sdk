<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\HostedPage;

use InvalidArgumentException;

final class HostedPageUrl
{
    public static function build(
        string $host,
        string $merchantId,
        int $planId,
        ?string $merchantTransactionId = null,
        ?string $email = null,
        ?string $enc = null,
        ?int $quantity = null,
    ): string {
        $host = rtrim($host, '/');

        $hostParts = parse_url($host);

        if (! is_array($hostParts)
            || ($hostParts['scheme'] ?? null) !== 'https'
            || ! is_string($hostParts['host'] ?? null)
            || ($hostParts['host'] ?? '') === ''
            || isset($hostParts['path'])
            || isset($hostParts['query'])
            || isset($hostParts['fragment'])
            || isset($hostParts['user'])
            || isset($hostParts['pass'])) {
            throw new InvalidArgumentException('The BlueSnap checkout host must be an HTTPS origin without a path, query, or fragment.');
        }

        if (preg_match('/^[1-9]\d*$/', $merchantId) !== 1) {
            throw new InvalidArgumentException('The BlueSnap merchant ID must be a positive integer.');
        }

        if ($planId < 1) {
            throw new InvalidArgumentException('The BlueSnap plan ID must be a positive integer.');
        }

        if ($quantity !== null && $quantity < 1) {
            throw new InvalidArgumentException('The BlueSnap Hosted Payment Page quantity must be a positive integer.');
        }

        if ($merchantTransactionId !== null && ($merchantTransactionId === '' || strlen($merchantTransactionId) > 50)) {
            throw new InvalidArgumentException('The BlueSnap merchant transaction ID must contain between 1 and 50 characters.');
        }

        $plan = 'plan'.$planId;
        $query = $quantity === null ? $plan : $plan.'='.$quantity;
        $parameters = ['merchantid' => $merchantId];

        if ($merchantTransactionId !== null) {
            $parameters['merchanttransactionid'] = $merchantTransactionId;
        }

        if ($email !== null) {
            $parameters['email'] = $email;
        }

        if ($enc !== null) {
            $parameters['enc'] = $enc;
        }

        return $host.'/buynow/checkout?'.$query.'&'.http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }
}
