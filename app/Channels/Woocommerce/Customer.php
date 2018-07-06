<?php

namespace App\Channels\Woocommerce;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Channels\Woocommerce;
use App\CRM;

class Customer
{
    public static $index = 'customers';
    public static $type = 'woocommerce';
}