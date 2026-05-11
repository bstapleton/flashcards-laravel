<?php

namespace App\Http\Controllers;

use App\Enums\TagColour;
use App\Helpers\ApiResponse;
use App\Models\Tag;
use App\Transformers\BaseTransformer;
use App\Transformers\TagTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

class TagController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/subjects",
     *     summary="List subjects",
     *     tags={"tag"},
     *
     *     @OA\Response(response="200", description="Success"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index()
    {
        return response()->collection(Tag::all()->toArray(), new BaseTransformer);
    }

    /**
     * @OA\Post(
     *     path="/api/subjects",
     *     summary="Create a tag",
     *     tags={"tag"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "colour"},
     *
     *             @OA\Property(property="name", type="string", example="Mathematics"),
     *             @OA\Property(property="colour", type="string", example="green"),
     *         )
     *     ),
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="400", description="Error"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function store(Request $request)
    {
        if ($request->user()->tags()->count() > config('flashcard.tag_limit')) {
            return ApiResponse::error(
                'Cannot create more subjects',
                'You can only have a maximum of '.config('flashcard.tag_limit').' subjects per account.',
                'unable_to_create_tag'
            );
        }

        $request->validate([
            'name' => 'required|max:255',
            'colour' => ['required', new Enum(TagColour::class)],
        ]);

        $tag = Tag::firstOrCreate([
            'user_id' => $request->user()->id,
            'name' => $request->input('name'),
            'colour' => $request->input('colour'),
        ]);

        return fractal($tag, new TagTransformer)->respond();
    }

    /**
     * @OA\Patch(
     *     path="/api/subjects/{tag}",
     *     summary="Update a tag",
     *     tags={"tag"},
     *
     *     @OA\Parameter(name="tag", in="path", @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "colour"},
     *
     *             @OA\Property(property="name", type="string", example="Mathematics"),
     *             @OA\Property(property="colour", type="string", example="green"),
     *         )
     *     ),
     *
     *     @OA\Parameter(name="name", in="query", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Tag not found"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function update(FormRequest $request, Tag $tag)
    {
        if ($tag->user_id !== $request->user()->id) {
            return ApiResponse::error(
                'Unable to update tag',
                'You do not have permission to update this tag',
                'unable_to_update_tag'
            );
        }

        $request->validate([
            'name' => 'required|max:255',
            'colour' => ['required', new Enum(TagColour::class)],
        ]);

        if (! $tag->exists()) {
            return response()->json(ModelNotFoundException::class, 404);
        }

        $tag->update($request->all());

        return fractal($tag, new TagTransformer)->respond();
    }

    /**
     * @OA\Delete(
     *     path="/api/subjects/{id}",
     *     summary="Delete a tag",
     *     tags={"tag"},
     *
     *     @OA\Parameter(name="id", in="path", @OA\Schema(type="integer")),
     *
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
