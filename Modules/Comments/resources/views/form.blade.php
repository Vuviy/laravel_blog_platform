<form class="card shadow-sm p-4 mt-4" method="POST" action="{{route('comments.store')}}">
    @csrf
    <h5 class="mb-3">Додати коментар</h5>

    <!-- Ім'я -->


{{--    <input type="hidden" name="user_id" value="{{ $user->id->getValue() }}">--}}
    <input type="hidden" name="entity_id" value="{{ $entityId }}">
    <input type="hidden" name="entity_type" value="{{ $entityType }}">

    <!-- Коментар -->
    <div class="mb-3">
        <label for="comment" class="form-label">Коментар</label>
        <textarea
            class="form-control"
            id="comment"
            name="content"
            rows="4"
            placeholder="Напишіть ваш коментар..."
            required
        ></textarea>
    </div>

    <!-- Кнопка -->
    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary px-4">
            Відправити
        </button>
    </div>
</form>
