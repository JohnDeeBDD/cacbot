<?php

namespace Cacbot;

class Exception extends \Exception {

 public static function handle(Exception $e) {
        if ($e instanceof CacbotException) {
            // Handle custom exceptions
        } else {
            // Handle generic exceptions
        }
        // Log, alert, etc.
    }
}
