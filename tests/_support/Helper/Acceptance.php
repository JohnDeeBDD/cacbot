<?php
namespace Helper;

class Acceptance extends \Codeception\Module{

    private $hostname = "";

    /*
    Command line functions are executed either on localhost or on the remote dev servers.
    This function checks where the function is running, and if on local adds a prefix
    */
    public function xxdoExecuteCommandLine($command, $target){
        $prefix = "";
        if($this->hostname == "kali"){
            $prefix = "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $target . " ";
        }
        return shell_exec($prefix . $command);
    }

    /*
     * Updates the WPWebDriver module configuration to switch the active domain for the WordPress installation under test.
     * This function allows you to dynamically switch the test environment's URL. You can set it to "localhost"
     * for local testing, or to the IP address of a remote node or the mothership server. The function accepts
     * an associative array that specifies the new configuration parameters, such as the base URL.
     *
     * Example:
     *   $I->reconfigureThisVariable(["url" => "http://3.14.55.132"]);
     *   You can then login to that site with "Codeception" and "password"
     *
     * @param array $array An associative array with the new configuration options for WPWebDriver.
     *                     The 'url' key is commonly used to specify the WordPress site's URL.
     * @return void This function does not return a value.
     */
    public function reconfigureThisVariable($array){
        $this->getModule('WPWebDriver')->_reconfigure($array);
        $this->getModule('WPWebDriver')->_restart();
    }


    public function _beforeSuite($settings = []){
        $this->hostname = shell_exec("hostname");
    }

    public function pauseInTerminal(){
        echo "Press ENTER to continue: ";
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        fclose($handle);
        echo "\n";
    }

    public function getSiteUrls(){
        return json_decode(file_get_contents('/var/www/html/wp-content/plugins/cacbot/servers.json'), true);
    }

    /**
     * Helper function to execute remote commands via SSH
     */
    public function executeRemoteCommandAsUbuntu($serverIP, $command) {
        $sshCommand = "ssh -o StrictHostKeyChecking=no -i /home/johndee/ozempic.pem ubuntu@$serverIP $command";
        $result = shell_exec($sshCommand);
        echo($result);
        return $result;
    }

}
