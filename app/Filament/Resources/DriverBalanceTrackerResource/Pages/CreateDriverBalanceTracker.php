<?php

namespace App\Filament\Resources\DriverBalanceTrackerResource\Pages;

use App\Enums\BalanceOperation;
use App\Filament\Resources\DriverBalanceTrackerResource;
use App\Models\Driver;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateDriverBalanceTracker extends CreateRecord
{
    protected static string $resource = DriverBalanceTrackerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $driver = Driver::find($data['driver_id']);
        $data['balance_before'] = $driver->account->balance;

        $operation = BalanceOperation::from($data['operation']);
        $amount = $data['amount'];

        if ($operation === BalanceOperation::INCREMENT) {
            $data['balance_after'] = $data['balance_before'] + $amount;
        } else {
            $data['balance_after'] = $data['balance_before'] - $amount;
        }

        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $driver = Driver::find($record->driver_id);

        DB::transaction(function () use ($driver, $record) {
            $operation = $record->operation;
            $amount = $record->amount;

            if ($operation === BalanceOperation::INCREMENT) {
                $driver->account()->increment('balance', $amount);
            } else {
                $driver->account()->decrement('balance', $amount);
            }
        });
    }
}
