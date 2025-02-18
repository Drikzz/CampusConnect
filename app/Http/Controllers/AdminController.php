<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Order; // Import the Order model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.adminlogin');
    }

    public function login(Request $request)
    {
        // Validate
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        // Try to login the user
        if (Auth::attempt($credentials, $request->remember)) {
            // Check if user is admin
            if (Auth::user()->is_admin) {
                return redirect()->intended('/admin/dashboard');
            }
            
            Auth::logout();
            return back()->withErrors([
                'failed' => 'You do not have admin privileges'
            ]);
        }

        return back()->withErrors([
            'failed' => 'The provided credentials do not match our records'
        ]);
    }

    public function logout(Request $request) {
    Auth::logout();
    
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    return redirect()->route('admin.login');
}

    public function dashboard2()
    {
        return view('admin.admin-dashboard2');
    }

    public function userManagement()
    {
        $users = User::all();
        return view('admin.admin-userManagement', compact('users'));
    }

    public function create()
    {
        return view('admin.create-user');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'first_name' => 'required',
            'last_name' => 'required',
            'wmsu_email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        User::create($request->all());

        return redirect()->route('admin-userManagement')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.edit-user', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|unique:users,username,' . $user->id,
            'first_name' => 'required',
            'last_name' => 'required',
            'wmsu_email' => 'required|email|unique:users,wmsu_email,' . $user->id,
        ]);

        $user->update($request->all());

        return redirect()->route('admin-userManagement')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin-userManagement')->with('success', 'User deleted successfully.');
    }

    public function show(User $user)
    {
        return view('admin.user-details', compact('user'));
    }

    public function transactions()
    {
        $transactions = Order::with(['buyer', 'seller', 'items.product.images'])->get();
        return view('admin.admin-transactions', compact('transactions'));
    }

    public function productManagement() {
        $products = Product::all();
        return view('admin.admin-productManagement', compact('products'));
    }
}
