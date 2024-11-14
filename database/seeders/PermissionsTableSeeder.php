<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            // Roles Permissions
            ['name' => 'role-list', 'head' => 'Roles', 'permission' => 'View list of roles'],
            ['name' => 'role-create', 'head' => 'Roles', 'permission' => 'Create a new role'],
            ['name' => 'role-edit', 'head' => 'Roles', 'permission' => 'Edit existing roles'],
            ['name' => 'role-delete', 'head' => 'Roles', 'permission' => 'Delete roles'],
        
            // User Permissions
            ['name' => 'user-create', 'head' => 'Users', 'permission' => 'Create new users'],
            ['name' => 'user-list', 'head' => 'Users', 'permission' => 'View list of users'],
            ['name' => 'user-edit', 'head' => 'Users', 'permission' => 'Edit user information'],
            ['name' => 'user-delete', 'head' => 'Users', 'permission' => 'Delete users'],
        
            // Store Permissions
            ['name' => 'manage-store', 'head' => 'Store Profile', 'permission' => 'Manage store details'],
            ['name' => 'manage-store-billing', 'head' => 'Store Billing Details', 'permission' => 'Manage store billing information'],
        
            // Service Permissions
            ['name' => 'service-create', 'head' => 'Services', 'permission' => 'Create new services'],
            ['name' => 'service-list', 'head' => 'Services', 'permission' => 'View list of services'],
            ['name' => 'service-edit', 'head' => 'Services', 'permission' => 'Edit service details'],
            ['name' => 'service-delete', 'head' => 'Services', 'permission' => 'Delete services'],
        
            // Package Permissions
            ['name' => 'package-create', 'head' => 'Packages', 'permission' => 'Create new packages'],
            ['name' => 'package-list', 'head' => 'Packages', 'permission' => 'View list of packages'],
            ['name' => 'package-edit', 'head' => 'Packages', 'permission' => 'Edit package information'],
            ['name' => 'package-delete', 'head' => 'Packages', 'permission' => 'Delete packages'],
        
            // Staff Permissions
            ['name' => 'staff-create', 'head' => 'Staff', 'permission' => 'Add new staff members'],
            ['name' => 'staff-list', 'head' => 'Staff', 'permission' => 'View list of staff members'],
            ['name' => 'staff-edit', 'head' => 'Staff', 'permission' => 'Edit staff details'],
            ['name' => 'staff-delete', 'head' => 'Staff', 'permission' => 'Remove staff members'],
            ['name' => 'staff-document-create', 'head' => 'Staff', 'permission' => 'Add staff documents'],
            ['name' => 'staff-document-download', 'head' => 'Staff', 'permission' => 'Download staff documents'],
            ['name' => 'staff-document-delete', 'head' => 'Staff', 'permission' => 'Delete staff documents'],
        
            // Schedule Permissions
            ['name' => 'schedule-create', 'head' => 'Schedule', 'permission' => 'Create schedules'],
            ['name' => 'schedule-list', 'head' => 'Schedule', 'permission' => 'View schedules'],
            ['name' => 'schedule-edit', 'head' => 'Schedule', 'permission' => 'Edit schedules'],
            ['name' => 'schedule-delete', 'head' => 'Schedule', 'permission' => 'Delete schedules'],
        
            // Billing Permissions
            ['name' => 'billing-create', 'head' => 'Billing', 'permission' => 'Create billing records'],
            ['name' => 'billing-list', 'head' => 'Billing', 'permission' => 'View billing records'],
            ['name' => 'billing-edit', 'head' => 'Billing', 'permission' => 'Edit billing records'],
            ['name' => 'billing-delete', 'head' => 'Billing', 'permission' => 'Delete billing records'],
            ['name' => 'billing-download', 'head' => 'Billing', 'permission' => 'Download billing information'],
            ['name' => 'bill-overview', 'head' => 'Billing', 'permission' => 'View customer billing overview'],
            ['name' => 'refund-bill', 'head' => 'Billing', 'permission' => 'Process customer bill refunds'],
            // Cashbook Permissions
            ['name' => 'cashbook-view', 'head' => 'Cashbook', 'permission' => 'View cashbook records'],
            ['name' => 'cashbook-withdraw-cash', 'head' => 'Cashbook', 'permission' => 'Withdraw cash entries'],
            ['name' => 'cashbook-add-cash', 'head' => 'Cashbook', 'permission' => 'Add cash entries'],
        
            // Report Permissions
            ['name' => 'report-view', 'head' => 'Reports', 'permission' => 'View reports'],
        
            // Customer Permissions
            ['name' => 'customer-list', 'head' => 'Customer', 'permission' => 'View list of customers'],
            ['name' => 'customer-create', 'head' => 'Customer', 'permission' => 'Create new customers'],
            ['name' => 'customer-edit', 'head' => 'Customer', 'permission' => 'Edit customer details'],
            ['name' => 'customer-delete', 'head' => 'Customer', 'permission' => 'Delete customers'],
             // Memebership Permissions
             ['name' => 'membership-list', 'head' => 'Memebership', 'permission' => 'View list of Memeberships'],
             ['name' => 'membership-create', 'head' => 'Memebership', 'permission' => 'Create new Memeberships'],
             ['name' => 'membership-edit', 'head' => 'Memebership', 'permission' => 'Edit Memebership details'],
             ['name' => 'membership-delete', 'head' => 'Memebership', 'permission' => 'Delete Memebership'],
              // Category Permissions
            ['name' => 'category-list', 'head' => 'Category', 'permission' => 'View list of Categories'],
            ['name' => 'category-create', 'head' => 'Category', 'permission' => 'Create new Categories'],
            ['name' => 'category-edit', 'head' => 'Category', 'permission' => 'Edit Category details'],
            ['name' => 'category-delete', 'head' => 'Category', 'permission' => 'Delete Category'],
            // Product Permissions
            ['name' => 'product-list', 'head' => 'Product', 'permission' => 'View list of Products'],
            ['name' => 'product-create', 'head' => 'Product', 'permission' => 'Create new Products'],
            ['name' => 'product-edit', 'head' => 'Product', 'permission' => 'Edit Product details'],
            ['name' => 'product-delete', 'head' => 'Product', 'permission' => 'Delete Product'],
            // Stock Permissions
            ['name' => 'stock-list', 'head' => 'Stock', 'permission' => 'View list of Stocks'],
            ['name' => 'stock-create', 'head' => 'Stock', 'permission' => 'Create new Stocks'],
            ['name' => 'stock-edit', 'head' => 'Stock', 'permission' => 'Edit Stock details'],
            ['name' => 'stock-delete', 'head' => 'Stock', 'permission' => 'Delete Stock'],
            // Inventory Permissions
            ['name' => 'inventory-list', 'head' => 'Inventory', 'permission' => 'View list of Inventorys'],
            ['name' => 'inventory-create', 'head' => 'Inventory', 'permission' => 'Create new Inventorys'],
            ['name' => 'inventory-edit', 'head' => 'Inventory', 'permission' => 'Edit Inventory details'],
            ['name' => 'inventory-delete', 'head' => 'Inventory', 'permission' => 'Delete Inventory'],
             // Holiday Permissions
             ['name' => 'holiday-list', 'head' => 'Holiday', 'permission' => 'View list of Holidays'],
             ['name' => 'holiday-create', 'head' => 'Holiday', 'permission' => 'Create new Holidays'],
             ['name' => 'holiday-edit', 'head' => 'Holiday', 'permission' => 'Edit Holiday details'],
             ['name' => 'holiday-delete', 'head' => 'Holiday', 'permission' => 'Delete Holiday'],
            // Leave Permissions
            ['name' => 'leave-list', 'head' => 'Leave', 'permission' => 'View list of Leaves'],
            ['name' => 'leave-create', 'head' => 'Leave', 'permission' => 'Create new Leaves'],
            ['name' => 'leave-edit', 'head' => 'Leave', 'permission' => 'Edit Leave details'],
            ['name' => 'leave-delete', 'head' => 'Leave', 'permission' => 'Delete Leave'],
              // Attendance Permissions
            ['name' => 'attendence-list', 'head' => 'Attendence', 'permission' => 'View list of Attendences'],
            ['name' => 'attendence-create', 'head' => 'Attendence', 'permission' => 'Create new Attendences'],
            ['name' => 'attendence-edit', 'head' => 'Attendence', 'permission' => 'Edit Attendence details'],
            ['name' => 'attendence-delete', 'head' => 'Attendence', 'permission' => 'Delete Attendence'],
             // Salary Permissions
             ['name' => 'salary-list', 'head' => 'Salary', 'permission' => 'View list of Salaries'],
             ['name' => 'salary-create', 'head' => 'Salary', 'permission' => 'Create new Salaries'],
             ['name' => 'salary-edit', 'head' => 'Salary', 'permission' => 'Edit Salary details'],
             ['name' => 'salary-delete', 'head' => 'Salary', 'permission' => 'Delete Salary'],
             ['name' => 'slip-download', 'head' => 'Salary', 'permission' => 'Download Salary Slip'],
           
        ];
        foreach ($permissions as $permission) {
          Permission::updateOrCreate(['name' => $permission['name']], [
              'head' => $permission['head'],
              'permission' => $permission['permission']
          ]);
      }
      
      // Create roles and assign permissions
      $superAdminRole = Role::updateOrCreate(['name' => 'Super Admin']);
      $companyAdminRole = Role::updateOrCreate(['name' => 'Company Admin']);
      $storeManagerRole = Role::updateOrCreate(['name' => 'Store Manager']);

      $roles = [
     'Super Admin'=>Permission::all(),

      'Company Admin'=>[
          'role-list','role-list', 'role-create', 'role-edit', 'role-delete',
          'user-create', 'user-list', 'user-edit', 'user-delete',
          'manage-store', 'manage-store-billing',
          'service-create', 'service-list', 'service-edit', 'service-delete',
          'package-create', 'package-list', 'package-edit', 'package-delete',
          'staff-create', 'staff-list', 'staff-edit', 'staff-delete',
          'staff-document-create', 'staff-document-download', 'staff-document-delete',
          'schedule-create', 'schedule-list', 'schedule-edit', 'schedule-delete',
          'billing-create', 'billing-list', 'billing-edit', 'billing-delete',
          'billing-download', 'bill-overview', 'refund-bill',
          'cashbook-view', 'cashbook-withdraw-cash', 'cashbook-add-cash',
          'report-view','customer-list', 'customer-create', 'customer-edit', 'customer-delete',
          'membership-list', 'membership-create', 'membership-edit', 'membership-delete',
          'category-list', 'category-create', 'category-edit', 'category-delete',
          'product-list', 'product-create', 'product-edit', 'product-delete',
          'stock-list', 'stock-create', 'stock-edit', 'stock-delete',
          'inventory-list', 'inventory-create', 'inventory-edit', 'inventory-delete',    
          'attendence-list', 'attendence-create', 'attendence-edit', 'attendence-delete'
      ],

      // Assign permissions to Branch Manager
     'Store Manager'=>[
          
          'schedule-list',
          'schedule-create',
          'schedule-edit',
          'schedule-delete',
          'billing-list',
          'customer-list',
          'customer-create',
          'customer-edit',
          'customer-delete',
          
      ]
     ];
     foreach ($roles as $roleName => $permissions) {
      $role = Role::firstOrCreate(['name' => $roleName]);
      $role->syncPermissions($permissions); // This ensures that only the specified permissions are assigned
    }
    }
}
