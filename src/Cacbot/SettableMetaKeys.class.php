<?php

namespace Cacbot;

class SettableMetaKeys{

    public static array $settable_meta_keys = [
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

    public static string $prefix = "_cacbot_";

}