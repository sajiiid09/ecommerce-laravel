<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\UserAddress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $districts = [
            'Bagerhat',
            'Bandarban',
            'Barguna',
            'Barishal',
            'Bhola',
            'Bogura',
            'Brahmanbaria',
            'Chandpur',
            'Chapainawabganj',
            'Chattogram',
            'Chuadanga',
            "Cox's Bazar",
            'Cumilla',
            'Dhaka',
            'Dinajpur',
            'Faridpur',
            'Feni',
            'Gaibandha',
            'Gazipur',
            'Gopalganj',
            'Habiganj',
            'Jamalpur',
            'Jashore',
            'Jhalakathi',
            'Jhenaidah',
            'Joypurhat',
            'Khagrachhari',
            'Khulna',
            'Kishoreganj',
            'Kurigram',
            'Kushtia',
            'Lakshmipur',
            'Lalmonirhat',
            'Madaripur',
            'Magura',
            'Manikganj',
            'Meherpur',
            'Moulvibazar',
            'Munshiganj',
            'Mymensingh',
            'Naogaon',
            'Narail',
            'Narayanganj',
            'Narsingdi',
            'Natore',
            'Netrokona',
            'Nilphamari',
            'Noakhali',
            'Pabna',
            'Panchagarh',
            'Patuakhali',
            'Pirojpur',
            'Rajbari',
            'Rajshahi',
            'Rangamati',
            'Rangpur',
            'Satkhira',
            'Shariatpur',
            'Sherpur',
            'Sirajganj',
            'Sunamganj',
            'Sylhet',
            'Tangail',
            'Thakurgaon',
        ];

        foreach ($districts as $sortOrder => $name) {
            District::query()->updateOrCreate(
                ['name' => $name],
                [
                    'delivery_fee_minor' => $name === 'Dhaka' ? 8000 : 12000,
                    'sort_order' => $sortOrder,
                ],
            );
        }

        $districtIds = District::query()->get()->mapWithKeys(fn (District $district): array => [
            Str::lower(trim($district->name)) => $district->id,
        ]);

        UserAddress::query()
            ->whereNull('district_id')
            ->whereNotNull('district')
            ->get()
            ->each(function (UserAddress $address) use ($districtIds): void {
                $districtId = $districtIds->get(Str::lower($address->districtName()));
                if ($districtId !== null) {
                    $address->forceFill(['district_id' => $districtId])->saveQuietly();
                }
            });
    }
}
