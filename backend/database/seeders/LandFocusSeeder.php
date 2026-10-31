<?php

namespace Database\Seeders;

use App\Models\CalendarEvent;
use App\Models\Property;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LandFocusSeeder extends Seeder
{
    public function run(): void
    {
        $properties = [
            [
                'title' => 'Kiwanja cha Ardhi - Mlandizi',
                'description' => 'Kiwanja cha ardhi kilichopimwa vizuri katika eneo la Disunyara na Makazi Mapya, Mlandizi. Kina hati halali na lina uwezo wa ujenzi wa nyumba ya makazi.',
                'price' => 7200000,
                'location' => 'Disunyara na Makazi Mapya, Mlandizi Kibaha',
                'property_type' => 'land',
                'status' => 'available',
                'area_sqm' => 600,
                'featured' => true,
                'images' => ['/images/sample-land.jpg'],
            ],
            [
                'title' => 'Shamba la Kilimo - Fukayosi Bagamoyo',
                'description' => 'Shamba lenye rutuba kwa kilimo cha mazao mbalimbali. Lina hifadhi maji , lenye kustawisha mazao takribani saba na uhakika wa barabara ya kufikia.',
                'price' => 2000000,
                'location' => 'Fukayosi Bagamoyo',
                'property_type' => 'farm',
                'status' => 'available',
                'area_sqm' => 4046,
                'featured' => true,
                'images' => ['/images/sample-farm.jpg'],
            ],
            
        ];

        foreach ($properties as $data) {
            Property::create(array_merge(['id' => (string) Str::uuid()], $data));
        }

        CalendarEvent::create([
            'id' => (string) Str::uuid(),
            'title' => 'Warsha ya Uwekezaji wa Ardhi',
            'description' => 'Jiunge nasi kwa warsha ya bure kuhusu jinsi ya kuwekeza kwa usalama katika ardhi Tanzania.',
            'event_type' => 'announcement',
            'start_at' => now()->addDays(14),
            'location' => 'Ofisi ya LandFocus, Goba',
            'is_official' => true,
        ]);
    }
}
