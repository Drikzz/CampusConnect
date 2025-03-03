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

                // Mail::to('test@wmsu.edu.ph')->send(new WelcomeMail(Auth::user()));

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
        // Only clear registration-related data, not the entire session
        $request->session()->forget([
            'registration_data',
            'form_data',
            'step1_completed',
            'temp_files'
        ]);

        // Get data for form dropdowns
        $departments = Department::orderBy('name')->get();
        $gradeLevels = GradeLevel::orderBy('level')->get();
        $userTypes = UserType::orderBy('name')->get();

        return Inertia::render('Auth/RegisterPersonalInfo', [
            'departments' => $departments,
            'gradeLevels' => $gradeLevels,
            'userTypes' => $userTypes,
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

    public function completeRegistration(Request $request)
    {
        if (!$request->session()->has('registration_data')) {
            return redirect()->route('register.personal-info')
                ->with('toast', [
                    'variant' => 'destructive',
                    'title' => 'Error!',
                    'description' => 'Please complete the first step of registration.'
                ]);
        }

        $firstStepData = $request->session()->get('registration_data');

        try {
            $rules = [
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'password' => [
                    'required',
                    'string',
                    'min:8',  // Enforce minimum 8 characters
                    'confirmed',
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
                'profile_picture' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048']
            ];

            // Add email validation based on user type
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

            // Merge data and hash password
            $userData = array_merge($firstStepData, $validatedData);
            $userData['password'] = bcrypt($userData['password']);

            // Handle file uploads
            try {
                // Store profile picture
                $userData['profile_picture'] = $request->file('profile_picture')
                    ->store($firstStepData['user_type_id'] . '/profile_pictures', 'public');

                // Store ID pictures if provided
                if ($request->hasFile('wmsu_id_front')) {
                    $userData['wmsu_id_front'] = $request->file('wmsu_id_front')
                        ->store($firstStepData['user_type_id'] . '/id_front', 'public');
                }
                if ($request->hasFile('wmsu_id_back')) {
                    $userData['wmsu_id_back'] = $request->file('wmsu_id_back')
                        ->store($firstStepData['user_type_id'] . '/id_back', 'public');
                }
            } catch (\Exception $e) {
                return back()
                    ->withInput()
                    ->with('toast', [
                        'variant' => 'destructive',
                        'title' => 'Error!',
                        'description' => 'Failed to upload files. Please try again.'
                    ]);
            }

            // Get proper user type ID
            $userTypeId = UserType::where('code', $firstStepData['user_type_id'])->first()->id;
            $userData['user_type_id'] = $userTypeId;

            // Create user
            $user = User::create($userData);

            // Log the user in AFTER creating the Registered event
            Auth::login($user);

            // Fire the Registered event - this triggers verification email
            event(new Registered($user));

            // Clear session data
            $request->session()->forget('registration_data');

            // Redirect to verification notice page
            return redirect()->route('verification.notice')
                ->with('message', 'Registration successful! Please check your email to verify your account.');
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('toast', [
                    'variant' => 'destructive',
                    'title' => 'Error!',
                    'description' => 'Please check the form for errors.'
                ]);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('toast', [
                    'variant' => 'destructive',
                    'title' => 'Error!',
                    'description' => 'An unexpected error occurred. Please try again.'
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
        //Logout the sser
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
