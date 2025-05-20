<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\detailTransaksiModel;
use App\Models\foodModel;
use App\Models\transaksiModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class detailTransaksiController extends Controller
{

    //get all data detail transaksi
    public function getAll()
    {
        $data = detailTransaksiModel::all();
        return response()->json($data);
    }

    //get data berdasarkan primay key(id) detail transaksi
    public function getDetailId($id)
    {
        $data = detailTransaksiModel::with('detailMenu')->find($id);
        return response()->json($data);
    }

    public function getDetailTransaksiId($id)
    {
        $Auth = Auth::user();
         // Mengambil detail transaksi dan data terkait berdasarkan ID transaksi
        $data = detailTransaksiModel::with(['detailTransaksi.detailPegawai', 'detailMenu'])
            ->where('id_transaksi', $id)
            ->get();

        // cek yang membuka detail transaksi adalah user yang terlibat dalam transaksi
        $isAuthorized = $data->contains(function ($item) use ($Auth) {
            return $item->detailTransaksi->id_user == $Auth->id_user;
        });

        // Cek user kasir/manajer
        if ($Auth->role == 'KASIR' || $Auth->role == 'MANAJER') {

            // jika user manajer
            if ($isAuthorized || $Auth->role == 'MANAJER') {
                return response()->json($data);

            } else {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            }

        } else {

            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }
     

    // create detail transaksi
    public function addDetailTransaksi(Request $request, $id) // INPUT id_transaksi 
    {
        $Auth = Auth::user();

        $CheckTransaction = transaksiModel::find($id);

        // Cek user role= kasir
        if ($Auth->role == "KASIR") {

            // Cek status transaksi "BELUM_BAYAR"
            if ($CheckTransaction->status == "BELUM_BAYAR") {
                if ($Auth->id_user == $CheckTransaction->id_user) {

                    // Create validator 
                    $validator = Validator::make($request->all(), [
                        'menu_items' => 'required|array',
                        'menu_items.*.id_menu' => 'required|integer',
                    ]);

                    // Cek gagal pada validator
                    if ($validator->fails()) {
                        return response()->json($validator->errors()->toJson());
                    }

                    // Menyimpan hasil setiap operasi simpan
                    $results = [];

                    // Menyimpan data menu_item yang diberikan dalam request
                    foreach ($request->menu_items as $menuItem) {

                        $CheckFood = foodModel::find($menuItem['id_menu']);

                        if ($CheckFood) {
                            // Create valiabe save
                            $save = detailTransaksiModel::create([
                                'id_transaksi' => $id, 
                                'id_menu' => $menuItem['id_menu'],
                                'harga' => $CheckFood->harga,
                            ]);

                            // memanggil array results diatas untuk diisi
                            $results[] = $save ? ['status' => true, 'id_menu' => $menuItem['id_menu'], 'message' => 'Berhasil Menambah']
                                : ['status' => false, 'id_menu' => $menuItem['id_menu'], 'message' => 'Gagal Menambah'];
                        } else {
                            //error result
                            $results[] = ['status' => false, 'id_menu' => $menuItem['id_menu'], 'message' => 'Menu not found'];
                        }
                    }

                    // hasil setelah memproses dari valiable result
                    return response()->json($results, status: 200);

                } else {
                    return response()->json(['status' => false, 'message' => 'Unauthorized'], status: 401);
                }

            } else {
                return response()->json(['status' => false, 'message' => 'Gagal, transaksi sudah lunas'], status: 500);
            }

        } else {
            return response()->json(['status' => false, 'message' => 'Hanya Kasir yang bisa menambah'], status: 500);
        }
    }


    //delete detail transaksi
    public function deleteDetailTransaksi($id)
    {
        $Auth = Auth::user();

        $CheckTransactionDetail = detailTransaksiModel::find($id);
        $CheckTransaction = transaksiModel::find($CheckTransactionDetail->id_transaksi);

        if ($Auth->role == "KASIR") {

            if ($CheckTransaction->status == "BELUM_BAYAR") {

                if ($CheckTransaction->id_user == $Auth->id_user) {

                    $delete = detailTransaksiModel::find($id)->delete();
                    return response()->json($delete);

                } else {

                    return response()->json(['status' => false, 'message' => 'Unauthorized'], status: 500);
                }

            } else {

                return response()->json(['status' => false, 'message' => 'Gagal, karena transaksi sudah lunas'], status: 500);
            }

        } else {

            return response()->json(['status' => false, 'message' => 'Hanya Kasir yang bisa menghapus'], status: 500);

        }

    }

    public function getblmbyr (){
        $Auth = Auth::user();
        $data = transaksiModel::where('status', 'BELUM_BAYAR')->get();
        if ($Auth->role == 'MANAJER') {
                return response()->json($data);

            } else {
                return response()->json(['status' => false, 'message' => 'Hanya Manajer yang  bisa melihat data'], 401);
            }
        
    }

    public function getlunas (){
        $Auth = Auth::user();
        $data = transaksiModel::where ('status','LUNAS')->get();
        if ($Auth->role == 'MANAJER') {
            return response()->json($data);

        } else {
            return response()->json(['status' => false, 'message' => 'Hanya Manajer yang  bisa melihat data'], 401);
        }
    }


}