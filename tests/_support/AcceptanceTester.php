<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{

    public function __construct(\Codeception\Scenario $scenario)
    {
        parent::__construct($scenario);
      //  require_once ("/var/www/html/wp-content/plugins/cacbot/src/Cacbot/autoloader.php");
      //==  require_once ("/var/www/html/wp-content/plugins/aion-mother/src/CacbotMothership/autoloader.php");
    }

    private $mothership_url;
    private $remote_node_url;
    private $page_source;

    use _generated\AcceptanceTesterActions;

    public function setupTestPostOnLocalhost()
    {
        //This is the localhost context

        $this->amOnUrl("http://localhost/");
        $this->loginAsAdmin();
        $this->amOnPage("/wp-admin/");
        $this->see("Aion");
        $command = 'wp post create --post_type=cacbot-conversation --post_title="TestPost"';
        $postID = ( $this->extractPostNumeral(shell_exec($command)));

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

    public function setupTestPostOnMothership()
    {
        $remoteNodeIP = $this->getSiteUrls();
        $remoteNodeIP = $remoteNodeIP[0];
        $this->amOnUrl("http://" . $remoteNodeIP);
        $this->loginAsAdmin();
        $this->amOnPage("/wp-admin/");
        $this->see("Mothership");

        $command = "ssh -o StrictHostKeyChecking=no -i /home/johndee/ozempic.pem ubuntu@" . $remoteNodeIP . " /var/www/html/wp-content/plugins/cacbot/tests/acceptance/intelligent_response_setup.sh";
        return (shell_exec($command));
    }

    public function setupTestPostOnRemoteNode()
    {
        $remoteNodeIP = $this->getSiteUrls();
        $remoteNodeIP = $remoteNodeIP[1];
        $this->amOnUrl("http://" . $remoteNodeIP);
        $this->loginAsAdmin();
        $this->amOnPage("/wp-admin/");
        $this->see("RemoteNode");

        $command = "ssh -o StrictHostKeyChecking=no -i /home/johndee/ozempic.pem ubuntu@" . $remoteNodeIP . " /var/www/html/wp-content/plugins/cacbot/tests/acceptance/intelligent_response_setup.sh";
        return(shell_exec($command));
    }

    public function extractPostNumeral($string) {
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

    public function makeAComment($comment)

    {
        $I = $this;
        $I->amOnPage("/cacbot-conversation/testpost");
        $I->fillField("comment", $comment);
        $I->scrollTo("#submit");
        $I->click("SUBMIT");
    }

    public function shouldSeeAnIntelligentResponse($response)
    {
        $I = $this;
        $I->amOnPage("/cacbot-conversation/testpost");
        $I->see($response);
    }

    public function cleanupAfterRemotenodeIntelligentResponse(){}

    /**
     * Function to delete old posts from servers
     */
    public function deleteOldPosts($mothershipIP, $remoteNodeIP) {
        $privateKey = '/home/johndee/ozempic.pem';

        // Delete on mothership
        executeRemoteCommand($mothershipIP, "php /var/www/html/wp-content/plugins/cacbot/doDeleteAllEtmConnections.php", $privateKey);

        // Delete on remote node
        executeRemoteCommand($remoteNodeIP, "php /var/www/html/wp-content/plugins/cacbot/doDeleteTestEtmConnections.php", $privateKey);

        // Delete locally
        echo(shell_exec("php /var/www/html/wp-content/plugins/cacbot/doDeleteTestEtmConnections.php"));
    }


}
