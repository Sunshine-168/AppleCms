<?php

namespace App\Services;

use App\Libraries\IpLimit\IpLocationQuery;

class IpLocationService
{
    protected $ipQuery;

    public function __construct()
    {
        $this->ipQuery = new IpLocationQuery();
    }

    /**
     * 查询 IP 地址对应的省份
     * @param string $ip IP地址
     * @return string 省份简写
     */
    public function query($ip)
    {
        return $this->ipQuery->query($ip);
    }
}
