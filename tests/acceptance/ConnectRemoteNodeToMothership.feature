Feature: Connect remote_node to mothership and share Remote Application Password
  In order to establish secure communication between the remote_node and mothership
  As an administrator
  I want the Aion assistant user and application password to be generated and sent to the mothership when the Aion Chat plugin is activated.

  Background:
    Given the Aion Chat plugin is installed on the remote_node
    And the mothership is running and accepting POST requests

  Scenario: remote_node activates the Aion Chat plugin
    Given there is an Aion assistant designated by the function \Cacbot\User::get_aion_assistant_email()
    When the plugin is activated on the remote node
    And the Aion assistant does not previously exist
    Then the plugin should create the Aion assistant user on the remote_node
    And the plugin should create an application password if one does not already exist for the Aion assistant
    And the plugin should send a POST request over HTTP to the mothership with the assistant's remote application password

  Scenario: Aion assistant user already exists on the remote_node
    Given the Aion assistant user already exists on the remote_node
    When the plugin is activated on the remote node
    Then the plugin should not create a new Aion assistant user
    But the plugin should check if an application password already exists for the Aion assistant
    And if no application password exists, the plugin should generate a new application password
    And the plugin should send a POST request over HTTP to the mothership with the assistant's remote application password

  Scenario: Application password already exists for the Aion assistant
    Given the Aion assistant user exists on the remote_node
    And the Aion assistant already has a valid application password
    When the plugin is activated on the remote node
    Then the plugin should not generate a new application password
    And the plugin should send a POST request over HTTP to the mothership with the existing assistant's remote application password

  Scenario: Mothership fails to acknowledge receipt of the application password
    Given the Aion assistant's application password has been generated
    And the plugin sends a POST request to the mothership with the application password
    When the mothership fails to acknowledge the receipt of the application password
    Then the plugin should retry the POST request a configurable number of times
    And the plugin should log an error if the mothership does not respond after all retries
