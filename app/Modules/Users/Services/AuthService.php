<?php

declare(strict_types=1);

namespace Modules\Users\Services;

use App\Contracts\SessionManagerInterface;
use Illuminate\Http\Request;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;

class AuthService
{

    public function __construct(private SessionManagerInterface $sessionManager, private UserService $userService)
    {
    }

    public function login(array $credentials): array
    {
        $errors = [];
        $user = $this->userService->getByEmail(new Email($credentials['email']));

        if (!$user || !$user->password->verify($credentials['password'])) {
            $errors = ['message' => 'Invalid login credentials.'];
            return $errors;
        }

        $this->sessionManager->store('user_id', $user->id->getValue());
        $this->sessionManager->store('user', $user);
        $this->sessionManager->regenerate();

        return [];
    }

    public function register(array $data): void
    {
        $this->userService->create($data);
    }

    public function logout(Request $request): void
    {
        $this->sessionManager->flush();
        $this->sessionManager->regenerate();
    }
}
