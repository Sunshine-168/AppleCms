<?php

namespace Tests\Unit;

use App\Services\Video\DomainBindService;
use Tests\TestCase;

class DomainBindServiceTest extends TestCase
{
    public function test_normalize_strips_scheme_path_port_and_case(): void
    {
        $this->assertSame('example.com', DomainBindService::normalizeHost('https://WWW.Example.com/x'));
        $this->assertSame('example.com', DomainBindService::normalizeHost('http://example.com:80/foo'));
        $this->assertSame('example.com', DomainBindService::normalizeHost('example.com:443'));
        $this->assertSame('127.0.0.1', DomainBindService::normalizeHost('127.0.0.1'));
        $this->assertSame('localhost', DomainBindService::normalizeHost('LocalHost'));
        $this->assertSame('', DomainBindService::normalizeHost(''));
        $this->assertSame('', DomainBindService::normalizeHost('not a host'));
    }

    public function test_host_candidates_pair_www_and_skip_ip(): void
    {
        $this->assertSame(['example.com', 'www.example.com'], DomainBindService::hostCandidates('example.com'));
        $this->assertSame(['example.com', 'www.example.com'], DomainBindService::hostCandidates('www.example.com'));
        $this->assertSame(['127.0.0.1'], DomainBindService::hostCandidates('127.0.0.1'));
        $this->assertSame(['localhost'], DomainBindService::hostCandidates('localhost'));
        $this->assertSame([], DomainBindService::hostCandidates(''));
    }
}
