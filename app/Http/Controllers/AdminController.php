<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Category; // Import the Category model
use App\Models\Transaction;
use App\Models\Order; // Import the Order model
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Import the Log facade
use Inertia\Inertia;

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

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
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
        // Get the base counts
        $pendingRequests = WalletTransaction::where('status', 'pending')->count();
        $totalUsers = User::count();
        $totalProducts = Product::count();
        $totalOrders = Order::count();

        // Get counts of new items today
        $today = now()->startOfDay();
        $newUsers = User::where('created_at', '>=', $today)->count();
        $newProducts = Product::where('created_at', '>=', $today)->count();
        $newOrders = Order::where('created_at', '>=', $today)->count();

        // Get recent wallet transactions with user info
        $recentTransactions = WalletTransaction::with(['user:id,first_name,last_name,seller_code'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'type' => $transaction->amount > 0 ? 'credit' : 'debit',
                    'status' => $transaction->status,
                    'reference_type' => ucfirst($transaction->reference_type),
                    'created_at' => $transaction->created_at,
                    'user' => $transaction->user ? [
                        'name' => $transaction->user->first_name . ' ' . $transaction->user->last_name,
                        'seller_code' => $transaction->user->seller_code
                    ] : null
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'pendingRequests' => $pendingRequests,
                'totalUsers' => $totalUsers,
                'totalProducts' => $totalProducts,
                'totalOrders' => $totalOrders,
                'newUsers' => $newUsers,
                'newProducts' => $newProducts,
                'newOrders' => $newOrders,
                'recentTransactions' => $recentTransactions
            ]
        ]);
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

    public function walletRequests()
    {
        $transactions = WalletTransaction::with([
            'wallet.user' => function ($query) {
                $query->select('id', 'seller_code', 'first_name', 'last_name');
            }
        ])
            ->latest()
            ->get();

        return Inertia::render('Admin/WalletRequests', [
            'transactions' => $transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'seller_code' => $transaction->seller_code,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'previous_balance' => $transaction->previous_balance,
                    'new_balance' => $transaction->new_balance,
                    'reference_type' => $transaction->reference_type,
                    'reference_id' => $transaction->reference_id,
                    'status' => $transaction->status,
                    'description' => $transaction->description,
                    'receipt_url' => $transaction->receipt_url,
                    'created_at' => $transaction->created_at,
                    'processed_at' => $transaction->processed_at,
                    'wallet' => $transaction->wallet ? [
                        'id' => $transaction->wallet->id,
                        'balance' => $transaction->wallet->balance,
                        'user' => [
                            'first_name' => $transaction->wallet->user->first_name,
                            'last_name' => $transaction->wallet->user->last_name,
                        ]
                    ] : null
                ];
            })
        ]);
    }

    public function approveWalletRequest($id)
    {
        try {
            DB::beginTransaction();

            $transaction = WalletTransaction::findOrFail($id);
            $wallet = $transaction->wallet;

            // Update transaction status
            $transaction->update([
                'status' => 'completed',
                'processed_at' => now(),
                'processed_by' => auth()->id()
            ]);

            // If this was a verification transaction, activate the wallet
            if ($transaction->reference_type === 'verification') {
                $wallet->update([
                    'is_activated' => true,
                    'status' => 'active',
                    'activated_at' => now()
                ]);
            } else {
                // Handle regular refill transaction
                $wallet->increment('balance', $transaction->amount);
            }

            DB::commit();

            return back()->with('success', 'Wallet request approved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet approval error: ' . $e->getMessage());
            return back()->with('error', 'Failed to approve wallet request');
        }
    }

    public function rejectWalletRequest($id)
    {
        try {
            $transaction = WalletTransaction::findOrFail($id);
            $transaction->update([
                'status' => 'rejected',
                'processed_at' => now(),
                'processed_by' => auth()->id()
            ]);

            return back()->with('success', 'Wallet refill request rejected');
        } catch (\Exception $e) {
            Log::error('Wallet rejection error: ' . $e->getMessage());
            return back()->with('error', 'Failed to reject wallet request');
        }
    }

    public function wallet()
    {
        // Get all sellers with their wallet information
        $sellers = User::where('is_seller', true)
            ->with(['wallet', 'products'])
            ->get()
            ->map(function ($seller) {
                return [
                    'id' => $seller->id,
                    'name' => $seller->first_name . ' ' . $seller->last_name,
                    'seller_code' => $seller->seller_code,
                    'balance' => $seller->wallet?->balance ?? 0,
                    'total_listings' => $seller->products->count(),
                    'fees_collected' => WalletTransaction::where('wallet_id', $seller->wallet?->id)
                        ->where('type', 'fee')
                        ->sum('amount')
                ];
            });

        // Get refund requests
        $refundRequests = RefundRequest::with(['buyer', 'order'])
            ->latest()
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'buyer_name' => $request->buyer->first_name . ' ' . $request->buyer->last_name,
                    'amount' => $request->amount,
                    'reason' => $request->reason,
                    'proof_image' => $request->proof_image ? asset('storage/' . $request->proof_image) : null,
                    'status' => $request->status,
                    'created_at' => $request->created_at
                ];
            });

        // Get wallet stats
        $stats = [
            'revenue' => WalletTransaction::where('type', 'fee')
                ->whereMonth('created_at', now())
                ->sum('amount'),
            'refunds' => WalletTransaction::where('type', 'refund')
                ->whereMonth('created_at', now())
                ->sum('amount'),
            'top_sellers' => User::where('is_seller', true)
                ->whereHas('wallet')
                ->withSum('wallet as balance', 'balance')
                ->orderByDesc('balance')
                ->take(5)
                ->get()
                ->map(fn($seller) => [
                    'name' => $seller->first_name . ' ' . $seller->last_name,
                    'balance' => $seller->balance
                ])
        ];

        return Inertia::render('Admin/Wallet', [
            'sellers' => $sellers,
            'refundRequests' => $refundRequests,
            'stats' => $stats
        ]);
    }
}
