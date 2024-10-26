<?php

namespace Cacbot;

class DialecticException extends \Exception {}

class Dialectic {

    public static function converse(){}
    public static function is_dialectical_situation($post_id): bool
    {
        if(metadata_exists('post', $post_id, '_cacbot_interlocutor_post_id')) {
            return true;
        }else{
            return false;
        }
    }
}