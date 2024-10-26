<?php

use Cacbot\CommentOverride;

class CommentOverrideTest extends \Codeception\TestCase\WPTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once('/var/www/html/wp-content/plugins/cacbot/src/Cacbot/autoloader.php');
    }

    /**
     * @test
     * it should check class and method existence
     */
    public function testClassAndMethodExistence()
    {
        $className = '\Cacbot\CommentOverride';

        // Check if the class exists
        $this->assertTrue(class_exists($className), "Class {$className} does not exist");

        // Check if required methods exist
        $this->assertTrue(method_exists($className, 'enable_comment_override'), 'Method enable_comment_override does not exist');
        $this->assertTrue(method_exists($className, 'is_anchor_post'), 'Method is_anchor_post does not exist');
        $this->assertTrue(method_exists($className, 'filter_comments_array'), 'Method filter_comments_array does not exist');
        $this->assertTrue(method_exists($className, 'handle_preprocess_comment'), 'Method handle_preprocess_comment does not exist');
    }

    /**
     * @test
     * it should correctly identify anchor posts
     */
    public function testIsAnchorPost()
    {
        $post_id = $this->factory->post->create();

        // Test when no meta is set (should return false)
        $this->assertFalse(\Cacbot\CommentOverride::is_anchor_post($post_id), "is_anchor_post should return false when no meta is set");

        // Test when meta is set to 'true'
        update_post_meta($post_id, '_cacbot_anchor_post', 'true');
        $this->assertTrue(\Cacbot\CommentOverride::is_anchor_post($post_id), "is_anchor_post should return true when meta is set to 'true'");

        // Test when meta is set to a value other than 'true'
        update_post_meta($post_id, '_cacbot_anchor_post', 'false');
        $this->assertFalse(\Cacbot\CommentOverride::is_anchor_post($post_id), "is_anchor_post should return false when meta is set to 'false'");
    }

    /**
     * @test
     * it should create a conversation if none exists
     */
    public function testCreateConversation()
    {
        $post_id = $this->factory->post->create();
        $user_id = $this->factory->user->create();

        // Create a new conversation
        $conversation_id = \Cacbot\CommentOverride::create_conversation($user_id, $post_id);

        // Assert the conversation was created
        $this->assertNotFalse($conversation_id, "create_conversation should return a valid post ID");

        // Assert the conversation is of type 'cacbot-conversation'
        $conversation_post = get_post($conversation_id);
        $this->assertEquals('cacbot-conversation', $conversation_post->post_type, "Post type should be 'cacbot-conversation'");

        // Assert the post meta links to the correct anchor post
        $linked_post_id = get_post_meta($conversation_id, '_linked_anchor_post', true);
        $this->assertEquals($post_id, $linked_post_id, "The conversation should be linked to the correct anchor post");
    }

    /**
     * @test
     * it should retrieve an existing conversation
     */
    public function testGetConversation()
    {
        $post_id = $this->factory->post->create();
        $user_id = $this->factory->user->create();

        // Assert no conversation exists initially
        $this->assertNull(\Cacbot\CommentOverride::get_conversation($user_id, $post_id), "No conversation should exist initially");

        // Create a conversation
        $conversation_id = \Cacbot\CommentOverride::create_conversation($user_id, $post_id);

        // Retrieve the conversation
        $retrieved_conversation = \Cacbot\CommentOverride::get_conversation($user_id, $post_id);
        $this->assertNotNull($retrieved_conversation, "The conversation should be retrievable after creation");
        $this->assertEquals($conversation_id, $retrieved_conversation->ID, "The retrieved conversation ID should match the created one");
    }

    /**
     * @test
     * it should return the default comments for non-logged-in users
     */
    public function testFilterCommentsArrayForNonLoggedInUser()
    {
        $post_id = $this->factory->post->create();

        // Simulate a non-logged-in user
        wp_set_current_user(0);

        $comments = ['comment 1', 'comment 2'];

        $filtered_comments = \Cacbot\CommentOverride::filter_comments_array($comments, $post_id);

        // Non-logged-in users should see default comments
        $this->assertEquals($comments, $filtered_comments, "Non-logged-in users should see the default comments");
    }

    /**
     * @test
     * it should redirect new comments to the linked conversation post
     */
    public function testHandlePreprocessCommentRedirect()
    {
        $post_id = $this->factory->post->create();
        $user_id = $this->factory->user->create();

        // Mark the post as an anchor post
        update_post_meta($post_id, '_cacbot_anchor_post', 'true');

        // Simulate a logged-in user
        wp_set_current_user($user_id);

        // Pre-process comment data
        $commentdata = [
            'comment_post_ID' => $post_id,
            'comment_content' => 'Test comment',
            'user_id'         => $user_id,
        ];

        // Ensure conversation gets created and comment is redirected to it
        $processed_commentdata = \Cacbot\CommentOverride::handle_preprocess_comment($commentdata);

        // Check that the comment is redirected to the conversation post
        $conversation = \Cacbot\CommentOverride::get_conversation($user_id, $post_id);
        $this->assertEquals($conversation->ID, $processed_commentdata['comment_post_ID'], "Comment should be redirected to the linked conversation post");
    }
}
