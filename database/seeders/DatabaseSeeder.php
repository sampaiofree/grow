<?php

namespace Database\Seeders;

use App\Models\DoppusProdutor;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'sampaio.free@gmail.com'],
            [
                'name' => 'Sampaio',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        DoppusProdutor::updateOrCreate(
            [
                'customer_email' => $user->email,
                'items_code' => '85054878',
            ],
            [
                'user_id' => $user->id,
                'customer_name' => $user->name ?? 'Sampaio',
                'status_code' => 'approved',
                'status_message' => 'Assinatura ativa',
                'status_registration_date' => now(),
            ]
        );
    }
}
