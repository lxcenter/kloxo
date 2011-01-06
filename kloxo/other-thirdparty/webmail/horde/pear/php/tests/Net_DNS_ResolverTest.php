<?php
require_once 'Net/DNS.php';

class Net_DNS_ResolverTest extends PHPUnit_Framework_TestCase {

    public function testBug16501() {
        $resolver = new Net_DNS_Resolver(array('nameservers' => array('192.168.0.1')));

        $packet = new Net_DNS_Packet();

        $packet->header = new Net_DNS_Header();
        $packet->header->id = $resolver->nextid();
        $packet->header->qr = 0;
        $packet->header->opcode = "UPDATE";

        $packet->question[0] = new Net_DNS_Question('example.com', 'SOA', 'IN');
        $packet->answer = array();

        $packet->authority[0] = Net_DNS_RR::factory('example.com. 0 ANY A');
        $packet->authority[1] = Net_DNS_RR::factory('example.com. 1800 IN A 192.168.0.2');

        $tsig = Net_DNS_RR::factory('example-key TSIG 6i7jUkH1LXDnMKc7ElBKXQ==');
        $packet->additional = array($tsig);

        $packet->header->qdcount = count($packet->question);
        $packet->header->ancount = count($packet->answer);
        $packet->header->nscount = count($packet->authority);
        $packet->header->arcount = count($packet->additional);

        $response = $resolver->send_tcp($packet, $packet->data());

        $this->assertSame("NOERROR", $response->header->rcode);
    }

    public function testBug16502() {
        $resolver = new Net_DNS_Resolver(array('nameservers' => array('192.168.0.1')));

        $packet = new Net_DNS_Packet();

        $packet->header = new Net_DNS_Header();
        $packet->header->id = $resolver->nextid();
        $packet->header->qr = 0;
        $packet->header->opcode = "UPDATE";

        $packet->question[0] = new Net_DNS_Question('example.com', 'SOA', 'IN');
        $packet->answer = array();
        $packet->authority[0] = Net_DNS_RR::factory('example.com. 0 ANY A');
        $packet->authority[1] = Net_DNS_RR::factory('example.com. 1800 IN A 192.168.0.2');
        $tsig = Net_DNS_RR::factory('example-key TSIG 6i7jUkH1LXDnMKc7ElBKXQ==');
        $packet->additional = array($tsig);
        $packet->header->qdcount = count($packet->question);
        $packet->header->ancount = count($packet->answer);
        $packet->header->nscount = count($packet->authority);
        $packet->header->arcount = count($packet->additional);
        $response = $resolver->send_tcp($packet, $packet->data());

        $this->assertSame("NOERROR", $response->header->rcode);
    }

    public function testBug16515() {
        $r = new Net_DNS_Resolver();

        $data = $r->query('example.com.', 'TXT');
        $this->assertNotSame(false, $data);


        $this->assertTrue(is_array($data->answer), "Expected an array, found " . gettype($data->answer) . "\n" . print_r($data->answer, true));

        $txt_rr = reset($data->answer);

        $this->assertSame('example.com. 3600 IN TXT "x" "y" "z"', $txt_rr->string());
        $this->assertSame('xyz', $txt_rr->rr_rdata(0, 0));
    }


    public function testPregChange() {

        $r = new Net_DNS_Resolver();

        // A
        $a = Net_DNS_RR::factory('example.com. 1800 IN A 10.10.10.10');
        $this->assertSame('10.10.10.10', $a->address);

        // CNAME
        $cname = Net_DNS_RR::factory('example.com. 1800 IN CNAME www.example.com');
        $this->assertSame('www.example.com', $cname->cname);

        // HINFO
        $hinfo = Net_DNS_RR::factory('example.com. 1800 IN HINFO PC-Intel-700mhz "Redhat Linux 7.1"');
        $this->assertSame('PC-Intel-700mhz', $hinfo->cpu);
        $this->assertSame('"Redhat Linux 7.1"', $hinfo->os);

        // MX
        $mx = Net_DNS_RR::factory('example.com. 1800 IN MX 10 mail.example.com');
        $this->assertSame('10', $mx->preference);
        $this->assertSame('mail.example.com', $mx->exchange);

        // NAPTR
        $naptr = Net_DNS_RR::factory('example.com. 1800 IN NAPTR 100 10 "S" "SIPD2U" "!^.*$!sip:customer-service@example.com!" _sip._udp.example.com');
        $this->assertSame('"S"', $naptr->flags);
        $this->assertSame('"SIPD2U"', $naptr->services);
        $this->assertSame('_sip._udp.example.com', $naptr->replacement);

        // NS
        $ns = Net_DNS_RR::factory('example.com. 1800 IN NS dns1.example.com');
        $this->assertSame('dns1.example.com', $ns->nsdname);

        // PTR
        $ptr = Net_DNS_RR::factory('192.168.0.100 1800 IN PTR mail.example.com');
        $this->assertSame('mail.example.com', $ptr->ptrdname);

        // SOA
        $soa = Net_DNS_RR::factory('example.com. 3600 IN SOA ns.example.com. support.example.com. 8 3600 600 1209600 3600');
        $this->assertSame('ns.example.com', $soa->mname);
        $this->assertSame('support.example.com', $soa->rname);

        // SRV
        $srv = Net_DNS_RR::factory('_xmpp-server._tcp.gmail.com. IN SRV 5 0 5269 xmpp-server.l.google.com.');
        $this->assertSame('_xmpp-server._tcp.gmail.com', $srv->name);
        $this->assertSame('5269', $srv->port);
        $this->assertSame('xmpp-server.l.google.com', $srv->target);

        // TXT
        $txt  = Net_DNS_RR::factory('example.com. 1800 IN TXT "text message"');
        $this->assertSame('"text message"', $txt->text);
    }
 }}
