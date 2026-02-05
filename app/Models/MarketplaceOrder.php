<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceOrder extends Model
{
    protected $table   = 'marketplace_order';
    public $timestamps = true;

    protected $fillable = [
        'marketplace_order_id',
        'marketplace_invoice_id',
        'marketplace_user_id',
        'user_id',
        'total_price',
        'subtotal_price', // Ensure this is here
        'total_tax',      // Ensure this is here
        'total_shipping', // Ensure this is here
        'currency',
        'customer_name',
        'customer_email',
        'shipping_address', // Or shipping_address_json
        'tracking_number',
        'tracking_url',
        'financial_status',
        'fulfillment_status',
        'created_at',
        'updated_at',
    ];

    /**
     * This automatically converts the JSON string from the DB
     * into a PHP array when you access it.
     */
    protected $casts = [
        'shipping_address_json' => 'array',
        'created_at'            => 'datetime',
    ];

    public function products()
    {
        return $this->hasMany(MarketplaceOrderProduct::class, 'marketplace_order_id', 'marketplace_order_id');
    }
}
