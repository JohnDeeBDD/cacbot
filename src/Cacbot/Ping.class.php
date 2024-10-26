<?php

namespace Cacbot;

class Ping{

    public static function doPing($api_key){
            //this action is happening on the remote.
            global $Cacbot_mothership_url;
            $response = \wp_remote_post($Cacbot_mothership_url . "/wp-json/cacbot/v1/ping", array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => array(
                        'data' => \serialize([
                            'remote-site-url' => \site_url(),
                            'ion-api-key'   => $api_key
                        ]),
                    )
                )
            );
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                echo "Something went wrong: \Cacbot\Ping27 $error_message";
                die();
            }
            return $response;

        }

}