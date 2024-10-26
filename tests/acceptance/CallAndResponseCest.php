<?php

class CallAndResponseCest
{
    const PRIVATE_KEY_PATH = '/home/johndee/ozempic.pem';
    const WP_PATH = '/var/www/html';

     public function runTest(\AcceptanceTester $I){

        // Get IPs for Mothership and Remote Node
        $siteUrls = $I->getSiteUrls();
        $mothershipIP = $siteUrls[0];
        $remoteNodeIP = $siteUrls[1];

        // Delete old posts on all servers
        $this->executeRemoteCommand($mothershipIP, "php /var/www/html/wp-content/plugins/cacbot/doDeleteAllEtmConnections.php");
        $this->executeRemoteCommand($remoteNodeIP, "php /var/www/html/wp-content/plugins/cacbot/doDeleteTestEtmConnections.php");
        echo(shell_exec("php /var/www/html/wp-content/plugins/cacbot/doDeleteTestEtmConnections.php"));

        // Call the command line UI to select and run tests
        $this->runModeSelection($I, $mothershipIP, $remoteNodeIP);
    }

    private function runModeSelection(AcceptanceTester $I, $mothershipIP, $remoteNodeIP)
    {
        // Check if MODE and NUMBER_OF_CALLS are provided as environment variables
        $mode = getenv('MODE') ?: '4';
        $numberOfCalls = getenv('NUMBER_OF_CALLS') ? intval(getenv('NUMBER_OF_CALLS')) : 3; // Default to 3 calls if not provided

        // Run the selected mode or all modes
        switch ($mode) {
            case '1':
                echo "Running Localhost mode with $numberOfCalls call-and-response(s)...\n";
                $this->localhostModeTest($I, $numberOfCalls);
                break;
            case '2':
                echo "Running Mothership mode with $numberOfCalls call-and-response(s)...\n";
                $this->mothershipModeTest($I, $mothershipIP, $numberOfCalls);
                break;
            case '3':
                echo "Running Remote Node mode with $numberOfCalls call-and-response(s)...\n";
                $this->remoteModeTest($I, $mothershipIP, $remoteNodeIP, $numberOfCalls);
                break;
            case '4':
                echo "Running all modes with $numberOfCalls call-and-response(s) each...\n";
                $this->localhostModeTest($I, $numberOfCalls);
                $this->mothershipModeTest($I, $mothershipIP, $numberOfCalls);
                $this->remoteModeTest($I, $mothershipIP, $remoteNodeIP, $numberOfCalls);
                break;
            default:
                echo "Invalid selection. Please choose a valid mode.\n";
                $this->runModeSelection($I, $mothershipIP, $remoteNodeIP); // Re-prompt in case of invalid input
        }
    }

    private function executeRemoteCommand($serverIP, $command, $privateKey = self::PRIVATE_KEY_PATH)
    {
        $sshCommand = "ssh -o StrictHostKeyChecking=no -i $privateKey ubuntu@$serverIP $command";
        echo(shell_exec($sshCommand));
    }

    private function cleanupTest($remoteNodeIP, $mothershipIP, $postID)
    {
        $cleanup = false;

        if ($cleanup) {
            // Cleanup remote node post
            $this->executeRemoteCommand($remoteNodeIP, "wp post delete $postID --force --path=" . self::WP_PATH);

            // Get conversation ID and delete it
            $conversationID = shell_exec("ssh -o StrictHostKeyChecking=no -i " . self::PRIVATE_KEY_PATH . " ubuntu@$mothershipIP wp post list --post_type='cacbot-conversation' --format=ids --path=" . self::WP_PATH);
            $this->executeRemoteCommand($mothershipIP, "wp post delete $conversationID --force --path=" . self::WP_PATH);
        }
    }

    private function remoteModeTest(AcceptanceTester $I, $mothershipIP, $remoteNodeIP, $numberOfCalls)
    {
        $remoteNodePostID = $this->setupTestPostOnRemoteNode($I);

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
        $this->cleanupTest($remoteNodeIP, $mothershipIP, $remoteNodePostID);
    }

    private function localhostModeTest(AcceptanceTester $I, $numberOfCalls)
    {
        $localhostPostID = $this->setupTestPostOnLocalhost($I);

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

    private function mothershipModeTest(AcceptanceTester $I, $mothershipIP, $numberOfCalls)
    {
        $mothershipPostID = $this->setupTestPostOnMothership($I);

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
        $this->cleanupMothershipTest($mothershipIP, $mothershipPostID);
    }

    private function cleanupMothershipTest($mothershipIP, $postID)
    {
        $cleanup = false;

        if ($cleanup) {
            $this->executeRemoteCommand($mothershipIP, "wp post delete $postID --force --path=" . self::WP_PATH);
        }
    }

    private function setupTestPostOnLocalhost(AcceptanceTester $I)
    {
        $I->amOnUrl("http://localhost/");
        $I->loginAsAdmin();
        $I->amOnPage("/wp-admin/");
        $I->see("Aion");
        $command = 'wp post create --post_type=cacbot-conversation --post_title="TestPost"';
        $postID = $this->extractPostNumeral(shell_exec($command));

        $command = "wp post meta update " . $postID . " cacbot-instructions 'You are a helpful assistant.'";
        echo(shell_exec($command));

        $command = 'wp user get Assistant --field=ID';
        $IonUserID = (shell_exec($command));
        $command = "wp post update " . $postID . " --post_author=" . $IonUserID;
        echo(shell_exec($command));
        $command = "wp post update " . $postID . " --post_status='publish'";
        echo(shell_exec($command));
        return $postID;
    }

    private function setupTestPostOnMothership(AcceptanceTester $I)
    {
        $remoteNodeIP = $I->getSiteUrls()[0];
        $I->amOnUrl("http://" . $remoteNodeIP);
        $I->loginAsAdmin();
        $I->amOnPage("/wp-admin/");
        $I->see("Mothership");

        $command = "ssh -o StrictHostKeyChecking=no -i /home/johndee/ozempic.pem ubuntu@" . $remoteNodeIP . " /var/www/html/wp-content/plugins/cacbot/tests/acceptance/intelligent_response_setup.sh";
        return shell_exec($command);
    }

    private function setupTestPostOnRemoteNode(AcceptanceTester $I)
    {
        $remoteNodeIP = $I->getSiteUrls()[1];
        $I->amOnUrl("http://" . $remoteNodeIP);
        $I->loginAsAdmin();
        $I->amOnPage("/wp-admin/");
        $I->see("RemoteNode");

        $command = "ssh -o StrictHostKeyChecking=no -i /home/johndee/ozempic.pem ubuntu@" . $remoteNodeIP . " /var/www/html/wp-content/plugins/cacbot/tests/acceptance/intelligent_response_setup.sh";
        return shell_exec($command);
    }

    private function extractPostNumeral($string)
    {
        if (preg_match('/\b(\d+)\b/', $string, $matches)) {
            return (int)$matches[1];
        } else {
            return false;
        }
    }

}
