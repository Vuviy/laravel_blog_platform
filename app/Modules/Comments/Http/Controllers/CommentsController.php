<?php

namespace Modules\Comments\Http\Controllers;

use Modules\Comments\Http\Requests\CommentCreateRequest;
use Modules\Comments\Services\CommentService;

class CommentsController
{

    public function __construct(private CommentService $commentService)
    {
    }

    public function store(CommentCreateRequest $request)
    {
        $data = $request->validated();

        if(!session('user_id')){
            return redirect()->back()->withErrors(['message' => 'You not signed in!']);
        }
        $data['user_id'] = session('user_id');

        $this->commentService->create($data);

        return redirect()->back()->with('success', 'Comment created.');
    }
}
