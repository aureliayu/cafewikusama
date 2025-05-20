<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\userModel;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    // daftar akun user
    public function Register(Request $request)
    {
        // Cek username yang diinputkan (tidak boleh sama dengan yang sudah ada)
        if (userModel::where('username', $request->username)->exists()) {
         
            return response()->json(['status' => false, 'message' => 'Username already taken'], 422);
        }

        // validasi tambah akun user yang wajib diisi/required
        $validator = Validator::make($request->all(), [
            'nama_user' => 'required|string|max:100',
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:100',
            'role' => 'required',
        ]);

        //kondisi error 
        if ($validator->fails()) {
            
            return response()->json($validator->errors()->toJson(), 400);
        }
        // Menyimpan data user 
        $save = userModel::create([
            'nama_user' => $request->get('nama_user'),
            'username' => $request->get('username'),
            'password' => $request->get('password'),
            'role' => $request->get('role'),
        ]);

        //kondisi hasil input
        if ($save) {

            return response()->json(['status' => true, 'message' => 'Berhasil Menambahkan Akun'], 200);
        
        }

        return response()->json(['status' => false, 'message' => 'Maaf Anda Gagal Menambahkan Akun '], 500);
    
    }


    //memasukkan akun yang sebelumnya sudah terdaftar (login)
    public function Login(Request $request)
    {

        // login hanya membutuhkan username dan password
        $credentials = $request->only('username', 'password');
        try {
            //(if) : berhasil autentifikasi jwt dan menampilkan token
            if (!$token = Auth::guard('user_model')->attempt($credentials)) {
                //tidak berhasil autentifikasi
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            // jwt error tidak dapat membuat token
        } catch (JWTException $e) {
            return response()->json(['error' => 'Gaada tokennya jwtmu error tuh!'], 500);
        }

        // output yang muncul if auth success
        $user = Auth::guard('user_model')->user();

        return response()->json([
            'user' => $request->username,
            'role' => $user->role,
            'token' => $token,
        ]);

    }

    //Logout 
    public function Logout()
    {
        try {
            // valiidasi token jwt yang sebelumnya sudah dibuat
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'message' => 'Berhasil Log out',
            ]);
        } catch (JWTException $e) {
            // kondisi error jika token tidak terverifikasi
            return response()->json(['error' => 'coba lagi:('], 500);
        }

    }


}