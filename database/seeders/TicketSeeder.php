<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\User;
use App\Models\IssueCategory;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::take(5)->get();
        $categories = IssueCategory::all();

        if ($users->isEmpty() || $categories->isEmpty()) {
            return;
        }

        $statuses = ['open', 'pending', 'closed'];
        $descriptions = [
            'I am having trouble with the payment process. The app keeps showing an error when I try to pay.',
            'My order was delivered to the wrong address. Please help me resolve this issue.',
            'The app is crashing every time I try to place an order. This is very frustrating.',
            'I want to cancel my subscription but I cannot find the option in the app.',
            'The delivery time was much longer than expected. I was told it would be delivered in 2 hours but it took 5 hours.',
            'I received damaged items. The clothes were not properly cleaned.',
            'I cannot log into my account. It says my password is incorrect.',
            'The app is very slow and takes a long time to load.',
            'I was charged twice for the same order. Please refund the duplicate charge.',
            'The driver was very rude and unprofessional during delivery.'
        ];

        for ($i = 0; $i < 20; $i++) {
            Ticket::create([
                'issue_category_id' => $categories->random()->id,
                'user_id' => $users->random()->id,
                'status' => $statuses[array_rand($statuses)],
                'description' => $descriptions[array_rand($descriptions)],
                'opened_at' => now()->subDays(rand(1, 30))
            ]);
        }
    }
}
