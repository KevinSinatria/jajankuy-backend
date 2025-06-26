<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Google\Client;
use Google\Service\Dfareporting\Resource\Ads;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Support\Facades\Log;

class AdsController extends Controller
{
    private function initializeGoogleClient()
    {
        $client = new Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->setAccessType('offline');
        $client->setScopes(Drive::DRIVE_FILE);
        $client->addScope(Drive::DRIVE);
        $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));

        return $client;
    }

    private function makeFilePublic($fileId)
    {
        try {
            $client = $this->initializeGoogleClient();
            $service = new Drive($client);

            $permission = new Permission();
            $permission->setType('anyone');
            $permission->setRole('reader');
            $service->permissions->create($fileId, $permission);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ad = Ad::all();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data',
            'data' => $ad
        ], 200);
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

        try {
            $image = $request->file('image');
            $img = Image::read($image->getRealPath())->resize(1500, 300);
            $fileName = Str::random(20) . '_' . $image->getClientOriginalName();

            // Init google client
            $client = $this->initializeGoogleClient();
            $service = new Drive($client);

            // Upload file to Google Drive
            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'parents' => ['1suWR7pBZTrEQ2V_CTFDTTz-1o2w_dCmn'],
            ]);

            $content = $img->encode();
            $file = $service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $image->getClientMimeType(),
                'uploadType' => 'multipart',
                'fields' => 'id',
                'supportsAllDrives' => true
            ]);

            $fileId = $file->id;

            if(!$fileId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal upload gambar',
                    'errors' => (object) []
                ], 500);
            }

            if (!$this->makeFilePublic($fileId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat gambar menjadi publik',
                    'errors' => (object) []
                ], 500);
            }

            $directUrl = 'https://drive.google.com/uc?id=' . $fileId;

            $ads = Ad::create([
                'image_url' => $directUrl
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil upload gambar',
                'data' => $ads
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => (object) []
            ], 500);
        }

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
        try {
            $ad = Ad::find($id);

            if(!$ad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ads tidak ditemukan',
                    'errors' => (object) []
                ], 404);
            }

            preg_match('/id=([^&]+)/', $ad->image_url, $matches);
            $fileId = $matches[1] ?? null;

            if($fileId) {
                $client = $this->initializeGoogleClient();
                $service = new Drive($client);
                try {
                    $service->files->delete($fileId, ['supportsAllDrives' => true]);
                } catch (Exception $e) {
                    Log::warning($e->getMessage());

                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal menghapus gambar',
                        'errors' => (object) []
                    ], 500);
                }
            }

            $ad->delete();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menghapus gambar'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => (object) []
            ], 500);
        }
    }
}
