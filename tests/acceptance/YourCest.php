<?php

class YourCest
{
public function testExample(AcceptanceTester $I)
{
    $envVariable = getenv('MODE');
    $I->comment("Environment variable: " . $envVariable);

}
}