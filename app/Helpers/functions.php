<?php
use Illuminate\Support\Facades\DB;

if (! function_exists('getMarketplaceShopName')) {
    function getMarketplaceShopName($user_id, $marketplace_user_id)
    {
        $shop = DB::table('marketplace_user')
            ->where('marketplace_user_id', $marketplace_user_id)
            ->where('id', $user_id)
            ->first();

        if ($shop) {
            return strtoupper($shop->marketplace_shop_name);
        }

        return "Unknown Marketplace";
    }
}
