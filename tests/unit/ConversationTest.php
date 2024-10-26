<?php

use Cacbot\Conversation;

class ConversationTest extends \Codeception\TestCase\WPTestCase
{

    public function setUp(): void
    {
        parent::setUp();
        require_once('/var/www/html/wp-content/plugins/cacbot/src/Cacbot/autoloader.php');
    }

    /**
     * @test
     * it should be exist
     */
    public function testClassAndMethodExistence() {
        $className = '\Cacbot\Conversation';
        $methodName = 'parseCacbotConversationTitle';

        // Check if the class exists
        $this->assertTrue(class_exists($className), "Class {$className} does not exist");

        // If the class exists, check if the method exists in the class
        if (class_exists($className)) {
            $this->assertTrue(method_exists($className, $methodName), "Method {$methodName} does not exist in class {$className}");
        }
    }

    /**
     * @test
     * it should return the data parsed correctly
     */
    public function testParseIonConversationTitle() {
        $remote_post_id = 123;
        $user_id = 456;
        $remote_site_url = 'https://example.com';

        // Create a string using the buildIonConversationTitle method
        $builtTitle = Conversation::buildCacbotConversationTitle($remote_post_id, $user_id, $remote_site_url);

        // Now attempt to parse this string back into its components
        $parsedComponents = Conversation::parseCacbotConversationTitle($builtTitle);

        // Assert that the parsed components match the original input
        $this->assertEquals($remote_post_id, $parsedComponents['remote_post_id']);
        $this->assertEquals($user_id, $parsedComponents['user_id']);
        $this->assertEquals($remote_site_url, $parsedComponents['remote_site_url']);
    }

}