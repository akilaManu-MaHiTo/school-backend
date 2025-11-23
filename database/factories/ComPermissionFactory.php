<?php

namespace Database\Factories;

use App\Models\ComPermission;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComPermissionFactory extends Factory
{
    protected $model = ComPermission::class;

    public function definition(): array
    {
        return [
            'userType' => 'Admin',
            'description' => 'Administrator Role with full permissions',
            'permissionObject' => json_encode([
                "INSIGHT_VIEW" => true,
                "ADMIN_USERS_EDIT" => true,
                "ADMIN_USERS_VIEW" => true,
                "ADMIN_USERS_DELETE" => true,
                "ADMIN_ACCESS_MNG_EDIT" => true,
                "ADMIN_ACCESS_MNG_VIEW" => true,
                "ADMIN_ACCESS_MNG_CREATE" => true,
                "ADMIN_ACCESS_MNG_DELETE" => true,
                "AUDIT_INSPECTION_CALENDAR_EDIT" => true,
                "AUDIT_INSPECTION_CALENDAR_VIEW" => true,
                "AUDIT_INSPECTION_DASHBOARD_VIEW" => true,
                "AUDIT_INSPECTION_CALENDAR_CREATE" => true,
                "AUDIT_INSPECTION_CALENDAR_DELETE" => true,
                "AUDIT_INSPECTION_INTERNAL_AUDIT_TASK_EDIT" => true,
                "AUDIT_INSPECTION_INTERNAL_AUDIT_TASK_VIEW" => true,
                "AUDIT_INSPECTION_INTERNAL_AUDIT_TASK_CREATE" => true,
                "AUDIT_INSPECTION_INTERNAL_AUDIT_TASK_DELETE" => false,
                "AUDIT_INSPECTION_INTERNAL_AUDIT_REGISTER_EDIT" => true,
                "AUDIT_INSPECTION_INTERNAL_AUDIT_REGISTER_VIEW" => true,
                "AUDIT_INSPECTION_INTERNAL_AUDIT_REGISTER_CREATE" => true,
                "AUDIT_INSPECTION_INTERNAL_AUDIT_REGISTER_DELETE" => true
            ]),
        ];
    }
}
