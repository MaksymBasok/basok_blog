<?php

namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Http\Requests\Api\Blog\Category\StoreRequest;
use App\Http\Requests\Api\Blog\Category\UpdateRequest;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(BlogCategory::all());
}

public function show($id)
{
$category = BlogCategory::find($id);
if (!$category) {
return response()->json(['message' => 'Категорію не знайдено'], 404);
}
return response()->json($category);
}

public function store(StoreRequest $request)
{
$category = BlogCategory::create($request->validated());
return response()->json($category, 201);
}

public function update(UpdateRequest $request, $id)
{
$category = BlogCategory::find($id);
if (!$category) {
return response()->json(['message' => 'Категорію не знайдено'], 404);
}
$category->update($request->validated());
return response()->json($category);
}

public function destroy($id)
{
$category = BlogCategory::find($id);
if (!$category) {
return response()->json(['message' => 'Категорію не знайдено'], 404);
}
$category->delete();
return response()->json(['message' => 'Категорію видалено']);
}
}
