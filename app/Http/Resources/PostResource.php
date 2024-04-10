<?php

namespace App\Http\Resources;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $comments = $this->comments;
        return [
            'id' => $this->id,
            'body' => $this->body,
            'pinned' => $this->pinned,
            'created_at' => $this->created_at->format('Y-m_d H:i'),
            'updated_at' => $this->updated_at->format('Y-m_d H:i'),
            'user' =>  new UserResource($this->user),
            'group' => new GroupResource($this->group),
            'attachments' => PostAttachmentResource::collection( $this->attachment ?? []),
            'num_of_reactions' => $this->reactions_count,
            'num_of_comments' => count($comments),
            'current_user_has_reaction' => $this->reactions->count() > 0,
            'comments' => self::convertIntoTree($comments)
        ];
    }

    /**
     * @param Comment[] $comments
     * @param $parentId
     * @return array
     */
    private static function convertIntoTree($comments, $parentId = null): array
    {
        $commentTree = [];

        foreach ($comments as $comment) {
            if ($comment->parent_id === $parentId) {

                $children = self::convertIntoTree($comments, $comment->id);
                $comment->childComments = $children; //
                $comment->num_of_comments = collect($children)->sum('num_of_comments') + count($children);

                $commentTree[] =   new CommentResource($comment);
            }
        }

        return $commentTree;
    }
}
