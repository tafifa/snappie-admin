<?php

namespace App\Observers;

use App\Models\UserComment;
use Illuminate\Support\Facades\Log;

class UserCommentObserver
{
    /**
     * Handle the UserComment "created" event.
     * Note: total_comment increment is handled in SocialService::createComment()
     */
    public function created(UserComment $comment): void
    {
        // Increment is already handled in SocialService::createComment()
        // This observer is primarily for handling deletions
    }

    /**
     * Handle the UserComment "deleted" event.
     */
    public function deleted(UserComment $comment): void
    {
        // Decrement post's total_comment
        if ($comment->post_id) {
            $post = \App\Models\Post::find($comment->post_id);
            if ($post && $post->total_comment > 0) {
                $post->decrement('total_comment');
                Log::info("Decremented total_comment for post {$post->id} after comment {$comment->id} deletion");
            }
        }
    }
}
