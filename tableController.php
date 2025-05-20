<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\tableModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TableController extends Controller
{

    //Function used to get all data from table 
    public function getMeja()
    {

        $data = tableModel::all();
        return response()->json($data);

    }

    public function getMejaKosong()
    {

        $data = tableModel::where('status', 'KOSONG')->get();
        return response()->json($data);

    }

    //Function used to get data based on primary key
    public function getMejaId($id)
    {

        $data = tableModel::find($id);
        return response()->json($data);

    }

    //Function used to create table data
    public function addMeja(Request $request)
    {
        // Gets current user
        $Auth = Auth::user();

        // Check if current user's role is admin
        if ($Auth->role == "ADMIN") {

            // Validates input data
            $validator = Validator::make($request->all(), [
                'nomor_meja' => 'required|integer',
            ]);

            // Checks if validator fails
            if ($validator->fails()) {

                // Returns an error if so
                return response()->json($validator->errors()->toJson());

            }

            // Checks if there's a duplicate data
            $existingTable = tableModel::where('nomor_meja', $request->get('nomor_meja'))->first();
            if ($existingTable) {

                return response()->json(['status' => false, 'message' => 'Nomor Meja sudah ada'], 500);

            }

            // Creates a variable to save inputted data
            $save = tableModel::create([
                'nomor_meja' => $request->get('nomor_meja'),
                'status' => 'KOSONG',
            ]);

            // Checks if save is successful
            if ($save) {

                // If the $save is successful, return a 200 response
                // with a success message
                return response()->json(['status' => true, 'message' => 'Sukses menambah'], 200);

            } else {

                // If the $save is not successful, return a 500 response
                // with an error message
                return response()->json(['status' => false, 'message' => 'Gagal menambah'], 500);

            }

        } else {

            // Else returns an error
            return response()->json(['status' => false, 'message' => 'Hanya Admin yang bisa menambah'], 500);

        }

    }

    //Function used to update table data
    public function updateMeja(Request $request, $id)
    {
        // Gets current user
        $Auth = Auth::user();

        // Check if current user's role is admin
        if ($Auth->role == "ADMIN") {

            // Validates input data for registration
            $validator = Validator::make($request->all(), [
                'nomor_meja' => 'required|integer',
            ]);

            // Checks if validator fails
            if ($validator->fails()) {
                // Returns an error if so
                return response()->json($validator->errors()->toJson());
            }

            // Retrieve the current table data
            $table = tableModel::find($id);

            // Check if table exists
            if (!$table) {
                return response()->json(['status' => false, 'message' => 'Table not found'], 404);
            }

            // Check if the status is "KOSONG"
            if ($table->status !== 'KOSONG') {
                return response()->json(['status' => false, 'message' => 'Meja sedang digunakan'], 400);
            }

            // Check for duplicate nomor_meja
            $existingTable = tableModel::where('nomor_meja', $request->get('nomor_meja'))->where('id_meja', '!=', $id)->first();
            if ($existingTable) {
                return response()->json(['status' => false, 'message' => 'Nomor Meja sudah ada'], 400);
            }

            // Update the table data
            $save = $table->update([
                'nomor_meja' => $request->get('nomor_meja'),
            ]);

            // Checks if save is successful
            if ($save) {
                // If the $save is successful, return a 200 response
                // with a success message
                return response()->json(['status' => true, 'message' => 'Sukses mengubah'], 200);
            } else {
                // If the $save is not successful, return a 500 response
                // with an error message
                return response()->json(['status' => false, 'message' => 'Gagal mengubah'], 500);
            }

        } else {
            // Else returns an error
            return response()->json(['status' => false, 'message' => 'Hanya Admin yang bisa menambah'], 403);
        }
    }

    //Function used to delete table data

    public function deleteMeja($id)
    {

        //Gets current user
        $Auth = Auth::user();

        //Checks if the current user is ADMIN or not 
        if ($Auth->role == "ADMIN") {

            //Deletes table data based on primary key
            $table = tableModel::find($id);

            if ($table->status == "DIGUNAKAN") {

                return response()->json(['status' => false, 'message' => 'Meja sedang digunakan'], status: 500);

            } else {

                $table->delete();
                return response()->json(['status' => true, 'message' => 'Sukses menghapus'], status: 200);

            }

        } else {
            //else returns an error
            return response()->json(['status' => false, 'message' => 'Hanya Admin yang bisa menghapus'], status: 500);
        }

    }

}