<?php

namespace App\Channels\Woocommerce;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Channels\Woocommerce;

class Order
{
    public static $index = 'woocommerce';
    public static $type = 'orders';
}