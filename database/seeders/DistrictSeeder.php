<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('shop_districts')->insert([
            ['id' => 1, 'name' => 'Bombuflat', 'state_id' => 1],
            ['id' => 2, 'name' => 'Garacharma', 'state_id' => 1],
            ['id' => 3, 'name' => 'Port Blair', 'state_id' => 1],
            ['id' => 4, 'name' => 'Rangat', 'state_id' => 1],
            ['id' => 5, 'name' => 'Addanki', 'state_id' => 2],
            ['id' => 6, 'name' => 'Adivivaram', 'state_id' => 2],
            ['id' => 7, 'name' => 'Adoni', 'state_id' => 2],
            ['id' => 8, 'name' => 'Aganampudi', 'state_id' => 2],
            ['id' => 9, 'name' => 'Ajjaram', 'state_id' => 2],
            ['id' => 10, 'name' => 'Akividu', 'state_id' => 2],
            ['id' => 11, 'name' => 'Akkarampalle', 'state_id' => 2],
            ['id' => 12, 'name' => 'Akkayapalle', 'state_id' => 2],
            ['id' => 13, 'name' => 'Akkireddipalem', 'state_id' => 2],
            ['id' => 14, 'name' => 'Alampur', 'state_id' => 2],
            ['id' => 15, 'name' => 'Amalapuram', 'state_id' => 2],
            ['id' => 16, 'name' => 'Amudalavalasa', 'state_id' => 2],
            ['id' => 17, 'name' => 'Amur', 'state_id' => 2],
            ['id' => 18, 'name' => 'Anakapalle', 'state_id' => 2],
            ['id' => 19, 'name' => 'Anantapur', 'state_id' => 2],
            ['id' => 20, 'name' => 'Andole', 'state_id' => 2],
            ['id' => 21, 'name' => 'Atmakur', 'state_id' => 2],
            ['id' => 22, 'name' => 'Attili', 'state_id' => 2],
            ['id' => 23, 'name' => 'Avanigadda', 'state_id' => 2],
            ['id' => 24, 'name' => 'Badepalli', 'state_id' => 2],
            ['id' => 25, 'name' => 'Badvel', 'state_id' => 2],
            ['id' => 26, 'name' => 'Balapur', 'state_id' => 2],
            ['id' => 27, 'name' => 'Bandarulanka', 'state_id' => 2],
            ['id' => 28, 'name' => 'Banganapalle', 'state_id' => 2],
            ['id' => 29, 'name' => 'Bapatla', 'state_id' => 2],
            ['id' => 30, 'name' => 'Bapulapadu', 'state_id' => 2],
            ['id' => 31, 'name' => 'Belampalli', 'state_id' => 2],
            ['id' => 32, 'name' => 'Bestavaripeta', 'state_id' => 2],
            ['id' => 33, 'name' => 'Betamcherla', 'state_id' => 2],
            ['id' => 34, 'name' => 'Bhattiprolu', 'state_id' => 2],
            ['id' => 35, 'name' => 'Bhimavaram', 'state_id' => 2],
            ['id' => 36, 'name' => 'Bhimunipatnam', 'state_id' => 2],
            ['id' => 37, 'name' => 'Bobbili', 'state_id' => 2],
            ['id' => 38, 'name' => 'Bombuflat', 'state_id' => 2],
            ['id' => 39, 'name' => 'Bommuru', 'state_id' => 2],
            ['id' => 40, 'name' => 'Bugganipalle', 'state_id' => 2],
            ['id' => 41, 'name' => 'Challapalle', 'state_id' => 2],
            ['id' => 42, 'name' => 'Chandur', 'state_id' => 2],
            ['id' => 43, 'name' => 'Chatakonda', 'state_id' => 2],
            ['id' => 44, 'name' => 'Chemmumiahpet', 'state_id' => 2],
            ['id' => 45, 'name' => 'Chidiga', 'state_id' => 2],
            ['id' => 46, 'name' => 'Chilakaluripet', 'state_id' => 2],
            ['id' => 47, 'name' => 'Chimakurthy', 'state_id' => 2],
            ['id' => 48, 'name' => 'Chinagadila', 'state_id' => 2],
            ['id' => 49, 'name' => 'Chinagantyada', 'state_id' => 2],
            ['id' => 50, 'name' => 'Chinnachawk', 'state_id' => 2],
            ['id' => 51, 'name' => 'Chintalavalasa', 'state_id' => 2],
            ['id' => 52, 'name' => 'Chipurupalle', 'state_id' => 2],
            ['id' => 53, 'name' => 'Chirala', 'state_id' => 2],
            ['id' => 54, 'name' => 'Chittoor', 'state_id' => 2],
            ['id' => 55, 'name' => 'Chodavaram', 'state_id' => 2],
            ['id' => 56, 'name' => 'Choutuppal', 'state_id' => 2],
            ['id' => 57, 'name' => 'Chunchupalle', 'state_id' => 2],
            ['id' => 58, 'name' => 'Cuddapah', 'state_id' => 2],
            ['id' => 59, 'name' => 'Cumbum', 'state_id' => 2],
            ['id' => 60, 'name' => 'Darnakal', 'state_id' => 2],
            ['id' => 61, 'name' => 'Dasnapur', 'state_id' => 2],
            ['id' => 62, 'name' => 'Dauleshwaram', 'state_id' => 2],
            ['id' => 63, 'name' => 'Dharmavaram', 'state_id' => 2],
            ['id' => 64, 'name' => 'Dhone', 'state_id' => 2],
            ['id' => 65, 'name' => 'Dommara Nandyal', 'state_id' => 2],
            ['id' => 66, 'name' => 'Dowlaiswaram', 'state_id' => 2],
            ['id' => 67, 'name' => 'East Godavari Dist.', 'state_id' => 2],
            ['id' => 68, 'name' => 'Eddumailaram', 'state_id' => 2],
            ['id' => 69, 'name' => 'Edulapuram', 'state_id' => 2],
            ['id' => 70, 'name' => 'Ekambara kuppam', 'state_id' => 2],
            ['id' => 71, 'name' => 'Eluru', 'state_id' => 2],
            ['id' => 72, 'name' => 'Enikapadu', 'state_id' => 2],
            ['id' => 73, 'name' => 'Fakirtakya', 'state_id' => 2],
            ['id' => 74, 'name' => 'Farrukhnagar', 'state_id' => 2],
            ['id' => 75, 'name' => 'Gaddiannaram', 'state_id' => 2],
            ['id' => 76, 'name' => 'Gajapathinagaram', 'state_id' => 2],
            ['id' => 77, 'name' => 'Gajularega', 'state_id' => 2],
            ['id' => 78, 'name' => 'Galikonda', 'state_id' => 2],
            ['id' => 79, 'name' => 'Gandhipuram', 'state_id' => 2],
            ['id' => 80, 'name' => 'Ganjam', 'state_id' => 2],
            ['id' => 81, 'name' => 'Garladinne', 'state_id' => 2],
            ['id' => 82, 'name' => 'Gata', 'state_id' => 2],
            ['id' => 83, 'name' => 'Gautami', 'state_id' => 2],
            ['id' => 84, 'name' => 'Gokavaram', 'state_id' => 2],
            ['id' => 85, 'name' => 'Gollagudem', 'state_id' => 2],
            ['id' => 86, 'name' => 'Gopalapuram', 'state_id' => 2],
            ['id' => 87, 'name' => 'Gowrampeta', 'state_id' => 2],
            ['id' => 88, 'name' => 'Gudem', 'state_id' => 2],
            ['id' => 89, 'name' => 'Guntur', 'state_id' => 2],
            ['id' => 90, 'name' => 'Gurazala', 'state_id' => 2],
            ['id' => 91, 'name' => 'Gunupur', 'state_id' => 2],
            ['id' => 92, 'name' => 'Guntakal', 'state_id' => 2],
            ['id' => 93, 'name' => 'Guntapalli', 'state_id' => 2],
            ['id' => 94, 'name' => 'Halasuru', 'state_id' => 2],
            ['id' => 95, 'name' => 'Hanuman Junction', 'state_id' => 2],
            ['id' => 96, 'name' => 'Harishchandrapuram', 'state_id' => 2],
            ['id' => 97, 'name' => 'Harvanth', 'state_id' => 2],
            ['id' => 98, 'name' => 'Haveli', 'state_id' => 2],
            ['id' => 99, 'name' => 'Hazaribagh', 'state_id' => 2],
            ['id' => 100, 'name' => 'Ichapuram', 'state_id' => 2],
            ['id' => 101, 'name' => 'Idupulapaya', 'state_id' => 2],
            ['id' => 102, 'name' => 'Indukurpet', 'state_id' => 2],
            ['id' => 103, 'name' => 'Indranagaram', 'state_id' => 2],
            ['id' => 104, 'name' => 'Injapuram', 'state_id' => 2],
            ['id' => 105, 'name' => 'Irugur', 'state_id' => 2],
            ['id' => 106, 'name' => 'Jaggampeta', 'state_id' => 2],
            ['id' => 107, 'name' => 'Jangareddygudem', 'state_id' => 2],
            ['id' => 108, 'name' => 'Javur', 'state_id' => 2],
            ['id' => 109, 'name' => 'Jorhat', 'state_id' => 2],
            ['id' => 110, 'name' => 'Junnareddy', 'state_id' => 2],
            ['id' => 111, 'name' => 'Kadapa', 'state_id' => 2],
            ['id' => 112, 'name' => 'Kakinada', 'state_id' => 2],
            ['id' => 113, 'name' => 'Kalapathar', 'state_id' => 2],
            ['id' => 114, 'name' => 'Kallepalli', 'state_id' => 2],
            ['id' => 115, 'name' => 'Kamalapuram', 'state_id' => 2],
            ['id' => 116, 'name' => 'Kanuru', 'state_id' => 2],
            ['id' => 117, 'name' => 'Karur', 'state_id' => 2],
            ['id' => 118, 'name' => 'Kavali', 'state_id' => 2],
            ['id' => 119, 'name' => 'Kondapur', 'state_id' => 2],
            ['id' => 120, 'name' => 'Korukonda', 'state_id' => 2],
            ['id' => 121, 'name' => 'Krishna Nagar', 'state_id' => 2],
            ['id' => 122, 'name' => 'Kuppam', 'state_id' => 2],
            ['id' => 123, 'name' => 'Lakshmipuram', 'state_id' => 2],
            ['id' => 124, 'name' => 'Lalacheruvu', 'state_id' => 2],
            ['id' => 125, 'name' => 'Lankapalli', 'state_id' => 2],
            ['id' => 126, 'name' => 'Lingampeta', 'state_id' => 2],
            ['id' => 127, 'name' => 'Mandapeta', 'state_id' => 2],
            ['id' => 128, 'name' => 'Malkangiri', 'state_id' => 2],
            ['id' => 129, 'name' => 'Mallela', 'state_id' => 2],
            ['id' => 130, 'name' => 'Mandalapalli', 'state_id' => 2],
            ['id' => 131, 'name' => 'Mangalagiri', 'state_id' => 2],
            ['id' => 132, 'name' => 'Marripudi', 'state_id' => 2],
            ['id' => 133, 'name' => 'Markapur', 'state_id' => 2],
            ['id' => 134, 'name' => 'Mathurapuram', 'state_id' => 2],
            ['id' => 135, 'name' => 'Medipalli', 'state_id' => 2],
            ['id' => 136, 'name' => 'Midhun', 'state_id' => 2],
            ['id' => 137, 'name' => 'Mokila', 'state_id' => 2],
            ['id' => 138, 'name' => 'Morra', 'state_id' => 2],
            ['id' => 139, 'name' => 'Mothadaka', 'state_id' => 2],
            ['id' => 140, 'name' => 'Munagala', 'state_id' => 2],
            ['id' => 141, 'name' => 'Mundlamuru', 'state_id' => 2],
            ['id' => 142, 'name' => 'Mummidivaram', 'state_id' => 2],
            ['id' => 143, 'name' => 'Murari', 'state_id' => 2],
            ['id' => 144, 'name' => 'Murtipalli', 'state_id' => 2],
            ['id' => 145, 'name' => 'Nagulavancha', 'state_id' => 2],
            ['id' => 146, 'name' => 'Nagulavaram', 'state_id' => 2],
            ['id' => 147, 'name' => 'Nandigama', 'state_id' => 2],
            ['id' => 148, 'name' => 'Nandivada', 'state_id' => 2],
            ['id' => 149, 'name' => 'Nandyal', 'state_id' => 2],
            ['id' => 150, 'name' => 'Narasapur', 'state_id' => 2],
            ['id' => 151, 'name' => 'Nellore', 'state_id' => 2],
            ['id' => 152, 'name' => 'Nidadavole', 'state_id' => 2],
            ['id' => 153, 'name' => 'Nimmakuru', 'state_id' => 2],
            ['id' => 154, 'name' => 'Nizamabad', 'state_id' => 2],
            ['id' => 155, 'name' => 'Nuzividu', 'state_id' => 2],
            ['id' => 156, 'name' => 'Ongole', 'state_id' => 2],
            ['id' => 157, 'name' => 'Orugallu', 'state_id' => 2],
            ['id' => 158, 'name' => 'Padmanabham', 'state_id' => 2],
            ['id' => 159, 'name' => 'Palakurthy', 'state_id' => 2],
            ['id' => 160, 'name' => 'Palnadu', 'state_id' => 2],
            ['id' => 161, 'name' => 'Pattikonda', 'state_id' => 2],
            ['id' => 162, 'name' => 'Penukonda', 'state_id' => 2],
            ['id' => 163, 'name' => 'Peddapuram', 'state_id' => 2],
            ['id' => 164, 'name' => 'Peddapalli', 'state_id' => 2],
            ['id' => 165, 'name' => 'Pedapudi', 'state_id' => 2],
            ['id' => 166, 'name' => 'Peddamudium', 'state_id' => 2],
            ['id' => 167, 'name' => 'Peddavur', 'state_id' => 2],
            ['id' => 168, 'name' => 'Peddaboinapalli', 'state_id' => 2],
            ['id' => 169, 'name' => 'Peddagottipadu', 'state_id' => 2],
            ['id' => 170, 'name' => 'Peddakur', 'state_id' => 2],
            ['id' => 171, 'name' => 'Peddagadda', 'state_id' => 2],
            ['id' => 172, 'name' => 'Peddarpalli', 'state_id' => 2],
            ['id' => 173, 'name' => 'Perumallapalli', 'state_id' => 2],
            ['id' => 174, 'name' => 'Peddapuram', 'state_id' => 2],
            ['id' => 175, 'name' => 'Peddagattu', 'state_id' => 2],
            ['id' => 176, 'name' => 'Penamaluru', 'state_id' => 2],
            ['id' => 177, 'name' => 'Peddamur', 'state_id' => 2],
            ['id' => 178, 'name' => 'Peddarubadu', 'state_id' => 2],
            ['id' => 179, 'name' => 'Rajahmundry', 'state_id' => 2],
            ['id' => 180, 'name' => 'Rajolubanda', 'state_id' => 2],
            ['id' => 181, 'name' => 'Rajampet', 'state_id' => 2],
            ['id' => 182, 'name' => 'Rajamahendravaram', 'state_id' => 2],
            ['id' => 183, 'name' => 'Ramachandrapuram', 'state_id' => 2],
            ['id' => 184, 'name' => 'Rangapuram', 'state_id' => 2],
            ['id' => 185, 'name' => 'Ranasthalam', 'state_id' => 2],
            ['id' => 186, 'name' => 'Rangapur', 'state_id' => 2],
            ['id' => 187, 'name' => 'Ramasamudram', 'state_id' => 2],
            ['id' => 188, 'name' => 'Ramapuram', 'state_id' => 2],
            ['id' => 189, 'name' => 'Ramachandrapuram', 'state_id' => 2],
            ['id' => 190, 'name' => 'Rangapuram', 'state_id' => 2],
            ['id' => 191, 'name' => 'Ramaswamy', 'state_id' => 2],
            ['id' => 192, 'name' => 'Rangareddy', 'state_id' => 2],
            ['id' => 193, 'name' => 'Ravi', 'state_id' => 2],
            ['id' => 194, 'name' => 'Saddened', 'state_id' => 2],
            ['id' => 195, 'name' => 'Sanivarapupeta', 'state_id' => 2],
            ['id' => 196, 'name' => 'Sattirajupeta', 'state_id' => 2],
            ['id' => 197, 'name' => 'Satyanarayana', 'state_id' => 2],
            ['id' => 198, 'name' => 'Satyavati', 'state_id' => 2],
            ['id' => 199, 'name' => 'Sohra', 'state_id' => 2],
            ['id' => 200, 'name' => 'Sundarapalli', 'state_id' => 2],
            ['id' => 201, 'name' => 'Penukonda', 'region_id' => 2],
            ['id' => 202, 'name' => 'Phirangipuram', 'region_id' => 2],
            ['id' => 203, 'name' => 'Pithapuram', 'region_id' => 2],
            ['id' => 204, 'name' => 'Ponnur', 'region_id' => 2],
            ['id' => 205, 'name' => 'Port Blair', 'region_id' => 2],
            ['id' => 206, 'name' => 'Pothinamallayyapalem', 'region_id' => 2],
            ['id' => 207, 'name' => 'Prakasam', 'region_id' => 2],
            ['id' => 208, 'name' => 'Prasadampadu', 'region_id' => 2],
            ['id' => 209, 'name' => 'Prasantinilayam', 'region_id' => 2],
            ['id' => 210, 'name' => 'Proddatur', 'region_id' => 2],
            ['id' => 211, 'name' => 'Pulivendla', 'region_id' => 2],
            ['id' => 212, 'name' => 'Punganuru', 'region_id' => 2],
            ['id' => 213, 'name' => 'Puttur', 'region_id' => 2],
            ['id' => 214, 'name' => 'Qutubullapur', 'region_id' => 2],
            ['id' => 215, 'name' => 'Rajahmundry', 'region_id' => 2],
            ['id' => 216, 'name' => 'Rajamahendri', 'region_id' => 2],
            ['id' => 217, 'name' => 'Rajampet', 'region_id' => 2],
            ['id' => 218, 'name' => 'Rajendranagar', 'region_id' => 2],
            ['id' => 219, 'name' => 'Rajoli', 'region_id' => 2],
            ['id' => 220, 'name' => 'Ramachandrapuram', 'region_id' => 2],
            ['id' => 221, 'name' => 'Ramanayyapeta', 'region_id' => 2],
            ['id' => 222, 'name' => 'Ramapuram', 'region_id' => 2],
            ['id' => 223, 'name' => 'Ramarajupalli', 'region_id' => 2],
            ['id' => 224, 'name' => 'Ramavarappadu', 'region_id' => 2],
            ['id' => 225, 'name' => 'Rameswaram', 'region_id' => 2],
            ['id' => 226, 'name' => 'Rampachodavaram', 'region_id' => 2],
            ['id' => 227, 'name' => 'Ravulapalam', 'region_id' => 2],
            ['id' => 228, 'name' => 'Rayachoti', 'region_id' => 2],
            ['id' => 229, 'name' => 'Rayadrug', 'region_id' => 2],
            ['id' => 230, 'name' => 'Razam', 'region_id' => 2],
            ['id' => 231, 'name' => 'Razole', 'region_id' => 2],
            ['id' => 232, 'name' => 'Renigunta', 'region_id' => 2],
            ['id' => 233, 'name' => 'Repalle', 'region_id' => 2],
            ['id' => 234, 'name' => 'Rishikonda', 'region_id' => 2],
            ['id' => 235, 'name' => 'Salur', 'region_id' => 2],
            ['id' => 236, 'name' => 'Samalkot', 'region_id' => 2],
            ['id' => 237, 'name' => 'Sattenapalle', 'region_id' => 2],
            ['id' => 238, 'name' => 'Seetharampuram', 'region_id' => 2],
            ['id' => 239, 'name' => 'Serilungampalle', 'region_id' => 2],
            ['id' => 240, 'name' => 'Shankarampet', 'region_id' => 2],
            ['id' => 241, 'name' => 'Shar', 'region_id' => 2],
            ['id' => 242, 'name' => 'Singarayakonda', 'region_id' => 2],
            ['id' => 243, 'name' => 'Sirpur', 'region_id' => 2],
            ['id' => 244, 'name' => 'Sirsilla', 'region_id' => 2],
            ['id' => 245, 'name' => 'Sompeta', 'region_id' => 2],
            ['id' => 246, 'name' => 'Sriharikota', 'region_id' => 2],
            ['id' => 247, 'name' => 'Srikakulam', 'region_id' => 2],
            ['id' => 248, 'name' => 'Srikalahasti', 'region_id' => 2],
            ['id' => 249, 'name' => 'Sriramnagar', 'region_id' => 2],
            ['id' => 250, 'name' => 'Sriramsagar', 'region_id' => 2],
            ['id' => 251, 'name' => 'Srisailam', 'region_id' => 2],
            ['id' => 252, 'name' => 'Srisailamgudem Devasthanam', 'region_id' => 2],
            ['id' => 253, 'name' => 'Sulurpeta', 'region_id' => 2],
            ['id' => 254, 'name' => 'Suriapet', 'region_id' => 2],
            ['id' => 255, 'name' => 'Suryaraopet', 'region_id' => 2],
            ['id' => 256, 'name' => 'Tadepalle', 'region_id' => 2],
            ['id' => 257, 'name' => 'Tadepalligudem', 'region_id' => 2],
            ['id' => 258, 'name' => 'Tadpatri', 'region_id' => 2],
            ['id' => 259, 'name' => 'Tallapalle', 'region_id' => 2],
            ['id' => 260, 'name' => 'Tanuku', 'region_id' => 2],
            ['id' => 261, 'name' => 'Tekkali', 'region_id' => 2],
            ['id' => 262, 'name' => 'Tenali', 'region_id' => 2],
            ['id' => 263, 'name' => 'Tigalapahad', 'region_id' => 2],
            ['id' => 264, 'name' => 'Tiruchanur', 'region_id' => 2],
            ['id' => 265, 'name' => 'Tirumala', 'region_id' => 2],
            ['id' => 266, 'name' => 'Tirupati', 'region_id' => 2],
            ['id' => 267, 'name' => 'Tirvuru', 'region_id' => 2],
            ['id' => 268, 'name' => 'Trimulgherry', 'region_id' => 2],
            ['id' => 269, 'name' => 'Tuni', 'region_id' => 2],
            ['id' => 270, 'name' => 'Turangi', 'region_id' => 2],
            ['id' => 271, 'name' => 'Ukkayapalli', 'region_id' => 2],
            ['id' => 272, 'name' => 'Ukkunagaram', 'region_id' => 2],
            ['id' => 273, 'name' => 'Uppal Kalan', 'region_id' => 2],
            ['id' => 274, 'name' => 'Upper Sileru', 'region_id' => 2],
            ['id' => 275, 'name' => 'Uravakonda', 'region_id' => 2],
            ['id' => 276, 'name' => 'Vadlapudi', 'region_id' => 2],
            ['id' => 277, 'name' => 'Vaparala', 'region_id' => 2],
            ['id' => 278, 'name' => 'Vemalwada', 'region_id' => 2],
            ['id' => 279, 'name' => 'Venkatagiri', 'region_id' => 2],
            ['id' => 280, 'name' => 'Venkatapuram', 'region_id' => 2],
            ['id' => 281, 'name' => 'Vepagunta', 'region_id' => 2],
            ['id' => 282, 'name' => 'Vetapalem', 'region_id' => 2],
            ['id' => 283, 'name' => 'Vijayapuri', 'region_id' => 2],
            ['id' => 284, 'name' => 'Vijayapuri South', 'region_id' => 2],
            ['id' => 285, 'name' => 'Vijayawada', 'region_id' => 2],
            ['id' => 286, 'name' => 'Vinukonda', 'region_id' => 2],
            ['id' => 287, 'name' => 'Visakhapatnam', 'region_id' => 2],
            ['id' => 288, 'name' => 'Vizianagaram', 'region_id' => 2],
            ['id' => 289, 'name' => 'Vuyyuru', 'region_id' => 2],
            ['id' => 290, 'name' => 'Wanparti', 'region_id' => 2],
            ['id' => 291, 'name' => 'West Godavari Dist.', 'region_id' => 2],
            ['id' => 292, 'name' => 'Yadagirigutta', 'region_id' => 2],
            ['id' => 293, 'name' => 'Yarada', 'region_id' => 2],
            ['id' => 294, 'name' => 'Yellamanchili', 'region_id' => 2],
            ['id' => 295, 'name' => 'Yemmiganur', 'region_id' => 2],
            ['id' => 296, 'name' => 'Yenamalakudru', 'region_id' => 2],
            ['id' => 297, 'name' => 'Yendada', 'region_id' => 2],
            ['id' => 298, 'name' => 'Yerraguntla', 'region_id' => 2],
            ['id' => 299, 'name' => 'Along', 'region_id' => 3],
            ['id' => 300, 'name' => 'Basar', 'region_id' => 3],
            ['id' => 301, 'name' => 'Bondila', 'region_id' => 3],
            ['id' => 302, 'name' => 'Changlang', 'region_id' => 3],
            ['id' => 303, 'name' => 'Daporijo', 'region_id' => 3],
            ['id' => 304, 'name' => 'Deomali', 'region_id' => 3],
            ['id' => 305, 'name' => 'Itanagar', 'region_id' => 3],
            ['id' => 306, 'name' => 'Jairampur', 'region_id' => 3],
            ['id' => 307, 'name' => 'Khonsa', 'region_id' => 3],
            ['id' => 308, 'name' => 'Naharlagun', 'region_id' => 3],
            ['id' => 309, 'name' => 'Namsai', 'region_id' => 3],
            ['id' => 310, 'name' => 'Pasighat', 'region_id' => 3],
            ['id' => 311, 'name' => 'Roing', 'region_id' => 3],
            ['id' => 312, 'name' => 'Seppa', 'region_id' => 3],
            ['id' => 313, 'name' => 'Tawang', 'region_id' => 3],
            ['id' => 314, 'name' => 'Tezu', 'region_id' => 3],
            ['id' => 315, 'name' => 'Ziro', 'region_id' => 3],
            ['id' => 316, 'name' => 'Abhayapuri', 'region_id' => 4],
            ['id' => 317, 'name' => 'Ambikapur', 'region_id' => 4],
            ['id' => 318, 'name' => 'Amguri', 'region_id' => 4],
            ['id' => 319, 'name' => 'Anand Nagar', 'region_id' => 4],
            ['id' => 320, 'name' => 'Badarpur', 'region_id' => 4],
            ['id' => 321, 'name' => 'Badarpur Railway Town', 'region_id' => 4],
            ['id' => 322, 'name' => 'Bahbari Gaon', 'region_id' => 4],
            ['id' => 323, 'name' => 'Bamun Sualkuchi', 'region_id' => 4],
            ['id' => 324, 'name' => 'Barbari', 'region_id' => 4],
            ['id' => 325, 'name' => 'Barpathar', 'region_id' => 4],
            ['id' => 326, 'name' => 'Barpeta', 'region_id' => 4],
            ['id' => 327, 'name' => 'Barpeta Road', 'region_id' => 4],
            ['id' => 328, 'name' => 'Basugaon', 'region_id' => 4],
            ['id' => 329, 'name' => 'Bihpuria', 'region_id' => 4],
            ['id' => 330, 'name' => 'Bijni', 'region_id' => 4],
            ['id' => 331, 'name' => 'Bilasipara', 'region_id' => 4],
            ['id' => 332, 'name' => 'Biswanath Chariali', 'region_id' => 4],
            ['id' => 333, 'name' => 'Bohori', 'region_id' => 4],
            ['id' => 334, 'name' => 'Bokajan', 'region_id' => 4],
            ['id' => 335, 'name' => 'Bokokhat', 'region_id' => 4],
            ['id' => 336, 'name' => 'Bongaigaon', 'region_id' => 4],
            ['id' => 337, 'name' => 'Bongaigaon Petro-chemical Town', 'region_id' => 4],
            ['id' => 338, 'name' => 'Borgolai', 'region_id' => 4],
            ['id' => 339, 'name' => 'Chabua', 'region_id' => 4],
            ['id' => 340, 'name' => 'Chandrapur Bagicha', 'region_id' => 4],
            ['id' => 341, 'name' => 'Chapar', 'region_id' => 4],
            ['id' => 342, 'name' => 'Chekonidhara', 'region_id' => 4],
            ['id' => 343, 'name' => 'Choto Haibor', 'region_id' => 4],
            ['id' => 344, 'name' => 'Dergaon', 'region_id' => 4],
            ['id' => 345, 'name' => 'Dharapur', 'region_id' => 4],
            ['id' => 346, 'name' => 'Dhekiajuli', 'region_id' => 4],
            ['id' => 347, 'name' => 'Dhemaji', 'region_id' => 4],
            ['id' => 348, 'name' => 'Dhing', 'region_id' => 4],
            ['id' => 349, 'name' => 'Dhubri', 'region_id' => 4],
            ['id' => 350, 'name' => 'Dhuburi', 'region_id' => 4],
            ['id' => 401, 'name' => 'Maibong', 'region_id' => 4],
            ['id' => 402, 'name' => 'Majgaon', 'region_id' => 4],
            ['id' => 403, 'name' => 'Makum', 'region_id' => 4],
            ['id' => 404, 'name' => 'Mangaldai', 'region_id' => 4],
            ['id' => 405, 'name' => 'Mankachar', 'region_id' => 4],
            ['id' => 406, 'name' => 'Margherita', 'region_id' => 4],
            ['id' => 407, 'name' => 'Mariani', 'region_id' => 4],
            ['id' => 408, 'name' => 'Marigaon', 'region_id' => 4],
            ['id' => 409, 'name' => 'Moran', 'region_id' => 4],
            ['id' => 410, 'name' => 'Moranhat', 'region_id' => 4],
            ['id' => 411, 'name' => 'Nagaon', 'region_id' => 4],
            ['id' => 412, 'name' => 'Naharkatia', 'region_id' => 4],
            ['id' => 413, 'name' => 'Nalbari', 'region_id' => 4],
            ['id' => 414, 'name' => 'Namrup', 'region_id' => 4],
            ['id' => 415, 'name' => 'Naubaisa Gaon', 'region_id' => 4],
            ['id' => 416, 'name' => 'Nazira', 'region_id' => 4],
            ['id' => 417, 'name' => 'New Bongaigaon Railway Colony', 'region_id' => 4],
            ['id' => 418, 'name' => 'Niz-Hajo', 'region_id' => 4],
            ['id' => 419, 'name' => 'North Guwahati', 'region_id' => 4],
            ['id' => 420, 'name' => 'Numaligarh', 'region_id' => 4],
            ['id' => 421, 'name' => 'Palasbari', 'region_id' => 4],
            ['id' => 422, 'name' => 'Panchgram', 'region_id' => 4],
            ['id' => 423, 'name' => 'Pathsala', 'region_id' => 4],
            ['id' => 424, 'name' => 'Raha', 'region_id' => 4],
            ['id' => 425, 'name' => 'Rangapara', 'region_id' => 4],
            ['id' => 426, 'name' => 'Rangia', 'region_id' => 4],
            ['id' => 427, 'name' => 'Salakati', 'region_id' => 4],
            ['id' => 428, 'name' => 'Sapatgram', 'region_id' => 4],
            ['id' => 429, 'name' => 'Sarthebari', 'region_id' => 4],
            ['id' => 430, 'name' => 'Sarupathar', 'region_id' => 4],
            ['id' => 431, 'name' => 'Sarupathar Bengali', 'region_id' => 4],
            ['id' => 432, 'name' => 'Senchoagaon', 'region_id' => 4],
            ['id' => 433, 'name' => 'Sibsagar', 'region_id' => 4],
            ['id' => 434, 'name' => 'Silapathar', 'region_id' => 4],
            ['id' => 435, 'name' => 'Silchar', 'region_id' => 4],
            ['id' => 436, 'name' => 'Silchar Part-X', 'region_id' => 4],
            ['id' => 437, 'name' => 'Sonari', 'region_id' => 4],
            ['id' => 438, 'name' => 'Sorbhog', 'region_id' => 4],
            ['id' => 439, 'name' => 'Sualkuchi', 'region_id' => 4],
            ['id' => 440, 'name' => 'Tangla', 'region_id' => 4],
            ['id' => 441, 'name' => 'Tezpur', 'region_id' => 4],
            ['id' => 442, 'name' => 'Tihu', 'region_id' => 4],
            ['id' => 443, 'name' => 'Tinsukia', 'region_id' => 4],
            ['id' => 444, 'name' => 'Titabor', 'region_id' => 4],
            ['id' => 445, 'name' => 'Udalguri', 'region_id' => 4],
            ['id' => 446, 'name' => 'Umrangso', 'region_id' => 4],
            ['id' => 447, 'name' => 'Uttar Krishnapur Part-I', 'region_id' => 4],
            ['id' => 448, 'name' => 'Amarpur', 'region_id' => 5],
            ['id' => 449, 'name' => 'Ara', 'region_id' => 5],
            ['id' => 450, 'name' => 'Araria', 'region_id' => 5],
            ['id' => 451, 'name' => 'Areraj', 'region_id' => 5],
            ['id' => 452, 'name' => 'Asarganj', 'region_id' => 5],
            ['id' => 453, 'name' => 'Aurangabad', 'region_id' => 5],
            ['id' => 454, 'name' => 'Bagaha', 'region_id' => 5],
            ['id' => 455, 'name' => 'Bahadurganj', 'region_id' => 5],
            ['id' => 456, 'name' => 'Bairgania', 'region_id' => 5],
            ['id' => 457, 'name' => 'Bakhtiyarpur', 'region_id' => 5],
            ['id' => 458, 'name' => 'Banka', 'region_id' => 5],
            ['id' => 459, 'name' => 'Banmankhi', 'region_id' => 5],
            ['id' => 460, 'name' => 'Bar Bigha', 'region_id' => 5],
            ['id' => 461, 'name' => 'Barauli', 'region_id' => 5],
            ['id' => 462, 'name' => 'Barauni Oil Township', 'region_id' => 5],
            ['id' => 463, 'name' => 'Barh', 'region_id' => 5],
            ['id' => 464, 'name' => 'Barhiya', 'region_id' => 5],
            ['id' => 465, 'name' => 'Bariapur', 'region_id' => 5],
            ['id' => 466, 'name' => 'Baruni', 'region_id' => 5],
            ['id' => 467, 'name' => 'Begusarai', 'region_id' => 5],
            ['id' => 468, 'name' => 'Behea', 'region_id' => 5],
            ['id' => 469, 'name' => 'Belsand', 'region_id' => 5],
            ['id' => 470, 'name' => 'Bettiah', 'region_id' => 5],
            ['id' => 471, 'name' => 'Bhabua', 'region_id' => 5],
            ['id' => 472, 'name' => 'Bhagalpur', 'region_id' => 5],
            ['id' => 473, 'name' => 'Bhimnagar', 'region_id' => 5],
            ['id' => 474, 'name' => 'Bhojpur', 'region_id' => 5],
            ['id' => 475, 'name' => 'Bihar', 'region_id' => 5],
            ['id' => 476, 'name' => 'Bihar Sharif', 'region_id' => 5],
            ['id' => 477, 'name' => 'Bihariganj', 'region_id' => 5],
            ['id' => 478, 'name' => 'Bikramganj', 'region_id' => 5],
            ['id' => 479, 'name' => 'Birpur', 'region_id' => 5],
            ["id" => 1843, "name" => "Adimaly", "state_id" => 19],
            ["id" => 1844, "name" => "Adoor", "state_id" => 19],
            ["id" => 1845, "name" => "Adur", "state_id" => 19],
            ["id" => 1846, "name" => "Akathiyur", "state_id" => 19],
            ["id" => 1847, "name" => "Alangad", "state_id" => 19],
            ["id" => 1848, "name" => "Alappuzha", "state_id" => 19],
            ["id" => 1849, "name" => "Aluva", "state_id" => 19],
            ["id" => 1850, "name" => "Ancharakandy", "state_id" => 19],
            ["id" => 1851, "name" => "Angamaly", "state_id" => 19],
            ["id" => 1852, "name" => "Aroor", "state_id" => 19],
            ["id" => 1853, "name" => "Arukutti", "state_id" => 19],
            ["id" => 1854, "name" => "Attingal", "state_id" => 19],
            ["id" => 1855, "name" => "Avinissery", "state_id" => 19],
            ["id" => 1856, "name" => "Azhikode North", "state_id" => 19],
            ["id" => 1857, "name" => "Azhikode South", "state_id" => 19],
            ["id" => 1858, "name" => "Azhiyur", "state_id" => 19],
            ["id" => 1859, "name" => "Balussery", "state_id" => 19],
            ["id" => 1860, "name" => "Bangramanjeshwar", "state_id" => 19],
            ["id" => 1861, "name" => "Beypur", "state_id" => 19],
            ["id" => 1862, "name" => "Brahmakulam", "state_id" => 19],
            ["id" => 1863, "name" => "Chala", "state_id" => 19],
            ["id" => 1864, "name" => "Chalakudi", "state_id" => 19],
            ["id" => 1865, "name" => "Changanacheri", "state_id" => 19],
            ["id" => 1866, "name" => "Chauwara", "state_id" => 19],
            ["id" => 1867, "name" => "Chavakkad", "state_id" => 19],
            ["id" => 1868, "name" => "Chelakkara", "state_id" => 19],
            ["id" => 1869, "name" => "Chelora", "state_id" => 19],
            ["id" => 1870, "name" => "Chendamangalam", "state_id" => 19],
            ["id" => 1871, "name" => "Chengamanad", "state_id" => 19],
            ["id" => 1872, "name" => "Chengannur", "state_id" => 19],
            ["id" => 1873, "name" => "Cheranallur", "state_id" => 19],
            ["id" => 1874, "name" => "Cheriyakadavu", "state_id" => 19],
            ["id" => 1875, "name" => "Cherthala", "state_id" => 19],
            ["id" => 1876, "name" => "Cherukunnu", "state_id" => 19],
            ["id" => 1877, "name" => "Cheruthazham", "state_id" => 19],
            ["id" => 1878, "name" => "Cheruvannur", "state_id" => 19],
            ["id" => 1879, "name" => "Cheruvattur", "state_id" => 19],
            ["id" => 1880, "name" => "Chevvur", "state_id" => 19],
            ["id" => 1881, "name" => "Chirakkal", "state_id" => 19],
            ["id" => 1882, "name" => "Chittur", "state_id" => 19],
            ["id" => 1883, "name" => "Chockli", "state_id" => 19],
            ["id" => 1884, "name" => "Churnikkara", "state_id" => 19],
            ["id" => 1885, "name" => "Dharmadam", "state_id" => 19],
            ["id" => 1886, "name" => "Edappal", "state_id" => 19],
            ["id" => 1887, "name" => "Edathala", "state_id" => 19],
            ["id" => 1888, "name" => "Elayavur", "state_id" => 19],
            ["id" => 1889, "name" => "Elur", "state_id" => 19],
            ["id" => 1890, "name" => "Eranholi", "state_id" => 19],
            ["id" => 1891, "name" => "Erattupetta", "state_id" => 19],
            ["id" => 1892, "name" => "Ernakulam", "state_id" => 19],
            ["id" => 1893, "name" => "Eruvatti", "state_id" => 19],
            ["id" => 1894, "name" => "Ettumanoor", "state_id" => 19],
            ["id" => 1895, "name" => "Feroke", "state_id" => 19],
            ["id" => 1896, "name" => "Guruvayur", "state_id" => 19],
            ["id" => 1897, "name" => "Haripad", "state_id" => 19],
            ["id" => 1898, "name" => "Hosabettu", "state_id" => 19],
            ["id" => 1899, "name" => "Idukki", "state_id" => 19],
            ["id" => 1900, "name" => "Iringaprom", "state_id" => 19],
            ["id" => 1901, "name" => "Irinjalakuda", "state_id" => 19],
            ["id" => 1902, "name" => "Iriveri", "state_id" => 19],
            ["id" => 1903, "name" => "Kadachira", "state_id" => 19],
            ["id" => 1904, "name" => "Kadalundi", "state_id" => 19],
            ["id" => 1905, "name" => "Kadamakkudy", "state_id" => 19],
            ["id" => 1906, "name" => "Kadirur", "state_id" => 19],
            ["id" => 1907, "name" => "Kadungallur", "state_id" => 19],
            ["id" => 1908, "name" => "Kakkodi", "state_id" => 19],
            ["id" => 1909, "name" => "Kalady", "state_id" => 19],
            ["id" => 1910, "name" => "Kalamassery", "state_id" => 19],
            ["id" => 1911, "name" => "Kalliasseri", "state_id" => 19],
            ["id" => 1912, "name" => "Kalpetta", "state_id" => 19],
            ["id" => 1913, "name" => "Kanhangad", "state_id" => 19],
            ["id" => 1914, "name" => "Kanhirode", "state_id" => 19],
            ["id" => 1915, "name" => "Kanjikkuzhi", "state_id" => 19],
            ["id" => 1916, "name" => "Kanjikode", "state_id" => 19],
            ["id" => 1917, "name" => "Kanjirappalli", "state_id" => 19],
            ["id" => 1918, "name" => "Kannanallur", "state_id" => 19],
            ["id" => 1919, "name" => "Kannur", "state_id" => 19],
            ["id" => 1920, "name" => "Kanjirath", "state_id" => 19],
            ["id" => 1921, "name" => "Kannankode", "state_id" => 19],
            ["id" => 1922, "name" => "Kanjiram", "state_id" => 19],
            ["id" => 1923, "name" => "Kottayam", "state_id" => 19],
            ["id" => 1924, "name" => "Kodungallur", "state_id" => 19],
            ["id" => 1925, "name" => "Kozhikode", "state_id" => 19],
            ["id" => 1926, "name" => "Kumbalanghi", "state_id" => 19],
            ["id" => 1927, "name" => "Kuttanadu", "state_id" => 19],
            ["id" => 1928, "name" => "Kundara", "state_id" => 19],
            ["id" => 1929, "name" => "Kurichy", "state_id" => 19],
            ["id" => 1930, "name" => "Kurississery", "state_id" => 19],
            ["id" => 1931, "name" => "Kuruvattur", "state_id" => 19],
            ["id" => 1932, "name" => "Kuttikkattoor", "state_id" => 19],
            ["id" => 1933, "name" => "Kottur", "state_id" => 19],
            ["id" => 1934, "name" => "Kunukkara", "state_id" => 19],
            ["id" => 1935, "name" => "Kulathur", "state_id" => 19],
            ["id" => 1936, "name" => "Kumarakom", "state_id" => 19],
            ["id" => 1937, "name" => "Kumbla", "state_id" => 19],
            ["id" => 1938, "name" => "Kuttippuram", "state_id" => 19],
            ["id" => 1939, "name" => "Kothamangalam", "state_id" => 19],
            ["id" => 1940, "name" => "Koyilandy", "state_id" => 19],
            ["id" => 1941, "name" => "Kunnamkulam", "state_id" => 19],
            ["id" => 1942, "name" => "Manalur", "state_id" => 19],
            ["id" => 1943, "name" => "Manjeri", "state_id" => 19],
            ["id" => 1944, "name" => "Mankada", "state_id" => 19],
            ["id" => 1945, "name" => "Manaloor", "state_id" => 19],
            ["id" => 1946, "name" => "Mannarkad", "state_id" => 19],
            ["id" => 1947, "name" => "Mavelikkara", "state_id" => 19],
            ["id" => 1948, "name" => "Melattur", "state_id" => 19],
            ["id" => 1949, "name" => "Muvattupuzha", "state_id" => 19],
            ["id" => 1950, "name" => "Mundakkayam", "state_id" => 19],
            ["id" => 1951, "name" => "Munakkadu", "state_id" => 19],
            ["id" => 1952, "name" => "Muthalamada", "state_id" => 19],
            ["id" => 1953, "name" => "Mylode", "state_id" => 19],
            ["id" => 1954, "name" => "Nagapattinam", "state_id" => 19],
            ["id" => 1955, "name" => "Nellikkunnu", "state_id" => 19],
            ["id" => 1956, "name" => "Nellore", "state_id" => 19],
            ["id" => 1957, "name" => "Nedumbassery", "state_id" => 19],
            ["id" => 1958, "name" => "Nellore", "state_id" => 19],
            ["id" => 1959, "name" => "Nedungadapalam", "state_id" => 19],
            ["id" => 1960, "name" => "Ollur", "state_id" => 19],
            ["id" => 1961, "name" => "Ottappalam", "state_id" => 19],
            ["id" => 1962, "name" => "Pakal", "state_id" => 19],
            ["id" => 1963, "name" => "Palakkad", "state_id" => 19],
            ["id" => 1964, "name" => "Pallur", "state_id" => 19],
            ["id" => 1965, "name" => "Pattambi", "state_id" => 19],
            ["id" => 1966, "name" => "Perumbavoor", "state_id" => 19],
            ["id" => 1967, "name" => "Perumbadi", "state_id" => 19],
            ["id" => 1968, "name" => "Perumpadappu", "state_id" => 19],
            ["id" => 1969, "name" => "Peruvallur", "state_id" => 19],
            ["id" => 1970, "name" => "Petta", "state_id" => 19],
            ["id" => 1971, "name" => "Punnappra", "state_id" => 19],
            ["id" => 1972, "name" => "Puthur", "state_id" => 19],
            ["id" => 1973, "name" => "Punnapra", "state_id" => 19],
            ["id" => 1974, "name" => "Ravananvattom", "state_id" => 19],
            ["id" => 1975, "name" => "Ranni", "state_id" => 19],
            ["id" => 1976, "name" => "Rishikesh", "state_id" => 19],
            ["id" => 1977, "name" => "Thiruvananthapuram", "state_id" => 19],
        ]);
    }
}