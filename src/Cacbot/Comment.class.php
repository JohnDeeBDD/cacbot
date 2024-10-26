<?php

namespace Cacbot;

class Comment
{
    /**
     * Main handler for the event that a user has posted a comment on the remote node.
     *
     * @param int $comment_ID The ID of the comment.
     * @return void
     * @throws \Exception
     */
    public static function do_on_WordPress_action__comment_post(int $comment_ID)
    {
        global $CacbotProtocol;
        if ($CacbotProtocol !== "remote_node") {
            return;
        }

        $Comment = \get_comment($comment_ID);

        // The plugin only responds to comments made on an Aion conversation CPT or posts with specific meta
        $post_type = \get_post_type($Comment->comment_post_ID);
        $is_anchor_post = \get_post_meta($Comment->comment_post_ID, '_cacbot_anchor_post', true) === "true";

        if ($post_type !== "cacbot-conversation" && !$is_anchor_post) {
            return;
        }

        if (Dialectic::is_dialectical_situation($Comment->comment_post_ID)) {
            self::do_process_comment_in_dialectical_situation($Comment);
        } else {
            self::do_process_comment($Comment);
        }
    }



    public static function do_process_comment_in_dialectical_situation($Comment) {

        $interlocutor_post_id = \get_post_meta($Comment->comment_post_ID, "_cacbot_interlocutor_post_id", true);

        // Check if the original post has 1 published comment and the interlocutor post has 0 published comments
        if ((self::get_published_comments_count($Comment->comment_post_ID) === 1) && (self::get_published_comments_count($interlocutor_post_id) === 0) ) {
            self::do_process_comment($Comment);

            // Retrieve the first two comments from the original post
            $comments = \get_comments([
                'post_id' => $Comment->comment_post_ID,
                'number' => 2, // Limit to the first two comments
                'status' => 'approve' // Only approved (published) comments
            ]);

            $comments = array_reverse($comments);
            foreach ($comments as $comment) {
                // Prepare the new comment data for the interlocutor post
                $new_comment_data = [
                    'comment_post_ID' => $interlocutor_post_id, // The new post ID
                    'comment_author' => $comment->comment_author,
                    'comment_author_email' => $comment->comment_author_email,
                    'comment_author_url' => $comment->comment_author_url,
                    'comment_content' => $comment->comment_content,
                    'comment_type' => $comment->comment_type,
                    'comment_parent' => 0, // If you want to make sure it's a top-level comment
                    'user_id' => $comment->user_id,
                    'comment_author_IP' => $comment->comment_author_IP,
                    'comment_agent' => $comment->comment_agent,
                    'comment_date' => current_time('mysql'),
                    'comment_date_gmt' => current_time('mysql', 1),
                    'comment_approved' => 1, // Automatically approve the copied comment
                ];

                // Insert the new comment for the interlocutor post
                $comment_ID = \wp_insert_comment($new_comment_data);
            }
            $Comment = \get_comment($comment_ID);
           // self::do_process_comment($Comment);

        }

    }

    /**
     * Get the number of published comments for a post.
     *
     * @param int $post_id The ID of the post.
     * @return int The number of published comments.
     */
    public static function get_published_comments_count( $post_id ) {
        // Make sure the post_id is an integer
        $post_id = (int) $post_id;

        // Use the WordPress get_comments() function to retrieve comments
        $comments_count = get_comments( array(
            'post_id' => $post_id,
            'status'  => 'approve', // Only count approved (published) comments
            'count'   => true       // Only return the count of comments
        ) );

        return $comments_count;
    }

    public static function do_process_comment($Comment){
        $comment_ID = $Comment->comment_ID;
        //Information in the HTTP request is stored in the database as comment meta:
        SettableHTTP_Properties::set_comment_meta_from_http_parameters($comment_ID);

        $Prompt = new Prompt();
        $Prompt->init_this_prompt($comment_ID, "created on remote");
        $Prompt->open_ai_api_key = ApiKey::set_open_ai_api_key_in_Prompt($comment_ID);
        $Prompt->populate_user_email_properties();
        \add_comment_meta($comment_ID, "Prompt_being_sent", \var_export($Prompt, true));
        $Interlocutor_user_id = \get_post_meta($Prompt->post_id, '_cacbot_interlocutor_user_id', true);
        $post_author_id = Interlocutor::get_post_author_id_from_comment($Comment);
        if($Interlocutor_user_id === "CLOSED"){
            die("You cannot do that.");
        }
        $next_speaker_user_id = Interlocutor::determine_next_speaker($Prompt->post_id, $Interlocutor_user_id);
        $BandwidthManager = new BandwidthManager();
        $Prompt = $BandwidthManager->manage_pre_send_Prompt($Prompt);
        if($Prompt->comments_open){
            $Prompt->response = $Prompt->send_up();
            \add_comment_meta($comment_ID, "raw_curl_response", \var_export($Prompt->response, true));
            //\add_comment_meta($comment_ID, "response_body", \var_export($Prompt->response['body'], true));
            if (isset($Prompt->model)) {
                if ($Prompt->model === "dall-e-3") {
                    $comment_content = Dall_E_3::doHandleResponse($Prompt);
                    self::put_comment_reply_on_post($next_speaker_user_id, $Prompt->post_id, $comment_content);
                    return;
                }
            }else{
                self::put_comment_reply_on_post($next_speaker_user_id, $Prompt->post_id, self::chopEnds($Prompt->response['body']));
            }
        }
        $Prompt = $BandwidthManager->manage_post_send_Prompt($Prompt);

    }

