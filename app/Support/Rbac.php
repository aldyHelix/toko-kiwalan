<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Single source of truth for the admin RBAC catalogue: guard, roles, permissions
 * and the role-permission map. Consumed by the RolePermissionSeeder, the Admin
 * model and authorization checks.
 *
 * Roles/permissions are all on the `admin` guard — the storefront `web` guard
 * (customers) carries no roles in Fase 1. super-admin additionally bypasses all
 * checks via a Gate::before rule (see AppServiceProvider).
 */
final class Rbac
{
    public const GUARD = 'admin';

    // Roles
    public const SUPER_ADMIN = 'super-admin';

    public const ADMIN = 'admin';

    public const BRANCH_MANAGER = 'branch-manager';

    public const ROLES = [
        self::SUPER_ADMIN,
        self::ADMIN,
        self::BRANCH_MANAGER,
    ];

    // Permissions (areas grow as later modules land)
    public const MANAGE_SETTINGS = 'manage settings';

    public const MANAGE_BRANCHES = 'manage branches';

    public const MANAGE_CATALOG = 'manage catalog';

    public const MANAGE_ORDERS = 'manage orders';

    public const MANAGE_PAYMENTS = 'manage payments';

    public const MANAGE_THEMES = 'manage themes';

    public const MANAGE_PRODUCT_IMPORTS = 'manage product imports';

    public const PERMISSIONS = [
        self::MANAGE_SETTINGS,
        self::MANAGE_BRANCHES,
        self::MANAGE_CATALOG,
        self::MANAGE_ORDERS,
        self::MANAGE_PAYMENTS,
        self::MANAGE_THEMES,
        self::MANAGE_PRODUCT_IMPORTS,
    ];

    /**
     * Role → permissions granted. super-admin is intentionally omitted: it is
     * granted everything via Gate::before, not an explicit list.
     *
     * @return array<string, array<int, string>>
     */
    public static function rolePermissions(): array
    {
        return [
            self::ADMIN => self::PERMISSIONS,
            self::BRANCH_MANAGER => [
                self::MANAGE_CATALOG,
                self::MANAGE_ORDERS,
            ],
        ];
    }
}
