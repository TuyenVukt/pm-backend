<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $users = [
        //     [
        //         'id' => 1,
        //         'name' =>'ADMIN 1',
        //         'email'=>'admin1@gmail.com',
        //         'password'=>Hash::make('12345678'),
        //         'avatar'=>'public/images/avatars/default.png',
        //         'is_active'=>1,
        //         'role'=>1,
        //         'workspace'=>0,
        //     ],
        //     [
        //         'id' => 2,
        //         'name' =>'ADMIN 2',
        //         'email'=>'admin2@gmail.com',
        //         'password'=>Hash::make('12345678'),
        //         'avatar'=>'public/images/avatars/default.png',
        //         'is_active'=>1,
        //         'role'=>1,
        //         'workspace'=>0,
        //     ],


        // ];
        $time = Carbon::now()->format('Y-m-d H:i:s');
        
        DB::table('users')->insert(
            [
                'name' =>'ADMIN 1',
                'email'=>'admin1@gmail.com',
                'password'=>Hash::make('12345678'),
                'avatar'=>'public/images/avatars/default.png',
                'is_active'=>1,
                'email_verified_at'=>$time,
                'role'=>1,
                'workspace_id'=>0,
            ],
            [
                'name' =>'ADMIN 2',
                'email'=>'admin2@gmail.com',
                'password'=>Hash::make('12345678'),
                'avatar'=>'public/images/avatars/default.png',
                'is_active'=>1,
                'email_verified_at'=>$time,
                'role'=>1,
                'workspace_id'=>0,
            ]
        );

    }
}
