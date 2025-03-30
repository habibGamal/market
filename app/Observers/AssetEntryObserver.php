<?php

namespace App\Observers;

use App\Models\AssetEntry;
use App\Services\VaultService;
use App\Services\WorkDayService;

class AssetEntryObserver
{
    public function creating(AssetEntry $assetEntry): void
    {
        // Set the officer_id to the currently authenticated user if not already set
        if (!$assetEntry->officer_id) {
            $assetEntry->officer_id = auth()->id();
        }
    }

    public function created(AssetEntry $assetEntry): void
    {
        \DB::transaction(function () use ($assetEntry) {
            // Add to vault immediately when a new asset entry is created
            app(VaultService::class)->add($assetEntry->value);
            app(WorkDayService::class)->update();
        });
    }

    /**
     * Handle the AssetEntry "deleted" event.
     */
    public function deleted(AssetEntry $assetEntry): void
    {
        \DB::transaction(function () use ($assetEntry) {
            // Remove from vault when asset entry is deleted
            app(VaultService::class)->remove($assetEntry->value);
            app(WorkDayService::class)->update();
        });
    }
}
