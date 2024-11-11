<?php

namespace Database\Seeders;

use App\Models\Website;
use App\Models\Zone;
use App\Repositories\WebsiteRepository;
use App\Repositories\ZoneRepository;
use Illuminate\Database\Seeder;

class CreateDefaultWebSiteAndZone extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $repo_website = new WebsiteRepository();
        $repo_zone = new ZoneRepository();
        $input_website = [
            'name' => 'tapo_nail'
        ];
        $web = $repo_website->findOneBy($input_website);
        if(!isset($web)) {
            $input_website['status'] = Website::STATUS_ACTIVE;
            $web = $repo_website->create($input_website);
        }
        $zones = $repo_zone->findAll();
        if(count($zones) == 0) {
            $input_zones = [
                'status' => Zone::STATUS_ACTIVE,
                'website_id' => $web->id
            ];
            foreach([Zone::TYPE_BOOST, Zone::TYPE_HORIZONTAL, Zone::TYPE_POPUP] as $val) {
                $input_zones['name'] = config('enums.advertising.zone.type')[$val];
                $input_zones['type'] = $val;
                $input_zones['key'] = $val;
                $repo_zone->create($input_zones);
            }
        }
    }
}
