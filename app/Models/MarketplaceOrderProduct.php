<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceOrderProduct extends Model
{
    protected $table = 'marketplace_order_product';

    public $timestamps = false;

    protected $fillable = [
        'marketplace_order_id',
        'product_id',
        'product_name',
        'product_sku',
        'product_image',
        'quantity_purchase',
        'vendor',           // Added from GraphQL
        'unit_price',       // Added from originalUnitPriceSet
        'discounted_total', // Added from discountedTotalSet
        'currency',         // Useful to track if you sell in multiple currencies
    ];

    /**
     * Relationship back to the Order
     */
    public function order()
    {
        return $this->belongsTo(MarketplaceOrder::class, 'marketplace_order_id', 'marketplace_order_id');
    }
}
