<?php
require_once 'Net/DNS.php';

class Net_DNS_RRTest extends PHPUnit_Framework_TestCase {

    public function testBug16501() {
        $rr = Net_DNS_RR::factory('example.com. 3600 IN SOA ns.example.com. support.example.com. 8 3600 600 1209600 3600');

        $expected = new Net_DNS_RR_SOA($foo = null, $bar = null);
        $expected->name = 'example.com';
        $expected->type = 'SOA';
        $expected->class = 'IN';
        $expected->ttl = 3600;
        $expected->rdlength = 0;

        $expected->mname = 'ns.example.com';
        $expected->rname = 'support.example.com';
        $expected->serial = 8;
        $expected->refresh = 3600;
        $expected->retry = 600;
        $expected->expire = 1209600;
        $expected->minimum = 3600;


        $this->assertSame(print_r($expected, true), print_r($rr, true));
    }

    public function testBug16504() {
        $rr = Net_DNS_RR::new_from_array(array('type' => 'A', 'name' => 'example.com', 'ttl' => '3600' , 'address' => '192.168.0.15'));

        $this->assertTrue($rr instanceof Net_DNS_RR_A);
    }


}
