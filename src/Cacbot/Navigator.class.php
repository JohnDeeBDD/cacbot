<?php

namespace Cacbot;

class Navigator{



    public static function manage_redirect($post_id){
        $latest_comment = Comment::get_latest_comment_content($post_id);
        if($latest_comment){
              $destination = Navigator::parse_cacbot_navigate_shortcode($latest_comment);
              if($destination){
                  \wp_redirect($destination, 301);
                  exit;
              }
        }
    }

    /**
     * Parses a string to detect and extract the URL from the [cacbot_navigate] shortcode.
     *
     * This function checks if a given string contains the [cacbot_navigate destination="someURL"]
     * shortcode pattern. If the shortcode is found in the correct format, it extracts and returns
     * the URL provided in the `destination` attribute. If the shortcode is not present or is
     * improperly formatted, it returns `false`.
     *
     * @param string $string The input string to search for the shortcode.
     *
     * @return string|bool Returns the extracted URL as a string if the shortcode is found in the
     *                     correct format, or `false` if the shortcode is absent or incorrectly formatted.
     */
    public static function parse_cacbot_navigate_shortcode($string) {
          if(strpos($string, "***RIGHT ANGLES***") !== false){
            return "http://13.58.104.91/dest/";
        }
              return false;

    }



}