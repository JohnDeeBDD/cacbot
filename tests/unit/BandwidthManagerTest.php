<?php

class BandwidthManagerTest extends \Codeception\TestCase\WPTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once('/var/www/html/wp-content/plugins/cacbot/src/Cacbot/autoloader.php');
    }

    /**
     * It should exist
     * @test
     */
    public function testBandwidthManagerExists()
    {
        $BandwidthManager = new \Cacbot\BandwidthManager();
        $this->assertInstanceOf(\Cacbot\BandwidthManager::class, $BandwidthManager);
    }

    /**
     * It should close comments on the post
     * @test
     */
    public function testCloseComments()
    {
        // Step 1: Create a new WordPress post with comments open
        $post_id = wp_insert_post([
            'post_title' => 'Test Post',
            'post_content' => 'This is a test post.',
            'post_status' => 'publish',
            'comment_status' => 'open', // Initially, comments are open
        ]);

        // Confirm the post was created successfully
        $this->assertIsInt($post_id);

        // Step 2: Call the BandwidthManager::close_comments method
        \Cacbot\BandwidthManager::close_comments($post_id);

        // Step 3: Retrieve the post and confirm the comments are closed
        $updated_post = get_post($post_id);
        $this->assertEquals('closed', $updated_post->comment_status);
    }
}
