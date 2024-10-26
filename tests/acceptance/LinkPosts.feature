Feature: Linking Anchor Post to dynamic Aion Conversation per user
  As a WordPress site user
  I want to see a dynamic Aion Conversation when I visit a designated anchor post
  So that I have a personalized conversation thread linked to that post

  Background:
    Given the Aion Chat plugin is installed and activated
    And a custom post type "Aion Conversation" exists with the slug "cacbot-conversation"
    And there is a post with the title "Sample Anchor Post"
    And the post meta "_cacbot_anchor_post" is set to true for "Sample Anchor Post"

  Scenario Outline: Create a new Aion Conversation for the first-time visitor to an anchor post
    Given I am a logged-in user
    And I visit the "<post_type>" post titled "Sample Anchor Post"
    When the system detects "_cacbot_anchor_post" is true for "Sample Anchor Post"
    And I have no linked Aion Conversation for this post stored in my user meta
    Then the system creates a new "Aion Conversation" linked to me and "Sample Anchor Post"
    And the system stores the Aion Conversation post ID in my user meta as "_cacbot_linked_conversation_<post_id>"
    And the comment section for "Sample Anchor Post" is replaced with the comments from my new Aion Conversation
    And I can add comments to the Aion Conversation

    Examples:
      | post_type         |
      | regular           |
      | cacbot-conversation |

  Scenario Outline: Display the existing linked Aion Conversation when revisiting the anchor post
    Given I am a logged-in user
    And I have previously visited the "<post_type>" post titled "Sample Anchor Post"
    And the system has stored an Aion Conversation post ID in my user meta as "_cacbot_linked_conversation_<post_id>"
    When I visit the "<post_type>" post titled "Sample Anchor Post" again
    Then the comment section for "Sample Anchor Post" is replaced with the comments from my linked Aion Conversation
    And I can continue the conversation by adding new comments

    Examples:
      | post_type         |
      | regular           |
      | cacbot-conversation |

  Scenario: Different users see their own linked Aion Conversation on the same anchor post
    Given there are two logged-in users "User A" and "User B"
    And "User A" has visited the regular post titled "Sample Anchor Post" and has a linked Aion Conversation stored in their user meta
    And "User B" has not visited "Sample Anchor Post" before
    When "User B" visits the regular post titled "Sample Anchor Post"
    Then the system creates a new "Aion Conversation" linked to "User B" and "Sample Anchor Post"
    And the system stores the new Aion Conversation post ID in "User B"'s user meta as "_cacbot_linked_conversation_<post_id>"
    And "User B" sees their own Aion Conversation in the comment section
    And "User A" still sees their own Aion Conversation when they visit the regular post titled "Sample Anchor Post"

  Scenario Outline: Non-anchor post behavior
    Given I am a logged-in user
    And I visit a "<post_type>" post that does not have the post meta "_cacbot_anchor_post" set to true
    Then the default WordPress comment section is displayed
    And no Aion Conversation is created

    Examples:
      | post_type         |
      | regular           |
      | cacbot-conversation |

  Scenario Outline: Non-logged-in user visits anchor post
    Given I am a non-logged-in user
    When I visit the "<post_type>" post titled "Sample Anchor Post"
    Then the default WordPress comment section is displayed
    And no Aion Conversation is created

    Examples:
      | post_type         |
      | regular           |
      | cacbot-conversation |

  Scenario: Aion Conversation created only once per user and anchor post
    Given I am a logged-in user
    And I visit the regular post titled "Sample Anchor Post" multiple times
    And I already have a linked Aion Conversation stored in my user meta as "_cacbot_linked_conversation_<post_id>"
    When I leave the post and come back again
    Then the system will not create a new Aion Conversation for me on "Sample Anchor Post"
    And the comment section will always display my existing linked Aion Conversation
