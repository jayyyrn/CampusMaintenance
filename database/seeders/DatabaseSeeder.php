<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, Department, Equipment, Inventory};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $depts = [
            ['dept_name' => 'Information Technology', 'dept_code' => 'IT'],
            ['dept_name' => 'Engineering',             'dept_code' => 'ENG'],
            ['dept_name' => 'Education',               'dept_code' => 'EDU'],
            ['dept_name' => 'Business',                'dept_code' => 'BUS'],
            ['dept_name' => 'Administration',          'dept_code' => 'ADMIN'],
        ];
        foreach ($depts as $d) Department::create($d);

        $users = [
            ['username'=>'admin',        'full_name'=>'System Administrator',    'email'=>'admin@campus.edu',       'role'=>'admin',             'department_id'=>5],
            ['username'=>'teacher1',     'full_name'=>'Prof. Maria Santos',      'email'=>'msantos@campus.edu',     'role'=>'teacher',           'department_id'=>1],
            ['username'=>'teacher2',     'full_name'=>'Prof. Juan Dela Cruz',    'email'=>'jdelacruz@campus.edu',   'role'=>'teacher',           'department_id'=>2],
            ['username'=>'coordinator1', 'full_name'=>'Engr. Roberto Reyes',     'email'=>'rreyes@campus.edu',      'role'=>'coordinator',       'department_id'=>2],
            ['username'=>'tech1',        'full_name'=>'Mr. Pedro Electrician',   'email'=>'pelectric@campus.edu',   'role'=>'technician',        'department_id'=>2],
            ['username'=>'tech2',        'full_name'=>'Mr. Carlos Carpenter',   'email'=>'ccarpenter@campus.edu',  'role'=>'technician',        'department_id'=>2],
            ['username'=>'lead1',        'full_name'=>'Engr. Martin Supervisor','email'=>'msupervisor@campus.edu', 'role'=>'lead_technician',   'department_id'=>2],
            ['username'=>'inventory1',   'full_name'=>'Ms. Ana Inventory',       'email'=>'ainventory@campus.edu',  'role'=>'inventory_officer', 'department_id'=>5],
        ];
        foreach ($users as $u) {
            $u['password'] = Hash::make('password123');
            User::create($u);
        }

        Equipment::insert([
            ['asset_no'=>'AC-201', 'equipment_name'=>'Aircon Unit 2HP',   'category'=>'aircon',  'location'=>'Room 201', 'department_id'=>1, 'status'=>'operational'],
            ['asset_no'=>'PR-201', 'equipment_name'=>'Projector Epson',   'category'=>'general', 'location'=>'Room 201', 'department_id'=>1, 'status'=>'operational'],
            ['asset_no'=>'CH-105', 'equipment_name'=>'Chair (Office)',    'category'=>'general', 'location'=>'Room 105', 'department_id'=>1, 'status'=>'under_repair'],
            ['asset_no'=>'PC-LAB1','equipment_name'=>'Desktop Computer',  'category'=>'general', 'location'=>'IT Lab 1', 'department_id'=>1, 'status'=>'operational'],
        ]);

        Inventory::insert([
            ['item_code'=>'LED-9W',   'item_name'=>'LED Bulb 9W',         'category'=>'Electrical','unit'=>'pcs','qty_on_hand'=>42, 'min_stock_level'=>10,'unit_cost'=>85.00],
            ['item_code'=>'FW-001',   'item_name'=>'Faucet Washer',       'category'=>'Plumbing',  'unit'=>'pcs','qty_on_hand'=>6,  'min_stock_level'=>10,'unit_cost'=>15.00],
            ['item_code'=>'PT-WHT-1L','item_name'=>'Paint (White) 1L',    'category'=>'Carpentry', 'unit'=>'can','qty_on_hand'=>11, 'min_stock_level'=>5, 'unit_cost'=>250.00],
            ['item_code'=>'PVC-1',    'item_name'=>'PVC Pipe 1"',         'category'=>'Plumbing',  'unit'=>'pcs','qty_on_hand'=>3,  'min_stock_level'=>8, 'unit_cost'=>120.00],
            ['item_code'=>'NAIL-2',   'item_name'=>'Nails 2"',            'category'=>'Carpentry', 'unit'=>'kg', 'qty_on_hand'=>15, 'min_stock_level'=>5, 'unit_cost'=>90.00],
            ['item_code'=>'WIRE-12',  'item_name'=>'Electrical Wire #12', 'category'=>'Electrical','unit'=>'m',  'qty_on_hand'=>100,'min_stock_level'=>30,'unit_cost'=>35.00],
            ['item_code'=>'SWITCH-1', 'item_name'=>'Light Switch',        'category'=>'Electrical','unit'=>'pcs','qty_on_hand'=>20, 'min_stock_level'=>10,'unit_cost'=>65.00],
            ['item_code'=>'OUTLET-1', 'item_name'=>'Wall Outlet',         'category'=>'Electrical','unit'=>'pcs','qty_on_hand'=>18, 'min_stock_level'=>10,'unit_cost'=>95.00],
        ]);
    }
}