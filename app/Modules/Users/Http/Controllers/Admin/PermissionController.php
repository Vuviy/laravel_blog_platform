<?php

namespace Modules\Users\Http\Controllers\Admin;

use App\ValueObjects\Id;
use Illuminate\Http\Request;
use Modules\Users\Http\Requests\PermissionCreateRequest;
use Modules\Users\Services\PermissionService;

class PermissionController
{

    public function __construct(
        private PermissionService $service
    ) {}


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = $this->service->getAll();
        $title = 'Permissions';

        return view('users::permission.index', compact( 'permissions','title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Create a new permissions';
        return view('users::permission.form', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PermissionCreateRequest $request)
    {
        $id = $this->service->create($request->all());
        return redirect(route('admin.permissions.edit', ['permission' => $id]))->with('success', 'permissions created successfully');

    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('permissions::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $permission = $this->service->getById(new Id($id));
        $title = 'Edit a new permission';

        return view('users::permission.form', compact('title', 'permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->service->update(new Id($id), $request->all());
        return redirect(route('admin.permissions.edit', ['permission' => $id]))->with('success', 'permissions edited successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->service->delete(new Id($id));
        return redirect(route('admin.permissions.index'))->with('success', 'permission deleted successfully');
    }
}
