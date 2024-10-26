<?php

namespace Cacbot\Tests;

use CacbotMothership\User;

class UserTest extends \Codeception\TestCase\WPTestCase {

    public function testGetCacbotAssistantEmail() {
        $expected_email = "assistant@cacbot.com";
        $actual_email = \Cacbot\User::get_cacbot_assistant_email();
        $this->assertEquals($expected_email, $actual_email, "The emails should match.");
    }

    public function test_User_force_return_user_id() {
        /* The purpose of this function is that when given an email address, it either returns the user ID or creates a new user and returns that id.
        */
        $email = 'new_user@example.com';
        $user_id = User::force_return_user_id($email);
        $this->assertIsInt($user_id, "The returned user ID should be an integer.");
    }

    public function testDoesAionAssistantUserExistMethodExistence()
    {
        $this->assertTrue(
            method_exists(\Cacbot\User::class, 'does_aion_assistant_user_exist'),
            'The method does_aion_assistant_user_exist does not exist in the Cacbot\User class.'
        );
    }

    public function testAionAssistantUserExistenceBeforeAndAfterCreation() {
        $email = "assistant@cacbot.com";

        // Assert that the user does not exist initially
        $userExistsBefore = \Cacbot\User::get_cacbot_assistant_user_id($email);
        $this->assertFalse($userExistsBefore, "Initially, the user with email {$email} should not exist.");

        // Create a user with the specified email
        $this->factory->user->create(['user_email' => $email]);


        // Assert that the user exists after creation
        $userExistsAfter = \Cacbot\User::get_cacbot_assistant_user_id($email);
        $this->assertTrue(is_int($userExistsAfter), "After creation, the user with email {$email} should exist.");
    }

    public function testAddAionRole() {
        // Ensure the role does not already exist before running the test
        remove_role('cacbot');

        // Call the method to add the 'aion' role
        \Cacbot\User::add_cacbot_role();

        // Retrieve the role object
        $aion_role = \get_role('cacbot');

        // Assert that the role has been created
        $this->assertNotNull($aion_role, "The 'cacbot' role should have been added.");

        // Assert that the role has the expected capabilities (if any were defined in the add_aion_role method)
        // Example assertion: (Uncomment and adjust based on actual role capabilities defined in add_aion_role)
        // $this->assertEquals($expected_capabilities, $aion_role->capabilities, "The 'aion' role should have the expected capabilities.");

        // Cleanup: Remove the 'aion' role after testing to maintain test isolation
        remove_role('cacbot');
    }

}