<?php

namespace App\Observers;

use App\Models\FixedAsset;
use App\Services\VaultService;
use App\Services\WorkDayService;
use Illuminate\Support\Facades\DB;

class FixedAssetObserver
{
    public function creating(FixedAsset $fixedAsset): void
    {
        $fixedAsset->accountant_id = auth()->id();
    }

    /**
     * Handle the FixedAsset "created" event.
     */
    public function created(FixedAsset $fixedAsset): void
    {
        DB::transaction(function () use ($fixedAsset) {
            app(VaultService::class)->remove($fixedAsset->value);
            app(WorkDayService::class)->update();
        });
    }

    /**
     * Handle the FixedAsset "deleted" event.
     */
    public function deleted(FixedAsset $fixedAsset): void
    {
        DB::transaction(function () use ($fixedAsset) {
            app(VaultService::class)->add($fixedAsset->value);
            app(WorkDayService::class)->update();
        });
    }
}
