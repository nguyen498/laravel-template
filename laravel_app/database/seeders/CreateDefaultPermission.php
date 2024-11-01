<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Repositories\EmployeeRepository;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateDefaultPermission extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->permission();
    }

    public function permission() {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('employees')->truncate();
            DB::table('roles')->truncate();
            DB::table('role_user')->truncate();
            DB::table('permissions')->truncate();
            DB::table('permission_role')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $repo_employee = new EmployeeRepository();
            DB::beginTransaction();
            $viewEmployeePerm = Permission::create([ 'name' => 'view-employee', 'display_name' => 'View employee', 'type' => 1]);
            $createEmployeePerm = Permission::create([ 'name' => 'create-employee', 'display_name' => 'Create employee', 'type' => 1]);
            $updateEmployeePerm = Permission::create([ 'name' => 'update-employee', 'display_name' => 'Update employee', 'type' => 1]);
            $deleteEmployeePerm = Permission::create([ 'name' => 'delete-employee', 'display_name' => 'Delete employee', 'type' => 1]);
            $exportEmployeePerm = Permission::create([ 'name' => 'export-employee', 'display_name' => 'Export employee', 'type' => 1]);

            $viewRolePerm = Permission::create([ 'name' => 'view-role', 'display_name' => 'View role', 'type' => 2]);
            $createRolePerm = Permission::create([ 'name' => 'create-role', 'display_name' => 'Create role', 'type' => 2]);
            $updateRolePerm = Permission::create([ 'name' => 'update-role', 'display_name' => 'Update role', 'type' => 2]);
            $deleteRolePerm = Permission::create([ 'name' => 'delete-role', 'display_name' => 'Delete role', 'type' => 2]);

            $viewPostPerm = Permission::create([ 'name' => 'view-post', 'display_name' => 'View post', 'type' => 3]);
            $createPostPerm = Permission::create([ 'name' => 'create-post', 'display_name' => 'Create post', 'type' => 3]);
            $updatePostPerm = Permission::create([ 'name' => 'update-post', 'display_name' => 'Update post', 'type' => 3]);
            $deletePostPerm = Permission::create([ 'name' => 'delete-post', 'display_name' => 'Delete post', 'type' => 3]);

            $viewNotificationPerm = Permission::create([ 'name' => 'view-notification', 'display_name' => 'View notification', 'type' => 4]);
            $createNotificationPerm = Permission::create([ 'name' => 'create-notification', 'display_name' => 'Create notification', 'type' => 4]);
            $updateNotificationPerm = Permission::create([ 'name' => 'update-notification', 'display_name' => 'Update notification', 'type' => 4]);
            $deleteNotificationPerm = Permission::create([ 'name' => 'delete-notification', 'display_name' => 'Delete notification', 'type' => 4]);

            $viewRequestAdvertisingPerm = Permission::create([ 'name' => 'view-request-advertising', 'display_name' => 'View request advertising', 'type' => 5]);
            $createRequestAdvertisingPerm = Permission::create([ 'name' => 'create-request-advertising', 'display_name' => 'Create request advertising', 'type' => 5]);
            $updateRequestAdvertisingPerm = Permission::create([ 'name' => 'update-request-advertising', 'display_name' => 'Update request advertising', 'type' => 5]);
            $deleteRequestAdvertisingPerm = Permission::create([ 'name' => 'delete-request-advertising', 'display_name' => 'Delete request advertising', 'type' => 5]);

            $super = Role::where('name', 'superadmin')->first();
            if(!isset($super)) {
                $super = Role::create([
                    'name' => 'superadmin',
                    'display_name' => 'Super admin',
                    'type' => Role::TYPE_SUPER_ADMIN,
                    'status' => Role::STATUS_ACTIVE
                ]);
            }
            if(isset($super)) {
                $super->givePermission($viewEmployeePerm);
                $super->givePermission($createEmployeePerm);
                $super->givePermission($updateEmployeePerm);
                $super->givePermission($deleteEmployeePerm);
                $super->givePermission($exportEmployeePerm);

                $super->givePermission($viewRolePerm);
                $super->givePermission($createRolePerm);
                $super->givePermission($updateRolePerm);
                $super->givePermission($deleteRolePerm);

                $super->givePermission($viewPostPerm);
                $super->givePermission($createPostPerm);
                $super->givePermission($updatePostPerm);
                $super->givePermission($deletePostPerm);

                $super->givePermission($viewNotificationPerm);
                $super->givePermission($createNotificationPerm);
                $super->givePermission($updateNotificationPerm);
                $super->givePermission($deleteNotificationPerm);

                $super->givePermission($viewRequestAdvertisingPerm);
                $super->givePermission($createRequestAdvertisingPerm);
                $super->givePermission($updateRequestAdvertisingPerm);
                $super->givePermission($deleteRequestAdvertisingPerm);
            }


            $admin = Role::where('name', 'admin')->first();
            if(!isset($admin)) {
                $admin = Role::create([
                    'name' => 'admin',
                    'display_name' => 'Admin quản trị',
                    'type' => Role::TYPE_ADMIN,
                    'status' => Role::STATUS_ACTIVE
                ]);
            }
            if(isset($admin)) {
                $admin->givePermission($viewEmployeePerm);
                $admin->givePermission($createEmployeePerm);
                $admin->givePermission($updateEmployeePerm);
                $admin->givePermission($deleteEmployeePerm);
                $admin->givePermission($exportEmployeePerm);

                $admin->givePermission($viewRolePerm);
                $admin->givePermission($createRolePerm);
                $admin->givePermission($updateRolePerm);
                $admin->givePermission($deleteRolePerm);

                $admin->givePermission($viewPostPerm);
                $admin->givePermission($createPostPerm);
                $admin->givePermission($updatePostPerm);
                $admin->givePermission($deletePostPerm);

                $admin->givePermission($viewNotificationPerm);
                $admin->givePermission($createNotificationPerm);
                $admin->givePermission($updateNotificationPerm);
                $admin->givePermission($deleteNotificationPerm);

                $admin->givePermission($viewRequestAdvertisingPerm);
                $admin->givePermission($createRequestAdvertisingPerm);
                $admin->givePermission($updateRequestAdvertisingPerm);
                $admin->givePermission($deleteRequestAdvertisingPerm);
            }


            $pogofdev = Employee::where('username', 'pogofdev')->first();
            if(!isset($pogofdev)) {
                $pogofdev = Employee::create([
                    'fullname' => 'pogofdev',
                    'reference' => $repo_employee->getReference(),
                    'username' => 'pogofdev',
                    'email' => 'pogofdev@admin.com',
                    'password' => bcrypt('admin123'),
                    'type' => Employee::TYPE_SUPER_ADMIN,
                ]);
            }
            $pogofdev->roles()->save($super);

            $admin_employee = Employee::where('username', 'jobadmin')->first();
            if(!isset($admin_employee)) {
                $admin_employee = Employee::create([
                    'fullname' => 'Admin',
                    'reference' => $repo_employee->getReference(),
                    'username' => 'jobadmin',
                    'email' => 'jobadmin@job_posting.com.vn',
                    'password' => bcrypt('jobadmin123'),
                    'type' => Employee::TYPE_NORMAL,
                ]);
            }
            $admin_employee->roles()->save($admin);
            DB::commit();
        }
        catch (\Exception $e) {
            DB::rollBack();
            echo $e->getMessage();
        }
    }
}
