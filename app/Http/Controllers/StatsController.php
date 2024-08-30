<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Post;

class StatsController extends Controller
{
    public function index()
    {
        $stats = Cache::rememberForever('stats', function () {
            return [
                'total users' => User::count(),
                'total posts' => Post::count(),
                'users dont have posts' => User::doesntHave('posts')->count(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $stats,
        ], 200);
    }

}
