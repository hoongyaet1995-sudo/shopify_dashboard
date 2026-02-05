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

/**
 * Returns Bootstrap class for Financial Status
 */
if (! function_exists('getFinancialStatusClass')) {
    function getFinancialStatusClass($status)
    {
        return match (strtoupper($status)) {
            'PAID'     => 'bg-secondary bg-opacity-10 text-dark', // Shopify uses subtle grey/green
            'PENDING'  => 'bg-warning bg-opacity-25 text-dark',
            'REFUNDED' => 'bg-danger bg-opacity-10 text-danger',
            default    => 'bg-light text-dark border',
        };
    }
}

/**
 * Returns Bootstrap class for Fulfillment Status
 */
if (! function_exists('getFulfillmentStatusClass')) {
    function getFulfillmentStatusClass($status)
    {
        return match (strtoupper($status)) {
            'FULFILLED'   => 'bg-secondary bg-opacity-10 text-dark',
            'UNFULFILLED' => 'bg-warning bg-opacity-25 text-dark',
            'PARTIAL'     => 'bg-info bg-opacity-10 text-dark',
            default       => 'bg-light text-dark border',
        };
    }
}
