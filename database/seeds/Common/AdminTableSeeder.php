<?php

use App\Models\Common\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Traits\Encryptable;
use Carbon\Carbon;

class AdminTableSeeder extends Seeder
{

    use Encryptable;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($company = null)
    {
    	Schema::disableForeignKeyConstraints();

        $user = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@demo.com',
            'password' => Hash::make('123456'),
            'mobile' => '9876543210',
            'company_id' => $company,
        ]);

        $role = Role::where('name', 'ADMIN')->first();

        if($role != null) $user->assignRole($role->id);

        $current = Admin::create([
                'name' => 'Dispatcher',
                'email' => 'dispatcher@demo.com',
                'password' => Hash::make('123456'),
                'mobile' => '9876543211',
                'company_id' => $company
            ]);

        $dispatcher = Role::where('name', 'DISPATCHER')->first();

        if($dispatcher != null) $current->assignRole($dispatcher->id);

        $current = Admin::create([
                'name' => 'Dispute',
                'email' => 'dispute@demo.com',
                'password' => Hash::make('123456'),
                'mobile' => '9876543212',
                'company_id' => $company
            ]);

        $dispute = Role::where('name', 'DISPUTE')->first();

        if($dispute != null) $current->assignRole($dispute->id);


        $user_list = [
            [
                'name' => 'Daniel',
                'email' => 'brayden@demo.com',
                'password' => Hash::make('123456'),
                'mobile' => '9876543213',
                'company_id' => $company
            ],
            [
                'name' => 'James',
                'email' => 'cina@demo.com',
                'password' => Hash::make('123456'),
                'mobile' => '9876543214',
                'company_id' => $company
            ],
            [
                'name' => 'Emily',
                'email' => 'kain@demo.com',
                'password' => Hash::make('123456'),
                'mobile' => '9876543215',
                'company_id' => $company
            ],
            [
                'name' => 'Jack',
                'email' => 'rigo@demo.com',
                'password' => Hash::make('123456'),
                'mobile' => '9876543216',
                'company_id' => $company
            ]
        ];

        foreach ($user_list as $current_user) {

            $current = Admin::create($current_user);

            $dispatcher = Role::where('name', 'DISPATCHER')->first();

            if($dispatcher != null) $current->assignRole($dispatcher->id);

        }



        foreach ($user_list as $current_user) {

            $current = Admin::create($current_user);

            $dispute = Role::where('name', 'DISPUTE')->first();

            if($dispute != null) $current->assignRole($dispute->id);

        }


	    Schema::enableForeignKeyConstraints();
    }
}
