<?php

namespace Tests\Unit;

use App\Support\AdminIpAllowlist;
use PHPUnit\Framework\TestCase;

class AdminIpAllowlistTest extends TestCase
{
    public function test_empty_list_allows_any_ip(): void
    {
        $this->assertTrue(AdminIpAllowlist::allows('203.0.113.8', []));
        $this->assertSame([], AdminIpAllowlist::parse(''));
        $this->assertSame([], AdminIpAllowlist::parse("  \n# comment"));
    }

    public function test_parses_comma_and_cidr_and_skips_star(): void
    {
        $this->assertSame(
            ['203.0.113.8', '192.168.1.0/24', '*'],
            AdminIpAllowlist::parse("203.0.113.8, 192.168.1.0/24\n*")
        );
        $this->assertTrue(AdminIpAllowlist::isValid('203.0.113.8'));
        $this->assertTrue(AdminIpAllowlist::isValid('192.168.1.0/24'));
        $this->assertFalse(AdminIpAllowlist::isValid('*'));
        $this->assertFalse(AdminIpAllowlist::isValid('office'));
    }

    public function test_cidr_match_and_local_detection(): void
    {
        $this->assertTrue(AdminIpAllowlist::allows('192.168.1.20', ['192.168.1.0/24']));
        $this->assertFalse(AdminIpAllowlist::allows('10.0.0.2', ['192.168.1.0/24']));
        $this->assertTrue(AdminIpAllowlist::allows('127.0.0.1', ['127.0.0.1']));
        $this->assertTrue(AdminIpAllowlist::isLocal('127.0.0.1'));
        $this->assertTrue(AdminIpAllowlist::isLocal('192.168.0.8'));
        $this->assertFalse(AdminIpAllowlist::isLocal('203.0.113.8'));
    }
}
