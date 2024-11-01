<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;
use App\Utils\StringHelpers;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProcessDemoCrm extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $account = Account::create(['name' => 'Acme Corporation']);
        $dict_code = [];
        $random = null;
        while(!isset($random)) {
            $ran = StringHelpers::generateRandomNumber(10);
            if(!isset($dict_code[$ran])) {
                $dict_code[$ran] = $ran;
                $random = $ran;
            }
        }
        User::factory()->create([
            'reference' => $random,
            'account_id' => $account->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@example.com',
            'password' => 'secret',
            'owner' => true,
        ]);
        for ($i = 0; $i < 5; $i++) {
            $random = null;
            while(!isset($random)) {
                $ran = StringHelpers::generateRandomNumber(10);
                if(!isset($dict_code[$ran])) {
                    $dict_code[$ran] = $ran;
                    $random = $ran;
                }
            }
            User::factory()->create([
                'reference' => $random,
                'account_id' => $account->id
            ]);
        }


        $organizations = Organization::factory(100)
            ->create(['account_id' => $account->id]);

        Contact::factory(100)
            ->create(['account_id' => $account->id])
            ->each(function ($contact) use ($organizations) {
                $contact->update(['organization_id' => $organizations->random()->id]);
            });
    }
}
