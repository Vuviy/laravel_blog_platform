<?php

namespace App\Http\Middleware;

use App\Contracts\SessionManagerInterface;
use App\ValueObjects\Id;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Modules\Users\Services\UserService;

class AdminUserMiddleware
{
    public function __construct(private UserService $userService)
//    public function __construct(private UserService $userService, private SessionManagerInterface $sessionManager)
    {
    }

    public function handle(Request $request, Closure $next)
    {
//        $userId = $this->sessionManager->get('user_id');
        $userId = session('user_id');

        if(!$userId){
            return redirect()->route('admin.loginForm');
        }

        $user = $this->userService->getById(new Id($userId));

        if(!$user->hasRole('admin')){
            return redirect()->route('home', ['locale' => App::getLocale()]);
        }

        return $next($request);
    }
}
