<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;

class TagController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tags",
     *     description="List tags",
     *     tags={"tag"},
     *     @OA\Response(response="200", description="Success"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index()
    {
        return response()->json(Tag::all());
    }

    /**
     * @OA\Post(
     *     path="/api/tags",
     *     description="Create a tag",
     *     tags={"tag"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function store(FormRequest $request)
    {
        $request->validate([
            'name' => 'required|max:255'
        ]);

        // Don't create duplicates
        Tag::firstOrCreate($request->all());

        return redirect()->route('tags.index')->with('success', 'Tag created');
    }

    /**
     * @OA\Put(
     *     path="/api/tags/{id}",
     *     description="Update a tag",
     *     tags={"tag"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Tag not found"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function update(FormRequest $request, Tag $tag)
    {
        $request->validate([
            'name' => 'required|max:255'
        ]);

        if (!$tag->exists()) {
            return response()->json(ModelNotFoundException::class, 404);
        }

        $tag->update($request->all());

        return redirect()->route('tags.index')->with('success', 'Tag created');
    }

    /**
     * @OA\Delete(
     *     path="/api/tags/{id}",
     *     description="Delete a tag",
     *     tags={"tag"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="204", description="No content"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function destroy(Tag $tag)
    {
        $tag->flashcards()->detach($tag);
        $tag->delete();

        return response()->noContent();
    }
}
