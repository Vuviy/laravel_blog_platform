<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Users\Http\Requests\LoginRequest;
use Modules\Users\Services\UserService;
use Modules\Users\ValueObjects\Email;

class AdminController extends Controller
{

    public function __construct(private UserService $service)
    {
    }

    public function index(Request $request)
    {
        $title = 'Admin';
        $user = $request->attributes->get('user');
        return view('admin.index', compact('title' , 'user'));
    }

    public function loginForm()
    {
        $title = 'Login';
        return view('admin.login_form', compact('title'));
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

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home', ['locale' => app()->currentLocale()]);

    }

}
