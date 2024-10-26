<?php

namespace Cacbot;

class CommentOverride {

    /**
     * Initialize the class by setting up WordPress hooks.
     */
    public static function enable_comment_override() {
        // Hook into the 'comments_array' filter to replace comments with those from the Aion Conversation
        \add_filter('comments_array', [__CLASS__, 'filter_comments_array'], 10, 2);

        // Hook into 'preprocess_comment' to redirect new comments to the Aion Conversation post
        \add_filter('preprocess_comment', [__CLASS__, 'handle_preprocess_comment']);

        // Hook into 'comment_post_redirect' to keep the user on the anchor post after commenting
        \add_filter('comment_post_redirect', [__CLASS__, 'redirect_to_anchor_post'], 10, 2);
    }

    /**
     * Transfers meta data from anchor post to cloned post using the settable meta keys.
     *
     * @param int $anchorPostId ID of the anchor post
     * @param int $clonePostId ID of the cloned post
     * @return void
     */
    public static function transferMetaFromAnchorToClone($anchorPostId, $clonePostId): void
    {
        // Iterate over the settable meta keys from the CommentMeta trait
        foreach (SettableMetaKeys::$settable_meta_keys as $key) {
            $prefixedKey = SettableMetaKeys::$prefix;

            // Check if the meta key exists on the anchor post
            if (\metadata_exists('post', $anchorPostId, $prefixedKey)) {
                // Get the meta value from the anchor post
                $metaValue = \get_post_meta($anchorPostId, $prefixedKey, true);

                // Transfer the meta value to the clone post
                \update_post_meta($clonePostId, $prefixedKey, sanitize_text_field($metaValue));
            }
        }

        // Optionally, you can add any specific logic for transferring system instructions
        if (\metadata_exists('post', $anchorPostId, "_cacbot_system_instructions")) {
            $systemInstructions = \get_post_meta($anchorPostId, "_cacbot_system_instructions", true);
            \update_post_meta($clonePostId, "_cacbot_system_instructions", sanitize_text_field($systemInstructions));
        }
    }

    /**
     * Check if a given post is marked as an anchor post.
     * This relies on the post meta "_cacbot_anchor_post".
     *
     * @param int $post_id
     * @return bool
     */
    public static function is_anchor_post($post_id) {
        return \get_post_meta($post_id, '_cacbot_anchor_post', true) === 'true';
    }

    /**
     * Retrieve the Aion Conversation post ID linked to the user for a specific anchor post.
     *
     * @param int $user_id
     * @param int $post_id
     * @return int|false
     */
    public static function get_linked_conversation($user_id, $post_id) {
        return \get_user_meta($user_id, "_cacbot_linked_conversation_{$post_id}", true);
    }

    /**
     * Set the Aion Conversation post ID for a user and anchor post.
     *
     * @param int $user_id
     * @param int $post_id
     * @param int $conversation_id
     */
    public static function set_linked_conversation($user_id, $post_id, $conversation_id) {
        \update_user_meta($user_id, "_cacbot_linked_conversation_{$post_id}", $conversation_id);
    }
    /**
     * Create a new Aion Conversation for a specific user and post.
     *
     * @param int $user_id
     * @param int $post_id
     * @return int|false
     */
    public static function create_conversation($user_id, $post_id) {
        $conversation_args = [
            'post_title'   => sprintf('Cacbot Conversation for User %d on Post %d', $user_id, $post_id),
            'post_content' => '',
            'post_status'  => 'private',
            'post_type'    => 'cacbot-conversation',
            'post_author'  => $user_id,
            'meta_input'   => [
                '_linked_anchor_post' => $post_id, // Store the anchor post ID in the conversation
            ],
        ];

        $conversation_id = \wp_insert_post($conversation_args);

        if (!\is_wp_error($conversation_id)) {
            // Link the new conversation to the user and anchor post
            self::set_linked_conversation($user_id, $post_id, $conversation_id);

            // Transfer meta data from anchor post to the newly created conversation (clone)
            self::transferMetaFromAnchorToClone($post_id, $conversation_id);

            return $conversation_id;
        }

        return false; // Return false if the conversation creation failed
    }

