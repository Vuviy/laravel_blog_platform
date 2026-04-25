<?php

namespace Modules\Comments\Http\Controllers\Admin;

use App\Attributes\AllowedPermissions;
use App\ValueObjects\Id;
use Illuminate\Http\Request;
use Modules\Comments\Services\CommentService;
use Modules\Users\Enums\Permission;


class CommentsController
{
    public function __construct(private CommentService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comments = $this->service->getAll();

        $title = __('comments');

        return view('comments::admin.index' , compact('comments', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = __('common.comments');
        return view('comments::admin.form', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('comments::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $comment = $this->service->getCommentById(new Id($id));
        $title = __('comments');
        return view('comments::admin.form', compact('comment', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->service->update(new Id($id), $request->all());
        return redirect(route('admin.comments.edit', ['comment' => $id]))->with('success', 'comment edited successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    #[AllowedPermissions([Permission::COMMENT_DELETE->value])]
    public function destroy($id)
    {
        $this->service->delete(new Id($id));
        return redirect(route('admin.comments.index'))->with('success', 'comment deleted successfully');
    }
}
