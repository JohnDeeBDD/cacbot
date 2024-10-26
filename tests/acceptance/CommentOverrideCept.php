<?php

/**
 * This test performs a basic call and response under different modes.
 */

$I = new AcceptanceTester($scenario);

// Get IPs for Mothership and Remote Node
$siteUrls = $I->getSiteUrls();
$mothershipIP = $siteUrls[0];
$remoteNodeIP = $siteUrls[1];

do_remote_node_test($I, $remoteNodeIP);
do_localhost_test($I, $remoteNodeIP);
do_mothership_test($I, $remoteNodeIP);

function do_mothership_test($I, $mothershipIP){
    $privateKey = '/home/johndee/ozempic.pem';
    executeRemoteSSHCommand($mothershipIP, "php /var/www/html/wp-content/plugins/cacbot/doDeleteTestEtmConnections.php", $privateKey);
    $command = "ssh -o StrictHostKeyChecking=no -i /home/johndee/ozempic.pem ubuntu@" . $mothershipIP . " /var/www/html/wp-content/plugins/cacbot/tests/acceptance/comment_override_setup.sh";
    $remoteNodePostID = (shell_exec($command));
    $remoteNodePostID = $I->extractPostNumeral($remoteNodePostID);
    $I->amOnUrl("http://" . $mothershipIP);
    $I->loginAsAdmin();
    $I->amOnPage("/cacbot-conversation/testpost/");
    $I->wantTo("Make a comment and see it over written");
    $I->makeAComment("What is the capital city of France?");
    $I->shouldSeeAnIntelligentResponse("Paris");
}


function do_localhost_test($I, $remoteNodeIP){
    echo(shell_exec("php /var/www/html/wp-content/plugins/cacbot/doDeleteTestEtmConnections.php"));
    $command = "/var/www/html/wp-content/plugins/cacbot/tests/acceptance/comment_override_setup.sh";
    echo(shell_exec($command));
    $I->reconfigureThisVariable(["url" => 'http://localhost']);
    $I->loginAsAdmin();
    $I->amOnUrl("http://localhost");
    $I->loginAsAdmin();
    $I->amOnPage("/cacbot-conversation/testpost/");
    $I->wantTo("Make a comment and see it over written");
    $I->makeAComment("What is the capital city of France?");
    $I->shouldSeeAnIntelligentResponse("Paris");
}

function do_remote_node_test($I, $remoteNodeIP){
    $privateKey = '/home/johndee/ozempic.pem';
    executeRemoteSSHCommand($remoteNodeIP, "php /var/www/html/wp-content/plugins/cacbot/doDeleteTestEtmConnections.php", $privateKey);
    $command = "ssh -o StrictHostKeyChecking=no -i /home/johndee/ozempic.pem ubuntu@" . $remoteNodeIP . " /var/www/html/wp-content/plugins/cacbot/tests/acceptance/comment_override_setup.sh";
    $remoteNodePostID = (shell_exec($command));
    $remoteNodePostID = $I->extractPostNumeral($remoteNodePostID);
    $I->amOnUrl("http://" . $remoteNodeIP);
    $I->loginAsAdmin();
    $I->amOnPage("/cacbot-conversation/testpost/");
    $I->wantTo("Make a comment and see it over written");
    $I->makeAComment("What is the capital city of France?");
    $I->shouldSeeAnIntelligentResponse("Paris");
}



/**
 * Helper function to execute remote commands via SSH
 */
function executeRemoteSSHCommand($serverIP, $command, $privateKey) {
    $sshCommand = "ssh -o StrictHostKeyChecking=no -i $privateKey ubuntu@$serverIP $command";
    echo(shell_exec($sshCommand));
}

/**
 * Test function for remote mode
 */
function remote_mode_test($I, $mothershipIP, $remoteNodeIP) {

    $remoteNodeIP = $I->getSiteUrls();
    $remoteNodeIP = $remoteNodeIP[1];
    $I->amOnUrl("http://" . $remoteNodeIP);
    $I->loginAsAdmin();
    $I->amOnPage("/wp-admin/");
    $I->see("RemoteNode");

    $command = "ssh -o StrictHostKeyChecking=no -i /home/johndee/ozempic.pem ubuntu@" . $remoteNodeIP . " /var/www/html/wp-content/plugins/cacbot/tests/acceptance/intelligent_response_setup.sh";
    $remoteNodePostID = shell_exec($command);

    cleanupTest($remoteNodeIP, $mothershipIP, $remoteNodePostID);
}


/**
 * Cleanup test posts
 */
function cleanupTest($remoteNodeIP, $mothershipIP, $postID) {
    $cleanup = false;

    if ($cleanup) {
        $privateKey = '/home/johndee/ozempic.pem';

        // Cleanup remote node post
        executeRemoteCommand($remoteNodeIP, "wp post delete $postID --force --path=/var/www/html/", $privateKey);

        // Get conversation ID and delete it
        $conversationID = shell_exec("ssh -o StrictHostKeyChecking=no -i $privateKey ubuntu@$mothershipIP wp post list --post_type='cacbot-conversation' --format=ids --path=/var/www/html/");
        executeRemoteCommand($mothershipIP, "wp post delete $conversationID --force --path=/var/www/html/", $privateKey);
    }
}

/**
 * Test function for localhost mode
 */
function localhost_mode_test($I) {
    // Setup a test post on localhost
    $localhostPostID = $I->setupTestPostOnLocalhost();

}

/**
 * Test function for mothership mode
 */
function mothership_mode_test($I, $mothershipIP) {
    // Setup a test post on the mothership
    $mothershipPostID = setupMothershipTest($I);


    // Optionally cleanup after the test
    cleanupMothershipTest($mothershipIP, $mothershipPostID);
}


function extractPostID($string) {
    // Use preg_match to find a sequence of digits in the string
    if (preg_match('/\b(\d+)\b/', $string, $matches)) {
        // If a match is found, it's stored in $matches
        // $matches[0] would contain the whole matched string,
        // while $matches[1] contains the first captured parenthesized subpattern, which in this case is the numeral.
        return (int)$matches[1]; // Return the numeral as an integer
    } else {
        // Return some default or error value if no numeral is found
        return false;
    }
}