<?php

namespace Cacbot;

class PostFileEmbedder{

    public static function display_text_file_content( $atts ) {

        $file_path = $atts[1];

        // Check if file exists
        if ( file_exists( $file_path ) ) {
            // Read and return the file content
            return  file_get_contents( $file_path );
        } else {
            return 'File not found.';
        }
    }

}