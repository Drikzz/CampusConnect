<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationData;

class authcontroller extends Controller
{  
    // login User
    public function login(Request $request) {
        // Validate
        $fields = $request->validate([
            'username' => ['required', 'max:255'],
            'wmsu_email' => ['required', 'max:255', 'email'],
            'password' => ['required'],
        ]);

        // Try to login the user
        if (Auth::attempt($fields, $request->remember)) {
            return redirect()->intended('/');
        } else {
            return back()->withErrors([
                'failed' => 'The provided credentials do not match our records'
            ]);
        }
    }

    // highschool student functions
    public function register_form_highschool(){
        $gradeLevels = GradeLevel::orderBy('level')->get();

        return view('auth.register-highschool', ['gradeLevels' => $gradeLevels]);
    }

    public function registerHSStudent(Request $request)
    {

        try {
            $fields = $request->validate([
                'username' => ['required', 'max:255', 'unique:users'],
                'password' => ['required', 'min:3'],
                'first_name' => ['required', 'max:255'],
                'last_name' => ['required', 'max:255'],
                'wmsu_email' => ['required', 'string', 'regex:/^eh[0-9]{9}@wmsu.edu.ph$/', 'unique:users'],
                'grade_level' => ['required', 'string'],
                'profile_picture' => ['nullable', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
                'wmsu_id_front' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
                'wmsu_id_back' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
            ], [
                'wmsu_email.required' => 'Please enter your WMSU email',
                'profile_picture.mimes' => 'Profile Picture must be in jpeg, png, or jpg format',
                'wmsu_email.regex' => 'Email must be in format: eh123456789@wmsu.edu.ph',
                'grade_level.required' => 'Please select a department',
                'wmsu_id_front.required' => 'Please upload the front of your WMSU ID',
                'wmsu_id_front.mimes' => 'WMSU ID front must be in jpeg, png, or jpg format',
                'wmsu_id_back.required' => 'Please upload the back of your WMSU ID',
                'wmsu_id_back.mimes' => 'WMSU Back front must be in jpeg, png, or jpg format',
            ]);
    
            // Handle file uploads first
            $profilePath = null;
            if ($request->hasFile('profile_picture')) {
                // Save to permanent storage
                $profilePath = Storage::disk('public')->put('highschool/profile_pictures', $request->profile_picture);
            }
            
            $idFrontPath = Storage::disk('public')->put('highschool/id_front', $request->wmsu_id_front);
            $idBackPath = Storage::disk('public')->put('highschool/id_back', $request->wmsu_id_back);
    
            // Get appropriate user type
            $userType = UserType::where('code', 'HS')->first()->id;
            $grade_level_id = GradeLevel::where('name', $fields['grade_level'])->first()->id;
    
            // Create user with all data
            $user = User::create([
                'user_type_id'=> $userType,
                'username' => $fields['username'],
                'password' => $fields['password'],
                'first_name' => $fields['first_name'],
                'last_name' => $fields['last_name'],
                'wmsu_email' => $fields['wmsu_email'],
                'grade_level_id' => $grade_level_id,
                'profile_picture' => $profilePath,
                'wmsu_id_front' => $idFrontPath,
                'wmsu_id_back' => $idBackPath,
            ]);
    
            // Login 
            Auth::login($user);
    
            // Redirect
            return redirect('/');
    
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors());
        }
    }

    // college student functions
    public function register_form_college()
    {
        $departments = Department::orderBy('name')->get();

        return view('auth.register-college', ['departments' => $departments]);
    }

