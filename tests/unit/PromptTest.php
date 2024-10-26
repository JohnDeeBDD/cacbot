<?php

class PromptTest extends \Codeception\TestCase\WPTestCase{

    private $post_id;
    private $comment_id;
    private $user_id;
    private $user_email;

    public function setUp(): void {
        parent::setUp();
        require_once('/var/www/html/wp-content/plugins/cacbot/src/Cacbot/autoloader.php');
        \Cacbot\User::create_Aion_user();
        $AionUserID = \Cacbot\User::get_Aion_user_id();
    }

    /**
     * @test
     * it should be instantiable
     */
	public function isShouldBeInstantiable(){
       $Prompt = new \Cacbot\Prompt;
    }

    private function setupStubAionConversation(){
        $my_post = array(
            'post_title'    => "Test Post",
            'post_content'  => "lorum ipsum",
            'post_status'   => 'publish',
            'post_author'   => \Cacbot\User::get_Aion_user_id(),
            'post_type'     => "cacbot-conversation",
        );
        $post_id = wp_insert_post( $my_post );

        $data = array(
            'comment_post_ID'      => $post_id,
            'comment_content'      => "This is a comment.",
            //'comment_parent'       => $field['comment_parent'],
            'user_id'              => \Cacbot\User::get_Aion_user_id(),
            'comment_author'       => "Aion",
            'comment_author_email' => "aion@aion.garden",
            'comment_author_url'   => "https://aion.garden",
        );

        $comment_id = wp_insert_comment( $data );
        if ( ! is_wp_error( $comment_id ) ) {
            return $comment_id;
        }

        return $comment_id;
    }

}

