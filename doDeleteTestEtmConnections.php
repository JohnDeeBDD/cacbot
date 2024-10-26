<?php

$command = "wp post delete $(wp post list --post_type='cacbot-conversation' --title='TestPost' --format=ids --path=/var/www/html) --force --path=/var/www/html";
echo(shell_exec($command));

$command = "wp post delete $(wp post list --post_type='cacbot-conversation' --title='ConversationA' --format=ids --path=/var/www/html) --force --path=/var/www/html";
echo(shell_exec($command));

$command = "wp post delete $(wp post list --post_type='cacbot-conversation' --title='ConversationB' --format=ids --path=/var/www/html) --force --path=/var/www/html";
echo(shell_exec($command));

// Get all posts of type 'cacbot-conversation'
$command = "wp post list --post_type='cacbot-conversation' --format=ids --path=/var/www/html";
$all_posts = shell_exec($command);

// Split the output into an array of post IDs
$post_ids = explode("\n", trim($all_posts));

$matching_ids = [];

// Iterate through each post ID
foreach ($post_ids as $post_id) {
    // Get the post title for each post ID
    $command = "wp post get $post_id --field=post_title --path=/var/www/html";
    $post_title = trim(shell_exec($command));

    // Check if the title matches the pattern 'Aion Conversation for User % on Post %'
    if (preg_match('/Cacbot Conversation for User \d+ on Post \d+/', $post_title)) {
        $matching_ids[] = $post_id;
    }
}

// Delete matching posts
if (!empty($matching_ids)) {
    $ids_to_delete = implode(' ', $matching_ids);
    $command = "wp post delete $ids_to_delete --force --path=/var/www/html";
    echo(shell_exec($command));
} else {
    echo "No matching posts found.\n";
};