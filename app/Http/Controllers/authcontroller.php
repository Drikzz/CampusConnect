<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class AuthController extends Controller
{

    // login User
    public function login(Request $request)
    {
        try {
            // First validate required fields with custom messages
            $fields = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string'
            ], [
                'username.required' => 'Username is required',
                'password.required' => 'Password is required'
            ]);

            // Attempt authentication
            if (Auth::attempt($fields, $request->remember)) {
                $request->session()->regenerate();

                // Check if user is admin
                if (Auth::user()->is_admin) {
                    return redirect()->route('admin.dashboard')
                        ->with('toast', [
                            'variant' => '',
                            'title' => 'Welcome Admin!',
                            'description' => 'You have been logged in successfully.'
                        ]);
                }

                // Regular user login flow continues...
                // Check if there's a pending wishlist action
                if ($request->session()->has('wishlist_after_login')) {
                    app(WishlistController::class)->handleAfterLogin($request);
                }

                // If there's a redirect parameter, use it
                if ($request->has('redirect')) {
                    return redirect($request->redirect);
                }

                return redirect()->intended(route('dashboard.profile'))
                    ->with('toast', [
                        'variant' => '',
                        'title' => 'Success!',
                        'description' => 'Login successful.'
                    ]);
            }

            // Invalid credentials
            return back()->withErrors([
                'username' => 'The provided credentials do not match our records.',
            ])->with('toast', [
                'variant' => 'destructive',
                'title' => 'Error!',
                'description' => 'Invalid credentials. Please try again.'
            ]);
        } catch (ValidationException $e) {
            // Validation failed
            return back()->withErrors($e->errors())
                ->with('toast', [
                    'variant' => 'destructive',
                    'title' => 'Error!',
                    'description' => 'Please fill in all required fields.'
                ]);
        }
    }

    public function showPersonalInfoForm(Request $request)
    {
        // If accessing directly from a non-registration page, clear all session data
        $referer = $request->headers->get('referer');
        $isFromRegistration = $referer && (
            str_contains($referer, '/register') || 
            str_contains($referer, '/register/details') ||
            str_contains($referer, '/register/personal-info')
        );

        // If not coming from another registration page, clear all data
        if (!$isFromRegistration) {
            // Clear session data
            $request->session()->forget([
                'registration_data',
                'form_data',
                'step1_completed',
                'temp_files'
            ]);
            
            // Set flag to clear client-side storage
            $clearStorage = true;
        } else {
            $clearStorage = false;
        }

        // Get data for form dropdowns
        $departments = Department::orderBy('name')->get();
        $gradeLevels = GradeLevel::orderBy('level')->get();
        $userTypes = UserType::orderBy('name')->get();

        return Inertia::render('Auth/RegisterPersonalInfo', [
            'departments' => $departments,
            'gradeLevels' => $gradeLevels,
            'userTypes' => $userTypes,
            'clearStorage' => $clearStorage,
        ])->with('toast', [
            'variant' => 'default',
            'title' => 'Welcome!',
            'description' => 'Please fill out your personal information.'
        ]);
    }

    public function processPersonalInfo(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_type_id' => ['required', 'exists:user_types,code'],
                'grade_level_id' => ['nullable', 'exists:grade_levels,id'],
                'wmsu_dept_id' => ['nullable', 'exists:departments,id'],
                'first_name' => ['required', 'string', 'max:255'],
                'middle_name' => ['nullable', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'gender' => ['required', 'string', 'in:male,female,non-binary,prefer-not-to-say'],
                'date_of_birth' => ['required', 'date', 'before:today'],
                'phone' => ['required', 'string', 'regex:/^[0-9]{11}$/'],
            ]);

            $request->session()->put('registration_data', $validatedData);
            
            // Return to details page with a proper Inertia redirect rather than JSON response
            return redirect()->route('register.details')
                ->with('toast', [
                    'variant' => 'default',
                    'title' => 'Success!',
                    'description' => 'Personal information saved successfully.'
                ]);
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('toast', [
                    'variant' => 'destructive',
                    'title' => 'Error!',
                    'description' => 'Please check the form for errors.'
                ]);
        }
    }

    public function showDetailsForm(Request $request)
    {
        // Check if we have the first step data
        if (!$request->session()->has('registration_data')) {
            return redirect()->route('register.personal-info')
                ->with('toast', [
                    'variant' => 'destructive',
                    'title' => 'Error!',
                    'description' => 'Please complete the first step of registration.'
                ]);
        }

        $registrationData = $request->session()->get('registration_data');

        return Inertia::render('Auth/RegisterAccountInfo', [
            'registrationData' => $registrationData
        ])->with('toast', [
            'variant' => 'default',
            'title' => 'Almost there!',
            'description' => 'Please complete your account details.'
        ]);
    }

    public function processAccountInfo(Request $request)
    {
        if (!$request->session()->has('registration_data')) {
            return redirect()->route('register.personal-info')
                ->with('toast', [
                    'variant' => 'destructive',
                    'title' => 'Error!',
                    'description' => 'Please complete the first step of registration.'
                ]);
        }

        try {
            $rules = [
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',  // This uses the password_confirmation field automatically
                    function ($attribute, $value, $fail) {
                        $strength = 0;
                        if (strlen($value) >= 8) $strength++;
                        if (preg_match('/[A-Z]/', $value)) $strength++;
                        if (preg_match('/[a-z]/', $value)) $strength++;
                        if (preg_match('/[0-9]/', $value)) $strength++;
                        if (preg_match('/[^A-Za-z0-9]/', $value)) $strength++;

                        // Require at least 4 criteria (Strong password)
                        if ($strength < 4) {
                            $fail('Password must meet at least 4 of these criteria: minimum 8 characters, uppercase letter, lowercase letter, number, special character.');
                        }
                    }
                ],
                'password_confirmation' => ['required'], // Add explicit validation for password_confirmation
            ];

            // Add email validation based on user type
            $firstStepData = $request->session()->get('registration_data');
            
            if (in_array($firstStepData['user_type_id'], ['HS', 'COL', 'PG', 'EMP'])) {
                $rules['wmsu_email'] = [
                    'required',
                    'string',
                    'email',
                    'unique:users,wmsu_email',
                    'regex:/^[a-zA-Z0-9._%+-]+@wmsu\.edu\.ph$/'
                ];
            } elseif ($firstStepData['user_type_id'] === 'ALM') {
                $rules['wmsu_email'] = [
                    'nullable',
                    'string',
                    'email',
                    'unique:users,wmsu_email',
                    'regex:/^[a-zA-Z0-9._%+-]+@wmsu\.edu\.ph$/'
                ];
            }

            // Add ID verification for required user types
            if (in_array($firstStepData['user_type_id'], ['HS', 'COL', 'EMP', 'PG', 'ALM'])) {
                $rules['wmsu_id_front'] = ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'];
                $rules['wmsu_id_back'] = ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'];
            }

            $validatedData = $request->validate($rules);
            
            // Store ID images if provided
            $accountData = [
                'username' => $validatedData['username'],
                'password' => bcrypt($validatedData['password']),
            ];
            
            if (isset($validatedData['wmsu_email'])) {
                $accountData['wmsu_email'] = $validatedData['wmsu_email'];
            }
            
            // Handle file uploads
            try {
                if ($request->hasFile('wmsu_id_front')) {
                    $accountData['wmsu_id_front'] = $request->file('wmsu_id_front')
                        ->store($firstStepData['user_type_id'] . '/id_front', 'public');
                }
                
                if ($request->hasFile('wmsu_id_back')) {
                    $accountData['wmsu_id_back'] = $request->file('wmsu_id_back')
                        ->store($firstStepData['user_type_id'] . '/id_back', 'public');
                }
            } catch (\Exception $e) {
                return back()->withInput()
                    ->with('toast', [
                        'variant' => 'destructive',
                        'title' => 'Error!',
                        'description' => 'Failed to upload ID files. Please try again.'
                    ]);
            }
            
            // Store account data in session
            $request->session()->put('account_data', $accountData);
            
            // Use Inertia's redirect with header that prevents scroll restoration
            return redirect()->route('register.profile-picture')
                ->header('X-Inertia-Scroll-Restoration', 'false')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT')
                ->with('toast', [
                    'variant' => 'default',
                    'title' => 'Success!',
                    'description' => 'Account details saved successfully.'
                ]);
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('toast', [
                    'variant' => 'destructive',
                    'title' => 'Error!',
                    'description' => 'Please check the form for errors.'
                ]);
        }
    }

    public function showProfilePicturePage(Request $request)
    {
        if (!$request->session()->has('registration_data') || !$request->session()->has('account_data')) {
            return redirect()->route('register.personal-info')
                ->with('toast', [
                    'variant' => 'destructive',
                    'title' => 'Error!',
                    'description' => 'Please complete the previous steps of registration.'
                ]);
        }

        return Inertia::render('Auth/RegisterProfilePicture', [
            'registrationData' => $request->session()->get('registration_data'),
            'accountData' => $request->session()->get('account_data')
        ])->with('toast', [
            'variant' => 'default',
            'title' => 'Almost there!',
            'description' => 'Add a profile picture or skip this step.'
        ]);
    }
    
    public function completeRegistration(Request $request)
    {
        if (!$request->session()->has('registration_data') || !$request->session()->has('account_data')) {
            return redirect()->route('register.personal-info')
                ->with('toast', [
                    'variant' => 'destructive',
                    'title' => 'Error!',
                    'description' => 'Please complete the previous steps of registration.'
                ]);
        }

        $personalData = $request->session()->get('registration_data');
        $accountData = $request->session()->get('account_data');

        try {
            // Check if profile picture is provided (optional)
            if ($request->hasFile('profile_picture')) {
                $validatedData = $request->validate([
                    'profile_picture' => ['image', 'mimes:jpeg,png,jpg', 'max:2048']
                ]);
                
                // Upload the profile picture
                try {
                    $accountData['profile_picture'] = $request->file('profile_picture')
                        ->store($personalData['user_type_id'] . '/profile_pictures', 'public');
                } catch (\Exception $e) {
                    return back()
                        ->withInput()
                        ->with('toast', [
                            'variant' => 'destructive',
                            'title' => 'Error!',
                            'description' => 'Failed to upload profile picture. Please try again.'
                        ]);
                }
            } else {
                // Set a default profile picture if none provided
                $accountData['profile_picture'] = 'default-profile.jpg';
            }

            // Merge all data for user creation
            $userData = array_merge($personalData, $accountData);

            // Get proper user type ID
            $userTypeId = UserType::where('code', $personalData['user_type_id'])->first()->id;
            $userData['user_type_id'] = $userTypeId;

            // Create the user
            $user = User::create($userData);

            // Log the user in
            Auth::login($user);

            // Fire the Registered event - this triggers verification email
            event(new Registered($user));

            // Clear session data
            $request->session()->forget(['registration_data', 'account_data']);

            // Redirect to verification notice page
            return redirect()->route('verification.notice')
                ->with('message', 'Registration successful! Please check your email to verify your account.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('toast', [
                    'variant' => 'destructive',
                    'title' => 'Error!',
                    'description' => 'An unexpected error occurred: ' . $e->getMessage()
                ]);
        }
    }

    //send notice
    public function verifyNotice()
    {
        // return view('auth.verify-email');

        return inertia('Auth/VerifyEmailNotice');
    }

    //verify email
    public function verifyEmail(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect()->route('dashboard.profile')
            ->with('toast', [
                'variant' => 'success',
                'title' => 'Email Verified!',
                'description' => 'Your email has been verified successfully.'
            ]);
    }

    //resend verification email
    public function verifyHandler(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return back()
            ->with('toast', [
                'variant' => 'success',
                'title' => 'Sent!',
                'description' => 'A new verification link has been sent to your email.'
            ]);
    }

    //Logout User
    public function logout(Request $request)
    {
        //Logout the user
        Auth::logout();

        // Invalidate user's session
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        // Redirect to home
        return redirect('/')
            ->with('toast', [
                'variant' => 'default',
                'title' => 'Success!',
                'description' => 'You have been logged out successfully.'
            ]);
    }

    public function showLoginForm()
    {
        return Inertia::render('Auth/Login');
    }

}
