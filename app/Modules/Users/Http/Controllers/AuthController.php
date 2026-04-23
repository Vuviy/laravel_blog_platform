<?php

namespace Modules\Users\Http\Controllers;

use App\Contracts\SessionManagerInterface;
use Illuminate\Http\Request;
use Modules\Users\Http\Requests\LoginRequest;
use Modules\Users\Http\Requests\RegisterRequest;
use Modules\Users\Services\AuthService;

class AuthController
{

    public function __construct(private AuthService $authService, private SessionManagerInterface $sessionManager)
    {
    }

    public function loginForm(Request $request)
    {
        return view('users::login');
    }

    public function registerForm(Request $request)
    {
        return view('users::register');
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $errors = $this->authService->login($data);

        if(0 < count($errors)) {
            return redirect()->back()->withErrors($errors);
        }

        $user = $this->sessionManager->get('user');

        if($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('home', ['locale' => app()->currentLocale()]);
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $this->authService->register($data);

        return redirect()->route('loginForm', ['locale' => app()->currentLocale()]);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request);
        return redirect()->route('home', ['locale' => app()->currentLocale()]);
    }
}
