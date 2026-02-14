<?php
// app/Http/Controllers/PDFController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;
class PDFController extends Controller
{

    public static function createPDF($order)
    {
     
        try {
            //code...
            $lang="en";
            // $order=Order::with(['user','orderItems','deliveryAddress'])->first();
            $statuses=config('order_status');
            // Render the Blade view and capture the HTML content
            $options = new Options();
            $options->set('defaultFont', 'sans-serif'); 
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('debugKeepTemp', true);
            $options->set('chroot', public_path());

            $dompdf = new Dompdf($options);
            
            // Load HTML content
            $htmlContent = view('livewire.admin.orders.pdf-slip',compact('lang','order','statuses'))->render();
            $dompdf->loadHtml($htmlContent);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // $dompdf->stream("codexworld", array("Attachment" => 0));
            // Output the PDF to a file
            $output = $dompdf->output();
            file_put_contents(storage_path('app/public/invoices/invoice-'.$order->id.'.pdf'), $output);

            return true;
        } catch (Exception $ex) {
            Log::info('Error in PDFContoller'. $ex->getMessage());
        }
    }

 
}
