<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AdminLoginController extends Controller
{
    /**
     * Handle an admin login request.
     */
    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only('username', 'password');
        $user = $this->filterAdminUsers($credentials['username'], $credentials['password']);

        if ($user) {
            Auth::login($user);
            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('username'));
    }

    /**
     * Filter only admin users.
     */
    public function filterAdminUsers($username, $password)
    {
        $user = \DB::table('users')
            ->where('username', $username)
            ->where('is_admin', true)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            return $user;
        }

        return null;
    }

    /**
     * Redirect to admin dashboard after authentication.
     */
    protected function authenticated(Request $request, $user)
    {
        return redirect()->route('admin.dashboard');
    }
}
