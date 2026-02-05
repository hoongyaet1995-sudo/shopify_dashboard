<?php
namespace App\Http\Controllers;

use App\Models\MarketplaceUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchantController extends Controller
{
    // Returns the HTML for the AJAX popup
    public function create()
    {
        return view('partials.merchant-form')->render();
    }

    // Saves the data to the database
    public function store(Request $request)
    {
        $request->validate([
            'marketplace_user_name' => 'required',
            'marketplace_shop_name' => 'required',
        ]);

        // 1. Prepare Shopify OAuth Data
        $api_key = config('services.marketplace.shopify.api_key');
        $state   = bin2hex(random_bytes(16));
        session(['shopify_state' => $state]); // Store state to verify in callback

        $shopify_callback = "https://coalitional-liturgistic-miguelina.ngrok-free.dev/shopify/callback";

        $params = http_build_query([
            'client_id'    => $api_key,
            'scope'        => 'read_products,write_products,read_orders,read_customers',
            'redirect_uri' => $shopify_callback,
            'state'        => $state,
        ]);

        $exists = MarketplaceUser::where('marketplace_shop_name', $request->marketplace_shop_name)
            ->exists();

        if (! $exists) {
            // 2. Save using Eloquent Model
            MarketplaceUser::create([
                'marketplace_user_id'   => session('customer_id'),
                'marketplace_user_name' => $request->marketplace_user_name,
                'marketplace_shop_name' => $request->marketplace_shop_name,
                'marketplace_state'     => $state,
                // 'created_at' => now(), // You can remove this if $timestamps = true
            ]);
        }

        // 3. Construct the Shopify Install URL
        $install_url = "https://{$request->marketplace_shop_name}.myshopify.com/admin/oauth/authorize?{$params}";

        // 4. Return the URL to AJAX instead of using header()
        return response()->json([
            'success'      => true,
            'redirect_url' => $install_url,
        ]);
    }

    public function authorize(Request $request)
    {
        // 1️⃣ 验证输入
        $request->validate([
            'store_id' => 'required|integer|exists:marketplace_user,marketplace_user_id',
        ]);

        // 2️⃣ 获取商家数据
        $merchant = DB::table('marketplace_user')
            ->where('marketplace_user_id', $request->store_id)
            ->first();

        if ($merchant) {
            $api_key = config('services.marketplace.shopify.api_key');
            $state   = bin2hex(random_bytes(16));
            session(['shopify_state' => $state]);

            $shopify_callback = "https://coalitional-liturgistic-miguelina.ngrok-free.dev/shopify/callback";

            $params = http_build_query([
                'client_id'    => $api_key,
                'scope'        => 'read_products,write_products,read_orders,read_customers',
                'redirect_uri' => $shopify_callback,
                'state'        => $state,
            ]);

            // 更新数据库中的 state 记录
            DB::table('marketplace_user')
                ->where('marketplace_user_id', $request->store_id)
                ->update([
                    'marketplace_state' => $state,
                    'updated_at'        => now(),
                ]);

            // 构建 Shopify 授权地址
            $install_url = "https://{$merchant->marketplace_shop_name}.myshopify.com/admin/oauth/authorize?{$params}";

            return response()->json([
                'success'      => true,
                'redirect_url' => $install_url,
            ]);

        } else {
            // 关键点：返回 404 状态码和 JSON 错误信息
            return response()->json([
                'success' => false,
                'message' => 'No marketplace user found.',
            ], 404);
        }
    }

    public function callback(Request $request)
    {
        $params     = $request->all();
        $api_key    = config('services.marketplace.shopify.api_key');
        $api_secret = config('services.marketplace.shopify.api_secret');

        // 1. Basic Security Check (Verify state)
        if ($params['state'] !== session('shopify_state')) {
            return redirect('/marketplace-merchant')->with('error', 'State mismatch. Possible CSRF attack.');
        }

        // 2. Exchange 'code' for 'access_token'
        $token_url = "https://{$params['shop']}/admin/oauth/access_token";

        $response = \Illuminate\Support\Facades\Http::post($token_url, [
            'client_id'     => $api_key,
            'client_secret' => $api_secret,
            'code'          => $params['code'],
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            $access_token = $responseData['access_token'];

            // Insert the log
            DB::table('api_log')->insert([
                'log_action' => 'shopify_auth_token',
                'log_data'   => json_encode($responseData), // Convert Array to JSON String
                'log_result' => 'success',
                'log_user'   => session('customer_id'),
                'created_at' => now(),
            ]);

            \Log::info('Shopify Response:', $response->json());

            // 3. Save the token to your marketplace_user table
            $shop_identifier = str_replace('.myshopify.com', '', $params['shop']);

            $affected = DB::table('marketplace_user')
                ->where('marketplace_shop_name', $shop_identifier)
                ->update([
                    'marketplace_access_token' => $access_token,
                    'marketplace_shop_id'      => $params['code'], // 再次提醒：建议确认字段类型
                ]);

            if ($affected === 0) {
                // 说明数据库里找不到 marketplace_shop_name 等于 $shop_identifier 的记录
                \Log::error("Update failed: No record found for " . $shop_identifier);
            }

            return redirect('/marketplace-merchant')->with('success', 'Shopify Store Connected!');
        }

        return redirect('/marketplace-merchant')->with('error', 'Failed to get access token.');
    }
}
