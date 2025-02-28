<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Category; // Import the Category model
use App\Models\Transaction;
use App\Models\Order; // Import the Order model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Import the Log facade

class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.adminlogin');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            // Authentication passed...
            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('username'));
    }

    public function logout(Request $request) {
    Auth::logout();
    
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    return redirect()->route('admin.login');
}

    public function dashboard2()
    {
        return view('admin.dashboard2');
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
        $transactions = Order::with(['buyer', 'seller', 'items.product'])->get();

        $totalTransactions = $transactions->count();
        $pendingTransactions = $transactions->where('status', 'Pending')->count();
        $processingTransactions = $transactions->where('status', 'Processing')->count();
        $shippedTransactions = $transactions->where('status', 'Shipped')->count();
        $deliveredTransactions = $transactions->where('status', 'Delivered')->count();

        return view('admin.admin-transactions', compact(
            'transactions',
            'totalTransactions',
            'pendingTransactions',
            'processingTransactions',
            'shippedTransactions',
            'deliveredTransactions'
        ));
    }

    public function productManagement()
    {
        $products = Product::all();
        $categories = Category::all(); // Fetch all categories
        return view('admin.admin-productManagement', compact('products', 'categories'));
    }

    public function destroyProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function bulkDestroyProducts(Request $request)
    {
        $ids = $request->input('ids');
        Product::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Products deleted successfully']);
    }

    public function updateProduct(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'discount' => 'required|numeric',
            'discounted_price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        $product = Product::findOrFail($id);
        $product->update($request->all());

        return redirect()->route('admin.productManagement')->with('success', 'Product updated successfully.');
    }

    public function storeProduct(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'discounted_price' => 'nullable|numeric',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'stock' => 'required|integer',
            'seller_code' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'is_buyable' => 'required|boolean',
            'is_tradable' => 'required|boolean',
            'status' => 'required|in:Active,Inactive',
        ]);

        $product = new Product($validatedData);

        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = $path;
            }
            $product->images = json_encode($images);
        }

        $product->save();

        return redirect()->route('admin.productManagement')->with('success', 'Product added successfully.');
    }

    public function destroyTransaction($id)
    {
        $transaction = Order::findOrFail($id);
        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted successfully']);
    }

    public function bulkDestroyTransactions(Request $request)
    {
        $ids = $request->input('ids');
        Order::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Transactions deleted successfully']);
    }

    public function cancelTransaction($id)
    {
        $transaction = Order::findOrFail($id);
        $transaction->status = 'Cancelled';
        $transaction->save();

        return response()->json(['message' => 'Transaction cancelled successfully']);
    }

    public function getProduct($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    public function fundManagement()
    {
        return view('admin.admin-fundManagement');
    }

    public function dashboard()
    {
        return view('admin.admin-dashboard2');
    }

    public function settings()
    {

        return view('admin.admin-settings');

        // $request->validate([
        //     'commission_rate' => 'required|numeric|min:0|max:100',
        // ]);

        // // Save the commission rate to the database or configuration
        // // For example, using a settings table:
        // DB::table('settings')->updateOrInsert(
        //     ['key' => 'commission_rate'],
        //     ['value' => $request->commission_rate]
        // );

        // return redirect()->back()->with('success', 'Commission rate updated successfully.');
    }
}
