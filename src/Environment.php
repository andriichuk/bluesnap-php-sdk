<?php

declare(strict_types=1);

namespace Andriichuk\BlueSnap;

enum Environment: string
{
    case Sandbox = 'https://sandbox.bluesnap.com';
    case Production = 'https://ws.bluesnap.com';
}
