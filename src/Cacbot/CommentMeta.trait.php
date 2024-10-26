<?php

namespace Cacbot;

trait CommentMeta
{

    // List of meta keys that can be set for a comment
    public $settable_meta_keys = [
        "account_user_id",
        "interlocutor_user_id",
        "interlocutor_user_email",
        "completion_tokens",
        "functions",
        "instructions",
        "max_tokens",
        "max_replies",
        "model",
        "open_ai_api_key",
        "prompt_tokens",
        "reply_strategy",
        "status",
        "system_instructions",
        "total_tokens",
    ];

    public array $converatible_id_to_email_properties = [
        "",
        "account_",
        "author_",
        "interlocutor_",
        ];


    //these properties are stored by "ID" key locally. We need to convert the user IDs to emails for transport.
    public function populate_user_email_properties() {
        foreach ($this->converatible_id_to_email_properties as $property) {

            $id_property = $property . "user_id";
            // Check if the ID property is set in the current object
            if (isset($this->{$id_property})) {
                $user_id = $this->{$id_property};

                // Check if the ID is an integer
                if (!is_int($user_id)) {
                    return new \WP_Error('invalid_user_id', sprintf('The user ID for %s must be an integer.', $id_property));
                }

                // Attempt to get the user object
                $user = \get_userdata($user_id);

                // Check if the user object was retrieved successfully
                if (!$user) {
                    return new \WP_Error('user_not_found', sprintf('User with ID %d not found for %s.', $user_id, $id_property));
                }

                // Set the email property in the current object
                $email_property = $property . "user_email";
                $this->{$email_property} = $user->user_email;
            }
        }
    }


    /**
     * Sets comment meta data from prompt properties.
     *
     * This method iterates through the settable meta keys and updates the comment meta
     * with the corresponding property values from the class instance, if they are set.
     *
     * @param int $commentId ID of the comment to update
     * @return void
     */
    public function setCommentMetaFromPromptProperties(int $commentId): void
    {
        foreach ($this->settable_meta_keys as $key) {
            if (isset($this->{$key})) {
                $prefixedKey = $this->getPrefixedMetaKey($key);
                if(($this->{$key} === "") or ($this->{$key} === false)){
                    continue;
                }
                \update_comment_meta($commentId, $prefixedKey, sanitize_text_field($this->{$key}));
            }
        }

        //testing:
        \update_comment_meta($commentId, "_cacbot_incoming_Prompt", \var_export($this, true));
    }


    /**
     * Sets meta data for a comment from an array of key-value pairs.
     * Only keys that are in the list of settable keys are stored.
     *
     * @param array $metaKeys List of settable meta keys
     * @param array $keyValuePairs Array of key-value pairs to set as meta data
     * @param int $commentId ID of the comment to update
     */
    public function setCommentMetaFromKeyValuePairs(array $metaKeys, array $keyValuePairs, int $commentId): void
    {
        foreach ($keyValuePairs as $pair) {
            if (!$this->isValidSinglePair($pair)) {
                continue;
            }

            $key = key($pair);
            $value = current($pair);
            if (in_array($key, $metaKeys, true)) {
                $prefixedKey = $this->getPrefixedMetaKey($key);
                \update_comment_meta($commentId, $prefixedKey, sanitize_text_field($value));
            }
        }
    }

    /**
     * Checks if the given item is a valid single key-value pair.
     *
     * @param mixed $item The item to check
     * @return bool True if valid, false otherwise
     */
    private function isValidSinglePair($item): bool
    {
        return is_array($item) && count($item) === 1;
    }

    /**
     * Sets prompt properties from comment meta data.
     *
     * @param int $commentId ID of the comment to retrieve meta data from
     */
    public function setPromptPropertiesFromCommentMeta(int $commentId, int $post_id): void
    {
        foreach ($this->settable_meta_keys as $key) {
            $metaKey = $this->getPrefixedMetaKey($key);
            $metaValue = \get_comment_meta($commentId, $metaKey, true);

            if (!empty($metaValue)) {
                $this->{$key} = $metaValue;
            }
        }
    }

    /**
     * Sets prompt properties from post meta data.
     *
     * @param int $postId ID of the post to retrieve meta data from
     */
    public function setPromptPropertiesFromPostMeta(int $postId): void
    {
        foreach ($this->settable_meta_keys as $key) {
            if (!isset($this->{$key})) {
                $metaKey = $this->getPrefixedMetaKey($key);
                if (\metadata_exists('post', $postId, $metaKey)) {
                    $this->{$key} = \get_post_meta($postId, $metaKey, true);
                }
            }
        }
        if (\metadata_exists('post', $postId, "_cacbot_system_instructions")) {
            $this->system_instructions = \get_post_meta($postId, "_cacbot_system_instructions", true);
        }

    }

    /**
     * Get the prefixed meta key.
     *
     * @param string $key The original meta key
     * @return string The prefixed meta key
     */
    private function getPrefixedMetaKey(string $key): string
    {
        return '_cacbot_' . $key;
    }
}
