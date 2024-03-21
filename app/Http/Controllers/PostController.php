<?php

namespace App\Http\Controllers;

use App\Http\Enums\ReactionEnum;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostAttachment;
use App\Models\User;
use App\Notifications\CommentCreated;
use App\Notifications\CommentDeleted;
use App\Notifications\PostCreated;
use App\Notifications\PostDeleted;
use App\Notifications\ReactionAddedOnComment;
use App\Notifications\ReactionAddedOnPost;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Reaction;

class PostController extends Controller
{
    public function view(Post $post)
    {
        $post->loadCount('reactions');
        $post->load([
            'comments' => function ($query) {
                $query->withCount('reactions');
            },
        ]);
        return inertia('Post/View', ['post' => new PostResource($post)]);
    }

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


            $group = $post->group;

            if ($group){
                $users = $group->approvedUsers()
                    ->where('users.id', '!=', $user->id)
                    ->get();
                Notification::send($users, new PostCreated($post, $group));
            }


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

        if ($post->isOwner($id) || $post->group && $post->group->isAdmin($id)){
            $post->delete();
            if (!$post->isOwner($id))
            {
                $post->user->notify(new PostDeleted($post->group));
            }
            return back();
        }


        return response("You have no Permission to delete", 403);
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
            'reaction' => [Rule::enum(ReactionEnum::class)]
        ]);
        $userId = Auth::id();
        $reaction = Reaction::where('user_id', $userId)
            ->where('object_id', $post->id)
            ->where('object_type', Post::class)
            ->first();

        if($reaction){
            $hasReaction = false;
            $reaction->delete();
        }else{
            $hasReaction = true;
            Reaction::create([
                'object_id' => $post->id,
                'object_type' => Post::class,
                'user_id' => $userId,
                'type' => $data['reaction']
            ]);
            if (!$post->isOwner($userId)){
                $user = User::where('id', $userId)->first();
                $post->user->notify(new ReactionAddedOnPost($post, $user));

            }
        }



        $reactions = Reaction::where('object_id', $post->id)->where('object_type', Post::class)->count();

        return response([
            'num_of_reactions' => $reactions,
            'current_user_has_reaction' => $hasReaction
            ]);
    }

    public function createComment(Request $request, Post $post)
    {
        $data = $request->validate([
            'comment' => ['required'],
            'parent_id' => ['nullable', 'exists:comments,id']
        ]);
        $comment = Comment::create([
            'post_id' => $post->id,
            'comment' => nl2br($data['comment']),
            'user_id' => Auth::id(),
            'parent_id' => $data['parent_id'] ?: null

        ]);

        $post = $comment->post;
        $post->user->notify(new CommentCreated($comment, $post));

        return response(new CommentResource($comment), 201);
    }

    public function deleteComment(Comment $comment)
    {
        $post = $comment->post;
        $id = Auth::id();
        if ($comment->isOwner($id) ||  $post->isOwner($id)){
            $comment->delete();

            if (!$comment->isOwner($id)){
                $comment->user->notify(new CommentDeleted($comment, $post));
            }

            return response('',204);
        }

        return response("Access Denied", 403);


    }

    public function updateComment(UpdateCommentRequest $request, Comment $comment)
    {
        $data = $request->validate(['comment' => 'required|string',]);

        $comment->update([
            'comment' => nl2br($data['comment'])
        ]);
        return new CommentResource($comment);
    }



    public function commentReaction(Request $request, Comment $comment)
    {
        $data = $request->validate([
            'reaction' => [Rule::enum(ReactionEnum::class)]
        ]);
        $userId = Auth::id();
        $reaction = Reaction::where('user_id', $userId)
            ->where('object_id', $comment->id)
            ->where('object_type', Comment::class)
            ->first();

        if($reaction){
            $hasReaction = false;
            $reaction->delete();
        }else{
            $hasReaction = true;
            Reaction::create([
                'object_id' => $comment->id,
                'object_type' => Comment::class,
                'user_id' => $userId,
                'type' => $data['reaction']
            ]);
            if (!$comment->isOwner($userId)){
                $user = User::where('id', $userId)->first();
                $comment->user->notify(new ReactionAddedOnComment($comment->post, $comment, $user));

            }
        }



        $reactions = Reaction::where('object_id', $comment->id)->where('object_type', Comment::class)->count();

        return response([
            'num_of_reactions' => $reactions,
            'current_user_has_reaction' => $hasReaction
        ]);
    }
}
