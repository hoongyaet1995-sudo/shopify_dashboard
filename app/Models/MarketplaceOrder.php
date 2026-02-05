<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceOrder extends Model
{
    protected $table = 'marketplace_order';
    // If you don't have created_at/updated_at columns, set this to false
    public $timestamps = true;

    protected $fillable = [
        'marketplace_order_id',
        'marketplace_invoice_id',
        'marketplace_user_id',
        'created_at',
        // Add any other columns you intend to save here
    ];
}
