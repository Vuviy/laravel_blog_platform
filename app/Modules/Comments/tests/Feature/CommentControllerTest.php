<?php
declare(strict_types=1);

namespace Modules\Comments\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Comments\Enums\EntityType;
use Modules\Comments\Services\CommentService;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    private CommentService $commentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commentService = $this->createMock(CommentService::class);
        $this->app->instance(CommentService::class, $this->commentService);
    }

    private function validData(array $override = []): array
    {
        return array_merge([
            'content'     => 'Тестовий коментар',
            'entity_id'   => 'b0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11',
            'entity_type' => EntityType::ARTICLE->value,
        ], $override);
    }

    public function testStoreCreatesCommentAndRedirects(): void
    {
        session(['user_id' => 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11']);

        $this->commentService
            ->expects($this->once())
            ->method('create')
            ->willReturn('new-comment-uuid');

        $response = $this->post(route('comments.store'), $this->validData());

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function testStoreValidationFailsWithoutContent(): void
    {
        $data = $this->validData();
        unset($data['content']);

        $response = $this->post(route('comments.store'), $data);

        $response->assertSessionHasErrors(['content']);
    }

    public function testStoreValidationFailsWhenContentTooShort(): void
    {
        $response = $this->post(route('comments.store'), $this->validData([
            'content' => 'ab',
        ]));

        $response->assertSessionHasErrors(['content']);
    }

    public function testStoreValidationFailsWhenContentTooLong(): void
    {
        $response = $this->post(route('comments.store'), $this->validData([
            'content' => str_repeat('a', 1001),
        ]));

        $response->assertSessionHasErrors(['content']);
    }


    public function testStoreValidationFailsWithoutEntityId(): void
    {
        $data = $this->validData();
        unset($data['entity_id']);

        $response = $this->post(route('comments.store'), $data);

        $response->assertSessionHasErrors(['entity_id']);
    }

    public function testStoreValidationFailsWithInvalidEntityIdUuid(): void
    {
        $response = $this->post(route('comments.store'), $this->validData([
            'entity_id' => 'not-a-uuid',
        ]));

        $response->assertSessionHasErrors(['entity_id']);
    }

    public function testStoreValidationFailsWithoutEntityType(): void
    {
        $data = $this->validData();
        unset($data['entity_type']);

        $response = $this->post(route('comments.store'), $data);

        $response->assertSessionHasErrors(['entity_type']);
    }

    public function testStoreValidationFailsWithInvalidParentIdUuid(): void
    {
        $response = $this->post(route('comments.store'), $this->validData([
            'parent_id' => 'not-a-uuid',
        ]));

        $response->assertSessionHasErrors(['parent_id']);
    }

    public function testStoreAcceptsValidParentId(): void
    {
        session(['user_id' => 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11']);

        $this->commentService
            ->method('create')
            ->willReturn('new-comment-uuid');

        $response = $this->post(route('comments.store'), $this->validData([
            'parent_id' => 'c0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11',
        ]));

        $response->assertSessionMissing('errors');
    }

    public function testStoreRedirectsWithErrorWhenNotLoggedIn(): void
    {
        $this->commentService
            ->expects($this->never())
            ->method('create');

        $response = $this->post(route('comments.store'), $this->validData());

        $response->assertRedirect();
        $response->assertSessionHasErrors(['message']);
    }

    public function testStoreAcceptsWithoutParentId(): void
    {
        session(['user_id' => 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11']);

        $this->commentService
            ->method('create')
            ->willReturn('new-comment-uuid');

        $response = $this->post(route('comments.store'), $this->validData());

        $response->assertSessionMissing('errors');
    }
}
