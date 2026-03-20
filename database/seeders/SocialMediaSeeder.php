<?php

namespace Database\Seeders;

use App\Models\SocialMedia;
use Illuminate\Database\Seeder;

class SocialMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * CRITICAL: "Referral" MUST be at position 7 (ID 7) because the
     * CreateOrdersWhenBookingCreated listener hardcodes social_media_id => 7.
     */
    public function run(): void
    {
        // Order matters! Referral must be ID 7
        $socialMediaSources = [
            ['name' => 'Unknown', 'is_hidden' => false],           // ID 1
            ['name' => 'Walk-in', 'is_hidden' => false],           // ID 2
            ['name' => 'Instagram', 'is_hidden' => false],         // ID 3
            ['name' => 'Facebook', 'is_hidden' => false],          // ID 4
            ['name' => 'Google', 'is_hidden' => false],            // ID 5
            ['name' => 'Returning Customer', 'is_hidden' => false], // ID 6
            ['name' => 'Referral', 'is_hidden' => false],          // ID 7 - REQUIRED
        ];

        foreach ($socialMediaSources as $source) {
            SocialMedia::create($source);
        }
    }
}
