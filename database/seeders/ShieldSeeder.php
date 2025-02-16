<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"\u0645\u0633\u0626\u0648\u0648\u0644 \u0645\u0634\u062a\u0631\u064a\u0627\u062a","guard_name":"web","permissions":["view_purchase::invoice","view_any_purchase::invoice","create_purchase::invoice","update_purchase::invoice","delete_purchase::invoice","delete_any_purchase::invoice","force_delete_purchase::invoice","force_delete_any_purchase::invoice","view_area","view_any_area","create_area","update_area","delete_area","delete_any_area","force_delete_area","force_delete_any_area"]},{"name":"super_admin","guard_name":"web","permissions":["view_purchase::invoice","view_any_purchase::invoice","create_purchase::invoice","update_purchase::invoice","delete_purchase::invoice","delete_any_purchase::invoice","force_delete_purchase::invoice","force_delete_any_purchase::invoice","view_brand","view_any_brand","create_brand","update_brand","delete_brand","delete_any_brand","force_delete_brand","force_delete_any_brand","view_category","view_any_category","create_category","update_category","delete_category","delete_any_category","force_delete_category","force_delete_any_category","view_product","view_any_product","create_product","update_product","delete_product","delete_any_product","force_delete_product","force_delete_any_product","view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","view_user","view_any_user","create_user","update_user","delete_user","delete_any_user","force_delete_user","force_delete_any_user","view_area","view_any_area","create_area","update_area","delete_area","delete_any_area","force_delete_area","force_delete_any_area","view_activitylog","view_any_activitylog","create_activitylog","update_activitylog","delete_activitylog","delete_any_activitylog","force_delete_activitylog","force_delete_any_activitylog","view_custom::activity::log","view_any_custom::activity::log","create_custom::activity::log","update_custom::activity::log","delete_custom::activity::log","delete_any_custom::activity::log","force_delete_custom::activity::log","force_delete_any_custom::activity::log"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
