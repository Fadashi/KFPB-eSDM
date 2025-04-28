<?php

namespace Database\Seeders;

use App\Models\Province;
use App\Models\City;
use App\Models\District;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data Provinsi
        $provinces = [
            ['name' => 'DKI Jakarta'],
            ['name' => 'Jawa Barat'],
            ['name' => 'Jawa Tengah'],
            ['name' => 'Jawa Timur'],
            ['name' => 'Banten'],
            ['name' => 'DI Yogyakarta'],
            ['name' => 'Sumatera Utara'],
            ['name' => 'Sumatera Barat'],
            ['name' => 'Sumatera Selatan'],
            ['name' => 'Bali'],
        ];

        // Simpan data provinsi
        foreach ($provinces as $province) {
            Province::firstOrCreate(['name' => $province['name']]);
        }

        // Data Kota/Kabupaten untuk setiap provinsi
        $cities = [
            // DKI Jakarta (ID: 1)
            ['province_id' => 1, 'name' => 'Jakarta Pusat', 'type' => 'Kota'],
            ['province_id' => 1, 'name' => 'Jakarta Utara', 'type' => 'Kota'],
            ['province_id' => 1, 'name' => 'Jakarta Timur', 'type' => 'Kota'],
            ['province_id' => 1, 'name' => 'Jakarta Selatan', 'type' => 'Kota'],
            ['province_id' => 1, 'name' => 'Jakarta Barat', 'type' => 'Kota'],
            ['province_id' => 1, 'name' => 'Kepulauan Seribu', 'type' => 'Kabupaten'],

            // Jawa Barat (ID: 2)
            ['province_id' => 2, 'name' => 'Bandung', 'type' => 'Kota'],
            ['province_id' => 2, 'name' => 'Bekasi', 'type' => 'Kota'],
            ['province_id' => 2, 'name' => 'Bogor', 'type' => 'Kota'],
            ['province_id' => 2, 'name' => 'Depok', 'type' => 'Kota'],
            ['province_id' => 2, 'name' => 'Cimahi', 'type' => 'Kota'],

            // Jawa Tengah (ID: 3)
            ['province_id' => 3, 'name' => 'Semarang', 'type' => 'Kota'],
            ['province_id' => 3, 'name' => 'Solo', 'type' => 'Kota'],
            ['province_id' => 3, 'name' => 'Magelang', 'type' => 'Kota'],
            ['province_id' => 3, 'name' => 'Pekalongan', 'type' => 'Kota'],
            ['province_id' => 3, 'name' => 'Salatiga', 'type' => 'Kota'],

            // Jawa Timur (ID: 4)
            ['province_id' => 4, 'name' => 'Surabaya', 'type' => 'Kota'],
            ['province_id' => 4, 'name' => 'Malang', 'type' => 'Kota'],
            ['province_id' => 4, 'name' => 'Batu', 'type' => 'Kota'],
            ['province_id' => 4, 'name' => 'Kediri', 'type' => 'Kota'],
            ['province_id' => 4, 'name' => 'Mojokerto', 'type' => 'Kota'],

            // Banten (ID: 5)
            ['province_id' => 5, 'name' => 'Serang', 'type' => 'Kota'],
            ['province_id' => 5, 'name' => 'Tangerang', 'type' => 'Kota'],
            ['province_id' => 5, 'name' => 'Tangerang Selatan', 'type' => 'Kota'],
            ['province_id' => 5, 'name' => 'Cilegon', 'type' => 'Kota'],
        ];

        // Simpan data kota/kabupaten
        foreach ($cities as $city) {
            City::firstOrCreate(
                ['province_id' => $city['province_id'], 'name' => $city['name']],
                ['type' => $city['type']]
            );
        }

        // Data Kecamatan untuk beberapa kota
        $districts = [
            // Jakarta Selatan (ID: 4)
            ['city_id' => 4, 'name' => 'Kebayoran Baru'],
            ['city_id' => 4, 'name' => 'Kebayoran Lama'],
            ['city_id' => 4, 'name' => 'Pancoran'],
            ['city_id' => 4, 'name' => 'Cilandak'],
            ['city_id' => 4, 'name' => 'Jagakarsa'],

            // Bandung (ID: 7)
            ['city_id' => 7, 'name' => 'Bandung Wetan'],
            ['city_id' => 7, 'name' => 'Coblong'],
            ['city_id' => 7, 'name' => 'Sumur Bandung'],
            ['city_id' => 7, 'name' => 'Cibeunying Kaler'],
            ['city_id' => 7, 'name' => 'Cibeunying Kidul'],

            // Semarang (ID: 12)
            ['city_id' => 12, 'name' => 'Semarang Tengah'],
            ['city_id' => 12, 'name' => 'Semarang Utara'],
            ['city_id' => 12, 'name' => 'Semarang Timur'],
            ['city_id' => 12, 'name' => 'Semarang Selatan'],
            ['city_id' => 12, 'name' => 'Candisari'],

            // Surabaya (ID: 17)
            ['city_id' => 17, 'name' => 'Genteng'],
            ['city_id' => 17, 'name' => 'Tegalsari'],
            ['city_id' => 17, 'name' => 'Bubutan'],
            ['city_id' => 17, 'name' => 'Simokerto'],
            ['city_id' => 17, 'name' => 'Pabean Cantian'],
        ];

        // Simpan data kecamatan
        foreach ($districts as $district) {
            District::firstOrCreate(
                ['city_id' => $district['city_id'], 'name' => $district['name']]
            );
        }
    }
} 