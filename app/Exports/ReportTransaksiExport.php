<?php

namespace App\Exports;

use App\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportTransaksiExport implements FromView, ShouldAutoSize
{
    protected $start;
    protected $end;

    function __construct($start, $end) {
           $this->start = $start;
           $this->end = $end;
    }

    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        if ($this->start && $this->end) {
            $startDate = strtotime($this->start);
            $endDate = strtotime($this->end);

            $order  = Order::with(['data_item.product', 'data_user.user_address'])
                    ->where('status', '2')
                    ->orWhere('status', '3')
                    ->orWhere('status', '4')
                    ->whereBetween('order_time', [date('Y-m-d H:i:s', $startDate), date('Y-m-d H:i:s', $endDate)]);
        } else {
            $order  = Order::with(['data_item.product', 'data_user.user_address'])
                    ->where('status', '2')
                    ->orWhere('status', '3')
                    ->orWhere('status', '4');
        }

        $orders = $order->get();

        return view('exports.report-sales-transaksi', [
            'orders' => $orders
        ]);
    }
}
