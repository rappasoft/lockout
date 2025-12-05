<?php

namespace Rappasoft\Lockout\Tests;

use Rappasoft\Lockout\Helpers\IpHelper;

class IpHelperTest extends TestCase
{
    /** @test */
    public function exact_ip_match_works()
    {
        $this->assertTrue(IpHelper::isIpAllowed('127.0.0.1', ['127.0.0.1']));
        $this->assertFalse(IpHelper::isIpAllowed('127.0.0.2', ['127.0.0.1']));
    }

    /** @test */
    public function cidr_notation_works()
    {
        $this->assertTrue(IpHelper::isIpAllowed('192.168.1.100', ['192.168.1.0/24']));
        $this->assertTrue(IpHelper::isIpAllowed('192.168.1.255', ['192.168.1.0/24']));
        $this->assertFalse(IpHelper::isIpAllowed('192.168.2.100', ['192.168.1.0/24']));
    }

    /** @test */
    public function cidr_with_different_masks_works()
    {
        $this->assertTrue(IpHelper::isIpAllowed('10.0.0.1', ['10.0.0.0/8']));
        $this->assertTrue(IpHelper::isIpAllowed('10.255.255.255', ['10.0.0.0/8']));
        $this->assertFalse(IpHelper::isIpAllowed('11.0.0.1', ['10.0.0.0/8']));
    }

    /** @test */
    public function multiple_ips_in_whitelist_work()
    {
        $whitelist = ['127.0.0.1', '192.168.1.0/24', '10.0.0.1'];
        
        $this->assertTrue(IpHelper::isIpAllowed('127.0.0.1', $whitelist));
        $this->assertTrue(IpHelper::isIpAllowed('192.168.1.50', $whitelist));
        $this->assertTrue(IpHelper::isIpAllowed('10.0.0.1', $whitelist));
        $this->assertFalse(IpHelper::isIpAllowed('172.16.0.1', $whitelist));
    }

    /** @test */
    public function empty_whitelist_returns_false()
    {
        $this->assertFalse(IpHelper::isIpAllowed('127.0.0.1', []));
    }

    /** @test */
    public function ip_blacklist_works()
    {
        $this->assertTrue(IpHelper::isIpBlocked('192.168.1.1', ['192.168.1.1']));
        $this->assertFalse(IpHelper::isIpBlocked('192.168.1.2', ['192.168.1.1']));
    }

    /** @test */
    public function ip_blacklist_with_cidr_works()
    {
        $this->assertTrue(IpHelper::isIpBlocked('192.168.1.100', ['192.168.1.0/24']));
        $this->assertFalse(IpHelper::isIpBlocked('192.168.2.100', ['192.168.1.0/24']));
    }

    /** @test */
    public function empty_blacklist_returns_false()
    {
        $this->assertFalse(IpHelper::isIpBlocked('127.0.0.1', []));
    }

    /** @test */
    public function parse_ip_list_from_string()
    {
        $result = IpHelper::parseIpList('127.0.0.1,192.168.1.1,10.0.0.0/8');
        
        $this->assertEquals(['127.0.0.1', '192.168.1.1', '10.0.0.0/8'], $result);
    }

    /** @test */
    public function parse_ip_list_handles_spaces()
    {
        $result = IpHelper::parseIpList('127.0.0.1, 192.168.1.1 , 10.0.0.0/8');
        
        $this->assertEquals(['127.0.0.1', '192.168.1.1', '10.0.0.0/8'], $result);
    }

    /** @test */
    public function parse_ip_list_handles_null()
    {
        $this->assertEquals([], IpHelper::parseIpList(null));
    }

    /** @test */
    public function parse_ip_list_handles_empty_string()
    {
        $this->assertEquals([], IpHelper::parseIpList(''));
    }

    /** @test */
    public function invalid_cidr_returns_false()
    {
        // Invalid IP in CIDR
        $this->assertFalse(IpHelper::isIpAllowed('192.168.1.1', ['999.999.999.999/24']));
    }

    /** @test */
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

