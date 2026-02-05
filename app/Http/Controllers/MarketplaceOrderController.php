<?php
namespace App\Http\Controllers;

use App\Models\Customer;         // Import the Model
use App\Models\MarketplaceOrder; // Import the Model
use App\Models\MarketplaceOrderProduct;
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

        $days   = $request->query('range', 7);
        $status = $request->query('status', 'all'); // Get the filter status

        $customer = Customer::find(session('customer_id'));

        // Start the query builder
        $query = MarketplaceOrder::with('products')
            ->whereBetween('created_at', [
                Carbon::now()->subDays((int) $days),
                Carbon::now(),
            ]);

        // Apply Filter Logic
        if ($status === 'unfulfilled') {
            $query->where('fulfillment_status', 'UNFULFILLED');
        } elseif ($status === 'unpaid') {
            $query->where('financial_status', '!=', 'PAID');
        } elseif ($status === 'open') {
            // Example: Open might mean not archived
            $query->where('fulfillment_status', '!=', 'FULFILLED');
        }

        $marketplace_order = $query->orderBy('id', 'desc')->get();

        return view('marketplace-order', compact('customer', 'marketplace_order', 'days', 'status'));
    }

    // New Sync Action
    public function syncOrders(Request $request)
    {
        $merchants = MarketplaceUser::whereNotNull('marketplace_access_token')->get();

        foreach ($merchants as $merchant) {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $merchant->marketplace_access_token,
            ])->post("https://{$merchant->marketplace_shop_name}.myshopify.com/admin/api/2024-01/graphql.json", [
                'query' => '
                query {
                  orders(first: 10, sortKey: CREATED_AT, reverse: true) {
                    edges {
                      node {
                        id
                        name
                        createdAt
                        displayFinancialStatus
                        displayFulfillmentStatus
                        currencyCode
                        customer {
                          firstName
                          lastName
                          email
                        }
                        shippingAddress {
                          address1
                          city
                          province
                          country
                          zip
                        }
                        totalPriceSet { shopMoney { amount } }
                        subtotalPriceSet { shopMoney { amount } }
                        totalShippingPriceSet { shopMoney { amount } }
                        totalTaxSet { shopMoney { amount } }
                        lineItems(first: 20) {
                            edges {
                                node {
                                id
                                title
                                sku
                                quantity
                                variant {
                                    id
                                    image {
                                        url
                                    }
                                    product {
                                        featuredImage {
                                            url
                                        }
                                    }
                                }
                                originalUnitPriceSet { shopMoney { amount } }
                                discountedTotalSet { shopMoney { amount } }
                                }
                            }
                        }
                        fulfillments {
                          trackingInfo {
                            number
                            url
                          }
                        }
                      }
                    }
                  }
                }
            ',
            ]);

            $data = $response->json();

            if (isset($data['errors'])) {
                \Log::error("Shopify Query Error for " . $merchant->marketplace_shop_name, $data['errors']);
                continue;
            }

            $orders = $data['data']['orders']['edges'] ?? [];

            foreach ($orders as $order) {
                $node = $order['node'];

                // Safely handle potential nulls
                $shippingAddress = $node['shippingAddress'] ? json_encode($node['shippingAddress']) : null;

                // Check if fulfillment and tracking info exists before accessing index [0]
                $trackingNumber = null;
                $trackingUrl    = null;
                if (! empty($node['fulfillments']) && ! empty($node['fulfillments'][0]['trackingInfo'])) {
                    $trackingNumber = $node['fulfillments'][0]['trackingInfo'][0]['number'] ?? null;
                    $trackingUrl    = $node['fulfillments'][0]['trackingInfo'][0]['url'] ?? null;
                }

                // 1. Save Order Main Data
                MarketplaceOrder::updateOrInsert(
                    ['marketplace_order_id' => $node['id']],
                    [
                        'user_id'                => $merchant->id,
                        'marketplace_user_id'    => $merchant->marketplace_user_id,
                        'marketplace_invoice_id' => $node['name'],
                        'financial_status'       => $node['displayFinancialStatus'],
                        'fulfillment_status'     => $node['displayFulfillmentStatus'],
                        'total_price'            => $node['totalPriceSet']['shopMoney']['amount'],
                        'subtotal_price'         => $node['subtotalPriceSet']['shopMoney']['amount'],
                        'total_tax'              => $node['totalTaxSet']['shopMoney']['amount'],
                        'total_shipping'         => $node['totalShippingPriceSet']['shopMoney']['amount'],
                        'currency'               => $node['currencyCode'],
                        'customer_name'          => ($node['customer']['firstName'] ?? '') . ' ' . ($node['customer']['lastName'] ?? ''),
                        'customer_email'         => $node['customer']['email'] ?? null,
                        'shipping_address'       => $shippingAddress,
                        'tracking_number'        => $trackingNumber,
                        'tracking_url'           => $trackingUrl,
                        'created_at'             => \Carbon\Carbon::parse($node['createdAt'])->toDateTimeString(),
                        'updated_at'             => now(),
                    ]
                );

                // 2. Save Line Items
                $lineItems = $node['lineItems']['edges'] ?? [];
                foreach ($lineItems as $item) {
                    $productNode = $item['node'];

                    // Logic: Use variant image if exists, else use product featured image, else null
                    $imageUrl = $productNode['variant']['image']['url'] ?? $productNode['variant']['product']['featuredImage']['url'] ?? null;

                    MarketplaceOrderProduct::updateOrInsert(
                        [
                            'marketplace_order_id' => $node['id'],
                            'product_id'           => $productNode['id'],
                        ],
                        [
                            'product_name'      => $productNode['title'],
                            'product_sku'       => $productNode['sku'],
                            'product_image'     => $imageUrl,
                            'quantity_purchase' => $productNode['quantity'],
                            'unit_price'        => $productNode['originalUnitPriceSet']['shopMoney']['amount'],
                            'discounted_total'  => $productNode['discountedTotalSet']['shopMoney']['amount'],
                        ]
                    );
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Full data sync completed.']);
    }
}
