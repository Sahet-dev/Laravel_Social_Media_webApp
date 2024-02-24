<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Models\PostAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
