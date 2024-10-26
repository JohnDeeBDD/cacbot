<?php

const PRIVATE_KEY_PATH = '/home/johndee/ozempic.pem';
const WP_PATH = '/var/www/html';

/**
 * This test performs a basic call and response under different modes.
 */
$I = new AcceptanceTester($scenario);

// Get IPs for Mothership and Remote Node
$siteUrls = json_decode(file_get_contents('/var/www/html/wp-content/plugins/cacbot/servers.json'), true);
$mothershipIP = $siteUrls[0];
$remoteNodeIP = $siteUrls[1];

// Delete old posts on all servers
$privateKey = '/home/johndee/ozempic.pem';
$command = "ssh -o StrictHostKeyChecking=no -i " . $privateKey . " ubuntu@" . $mothershipIP . " php /var/www/html/wp-content/plugins/cacbot/doDeleteAllEtmConnections.php";
echo(shell_exec($command));
$command = "ssh -o StrictHostKeyChecking=no -i " . $privateKey . " ubuntu@" . $remoteNodeIP . " php /var/www/html/wp-content/plugins/cacbot/doDeleteTestEtmConnections.php";
echo(shell_exec($command));
echo(shell_exec("php /var/www/html/wp-content/plugins/cacbot/doDeleteTestEtmConnections.php"));
// Call the command line UI to select and run tests


$stub_post_meta = [
    "_cacbot_max_replies"    =>  2,
    "_cacbot_anchor_post"    => "false"
];
$stub_post_meta = [];
runModeSelection($I, $mothershipIP, $remoteNodeIP, $stub_post_meta);

function runModeSelection($I, $mothershipIP, $remoteNodeIP, $stub_post_meta) {
    // Check if MODE and NUMBER_OF_CALLS are provided as environment variables
    $mode = getenv('MODE');
    $numberOfCalls = getenv('NUMBER_OF_CALLS') ? intval(getenv('NUMBER_OF_CALLS')) : 3; // Default to 3 calls if not provided

    if (!$mode) {
        $mode = 4;
    }

    // Run the selected mode or all modes
    switch ($mode) {
        case '1':
            echo "Running Localhost mode with $numberOfCalls call-and-response(s)...\n";
            localhost_mode_test($I, $numberOfCalls, $stub_post_meta);
            break;
        case '2':
            echo "Running Mothership mode with $numberOfCalls call-and-response(s)...\n";
            mothership_mode_test($I, $mothershipIP, $numberOfCalls, $stub_post_meta);
            break;
        case '3':
            echo "Running Remote Node mode with $numberOfCalls call-and-response(s)...\n";
            remote_mode_test($I, $mothershipIP, $remoteNodeIP, $numberOfCalls, $stub_post_meta);
            break;
        case '4':
            echo "Running all modes with $numberOfCalls call-and-response(s) each...\n";
            localhost_mode_test($I, $numberOfCalls, $stub_post_meta);
            mothership_mode_test($I, $mothershipIP, $numberOfCalls, $stub_post_meta);
            remote_mode_test($I, $mothershipIP, $remoteNodeIP, $numberOfCalls, $stub_post_meta);
            break;
        default:
            echo "Invalid selection. Please choose a valid mode.\n";
            runModeSelection($I, $mothershipIP, $remoteNodeIP); // Re-prompt in case of invalid input
    }
}

/**
 * Test function for remote mode
 */
function remote_mode_test($I, $mothershipIP, $remoteNodeIP, $numberOfCalls, $stub_post_meta) {
    $remoteNodePostID = setupTestPostOnRemoteNode($I, $stub_post_meta);

    $questions = [
        "Who was the President of the United States in 2003?",
        "Who was the next President after that one?",
        "That President's first name and nickname start with the same letter. What is the letter?"
    ];

    $expectedResponses = ["Bush", "Obama", "B"];

    for ($i = 0; $i < $numberOfCalls; $i++) {
        $I->makeAComment($questions[$i]);
        $I->shouldSeeAnIntelligentResponse($expectedResponses[$i]);
    }

    // Optionally cleanup after the test
    //cleanupTest($remoteNodeIP, $mothershipIP, $remoteNodePostID);
}

/**
 * Test function for localhost mode
 */
function localhost_mode_test($I, $numberOfCalls, $stub_post_meta) {
    $localhostPostID = setupTestPostOnLocalhost($I, $stub_post_meta);

    $questions = [
        "What is the capital city of France?",
        "What is the tallest structure in that city?",
        "In which year was the Eiffel Tower completed?"
    ];

    $expectedResponses = ["Paris", "Eiffel Tower", "1889"];

    for ($i = 0; $i < $numberOfCalls; $i++) {
        $I->makeAComment($questions[$i]);
        $I->shouldSeeAnIntelligentResponse($expectedResponses[$i]);
    }

    // Cleanup localhost test if necessary
}

