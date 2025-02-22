<?php

namespace App\Services;

use App\Models\Product;


class ProductManageCostServices
{
    public function __construct(
    ) {
    }

    /**
     * Update the cost of the product
     *
     * @param Product $product
     * @param $newPieceQuantity
     * @param $newCost
     */
    public function updateCost(Product $product, $newPieceQuantity, $newCost)
    {
        $product->loadSum('stockItems', 'piece_quantity');
        $existQuantity = $product->packetsQuantity;
        $newQuantity = $newPieceQuantity / $product->packet_to_piece;
        $productCost = ($existQuantity * $product->packet_cost + $newQuantity * $newCost) / ($existQuantity + $newQuantity);
        $product->packet_cost = $productCost;
        $product->save();
    }
}
