<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Net_DNS_AllTests::main');
}

require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'Net_DNS_ResolverTest.php';
require_once 'Net_DNS_RRTest.php';
require_once 'Net_DNS_RR_LOCTest.php';

class Net_DNS_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PEAR - Net_DNS');

        $suite->addTestSuite('Net_DNS_ResolverTest');
        $suite->addTestSuite('Net_DNS_RRTest');
        $suite->addTestSuite('Net_DNS_RR_LOCTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Net_DNS_AllTests::main') {
    Net_DNS_AllTests::main();
}
