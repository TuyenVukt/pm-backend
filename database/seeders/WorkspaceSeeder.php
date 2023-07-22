<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class WorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('workspace')->insert(
            [
                'name' =>'WORKSPACE 1',
                'organization_name'=>'Company 1',
                'domain'=>'www.company1.com',
                'avatar'=>'public/images/avatars/default.png',
                'secret_code'=>'12345678',
                'secret_key'=>'$2y$10$s3tXVu1.E8XYdSNIbCD1weONI.yNRAWAGxxayzt7kcWiRgz.DtMES',
                'description'=>'Mô tả công ty ở đây',
                'workspace_admin_id'=>1,
            ]
            );
    }
}
