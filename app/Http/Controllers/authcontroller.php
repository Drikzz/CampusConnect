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

    // public function register(Request $request)
    // {
    //     try {
    //         // Base validation rules
    //         $rules = [
    //             'user_type' => ['required', 'in:highschool,college,employee,alumni,postgraduate'],
    //             'username' => ['required', 'max:255', 'unique:users'],
    //             'password' => ['required', 'min:6'],
    //             'first_name' => ['required', 'max:255'],
    //             'last_name' => ['required', 'max:255'],
    //             'profile_picture' => ['nullable', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
    //         ];

    //         // Add type-specific validation rules
    //         switch ($request->user_type) {
    //             case 'highschool':
    //                 $rules = array_merge($rules, [
    //                     'wmsu_email' => ['required', 'string', 'regex:/^[a-z]{2}[0-9]{4}[0-9]{5}@wmsu\.edu\.ph$/', 'unique:users'],
    //                     'grade_level' => ['required', 'string'],
    //                     'wmsu_id_front' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
    //                     'wmsu_id_back' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
    //                 ]);
    //                 break;

    //             case 'college':
    //             case 'postgraduate':
    //                 $rules = array_merge($rules, [
    //                     'wmsu_email' => ['required', 'string', 'regex:/^eh[0-9]{9}@wmsu.edu.ph$/', 'unique:users'],
    //                     'wmsu_dept' => ['required', 'string'],
    //                     'wmsu_id_front' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
    //                     'wmsu_id_back' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
    //                 ]);
    //                 break;

    //             case 'employee':
    //                 $rules = array_merge($rules, [
    //                     'wmsu_email' => ['required', 'string', 'regex:/^eh[0-9]{9}@wmsu.edu.ph$/', 'unique:users'],
    //                 ]);
    //                 break;

    //             case 'alumni':
    //                 $rules = array_merge($rules, [
    //                     'wmsu_id_front' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
    //                     'wmsu_id_back' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
    //                 ]);
    //                 break;
    //         }

    //         // Validate request
    //         $fields = $request->validate($rules);

    //         // Handle file uploads
    //         $profilePath = null;
    //         if ($request->hasFile('profile_picture')) {
    //             $profilePath = Storage::disk('public')->put($request->user_type . '/profile_pictures', $request->profile_picture);
    //         }

    //         $idFrontPath = null;
    //         $idBackPath = null;
    //         if ($request->hasFile('wmsu_id_front')) {
    //             $idFrontPath = Storage::disk('public')->put($request->user_type . '/id_front', $request->wmsu_id_front);
    //         }
    //         if ($request->hasFile('wmsu_id_back')) {
    //             $idBackPath = Storage::disk('public')->put($request->user_type . '/id_back', $request->wmsu_id_back);
    //         }

    //         // Get user type
    //         $userTypeMap = [
    //             'highschool' => 'HS',
    //             'college' => 'COL',
    //             'employee' => 'EMP',
    //             'alumni' => 'ALM',
    //             'postgraduate' => 'PG'
    //         ];

    //         $userType = UserType::where('code', $userTypeMap[$request->user_type])->first()->id;

    //         // Prepare user data
    //         $userData = [
    //             'user_type_id' => $userType,
    //             'username' => $fields['username'],
    //             'password' => $fields['password'],
    //             'first_name' => $fields['first_name'],
    //             'last_name' => $fields['last_name'],
    //             'profile_picture' => $profilePath,
    //             'wmsu_id_front' => $idFrontPath,
    //             'wmsu_id_back' => $idBackPath,
    //         ];

    //         // Add type-specific data
    //         if (isset($fields['wmsu_email'])) {
    //             $userData['wmsu_email'] = $fields['wmsu_email'];
    //         }

    //         if (isset($fields['wmsu_dept'])) {
    //             $wmsu_dept_id = Department::where('code', $fields['wmsu_dept'])->first()->id;
    //             $userData['wmsu_dept_id'] = $wmsu_dept_id;
    //         }

    //         if (isset($fields['grade_level'])) {
    //             $grade_level_id = GradeLevel::where('name', $fields['grade_level'])->first()->id;
    //             $userData['grade_level_id'] = $grade_level_id;
    //         }

    //         // Create user
    //         $user = User::create($userData);

    //         // Login and redirect
    //         Auth::login($user);
    //         return redirect('/')->with('success', 'Registration completed successfully!');
    //     } catch (ValidationException $e) {
    //         return redirect()->back()
    //             ->withErrors($e->errors())
    //             ->withInput();
    //     }
    // }

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

        // Clear any existing registration data
        $request->session()->forget([
            'registration_data',
            'temp_profile_picture',
            'user_type_id',
            'grade_level_id',
            'wmsu_dept_id',
            'first_name',
            'middle_name',
            'last_name',
            'gender',
            'date_of_birth',
            'phone',
            'profile_picture'
        ]);

        // Force client-side storage clearing
        $clearLocalStorage = true;

        // Get departments and grade levels for form dropdowns if needed
        $departments = Department::orderBy('name')->get();
        $gradeLevels = GradeLevel::orderBy('level')->get();
        $userTypes = UserType::orderBy('name')->get();

        return view('auth.register-personal-info', [
            'user' => null,
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
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            // Removed password validation
        ]);

        // Store profile picture separately if uploaded
        if ($request->hasFile('profile_picture')) {
            // Store in temp folder and save the path
            $tempPath = $request->file('profile_picture')->store('temp/profile_pictures', 'public');
            // Store the path separately in session
            $request->session()->put('temp_profile_picture', $tempPath);
            // Remove profile_picture from validated data to avoid serialization issues
            unset($validatedData['profile_picture']);
        }

        // Store validated data in the session (without profile_picture)
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
        // Check if we have the first step data
        if (!$request->session()->has('registration_data')) {
            return redirect()->route('register.personal-info');
        }

        $firstStepData = $request->session()->get('registration_data');
        $tempProfilePicture = $request->session()->get('temp_profile_picture');

        try {
            // Base validation rules for all user types
            $rules = [
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/'
                ]
            ];

            // Add email validation for specific user types
            if (in_array($firstStepData['user_type_id'], ['HS', 'COL', 'PG', 'EMP'])) {
                $rules['wmsu_email'] = [
                    'required',
                    'string',
                    'max:255',
                    'unique:users,wmsu_email',
                    'regex:/^[a-zA-Z0-9._%+-]+@wmsu\.edu\.ph$/' // New simplified email format
                ];
            }

            // Add ID verification for all except employees
            if ($firstStepData['user_type_id'] !== 'EMP') {
                $rules['wmsu_id_front'] = ['required', 'image', 'max:2048', 'mimes:jpeg,png,jpg'];
                $rules['wmsu_id_back'] = ['required', 'image', 'max:2048', 'mimes:jpeg,png,jpg'];
            }

            $validatedData = $request->validate($rules);

            // Merge first and second step data
            $userData = array_merge($firstStepData, $validatedData);
            // Hash the password before saving
            $userData['password'] = bcrypt($userData['password']);

            // Move profile picture from temp to final location if it exists
            if ($tempProfilePicture && Storage::disk('public')->exists($tempProfilePicture)) {
                // Generate the final path
                $finalPath = str_replace(
                    'temp/profile_pictures',
                    $firstStepData['user_type_id'] . '/profile_pictures',
                    $tempProfilePicture
                );

                // Move the file
                Storage::disk('public')->move($tempProfilePicture, $finalPath);
                $userData['profile_picture'] = $finalPath;
            }

            // Handle ID uploads only (profile picture is already handled in first step)
            if ($request->hasFile('wmsu_id_front')) {
                $userData['wmsu_id_front'] = $request->file('wmsu_id_front')
                    ->store($firstStepData['user_type_id'] . '/id_front', 'public');
            }

            if ($request->hasFile('wmsu_id_back')) {
                $userData['wmsu_id_back'] = $request->file('wmsu_id_back')
                    ->store($firstStepData['user_type_id'] . '/id_back', 'public');
            }

            $userTypeId = UserType::where('code', $firstStepData['user_type_id'])->first()->id;
            $userData['user_type_id'] = $userTypeId;

            // Create the user
            $user = User::create($userData);

            // Clear all session data
            $request->session()->forget(['registration_data', 'temp_profile_picture']);

            // Clear any leftover form data
            $request->session()->forget([
                'user_type_id',
                'grade_level_id',
                'wmsu_dept_id',
                'first_name',
                'middle_name',
                'last_name',
                'gender',
                'date_of_birth',
                'phone',
                'profile_picture'
            ]);

            // Clear temporary storage if exists
            if ($tempProfilePicture) {
                Storage::disk('public')->delete($tempProfilePicture);
            }

            // Start fresh session
            $request->session()->regenerate();

            // Log in the user
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
