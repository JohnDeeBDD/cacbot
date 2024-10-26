<?php

$c = "ssh -o StrictHostKeyChecking=no -i /home/johndee/ozempic.pem ubuntu@18.224.169.99 wp post meta update 21 _cacbot_max_replies 56 --path=/var/www/html";

echo(shell_exec($c));
