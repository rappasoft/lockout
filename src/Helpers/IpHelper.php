<?php

namespace Rappasoft\Lockout\Helpers;

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
        // Exact match
        if ($ip === $pattern) {
            return true;
        }

        // CIDR notation support
        if (str_contains($pattern, '/')) {
            return self::ipInCidr($ip, $pattern);
        }

        return false;
    }

    /**
     * Check if an IP is within a CIDR range.
     */
    protected static function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = explode('/', $cidr);

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $maskLong = -1 << (32 - (int) $mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
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
