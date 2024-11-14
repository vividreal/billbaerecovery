<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimezoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('timezone')->insert([
            ['zone_id' => 1, 'country_code' => 'AD', 'zone_name' => 'Europe/Andorra'],
            ['zone_id' => 2, 'country_code' => 'AE', 'zone_name' => 'Asia/Dubai'],
            ['zone_id' => 3, 'country_code' => 'AF', 'zone_name' => 'Asia/Kabul'],
            ['zone_id' => 4, 'country_code' => 'AG', 'zone_name' => 'America/Antigua'],
            ['zone_id' => 5, 'country_code' => 'AI', 'zone_name' => 'America/Anguilla'],
            ['zone_id' => 6, 'country_code' => 'AL', 'zone_name' => 'Europe/Tirane'],
            ['zone_id' => 7, 'country_code' => 'AM', 'zone_name' => 'Asia/Yerevan'],
            ['zone_id' => 8, 'country_code' => 'AO', 'zone_name' => 'Africa/Luanda'],
            ['zone_id' => 9, 'country_code' => 'AQ', 'zone_name' => 'Antarctica/McMurdo'],
            ['zone_id' => 10, 'country_code' => 'AR', 'zone_name' => 'America/Argentina/Buenos_Aires'],
            ['zone_id' => 11, 'country_code' => 'AS', 'zone_name' => 'Pacific/Pago_Pago'],
            ['zone_id' => 12, 'country_code' => 'AT', 'zone_name' => 'Europe/Vienna'],
            ['zone_id' => 13, 'country_code' => 'AU', 'zone_name' => 'Australia/Sydney'],
            ['zone_id' => 14, 'country_code' => 'AW', 'zone_name' => 'America/Aruba'],
            ['zone_id' => 15, 'country_code' => 'AZ', 'zone_name' => 'Asia/Baku'],
            ['zone_id' => 16, 'country_code' => 'BA', 'zone_name' => 'Europe/Sarajevo'],
            ['zone_id' => 17, 'country_code' => 'BB', 'zone_name' => 'America/Barbados'],
            ['zone_id' => 18, 'country_code' => 'BD', 'zone_name' => 'Asia/Dhaka'],
            ['zone_id' => 19, 'country_code' => 'BE', 'zone_name' => 'Europe/Brussels'],
            ['zone_id' => 20, 'country_code' => 'BF', 'zone_name' => 'Africa/Ouagadougou'],
            ['zone_id' => 21, 'country_code' => 'BG', 'zone_name' => 'Europe/Sofia'],
            ['zone_id' => 22, 'country_code' => 'BH', 'zone_name' => 'Asia/Bahrain'],
            ['zone_id' => 23, 'country_code' => 'BI', 'zone_name' => 'Africa/Bujumbura'],
            ['zone_id' => 24, 'country_code' => 'BJ', 'zone_name' => 'Africa/Porto-Novo'],
            ['zone_id' => 25, 'country_code' => 'BL', 'zone_name' => 'America/St_Barthelemy'],
            ['zone_id' => 26, 'country_code' => 'BM', 'zone_name' => 'Atlantic/Bermuda'],
            ['zone_id' => 27, 'country_code' => 'BN', 'zone_name' => 'Asia/Brunei'],
            ['zone_id' => 28, 'country_code' => 'BO', 'zone_name' => 'America/La_Paz'],
            ['zone_id' => 29, 'country_code' => 'BR', 'zone_name' => 'America/Sao_Paulo'],
            ['zone_id' => 30, 'country_code' => 'BS', 'zone_name' => 'America/Nassau'],
            ['zone_id' => 31, 'country_code' => 'BT', 'zone_name' => 'Asia/Thimphu'],
            ['zone_id' => 32, 'country_code' => 'BW', 'zone_name' => 'Africa/Gaborone'],
            ['zone_id' => 33, 'country_code' => 'BY', 'zone_name' => 'Europe/Minsk'],
            ['zone_id' => 34, 'country_code' => 'BZ', 'zone_name' => 'America/Belize'],
            ['zone_id' => 35, 'country_code' => 'CA', 'zone_name' => 'America/Toronto'],
            ['zone_id' => 36, 'country_code' => 'CD', 'zone_name' => 'Africa/Kinshasa'],
            ['zone_id' => 37, 'country_code' => 'CF', 'zone_name' => 'Africa/Bangui'],
            ['zone_id' => 38, 'country_code' => 'CG', 'zone_name' => 'Africa/Brazzaville'],
            ['zone_id' => 39, 'country_code' => 'CH', 'zone_name' => 'Europe/Zurich'],
            ['zone_id' => 40, 'country_code' => 'CI', 'zone_name' => 'Africa/Abidjan'],
            ['zone_id' => 41, 'country_code' => 'CK', 'zone_name' => 'Pacific/Rarotonga'],
            ['zone_id' => 42, 'country_code' => 'CL', 'zone_name' => 'America/Santiago'],
            ['zone_id' => 43, 'country_code' => 'CM', 'zone_name' => 'Africa/Douala'],
            ['zone_id' => 44, 'country_code' => 'CN', 'zone_name' => 'Asia/Shanghai'],
            ['zone_id' => 45, 'country_code' => 'CO', 'zone_name' => 'America/Bogota'],
            ['zone_id' => 46, 'country_code' => 'CR', 'zone_name' => 'America/Costa_Rica'],
            ['zone_id' => 47, 'country_code' => 'CU', 'zone_name' => 'America/Havana'],
            ['zone_id' => 48, 'country_code' => 'CV', 'zone_name' => 'Atlantic/Cape_Verde'],
            ['zone_id' => 49, 'country_code' => 'CW', 'zone_name' => 'America/Curacao'],
            ['zone_id' => 50, 'country_code' => 'CY', 'zone_name' => 'Asia/Nicosia'],
            ['zone_id' => 51, 'country_code' => 'CZ', 'zone_name' => 'Europe/Prague'],
            ['zone_id' => 52, 'country_code' => 'DE', 'zone_name' => 'Europe/Berlin'],
            ['zone_id' => 53, 'country_code' => 'DJ', 'zone_name' => 'Africa/Djibouti'],
            ['zone_id' => 54, 'country_code' => 'DK', 'zone_name' => 'Europe/Copenhagen'],
            ['zone_id' => 55, 'country_code' => 'DM', 'zone_name' => 'America/Dominica'],
            ['zone_id' => 56, 'country_code' => 'DO', 'zone_name' => 'America/Santo_Domingo'],
            ['zone_id' => 57, 'country_code' => 'DZ', 'zone_name' => 'Africa/Algiers'],
            ['zone_id' => 58, 'country_code' => 'EC', 'zone_name' => 'America/Guayaquil'],
            ['zone_id' => 59, 'country_code' => 'EE', 'zone_name' => 'Europe/Tallinn'],
            ['zone_id' => 60, 'country_code' => 'EG', 'zone_name' => 'Africa/Cairo'],
            ['zone_id' => 61, 'country_code' => 'EH', 'zone_name' => 'Africa/El_Aaiun'],
            ['zone_id' => 62, 'country_code' => 'ER', 'zone_name' => 'Africa/Asmara'],
            ['zone_id' => 63, 'country_code' => 'ES', 'zone_name' => 'Europe/Madrid'],
            ['zone_id' => 64, 'country_code' => 'ET', 'zone_name' => 'Africa/Addis_Ababa'],
            ['zone_id' => 65, 'country_code' => 'FI', 'zone_name' => 'Europe/Helsinki'],
            ['zone_id' => 66, 'country_code' => 'FJ', 'zone_name' => 'Pacific/Fiji'],
            ['zone_id' => 67, 'country_code' => 'FM', 'zone_name' => 'Pacific/Pohnpei'],
            ['zone_id' => 68, 'country_code' => 'FO', 'zone_name' => 'Atlantic/Faroe'],
            ['zone_id' => 69, 'country_code' => 'FR', 'zone_name' => 'Europe/Paris'],
            ['zone_id' => 70, 'country_code' => 'GA', 'zone_name' => 'Africa/Libreville'],
            ['zone_id' => 71, 'country_code' => 'GB', 'zone_name' => 'Europe/London'],
            ['zone_id' => 72, 'country_code' => 'GD', 'zone_name' => 'America/Grenada'],
            ['zone_id' => 73, 'country_code' => 'GE', 'zone_name' => 'Asia/Tbilisi'],
            ['zone_id' => 74, 'country_code' => 'GF', 'zone_name' => 'America/Cayenne'],
            ['zone_id' => 75, 'country_code' => 'GH', 'zone_name' => 'Africa/Accra'],
            ['zone_id' => 76, 'country_code' => 'GI', 'zone_name' => 'Europe/Gibraltar'],
            ['zone_id' => 77, 'country_code' => 'GL', 'zone_name' => 'America/Godthab'],
            ['zone_id' => 78, 'country_code' => 'GM', 'zone_name' => 'Africa/Banjul'],
            ['zone_id' => 79, 'country_code' => 'GN', 'zone_name' => 'Africa/Conakry'],
            ['zone_id' => 80, 'country_code' => 'GP', 'zone_name' => 'America/Guadeloupe'],
            ['zone_id' => 81, 'country_code' => 'GQ', 'zone_name' => 'Africa/Malabo'],
            ['zone_id' => 82, 'country_code' => 'GR', 'zone_name' => 'Europe/Athens'],
            ['zone_id' => 83, 'country_code' => 'GT', 'zone_name' => 'America/Guatemala'],
            ['zone_id' => 84, 'country_code' => 'GU', 'zone_name' => 'Pacific/Guam'],
            ['zone_id' => 85, 'country_code' => 'GW', 'zone_name' => 'Africa/Bissau'],
            ['zone_id' => 86, 'country_code' => 'GY', 'zone_name' => 'America/Guyana'],
            ['zone_id' => 87, 'country_code' => 'HK', 'zone_name' => 'Asia/Hong_Kong'],
            ['zone_id' => 88, 'country_code' => 'HN', 'zone_name' => 'America/Tegucigalpa'],
            ['zone_id' => 89, 'country_code' => 'HR', 'zone_name' => 'Europe/Zagreb'],
            ['zone_id' => 90, 'country_code' => 'HT', 'zone_name' => 'America/Port-au-Prince'],
            ['zone_id' => 91, 'country_code' => 'HU', 'zone_name' => 'Europe/Budapest'],
            ['zone_id' => 92, 'country_code' => 'ID', 'zone_name' => 'Asia/Jakarta'],
            ['zone_id' => 93, 'country_code' => 'IE', 'zone_name' => 'Europe/Dublin'],
            ['zone_id' => 94, 'country_code' => 'IL', 'zone_name' => 'Asia/Jerusalem'],
            ['zone_id' => 95, 'country_code' => 'IN', 'zone_name' => 'Asia/Kolkata'],
            ['zone_id' => 96, 'country_code' => 'IQ', 'zone_name' => 'Asia/Baghdad'],
            ['zone_id' => 97, 'country_code' => 'IR', 'zone_name' => 'Asia/Tehran'],
            ['zone_id' => 98, 'country_code' => 'IS', 'zone_name' => 'Atlantic/Reykjavik'],
            ['zone_id' => 99, 'country_code' => 'IT', 'zone_name' => 'Europe/Rome'],
            ['zone_id' => 100, 'country_code' => 'JM', 'zone_name' => 'America/Jamaica'],
            // Additional entries continue here...
        ]);
    }
}
