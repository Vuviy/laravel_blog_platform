<?php

namespace App\Http\Middleware;

use App\ValueObjects\Id;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Modules\Users\Services\UserService;

class LoadUser
{

    public function __construct(private UserService $userService)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $user = null;
        $userId = session('user_id');
        if(!$userId){
            $request->attributes->set('user', $user);
            View::share('user', $user);
            return $next($request);
        }

        $user = $this->userService->getById(new Id($userId));

        if(!$user){
            $request->attributes->set('user', $user);
            View::share('user', $user);
            return $next($request);
        }

        $request->attributes->set('user', $user);
        View::share('user', $user);

        return $next($request);
    }
}
