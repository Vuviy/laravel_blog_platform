<div class="comments mt-4">

    @foreach($comments as $comment)

        @php
            $offset = $comment->depth * 25;
        @endphp
        <div class="mb-3" style="margin-left: {{ $offset }}px; border-left: 2px solid #eee; padding-left: 10px;">

            <div class="d-flex mb-4">

                <!-- Avatar -->
                <div class="flex-shrink-0">
                    <img src="https://i.pravatar.cc/50?u={{ $comment->userId }}"
                         class="rounded-circle"
                         width="50"
                         height="50"
                         alt="avatar">
                </div>

                <!-- Content -->
                <div class="flex-grow-1 ms-3">

                    <!-- Header -->
                    <div class="d-flex align-items-center mb-1">
                        <strong class="me-2">
                            {{ $comment->getUser()->username->getValue() }}
                        </strong>

                        <small class="text-muted">
                            {{ \Carbon\Carbon::parse($comment->created_at)->diffForHumans() }}
                        </small>
                    </div>

                    <!-- Body -->
                    <div class="bg-light p-3 rounded">
                        {{ $comment->content->getValue() }}
                    </div>

                    <!-- Actions -->
                    @if($user)
                        <div class="mt-1">

                            <div class="mt-1">
                                <button
                                    class="reply-btn text-decoration-none small me-2"
                                    data-comment-id="{{ $comment->id }}"
                                >
                                    Reply
                                </button>
                            </div>

                            <div id="reply-container-{{ $comment->id }}" class="mt-2" style="display: none">
                                <form method="POST" action="{{route('comments.store')}}">

                                    @csrf

                                    {{--                            <input type="hidden" name="user_id" value="{{ $user->id->getValue() }}">--}}
                                    <input type="hidden" name="entity_id" value="{{ $article->id }}">
                                    <input type="hidden" name="entity_type" value="{{ get_class($article) }}">
                                    <input type="hidden" name="parent_id" value="{{ $comment->id->getValue() }}">

                                    <div class="mb-2">
                                <textarea name="content" class="form-control" rows="2"
                                          placeholder="Your reply"></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-sm btn-primary">
                                        Send
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    @endforeach

</div>

<script>
    $(document).on('click', '.reply-btn', function () {

        const commentId = $(this).data('comment-id');
        const $container = $('#reply-container-' + commentId);


        if ($container.css("display") === 'none') {
            $container.css('display', 'block');
        } else {
            $container.css('display', 'none');
        }
        return;
    });
</script>
