<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; 
use Exception;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::where('user_id', $request->user()->id)
                    ->with('tags')
                    ->orderBy('pinned', 'desc')
                    ->get();

        return response()->json([
            'status' => 'success',
            'data' => PostResource::collection($posts)
        ], 200);
    }

    public function store(StorePostRequest $request)
    {
        try {
            $post = new Post($request->validated());
            $post->user_id = $request->user()->id;

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('photos/posts', 'public');
                $post->photo = $path;
            }

            $post->save();
            $post->tags()->sync($request->tags);

            return response()->json([
                'status' => 'success',
                'data' => new PostResource($post)
            ], 201);
        } catch (Exception $e) {
            Log::error('Failed to store post: ' . $e->getMessage());

            return response()->json([
                'status' => 'failed',
                'message' => 'Server error, please try again later.'
            ], 500);
        }
    }

    public function show($id, Request $request)
    {
        $post = Post::where('id', $id)
                    ->with('tags')
                    ->where('user_id', $request->user()->id)
                    ->first();

        if (!$post) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Post not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new PostResource($post)
        ], 200);
    }

    public function update(UpdatePostRequest $request, $id)
    {
        try {
            $post = Post::where('id', $id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$post) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Post not found'
                ], 404);
            }

            if ($request->hasFile('photo')) {
                if ($post->photo) {
                    Storage::delete($post->photo);
                }
                $path = $request->file('photo')->store('photos/posts', 'public');
                $post->photo = $path;
            }

            $post->update($request->validated());
            $post->tags()->sync($request->tags);

            return response()->json([
                'status' => 'success',
                'data' => new PostResource($post)
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to update post: ' . $e->getMessage());

            return response()->json([
                'status' => 'failed',
                'message' => 'Server error, please try again later.'
            ], 500);
        }
    }

    public function destroy($id, Request $request)
    {
        try {
            $post = Post::where('id', $id)
                        ->where('user_id', $request->user()->id)
                        ->first();

            if (!$post) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Post not found'
                ], 404);
            }

            $post->delete();

            return response()->json(null, 204);

        } catch (Exception $e) {
            Log::error('Failed to delete post: ' . $e->getMessage());

            return response()->json([
                'status' => 'failed',
                'message' => 'Server error, please try again later.'
            ], 500);
        }
    }

    public function deletedPosts(Request $request)
    {
        $posts = Post::onlyTrashed()
            ->with('tags')
            ->where('user_id', $request->user()->id)
            ->orderBy('pinned', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => PostResource::collection($posts)
        ], 200);
    }

    public function restore($id, Request $request)
    {
        try {
            $post = Post::onlyTrashed()
                ->where('id', $id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$post) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Post not found'
                ], 404);
            }

            $post->restore();

            return response()->json([
                'status' => 'success',
                'data' => new PostResource($post)
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to restore post: ' . $e->getMessage());

            return response()->json([
                'status' => 'failed',
                'message' => 'Server error, please try again later.'
            ], 500);
        }
    }
}
