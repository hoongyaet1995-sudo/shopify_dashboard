<?php
namespace App\Http\Controllers;

use App\Models\Customer;         // Import the Model
use App\Models\MarketplaceOrder; // Import the Model
use App\Models\MarketplaceUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // For Shopify API calls
use Illuminate\Support\Facades\Http;

class MarketplaceOrderController extends Controller
{
    public function index(Request $request)
    {
        if (! session()->has('customer_id')) {
            return redirect('/login')->with('error', 'Please login.');
        }

        $days = $request->query('range', 7);

        // 1. Use the Model to find the customer
        $customer = Customer::find(session('customer_id'));

        // 2. Use the Model with a query builder
        $marketplace_order = MarketplaceOrder::whereBetween('created_at', [
            Carbon::now()->subDays((int) $days),
            Carbon::now(),
        ])
            ->orderBy('created_at', 'desc') // Models make ordering easy
            ->get();

        return view('marketplace-order', compact('customer', 'marketplace_order', 'days'));
    }

    // New Sync Action
    public function syncOrders(Request $request)
    {
        $marketplace_user_id = session('marketplace_user_id');

        // 1. Fetch connected merchants that have an access token
        $merchants = MarketplaceUser::whereNotNull('marketplace_access_token')
            ->get();

        if ($merchants->isEmpty()) {
            return response()->json(['message' => 'No authorized merchants found.'], 404);
        }

        foreach ($merchants as $merchant) {
            // 2. Logic to fetch from Shopify would go here
            // Example Placeholder:
            // $response = Http::withHeaders(['X-Shopify-Access-Token' => $merchant->marketplace_access_token])
            //    ->get("https://{$merchant->marketplace_shop_name}.myshopify.com/admin/api/2024-01/orders.json");

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $merchant->marketplace_access_token,
            ])->post(
                "https://{$merchant->marketplace_shop_name}.myshopify.com/admin/api/2024-01/graphql.json",
                [
                    'query' => '
                        query {
                            orders(first: 10) {
                                edges {
                                    node {
                                        id
                                        name
                                        createdAt
                                    }
                                }
                            }
                        }
                    ',
                ]
            );

            $data = $response->json();
            // Log::info('Shopify orders response', $data);

            $orders = $data['data']['orders']['edges'] ?? [];

            foreach ($orders as $order) {
                $node = $order['node'];

                MarketplaceOrder::updateOrInsert(
                    ['marketplace_order_id' => $node['id']], // Unique condition
                    [
                        // Assign the order to the specific merchant currently in the loop
                        'user_id'                => $merchant->id,
                        'marketplace_user_id'    => $merchant->marketplace_user_id,
                        'marketplace_invoice_id' => $node['name'],
                        'created_at'             => Carbon::parse($node['createdAt'])->toDateTimeString(),
                        'updated_at'             => now(), // Good practice to track updates
                    ]
                );
            }

        }

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully from all connected stores.',
        ]);
    }
}
