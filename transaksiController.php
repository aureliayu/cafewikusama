<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\transaksiModel;
use App\Models\tableModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class transaksiController extends Controller
{

    //get semua data transaksi
    public function getTransaksi()
    {
        $Auth = Auth::user();

        if ($Auth->role === "MANAJER") {

            $data = transaksiModel::with([
                'detailPegawai',
                'detailMeja',
                'detailTransaksi.detailMenu',
            ])->withTrashed()->get();

            // Validasi token user manajer
            return response()->json($data);

        } else {

            //kondisi error karena tidak ada 
            return response()->json(['status' => false, 'message' => 'Hanya Manajer yang bisa mengakses'], status: 500);

        }

    }

    
    //Get(id)
    public function getTransaksiId($id)
    {
        $Auth = Auth::user();

        $data = transaksiModel::with([
            'detailPegawai',
            'detailMeja',
            'detailTransaksi.detailMenu',
        ])->find($id);

        //Cek token roken yang dimasukan adalah manajer
        if ($Auth->id_user == $data->id_user || $Auth->role == "MANAJER") {

            //cek json output dari get id 
            return response()->json($data);

        } else {

            //validasi error
            return response()->json(['status' => false, 'message' => 'Unauthorized'], status: 500);

        }

    }

    //tambah transaksi
    public function addTransaksi(Request $request)
    {
        $Auth = Auth::user();

        //menyediakan request dari parameter
        $check = tableModel::find($request->get('id_meja'));

      
        if ($Auth->role == "KASIR") {

            if ($check->status == "DIGUNAKAN") {

           
                return response()->json(['status' => false, 'message' => 'Meja sudah digunakan'], status: 500);

            } else {
                //membuat validator/validasi
                $validator = Validator::make($request->all(), [
                    'id_meja' => 'required|Integer',
                    'nama_pelanggan'
                ]);

                //validasi token dadn status data
                if ($validator->fails()) {
                    return response()->json($validator->errors()->toJson());

                }

                //membuat data inputan
                $save = transaksiModel::create([
                    'id_user' => $Auth->id_user,
                    'id_meja' => $request->get('id_meja'),
                    'nama_pelanggan' => $request->get('nama_pelanggan'),
                    'status' => 'BELUM_BAYAR',
                    'tanggal_transaksi' => now(),
                ]);


                //Cek input tidak ada yang error
                if ($save) {
                    //mengubah status meja menjadi digunakan
                    tableModel::find($request->id_meja)->update([
                        'status' => 'DIGUNAKAN',
                    ]);

                    // json berhasil menambahkan data
                    return response()->json($save);

                } else {

                    return response()->json(['status' => false, 'message' => 'Gagal menambah'], status: 500);

                }
            }

        } else {

            //validasi kesalahan token
            return response()->json(['status' => false, 'message' => 'Hanya Kasir yang bisa menambah'], status: 500);

        }

    }

    //update transaksi(id)
    public function updateTransaksi($id)
    {
        $Auth = Auth::user();
        $check = transaksiModel::find($id);

        if ($Auth->role == "KASIR") {

            if ($Auth->id_user == $check->id_user) {

                //update digunakan untuk mengubah status transaksi menjadi "LUNAS"
                $update = transaksiModel::where('id_transaksi', $id)->update([
                    'status' => 'LUNAS',
                ]);

                //Cek jika sudah terupdate
                if ($update) {
                    //mengubah status meja menjadi KOSONG sehingga dapat digunakan lagi
                    tableModel::find($check->id_meja)->update([
                        'status' => 'KOSONG',
                    ]);

                    return response()->json(['status' => true, 'message' => 'Sukses membayar'], status: 200);

                } else {
                    return response()->json(['status' => false, 'message' => 'Gagal membayar'], status: 500);

                }

            } else {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], status: 500);

            }

        } else {

            //error auth/jwt token
            return response()->json(['status' => false, 'message' => 'Hanya Kasir yang bisa memperbarui'], status: 500);

        }
    }
    
    //Delete Transaksi
    public function deleteTransaksi($id)
    {

        //Get user
        $Auth = Auth::user();
        $data = transaksiModel::find($id);

        if ($Auth->role == "KASIR") {

           
            if ($Auth->id_user == $data->id_user) {

                //Menghapus data bersadarkan id yang diinputkan pada url
                $data->delete();
                return response()->json(['status' => true, 'message' => 'Sukses menghapus data'], status: 200);

            } else {

                return response()->json(['status' => false, 'message' => 'Unauthorized'], status: 500);

            }


        } else {
            return response()->json(['status' => false, 'message' => 'Hanya Kasir yang bisa menghapus'], status: 500);

        }

    }

    public function pembayaranlunasId($id)
    {
        $Auth = Auth::user();
        if ($data = transaksiModel::where ('status','LUNAS')->get()->find($id)){

        }else {
         return response()->json(['status' => false, 'message' => 'Id Transaksi anda belum bayar'], status: 500);
        }
         
        //Cek token roken yang dimasukan adalah manajer
        if ($Auth->id_user == $data->id_user || $Auth->role == "MANAJER") {

            //cek json output dari get id 
            return response()->json($data);

        } else {

            //validasi error
            return response()->json(['status' => false, 'message' => 'Unauthorized'], status: 500);

        }

    }
    public function belumbayarId($id)
    {
     $Auth = Auth::user();
       if ($data = transaksiModel::where ('status','BELUM_BAYAR')->get()->find($id)){
       } else {
        return response()->json(['status' => false, 'message' => 'Id Transaksi anda sudah Lunas'], status: 500);
       }
       if ($Auth->id_user == $data->id_user || $Auth->role == "MANAJER") {

        //cek json output dari get id 
        return response()->json($data);

    } else {

        //validasi error
        return response()->json(['status' => false, 'message' => 'Unauthorized'], status: 500);

    }
        
    }


}