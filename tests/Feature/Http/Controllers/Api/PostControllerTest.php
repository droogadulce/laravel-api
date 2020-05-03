<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Post;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_store()
    {
        //$this->withoutExceptionHandling();
        $response = $this->json('POST', '/api/posts', [
            'title' => 'The test post'
        ]);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => 'The test post'])
            ->assertStatus(201); // Ok, created resource

        $this->assertDatabaseHas('posts', ['title' => 'The test post']);
    }

    public function test_validate_title()
    {
        $response = $this->json('POST', '/api/posts', [
            'title' => ''
        ]);
        
        // HTTP 422 - Unprocessable Entity
        $response->assertStatus(422) 
            ->assertJsonValidationErrors('title');
    }

    public function test_show()
    {
        $post = factory(Post::class)->create();

        $response = $this->json('GET', "/api/posts/$post->id");

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
        ->assertJson(['title' => $post->title])
        ->assertStatus(200); // OK
    }

    public function test_404_show()
    {
        $response = $this->json('GET', '/api/posts/1000');

        $response->assertStatus(404); // Not Found
    }

    public function test_update()
    {
        $post = factory(Post::class)->create();

        $response = $this->json('PUT', "/api/posts/$post->id", [
            'title' => 'new'
        ]);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => 'new'])
            ->assertStatus(200); // OK

        $this->assertDatabaseHas('posts', ['title' => 'new']);
    }

    public function test_delete()
    {
        $post = factory(Post::class)->create();

        $response = $this->json('DELETE', "/api/posts/$post->id");

        $response->assertSee(null)
            ->assertStatus(204); // No Content

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_index()
    {
        factory(Post::class, 5)->create();

        $response = $this->json('GET', '/api/posts');
        
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'created_at', 'updated_at']
            ]
        ])->assertStatus(200); // OK
    }
}
