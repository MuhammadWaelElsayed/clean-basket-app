<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\ExternalClient;

class ExternalClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // أنشئ secret عشوائي (plain)
        $plainSecret = Str::random(40);

        // أنشئ client_id عشوائي (UUID)
        $clientId = (string) Str::uuid();

        $client = ExternalClient::create([
            'name' => 'Test Partner',
            'external_number' => 'EXT-0001',
            'client_id' => $clientId,
            'client_secret_hash' => Hash::make($plainSecret),
            'is_active' => true,
            'token_ttl' => 30, // مدة صلاحية التوكن بالدقائق
        ]);

        // اطبع الـ client_id والـ secret مرة واحدة فقط
        $this->command->info('External Client Created:');
        $this->command->info('Name: ' . $client->name);
        $this->command->info('Client ID: ' . $clientId);
        $this->command->info('Client Secret (save this!): ' . $plainSecret);
    }
}
