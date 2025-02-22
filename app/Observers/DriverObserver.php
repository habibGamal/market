<?php

namespace App\Observers;

use App\Models\Driver;
use App\Models\DriverAccount;

class DriverObserver
{
    public function created(Driver $driver): void
    {
        DriverAccount::create([
            'driver_id' => $driver->id,
            'balance' => 0,
        ]);
    }
}
