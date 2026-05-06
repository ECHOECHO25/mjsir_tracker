<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DefaultEicSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'name'     => 'Chief Editor',
            'email'    => 'eic@mjsir.com',
            'password' => password_hash('Admin123!', PASSWORD_DEFAULT),
            'role'     => 'eic',
        ];

        // Insert default EIC account
        $this->db->table('users')->insert($data);
    }
}
