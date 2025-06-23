<?php

namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /** список полів, які ми приймаємо від Nuxt-форми */
    private const FILLABLE = [
        'title',
        'slug',
        'excerpt',
        'content_raw',
        'category_id',
        'is_published',
        'published_at',
    ];

    public function index(): JsonResponse
    {
        $posts = BlogPost::query()
            ->with(['user', 'category'])
            ->orderByDesc('id')
            ->get();

        return response()->json($posts, 200);
    }

    public function show(int $id): JsonResponse
    {
        $post = BlogPost::with(['user', 'category'])->find($id);

        return $post
            ? response()->json($post, 200)
            : response()->json(['message' => 'Post not found'], 404);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        /* якщо slug порожній – генеруємо його */
        $data['slug'] ??= Str::slug($data['title']);
        /* excerpt можна заповнювати автоматично з перших 200 символів */
        $data['excerpt'] ??= Str::limit(strip_tags($data['content_raw']), 200);

        $data['user_id'] = $request->user()?->id ?? 1;

        $post = BlogPost::create($data);

        return response()->json($post->load(['user', 'category']), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $post = BlogPost::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $data = $this->validated($request);

        $data['slug'] ??= Str::slug($data['title']);
        $data['excerpt'] ??= Str::limit(strip_tags($data['content_raw']), 200);

        $post->update($data);

        return response()->json($post->load(['user', 'category']), 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $post = BlogPost::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted'], 204);
    }

    /** валідація – усі поля, які приходять із Nuxt-форми */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title'         => 'required|string|min:3',
            'slug'          => 'nullable|string|min:3',
            'excerpt'       => 'nullable|string|min:3',
            'content_raw'   => 'required|string|min:5',
            'category_id'   => 'required|integer|exists:blog_categories,id',
            'is_published'  => 'boolean',
            'published_at'  => 'nullable|date',
        ]);
    }
}
