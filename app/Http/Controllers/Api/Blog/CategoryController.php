<?php

namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogCategoryCreateRequest;
use App\Http\Requests\BlogCategoryUpdateRequest;
use App\Models\BlogCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    /**
     * Повертаємо всі категорії разом із їхньою батьківською.
     */
    public function index(): JsonResponse
    {
        $categories = BlogCategory::with('parentCategory:id,title')
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $categories], Response::HTTP_OK);
    }

    /**
     * Створення нової категорії.
     */
    public function store(BlogCategoryCreateRequest $request): JsonResponse
    {
        $data         = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

        $category = BlogCategory::create($data)
            ->load('parentCategory:id,title');

        return response()->json($category, Response::HTTP_CREATED);
    }

    /**
     * Показ однієї категорії.
     */
    public function show(BlogCategory $category): JsonResponse
    {
        return response()->json(
            $category->load('parentCategory:id,title'),
            Response::HTTP_OK
        );
    }

    /**
     * Оновлення існуючої категорії.
     */
    public function update(
        BlogCategoryUpdateRequest $request,
                                  $id
    ): JsonResponse {
        $category = BlogCategory::findOrFail($id);

        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

        $category->update($data);

        $category->refresh()->load('parentCategory:id,title');

        return response()->json([
            'message'  => 'Категорію оновлено!',
            'category' => $category
        ], Response::HTTP_OK);
    }


    /**
     * Видалення категорії (soft-delete).
     */
    public function destroy($id): JsonResponse
    {
        $category = BlogCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Категорію видалено!'
        ], Response::HTTP_NO_CONTENT);
    }
}
