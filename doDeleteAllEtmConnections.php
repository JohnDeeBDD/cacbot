<?php

$command = "wp post delete $(wp post list --post_type='cacbot-conversation' --format=ids --path=/var/www/html) --force --path=/var/www/html";

echo(shell_exec($command));