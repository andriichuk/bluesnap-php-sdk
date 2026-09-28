<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\HostedPage;

use Andriichuk\BlueSnap\Configuration;
use InvalidArgumentException;

final readonly class HostedPageUrl
{
    public function __construct(private Configuration $configuration) {}

    public function build(HostedPageRequest $request): string
    {
        $merchantId = $this->configuration->merchantId;

        if ($merchantId === null) {
            throw new InvalidArgumentException('A BlueSnap merchant ID is required to build a Hosted Payment Page URL.');
        }

        $plan = 'plan'.$request->planId;
        $query = $request->quantity === null ? $plan : $plan.'='.$request->quantity;
        $parameters = ['merchantid' => $merchantId];

        if ($request->merchantTransactionId !== null) {
            $parameters['merchanttransactionid'] = $request->merchantTransactionId;
        }

        if ($request->email !== null) {
            $parameters['email'] = $request->email;
        }

        if ($request->enc !== null) {
            $parameters['enc'] = $request->enc;
        }

        return $this->configuration->checkoutHost().'/buynow/checkout?'.$query.'&'
            .http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }
}
