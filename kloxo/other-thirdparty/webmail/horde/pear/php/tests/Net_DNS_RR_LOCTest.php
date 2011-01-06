<?php
require_once 'Net/DNS.php';

class Net_DNS_RR_LOCTest extends PHPUnit_Framework_TestCase {

    public function testShouldSetUpInitialState() {
        $this->markTestIncomplete('function Net_DNS_RR_LOC($rro, $data, $offset = 0)');
    }

    public function testShouldParse() {
        $this->markTestIncomplete('parse');
        //LOC record yahoo.com.   IN LOC   37 23 30.900 N 121 59 19.000 W 7.00m 100.00m 100.00m 2.00m
    }

    public function testShouldFormatDataCorrectly() {
        $this->markTestIncomplete('rdatastr');
    }

    public function testShouldDoSomethingWithRRData() {
        $this->markTestIncomplete('rr_rdata');
    }

    public function testShouldReturnCorrectNtovalFigures() {
        $this->markTestIncomplete('precsize_ntoval');
    }

    public function testShouldReturnCorrectValtonFigures() {
        $this->markTestIncomplete('precsize_valton');
    }

    public function testShouldReturnACorrectlyFormattedLatDonDmsString() {
        $this->markTestIncomplete('latlon2dms');
    }

}
