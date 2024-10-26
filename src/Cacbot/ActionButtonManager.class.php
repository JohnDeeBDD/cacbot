<?php

namespace Cacbot;

/**
 * Class responsible for managing action buttons related to Aion Conversations.
 */
class ActionButtonManager
{

    /**
     * Registers the action to add custom buttons to the comment form.
     */
    public static function enable_custom_comment_buttons()
    {
        \add_filter('comment_form_field_comment', '\Cacbot\ActionButtonManager::add_tool_icons_to_comment_form');
        \add_action('wp_enqueue_scripts', '\Cacbot\ActionButtonManager::enqueue_dashicons');
    }

    /**
     * Adds tool icons below the comment form as action buttons.
     */
    public static function add_tool_icons_to_comment_form($field)
    {
        global $post;

        $comments_count = get_comments_number( $post->ID );

        $Dall_e_3_button = "";
        $Archive_comments_button = "";

        if($comments_count > 0){
            $Archive_comments_button = '<span id = "cacbot-archive-button"  class="dashicons dashicons-trash"></span>';
        }else{
            $Dall_e_3_button = '<span id = "cacbot-dall-e-3-button" class="dashicons dashicons-format-image"></span>';
        };

        if(!metadata_exists('post', $post->ID , '_cacbot_image')){
            $Dall_e_3_button = "";
        }

        $icons = '<style>
.comment-tool-icons {
    display: flex;
    justify-content: flex-start; /* Align icons to the left */
    margin-top: 10px; /* Add space between the comment box and the icons */
}
.comment-tool-icons .dashicons {
    font-size: 24px;
    margin-right: 10px; /* Add space between icons */
    margin-bottom: 10px; /* Add space between icons */
    cursor: pointer;
}
</style>
<div class="comment-tool-icons">' . $Archive_comments_button . $Dall_e_3_button . '</div>';
        return $field . $icons;
    }

    public static function enqueue_dashicons()
    {
        \wp_enqueue_style('dashicons');
    }
}