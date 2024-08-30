<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Tags\StoreTagRequest;
use App\Http\Requests\Tags\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Exception;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => TagResource::collection(Tag::all())
        ], 200);
    }

    public function store(StoreTagRequest $request)
    {
        try {
            $tag = Tag::create($request->validated());

            return response()->json([
                'status' => 'success',
                'data' => new TagResource($tag)
            ], 201);
        } catch (Exception $e) {
            Log::error('Failed to create tag: ' . $e->getMessage());

            return response()->json([
                'status' => 'failed',
                'message' => 'Server error, please try again later.'
            ], 500);
        }
    }

    public function show(string $id)
    {
        $tag= Tag::find($id);

        if(!$tag){
            return response()->json([
                'status' => 'failed',
                'message' => 'Tag not found.'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => new TagResource($tag)
        ], 200);
    }

    public function update(UpdateTagRequest $request, Tag $tag)
    {
        try {
            $tag->update($request->validated());

            return response()->json([
                'status' => 'success',
                'data' => new TagResource($tag)
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to update tag: ' . $e->getMessage());

            return response()->json([
                'status' => 'failed',
                'message' => 'Server error, please try again later.'
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {

            $tag= Tag::find($id);

            if(!$tag){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Tag not found.'
                ], 404);
            }
            $tag->delete();

            return response()->json([
                'status' => 'success',
                'data' => null
            ], 204);
        } catch (Exception $e) {
            Log::error('Failed to delete tag: ' . $e->getMessage());

            return response()->json([
                'status' => 'failed',
                'message' => 'Server error, please try again later.'
            ], 500);
        }
    }
}
