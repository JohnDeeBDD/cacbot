<?php
/*
Plugin Name: Cacbot
Plugin URI: https://cacbot.com
Description: The Comments Are a Chat Bot
Version: 4
Author: johndee
Author URI: https://generalchicken.guru
License: Copyright(C) 2024, generalchicken.guru . All rights reserved. THIS IS NOT FREE SOFTWARE.
*/

namespace Cacbot;

//die("Cacbot");

global $CacbotProtocol;
$CacbotProtocol = "remote_node";

require_once(plugin_dir_path(__FILE__) . 'src/Cacbot/autoloader.php');

global $Servers;
$Servers = new Servers();

//Setup WordPress filters to accommodate the plugin:
\add_filter('comment_flood_filter', '__return_false');
\add_filter('duplicate_comment_id', '__return_false');
\add_filter('wp_is_application_passwords_available', '__return_true');

//Admin Page:
\add_action('admin_menu', '\Cacbot\Plugin::do_create_admin_page');

//Aion user role:
\add_action('init', '\Cacbot\User::add_cacbot_role');

//Aion Conversation Custom Post Type:
Conversation::enable_cacbot_conversation_cpt();
Functions::enableFunctionCall();

CommentOverride::enable_comment_override();

//Main plugin starting point is when someone makes a comment on an Cacbot conversation CPT:
\add_filter('preprocess_comment', '\Cacbot\Comment::before_comment_post');
\add_action('comment_post', '\Cacbot\Dialectic::converse', 5, 1);
\add_action('comment_post', '\Cacbot\Comment::do_on_WordPress_action__comment_post', 10, 1);

//Action: Archive
Action_Archive::enable();



ExampleConversation::enablePublishExampleConversations();



\register_activation_hook(__FILE__, '\Cacbot\ActivationHook::do_activation_hook');
require_once(plugin_dir_path(__FILE__) . 'src/update-checker/plugin-update-checker.php');
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
use function register_meta;

PucFactory::buildUpdateChecker(    'https://cacbot.com/wp-content/uploads/details.json',__FILE__,'cacbot');

add_action( 'init', 'Cacbot\custom_register_post_meta' );

function custom_register_post_meta() {
    $args = [
        'auth_callback' => '__return_true',
        'type'          => 'boolean',
        'single'        => true,
        'show_in_rest'  => true,
    ];

    \register_meta( 'cacbot-conversation', '_cacbot_system_instructions', $args );
}

\add_filter( 'is_protected_meta', '__return_false' );

\add_action( 'wp', function() {

    if(\is_singular()) {
        global $post;
        Navigator::manage_redirect($post->ID);

        ActionButtonManager::enable_custom_comment_buttons();
        // Check if it's a singular 'cacbot-conversation' post type
        if (\is_singular("cacbot-conversation")) {
            \add_action('wp_enqueue_scripts', '\Cacbot\Scripts::do_on_WordPress_action__wp_enqueue_scripts');
            \Cacbot\CommentOverride::enable_comment_override();
        }

        // Check if the post meta _cacbot_anchor_post exists and is set to the string "true"
        $anchor_post = \get_post_meta($post->ID, '_cacbot_anchor_post', true);
        if ($anchor_post === 'true') {
            \add_action('wp_enqueue_scripts', '\Cacbot\Scripts::do_on_WordPress_action__wp_enqueue_scripts');
            \Cacbot\CommentOverride::enable_comment_override();
        }
    }

});


// Register the shortcode
\add_shortcode( 'fileEmbed', '\Cacbot\PostFileEmbedder::display_text_file_content' );