    /**
     * Retrieve the conversation for a user and post if it already exists.
     *
     * @param int $user_id
     * @param int $post_id
     * @return \WP_Post|null
     */
    public static function get_conversation($user_id, $post_id) {
        $conversation_id = self::get_linked_conversation($user_id, $post_id);
        if ($conversation_id) {
            return \get_post($conversation_id);
        }
        return null;
    }

    /**
     * Filter the comments array to replace them with comments from the Aion Conversation.
     *
     * @param array $comments
     * @param int $post_id
     * @return array
     */
    public static function filter_comments_array($comments, $post_id) {
        // Check if the user is logged in
        if (!\is_user_logged_in()) {
            // Non-logged-in users see the default comments
            return $comments;
        }

        $user_id = \get_current_user_id();

        // Check if the post is an anchor post
        if (!self::is_anchor_post($post_id)) {
            // Non-anchor posts show default comments
            return $comments;
        }

        // Get the user's linked conversation
        $conversation = self::get_conversation($user_id, $post_id);

        if (!$conversation) {
            // If no linked conversation exists, create one
            $conversation_id = self::create_conversation($user_id, $post_id);
            if ($conversation_id) {
                $conversation = \get_post($conversation_id);
            } else {
                // If conversation creation failed, return default comments
                return $comments;
            }
        }

        // Fetch comments from the Aion Conversation post
        $conversation_comments = \get_comments([
            'post_id' => $conversation->ID,
            'order'   => 'ASC',
        ]);

        return $conversation_comments;
    }

    /**
     * Handle new comments and redirect them to the Aion Conversation post.
     *
     * @param array $commentdata
     * @return array
     */
    public static function handle_preprocess_comment($commentdata) {
        // Check if the user is logged in
        if (!\is_user_logged_in()) {
            // Do nothing for non-logged-in users
            return $commentdata;
        }

        $user_id = \get_current_user_id();
        $post_id = $commentdata['comment_post_ID'];

        // Check if the post is an anchor post
        if (!self::is_anchor_post($post_id)) {
            // Do nothing for non-anchor posts
            return $commentdata;
        }

        // Get the user's linked conversation
        $conversation = self::get_conversation($user_id, $post_id);

        if (!$conversation) {
            // If no linked conversation exists, create one
            $conversation_id = self::create_conversation($user_id, $post_id);
            if ($conversation_id) {
                $conversation = \get_post($conversation_id);
            } else {
                // If conversation creation failed, do nothing
                return $commentdata;
            }
        }

        // Redirect the comment to the Aion Conversation post
        $commentdata['comment_post_ID'] = $conversation->ID;

        return $commentdata;
    }

    /**
     * Set the interlocutor's user ID on the cloned post if the current user is not the author.
     *
     * @param int $clone_post_id
     * @param int $anchor_post_id
     */
    public static function set_interlocutor($clone_post_id, $anchor_post_id) {
        // Get the author ID of the anchor post
        $anchor_author_id = get_post_field('post_author', $anchor_post_id);
        $current_user_id = get_current_user_id();

        // If the current user is not the author of the anchor post, set the interlocutor
        if ($anchor_author_id != $current_user_id) {
            update_post_meta($clone_post_id, '_cacbot_interlocutor', $anchor_author_id);
        }
    }

    /**
     * After a comment is posted, redirect the user back to the anchor post
     * instead of the clone post.
     *
     * @param string $location The URL to redirect the user after comment submission
     * @param \WP_Comment $comment The comment object
     * @return string The modified redirect URL
     */
    public static function redirect_to_anchor_post($location, $comment) {
        // Get the post ID where the comment was originally posted (the clone post)
        $clone_post_id = $comment->comment_post_ID;

        // Get the anchor post ID from the clone post's meta data
        $anchor_post_id = \get_post_meta($clone_post_id, '_linked_anchor_post', true);

        // If we have a valid anchor post ID, modify the redirect location to point to the anchor post
        if ($anchor_post_id) {
            $location = \get_permalink($anchor_post_id);
        }

        return $location;
    }
}
