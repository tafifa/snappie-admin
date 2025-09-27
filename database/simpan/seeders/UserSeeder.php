<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Create sample users
    $users = [
      [
        'name' => 'John Doe',
        'username' => 'johndoe',
        'email' => 'john@example.com',
        'total_coin' => 1500,
        'total_exp' => 2400,
        'status' => true,
        'last_login_at' => now()->subDays(1),
      ],
      [
        'name' => 'Jane Smith',
        'username' => 'janesmith',
        'email' => 'jane@example.com',
        'total_coin' => 850,
        'total_exp' => 1200,
        'status' => true,
        'last_login_at' => now()->subHours(3),
      ],
      [
        'name' => 'Bob Wilson',
        'username' => 'bobwilson',
        'email' => 'bob@example.com',
        'total_coin' => 2200,
        'total_exp' => 3800,
        'status' => true,
        'last_login_at' => now()->subMinutes(30),
      ],
      [
        'name' => 'Alice Brown',
        'username' => 'alicebrown',
        'email' => 'alice@example.com',
        'total_coin' => 450,
        'total_exp' => 650,
        'status' => false,
        'last_login_at' => now()->subWeeks(2),
      ],
    ];

    foreach ($users as $userData) {
      User::firstOrCreate(
        ['username' => $userData['username']],
        $userData
      );
    }

    $this->command->info('Users created successfully');
  }
}
