<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Foundation\Http\FormRequest;

class LessonController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/lessons",
     *     summary="List lessons",
     *     tags={"lesson"},
     *     @OA\Response(response="200", description="Success"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index()
    {
        //
    }

    // TODO: Lessons will need to create and associate with flashcards based on user-selected criteria (tags, difficulty)

    /**
     * @OA\Get(
     *     path="/api/lessons/{id}",
     *     summary="Show a lesson",
     *     tags={"lesson"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Lesson not found"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function show(Lesson $lesson)
    {
        //
    }
}
