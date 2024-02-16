<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $storePostRequest)
    {
        $data = $storePostRequest->validated();
        Post::create($data);
        return back();
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $updatePostRequest, Post $post)
    {
        $post->update($updatePostRequest->validated());

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //to do: check if user has permission
        $id = Auth::id();

        if ($post->user_id !== $id ){
            return response("You have no Permission to delete", 403);
        }

        $post->delete();

        return back();
    }
}
