<?php

namespace Modules\Users\Http\Controllers\Admin;

use App\ValueObjects\Id;
use Illuminate\Http\Request;
use Modules\Users\Entities\Role;
use Modules\Users\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Users\Services\UserService;

class UsersController
{
    public function __construct(
        private UserService $service,
        private RoleRepositoryInterface $roleRepository,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = $this->service->getAll();
        $title = 'Users';

        return view('users::admin.index', compact( 'users','title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = __('common.create');
        $roles = $this->roleRepository->getAllList();

        return view('users::admin.form', compact('title', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $id = $this->service->create($request->all());

        return redirect(route('admin.users.edit', ['user' => $id]))->with('success', 'User created successfully');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('users::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = $this->service->getById(new Id($id));
        $title = __('common.edit');
        $roles = $this->roleRepository->getAllList();

        $selectedRoleIds = array_map(
            fn(Role $role) => $role->id->getValue(),
            $user->roles
        );

        return view('users::admin.form', compact('title', 'user', 'roles', 'selectedRoleIds'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->service->update(new Id($id), $request->all());
        return redirect(route('admin.users.edit', ['user' => $id]))->with('success', 'User edited successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->service->delete(new Id($id));
        return redirect(route('admin.users.index'))->with('success', 'User deleted successfully');
    }
}
