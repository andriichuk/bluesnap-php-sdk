<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap\Exception;

use Psr\Http\Client\ClientExceptionInterface;

final class TransportException extends BlueSnapException implements ClientExceptionInterface
{
}
