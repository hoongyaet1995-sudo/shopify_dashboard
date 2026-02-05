<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    // Show the login form
    public function showForm()
    {
        return view('login');
    }

    // Handle the login request
    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        $customer = DB::table('customers')->where('username', $username)->first();

        if ($customer && $password == $customer->password) {
            session(['customer_id' => $customer->customers_id]);

            return redirect('/marketplace-merchant'); // It's better to redirect than return a string
        }

        return back()->with('error', 'Invalid username or password');
    }

    public function logout()
    {
        session()->forget('customer_id');
        return redirect('/login')->with('success', 'You have been logged out.');
    }
}
