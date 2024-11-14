<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Str;
use App\Helpers\FunctionHelper;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
// use Maatwebsite\Excel\Concerns\SkipsFailures;


class CustomersImport implements ToCollection, WithHeadingRow
{
    use Importable;
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
            $gender = ($row['gender'] != '') ? $row['gender']:NULL;
            $dob    = ($row['dob'] != '') ? date("Y-m-d", strtotime($row['dob'])) : NULL;
            Customer::create([
                'shop_id' => SHOP_ID,
                'customer_code' => FunctionHelper::generateCustomerCode(),
                'name' => $row['name'],
                'email' => $row['email'],
                'mobile' => $row['mobile'],
                'gender' => $gender,
                'dob' => $dob,
                'customer_code' => FunctionHelper::generateCustomerCode(),
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
