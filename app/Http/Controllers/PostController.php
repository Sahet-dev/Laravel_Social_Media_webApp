<?php

namespace App\Http\Controllers;

use App\Http\Enums\PostReactionEnum;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\PostReaction;

class PostController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $storePostRequest)
    {
        $data = $storePostRequest->validated();
        $user = $storePostRequest->user();

        DB::beginTransaction();
        $allFilePaths = [];
        try {
            $post = Post::create($data);
            /** @var \Illuminate\Http\UploadedFile[] $files */
            $files = $data['attachments'] ?? [];
            foreach ($files as $file){
                $path = $file->store('attachments/'.$post->id, 'public');
                $allFilePaths[] = $path;
                PostAttachment::create([
                    'post_id' => $post->id,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'created_by' => $user->id,

                ]);
            }
            DB::commit();
        } catch (\Exception $exception){
            foreach ($allFilePaths as $path){
                Storage::disk('public')->delete($path);
            }

            DB::rollBack();
            throw $exception;
        }

        return back();
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $updatePostRequest, Post $post)
    {
        $user = $updatePostRequest->user();

        DB::beginTransaction();
        $allFilePaths = [];
        try {
            $data = $updatePostRequest->validated();
            $post->update($data);

            $deleted_ids = $data['deleted_file_ids'] ?? [];

            $attachments = PostAttachment::query()->where('post_id', $post->id)
                ->whereIn('id', $deleted_ids)
                ->get();
            foreach ($attachments as $attachment){
                $attachment->delete();
            }

            /** @var \Illuminate\Http\UploadedFile[] $files */
            $files = $data['attachments'] ?? [];
            foreach ($files as $file){
                $path = $file->store('attachments/'.$post->id, 'public');
                $allFilePaths[] = $path;
                PostAttachment::create([
                    'post_id' => $post->id,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path, // Ensure that $path is set correctly,
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'created_by' => $user->id,

                ]);
            }
            DB::commit();
        } catch (\Exception $exception){
            foreach ($allFilePaths as $path){
                Storage::disk('public')->delete($path);
            }

            DB::rollBack();
            throw $exception;
        }


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

    public function downloadAttachment(PostAttachment $postAttachment)
    {
        //TODO check is user has permission to download

        if ($postAttachment->path !== null) {
            // TODO: Check if the user has permission to download

            // Download the file
            return response()->download(
                Storage::disk('public')->path($postAttachment->path),
                $postAttachment->name
            );
        } else {
            // Handle the case where $postAttachment->path is null
            return response()->json(['error' => 'File path is null'], 500);
            // You c
        }
    }

    public function postReaction(Request $request, Post $post)
    {

        $data = $request->validate([
            'reaction' => [Rule::enum(PostReactionEnum::class)]
        ]);
        $userId = Auth::id();
        $reaction = PostReaction::where('user_id', $userId)->where('post_id', $post->id)->first();

        if($reaction){
            $hasReaction = false;
            $reaction->delete();
        }else{
            $hasReaction = true;
            PostReaction::create([
                'post_id' => $post->id,
                'user_id' => $userId,
                'type' => $data['reaction']
            ]);
        }



        $reactions = PostReaction::where('post_id', $post->id)->count();

        return response([
            'num_of_reactions' => $reactions,
            'current_user_has_reaction' => $hasReaction
            ]);
    }

    public function createComment(Request $request, Post $post)
    {
        $data = $request->validate([
            'comment' => ['required']
        ]);
        $comment = Comment::create([
            'post_id' => $post->id,
            'comment' => nl2br($data['comment']),
            'user_id' => Auth::id()
        ]);

        return response(new CommentResource($comment), 201);
    }

    public function deleteComment(Comment $comment)
    {
        if ($comment->user_id !== Auth::id()){
            return response("Access Denied", 403);
        }

        $comment->delete();
        return response('',204);
    }

    public function updateComment(UpdateCommentRequest $request, Comment $comment)
    {
        $data = $request->validate(['comment' => 'required|string',]);

        $comment->update([
            'comment' => nl2br($data['comment'])
        ]);
        return new CommentResource($comment);
    }
}
