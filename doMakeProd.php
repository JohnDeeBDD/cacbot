<?php

//This script zips and ships the production version
//It only works from John Dee's personal computer

$version = readline('Version to create: ');


shell_exec("sudo rm -fr /var/www/html/wp-content/plugins/cacbot/cacbot");
shell_exec("sudo mkdir /var/www/html/wp-content/plugins/cacbot/cacbot");
copy("/var/www/html/wp-content/plugins/cacbot/cacbot.php", "/var/www/html/wp-content/plugins/cacbot/cacbot/cacbot.php");
shell_exec("sudo rsync -r --exclude src/CacbotMothership src cacbot");
shell_exec("sudo zip -r cacbot-$version.zip cacbot");
shell_exec("sudo rm cacbot.zip");
shell_exec("sudo cp cacbot-$version.zip cacbot.zip");
shell_exec("sudo rm -fr cacbot");

$command = "scp -i /home/johndee/sportsman.pem cacbot.zip ubuntu@3.13.139.91:/var/www/cacbot.com/wp-content/uploads/cacbot.zip";
echo ($command . PHP_EOL);shell_exec($command);

$command = "scp -i /home/johndee/sportsman.pem details.json ubuntu@3.13.139.91:/var/www/cacbot.com/wp-content/uploads/details.json";
echo ($command . PHP_EOL);shell_exec($command);

$command = "scp -i /home/johndee/sportsman.pem info.json ubuntu@3.13.139.91:/var/www/cacbot.com/wp-content/uploads/info.json";
echo ($command . PHP_EOL);shell_exec($command);