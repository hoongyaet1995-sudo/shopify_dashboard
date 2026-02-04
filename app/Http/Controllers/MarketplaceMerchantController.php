<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class MarketplaceMerchantController extends Controller
{
    public function index()
    {
        // 1. Check if the session has our ID
        if (! session()->has('customer_id')) {
            return redirect('/login')->with('error', 'Please login to access the dashboard.');
        }

        // 2. Fetch the specific customer data
        $customer = DB::table('customers')
            ->where('customers_id', session('customer_id'))
            ->first();

        // 3. Fetch ALL merchants for this user
        $merchants = DB::table('marketplace_user')
            ->where('marketplace_user_id', session('customer_id')) // Assuming customer_id links them
            ->get();

        // 4. Send both variables to the view
        return view('marketplace-merchant', compact('customer', 'merchants'));
    }
}
