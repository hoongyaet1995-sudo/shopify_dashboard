<?php
namespace App\Http\Controllers;

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
        
        $exists = DB::table('marketplace_user')
        ->where('marketplace_shop_name', $request->marketplace_shop_name)
        ->exists();

        if(!$exists){
            // 2. Save to database
            DB::table('marketplace_user')->insert([
                'marketplace_user_id'   => session('customer_id'),
                'marketplace_user_name' => $request->marketplace_user_name,
                'marketplace_shop_name' => $request->marketplace_shop_name,
                'marketplace_state' => $state,
                'created_at'            => now(),
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
        // 1️⃣ Validate input
        $request->validate([
            'store_id' => 'required|integer|exists:marketplace_user,marketplace_user_id',
        ]);

        // 2️⃣ Fetch merchant
        
        $merchant = DB::table('marketplace_user')
            ->where('marketplace_user_id', $request->store_id)
            ->first();

        $exist = DB::table('marketplace_user')
            ->where('marketplace_user_id', $request->store_id)
            ->exists();
            
        if($exist){
            // 3️⃣ (Later) Shopify verification logic goes here
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
            // For now, just return success

            // 2. Save to database
            DB::table('marketplace_user')
            ->where('marketplace_user_id', $request->store_id)
            ->update([
                'marketplace_state' => $state,
                'updated_at'        => now(),
            ]);

            // 3. Construct the Shopify Install URL
            $install_url = "https://{$merchant->marketplace_shop_name}.myshopify.com/admin/oauth/authorize?{$params}";

            // 4. Return the URL to AJAX instead of using header()
            return response()->json([
                'success'      => true,
                'redirect_url' => $install_url,
            ]);
        
        }
        else{
            return 'No marketplace user found.';
        }
    }

    public function callback(Request $request)
    {
        $params = $request->all();
        $api_key = config('services.marketplace.shopify.api_key'); 
        $api_secret = config('services.marketplace.shopify.api_secret'); 

        // 1. Basic Security Check (Verify state)
        if ($params['state'] !== session('shopify_state')) {
            return redirect('/dashboard')->with('error', 'State mismatch. Possible CSRF attack.');
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
            DB::table('marketplace_user')
                ->where('marketplace_shop_name', str_replace('.myshopify.com', '', $params['shop']))
                ->update(['marketplace_access_token' => $access_token]);

            return redirect('/dashboard')->with('success', 'Shopify Store Connected!');
        }

        return redirect('/dashboard')->with('error', 'Failed to get access token.');
    }
}
