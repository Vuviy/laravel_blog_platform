<?php

namespace App\Console\Commands;

use App\ValueObjects\Id;
use Illuminate\Console\Command;
use Modules\Users\Entities\Role;
use Modules\Users\Entities\User;
use Modules\Users\Enums\Permission;
use Modules\Users\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Users\Repositories\Contracts\UserRepositoryInterface;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\RoleName;
use Modules\Users\ValueObjects\Username;

class CreateAdminCommand extends Command
{

    public function __construct(private UserRepositoryInterface $userRepository, private RoleRepositoryInterface $roleRepository)
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create roles(admin, user); create admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->roleRepository->save($this->createRole('user'));
            $adminRoleId = $this->roleRepository->save($this->createRole('admin'));

            $this->roleRepository->syncPermissions(new Id($adminRoleId), array_column(Permission::cases(), 'value'));

            $userId = $this->userRepository->save($this->createAdminUser());

            $this->userRepository->syncRoles(new Id($userId), [$adminRoleId]);

            $this->info('Created role user2');
            $this->info('Created role admin2');
            $this->info('Created admin user with credentials: email admin2@admin.com, password password');
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }

    }

    private function createAdminUser(): User
    {
        return new User(
            email: new Email('admin@admin.com'),
            username: new Username('admin'),
            password: Password::fromPlain('password'),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    private function createRole(string $roleName): Role
    {
        return new Role(
            name: new RoleName($roleName),
            permissions: [],
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable()
        );
    }
}
