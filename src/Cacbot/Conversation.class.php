<?php

namespace Cacbot;

class Conversation
{

    public static function enable_cacbot_conversation_cpt()
    {
        \add_action('init', [self::class, 'register_aion_conversation_cpt']);
    }

    public static function register_aion_conversation_cpt()
    {
        $labels = [
            'name'                  => _x('Cacbot Conversations', 'cacbot'),
            'singular_name'         => _x('Cacbot Conversation', 'cacbot'),
            'menu_name'             => _x('Cacbot Conversations', 'cacbot'),
            'name_admin_bar'        => _x('Cacbot Conversation', 'cacbot'),
            'add_new'               => _x('Add New', 'cacbot'),
            'add_new_item'          => __('Add New Cacbot Conversation', 'cacbot'),
            'new_item'              => __('New Cacbot Conversation', 'cacbot'),
            'edit_item'             => __('Edit Cacbot Conversation', 'cacbot'),
            'view_item'             => __('View Cacbot Conversation', 'cacbot'),
            'all_items'             => __('All Cacbot Conversations', 'cacbot'),
            'search_items'          => __('Search Cacbot Conversations', 'cacbot'),
            'parent_item_colon'     => __('Parent Cacbot Conversations:', 'cacbot'),
            'not_found'             => __('No Cacbot Conversations found.', 'cacbot'),
            'not_found_in_trash'    => __('No Cacbot Conversations found in Trash.', 'cacbot')
        ];

        $args = [
            'labels'                => $labels,
            'description'           => __('A custom post type for Cacbot Conversations.', 'cacbot'),
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'query_var'             => true,
            'rewrite'               => ['slug' => 'cacbot-conversation'],
            'capability_type'       => 'post',
            'has_archive'           => false,
            'hierarchical'          => true,
            'menu_position'         => null,
            'rest_base'             => 'cacbot-conversations',
            'show_in_rest'          => true,
            'supports'              => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'revisions', 'page-attributes'],
            'taxonomies'            => ['category', 'post_tag']
        ];

        \register_post_type('cacbot-conversation', $args);
        \flush_rewrite_rules();
    }

    /**
     * Parses a conversation title string into its components.
     *
     * The expected format is "remote_post_id:initial_speaker_user_id:remote_site_url".
     * Note: The remote site URL can contain colons (e.g., "https://").
     * Note: The Aion who is responding is the post author
     *
     * @param string $titleString The conversation title string to parse.
     * @return array|null An associative array of components, or null if the format is incorrect.
     */
    public static function parseCacbotConversationTitle($titleString) {
        $pattern = '/^(\d+):(\d+):(.+)$/';

        if (preg_match($pattern, $titleString, $matches)) {
            if (count($matches) === 4) {
                return [
                    'remote_post_id' => $matches[1],
                    'user_id'        => $matches[2],
                    'remote_site_url' => $matches[3]
                ];
            }
        }

        error_log('parseCacbotConversationTitle: Incorrect format for title string: ' . $titleString);
        return null;
    }

    /**
     * Builds a conversation title string from its components.
     *
     * @param int $remote_post_id The remote post ID.
     * @param int $user_id The user ID of the initial speaker.
     * @param string $remote_site_url The URL of the remote site.
     * @return string The constructed title string.
     */
    public static function buildCacbotConversationTitle($remote_post_id, $user_id, $remote_site_url) {
        return sprintf('%d:%d:%s', $remote_post_id, $user_id, $remote_site_url);
    }

    /**
     * Function to set the comment status of a WordPress post.
     *
     * @param int $post_id The ID of the post to update.
     * @param string $status The desired comment status ('open' or 'closed').
     * @return bool True on success, false on failure.
     */
    public static function set_post_comment_status($post_id, $status) {
        // Ensure the status is valid ('open' or 'closed')
        if (!in_array($status, array('open', 'closed'))) {
            return false; // Invalid status provided
        }

        // Ensure the post ID is valid
        if (empty($post_id) || !is_numeric($post_id)) {
            return false; // Invalid post ID
        }

        // Use the WordPress function to update the comment status
        $result = \wp_update_post(array(
            'ID' => $post_id,
            'comment_status' => $status
        ));

        // Return true on success, false on failure
        return ($result !== 0);
    }


}
