<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\transaksiModel;
use Illuminate\Support\Facades\Log;

class pdfController extends Controller
{
    public function getPdf($id)
    {
        //memanggil transaksimodel untuk mengakses data 
        $transaction = transaksiModel::with([
            'detailPegawai',
            'detailMeja',
            'detailTransaksi.detailMenu',
        ])->find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }
        //menjumlahkan total harga 
        $totalHarga = $transaction->detailTransaksi->sum('harga');

        // mengelompokkan sesuai item (id_menu)
        $groupedItems = $transaction->detailTransaksi->groupBy('id_menu')->map(function ($items) {
            $firstItem = $items->first();
            return [
                'nama_menu' => $firstItem->detailMenu->nama_menu,
                'harga' => $firstItem->harga,
                //menghitung jumlah item (id_menu)menggunkan count
                'quantity' => $items->count(),
            ];
        });

        //array data(menyiapkan data pdf)
        $data = [
            'title' => 'Wikusama Cafe',
            'date' => date('m/d/Y'),
            'transaction' => $transaction,
            'totalHarga' => $totalHarga,
            'groupedItems' => $groupedItems,
        ];
        //proses pembuatan file pdf dengan memanggil array yang tadi dibuat dan views/tampilan
        try {
            $pdf = Pdf::loadView('invoice.generate-invoice-pdf', $data);
            return $pdf->download('invoice.pdf');

        } catch (\Exception $e) {
            Log::error('PDF generation error: ' . $e->getMessage());
            return response()->json(['message' => 'Error generating PDF'], 500);
        }
    }
}