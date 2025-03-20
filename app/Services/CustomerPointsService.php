<?php

namespace App\Services;

use App\Enums\SettingKey;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerPointsService
{
    /**
     * Add points to a customer
     *
     * @param Customer $customer The customer to add points to
     * @param float $amount The amount of points to add
     * @param string|null $reason Optional reason for adding points
     * @return bool
     */
    public function addPoints(Customer $customer, float $total): bool
    {
        return DB::transaction(function () use ($customer, $total) {
            $ratingPointsPercent = (float) settings(SettingKey::RATING_POINTS_PERCENT, 0);
            $pointsToAdd = $total * ($ratingPointsPercent / 100);
            $customer->rating_points += $pointsToAdd;
            $customer->rating_points = (int) $customer->rating_points;
            return $customer->save();
        });
    }

    /**
     * Remove points from a customer
     *
     * @param Customer $customer The customer to remove points from
     * @param float $amount The amount of points to remove
     * @param string|null $reason Optional reason for removing points
     * @return bool
     * @throws \Exception if customer doesn't have enough points
     */
    public function removePoints(Customer $customer, float $total): bool
    {
        return DB::transaction(function () use ($customer, $total) {
            $ratingPointsPercent = (float) settings(SettingKey::RATING_POINTS_PERCENT, 0);
            $pointsToRemove = $total * ($ratingPointsPercent / 100);
            $customer->rating_points -= $pointsToRemove;
            $customer->rating_points = (int) $customer->rating_points;
            return $customer->save();
        });
    }
}
