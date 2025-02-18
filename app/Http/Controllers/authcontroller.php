<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    // login User
    public function login(Request $request)
    {
        // First validate required fields
        $fields = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        // If WMSU email is provided, validate its format
        if ($request->filled('wmsu_email')) {
            $request->validate([
                'wmsu_email' => 'regex:/^[a-zA-Z0-9._%+-]+@wmsu\.edu\.ph$/'
            ]);
        }

        // Attempt authentication
        if (Auth::attempt($fields)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'failed' => 'The provided credentials do not match our records.',
        ])->onlyInput('username', 'wmsu_email');
    }

    public function showPersonalInfoForm(Request $request)
    {
        // Clear all session data
        $request->session()->invalidate();
        $request->session()->regenerate();

        // Clear registration data
        $request->session()->forget(['registration_data']);

        // Force client-side storage clearing
        $clearLocalStorage = true;

        // Get data for form dropdowns
        $departments = Department::orderBy('name')->get();
        $gradeLevels = GradeLevel::orderBy('level')->get();
        $userTypes = UserType::orderBy('name')->get();

        return view('auth.register-personal-info', [
            'departments' => $departments,
            'gradeLevels' => $gradeLevels,
            'userTypes' => $userTypes,
            'clearLocalStorage' => $clearLocalStorage
        ]);
    }

    public function processPersonalInfo(Request $request)
    {
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
        return redirect()->route('register.details');
    }

    public function showDetailsForm(Request $request)
    {
        // Check if we have the first step data
        if (!$request->session()->has('registration_data')) {
            return redirect()->route('register.personal-info');
        }

        $registrationData = $request->session()->get('registration_data');

        // Get departments and grade levels for form dropdowns
        $departments = Department::orderBy('name')->get();
        $gradeLevels = GradeLevel::orderBy('level')->get();
        $userTypes = UserType::orderBy('name')->get();

        return view('auth.register-account-info', [
            'departments' => $departments,
            'gradeLevels' => $gradeLevels,
            'userTypes' => $userTypes,
            'registrationData' => $registrationData
        ]);
    }

    public function completeRegistration(Request $request)
    {
        if (!$request->session()->has('registration_data')) {
            return redirect()->route('register.personal-info');
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

            // Add email validation for specific user types
            if (in_array($firstStepData['user_type_id'], ['HS', 'COL', 'PG', 'EMP'])) {
                $rules['wmsu_email'] = [
                    'required',
                    'string',
                    'unique:users,wmsu_email',
                    'regex:/^[a-zA-Z0-9._%+-]+@wmsu\.edu\.ph$/'
                ];
            }

            // Add ID verification for all except employees
            if ($firstStepData['user_type_id'] !== 'EMP') {
                $rules['wmsu_id_front'] = ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'];
                $rules['wmsu_id_back'] = ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'];
            }

            $validatedData = $request->validate($rules);

            // Merge data and hash password
            $userData = array_merge($firstStepData, $validatedData);
            $userData['password'] = bcrypt($userData['password']);

            // Handle all file uploads consistently
            $userType = $firstStepData['user_type_id'];

            // Store profile picture
            $userData['profile_picture'] = $request->file('profile_picture')
                ->store($userType . '/profile_pictures', 'public');

            // Store ID pictures if provided
            if ($request->hasFile('wmsu_id_front')) {
                $userData['wmsu_id_front'] = $request->file('wmsu_id_front')
                    ->store($userType . '/id_front', 'public');
            }
            if ($request->hasFile('wmsu_id_back')) {
                $userData['wmsu_id_back'] = $request->file('wmsu_id_back')
                    ->store($userType . '/id_back', 'public');
            }

            // Get proper user type ID
            $userTypeId = UserType::where('code', $userType)->first()->id;
            $userData['user_type_id'] = $userTypeId;

            // Create user and login
            $user = User::create($userData);
            $request->session()->forget('registration_data');
            Auth::login($user);

            return redirect()->route('index')
                ->with('success', 'Registration completed successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
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
        return redirect('/');
    }
}