    /**
     * Set comment meta data from HTTP parameters.
     *
     * @param int $comment_id The ID of the comment.
     * @return void
     */
    public static function set_comment_meta_from_http_parameters($comment_id)
    {

        $parameters = [
            "functions",
            "account_user_id",
            "status",
            "completion_tokens",
            "max_tokens",
            "total_tokens",
            "prompt_tokens",
            "model",
            "open_ai_api_key",
            "remote_open_ai_api_key",
            "system_instructions",
            "reply_strategy"
        ];
        foreach ($parameters as $parameter) {
            if (isset($_REQUEST[$parameter])) {
                $meta_key = self::$meta_key . $parameter;
                \update_comment_meta($comment_id, $meta_key, $_REQUEST[$parameter]);
            }
        }
    }

    /**
     * Set the OpenAI API key in the comment meta if it does not already exist. When this function is run,
     * SettableHTTP_Properties::set_comment_meta_from_http_parameters should have already been run, therefore if a key
     * was posted in the HTTP request it should already be in the comment meta.
     *
     * @param int $comment_ID The ID of the comment.
     * @return string the api key
     */
    public static function set_open_ai_api_key_in_comment_meta($comment_ID)
    {
        $meta_key = "_cacbot_open_ai_api_key";
        $api_key = \get_comment_meta($comment_ID, $meta_key, true);

        if (empty($api_key)) {
            $api_key = ApiKey::get_openai_api_key();
            if ($api_key) {
                \update_comment_meta($comment_ID, $meta_key, $api_key);
            }
        }
        return $api_key;
    }

    /**
     * Insert a comment reply on a post.
     *
     * @param int $comment_author_user_id The user ID of the comment author.
     * @param int $post_id The ID of the post.
     * @param string $comment_content The content of the comment.
     * @return int The ID of the inserted comment.
     * @throws \Exception If an error occurs while posting the comment.
     */
    public static function put_comment_reply_on_post($comment_author_user_id, $post_id, $comment_content)
    {
       // $post_author_id = \get_post_field('post_author', $post_id);
        $user = \get_userdata($comment_author_user_id);
        $nicename = $user->display_name;
        $comment_content = str_replace('```', '###TRIPLE_BACKTICK###', $comment_content);
        $comment_data = array(
            'comment_post_ID' => $post_id,
            'comment_author' => $nicename,
            'comment_author_email' => $user->user_email,
            'comment_author_url' => "https://cacbot.com",
            'comment_content' => $comment_content,
            'comment_type' => 'comment',
            'comment_parent' => 0,
            'user_id' => $comment_author_user_id,
            'comment_date' => current_time('mysql'),
            'comment_approved' => 1,
        );
        $comment_id = \wp_insert_comment($comment_data);
        if ($comment_id) {
            return $comment_id;
        } else {
            throw new \Exception("An error occurred while posting the comment.");
        }
    }

    /**
     * Remove the first and last character from a string.
     *
     * @param string $string The input string.
     * @return string The modified string.
     */
    private static function chopEnds(string $string): string
    {
        if (strlen($string) <= 3) {
            return $string;
        }
        return substr($string, 1, -1);
    }

    /**
     * Function to intercept a comment before it is published.
     *
     * @param array $comment_data The comment data array.
     * @return array The modified comment data array.
     */
    public static function before_comment_post($comment_data)
    {
        global $CacbotProtocol;

        //if ("remote_node" === $CacbotProtocal) {
            // Check if the post type is 'cacbot-conversation'
            if (\get_post_type($comment_data['comment_post_ID']) !== "cacbot-conversation") {
                return $comment_data;
            }
            Interlocutor::set_interlocutor($comment_data);
       // }
        return $comment_data;
    }

    /**
     * Retrieves the content of the latest approved comment on a given post.
     *
     * @param int $post_id The ID of the post for which to retrieve the latest comment.
     * @return string|bool The content of the latest comment if available, or false if there are no comments.
     */
    public static function get_latest_comment_content($post_id) {
        // Get the latest comment for the given post ID
        $comments = \get_comments([
            'post_id' => $post_id,
            'number'  => 1,
            'orderby' => 'comment_date',
            'order'   => 'DESC',
            'status'  => 'approve' // Only fetch approved comments
        ]);

        // Check if there are any comments
        if (!empty($comments) && isset($comments[0])) {
            return $comments[0]->comment_content;
        }

        // Return false if no comments are found
        return false;
    }


}
