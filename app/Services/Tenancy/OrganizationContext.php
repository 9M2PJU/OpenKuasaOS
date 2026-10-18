<?php

namespace App\Services\Tenancy;

use App\Models\Org;

class OrganizationContext
{
    protected static ?Org $currentOrg = null;
    protected static ?string $currentOrgId = null;

    public static function set(?Org $org): void
    {
        static::$currentOrg = $org;
        static::$currentOrgId = $org?->id;
    }

    public static function setId(?string $orgId): void
    {
        static::$currentOrgId = $orgId;
        if ($orgId && (!static::$currentOrg || static::$currentOrg->id !== $orgId)) {
            static::$currentOrg = Org::find($orgId);
        } elseif (!$orgId) {
            static::$currentOrg = null;
        }
    }

    public static function get(): ?Org
    {
        return static::$currentOrg;
    }

    public static function getId(): ?string
    {
        return static::$currentOrgId;
    }

    public static function id(): ?string
    {
        return static::$currentOrgId;
    }

    public static function clear(): void
    {
        static::$currentOrg = null;
        static::$currentOrgId = null;
    }
}
