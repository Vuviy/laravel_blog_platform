<?php

namespace App\Http\Middleware;

use App\Attributes\AllowedPermissions;
use Closure;
use Illuminate\Http\Request;

class CheckPermissionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        $action = $route->getAction();

        $controller = $action['controller'] ?? null;

        if (!$controller) {
            return $next($request);
        }

        [$controllerClass, $method] = explode('@', $controller);

        $reflectionMethod = new \ReflectionMethod($controllerClass, $method);
        $attributes = $reflectionMethod->getAttributes(AllowedPermissions::class);

        if (0 === count($attributes)) {
            return $next($request);
        }

        $allowedPermissions = $attributes[0]->newInstance()->permissions;

        if (0 === count($allowedPermissions)) {
            return $next($request);
        }

        $user = session('user');

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
