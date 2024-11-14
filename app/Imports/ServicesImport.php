<?php

namespace App\Imports;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\GstTaxPercentage;
use App\Models\Hours;
use App\Helpers\CustomHelper;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class ServicesImport implements ToCollection, WithHeadingRow, SkipsOnFailure
{
    use Importable, SkipsFailures;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        Validator::make($rows->toArray(), 
        [
            '*.name' => 'required',
            // '*.email' => 'email|unique:customers,email',
        ],
        [
            '*.name.required' => 'Customer name is required',
            '*.email.unique' => 'Duplicate Email id. Customer email must be unique',
            '*.email.required' => 'Customer email is required',
            '*.email.email' => 'Invalid email',
        ])->validate();
 
       foreach ($rows as $row) {

            $service_category_id=NULL;
            if($row['service_category_id']!=''){
                // $service_category = ServiceCategory::whereRaw('LOWER(name) = ?', [ strtolower($row['service_category_id']) ])->first();
                $service_category = ServiceCategory::firstOrCreate(['shop_id' => SHOP_ID, 'name' => $row['service_category_id']]);
                if($service_category){
                    $service_category_id = $service_category->id;
                }
            }

            $hours_id=NULL;
            if($row['hours_id']!=''){
                $hours = Hours::firstOrCreate(['value' => $row['hours_id']], ['name' => $row['hours_id']. ' mns'] );
                if($hours){
                    $hours_id = $hours->id;
                }
            }

            $gst_tax=NULL;
            if($row['gst_tax']!=''){
                $gst = GstTaxPercentage::firstOrCreate(['percentage' => $row['gst_tax']]);
                if($hours){
                    $gst_tax = $gst->id;
                }
            }

            $lead_before=NULL;
            if($row['lead_before']!=''){
                $leadBefore = Hours::firstOrCreate(['value' => $row['lead_before']], ['name' => $row['lead_before']. ' mns'] );

                if($leadBefore){
                    $lead_before = $leadBefore->id;
                }
            }

            $lead_after=NULL;
            if($row['lead_after']!=''){
                $leadAfter = Hours::firstOrNew(['value' => $row['lead_after']], ['name' => $row['lead_after']. ' mns', 'status' => 1] );
                if($leadAfter){
                    $lead_after = $leadAfter->id;
                }
            }

            $tax_included   = (Str::of($row['tax_included'])->trim() == 'yes')?1:0;
            $hsn_code       = ($row['hsn_code'] != '') ? $row['hsn_code']:NULL;

            Service::create([
                'shop_id' => SHOP_ID,
                'name' => $row['name'],
                'service_category_id' => $service_category_id,
                'slug' => $row['name'],
                'hours_id' => $hours_id,
                'gst_tax' => $gst_tax,
                'tax_included' => $tax_included,
                'price' => $row['price'],
                'hsn_code' => $hsn_code,
                'lead_before' => $lead_before,
                'lead_after' => $lead_after,

            ]);
       }        
    }

    /**
     * @param Failure[] $failures
     */
    public function onFailure(Failure ...$failures)
    {
        // Wishing you a lifetime of love and happiness --  the failures how you'd like.
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    // public function model(array $row)
    // {
    //     return new Customer([
    //         'shop_id'   => SHOP_ID,
    //         'name'      => $row['name'],
    //         'email'     => $row['email'], 
    //         'mobile'    => $row['mobile'], 
    //         'gender'    => $row['gender'], 
    //         'dob'       => $row['dob'], 
    //     ]);
    // }
}

