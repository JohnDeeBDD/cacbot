<?php

namespace Cacbot;

class Plugin{

    public static function do_create_admin_page(){
        if (!current_user_can('manage_options')) {
            return;
        }
        add_menu_page(
            'Aion Admin Page', // Page title
            'Aion', // Menu title
            'manage_options', // Capability
            'aion-admin-page', // Menu slug
            'Cacbot\Plugin::cacbot_admin_page_content', // Callback function for content
            'dashicons-admin-generic', // Icon
            99 // Position
        );
    }

    public static function cacbot_admin_page_content()
    {
        // Check if form has been submitted
        if (isset($_POST['submit'])) {
            // Verify nonce
            check_admin_referer('aion_admin_page_nonce_action', 'aion_admin_page_nonce');

            // Update the OpenAI API Key option
            $openai_api_key = sanitize_text_field($_POST['openai-api-key']);
            update_option('openai-api-key', $openai_api_key);

            // Save the Ion Chat Protocol option
            $ion_chat_protocol = sanitize_text_field($_POST['cacbot-protocol']);
            update_option('cacbot-protocol', $ion_chat_protocol);
        }

        // Get the existing options, if any
        $existing_api_key = get_option('openai-api-key', '');
        $existing_protocol = get_option('cacbot-protocol', 'remote_node'); // Default to 'remote_node'

        ?>
        <div class="wrap">
            <h1>Aion Chat Settings</h1>
            <form method="post" action="">
                <?php wp_nonce_field('aion_admin_page_nonce_action', 'aion_admin_page_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="openai-api-key">OpenAI API Key</label>
                        </th>
                        <td>
                            <input
                                    name="openai-api-key"
                                    type="text"
                                    id="openai-api-key"
                                    value="<?php echo esc_attr($existing_api_key); ?>"
                                    placeholder="Get at https://platform.openai.com/"
                                    class="regular-text"
                                    oninput="checkInput()"
                            />
                        </td>
                    </tr>
                    <tr style = "display:none;">
                        <th scope="row">Aion Chat Protocol</th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="radio" name="cacbot-protocol" value="remote_node" <?php checked($existing_protocol, 'remote_node'); ?>>
                                    Remote Node
                                </label><br>
                                <label>
                                    <input type="radio" name="cacbot-protocol" value="mothership" <?php checked($existing_protocol, 'mothership'); ?>>
                                    Mothership
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input
                            type="submit"
                            name="submit"
                            id="submit"
                            class="button button-primary"
                            value="Save Changes"
                        <?php echo($existing_api_key === '' ? 'disabled' : ''); ?>
                    />
                </p>
            </form>
            <script>
                function checkInput() {
                    const apiKeyInput = document.getElementById("openai-api-key");
                    const submitButton = document.getElementById("submit");
                    submitButton.disabled = apiKeyInput.value === "";
                }
            </script>
        </div><?php
    }

}