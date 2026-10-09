<?php

namespace Rappasoft\Lockout\Helpers;

use Symfony\Component\HttpFoundation\IpUtils;

class IpHelper
{
    /**
     * Check if an IP address matches a whitelist entry (supports CIDR notation).
     */
    public static function isIpAllowed(string $ip, array $whitelist): bool
    {
        if (empty($whitelist)) {
            return false;
        }

        foreach ($whitelist as $allowedIp) {
            if (self::ipMatches($ip, $allowedIp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IP address matches a blacklist entry (supports CIDR notation).
     */
    public static function isIpBlocked(string $ip, array $blacklist): bool
    {
        if (empty($blacklist)) {
            return false;
        }

        foreach ($blacklist as $blockedIp) {
            if (self::ipMatches($ip, $blockedIp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IP matches a pattern (supports CIDR notation).
     */
    protected static function ipMatches(string $ip, string $pattern): bool
    {
        // CIDR notation support
        if (str_contains($pattern, '/')) {
            return self::ipInCidr($ip, $pattern);
        }

        return IpUtils::checkIp($ip, $pattern);
    }

    /**
     * Check if an IP is within a CIDR range.
     */
    protected static function ipInCidr(string $ip, string $cidr): bool
    {
        $mask = explode('/', $cidr, 2)[1];
        if (! ctype_digit($mask)) {
            return false;
        }

        return IpUtils::checkIp($ip, $cidr);
    }

    /**
     * Parse IP whitelist/blacklist from comma-separated string.
     */
    public static function parseIpList(?string $ipList): array
    {
        if (empty($ipList)) {
            return [];
        }

        return array_map('trim', explode(',', $ipList));
    }
}
