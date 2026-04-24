<?php

namespace App\Http\Middleware;

use App\Attributes\AllowedPermissions;
use App\ValueObjects\Id;
use Closure;
use Illuminate\Http\Request;
use Modules\Users\Services\UserService;

class CheckPermissionMiddleware
{

    public function __construct(private UserService $userService)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        $controller = $route->getController();
        $method = $route->getActionMethod();

        if (!$controller || !$method) {
            return $next($request);
        }

        $reflectionMethod = new \ReflectionMethod($controller, $method);
        $attributes = $reflectionMethod->getAttributes(AllowedPermissions::class);


        if (0 === count($attributes)) {
            return $next($request);
        }

        $allowedPermissions = $attributes[0]->newInstance()->permissions;

        if (0 === count($allowedPermissions)) {
            return $next($request);
        }

        $userId = session('user_id');
        if (null === $userId) {
            abort(403);
        }
        $user = $this->userService->getById(new Id($userId));

        if (null === $user) {
            abort(403);
        }

        $hasPermission = collect($allowedPermissions)->every(
            fn($permission) => $user->hasPermission($permission)
        );

        if (false === $hasPermission) {
            return redirect()->back()->withErrors( ['message' => 'Not allowed to perform this action.']);
        }

        return $next($request);
    }
}
