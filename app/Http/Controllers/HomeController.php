<?php

namespace App\Http\Controllers;

use App\Http\Enums\GroupUserStatus;
use App\Http\Resources\GroupResource;
use App\Http\Resources\PostResource;
use App\Models\Group;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use PHPUnit\TextUI\Configuration\GroupCollection;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $posts = Post::query()
            ->withCount('reactions')
            ->with([
                'comments' => function ($query)  use ($userId){
                    $query->withCount('reactions');
                },
                'reactions' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->latest()->paginate(8);

        $posts = PostResource::collection($posts);
        if ($request->wantsJson()){
            return $posts;
        }

        $groups = Group::query()
            ->with('currentUserGroup')
            ->select(['groups.*'])
            ->join('group_users AS gu', 'gu.group_id', 'groups.id')
            ->where('gu.user_id', Auth::id())
//            ->where('status', GroupUserStatus::APPROVED->value)
            ->orderBy('gu.role')
            ->orderBy('name', 'desc')

            ->get()
        ;

        return Inertia::render('Home', [
            'posts' => $posts,
            'groups' => GroupResource::collection($groups)
        ]);
    }
}
