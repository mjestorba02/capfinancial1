<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/login';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Handle a registration request for the application.
     * Employees are created in employees table and can log in immediately.
     * Admins are created in users table with pending status; admin must approve before they can log in.
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $data = $request->all();

        if ($data['position'] === 'Employee') {
            $this->createEmployee($data);
            return redirect()->route('login')
                ->with('success', 'Registration submitted! You will be able to sign in to the employee portal after an admin approves your account.');
        }

        // Admin registration
        event(new Registered($user = $this->createUser($data)));

        return redirect()->route('login')
            ->with('pending_approval', true);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users'),
                Rule::unique('employees'),
            ],
            'position' => ['required', 'string', 'in:Employee,Admin,HR'],
            'department' => ['required', 'string', 'in:Finance'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user (admin) instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function createUser(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'position' => $data['position'],
            'department' => $data['department'],
            'approval_status' => 'pending',
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Create a new employee instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\Employee
     */
    protected function createEmployee(array $data)
    {
        return Employee::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'department' => $data['department'],
            'approval_status' => 'pending',
            'password' => Hash::make($data['password']),
        ]);
    }
}
