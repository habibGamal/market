<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\Order;
use Illuminate\Support\Collection;

class OfferService
{
    public function calculateOrderDiscount(Order $order): array
    {
        $appliedOffers =  Offer::query()
            ->where('is_active', true)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->get()
            ->filter(fn (Offer $offer) => $this->isOrderEligibleForOffer($order, $offer));
        $discount = $appliedOffers->sum(fn (Offer $offer) => $this->calculateDiscountValue($order, $offer));
        return [
            'applied_offers' => $appliedOffers,
            'discount' => $discount,
        ];
    }

    private function isOrderEligibleForOffer(Order $order, Offer $offer): bool
    {
        $conditions = $offer->instructions['conditions'];

        // Check business type condition
        if (isset($conditions['in_business_type']) && !empty($conditions['in_business_type'])) {
            if (!in_array($order->customer->business_type_id, (array)$conditions['in_business_type'])) {
                return false;
            }
        }

        // Check location conditions
        if (isset($conditions['in_gov']) && !empty($conditions['in_gov'])) {
            if (!in_array($order->customer->gov_id, (array)$conditions['in_gov'])) {
                return false;
            }
        }

        if (isset($conditions['in_cities']) && !empty($conditions['in_cities'])) {
            if (!in_array($order->customer->city_id, (array)$conditions['in_cities'])) {
                return false;
            }
        }

        if (isset($conditions['in_areas']) && !empty($conditions['in_areas'])) {
            if (!in_array($order->customer->area_id, (array)$conditions['in_areas'])) {
                return false;
            }
        }

        // Check minimum requirements
        if (isset($conditions['min_total_packets'])) {
            if ($order->items->sum('packets_quantity') < $conditions['min_total_packets']) {
                return false;
            }
        }

        if (isset($conditions['min_customer_points'])) {
            if ($order->customer->rating_points < $conditions['min_customer_points']) {
                return false;
            }
        }

        if (isset($conditions['min_total_order'])) {
            if ($order->total < $conditions['min_total_order']) {
                return false;
            }
        }

        // Check category conditions
        if (isset($conditions['categories'])) {
            if (!$this->checkCategoryConditions($order, $conditions['categories'])) {
                return false;
            }
        }

        // Check brand conditions
        if (isset($conditions['brands'])) {
            if (!$this->checkBrandConditions($order, $conditions['brands'])) {
                return false;
            }
        }

        // Check product conditions
        if (isset($conditions['products'])) {
            if (!$this->checkProductConditions($order, $conditions['products'])) {
                return false;
            }
        }

        return true;
    }

    private function checkCategoryConditions(Order $order, array $conditions): bool
    {
        if ($conditions['strategy'] === 'general') {
            $categoryGroups = $order->items
                ->groupBy(fn ($item) => $item->product->category_id);

            if ($categoryGroups->count() < $conditions['general']['number_of_diff_categories']) {
                return false;
            }

            if (isset($conditions['general']['min_value'])) {
                $categoryTotals = $categoryGroups->map->sum('total');
                if ($categoryTotals->sum() < $conditions['general']['min_value']) {
                    return false;
                }
            }

            if (isset($conditions['general']['min_packets_quantity'])) {
                $categoryPackets = $categoryGroups->map->sum('packets_quantity');
                if ($categoryPackets->sum() < $conditions['general']['min_packets_quantity']) {
                    return false;
                }
            }
        } else {
            foreach ($conditions['specific'] as $categoryId => $requirements) {
                $categoryItems = $order->items->filter(fn ($item) => $item->product->category_id == $categoryId);

                if (isset($requirements['min_value'])) {
                    if ($categoryItems->sum('total') < $requirements['min_value']) {
                        return false;
                    }
                }

                if (isset($requirements['min_packets_quantity'])) {
                    if ($categoryItems->sum('packets_quantity') < $requirements['min_packets_quantity']) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function checkBrandConditions(Order $order, array $conditions): bool
    {
        if ($conditions['strategy'] === 'general') {
            $brandGroups = $order->items
                ->groupBy(fn ($item) => $item->product->brand_id);

            if ($brandGroups->count() < $conditions['general']['number_of_diff_brands']) {
                return false;
            }

            if (isset($conditions['general']['min_value'])) {
                $brandTotals = $brandGroups->map->sum('total');
                if ($brandTotals->sum() < $conditions['general']['min_value']) {
                    return false;
                }
            }

            if (isset($conditions['general']['min_packets_quantity'])) {
                $brandPackets = $brandGroups->map->sum('packets_quantity');
                if ($brandPackets->sum() < $conditions['general']['min_packets_quantity']) {
                    return false;
                }
            }
        } else {
            foreach ($conditions['specific'] as $brandId => $requirements) {
                $brandItems = $order->items->filter(fn ($item) => $item->product->brand_id == $brandId);

                if (isset($requirements['min_value'])) {
                    if ($brandItems->sum('total') < $requirements['min_value']) {
                        return false;
                    }
                }

                if (isset($requirements['min_packets_quantity'])) {
                    if ($brandItems->sum('packets_quantity') < $requirements['min_packets_quantity']) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function checkProductConditions(Order $order, array $conditions): bool
    {
        if ($conditions['strategy'] === 'general') {
            $productGroups = $order->items
                ->groupBy(fn ($item) => $item->product_id);

            if ($productGroups->count() < $conditions['general']['number_of_diff_products']) {
                return false;
            }

            if (isset($conditions['general']['min_value'])) {
                $productTotals = $productGroups->map->sum('total');
                if ($productTotals->sum() < $conditions['general']['min_value']) {
                    return false;
                }
            }

            if (isset($conditions['general']['min_packets_quantity'])) {
                $productPackets = $productGroups->map->sum('packets_quantity');
                if ($productPackets->sum() < $conditions['general']['min_packets_quantity']) {
                    return false;
                }
            }
        } else {
            foreach ($conditions['specific'] as $productId => $requirements) {
                $productItems = $order->items->filter(fn ($item) => $item->product_id == $productId);

                if (isset($requirements['min_value'])) {
                    if ($productItems->sum('total') < $requirements['min_value']) {
                        return false;
                    }
                }

                if (isset($requirements['min_packets_quantity'])) {
                    if ($productItems->sum('packets_quantity') < $requirements['min_packets_quantity']) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function calculateDiscountValue(Order $order, Offer $offer): float
    {
        $discount = $offer->instructions['discount'];

        if ($discount['type'] === 'percent') {
            return $order->total * ($discount['value'] / 100);
        }

        return (float) $discount['value'];
    }
}
