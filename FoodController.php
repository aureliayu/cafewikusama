<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\foodModel;
// use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FoodController extends Controller
{

    public function getFood()
    {
        $food = foodModel::all();
        return response()->json($food);

    }

    public function getFoodId($id)
    {

        $food = foodModel::find($id);
        return response()->json($food);

    }

    // create food/menu baru
    public function addFood(Request $request)
    {

        $Auth = Auth::user();
        if ($Auth->role == "ADMIN") {

            //validator input data
            $validator = Validator::make($request->all(), [
                'nama_menu' => 'required|String|max:100',
                'jenis' => 'required',
                'deskripsi' => 'required|String',
                'gambar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'harga' => 'required|Integer',
            ]);

            //Cek validator gagal
            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson());
            }

            // request upload gambar
            if ($request->hasFile('gambar')) {
                $image = $request->file('gambar');
                $imageName = $image->getClientOriginalName();
                $imagePath = $image->storeAs('images/menu', $imageName, 'public');
            }

            //varibel penyimpanan data
            $save = foodModel::create([
                'nama_menu' => $request->get('nama_menu'),
                'jenis' => $request->get('jenis'),
                'deskripsi' => $request->get('deskripsi'),
                'gambar' => $imagePath,
                'harga' => $request->get('harga'),
            ]);

            //cek berhasil menambahkan/tidak
            if ($save) {
                return response()->json(['status' => true, 'message' => 'Sukses menambah'], status: 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Gagal menambah'], status: 500);

            }

        } else {
            return response()->json(['status' => false, 'message' => 'Hanya Admin yang bisa menambah'], status: 500);
        }

    }

    // update food
    public function updateFood(Request $request, $id)
    {
        
        $Auth = Auth::user();

        if ($Auth->role == "ADMIN") {

            // validator input data 
            $validator = Validator::make($request->all(), [
                'nama_menu' => 'nullable|string|max:100',
                'jenis' => 'nullable',
                'deskripsi' => 'nullable|string',
                'harga' => 'nullable|integer',
                'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Cek validator diatas
            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson());
            }

            // array for update data
            $updateData = [
                'nama_menu' => $request->get('nama_menu') ?? foodModel::select('nama_menu')->where('id_menu', $id)->first()->nama_menu,
                'jenis' => $request->get('jenis') ?? foodModel::select('jenis')->where('id_menu', $id)->first()->jenis,
                'deskripsi' => $request->get('deskripsi') ?? foodModel::select('deskripsi')->where('id_menu', $id)->first()->deskripsi,
                'harga' => $request->get('harga') ?? foodModel::select('harga')->where('id_menu', $id)->first()->harga,
            ];

            
            // if ($request->hasFile('gambar')) {

            //     $image = $request->file('gambar');
            //     $imageName = $image->getClientOriginalName();
            //     $imagePath = $image->storeAs('images/menu', $imageName, 'public');

            //     // Add image path to update data
            //     $updateData['gambar'] = $imagePath;
           
            // }

            // save Update food 
            $save = foodModel::where('id_menu', $id)->update($updateData);

            // Cek penyimpanan food
            if ($save) {

                return response()->json(['status' => true, 'message' => 'Sukses memperbarui'], 200);
           
            } else {

                return response()->json(['status' => false, 'message' => 'Gagal memperbarui'], 500);
           
            }

        } else {

            return response()->json(['status' => false, 'message' => 'Hanya Admin yang bisa memperbarui'], 500);
        
        }
        
    }


    public function deleteFood($id)
    {

        $Auth = Auth::user();

        if ($Auth->role == "ADMIN") {

            //Delete berdasakan primary key
            $delete = foodModel::where('id_menu', $id)->delete();
            return response()->json($delete);

        } else {
            return response()->json(['status' => false, 'message' => 'Hanya Admin yang bisa menghapus'], status: 500);
        }

    }

}