<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\userModel;
use App\Models\transaksiModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class userController extends Controller
{

    //Get Data user keseluruhan
    public function getUserData()
    {

        //memanggil user yang dimaksud
        $Auth = Auth::user();

        //validasi role token adalah admin
        if ($Auth->role == "ADMIN") {

            //memanggil data yang ada pada tabel user
            $data = userModel::withTrashed()->get();
            return response()->json($data);

        } else {

            //gagal auth akan menampilkan error
            return response()->json(['status' => false, 'message' => 'Gagal, user bukan admin'], status: 500);

        }

    }

    //Get data berdasarkan primary key(id)
    public function getUserDataId($id)
    {

        $Auth = Auth::user();

        if ($Auth->role == "ADMIN") {
            //Get data user sesuai dengan (id) yang diinputkan
            $data = userModel::find($id);
            return response()->json($data);
        } else {

            return response()->json(['status' => false, 'message' => 'Gagal, user bukan admin'], status: 500);

        }

    }

    //Update user 
    public function updateUserData(Request $request, $id)
    {
        // autentifikasi user yang dimaksud
        $Auth = Auth::user();

        // kondisi cek username jika ada username yang sama 
        if (userModel::where('username', $request->username)->exists()) {

            return response()->json(['status' => false, 'message' => 'Username already taken'], 422);

        }

        // mengubah data user sesuai id yang dimasukkan tadi  
        $validator = Validator::make($request->all(), [
            'username' => 'nullable|String|max:100',
            'nama_user' => 'nullable|String|max:100',
            'role' => 'nullable|String',
        ]);

        // Cek validator error
        if ($validator->fails()) {

            return response()->json($validator->errors()->toJson());

        }

        // Cek user mengupdate data dengan data yang sudah diinput diatas
        if ($Auth->role == "ADMIN" && $Auth->id_user == $id) {

            // Initialize an array to hold the update data
            $updateData = [
                'username' => $request->get('username') ?? userModel::select('username')->find($id)->username,
                'nama_user' => $request->get('nama_user') ?? userModel::select('nama_user')->find($id)->nama_user,
                'role' => "ADMIN",
            ];

            //Update user data based on primary key
            $save = userModel::find($id)->update($updateData);

            if ($save) {

                return response()->json(['status' => true, 'message' => 'Sukses memperbarui data'], 200);

            } else {

                return response()->json(['status' => false, 'message' => 'Gagal memperbarui'], 500);

            }
            //konsisi lain
        } else if ($Auth->role == "ADMIN") {



            // array untuk menyimpan update data
            $updateData = [
                'username' => $request->get('username') ?? userModel::select('username')->find($id)->username,
                'nama_user' => $request->get('nama_user') ?? userModel::select('nama_user')->find($id)->nama_user,
                'role' => $request->get('role') ?? userModel::select('role')->find($id)->role,
               
            ];

            //Update user berdasarkan primary key (menyimpan)
            $save = userModel::find($id)->update($updateData);

            if ($save) {

                return response()->json(['status' => true, 'message' => 'Sukses memperbarui data'], 200);

            } else {

                return response()->json(['status' => false, 'message' => 'Gagal memperbaruidata;('], 500);

            }

        } else {

            // kondisi error jika user bukan admin
            return response()->json(['error' => 'Unauthorized'], 403);

        }

        // cek update berhasil 
        if ($update) {

            return response()->json(['success' => 'Data user berhasil diperbarui']);

        } else {

            return response()->json(['error' => 'Data user tidak dapat diperbarui'], 500);

        }

    }

    // deleteuser
    public function deleteUser($id)
    {
        $Auth = Auth::user();

        if ($Auth->role == "ADMIN") {

           
            if ($Auth->id_user == $id) {

                return response()->json(['status' => false, 'message' => 'Gagal, tidak bisa menghapus user yang sedang login'], 400);

            } else {

                // Cek status user yang terhubung transaksi (harus lunas semua)
                $unpaidTransactions = transaksiModel::where('id_user', $id)
                    ->where('status', '!=', 'LUNAS')
                    ->exists();

                if ($unpaidTransactions) {
                    return response()->json(['status' => false, 'message' => 'Gagal, user masih memiliki transaksi yang belum dibayar'], 400);
                }

                $delete = userModel::withTrashed()->find($id);

                if ($delete->trashed()) {

                    //Force delete
                    $delete->forceDelete();
                    return response()->json(['status' => true, 'message' => 'User berhasil dihapus']);
                }

                // output menghapus data berdasarkan (id)
                $delete->delete();
                return response()->json(['status' => true, 'message' => 'User berhasil dihapus']);

            }
        } else {

            // kondisi lain
            return response()->json(['status' => false, 'message' => 'Gagal, coba lagi'], 403);

        }

    }

}