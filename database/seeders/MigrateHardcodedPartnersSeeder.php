<?php

namespace Database\Seeders;

use App\Models\B2BPartner;
use App\Models\B2BPartnerSecret;
use Illuminate\Database\Seeder;

class MigrateHardcodedPartnersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder migrates the hardcoded partners from the ValidatePartner middleware
     * into the database.
     */
    public function run(): void
    {
        $hardcodedPartners = [
            [
                'name' => 'nana',
                'secret' => 'rprzGLcDDr9lTmcIXuIdkFdC5UhZO6zzRzgPYq1ySiBvp8KiuttvpUrHllE75XeB',
                'service_fees' => 9,
                'delivery_fees' => 11,
                'active' => true
            ],
            [
                'name' => 'clean life',
                'secret' => '7oKaxU0yUsy9JGvo8mX739zRgswRwrWsFYVYPDbzpG1L7W2xujYt2K5VqTGc65II',
                'service_fees' => 0,
                'delivery_fees' => 0,
                'active' => true
            ],
            [
                'name' => 'dhyafatech',
                'secret' => 'hoHYS4SGmCGUhTF2X3b1ecHzJ1L5ybK9GABdiz1utSRoFbvufC8KFTA548bf98We',
                'service_fees' => 0,
                'delivery_fees' => 0,
                'active' => true
            ],
            [
                'name' => 'source2',
                'secret' => 'QZjtCzbTS9SB431LhPrO66j9fbSMPfKPGASdxWwKCHUfPV90ARpGfthkqxo8V32x',
                'service_fees' => 0,
                'delivery_fees' => 0,
                'active' => true
            ],
        ];

        $this->command->info('Migrating hardcoded partners to database...');

        foreach ($hardcodedPartners as $partnerData) {
            // Check if partner already exists by secret
            $existingSecret = B2BPartnerSecret::where('secret', $partnerData['secret'])->first();

            if ($existingSecret) {
                $this->command->warn("Partner '{$partnerData['name']}' already exists (secret found). Skipping...");
                continue;
            }

            // Create partner
            $partner = B2BPartner::create([
                'name' => $partnerData['name'],
                'service_fees' => $partnerData['service_fees'],
                'delivery_fees' => $partnerData['delivery_fees'],
                'active' => $partnerData['active'],
            ]);

            // Create secret
            B2BPartnerSecret::create([
                'b2b_partner_id' => $partner->id,
                'secret' => $partnerData['secret'],
                'active' => true,
            ]);

            $this->command->info("✓ Created partner: {$partnerData['name']} (ID: {$partner->id})");
        }

        $this->command->info('✓ Migration completed successfully!');
    }
}
