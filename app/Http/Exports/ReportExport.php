<?php

namespace App\Exports;

use App\Models\Billing;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Carbon;
use DB;

class ReportExport implements FromQuery
{
    use Exportable;

    public function __construct($date_from, $date_to,)
    {
        $this->date_from    = $date_from;
        $this->date_to      = $date_to;
    }


    public function query()
    {
        $from       = Carbon\Carbon::parse($this->date_from)->startOfDay();  
        $to         = Carbon\Carbon::parse($this->date_to)->endOfDay();
        return Billing::query()->select( DB::raw("DATE_FORMAT(created_at, '%d %M') as day"), DB::raw("SUM(amount) as amount"), 'id', 'payment_status', 'billing_code', 'billed_date', 'checkin_time', 'checkout_time', 'customer_id')
                        ->where('shop_id', SHOP_ID)->groupBy('billings.id')
                        ->whereBetween('created_at', [$from, $to])
                        ->orderBy('created_at', 'DESC');
    }
}
