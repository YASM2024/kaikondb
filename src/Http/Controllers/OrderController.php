<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Kaikon2\Kaikondb\Models\Order;
use Illuminate\Support\Facades\Response;

class OrderController extends Controller
{
    //
    public function showMaster(){
        $orders = Order::all();
        return $orders;
    }

    public function showMasterHaveSpecies(){
        //
        $orders = Order::has('species')->get();
        return $orders;
    }
    

    public function downloadMaster(){
            
        $orders = Order::all();
        $stream = fopen('php://temp', 'w');
        $csvheader = '"id","order_ja","order"'."\n";
        fwrite($stream, $csvheader);
        
        foreach ($orders as $order) {
            $csvdata = array(
                $order->id,
                $order->order_ja,
                $order->order
            );
            fwrite($stream, "\"" . implode("\",\"", $csvdata) . "\"\n");
        }

        rewind($stream);                      //ファイルポインタを先頭に戻す
        $csv = stream_get_contents($stream);
        $csv = mb_convert_encoding($csv,'UTF-8');

        fclose($stream);

        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=orders.csv'
        );

        return Response::make($csv, 200, $headers);
    }

    public function importMaster(){
        return "importMaster";
    }
}
