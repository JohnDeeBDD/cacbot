#!/bin/bash

# Create a new post with a specific post type, title, and set its status to 'publish', then capture its ID
POST_ID=$(wp post create --post_type=cacbot-conversation --post_title="TestPost" --post_status=publish --porcelain --path=/var/www/html)

# Check if the post was created successfully
if [ -z "$POST_ID" ]; then
    echo "Failed to create post."
    exit 1
else
    echo "Post created successfully with ID $POST_ID and status set to 'publish'."
fi

# Get the user ID of the author "Aion"
AUTHOR_ID=$(wp user get Assistant --field=ID --path=/var/www/html)

# Check if the user ID was retrieved successfully
if [ -z "$AUTHOR_ID" ]; then
    echo "Failed to retrieve user ID for 'Assistant'."
    exit 1
else
    echo "User 'Assistant' has ID $AUTHOR_ID"
fi

# Set the post meta "_cacbot_system_instructions" with the value "You are a helpful assistant named Tony."
wp post meta set $POST_ID _cacbot_system_instructions "You are a helpful assistant named Tony." --path=/var/www/html

if [ $? -eq 0 ]; then
    echo "Post meta '_cacbot_system_instructions' updated successfully."
else
    echo "Failed to update post meta '_cacbot_system_instructions'."
    exit 1
fi

# Set the post meta "_cacbot_anchor_post" with the value "true"
wp post meta set $POST_ID _cacbot_anchor_post "true" --path=/var/www/html

if [ $? -eq 0 ]; then
    echo "Post meta '_cacbot_anchor_post' updated successfully."
else
    echo "Failed to update post meta '_cacbot_anchor_post'."
    exit 1
fi

# Optionally, set the author of the post
wp post update $POST_ID --post_author="$AUTHOR_ID" --path=/var/www/html

if [ $? -eq 0 ]; then
    echo "Post author updated successfully."
else
    echo "Failed to update post author."
    exit 1
fi
