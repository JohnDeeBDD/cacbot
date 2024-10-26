<?php

use Cacbot\Conversation;

class TrafficControllerTest extends \Codeception\TestCase\WPTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once('/var/www/html/wp-content/plugins/cacbot/src/Cacbot/autoloader.php');
        require_once('/var/www/html/wp-content/plugins/cacbot-mother/src/CacbotMothership/autoloader.php');
    }

    /**
     * @test
     * it should be instantiable
     */
    public function itShouldBeInstantiable()
    {
        $TrafficController = new \CacbotMothership\TrafficController();
    }
}