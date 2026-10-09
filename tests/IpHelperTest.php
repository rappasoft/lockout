<?php

namespace Rappasoft\Lockout\Tests;

use PHPUnit\Framework\Attributes\Test;

use Rappasoft\Lockout\Helpers\IpHelper;

class IpHelperTest extends TestCase
{
    #[Test]
    public function exact_ip_match_works()
    {
        $this->assertTrue(IpHelper::isIpAllowed('127.0.0.1', ['127.0.0.1']));
        $this->assertFalse(IpHelper::isIpAllowed('127.0.0.2', ['127.0.0.1']));
    }

    #[Test]
    public function cidr_notation_works()
    {
        $this->assertTrue(IpHelper::isIpAllowed('192.168.1.100', ['192.168.1.0/24']));
        $this->assertTrue(IpHelper::isIpAllowed('192.168.1.255', ['192.168.1.0/24']));
        $this->assertFalse(IpHelper::isIpAllowed('192.168.2.100', ['192.168.1.0/24']));
    }

    #[Test]
    public function cidr_with_different_masks_works()
    {
        $this->assertTrue(IpHelper::isIpAllowed('10.0.0.1', ['10.0.0.0/8']));
        $this->assertTrue(IpHelper::isIpAllowed('10.255.255.255', ['10.0.0.0/8']));
        $this->assertFalse(IpHelper::isIpAllowed('11.0.0.1', ['10.0.0.0/8']));
    }

    #[Test]
    public function multiple_ips_in_whitelist_work()
    {
        $whitelist = ['127.0.0.1', '192.168.1.0/24', '10.0.0.1'];

        $this->assertTrue(IpHelper::isIpAllowed('127.0.0.1', $whitelist));
        $this->assertTrue(IpHelper::isIpAllowed('192.168.1.50', $whitelist));
        $this->assertTrue(IpHelper::isIpAllowed('10.0.0.1', $whitelist));
        $this->assertFalse(IpHelper::isIpAllowed('172.16.0.1', $whitelist));
    }

    #[Test]
    public function empty_whitelist_returns_false()
    {
        $this->assertFalse(IpHelper::isIpAllowed('127.0.0.1', []));
    }

    #[Test]
    public function ip_blacklist_works()
    {
        $this->assertTrue(IpHelper::isIpBlocked('192.168.1.1', ['192.168.1.1']));
        $this->assertFalse(IpHelper::isIpBlocked('192.168.1.2', ['192.168.1.1']));
    }

    #[Test]
    public function ip_blacklist_with_cidr_works()
    {
        $this->assertTrue(IpHelper::isIpBlocked('192.168.1.100', ['192.168.1.0/24']));
        $this->assertFalse(IpHelper::isIpBlocked('192.168.2.100', ['192.168.1.0/24']));
    }

    #[Test]
    public function empty_blacklist_returns_false()
    {
        $this->assertFalse(IpHelper::isIpBlocked('127.0.0.1', []));
    }

    #[Test]
    public function parse_ip_list_from_string()
    {
        $result = IpHelper::parseIpList('127.0.0.1,192.168.1.1,10.0.0.0/8');

        $this->assertEquals(['127.0.0.1', '192.168.1.1', '10.0.0.0/8'], $result);
    }

    #[Test]
    public function parse_ip_list_handles_spaces()
    {
        $result = IpHelper::parseIpList('127.0.0.1, 192.168.1.1 , 10.0.0.0/8');

        $this->assertEquals(['127.0.0.1', '192.168.1.1', '10.0.0.0/8'], $result);
    }

    #[Test]
    public function parse_ip_list_handles_null()
    {
        $this->assertEquals([], IpHelper::parseIpList(null));
    }

    #[Test]
    public function parse_ip_list_handles_empty_string()
    {
        $this->assertEquals([], IpHelper::parseIpList(''));
    }

    #[Test]
    public function invalid_cidr_returns_false()
    {
        // Invalid IP in CIDR
        $this->assertFalse(IpHelper::isIpAllowed('192.168.1.1', ['999.999.999.999/24']));
    }

    public function testMalformedCidrNeverWhitelistsAnIp()
    {
        foreach (['192.168.1.0/33', '192.168.1.0/-1', '192.168.1.0/', '192.168.1.0/foo', '192.168.1.0/24/0'] as $cidr) {
            $this->assertFalse(IpHelper::isIpAllowed('192.168.1.1', [$cidr]), $cidr);
            $this->assertFalse(IpHelper::isIpBlocked('192.168.1.1', [$cidr]), $cidr);
        }
    }

    public function testIpv6AddressesAndCidrRanges()
    {
        $this->assertTrue(IpHelper::isIpAllowed('2001:db8::1', ['2001:db8::/32']));
        $this->assertFalse(IpHelper::isIpAllowed('2001:db9::1', ['2001:db8::/32']));
        $this->assertTrue(IpHelper::isIpBlocked('::1', ['::1/128']));
        $this->assertTrue(IpHelper::isIpAllowed('::1', ['0:0:0:0:0:0:0:1']));
        $this->assertFalse(IpHelper::isIpAllowed('not-an-ip', ['not-an-ip']));
    }

    #[Test]
    public function cidr_edge_cases()
    {
        // /32 should match only exact IP
        $this->assertTrue(IpHelper::isIpAllowed('192.168.1.1', ['192.168.1.1/32']));
        $this->assertFalse(IpHelper::isIpAllowed('192.168.1.2', ['192.168.1.1/32']));

        // /0 should match everything (entire IPv4 range)
        $this->assertTrue(IpHelper::isIpAllowed('0.0.0.0', ['0.0.0.0/0']));
        $this->assertTrue(IpHelper::isIpAllowed('255.255.255.255', ['0.0.0.0/0']));
    }
}
