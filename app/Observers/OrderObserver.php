<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\OrderServices;

class OrderObserver
{
    public function __construct(private OrderServices $orderServices)
    {
    }

}
