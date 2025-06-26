<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class AdsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => [
                'required',
                'image',
                'mimes:jpg,png,jpeg,gif,svg',
                'max:4096',
                'dimensions:min_width=1500, min_height=290, max_height=310'
            ]
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Rasio gambar tidak sesuai. Mohon upload gambar dengan rasio mendekati 45:9 dan ukuran gambar beresolusi 1500x300 piksel.',
                'errors' => (object) []
            ], 422);
        }

        $image = $request->file('image');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
