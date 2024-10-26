<?php

namespace Cacbot;

class Scripts
{

    public static function do_on_WordPress_action__wp_enqueue_scripts()
    {

        // Register and enqueue the cacbot-conversation-cpt.js script
        \wp_register_script(
            'cacbot-conversation-cpt',
            \plugin_dir_url(__FILE__) . 'cacbot-conversation-cpt.js', // The JS file
            ['jquery', 'wp-api', 'heartbeat'],
            '1.0',
            true
        );
        \wp_enqueue_script('cacbot-conversation-cpt');

        // Retrieve the current post ID safely
        $post_id = get_queried_object_id();
        if ($post_id && is_numeric($post_id)) {

            // Localize script with nonce and post ID
            \wp_localize_script(
                'cacbot-conversation-cpt',
                'cacbot_data',
                array(
                    'nonce' => \wp_create_nonce("cacbot-action"), // Security nonce
                    'post_id' => $post_id // Current post ID
                )
            );
        }

        // Register and enqueue the cacbot-dialectic.js script
        \wp_register_script(
            'cacbot-dialectic',
            \plugin_dir_url(__FILE__) . 'cacbot-dialectic.js', // The JS file
            ['jquery', 'wp-api', 'wp-api-fetch'],
            '1.0',
            true
        );
        \wp_enqueue_script('cacbot-dialectic');


        \wp_register_script(
            'mods-2016',
            \plugin_dir_url(__FILE__) . 'mods-2016.js', // The JS file
            ['jquery', 'wp-api', 'wp-api-fetch'],
            '1.0',
            true
        );
        \wp_enqueue_script('mods-2016');
    }
}