    public function registerCollegeStudent(Request $request)
    {
        try {
            $fields = $request->validate([
                'username' => ['required', 'max:255', 'unique:users'],
                'password' => ['required', 'min:3'],
                'first_name' => ['required', 'max:255'],
                'last_name' => ['required', 'max:255'],
                'wmsu_email' => ['required', 'string', 'regex:/^eh[0-9]{9}@wmsu.edu.ph$/', 'unique:users'],
                'wmsu_dept' => ['required', 'string'],
                'profile_picture' => ['nullable', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
                'wmsu_id_front' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
                'wmsu_id_back' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
            ], [
                'wmsu_email.required' => 'Please enter your WMSU email',
                'profile_picture.mimes' => 'Profile Picture must be in jpeg, png, or jpg format',
                'wmsu_email.regex' => 'Email must be in format: eh123456789@wmsu.edu.ph',
                'wmsu_dept.required' => 'Please select a department',
                'wmsu_id_front.required' => 'Please upload the front of your WMSU ID',
                'wmsu_id_front.mimes' => 'WMSU ID front must be in jpeg, png, or jpg format',
                'wmsu_id_back.required' => 'Please upload the back of your WMSU ID',
                'wmsu_id_back.mimes' => 'WMSU Back front must be in jpeg, png, or jpg format',
            ]);
            
            // Handle file uploads first
            $profilePath = null;
            if ($request->hasFile('profile_picture')) {
                $profilePath = Storage::disk('public')->put('college/profile_pictures', $request->profile_picture);
            }
            $idFrontPath = Storage::disk('public')->put('college/id_front', $request->wmsu_id_front);
            $idBackPath = Storage::disk('public')->put('college/id_back', $request->wmsu_id_back);

            // Get appropriate user type
            $userType = UserType::where('code', 'COL')->first()->id;
            $wmsu_dept_id = Department::where('code', $fields['wmsu_dept'])->first()->id;
            // dd($wmsu_dept_id);

            // Create user with all data
            $user = User::create([
                'user_type_id'=> $userType,
                'username' => $fields['username'],
                'password' => $fields['password'],
                'first_name' => $fields['first_name'],
                'last_name' => $fields['last_name'],
                'wmsu_email' => $fields['wmsu_email'],
                'wmsu_dept_id' => $wmsu_dept_id,
                'profile_picture' => $profilePath,
                'wmsu_id_front' => $idFrontPath,
                'wmsu_id_back' => $idBackPath,
            ]);

            Auth::login($user);
            return redirect('/');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    // alumni functions
    public function register_form_alumni()
    {
        return view('auth.register-alumni');
    }

    public function registerAlumni(Request $request)
    {
        try {
            $fields = $request->validate([
                'username' => ['required', 'max:255', 'unique:users'],
                'password' => ['required', 'min:3'],
                'first_name' => ['required', 'max:255'],
                'last_name' => ['required', 'max:255'],
                'profile_picture' => ['nullable', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
                'wmsu_id_front' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
                'wmsu_id_back' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
            ], [
                'profile_picture.mimes' => 'Profile Picture must be in jpeg, png, or jpg format',
                'wmsu_id_front.required' => 'Please upload the front of your WMSU ID',
                'wmsu_id_front.mimes' => 'WMSU ID front must be in jpeg, png, or jpg format',
            ]);
            
            // Handle file uploads first
            $profilePath = null;
            if ($request->hasFile('profile_picture')) {
                $profilePath = Storage::disk('public')->put('alumni/profile_pictures', $request->profile_picture);
            }

            $idFrontPath = Storage::disk('public')->put('alumni/id_front', $request->wmsu_id_front);
            $idBackPath = Storage::disk('public')->put('alumni/id_back', $request->wmsu_id_back);

            // Get appropriate user type
            $userType = UserType::where('code', 'ALM')->first()->id;

            // Create user with all data
            $user = User::create([
                'user_type_id'=> $userType,
                'username' => $fields['username'],
                'password' => $fields['password'],
                'first_name' => $fields['first_name'],
                'last_name' => $fields['last_name'],
                'profile_picture' => $profilePath,
                'wmsu_id_front' => $idFrontPath,
                'wmsu_id_back' => $idBackPath,
            ]);

            Auth::login($user);
            return redirect('/');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    public function register_form_postgraduate()
    {

        $departments = Department::orderBy('name')->get();

        return view('auth.register-postGraduate', ['departments' => $departments]);
    }

    public function register_postGraduate(Request $request)
    {
        try {
            $fields = $request->validate([
                'username' => ['required', 'max:255', 'unique:users'],
                'password' => ['required', 'min:3'],
                'first_name' => ['required', 'max:255'],
                'last_name' => ['required', 'max:255'],
                'wmsu_email' => ['required', 'string', 'regex:/^eh[0-9]{9}@wmsu.edu.ph$/', 'unique:users'],
                'wmsu_dept' => ['required', 'string'],
                'profile_picture' => ['nullable', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
                'wmsu_id_front' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
                'wmsu_id_back' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
            ], [
                'wmsu_email.required' => 'Please enter your WMSU email',
                'profile_picture.mimes' => 'Profile Picture must be in jpeg, png, or jpg format',
                'wmsu_email.regex' => 'Email must be in format: eh123456789@wmsu.edu.ph',
                'wmsu_dept.required' => 'Please select a department',
                'wmsu_id_front.required' => 'Please upload the front of your WMSU ID',
                'wmsu_id_front.mimes' => 'WMSU ID front must be in jpeg, png, or jpg format',
                'wmsu_id_back.required' => 'Please upload the back of your WMSU ID',
                'wmsu_id_back.mimes' => 'WMSU Back front must be in jpeg, png, or jpg format',
            ]);
            
            // Handle file uploads first
            $profilePath = null;
            if ($request->hasFile('profile_picture')) {
                $profilePath = Storage::disk('public')->put('post_graduate/profile_pictures', $request->profile_picture);
            }
            $idFrontPath = Storage::disk('public')->put('post_graduate/id_front', $request->wmsu_id_front);
            $idBackPath = Storage::disk('public')->put('post_graduate/id_back', $request->wmsu_id_back);

            // Get appropriate user type
            $userType = UserType::where('code', 'PG')->first()->id;
            $wmsu_dept_id = Department::where('code', $fields['wmsu_dept'])->first()->id;
            // dd($wmsu_dept_id);

            // Create user with all data
            $user = User::create([
                'user_type_id'=> $userType,
                'username' => $fields['username'],
                'password' => $fields['password'],
                'first_name' => $fields['first_name'],
                'last_name' => $fields['last_name'],
                'wmsu_email' => $fields['wmsu_email'],
                'wmsu_dept_id' => $wmsu_dept_id,
                'profile_picture' => $profilePath,
                'wmsu_id_front' => $idFrontPath,
                'wmsu_id_back' => $idBackPath,
            ]);

            Auth::login($user);
            return redirect('/');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    //functions for employee
    public function register_form_employee(){

        return view('auth.register-employee');
    }

    public function registerEmployee(Request $request)
    {
        try {
            $fields = $request->validate([
                'username' => ['required', 'max:255', 'unique:users'],
                'password' => ['required', 'min:3'],
                'first_name' => ['required', 'max:255'],
                'last_name' => ['required', 'max:255'],
                'wmsu_email' => ['required', 'string', 'regex:/^eh[0-9]{9}@wmsu.edu.ph$/', 'unique:users'],
                'profile_picture' => ['nullable', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
            ], [
                'wmsu_email.required' => 'Please enter your WMSU email',
                'profile_picture.mimes' => 'Profile Picture must be in jpeg, png, or jpg format',
                'wmsu_email.regex' => 'Email must be in format: eh123456789@wmsu.edu.ph',
            ]);
            
            // Handle file uploads first
            $profilePath = null;
            if ($request->hasFile('profile_picture')) {
                $profilePath = Storage::disk('public')->put('employee/profile_pictures', $request->profile_picture);
            }
            // $idFrontPath = Storage::disk('public')->put('college/id_front', $request->wmsu_id_front);
            // $idBackPath = Storage::disk('public')->put('college/id_back', $request->wmsu_id_back);

            // Get appropriate user type
            $userType = UserType::where('code', 'EMP')->first()->id;
            // $wmsu_dept_id = Department::where('code', $fields['wmsu_dept'])->first()->id;
            // dd($wmsu_dept_id);

            // Create user with all data
            $user = User::create([
                'user_type_id'=> $userType,
                'username' => $fields['username'],
                'password' => $fields['password'],
                'first_name' => $fields['first_name'],
                'last_name' => $fields['last_name'],
                'wmsu_email' => $fields['wmsu_email'],
                'profile_picture' => $profilePath,
                // 'wmsu_dept_id' => $wmsu_dept_id,
                // 'wmsu_id_front' => $idFrontPath,
                // 'wmsu_id_back' => $idBackPath,
            ]);

            Auth::login($user);
            return redirect('/');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }
    
    //Logout User
    public function logout(Request $request) {
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
