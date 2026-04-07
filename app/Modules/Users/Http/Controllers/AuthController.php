<?php

namespace Modules\Users\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Users\Http\Requests\LoginRequest;
use Modules\Users\Http\Requests\RegisterRequest;
use Modules\Users\Services\UserService;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;

class AuthController
{

    public function __construct(
        private UserService $service,
    )
    {
    }

    public function loginForm(Request $request)
    {
        $user = $request->attributes->get('user');

        return view('users::login', compact('user'));
    }

    public function registerForm(Request $request)
    {
        $user = $request->attributes->get('user');
        return view('users::register' , compact('user'));
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = $this->service->getByEmail(new Email($data['email']));

        if (!$user || !$user->password->verify($data['password'])) {
            return redirect()->back()->withErrors(['message' => 'Invalid login credentials.']);
        }

        session(['user_id' => $user->id->getValue()]);
        session(['user' => $user]);
        $request->session()->regenerate();

        return redirect()->route('home', ['locale' => app()->currentLocale()]);

    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $this->service->create($data);

        return redirect()->route('loginForm', ['locale' => app()->currentLocale()]);
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home', ['locale' => app()->currentLocale()]);

    }


}
