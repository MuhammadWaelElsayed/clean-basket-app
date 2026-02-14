<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssignSuperAdminPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:assign-super-admin-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create permissions and assign roles to admin users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting permission and role assignment...');

        // Create permissions first
        $this->createPermissions();

        // Create roles
        $this->createRoles();

        // Assign roles to users
        $this->assignRoles();

        $this->info('Permissions and roles assigned successfully!');
    }

    /**
     * Create all permissions
     */
    protected function createPermissions()
    {
        $this->info('Creating permissions...');

        $permissions = $this->getAllPermissions();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'admin']
            );
            $this->line("Created/verified permission: {$permission}");
        }

        $this->info('All permissions created successfully!');
    }

    /**
     * Create roles
     */
    protected function createRoles()
    {
        $this->info('Creating roles...');

        // Create Super Admin role with all permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
        $superAdminRole->syncPermissions($this->getSuperAdminPermissions());
        $this->info('Super Admin role created with all permissions');

        // Create Supervisor role with limited permissions
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'admin']);
//        $supervisorRole->syncPermissions($this->getSupervisorPermissions());
        $this->info('Supervisor role created with limited permissions');
    }

    /**
     * Assign roles to admin users
     */
    protected function assignRoles()
    {
        $this->info('Assigning roles to users...');

        $admins = Admin::all();

        // Assign super admin role
        $superAdmin = $admins->firstWhere('email', 'admin@devicebee.com');
        if ($superAdmin) {
            if (!$superAdmin->hasRole('super_admin')) {
                $superAdmin->assignRole('super_admin');
                $this->info('Super Admin role assigned to admin@devicebee.com');
            } else {
                $this->info('admin@devicebee.com already has Super Admin role');
            }
        } else {
            $this->warn('Super Admin user (admin@devicebee.com) not found!');
        }
    }

    /**
     * Get all available permissions
     */
    protected function getAllPermissions()
    {
        return array_merge(
            $this->getPermissions(),
            $this->getSpecialPermissions()
        );
    }

    /**
     * Get CRUD permissions for all models
     */
    public function getPermissions()
    {
        $models = [
            'partner',
            'driver',
            'item',
            'working_hours',
            'order',
            'external_driver',
            'customer',
            'discount',
            'service',
            'city',
            'page',
            'banner',
            'onboard',
            'ticket'
        ];

        $permissions = [];
        $actions = ['view', 'create', 'update', 'delete'];

        foreach ($models as $model) {
            foreach ($actions as $action) {
                $permissions[] = "{$action}_{$model}";
            }
            // Add list permission for viewing multiple records
            $permissions[] = "list_{$model}";
        }

        return $permissions;
    }

    /**
     * Get special/additional permissions
     */
    public function getSpecialPermissions()
    {
        return [
            'service_fee_settings',
            'integration_tokens',

            'partners_map',
            'bulk_assign_services',
            'orders_map',
            'drivers_map',

            'basket_requests',
            'basket_inventory',
            'add_new_basket_inventory',

            'app_settings',

            'wallet_transactions',
            'wallet_manual_charge',
            'wallet_manual_withdraw',
            'wallet_settings',

            'manage_packages',
            'packages_finance',
            'packages_reports',

            'website_inquiry',

            'export_data',

            'support',

            'manage_roles_and_permissions',

            'manage_b2b_clients',
            'manage_b2b_pricing_tiers',
            'manage_b2b_custom_prices',
            'manage_b2b_orders',
        ];
    }

    /**
     * Get permissions for Super Admin role (all permissions)
     */
    public function getSuperAdminPermissions()
    {
        return $this->getAllPermissions();
    }

    public function revokeAllPermissions()
    {
        $admins = Admin::all();
        foreach ($admins as $admin) {
            $admin->roles()->detach();
            $admin->permissions()->detach();
        }
        $this->info('All permissions and roles revoked');
    }
}