/**
 * Test function for mothership mode
 */
function mothership_mode_test($I, $mothershipIP, $numberOfCalls, $stub_post_meta) {
    $mothershipPostID = setupTestPostOnMothership($I, $stub_post_meta);

    $questions = [
        "What is the capital city of the United States of America?",
        "What is the first name of the person that city is named after?",
        "In which year did George Washington become the first U.S. President?"
    ];

    $expectedResponses = ["Washington", "George", "1789"];

    for ($i = 0; $i < $numberOfCalls; $i++) {
        $I->makeAComment($questions[$i]);
        $I->shouldSeeAnIntelligentResponse($expectedResponses[$i]);
    }

    // Optionally cleanup after the test
   // cleanupMothershipTest($mothershipIP, $mothershipPostID);
}

/**
 * Function to setup test post on Localhost
 */
function setupTestPostOnLocalhost($I, $stub_post_meta) {
    $I->amOnUrl("http://localhost/");
    $I->loginAsAdmin();
    $I->amOnPage("/wp-admin/");
    $I->see("localhost");
    $command = 'wp post create --post_type=cacbot-conversation --post_title="TestPost"';
    $postID = ($I->extractPostNumeral(shell_exec($command)));


    foreach ($stub_post_meta as $meta_key => $meta_value) {
        $command = "wp post meta update " . $postID . " $meta_key $meta_value";
        echo(shell_exec($command));
    }

    $command = 'wp user get Assistant --field=ID';
    $IonUserID = (shell_exec($command));
    $command = "wp post update " . $postID . " --post_author=" . $IonUserID;
    echo(shell_exec($command));
    $command = "wp post update " . $postID . " --post_status='publish'";
    echo(shell_exec($command));
    return $postID;
}

/**
 * Function to setup test post on Mothership
 */
function setupTestPostOnMothership($I, $stub_post_meta) {
    $remoteNodeIP = $I->getSiteUrls();
    $remoteNodeIP = $remoteNodeIP[0];
    $I->amOnUrl("http://" . $remoteNodeIP);
    $I->loginAsAdmin();
    $I->amOnPage("/wp-admin/");
    $I->see("Mothership");

    $command = "ssh -o StrictHostKeyChecking=no -i /home/johndee/ozempic.pem ubuntu@" . $remoteNodeIP . " /var/www/html/wp-content/plugins/cacbot/tests/acceptance/intelligent_response_setup.sh";
    $postID = extractPostNumeral(shell_exec($command));

    // Apply post meta from $stub_post_meta
    foreach ($stub_post_meta as $meta_key => $meta_value) {
        $command = "ssh -o StrictHostKeyChecking=no -i " . PRIVATE_KEY_PATH . " ubuntu@" . $remoteNodeIP . " wp post meta update " . $postID . " $meta_key $meta_value --path=" . WP_PATH;

        echo(shell_exec($command));
    }

    return $postID;
}

/**
 * Function to setup test post on Remote Node
 */
function setupTestPostOnRemoteNode($I, $stub_post_meta) {
    $remoteNodeIP = $I->getSiteUrls();
    $remoteNodeIP = $remoteNodeIP[1];
    $I->amOnUrl("http://" . $remoteNodeIP);
    $I->loginAsAdmin();
    $I->amOnPage("/wp-admin/");
    $I->see("RemoteNode");
    $command = "ssh -o StrictHostKeyChecking=no -i /home/johndee/ozempic.pem ubuntu@" . $remoteNodeIP . " /var/www/html/wp-content/plugins/cacbot/tests/acceptance/intelligent_response_setup.sh";
    $postID = extractPostNumeral(shell_exec($command));
    echo("Applying remote node meta");

    // Apply post meta from $stub_post_meta
    foreach ($stub_post_meta as $meta_key => $meta_value) {
        $command = "ssh -o StrictHostKeyChecking=no -i " . PRIVATE_KEY_PATH . " ubuntu@" . $remoteNodeIP . " wp post meta update " . $postID . " $meta_key $meta_value --path=/var/www/html";
        echo(shell_exec($command));
    }

    return $postID;
}

function extractPostNumeral($string) {
    if (preg_match('/\b(\d+)\b/', $string, $matches)) {
        return (int)$matches[1];
    } else {
        return false;
    }
}
