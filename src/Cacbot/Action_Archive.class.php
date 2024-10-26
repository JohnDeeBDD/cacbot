<?php

namespace Cacbot;

class Action_Archive{

    public static function enable(){
        if(isset($_GET['model'])){
            if($_GET['model'] === "archive"){
                \add_action('init', function(){
                    if(!\wp_verify_nonce($_POST['nonce'], "cacbot-action")){
                        die("Something is wrong Action_Archive 12");
                    }
                    Action_Archive::do_archive_comments_locally($_POST['post_id']);
                    Action_Archive::do_archive_comments_on_mothership($_POST['post_id']);
                });
            }
        }
    }

    public static function do_archive_comments_locally($post_id) {
        // Get all comments associated with the given post_id
        $comments = \get_comments(array('post_id' => $post_id));

        // Check if there are any comments to delete
        if (!empty($comments)) {
            foreach ($comments as $comment) {
                // Use wp_delete_comment to delete each comment
                \wp_delete_comment($comment->comment_ID, true); // 'true' forces permanent deletion
            }
        }
    }


    public static function do_archive_comments_on_mothership($post_id){
        $site_url = \get_site_url();
        global $Servers;
        $Cacbot_mothership_url = $Servers->mothershipURL;
        $tickle = $Cacbot_mothership_url . "/wp-json/cacbot/v1/archive_comments?post_id=" . $post_id . "&url=" . $site_url;
        $response = \wp_remote_post($tickle, []);
    }

}