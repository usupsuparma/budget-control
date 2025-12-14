<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AreaSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $data = [
            ['continent_province' => 'Asia', 'country_city' => 'Afghanistan', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Albania', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Algeria', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Andorra', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Angola', 'status' => 1],
            ['continent_province' => 'South America', 'country_city' => 'Argentina', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Armenia', 'status' => 1],
            ['continent_province' => 'Oceania', 'country_city' => 'Australia', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Austria', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Azerbaijan', 'status' => 1],
            ['continent_province' => 'North America', 'country_city' => 'Bahamas', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Bahrain', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Bangladesh', 'status' => 1],
            ['continent_province' => 'North America', 'country_city' => 'Barbados', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Belarus', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Belgium', 'status' => 1],
            ['continent_province' => 'North America', 'country_city' => 'Belize', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Benin', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Bhutan', 'status' => 1],
            ['continent_province' => 'South America', 'country_city' => 'Bolivia', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Bosnia & Herzegovina', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Botswana', 'status' => 1],
            ['continent_province' => 'South America', 'country_city' => 'Brazil', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Brunei', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Bulgaria', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Burkina Faso', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Burundi', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Cambodia', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Cameroon', 'status' => 1],
            ['continent_province' => 'North America', 'country_city' => 'Canada', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Cape Verde', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Central African Republic', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Chad', 'status' => 1],
            ['continent_province' => 'South America', 'country_city' => 'Chile', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'China', 'status' => 1],
            ['continent_province' => 'South America', 'country_city' => 'Colombia', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Comoros', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Congo', 'status' => 1],
            ['continent_province' => 'North America', 'country_city' => 'Costa Rica', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Croatia', 'status' => 1],
            ['continent_province' => 'North America', 'country_city' => 'Cuba', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Cyprus', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Czech Republic', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Denmark', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Djibouti', 'status' => 1],
            ['continent_province' => 'North America', 'country_city' => 'Dominica', 'status' => 1],
            ['continent_province' => 'North America', 'country_city' => 'Dominican Republic', 'status' => 1],
            ['continent_province' => 'South America', 'country_city' => 'Ecuador', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Egypt', 'status' => 1],
            ['continent_province' => 'North America', 'country_city' => 'El Salvador', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Equatorial Guinea', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Eritrea', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Estonia', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Eswatini', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Ethiopia', 'status' => 1],
            ['continent_province' => 'Oceania', 'country_city' => 'Fiji', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Finland', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'France', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Gabon', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Gambia', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Georgia', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Germany', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Ghana', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Greece', 'status' => 1],
            ['continent_province' => 'North America', 'country_city' => 'Grenada', 'status' => 1],
            ['continent_province' => 'North America', 'country_city' => 'Guatemala', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Guinea', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Guinea-Bissau', 'status' => 1],
            ['continent_province' => 'South America', 'country_city' => 'Guyana', 'status' => 1],
            ['continent_province' => 'North America', 'country_city' => 'Haiti', 'status' => 1],
            ['continent_province' => 'North America', 'country_city' => 'Honduras', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Hungary', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Iceland', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'India', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Indonesia', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Iran', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Iraq', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Ireland', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Israel', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Italy', 'status' => 1],
            ['continent_province' => 'North America', 'country_city' => 'Jamaica', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Japan', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Jordan', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Kazakhstan', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Kenya', 'status' => 1],
            ['continent_province' => 'Oceania', 'country_city' => 'Kiribati', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Kuwait', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Kyrgyzstan', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Laos', 'status' => 1],
            ['continent_province' => 'Europe', 'country_city' => 'Latvia', 'status' => 1],
            ['continent_province' => 'Asia', 'country_city' => 'Lebanon', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Lesotho', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Liberia', 'status' => 1],
            ['continent_province' => 'Africa', 'country_city' => 'Libya', 'status' => 1],

            // INDONESIA REGION
            ['continent_province' => 'DKI Jakarta', 'country_city' => 'Jakarta Pusat', 'status' => 1],
            ['continent_province' => 'DKI Jakarta', 'country_city' => 'Jakarta Barat', 'status' => 1],
            ['continent_province' => 'DKI Jakarta', 'country_city' => 'Jakarta Timur', 'status' => 1],
            ['continent_province' => 'DKI Jakarta', 'country_city' => 'Jakarta Utara', 'status' => 1],
            ['continent_province' => 'DKI Jakarta', 'country_city' => 'Jakarta Selatan', 'status' => 1],

            ['continent_province' => 'Jawa Barat', 'country_city' => 'Bandung', 'status' => 1],
            ['continent_province' => 'Jawa Barat', 'country_city' => 'Bekasi', 'status' => 1],
            ['continent_province' => 'Jawa Barat', 'country_city' => 'Depok', 'status' => 1],
            ['continent_province' => 'Jawa Barat', 'country_city' => 'Bogor', 'status' => 1],
            ['continent_province' => 'Jawa Barat', 'country_city' => 'Cimahi', 'status' => 1],

            ['continent_province' => 'Jawa Tengah', 'country_city' => 'Semarang', 'status' => 1],
            ['continent_province' => 'Jawa Tengah', 'country_city' => 'Surakarta', 'status' => 1],
            ['continent_province' => 'Jawa Tengah', 'country_city' => 'Tegal', 'status' => 1],

            ['continent_province' => 'Jawa Timur', 'country_city' => 'Surabaya', 'status' => 1],
            ['continent_province' => 'Jawa Timur', 'country_city' => 'Malang', 'status' => 1],
            ['continent_province' => 'Jawa Timur', 'country_city' => 'Madiun', 'status' => 1],

            ['continent_province' => 'Banten', 'country_city' => 'Tangerang', 'status' => 1],
            ['continent_province' => 'Banten', 'country_city' => 'Cilegon', 'status' => 1],
            ['continent_province' => 'Banten', 'country_city' => 'Serang', 'status' => 1],

            ['continent_province' => 'Yogyakarta', 'country_city' => 'Yogyakarta', 'status' => 1],

            ['continent_province' => 'Bali', 'country_city' => 'Denpasar', 'status' => 1],

            ['continent_province' => 'Sumatera Utara', 'country_city' => 'Medan', 'status' => 1],
            ['continent_province' => 'Sumatera Barat', 'country_city' => 'Padang', 'status' => 1],

            ['continent_province' => 'Riau', 'country_city' => 'Pekanbaru', 'status' => 1],
            ['continent_province' => 'Jambi', 'country_city' => 'Jambi', 'status' => 1],
            ['continent_province' => 'Lampung', 'country_city' => 'Bandar Lampung', 'status' => 1],

            ['continent_province' => 'Kalimantan Barat', 'country_city' => 'Pontianak', 'status' => 1],
            ['continent_province' => 'Kalimantan Timur', 'country_city' => 'Balikpapan', 'status' => 1],
            ['continent_province' => 'Kalimantan Selatan', 'country_city' => 'Banjarmasin', 'status' => 1],

            ['continent_province' => 'Sulawesi Selatan', 'country_city' => 'Makassar', 'status' => 1],
            ['continent_province' => 'Sulawesi Utara', 'country_city' => 'Manado', 'status' => 1],

            ['continent_province' => 'NTB', 'country_city' => 'Mataram', 'status' => 1],
            ['continent_province' => 'NTT', 'country_city' => 'Kupang', 'status' => 1],
            ['continent_province' => 'Papua', 'country_city' => 'Jayapura', 'status' => 1],
        ];

        // Tambahkan timestamp
        foreach ($data as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        Area::insert($data);
    }
}
