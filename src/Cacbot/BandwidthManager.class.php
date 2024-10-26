<?php

namespace Cacbot;

class BandwidthManager
{
    protected Prompt $Prompt;
    public int $published_comment_count;

    // Evaluate the current bandwidth state before a Prompt is sent
    public function manage_pre_send_Prompt($Prompt)
    {
        // At this point, a comment has been published on the post, and now we are deciding if we should send up the
        // Prompt to another server. This will cause a response to be generated, thus one more comment. Is this allowed?
        // Get the current published comment count for the post
        $Prompt->comments_open = true;
        $this->published_comment_count = Comment::get_published_comments_count($Prompt->post_id);
        return $this->manage_send_Prompt($Prompt);
    }

    // Evaluate the bandwidth state after a Prompt is sent
    public function manage_post_send_Prompt($Prompt){
        $this->published_comment_count = $this->published_comment_count + 1;
        return $this->manage_send_Prompt($Prompt);
    }

    public function manage_send_Prompt($Prompt){

        if(isset($Prompt->max_replies)){
            // If the number of comments is below the allowed max replies, open the comments
            if ($this->published_comment_count  < $Prompt->max_replies) {
                $Prompt->comments_open = true;
            } else {
                // Otherwise, close the comments
                $Prompt->comments_open = false;
                // Close comments for the post
                BandwidthManager::close_comments($Prompt->post_id);
            }
        }
        return $Prompt;
    }
    // Close the comments on the current post
    public static function close_comments($post_id)
    {
        // Update the post to close comments
        \wp_update_post([
            'ID' => $post_id,
            'comment_status' => 'closed'
        ]);
    }


}
