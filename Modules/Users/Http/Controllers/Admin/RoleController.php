<?php

namespace Modules\Users\Http\Controllers\Admin;

use App\ValueObjects\Id;
use Illuminate\Http\Request;
use Modules\Users\Services\RoleService;

class RoleController
{

    public function __construct(
        private RoleService $service,
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = $this->service->getAll();
        $title = 'Roles';

        return view('users::role.index', compact( 'roles','title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = __('common.create');
        $permissions = \Modules\Users\Enums\Permission::cases();

        return view('users::role.form', compact('title', 'permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $id = $this->service->create($request->all());
        return redirect(route('admin.roles.edit', ['role' => $id]))->with('success', 'Role created successfully');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('users::role.show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {

        $role = $this->service->getById(new Id($id));
        $title = __('common.edit');
        $permissions = \Modules\Users\Enums\Permission::cases();

        $selectedPermissionKeys = array_map(
            fn(\Modules\Users\Enums\Permission $permission) => $permission->value,
            $role->permissions
        );

        return view('users::role.form', compact('title', 'role', 'permissions', 'selectedPermissionKeys'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->service->update(new Id($id), $request->all());
        return redirect(route('admin.roles.edit', ['role' => $id]))->with('success', 'Role edited successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->service->delete(new Id($id));
        return redirect(route('admin.roles.index'))->with('success', 'Role deleted successfully');
    }
}
