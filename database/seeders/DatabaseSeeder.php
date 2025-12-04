<?php

namespace Database\Seeders;

use App\Models\ComAssigneeLevel;
use App\Models\ComOrganization;
use App\Models\ComPermission;
use App\Models\ComResponsibleSection;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        ComPermission::factory()->create([
            'id'               => 1,
            'userType'         => 'Super Admin',
            'description'      => 'Administrator Role with full permissions',
            'permissionObject' => ([
                "INSIGHT_VIEW" => true,
                "STUDENT_PARENT_DASHBOARD_VIEW" => true,
                "TEACHERS_DASHBOARD_VIEW" => true,
                "MANAGEMENT_DASHBOARD_VIEW" => true,
                "ADMIN_USERS_VIEW" => true,
                "ADMIN_USERS_CREATE" => true,
                "ADMIN_USERS_EDIT" => true,
                "ADMIN_USERS_DELETE" => true,
                "ADMIN_ACCESS_MNG_VIEW" => true,
                "ADMIN_ACCESS_MNG_CREATE" => true,
                "ADMIN_ACCESS_MNG_EDIT" => true,
                "ADMIN_ACCESS_MNG_DELETE" => true,
                "SCHOOL_SETTINGS_VIEW" => true,
                "SCHOOL_SETTINGS_CREATE" => true,
                "SCHOOL_SETTINGS_EDIT" => true,
                "SCHOOL_SETTINGS_DELETE" => true,
                "ADD_CLASS_TEACHER_VIEW" => true,
                "ADD_CLASS_TEACHER_CREATE" => true,
                "ADD_CLASS_TEACHER_EDIT" => true,
                "ADD_CLASS_TEACHER_DELETE" => true,
                "STUDENT_PROMOTION_VIEW" => true,
                "STUDENT_PROMOTION_CREATE" => true,
                "STUDENT_PROMOTION_EDIT" => true,
                "STUDENT_PROMOTION_DELETE" => true,
                "STUDENT_PARENT_PARENT_REPORTS_VIEW" => true,
                "TEACHER_ClASS_REPORTS_VIEW" => true,
                "TEACHER_STUDENT_REPORTS_VIEW" => true,
                "MARKS_ENTRY_MONITORING_REPORTS_VIEW" => true,
                "MANAGEMENT_STAFF_STUDENT_REPORTS_VIEW" => true,
                "ADD_MARKS_VIEW" => true,
                "ADD_MARKS_CREATE" => true,
                "ADD_MARKS_EDIT" => true,
                "ADD_MARKS_DELETE" => true,
                "MANAGEMENT_DASHBOARD_CREATE" => true,
                "MANAGEMENT_DASHBOARD_EDIT" => true,
                "MANAGEMENT_DASHBOARD_DELETE" => true,
                "TEACHERS_DASHBOARD_CREATE" => true,
                "TEACHERS_DASHBOARD_EDIT" => true,
                "TEACHERS_DASHBOARD_DELETE" => true,
                "STUDENT_PARENT_DASHBOARD_CREATE" => true,
                "STUDENT_PARENT_DASHBOARD_EDIT" => true,
                "STUDENT_PARENT_DASHBOARD_DELETE" => true

            ]),
        ]);

        ComPermission::factory()->create([
            'id'               => 2,
            'userType'         => 'guest',
            'description'      => 'guest Role with full permissions',
            'permissionObject' => ([
                "INSIGHT_VIEW" => true,
                "STUDENT_PARENT_DASHBOARD_VIEW" => true,
                "TEACHERS_DASHBOARD_VIEW" => false,
                "MANAGEMENT_DASHBOARD_VIEW" => false,
                "ADMIN_USERS_VIEW" => false,
                "ADMIN_USERS_CREATE" => false,
                "ADMIN_USERS_EDIT" => false,
                "ADMIN_USERS_DELETE" => false,
                "ADMIN_ACCESS_MNG_VIEW" => false,
                "ADMIN_ACCESS_MNG_CREATE" => false,
                "ADMIN_ACCESS_MNG_EDIT" => false,
                "ADMIN_ACCESS_MNG_DELETE" => false,
                "SCHOOL_SETTINGS_VIEW" => false,
                "SCHOOL_SETTINGS_CREATE" => false,
                "SCHOOL_SETTINGS_EDIT" => false,
                "SCHOOL_SETTINGS_DELETE" => false,
                "ADD_CLASS_TEACHER_VIEW" => false,
                "ADD_CLASS_TEACHER_CREATE" => false,
                "ADD_CLASS_TEACHER_EDIT" => false,
                "ADD_CLASS_TEACHER_DELETE" => false,
                "STUDENT_PROMOTION_VIEW" => false,
                "STUDENT_PROMOTION_CREATE" => false,
                "STUDENT_PROMOTION_EDIT" => false,
                "STUDENT_PROMOTION_DELETE" => false,
                "STUDENT_PARENT_PARENT_REPORTS_VIEW" => false,
                "TEACHER_ClASS_REPORTS_VIEW" => false,
                "TEACHER_STUDENT_REPORTS_VIEW" => false,
                "MARKS_ENTRY_MONITORING_REPORTS_VIEW" => false,
                "MANAGEMENT_STAFF_STUDENT_REPORTS_VIEW" => false,
                "ADD_MARKS_VIEW" => false,
                "ADD_MARKS_CREATE" => false,
                "ADD_MARKS_EDIT" => false,
                "ADD_MARKS_DELETE" => false,
                "MANAGEMENT_DASHBOARD_CREATE" => false,
                "MANAGEMENT_DASHBOARD_EDIT" => false,
                "MANAGEMENT_DASHBOARD_DELETE" => false,
                "TEACHERS_DASHBOARD_CREATE" => false,
                "TEACHERS_DASHBOARD_EDIT" => false,
                "TEACHERS_DASHBOARD_DELETE" => false,
                "STUDENT_PARENT_DASHBOARD_CREATE" => false,
                "STUDENT_PARENT_DASHBOARD_EDIT" => false,
                "STUDENT_PARENT_DASHBOARD_DELETE" => false

            ]),
        ]);

        User::factory()->create([
            'name'          => 'Admin User',
            'userName'     => 'admin',
            'email'         => 'admin@suswebapp.com',
            'password'      => Hash::make('Admin@1234'),
            'userType'      => '1',
            'assigneeLevel' => '1',
        ]);

        User::factory()->create([
            'name'          => 'Super Admin',
            'userName'     => 'supperadmin',
            'email'         => 'supperadmin@suswebapp.com',
            'password'      => Hash::make('Supperadmin@1234'),
            'userType'      => '1',
            'assigneeLevel' => '1',

        ]);

        ComResponsibleSection::factory()->create([
            'id'            => 1,
            'sectionName'   => 'Hazard And Risk Section',
            'sectionCode'   => 'HRS',
            'responsibleId' => '1',
        ]);
        ComResponsibleSection::factory()->create([
            'id'            => 2,
            'sectionName'   => 'Accident Section',
            'sectionCode'   => 'As',
            'responsibleId' => '2',
        ]);
        ComResponsibleSection::factory()->create([
            'id'            => 3,
            'sectionName'   => 'Incident Section',
            'sectionCode'   => 'Is',
            'responsibleId' => '3',
        ]);
        ComResponsibleSection::factory()->create([
            'id'            => 4,
            'sectionName'   => 'Medicine Request Section',
            'sectionCode'   => 'MRS',
            'responsibleId' => '4',
        ]);
        ComResponsibleSection::factory()->create([
            'id'            => 5,
            'sectionName'   => 'Internal Audit Section',
            'sectionCode'   => 'IAS',
            'responsibleId' => '5',
        ]);
        ComResponsibleSection::factory()->create([
            'id'            => 6,
            'sectionName'   => 'External Audit Section',
            'sectionCode'   => 'EAS',
            'responsibleId' => '6',
        ]);
        ComResponsibleSection::factory()->create([
            'id'            => 7,
            'sectionName'   => 'Internal Question Section',
            'sectionCode'   => 'EQS',
            'responsibleId' => '7',
        ]);
        ComResponsibleSection::factory()->create([
            'id'            => 8,
            'sectionName'   => 'SDG Reporting Section',
            'sectionCode'   => 'SRS',
            'responsibleId' => '8',
        ]);
        ComResponsibleSection::factory()->create([
            'id'            => 9,
            'sectionName'   => 'Environment Management Section',
            'sectionCode'   => 'EMS',
            'responsibleId' => '9',
        ]);
        ComResponsibleSection::factory()->create([
            'id'            => 10,
            'sectionName'   => 'Target Setting Section',
            'sectionCode'   => 'TSS',
            'responsibleId' => '10',
        ]);
        ComResponsibleSection::factory()->create([
            'id'            => 11,
            'sectionName'   => 'Chemical Management Section',
            'sectionCode'   => 'CMS',
            'responsibleId' => '11',
        ]);
        ComResponsibleSection::factory()->create([
            'id'            => 12,
            'sectionName'   => 'Grievance Section',
            'sectionCode'   => 'GS',
            'responsibleId' => '12',
        ]);
        ComAssigneeLevel::factory()->create([
            'id'        => 1,
            'levelId'   => '1',
            'levelName' => 'Admin',
        ]);
        ComAssigneeLevel::factory()->create([
            'id'        => 2,
            'levelId'   => '2',
            'levelName' => 'Team Member',
        ]);
        ComAssigneeLevel::factory()->create([
            'id'        => 3,
            'levelId'   => '3',
            'levelName' => 'Executive',
        ]);
        ComAssigneeLevel::factory()->create([
            'id'        => 4,
            'levelId'   => '4',
            'levelName' => 'Manager ',
        ]);
        ComAssigneeLevel::factory()->create([
            'id'        => 5,
            'levelId'   => '5',
            'levelName' => 'CEO',
        ]);

        ComOrganization::factory()->create([
            'id'                      => 1,
            'organizationName'        => 'Sky School App',
            'organizationFactoryName' => 'Sky Smart Technology',
        ]);
    }
}
