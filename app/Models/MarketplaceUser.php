<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceUser extends Model
{
    protected $table = 'marketplace_user';
    // If you don't have created_at/updated_at columns, set this to false
    public $timestamps = true;

    protected $fillable = [
        'marketplace_user_id',
        'marketplace_user_name',
        'marketplace_shop_name',
        'marketplace_state',
        'marketplace_access_token',
        'marketplace_shop_id',
        // Add any other columns you intend to save here
    ];
}
