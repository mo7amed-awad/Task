<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class StatsController extends Controller
{
    public function index()
    {
        // Cache the statistics with a unique cache key
        $stats = Cache::remember('stats', 60 * 60, function () {
            return [
                'total_users' => User::count(),
                'total_posts' => Post::count(),
                'users_with_zero_posts' => User::doesntHave('posts')->count(),
            ];
        });

        return response()->json([
            'status' => 200,
            'message' => 'Statistics retrieved successfully',
            'data' => $stats,
        ]);
    }
}
