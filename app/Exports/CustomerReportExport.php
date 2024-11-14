<?php

namespace App\Exports;

use App\Models\Customer;
// use Illuminate\Contracts\View\View;
// use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;

class CustomerReportExport implements FromView
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('exports.xml', [
            'data' => $this->data
        ]);
    }
}
