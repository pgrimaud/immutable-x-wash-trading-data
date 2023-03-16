<?php

declare(strict_types=1);

namespace App\Helper;

class OrderHelper
{
    public static function isSellOrder(array $orderPayload): bool
    {
        return $orderPayload['sell']['type'] === 'ERC721';
    }
}
