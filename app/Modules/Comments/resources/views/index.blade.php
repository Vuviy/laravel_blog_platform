<div class="comments mt-4">

    @foreach($comments as $comment)
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
                <div class="mt-1">
                    <a href="#" class="text-decoration-none small me-2">Reply</a>
                    <a href="#" class="text-decoration-none small text-muted">Report</a>
                </div>

            </div>
        </div>
    @endforeach

</div>
